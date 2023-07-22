<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
session_start();
require_once '../includes/crud.php';
$db_con = new Database();
$db_con->connect();
require_once '../includes/functions.php';
require_once('../includes/firebase.php');
require_once('../includes/push.php');


$fnc = new functions;

include_once('../includes/custom-functions.php');

$fn = new custom_functions;

$response = array();

$delivery_boy_id = 104;
$reward = 1000;
$delivery_boy_name = 'test';
$id = 1008907;
$message_delivery_boy = "Hello, Dear " . ucwords($delivery_boy_name) . ", your order has been delivered. order ID : #" . $id . ". Please take a note of it.";

if ($fn->send_notification_to_delivery_boy($delivery_boy_id, "Your commission " . $reward . " has been credited", "$message_delivery_boy", 'delivery_boys', $id)) {
    echo "welcome";
} else {
    echo "Hello ";
}

if ($fn->store_delivery_boy_notification($delivery_boy_id, $id, "Your commission " . $reward . " has been credited", $message_delivery_boy, 'order_reward')) {
    echo "Success";
} else {
    echo "fail";
}
