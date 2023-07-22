<?php
include('includes/crud.php');
$db = new Database();
$db->connect();
include_once('includes/custom-functions.php');
$fn = new custom_functions;
include_once('includes/functions.php');
$function = new functions;

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

$sql = "SELECT * FROM `flash_sales_products`";
$db->sql($sql);
$res = $db->getResult();
foreach ($res as $row) {
    $dateTime = new DateTime($row['end_date']);
    $end_date = $row['end_date'];
    $date = $dateTime->format('Y-m-d');
    $time = $dateTime->format('H');

    $current_date = date('Y-m-d');
    if (($current_date == $date) && $time == date('H')) {
        $sql = "DELETE from  flash_sales_products where end_date='$end_date'";
        if ($db->sql($sql)) {
            echo "Sale Deleted Successfully";
        } else {
            echo "Sale Not Deleted";
        }
    } else {
        echo "No Data Found";
    }
}
