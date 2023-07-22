<?php
ob_start();
// start session

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

?>
<?php include "header.php"; ?>
<html>

<head>
    <title>View Ratings | <?= $settings['app_name'] ?> - Dashboard</title>
    <link href="plugins/rating/star-rating.css" media="all" rel="stylesheet" type="text/css" />
</head>
</body>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <?php include('public/view-ratings-table.php'); ?>
</div><!-- /.content-wrapper -->
</body>

</html>
<?php include "footer.php"; ?>
<script src="plugins/rating/star-rating.js" type="text/javascript"></script>
<script>
    $(document).on('load-success.bs.table', '#ratings_list', function(event) {
        $('.ratings').rating({
            theme: 'krajee-fa',
            filledStar: '<i class="fa fa-star"></i>',
            emptyStar: '<i class="fa fa-star"></i>',
            showClear: false,
            size: 'md'
        });

    });
</script>