<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('../includes/crud.php');
$db = new Database();
$db->connect();
include_once('../includes/variables.php');
include_once('verify-token.php');
include_once('../includes/custom-functions.php');
$fn = new custom_functions;

/*  
1.get-product-advt.php
    accesskey:90336
    adv_id:1        // {optional}
    offset:0        // {optional}
    limit:10        // {optional}
*/

if (!verify_token()) {
    return false;
}

if (!isset($_POST['accesskey'])  || trim($_POST['accesskey']) != $access_key) {
    $response['error'] = true;
    $response['message'] = "No Accsess key found!";
    print_r(json_encode($response));
    return false;
}

$where = "";
if (isset($_POST['adv_id']) && !empty($_POST['adv_id'])) {
    $id = $db->escapeString(trim($_POST['adv_id']));
    $where .= "WHERE id = $id";
}

$offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($_POST['offset'])) : 0;
$limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($_POST['limit'])) : 10;

$path = 'upload/product-advt/';

$sql1 = "SELECT count(id) as total FROM `product_ads` $where";
$db->sql($sql1);
$res1 = $db->getResult();
$total = $res1[0]['total'];
$sql = "SELECT * FROM `product_ads` $where ORDER BY `id` ASC LIMIT $offset,$limit";
$db->sql($sql);
$res = $db->getResult();
if (!empty($res)) {
    foreach ($res as $row) {
        $tempRow['id'] = $row['id'];
        $tempRow['ad1'] = (!empty($row['ad1'])) ? DOMAIN_URL . $path . $row['ad1'] : '';
        $tempRow['ad2'] = (!empty($row['ad2'])) ? DOMAIN_URL . $path . $row['ad2'] : '';
        $tempRow['ad3'] = (!empty($row['ad3'])) ? DOMAIN_URL . $path . $row['ad3'] : '';

        $rows[] = $tempRow;
    }
    $response['error'] = false;
    $response['message'] = 'Product Advertisement Retrived Successfully!';
    $response['total'] = $total;
    $response['data'] = $rows;
} else {
    $response['error'] = true;
    $response['message'] = 'Data not Found!';
}
print_r(json_encode($response));
