<?php
    $result = '';

    if (isset($_POST['msg-type'])) {
        
        $msg_type = $_POST['msg-type'];

        $data = file_get_contents ("data.json");
        $data = json_decode($data, true);
        if ($msg_type == 'load') {
            $result = $data;
        }
        elseif ($msg_type == 'update-label') {
            $data_id = $_POST['data-id'];
            $key = array_search($data_id, array_column($data, 'data-id'));
            $data[$key]['data-label'] = $_POST['data-label'];
            $result = $data[$key]['data-label'];
        }
        else {

        }
    }
    else {
        $result = 'no update';
    }

    echo json_encode($result);
?>