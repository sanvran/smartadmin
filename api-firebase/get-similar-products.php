<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
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
}

/*  
1.get-similar-products.php
    accesskey:90336
    get_similar_products:1
    product_id:211
    category_id:28
    user_id:369      // {optional}
    limit:6          // {optional}
*/

if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey'])) {
    if ((isset($_POST['get_similar_products']) && $_POST['get_similar_products'] == 1)) {

        if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
            $response['error'] = true;
            $response['message'] = "Product id is required.";
            print_r(json_encode($response));
            return false;
        }

        $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
        $product_id = $db->escapeString($fn->xss_clean($_POST['product_id']));
        $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
        $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 6;
        $offset = 0;
        $order =  "RAND()";

        $sql = "SELECT id,category_id,subcategory_id FROM products where id  =" . $product_id;
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $category_id = $res[0]['category_id'];
            $subcategory_id =  $res[0]['subcategory_id'];
            if (!empty($subcategory_id) && $subcategory_id != 0) {
                $product = $fn->get_products($user_id, $product_id, '', '', $subcategory_id, "p.status = 1", $limit, $offset, $order, '', '', '', 0);
            } else {
                $product = $fn->get_products($user_id, $product_id, '', $category_id, '', "p.status = 1", $limit, $offset, $order, '', '', '', 0);
            }
            print_r(json_encode($product));
            return false;
        } else {
            $response['error'] = true;
            $response['message'] = "Products not found.";
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
