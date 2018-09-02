<?php

    $result = '';

    if (isset($_POST['msg-type'])) {
        
        $msg_type = $_POST['msg-type'];

        $data = file_get_contents("json/data.json");
        $data = json_decode($data, true);
        if ($msg_type == 'load') {
            $result = $data;
            echo json_encode($result);
        }
        elseif ($msg_type == 'update-label') {
            $data_id = $_POST['data-id'];
            $key = array_search($data_id, array_column($data, 'data-id'));
            $data[$key]['data-label'] = $_POST['data-label'];
            file_put_contents("json/data.json", json_encode($data, JSON_PRETTY_PRINT));
            $result = $data[$key]['data-label'];
            echo json_encode($result);
        }
        elseif ($msg_type == 'update-content') {
            $data_id = $_POST['data-id'];
            $key = array_search($data_id, array_column($data, 'data-id'));
            $data[$key]['data-content'] = $_POST['data-content'];
            file_put_contents("json/data.json", json_encode($data, JSON_PRETTY_PRINT));
            $result = $data[$key]['data-content'];
            echo json_encode($result);
        }
        elseif ($msg_type == 'delete-parent') {
            $data_id = $_POST['data-id'];
            $del_key = array_search($data_id, array_column($data, 'data-id'));
            $del_parent = $data[$del_key];

            $new_data = array();
            foreach ($data as $key => $value) {
                if ($value['data-type'] == 'parent') {    // parent
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
            file_put_contents("json/data.json", json_encode($new_data, JSON_PRETTY_PRINT));
            $result = $new_data;
            echo json_encode($result);
        }
        elseif ($msg_type == 'delete-child') {
            $data_id = $_POST['data-id'];
            $del_key = array_search($data_id, array_column($data, 'data-id'));
            $del_child = $data[$del_key];

            $new_data = array();
            foreach ($data as $key => $value) {
                if ($value['data-type'] == 'parent') {    // parent
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
            file_put_contents("json/data.json", json_encode($new_data, JSON_PRETTY_PRINT));
            $result = $new_data;
            echo json_encode($result);
        }
        elseif ($msg_type == 'order-parent') {
            $data_id = $_POST['data-id'];
            $data_order = $_POST['data-order'];

            $key = array_search($data_id, array_column($data, 'data-id'));
            $data_element = $data[$key];

            $new_data = array();
            $order_flag = 0;
            foreach ($data as $key => $value) {
                if ($value['data-type'] == 'parent') {
                    if ($value['data-id'] != $data_id) {
                        if ($value['data-order'] + $order_flag == $data_order) {
                            $data_element['data-order'] = $data_order;
                            array_push($new_data, $data_element);
                            for ($child_key = 0; $child_key < count($data); $child_key ++) {
                                $child = $data[$child_key];
                                if ($child['data-type'] == 'child' && $child['data-parent'] == $data_id) {
                                    array_push($new_data, $child);
                                }
                            }
                            $order_flag ++;
                        }
                        $value['data-order'] = $value['data-order'] + $order_flag;
                        array_push($new_data, $value);
                        for ($child_key = 0; $child_key < count($data); $child_key ++) {
                            $child = $data[$child_key];
                            if ($child['data-type'] == 'child' && $child['data-parent'] == $value['data-id']) {
                                array_push($new_data, $child);
                            }
                        }
                    } else {
                        $order_flag --;
                    }
                }
            }
            file_put_contents("json/data.json", json_encode($new_data, JSON_PRETTY_PRINT));
            $result = $new_data;
            echo json_encode($result);
        }
        elseif ($msg_type == 'order-child') {
            $data_id = $_POST['data-id'];
            $data_order = $_POST['data-order'];
            $data_parent = $_POST['data-parent'];

            $key = array_search($data_id, array_column($data, 'data-id'));
            $data_element = $data[$key];
            $old_parent = $data_element['data-parent'];
            $data_element['data-parent'] = $data_parent;
            $data_element['data-order'] = $data_order;

            $new_data = array();
            $order_flag = 0;
            foreach ($data as $key => $value) {
                if ($value['data-type'] == 'parent') {
                    array_push($new_data, $value);
                    if ($value['data-id'] == $data_parent) {        // new-parent
                        if ($data_parent != $old_parent) {  // different parent
                            $order_flag = 0;
                            for ($child_key = 0; $child_key < count($data); $child_key ++) {
                                $child = $data[$child_key];
                                if ($child['data-type'] == 'child' && $child['data-parent'] == $data_parent) {
                                    if ($child['data-order'] + $order_flag == $data_order) {
                                        array_push($new_data, $data_element);
                                        $order_flag ++;
                                    }
                                    $child['data-order'] = $child['data-order'] + $order_flag;
                                    if ($child['data-id'] != $data_id) {
                                        array_push($new_data, $child);
                                    }
                                }
                            }
                            if ($order_flag == 0) {
                                array_push($new_data, $data_element);
                                $order_flag ++;
                            }
                        } else {        // same parent
                            $order_flag = 0;
                            for ($child_key = 0; $child_key < count($data); $child_key ++) {
                                $child = $data[$child_key];
                                if ($child['data-type'] == 'child' && $child['data-parent'] == $data_parent) {
                                    if ($child['data-id'] != $data_id) {
                                        if ($child['data-order'] + $order_flag == $data_order) {
                                            array_push($new_data, $data_element);
                                            $order_flag ++;
                                        }
                                        $child['data-order'] = $child['data-order'] + $order_flag;
                                        array_push($new_data, $child);
                                    } else {
                                        $order_flag --;
                                    }
                                }
                            }
                        }
                    }
                    elseif ($value['data-id'] == $old_parent) {    // old-parent
                        $order_flag = 0;
                        for ($child_key = 0; $child_key < count($data); $child_key ++) {
                            $child = $data[$child_key];
                            if ($child['data-type'] == 'child' && $child['data-parent'] == $value['data-id']) {
                                if ($child['data-id'] == $data_id) {
                                    $order_flag --;
                                }
                                else {
                                    $child['data-order'] = $child['data-order'] + $order_flag;
                                    array_push($new_data, $child);
                                }
                            }
                        }
                    }
                    else {
                        for ($child_key = 0; $child_key < count($data); $child_key ++) {
                            $child = $data[$child_key];
                            if ($child['data-type'] == 'child' && $child['data-parent'] == $value['data-id']) {
                                array_push($new_data, $child);
                            }
                        }
                    }
                }
            }
            file_put_contents("json/data.json", json_encode($new_data, JSON_PRETTY_PRINT));
            $result = $new_data;
            echo json_encode($result);
        }
        elseif ($msg_type == 'add-parent') {
            $data_id = 0;
            $data_order = 0;
            foreach ($data as $key => $value) {
                if ($data_id < $value['data-id']) {
                    $data_id = $value['data-id'];
                }
                if ($value['data-type'] == 'parent') {    // parent
                    if ($data_order < $value['data-order']) {
                        $data_order = $value['data-order'];
                    }
                }
            }
            $data_id ++;
            $data_order ++;
            $new_parent = array(
                'data-id'       => $data_id,
                'data-type'     => 'parent',
                'data-label'    => 'New Parent',
                'data-content'  => '',
                'data-order'    => $data_order,
            );
            array_push($data, $new_parent);
            file_put_contents("json/data.json", json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode($new_parent);
        }
        elseif ($msg_type == 'add-child') {
            $data_parent = $_POST['data-parent'];
            $data_id = 0;
            $data_order = 0;
            $key = array_search($data_parent, array_column($data, 'data-id'));
            $parent = $data[$key];
            foreach ($data as $key => $value) {
                if ($data_id < $value['data-id']) {
                    $data_id = $value['data-id'];
                }
                if ($value['data-type'] == 'child' && $value['data-parent'] == $data_parent) {    // parent's child
                    if ($data_order < $value['data-order']) {
                        $data_order = $value['data-order'];
                    }
                }
            }
            $data_id ++;
            $data_order ++;
            $new_child = array(
                'data-id'       => $data_id,
                'data-type'     => 'child',
                'data-label'    => 'New Child',
                'data-content'  => '',
                'data-order'    => $data_order,
                'data-parent'   => $data_parent,
            );

            $new_data = array();
            $new_child_added = false;
            foreach ($data as $key => $value) {
                if ($value['data-type'] == 'parent' && $value['data-order'] == $parent['data-order'] + 1) {
                    array_push($new_data, $new_child);
                    $new_child_added = true;
                }
                array_push($new_data, $value);
            }
            if ($new_child_added == false) {
                array_push($new_data, $new_child);
            }

            file_put_contents("json/data.json", json_encode($new_data, JSON_PRETTY_PRINT));
            $result = array(
                'data' => $new_data,
                'new_child' => $new_child, 
            );
            echo json_encode($result);
        }
        else {
        }
    
    }
    else {
        $result = 'no update';
    }

?>