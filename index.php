<?php session_start();
ob_start();
include_once('includes/crud.php');
$db = new Database;
include_once('includes/custom-functions.php');
$fn = new custom_functions();
$db->connect();
date_default_timezone_set('Asia/Kolkata');
$sql = "SELECT * FROM settings";
$db->sql($sql);
$res = $db->getResult();
$settings = json_decode($res[5]['value'], 1);
$logo = $fn->get_settings('logo');

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/ico" href="<?= DOMAIN_URL . 'dist/img/' . $logo ?>">
    <title>Admin Login - <?= $settings['app_name'] ?></title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/font-awesome.min.css">
    <link rel="stylesheet" href="dist/css/ionicons.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="plugins/iCheck/flat/blue.css">
    <link rel="stylesheet" href="plugins/morris/morris.css">
    <link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <link rel="stylesheet" href="plugins/datepicker/datepicker3.css">
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
    <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
</head>
</body>
<?php if (empty(DOMAIN_URL)) { ?>
    <h1 style="color:red;">You have to install app on your device!</h1>
<?php
} else {
    include 'public/login-form.php';
}
// include 'public/login-form.php';

?>



</body>

</html>