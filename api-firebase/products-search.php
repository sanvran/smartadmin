<?php
header('Access-Control-Allow-Origin: *');
include_once('../includes/crud.php');
include_once('../includes/variables.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
date_default_timezone_set('Asia/Kolkata');
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
}

/*  
1.products-search.php
    accesskey:90336
	type:products-search
	search:Himalaya Baby Powder
    user_id:227     // {optional}
    offset:0        // {optional}
    limit:10        // {optional}
    sort:id         // {optional}
    order:ASC/DESC  // {optional}
*/

if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey'])) {
    if (isset($_POST['type']) && $_POST['type'] == 'products-search') {

        $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));

        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

        $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;
        $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
        $sort = (isset($_POST['sort']) && !empty($_POST['sort']) && is_numeric($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : 'p.row_order + 0';
        $order = (isset($_POST['order']) && !empty($_POST['order']) && is_numeric($_POST['order'])) ? $db->escapeString($fn->xss_clean($_POST['order'])) : 'ASC';

        if ($access_key_received == $access_key) {
            $product = $fn->get_products($user_id, '', '', '', '', '', $limit, $offset, $sort, $order, '', '', 1, $search);

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
