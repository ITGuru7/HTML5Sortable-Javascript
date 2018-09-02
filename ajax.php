<?php
    $result = '';

    if (isset($_POST['msg-type'])) {
        
        $msg_type = $_POST['msg-type'];

        $data = file_get_contents("data.json");
        $data = json_decode($data, true);
        if ($msg_type == 'load') {
            $result = $data;
        }
        elseif ($msg_type == 'update-label') {
            $data_id = $_POST['data-id'];
            $key = array_search($data_id, array_column($data, 'data-id'));
            $data[$key]['data-label'] = $_POST['data-label'];
            file_put_contents("data.json", json_encode($data, JSON_PRETTY_PRINT));
            $result = $data[$key]['data-label'];
        }
        elseif ($msg_type == 'update-content') {
            $data_id = $_POST['data-id'];
            $key = array_search($data_id, array_column($data, 'data-id'));
            $data[$key]['data-content'] = $_POST['data-content'];
            file_put_contents("data.json", json_encode($data, JSON_PRETTY_PRINT));
            $result = $data[$key]['data-content'];
        }
        elseif ($msg_type == 'delete-parent') {
            $data_id = $_POST['data-id'];
            $del_key = array_search($data_id, array_column($data, 'data-id'));
            $del_parent = $data[$del_key];

            $new_data = array();
            foreach ($data as $key => $value) {
                if(!isset($value['data-parent'])) {    // parent
                    if ($value['data-id'] != $data_id) {
                        if ($value['data-order'] > $del_parent['data-order']) {
                            $value['data-order'] = $value['data-order'] - 1;
                        }
                        array_push($new_data, $value);
                    }
                } else {                                // child
                    if ($value['data-parent'] != $del_parent['data-id']) {
                        array_push($new_data, $value);
                    }
                }
            }
            file_put_contents("data.json", json_encode($new_data, JSON_PRETTY_PRINT));
            $result = $new_data;
        }
        elseif ($msg_type == 'delete-child') {
            $data_id = $_POST['data-id'];
            $del_key = array_search($data_id, array_column($data, 'data-id'));
            $del_child = $data[$del_key];

            $new_data = array();
            foreach ($data as $key => $value) {
                if(!isset($value['data-parent'])) {    // parent
                    array_push($new_data, $value);
                } else {                                // child
                    if ($value['data-parent'] != $del_child['data-parent']) {   // other parent's child
                        array_push($new_data, $value);
                    } else {
                        if ($value['data-id'] != $del_child['data-id']) {
                            if ($value['data-order'] > $del_child['data-order']) {
                                $value['data-order'] = $value['data-order'] - 1;
                            }
                            array_push($new_data, $value);
                        }
                    }
                }
            }
            file_put_contents("data.json", json_encode($new_data, JSON_PRETTY_PRINT));
            $result = $new_data;
        }
        elseif ($msg_type == 'add-parent') {
            $data_id = 999;
            $result = $data;
        }
        else {

        }
    }
    else {
        $result = 'no update';
    }

    echo json_encode($result);
?>