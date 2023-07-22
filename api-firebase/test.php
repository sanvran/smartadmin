<?php
include_once('../includes/custom-functions.php');
$fn = new custom_functions;

$res = $fn->send_notification_to_user('ticket_title', 'message', 'customer_notification', 1782, '');

print_r(json_encode($res));
