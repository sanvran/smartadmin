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
    exit();
}

if (!isset($_POST['accesskey'])) {
    $response['error'] = true;
    $response['message'] = "Access key is invalid or not passed!";
    print_r(json_encode($response));
    return false;
}
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey!";
    print_r(json_encode($response));
    return false;
}
/*  
1.get-variants-offline.php
    accesskey:90336
    get_variants_offline:1
    variant_ids:55,56 
*/

if (!verify_token()) {
    return false;
}

if ((isset($_POST['get_variants_offline']) && $_POST['get_variants_offline'] == 1) && (isset($_POST['variant_ids'])) && !empty(trim($_POST['variant_ids']))) {
    $variant_ids = $db->escapeString($fn->xss_clean($_POST['variant_ids']));

    $res = $fn->get_cart_data("", " pv.id IN( " . $variant_ids . ")", "");
    if (!empty($res)) {
        $response['error'] = false;
        $response['message'] = "Products retrived successfully!";
        $response['total'] = count($res);
        $response['data'] = array_values($res);
    } else {
        $response['error'] = true;
        $response['message'] = "No item(s) found!";
    }

    print_r(json_encode($response));
    return false;
}
