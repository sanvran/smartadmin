<?php
header('Access-Control-Allow-Origin: *');
include_once('../includes/crud.php');
$db = new Database();
$db->connect();
include_once('../includes/variables.php');
include_once('verify-token.php');
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
1.get-products-by-subcategory-id.php
    accesskey:90336
  	subcategory_id:32
  	user_id:369         // {optional}
  	limit:10            // {optional}
  	offset:0            // {optional}
  	sort:new / old / high / low     // {optional}
*/

if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey'])) {

    if (!isset($_POST['subcategory_id']) || empty($_POST['subcategory_id'])) {
        $response['error'] = true;
        $response['message'] = "Subcategory id is required.";
        print_r(json_encode($response));
        return false;
    }
    $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));

    $subcategory_id = (isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id'])) ? $db->escapeString($fn->xss_clean($_POST['subcategory_id'])) : "";
    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : '10';
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : '0';

    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : 'p.row_order + 0';
    $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $db->escapeString($fn->xss_clean($_POST['order'])) : "ASC";

    if ($access_key_received == $access_key) {
        $product = $fn->get_products($user_id, '', '', '', $subcategory_id, "p.status=1", $limit, $offset, $sort, $order);

        print_r(json_encode($product));
        return false;
    } else {
        $response['error'] = true;
        $response['message'] = "accesskey is incorrect.";
        print_r(json_encode($response));
    }
} else {
    $response['error'] = true;
    $response['message'] = "accesskey is required.";
    print_r(json_encode($response));
}
