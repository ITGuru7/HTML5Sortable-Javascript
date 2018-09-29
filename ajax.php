<?php
	session_start();

    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {

        $result = '';
        if ($result == '') {
            $result = '';
        }

        if (isset($_POST['msg-type'])) {
            
            $msg_type = $_POST['msg-type'];

            $data = file_get_contents("json/data.json");
            $data = json_decode($data, true);
            if ($msg_type == 'load') {
                $result = $data;
                echo json_encode($result);
            }
            elseif ($msg_type == 'update') {
                $update_data = $_POST['update-data'];

                foreach ($update_data as $update_dataItem) {
                    $index = array_search($update_dataItem['data-id'], array_column($data, 'data-id'));
                    foreach ($update_dataItem as $key => $value) {
                        if ($key == 'data-id' || $key == 'data-order' || $key == 'data-parent') {
                            $value = intval($value);
                        }
                        $data[$index][$key] = $value;
                    }
                }

                file_put_contents("json/data.json", json_encode($data, JSON_PRETTY_PRINT));
                echo json_encode("updated");
            }
            elseif ($msg_type == 'insert') {
                $insert_data = $_POST['insert-data'];
                array_push($data, $insert_data);

                file_put_contents("json/data.json", json_encode($data, JSON_PRETTY_PRINT));
                echo json_encode("inserted");
            }
            elseif ($msg_type == 'delete') {
                $delete_ids = $_POST['delete-ids'];
                $update_data = $_POST['update-data'];

                foreach ($delete_ids as $delete_id) {
                    $index = array_search($delete_id, array_column($data, 'data-id'));
                    array_splice($data, $index, 1);
                }
                foreach ($update_data as $update_dataItem) {
                    $index = array_search($update_dataItem['data-id'], array_column($data, 'data-id'));
                    foreach ($update_dataItem as $key => $value) {
                        if ($key == 'data-id' || $key == 'data-order' || $key == 'data-parent') {
                            $value = intval($value);
                        }
                        $data[$index][$key] = $value;
                    }
                }

                file_put_contents("json/data.json", json_encode($data, JSON_PRETTY_PRINT));
                echo json_encode("deleted");
            }
            else {
            }
        
        }
        else {
            $result = 'no update';
        }
    }

?>