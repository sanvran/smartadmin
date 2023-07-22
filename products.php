<?php
session_start();
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// start session

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

?>

<?php include "header.php"; ?>
<html>

<head>
    <title>Products | <?= $settings['app_name'] ?> - Dashboard</title>
    <!-- <link href="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.0.7/css/star-rating.css" media="all" rel="stylesheet" type="text/css" /> -->
    <link href="plugins/rating/star-rating.css" media="all" rel="stylesheet" type="text/css" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
</body>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <?php include('public/products-table.php'); ?>
</div><!-- /.content-wrapper -->
</body>

</html>
<?php include "footer.php"; ?>
<!-- <script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-star-rating@4.0.7/js/star-rating.js" type="text/javascript"></script> -->
<script src="plugins/rating/star-rating.js" type="text/javascript"></script>

<script>
    $(document).on('load-success.bs.table', '#products_table', function(event) {

        $('.ratings').rating({
            theme: 'krajee-fa',
            filledStar: '<i class="fa fa-star"></i>',
            emptyStar: '<i class="fa fa-star"></i>',
            showClear: false,
            size: 'md'
        });

    });
</script>