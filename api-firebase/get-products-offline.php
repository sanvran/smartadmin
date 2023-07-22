<?php
header('Access-Control-Allow-Origin: *');
include_once('../includes/variables.php');
include_once('../includes/crud.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');
$fn = new custom_functions;

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
$time_zone = $fn->set_timezone($config);
if (!$time_zone) {
    $response['error'] = true;
    $response['message'] = "Time Zone is not set.";
    print_r(json_encode($response));
    return false;
    exit();
}

/* 
1.get-products-offline.php
    accesskey:90336
    get_products_offline:1
    product_ids:214,215 
    user_id:452     // {optional}
    offset:0        // {optional}
    limit:10        // {optional}
    sort:p.id       // {optional}
    order:ASC / DESC    // {optional}
*/

if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey'])) {
    if ((isset($_POST['get_products_offline']) && $_POST['get_products_offline'] == 1)) {
        if (!isset($_POST['product_ids']) || empty($_POST['product_ids'])) {
            $response['error'] = true;
            $response['message'] = "Product id is required.";
            print_r(json_encode($response));
            return false;
        }
        $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));

        $product_id = $db->escapeString($fn->xss_clean($_POST['product_ids']));
        $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

        $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($_POST['offset'])) : 0;
        $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($_POST['limit'])) : 10;
        $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : "p.row_order + 0 ";
        $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $db->escapeString($fn->xss_clean($_POST['order'])) : "ASC";

        if ($access_key_received == $access_key) {
            $product = $fn->get_products($user_id, $product_id, '', '', '', '', $limit, $offset, $sort, $order, '', '', 1);

            print_r(json_encode($product));
            return false;
        } else {
            $response['error'] = true;
            $response['message'] = "accesskey is incorrect.";
            print_r(json_encode($response));
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Please Pass All Field.";
        print_r(json_encode($response));
    }
} else {
    $response['error'] = true;
    $response['message'] = "accesskey is required.";
    print_r(json_encode($response));
}
