<?php
/*login*/
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
include '../includes/crud.php';
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
include_once('../includes/variables.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
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
1.get-user-data.php
    accesskey:90336
    get_user_data:1
    user_id:1748
*/

$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
if (!isset($_POST['accesskey']) || $access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}
if (!verify_token()) {
    return false;
}

if (isset($_POST['get_user_data']) && $_POST['get_user_data'] != '') {
    if (isset($_POST['user_id']) && $_POST['user_id'] != '') {
        $id    = $db->escapeString($fn->xss_clean($_POST['user_id']));
        $response = array();
        $sql_query = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u WHERE u.id=" . $id;
        $db->sql($sql_query);
        $result = $db->getResult();
        if ($db->numRows($result) > 0) {
            foreach ($result as $row) {
                $response['error'] = false;
                $response['message'] = 'User Retrived Successfully';
                $response['user_id'] = $_SESSION['user_id']    = $row['id'];
                $response['name'] = $row['name'];
                $response['email'] = $row['email'];
                $response['mobile'] = $row['mobile'];
                $response['profile'] = !empty($row['profile']) ?  DOMAIN_URL . 'upload/profile/' . $row['profile'] : '';
                $response['dob'] = $row['dob'];
                $response['balance'] = $row['balance'];
                $response['city_id'] = !empty($row['city']) ? $row['city'] : '';
                $response['city_name'] = !empty($row['city_name']) ? $row['city_name'] : '';
                $response['area_id'] = !empty($row['area']) ? $row['area'] : '';
                $response['area_name'] = !empty($row['area_name']) ? $row['area_name'] : '';
                $response['street'] = $row['street'];
                $response['pincode'] = $row['pincode'];
                $response['referral_code'] = $row['referral_code'];
                $response['friends_code'] = $row['friends_code'];
                $response['apikey']     = $row['apikey'];
                $response['status']     = $row['status'];
                $response['created_at']     = $row['created_at'];
            }
        } else {
            $response['error']     = true;
            $response['message']   = "data not exists!";
        }
    } else {
        $response['error']     = true;
        $response['message']   = "user id required";
    }
    print_r(json_encode($response));
}
$db->disconnect();
