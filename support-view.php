<?php
session_start();

// set time for session timeout
$currentTime = time() + 25200;
$expired = 3600;

// if session not set go to login page
if (!isset($_SESSION['user'])) {
    header("location:index.php");
}

// if current time is more than session timeout back to login page
if ($currentTime > $_SESSION['timeout']) {
    session_destroy();
    header("location:index.php");
}

// destroy previous session timeout and create new one
unset($_SESSION['timeout']);
$_SESSION['timeout'] = $currentTime + $expired;

include_once('includes/custom-functions.php');
include_once('includes/crud.php');
include_once('includes/functions.php');
include_once('includes/variables.php');

$function = new functions;
$fn = new custom_functions;
$db = new Database();
$db->connect();

$id = (isset($_GET['id']) && !empty($fn->xss_clean($_GET['id'])) && is_numeric($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";
if (empty($id)) {
    header("location:support-system.php");
    exit();
}

// MySQL query that selects complaint by the ID 
$sql = "SELECT t.*,u.name FROM tickets t left join users u on u.id = t.user_id WHERE t.id = $id ORDER BY `created` ASC ";
$db->sql($sql);
$ticket = $db->getResult();
if (empty($ticket)) {
    header("location:support-system.php");
    exit();
}
$id1 = $ticket[0]['id'];
$status = $ticket[0]['status'];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php include "header.php"; ?>
<html>

<head>
    <title>View Complaints Support | <?= $settings['app_name'] ?> - Dashboard</title>
    <style type="text/css">
        .box.box-primary {
            border-top-color: #0078db;
        }

        .box.box-solid.box-primary>.box-header {
            color: #fff;
            background: #0078db;
            background-color: #0078db;
        }

        .box .box-primary .direct-chat .direct-chat-primary {
            min-height: 400px;
        }

        .direct-chat-messages {
            min-height: 400px;
        }

        .direct-chat-text {
            width: fit-content;
            border-radius: 5px;
            position: relative;
            background: #d2d6de;
            border: 1px solid #d2d6de;
            color: #444;
            float: right;
            margin-right: 10px;
        }

        .message1 {
            padding: 9px;
            color: #f9f5f5f5;
            margin-right: 1px;
            background-color: #767c81;
            line-height: 20px;
            max-width: 92%;
            display: inline-block;
            float: right;
            text-align: left;
            border-radius: 5px;
            clear: both;
        }

        .message {
            padding: 10px;
            margin-left: 1px;
            background-color: #2484d3;
            line-height: 20px;
            max-width: 90%;
            display: inline-block;
            text-align: left;
            border-radius: 5px;
            clear: both;
            color: white;
            font-size: 14px;
        }

        .right .direct-chat-text {
            margin-right: 10;
            display: table;
        }

        .user {
            color: #0f1010;
            font-size: 16px;
            margin-left: 7px;
        }

        .right .user {
            float: right;
        }

        #loader {
            text-align: center;
            height: 50px;
        }

        #loader img {
            max-height: 100%;
        }

        .created .right {
            color: black;
            float: right;
            font-size: 10px;
            clear: both;
        }

        .admin_created {
            float: right;
            margin: 1px;
        }

        .chat-body {
            height: 400px;
        }

        .chat-btn {
            border-radius: 3px;
            border-color: #014c8d;;
            height: 40px;
            border-width: 2px;
        }

        :focus-visible {
            outline: none !important;
        }

        .chat {
            padding: 0px 0px 0px 0px;
        }

        .ticket_status {
            width: 250px;
            border-color: #0078db;
            border-radius: 5px;
            border-width: 2px;
        }

        .ticket_status:focus {
            border-color: #0078db;
        }
    </style>
</head>
</body>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <?php include('public/support-view-data.php'); ?>
</div><!-- /.content-wrapper -->
</body>

</html>
<?php include "footer.php"; ?>