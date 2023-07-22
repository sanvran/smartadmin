<?php
session_start();
include '../includes/crud.php';
include_once('../includes/variables.php');
include_once('../includes/custom-functions.php');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');
$fn = new custom_functions;
include_once('verify-token.php');
$db = new Database();
$db->connect();
$response = array();
$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
$time_zone = $fn->set_timezone($config);
if (!$time_zone) {
    $response['error'] = true;
    $response['message'] = "Time Zone is not set.";
    print_r(json_encode($response));
    return false;
}


/* 
-------------------------------------------
APIs for eCart
-------------------------------------------
1. get_ticket_type
2. add_ticket
3. edit_ticket
4. get_tickets
5. send_message
6. get_messages
-------------------------------------------
-------------------------------------------
*/

if (!isset($_POST['accesskey'])) {
    $response['error'] = true;
    $response['message'] = "Access key is invalid or not passed!";
    print_r(json_encode($response));
    return false;
}

$accesskey = $db->escapeString($fn->xss_clean_array($_POST['accesskey']));
if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey!";
    print_r(json_encode($response));
    return false;
}

if (!verify_token()) {
    return false;
}


if ((isset($_POST['get_ticket_type'])) && ($_POST['get_ticket_type'] == 1)) {
    /* 
    1.get_ticket_type
        accesskey:90336
        get_ticket_type:1
        id:2                    // {optional}                      
        type:damaged product    // {optional}
        sort:id                 // {optioanl}
        order:ASC/DESC          // {optional}
        limit:10                // {optional}
        offset:0                // {optional}
        search:search_value     // {optional}
    */

    $where = "";
    $type = (isset($_POST['type']) && !empty($_POST['type'])) ? $db->escapeString($fn->xss_clean_array($_POST['type'])) : "";
    $id = (isset($_POST['id']) && !empty($_POST['id'])) ? $db->escapeString($fn->xss_clean_array($_POST['id'])) : "";

    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean_array($_POST['sort'])) : "id";
    $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $db->escapeString($fn->xss_clean_array($_POST['order'])) : "DESC";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit'])) ? $db->escapeString($fn->xss_clean_array($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset'])) ? $db->escapeString($fn->xss_clean_array($_POST['offset'])) : 0;

    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($_POST['search']);
        if ((empty($where))) {
            $where .= " WHERE (`id` like '%" . $search . "%' OR `type` like '%" . $search . "%' )";
        } else {
            $where .= " AND (`id` like '%" . $search . "%' OR `type` like '%" . $search . "%' )";
        }
    }

    if (isset($_POST['type']) && !empty($_POST['type'])) {
        $where .= (empty($where)) ? " WHERE type LIKE '%" . $type . "%' " : " AND type LIKE '%" . $type . "%'";
    }
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $where .= (empty($where)) ? " WHERE id= " . $id : " AND id= " . $id;
    }

    $sql = "SELECT count(id) as total FROM ticket_type $where";
    $db->sql($sql);
    $total = $db->getResult();

    $sql = "select * from ticket_type" . $where . " ORDER BY $sort $order LIMIT $offset,$limit ";
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {
        $response['error'] = false;
        $response['message'] = "Tickets Retrived successfully";
        $response['total'] = $total[0]['total'];
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Data not found";
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['add_ticket'])) && ($_POST['add_ticket'] == 1)) {
    /*
    2.add_ticket
        accesskey:90336
        add_ticket:1
        ticket_type_id:2
        user_id:1
        email:admin@gmail.com
        title:product image not displying
        message:its not showing images of products in App
        image[]:FILE      // {optional}
    */

    if (empty($_POST['ticket_type_id']) || empty($_POST['title']) || empty($_POST['email']) || empty($_POST['message']) || empty($_POST['user_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    $ticket_type_id = $db->escapeString($fn->xss_clean($_POST['ticket_type_id']));
    $user_id = $db->escapeString($fn->xss_clean($_POST['user_id']));
    $email = $db->escapeString($fn->xss_clean($_POST['email']));
    $title = $db->escapeString($fn->xss_clean($_POST['title']));
    $message = $db->escapeString($fn->xss_clean($_POST['message']));

    $error['image'] = '';
    $image = '';

    if (isset($_FILES["image"]) && !empty($_FILES["image"]["name"][0])) {
        if ($_FILES["image"]["error"] == 0) {
            for ($i = 0; $i < count($_FILES["image"]["name"]); $i++) {
                if ($_FILES["image"]["error"][$i] > 0) {
                    $response['error'] = true;
                    $response['message'] = "Images not uploaded!";
                    print_r(json_encode($response));
                    return false;
                } else {
                    $result = $fn->validate_other_images($_FILES["image"]["tmp_name"][$i], $_FILES["image"]["type"][$i]);
                    if ($result) {
                        $response['error'] = true;
                        $response['message'] = "image type must jpg, jpeg, gif, or png!";
                        print_r(json_encode($response));
                        return false;
                    }
                }
            }
        }

        if (($_FILES['image']['size'][0] > 0)) {
            $file_data = array();
            $target_path = '../upload/ticket_attachments/';
            if (!is_dir($target_path)) {
                mkdir($target_path, 0777, true);
            }

            $target_path1 = 'upload/ticket_attachments/';
            for ($i = 0; $i < count($_FILES["image"]["name"]); $i++) {
                $filename = $_FILES["image"]["name"][$i];
                $temp = explode('.', $filename);
                $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                $file_data[] = $target_path1 . '' . $filename;
                if (!move_uploaded_file($_FILES["image"]["tmp_name"][$i], $target_path . '' . $filename)) {
                    $response['error'] = true;
                    $response['message'] = "Images Images not uploaded!";
                    print_r(json_encode($response));
                    return false;
                }
            }
            $image = !empty($file_data) ? json_encode($file_data) : "";
        }
    }

    $sql = "INSERT INTO tickets (type_id,user_id,title, message,email,image) VALUES ('$ticket_type_id','$user_id', '$title', '$message', '$email','$image')";
    if ($db->sql($sql)) {
        $res = $fn->get_data('', '', 'tickets', '', '', 'ORDER BY id DESC', 'LIMIT 0,1');
        $ticket_id = $res[0]['id'];

        $sql_query = "INSERT INTO ticket_messages (ticket_id,type,user_id, message,attachments) VALUES ('$ticket_id','user', '$user_id', '$message','$image')";
        $db->sql(($sql_query));

        $res[0]['image'] = json_decode($res[0]['image'], 1);
        $res[0]['image'] = (empty($res[0]['image'])) ? array() : $res[0]['image'];
        for ($j = 0; $j < count($res[0]['image']); $j++) {
            $res[0]['image'][$j] = !empty(DOMAIN_URL . $res[0]['image'][$j]) ? DOMAIN_URL . $res[0]['image'][$j] : "";
        }

        $response['error'] = false;
        $response['message'] = "Tickets Added successfully";
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['edit_ticket'])) && ($_POST['edit_ticket'] == 1)) {
    /*
    3.edit_ticket
        accesskey:90336
        edit_ticket:1
        ticket_id:2
        ticket_type_id:2
        user_id:1
        title:product image not displying
        email:test@gmail.com
        message:its not showing images of products in App
        status:3 or 5 [3 -> resolved, 5 -> reopened]
        [1 -> pending, 2 -> opened, 3 -> resolved, 4 -> closed, 5 -> reopened]
    */

    if (empty($_POST['ticket_id']) || empty($_POST['ticket_type_id']) || empty($_POST['user_id']) || empty($_POST['title']) || empty($_POST['email']) || empty($_POST['message']) || empty($_POST['status'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    $ticket_id = $db->escapeString($fn->xss_clean($_POST['ticket_id']));
    $ticket_type_id = $db->escapeString($fn->xss_clean($_POST['ticket_type_id']));
    $user_id = $db->escapeString($fn->xss_clean($_POST['user_id']));
    $title = $db->escapeString($fn->xss_clean($_POST['title']));
    $email = $db->escapeString($fn->xss_clean($_POST['email']));
    $message = $db->escapeString($fn->xss_clean($_POST['message']));
    $status = $db->escapeString($fn->xss_clean($_POST['status']));

    $sql = "UPDATE tickets SET `type_id` =  '" . $ticket_type_id . "',`user_id` = '" . $user_id . "',`title` = '" . $title . "',`message` = '" . $message . "',`email` = '" . $email . "', `status` = '" . $status . "'  where `id`=" . $ticket_id;
    if ($db->sql($sql)) {
        $res = $fn->get_data($columns = ['tickets.*', 'ti_t.type'], '', 'tickets', 'LEFT JOIN ticket_type ti_t ON ti_t.id = tickets.type_id', '', 'ORDER BY tickets.id DESC', 'LIMIT 0,1');
        $ticket_id = $res[0]['id'];

        $res[0]['image'] = json_decode($res[0]['image'], 1);
        $res[0]['image'] = (empty($res[0]['image'])) ? array() : $res[0]['image'];
        for ($j = 0; $j < count($res[0]['image']); $j++) {
            $res[0]['image'][$j] = !empty(DOMAIN_URL . $res[0]['image'][$j]) ? DOMAIN_URL . $res[0]['image'][$j] : "";
        }

        $response['error'] = false;
        $response['message'] = "Tickets Updated successfully";
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['get_tickets'])) && ($_POST['get_tickets'] == 1)) {
    /*
    4.get_tickets
        accesskey:90336
        get_tickets:1
        ticket_id:2         // {optional}
        ticket_type_id:2    // {optional}
        user_id:1           // {optional}
        search:test         // {optional}
        sort:id             // {optioanl}
        order:ASC/DESC      // {optional}
        limit:10            // {optional}
        offset:0            // {optional}
        status:[1 -> pending, 2 -> opened, 3 -> resolved, 4 -> closed, 5 -> reopened]       // {optioanl}
    */

    $where = "";
    $ticket_id = (isset($_POST['ticket_id']) && !empty($_POST['ticket_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['ticket_id'])) : "";
    $ticket_type_id = (isset($_POST['ticket_type_id']) && !empty($_POST['ticket_type_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['ticket_type_id'])) : "";
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_id'])) : "";
    $status = (isset($_POST['status']) && !empty($_POST['status'])) ? $db->escapeString($fn->xss_clean_array($_POST['status'])) : "";

    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean_array($_POST['sort'])) : "id";
    $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $db->escapeString($fn->xss_clean_array($_POST['order'])) : "DESC";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit'])) ? $db->escapeString($fn->xss_clean_array($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset'])) ? $db->escapeString($fn->xss_clean_array($_POST['offset'])) : 0;

    if (isset($_POST['ticket_id']) && !empty($_POST['ticket_id'])) {
        $where .= (empty($where)) ? " WHERE t.id = " . $ticket_id : " AND t.id = " . $ticket_id;
    }
    if (isset($_POST['ticket_type_id']) && !empty($_POST['ticket_type_id'])) {
        $where .= (empty($where)) ? " WHERE t.type_id = " . $ticket_type_id : " AND t.type_id = " . $ticket_type_id;
    }
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        $where .= (empty($where)) ? " WHERE t.user_id = " . $user_id : " AND t.user_id = " . $user_id;
    }
    if (isset($_POST['status']) && !empty($_POST['status'])) {
        $where .= (empty($where)) ? " WHERE t.status = " . $status : " AND t.status = " . $status;
    }
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if ((empty($where))) {
            $where .= " WHERE (t.`id` like '%" . $search . "%' OR t.`title` like '%" . $search . "%' OR t.`message` like '%" . $search . "%' OR t.`status` like '%" . $search . "%')";
        } else {
            $where .= " and (t.`id` like '%" . $search . "%' OR t.`title` like '%" . $search . "%' OR t.`message` like '%" . $search . "%' OR t.`status` like '%" . $search . "%')";
        }
    }

    $sql = "SELECT count(t.id) as total FROM tickets t LEFT JOIN ticket_type ti_t ON ti_t.id = t.type_id $where";
    $db->sql($sql);
    $total = $db->getResult();

    $sql = "SELECT t.*,ti_t.type FROM tickets t LEFT JOIN ticket_type ti_t ON ti_t.id = t.type_id $where ORDER BY $sort $order LIMIT $offset,$limit";
    $db->sql($sql);
    $res = $db->getResult();
    $i = 0;

    foreach ($res as $row) {
        $row['image'] = json_decode($row['image'], 1);
        $row['image'] = (empty($row['image'])) ? array() : $row['image'];
        for ($j = 0; $j < count($row['image']); $j++) {
            $row['image'][$j] = !empty(DOMAIN_URL . $row['image'][$j]) ? DOMAIN_URL . $row['image'][$j] : "";
        }

        $data[$i] = $row;
        $i++;
    }
    if (!empty($data)) {
        $response['error'] = false;
        $response['message'] = "Tickets Retrived successfully";
        $response['total'] = $total[0]['total'];
        $response['data'] = $data;
    } else {
        $response['error'] = true;
        $response['message'] = "Data Not Found..!";
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['send_message'])) && ($_POST['send_message'] == 1)) {
    /*
    5.send_message
        accesskey:90336
        send_message:1
        type:user
        user_id:1
        ticket_id:2
        message:testing      // {optional}
        attachments[]: FILES  {type allowed -> image,video}     // {optional}
    */

    if (empty($_POST['type']) || empty($_POST['user_id']) || empty($_POST['ticket_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    $type = $db->escapeString($fn->xss_clean($_POST['type']));
    $user_id = $db->escapeString($fn->xss_clean($_POST['user_id']));
    $ticket_id = $db->escapeString($fn->xss_clean($_POST['ticket_id']));
    $message = (isset($_POST['message']) && !empty($_POST['message'])) ? $db->escapeString($fn->xss_clean($_POST['message'])) : "";

    $error['attachments'] = '';
    $attachments = '';

    $ticket = $fn->get_data('', 'id=' . $ticket_id, 'tickets');
    if (!empty($ticket)) {
        if ($ticket[0]['status'] == 'resolved' || $ticket[0]['status'] == 'closed') {
            $response['error'] = true;
            $response['message'] = "You can not send messgae because ticket is " . ucfirst($ticket[0]['status']) . " !";
            print_r(json_encode($response));
            return false;
        }

        if (isset($_FILES["attachments"]) && !empty($_FILES["attachments"]["name"][0])) {

            if ($_FILES["attachments"]["error"] == 0) {
                for ($i = 0; $i < count($_FILES["attachments"]["name"]); $i++) {
                    if ($_FILES["attachments"]["error"][$i] > 0) {
                        $response['error'] = true;
                        $response['message'] = "attachments not uploaded!";
                        print_r(json_encode($response));
                        return false;
                    } else {
                        if (!in_array($_FILES["attachments"]["tmp_name"][$i], array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'application/octet-stream'))) {
                            $result = $fn->validate_other_images($_FILES["attachments"]["tmp_name"][$i], $_FILES["attachments"]["type"][$i]);
                            if ($result) {
                                $response['error'] = true;
                                $response['message'] = "image type must jpg, jpeg, gif, or png!";
                                print_r(json_encode($response));
                                return false;
                            }
                        } elseif (!in_array($_FILES["attachments"]["tmp_name"][$i], array('video/mp4', 'video/mpeg', 'video/webm', 'video/mpg', 'application/octet-stream'))) {
                            $result = $fn->validate_video($_FILES["attachments"]["tmp_name"][$i], $_FILES["attachments"]["type"][$i]);
                            if ($result) {
                                $response['error'] = true;
                                $response['message'] = "Video type must mp4, mpeg, webm, or mpg!";
                                print_r(json_encode($response));
                                return false;
                            }
                        }
                    }
                }
            }
            // return false;

            if (($_FILES['attachments']['size'][0] > 0)) {
                $file_data = array();
                $target_path = '../upload/ticket_attachments/';
                if (!is_dir($target_path)) {
                    mkdir($target_path, 0777, true);
                }

                $target_path1 = 'upload/ticket_attachments/';
                for ($i = 0; $i < count($_FILES["attachments"]["name"]); $i++) {
                    $filename = $_FILES["attachments"]["name"][$i];
                    $temp = explode('.', $filename);
                    $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                    $file_data[] = $target_path1 . '' . $filename;
                    if (!move_uploaded_file($_FILES["attachments"]["tmp_name"][$i], $target_path . '' . $filename)) {
                        $response['error'] = true;
                        $response['message'] = "attachments not uploaded!";
                        print_r(json_encode($response));
                        return false;
                    }
                }
                $attachments = !empty($file_data) ? json_encode($file_data) : "";
            }
        }
        $date_created = date('Y-m-d H:m:s');
        $sql = "INSERT INTO ticket_messages (ticket_id,type,user_id, message,attachments,date_created) VALUES ('$ticket_id','$type', '$user_id', '$message','$attachments','$date_created')";
        if ($db->sql($sql)) {
            $res = $fn->get_data('', '', 'ticket_messages', '', '', 'ORDER BY id DESC', 'LIMIT 0,1');

            $res[0]['last_updated'] = !empty($res[0]['last_updated']) ? $res[0]['last_updated'] : "";
            $res[0]['attachments'] = json_decode($res[0]['attachments'], 1);
            $res[0]['attachments'] = (empty($res[0]['attachments'])) ? array() : $res[0]['attachments'];
            for ($j = 0; $j < count($res[0]['attachments']); $j++) {
                $res[0]['attachments'][$j] = !empty(DOMAIN_URL . $res[0]['attachments'][$j]) ? DOMAIN_URL . $res[0]['attachments'][$j] : "";
            }

            $response['error'] = false;
            $response['message'] = "Message Send successfully...";
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = "Message not Send.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Ticket id not found...";
    }

    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['get_messages'])) && ($_POST['get_messages'] == 1)) {
    /* 
    6.get_messages
        accesskey:90336
        get_messages:1
        ticket_id:2         
        user_type:2         // {optional}
        user_id:2           // {optional}
        search:test         // {optional}
        sort:id             // {optional}
        order:asc/desc      // {optional}
        offset:0            // {optional}
        limit:10            // {optional}
    */

    $where = "";
    $ticket_id = (isset($_POST['ticket_id']) && !empty($_POST['ticket_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['ticket_id'])) : "";
    $user_type = (isset($_POST['user_type']) && !empty($_POST['user_type'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_type'])) : "";
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_id'])) : "";

    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean_array($_POST['sort'])) : "id";
    $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $db->escapeString($fn->xss_clean_array($_POST['order'])) : "DESC";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit'])) ? $db->escapeString($fn->xss_clean_array($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset'])) ? $db->escapeString($fn->xss_clean_array($_POST['offset'])) : 0;

    if (isset($_POST['ticket_id']) && !empty($_POST['ticket_id'])) {
        $where .= (empty($where)) ? " WHERE tm.ticket_id = " . $ticket_id : " AND tm.ticket_id = " . $ticket_id;
    }
    if (isset($_POST['user_type']) && !empty($_POST['user_type'])) {
        $where .= (empty($where)) ? " WHERE tm.type =  '$user_type' " : " AND tm.type =  '$user_type' ";
    }
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        $where .= (empty($where)) ? " WHERE tm.user_id = " . $user_id : " AND tm.user_id = " . $user_id;
    }

    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if ((empty($where))) {
            $where .= " WHERE (tm.`id` like '%" . $search . "%' OR t.`title` like '%" . $search . "%' OR tm.`message` like '%" . $search . "%' OR tm.`type` like '%" . $search . "%')";
        } else {
            $where .= " AND (tm.`id` like '%" . $search . "%' OR t.`title` like '%" . $search . "%' OR tm.`message` like '%" . $search . "%' OR tm.`type` like '%" . $search . "%')";
        }
    }

    $sql = "SELECT count(tm.id) as total FROM ticket_messages tm LEFT JOIN tickets t ON t.id = tm.ticket_id $where";
    $db->sql($sql);
    $total = $db->getResult();

    $sql = "SELECT tm.*,case when tm.type='admin' then a.username else u.name end as username FROM ticket_messages tm LEFT JOIN tickets t ON t.id = tm.ticket_id LEFT JOIN users u ON u.id = tm.user_id LEFT JOIN admin a ON a.id = tm.user_id $where ORDER BY $sort $order LIMIT $offset,$limit";
    $db->sql($sql);
    $res = $db->getResult();
    $i = 0;

    foreach ($res as $row) {
        $row['last_updated'] = !empty($row['last_updated']) ? $row['last_updated'] : "";
        $row['attachments'] = json_decode($row['attachments'], 1);
        $row['attachments'] = (empty($row['attachments'])) ? array() : $row['attachments'];
        for ($j = 0; $j < count($row['attachments']); $j++) {
            $row['attachments'][$j] = !empty(DOMAIN_URL . $row['attachments'][$j]) ? DOMAIN_URL . $row['attachments'][$j] : "";
        }

        $data[$i] = $row;
        $i++;
    }

    if (!empty($data)) {
        $response['error'] = false;
        $response['message'] = "Message Retrived successfully";
        $response['total'] = $total[0]['total'];
        $response['data'] = $data;
    } else {
        $response['error'] = true;
        $response['message'] = "Data Not Found..!";
    }
    print_r(json_encode($response));
    return false;
}
