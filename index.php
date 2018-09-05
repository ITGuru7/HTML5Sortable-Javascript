<?php
	session_start();

	if (! isset($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = base64_encode(openssl_random_pseudo_bytes(32));
	}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta charset="utf-8">
	<meta content=<?php echo $_SESSION['csrf_token']?> name="csrf-token" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="description" content="HTML5Sortable">
	<meta name="author" content="natalia">

	<title>HTML5 Sortable Project</title>

  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

	<!-- jQuery library -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

  <!-- Popper JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>

  <!-- Latest compiled JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

	<script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>

	<script src="js/jquery.js"></script>
	<script src="js/underscore.js"></script>

	<!-- Custom styles/js for this template -->
	<link href="css/style.css" rel="stylesheet">
	<script src="js/html5sortable.js"></script>

</head>

<body>
	<!-- The Modal Edit -->
		<div class="modal fade" id="editModal">
			<div class="modal-dialog">
				<div class="modal-content">
			
					<!-- Modal Header -->
					<div class="modal-header">
						<h4 class="modal-title">Edit</h4>
						<input type="hidden">
						<button class="close" data-dismiss="modal">&times;</button>
					</div>
			
					<!-- Modal body -->
					<div class="modal-body">
						<textarea rows="10" cols="50" autofocus class="w-100 h-100"></textarea>
					</div>
			
					<!-- Modal footer -->
					<div class="modal-footer">
						<button class="btn btn-save text-white font-weight-bold" data-dismiss="modal">Save</button>
						<button class="btn text-white font-weight-bold" data-dismiss="modal">Cancel</button>
					</div>
			
				</div>
			</div>
		</div>

	<!-- Temp Data -->
		<div>
			<div id="temp-parent" class="row my-4 p-0 pt-1 nested-list d-none">
				<div class="row m-1 p-2 border-radius-3 bg-transparent w-100 d-flex justify-content-between">
					<div class="col p-0 d-flex justify-content-start">
						<i class="fa fa-th pt-2 mr-2 text-muted parent-handle"></i>
						<span class="d-none d-md-block">Title:&nbsp;</span>
						<label>New Parent</label>
					</div>
					<div class="col p-0 d-flex justify-content-end">
						<button class="btn btn-edit py-1 text-white font-weight-bold" data-toggle="modal" data-target="#editModal"><i class="fa fa-pencil"></i>&nbsp;<span class="d-none d-md-block float-right text-white">Edit</span></button>
						<button class="btn btn-delete-parent py-1 text-white font-weight-bold"><i class="fa fa-trash"></i>&nbsp;<span class="d-none d-md-block float-right text-white">Delete</span></button>
						<button class="btn btn-add-child py-1 text-white font-weight-bold"><i class="fa fa-plus"></i>&nbsp;<span class="d-none d-md-block float-right text-white">Add A Lesson</span></button>
					</div>
				</div>

				<div class="container-fluid p-0 pb-1 nested-children-container">
				</div>
			</div>

			<div id="temp-child" class="row mx-1 my-2 p-2 border-radius-3 nested-child bg-white d-none">
				<div class="col p-0 d-flex justify-content-start">
					<i class="fa fa-th text-muted child-handle pt-2 mr-2"></i>
					<span class="d-none d-md-block">Title:&nbsp;</span>
					<label>New Child</label>
				</div>
				<div class="col p-0 d-flex justify-content-end">
					<button class="btn btn-edit py-1 text-white font-weight-bold" data-toggle="modal" data-target="#editModal"><i class="fa fa-pencil"></i>&nbsp;<span class="d-none d-md-block float-right text-white">Edit</span></button>
					<button class="btn btn-delete-child py-1 text-white font-weight-bold"><i class="fa fa-trash"></i>&nbsp;<span class="d-none d-md-block float-right text-white">Delete</span></button>
				</div>
			</div>

		</div>

	<!-- Main Content -->
		<div id="page-container" class="container my-5 pb-1">

			<div class="row">
				<div class="alert alert-info d-none">
					<strong>Wait!</strong> requesting server.
				</div>
			</div>
			<div class="row">
				<button class="btn btn-add-parent py-1 text-white font-weight-bold ml-3"><i class="fa fa-plus"></i>&nbsp;<span class="d-none d-md-block float-right text-white">Add Nested List</span></button>
			</div>

		</div>

</body>

<script>

	window.csrf = { csrf_token: '<?php echo $_SESSION['csrf_token']; ?>' };

	//	load
		$(document).ready(function(){
			$.ajaxSetup({
				data: window.csrf
			});

			var message = {
				'msg-type'	: 'load'
			};
			AJAX(message);
		});

		var m_bEditing = false;

		init_sortable = () => {
			sortable('#page-container', {
				forcePlaceholderSize: true,
				handle: '.parent-handle',
				items: '.nested-list',
				placeholderClass: 'border',
			});
			sortable('.nested-children-container', {
				forcePlaceholderSize: true,
				acceptFrom: '.nested-children-container',
				handle: '.child-handle',
				items: '.nested-child',
				maxItems: 10,
				placeholderClass: 'border',
			});
		}

		init_sortable();

		reload_sortable = () => {
			sortable('#page-container');
			sortable('.nested-children-container');
		}

		set_sortable = (mode)  => {
			sortable($('#page-container'), mode);
			sortable($('.nested-children-container'), mode);
		}

		var jsonData = [];

		function search_array_with_props (search_array, search_props) {
			for (let index = 0; index < search_array.length; index ++) {
				var element = search_array[index];
				var search_keys = Object.keys(search_props);
				let i;
				for (i = 0; i < search_keys.length; i ++) {
					if (element[search_keys[i]] != search_props[search_keys[i]]) {
						break;
					}
				}
				if (i >= search_keys.length) {
					return index;
				}
			}
			return null;
		}


	//	update label/content
		function onEditTitle() {
			if(m_bEditing == false){
				m_bEditing = true;
				$(this).off('click');
				var label_title = $(this).text();
				$(this).html('<input type=text value="' + label_title + '" autofocus>');
				$('button').prop('disabled', true);
				$(this).find('input').keypress(onEndEditTitle);
				$(this).find('input').focusout(onEndEditTitle);
				set_sortable('disable');
			}
		}
		$('label').on('click', onEditTitle);

		function onEndEditTitle(event) {
			if(m_bEditing == true) {
				if (!( event.which == 13 || event.type == 'focusout')) {
					return;
				}
//				event.preventDefault();
				m_bEditing = false;
				var label_handler = $(this).parent();
				label_handler.on('click', onEditTitle);
				var label_title = label_handler.find('input').val();
				label_handler.html(label_title);
				$(this).remove();
				$('button').prop('disabled', false);
				set_sortable('enable');

				var data_id = label_handler.parent().parent().attr('data-id');
				if (data_id == null) {
					data_id = label_handler.parent().parent().parent().attr('data-id');
				}

				var index = search_array_with_props (jsonData, {'data-id' : data_id});
				jsonData[index]['data-label'] = label_title;
				var message = {
					'msg-type'		: 'update',
					'update-data'	: [
						{
							'data-id'		: data_id,
							'data-label'	: label_title,
						},
					],
				};
				AJAX(message);
			}
		}

		function onEditContent() {
			var data_id = $(this).parent().parent().attr('data-id');
			if (data_id == null) {
				data_id = $(this).parent().parent().parent().attr('data-id');
			}

			var index = search_array_with_props (jsonData, {'data-id' : data_id});
			$('.modal').find('input[type=hidden]').val(data_id);
			$('.modal').find('.modal-title').text(jsonData[index]['data-label']);
			$('.modal').find('.modal-body textarea').val(jsonData[index]['data-content']);
		}

		function onSaveContent() {
			var data_id = $('.modal').find('input[type=hidden]').val();
			var data_content = $('.modal').find('textarea').val();

			var index = search_array_with_props (jsonData, {'data-id' : data_id});
			jsonData[index]['data-content'] = data_content;
			var message = {
				'msg-type'		: 'update',
				'update-data'	: [
					{
						'data-id'		: data_id,
						'data-content'	: data_content,
					},
				],
			};
			AJAX(message);
		}
		$('button.btn-save').on('click', onSaveContent);

	//	delete parent/child
		function onDeleteParent() {
			var data_id = $(this).parent().parent().parent().attr('data-id');
			$(this).parent().parent().parent().remove();
			reload_sortable();

			var message = {
				'msg-type'			: 'delete',
				'delete-ids'		: [data_id,],
				'update-data'		: [],
			};

			var index = search_array_with_props (jsonData, {'data-id' : data_id});
			var delete_parent = jsonData[index];
			jsonData.splice(index, 1);

			for (let i = 0; i < jsonData.length; i ++) {
				let element = jsonData[i];
				if (element['data-type'] == 'child' && element['data-parent'] == data_id) {
					jsonData.splice(i, 1);
					message['delete-ids'].push(element['data-id']);
					i --;
				}
			}

			jsonData.forEach ((element, index) => {
				if (element['data-type'] == 'parent' && element['data-order'] > delete_parent['data-order']) {
					element['data-order'] --;
					$('.nested-list[data-id="' + element['data-id'] + '"]').attr('data-order', element['data-order']);
					message['update-data'].push({
						'data-id'		: element['data-id'],
						'data-order'	: element['data-order'],
					});
				}
			});

			AJAX(message);
		}
		$('button.btn-delete-parent').on('click', onDeleteParent);

		function onDeleteChild() {
			var data_id = $(this).parent().parent().attr('data-id');
			$(this).parent().parent().remove();
			reload_sortable();

			var message = {
				'msg-type'		: 'delete',
				'delete-ids'	: [data_id],
				'update-data'	: [],
			};

			var index = search_array_with_props (jsonData, {'data-id' : data_id});
			var delete_child = jsonData[index];
			jsonData.splice(index, 1);

			jsonData.forEach ((element, index) => {
				if (element['data-type'] == 'child' && element['data-parent'] == delete_child['data-parent'] && element['data-order'] > delete_child['data-order']) {
					element['data-order'] --;
					$('.nested-child[data-id="' + element['data-id'] + '"]').attr('data-order', element['data-order']);
					message['update-data'].push({
						'data-id' 		: element['data-id'],
						'data-order'	: element['data-order'],
					});
				}
			});

			AJAX(message);
		}
		$('button.btn-delete-child').on('click', onDeleteChild);

	//	adding parent/child not ready for new data-id
		function addParent(jsonParent) {
			var newParent = $('#temp-parent').clone();
			newParent.attr('id', '');
			newParent.removeClass('d-none');
			newParent.find('button.btn-edit').on('click', onEditContent);
			newParent.find('button.btn-delete-parent').on('click', onDeleteParent);
			newParent.find('button.btn-add-child').on('click', onAddChild);
			newParent.find('label').on('click', onEditTitle);
			newParent.attr('data-id', jsonParent['data-id']);
			newParent.attr('data-type', 'parent');
			newParent.attr('data-order', jsonParent['data-order']);
			newParent.find('label').text(jsonParent['data-label']);
			$('#page-container').append(newParent);

			newParent.find('.nested-children-container').get(0).addEventListener('sortupdate', onSortUpdateChild);
		}
		function onAddParent() {
			var data_id = 0;
			var data_order = 0;
			jsonData.forEach ((element, index) => {
				if (data_id < element['data-id']) {
					data_id = element['data-id'];
				}
				if (element['data-type'] == 'parent' && data_order < element['data-order']) {
					data_order = element['data-order'];
				}
			});
			data_id ++;
			data_order ++;
			var newParent = {
				'data-id'		: data_id,
				'data-type'		: 'parent',
				'data-label'	: 'New Parent',
				'data-content'	: '',
				'data-order'	: data_order,
			};

			addParent(newParent);

			jsonData.push(newParent);

			var message = {
				'msg-type'		: 'insert',
				'insert-data'	: newParent,
			};
			AJAX(message);

			reload_sortable();
		}
		$('button.btn-add-parent').on('click', onAddParent);

		function addChild(parent, jsonChild) {
			var newChild = $('#temp-child').clone();
			newChild.attr('id', '');
			newChild.removeClass('d-none');
			newChild.find('button.btn-edit').on('click', onEditContent);
			newChild.find('button.btn-delete-child').on('click', onDeleteChild);
			newChild.find('label').on('click', onEditTitle);
			if(jsonChild != null) {
				newChild.attr('data-id', jsonChild['data-id']);
				newChild.attr('data-type', 'child');
				newChild.attr('data-order', jsonChild['data-order']);
				newChild.find('label').text(jsonChild['data-label']);
			}
			parent.find('.nested-children-container').append(newChild);
		}
		function onAddChild() {
			var parent = $(this).parent().parent().parent();
			var data_parent = parent.attr('data-id');

			var data_id = 0;
			var data_order = 0;
			jsonData.forEach ((element, index) => {
				if (data_id < element['data-id']) {
					data_id = element['data-id'];
				}
				if (element['data-type'] == 'child' && element['data-parent'] == data_parent && data_order < element['data-order']) {
					data_order = element['data-order'];
				}
			});
			data_id ++;
			data_order ++;
			var newChild = {
				'data-id'		: data_id,
				'data-type'		: 'child',
				'data-label'	: 'New Child',
				'data-content'	: '',
				'data-order'	: data_order,
				'data-parent'	: data_parent
			};

			addChild(parent, newChild);

			jsonData.push(newChild);

			var message = {
				'msg-type'		: 'insert',
				'insert-data'	: newChild,
			};
			AJAX(message);

			reload_sortable();
		}
		$('button.btn-add-child').on('click', onAddChild);

	//	order parent/child
		function onSortUpdateParent(e) {
			var data_id = e.detail.destination.items[e.detail.destination.index].getAttribute('data-id');
			var old_data_order = $('.nested-list[data-id="' + data_id + '"]').attr('data-order');
			var new_data_order = e.detail.destination.index + 1;

			jsonData.forEach ((element, index) => {
				if (element['data-type'] == 'parent' && element['data-order'] > old_data_order) {
					element['data-order'] --;
				}
			});
			jsonData.forEach ((element, index) => {
				if (element['data-type'] == 'parent' && element['data-order'] >= new_data_order) {
					element['data-order'] ++;
				}
			});

			var message = {
				'msg-type'		: 'update',
				'update-data'	: [], 
			};

			var index = search_array_with_props (jsonData, {'data-id' : data_id});
			jsonData[index]['data-order'] = new_data_order;
			message['update-data'].push({
				'data-id' 		: jsonData[index]['data-id'],
				'data-order'	: new_data_order,
			});

			jsonData.forEach ((element, index) => {
				if (element['data-type'] == 'parent') {
					var old_element_order = $('.nested-list[data-id="' + element['data-id'] + '"]').attr('data-order');
					if (element['data-order'] != old_element_order) {
						$('.nested-list[data-id="' + element['data-id'] + '"]').attr('data-order', element['data-order']);
						message['update-data'].push({
							'data-id' 		: element['data-id'],
							'data-order'	: element['data-order'],
						});
					}
				}
			});

			AJAX(message);
		}
		document.querySelector('#page-container').addEventListener('sortupdate', onSortUpdateParent);

		function onSortUpdateChild(e) {
			var data_id = e.detail.destination.items[e.detail.destination.index].getAttribute('data-id');
			var old_data_order = $('.nested-child[data-id="' + data_id + '"]').attr('data-order');
			var new_data_order = e.detail.destination.index + 1;
			var old_parent_id = e.detail.origin.container.parentElement.getAttribute('data-id');
			var new_parent_id = e.detail.destination.container.parentElement.getAttribute('data-id');

			jsonData.forEach ((element, index) => {
				if (element['data-type'] == 'child' && element['data-parent'] == old_parent_id && element['data-order'] > old_data_order) {
					element['data-order'] --;
				}
			});
			jsonData.forEach ((element, index) => {
				if (element['data-type'] == 'child' && element['data-parent'] == new_parent_id && element['data-order'] >= new_data_order) {
					element['data-order'] ++;
				}
			});
			var index = search_array_with_props (jsonData, {'data-id' : data_id});
			jsonData[index]['data-order'] = new_data_order;
			jsonData[index]['data-parent'] = new_parent_id;

			$('.nested-child[data-id="' + data_id + '"]').attr('data-order', new_data_order);
			var message = {
				'msg-type'		: 'update',
				'update-data'	: [
					{
						'data-id'		: data_id,
						'data-order'	: new_data_order,
						'data-parent'	: new_parent_id,
					},
				],
			};

			jsonData.forEach ((element, index) => {
				if (element['data-type'] == 'child' && (element['data-parent'] == old_parent_id || element['data-parent'] == new_parent_id)) {
					var old_element_order = $('.nested-child[data-id="' + element['data-id'] + '"]').attr('data-order');
					if (element['data-order'] != old_element_order) {
						$('.nested-child[data-id="' + element['data-id'] + '"]').attr('data-order', element['data-order']);
						message['update-data'].push({
							'data-id'		: element['data-id'],
							'data-order'	: element['data-order'],
						});
					}
				}
			});

			AJAX(message);
		}
		$('.nested-children-container').each(function( index ) {
			$(this).get(0).addEventListener('sortupdate', onSortUpdateChild);
		});


	//	AJAX message function
		function AJAX(message) {
			$('.alert').removeClass('d-none');
			$.ajax({
				type: "POST",
				url: "./ajax.php",
				data: message,
				success: function(result) {
					var msg_type = message['msg-type'];
					if (msg_type == 'load') {
						result = JSON.parse(result);
						var parent_cnt = 0;
						result.forEach ((element) => {
							if (element['data-type'] == 'parent') {	// parent
								parent_cnt ++;
							}
						});
						for (let i = 1; i <= parent_cnt; i ++) {
							var parent_index = search_array_with_props (result, {'data-type' : 'parent', 'data-order' : i});
							var parent = result[parent_index];
							addParent(parent);
							jsonData.push(parent);
							var child_cnt = 0;
							result.forEach ((element) => {
								if (element['data-type'] == 'child' && element['data-parent'] == parent['data-id']) {	// parent's child
									child_cnt ++;
								}
							});
							for (let j = 1; j <= child_cnt; j ++) {
								var child_index = search_array_with_props (result, {'data-parent' : parent['data-id'], 'data-order' : j});
								var child = result[child_index];
								var parentElement = $('div[data-id="' + parent['data-id'] + '"]');
								addChild(parentElement, child);
								jsonData.push(child);
							}
						}
						reload_sortable();
					}
					// else if (msg_type == 'insert') {
					// }
					// else if (msg_type == 'delete') {
					// }
					// else if (msg_type == 'update') {
					// }

					$('.alert').addClass('d-none');
				},
			});
		}

</script>

</html>