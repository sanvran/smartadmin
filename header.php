<?php include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");

include('includes/variables.php');
include_once('includes/custom-functions.php');
include_once('includes/functions.php');
$fn = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);
$config = $fn->get_configurations();
$time_zone = $fn->set_timezone($config);
if (!$time_zone) {
    echo "Time Zone is not set.";
    return false;
    exit();
}

$settings['app_name'] = $config['app_name'];
$words = explode(" ", $settings['app_name']);
$acronym = "";
foreach ($words as $w) {
    $acronym .= $w[0];
}

$currency = $fn->get_settings('currency');
$settings['currency'] = $currency;
$isAuth = $fn->get_settings('doctor_brown');
$cal_time_check = $time_check = '';
if (!empty($isAuth)) {
    $isAuth = json_decode($isAuth);
    $time_check = $isAuth->time_check;
    $str = trim($isAuth->code_bravo) . "|" . trim($isAuth->code_adam) . "|" . trim($isAuth->dr_firestone) . "|" . DOMAIN_URL;
    $cal_time_check = hash('sha256', $str);
}

$role = $fn->get_role($_SESSION['id']);
$sql_logo = "select value from `settings` where variable='Logo' OR variable='logo'";
$db->sql($sql_logo);
$res_logo = $db->getResult();
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/ico" href="<?= 'dist/img/' . $res_logo[0]['value'] ?>">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="dist/css/ionicons.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link href="dist/css/multiple-select.css" rel="stylesheet" />
    <link rel="stylesheet" href="dist/css/print.css" type="text/css" media="print">
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="plugins/iCheck/flat/blue.css">
    <script src="plugins/jQuery/jquery-3.3.1.min.js"></script>
    <script src="plugins/jQueryUI/jquery-ui.js"></script>
    <script src="dist/js/v5.tinymce.min.js"></script>
    <link rel="stylesheet" href="plugins/switchery/switchery.min.css">
    <script src="plugins/switchery/switchery.min.js"></script>
    <link href="plugins/datetimepicker/bootstrap-datetimepicker.css" rel="stylesheet">
    <link rel="stylesheet" href="plugins/dropzone/dropzone.css">
    <link rel="stylesheet" href="plugins/morris/morris.css">
    <link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <link rel="stylesheet" href="plugins/datepicker/datepicker3.css">
    <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">
    <link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <link rel="stylesheet" href="plugins/izitoast/iziToast.css">
    <link rel="stylesheet" href="plugins/izitoast/iziToast.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap-table.css" />
    <link rel="stylesheet" href="bootstrap/css/bootstrap-table-filter-control.css" />
    <link rel="stylesheet" href="dist/css/jquery.fancybox.min.css" />

    <script src="dist/js/jquery.fancybox.min.js"></script>
    <link href="plugins/select2/select2.min.css" rel="stylesheet" />
    <script src="plugins/select2/select2-4.0.6.min.js"></script>
    <link rel="stylesheet" href="dist/css/lightbox.min.css">
    <script src="dist/js/lightbox.min.js"></script>
    <script src="dist/js/tinymce.min.js"></script>
    <script>
        $(document).ready(function() {
            var date = new Date();
            var currentMonth = date.getMonth() - 10;
            var currentDate = date.getDate();
            var currentYear = date.getFullYear() - 10;

            $('.datepicker').datepicker({
                minDate: new Date(currentYear, currentMonth, currentDate),
                dateFormat: 'yy-mm-dd',
            });
        });
    </script>
    <script language="javascript">
        function printpage() {
            window.print();
        }
    </script>

</head>

<body class="hold-transition skin-blue fixed sidebar-mini">
    <div class="wrapper">

        <!-- verify purchase code -->

        <header class="main-header">
            <a href="home.php" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini">
                    <h2><?= $acronym ?></h2>
                </span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg">
                    <h3><?= $settings['app_name'] ?></h3>
                </span>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <!-- User Account: style can be found in dropdown.less -->
                        <?php
                        $sql_query = "SELECT * FROM admin where id=" . $_SESSION['id'];

                        $db->sql($sql_query);
                        $result = $db->getResult();
                        foreach ($result as $row) {
                            $user = !empty($row['username']) ? $row['username'] : '';
                            $email = !empty($row['email']) ? $row['email'] : '';
                        ?>
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="images/avatar.png" class="user-image" alt="User Image">
                                    <span class="hidden-xs"><?= $user; ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <img src="images/avatar.png" class="img-circle" alt="User Image">
                                        <p>
                                            <?= $user; ?>
                                            <small><?= $email; ?></small>
                                        </p>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="admin-profile.php" class="btn btn-default btn-flat"> Edit Profile</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="logout.php" class="btn btn-default btn-flat">Log out</a>
                                        </div>
                                    </li>
                                    <!-- Menu Body -->
                                    <!-- Menu Footer-->
                                </ul>
                            </li>
                    </ul>
                </div>
            </nav>
        </header>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar" id="style-6">
            <?php } ?>

            <ul class="sidebar-menu">
                <li class="treeview">
                    <a href="home.php">
                        <i class="fa fa-home" class="active"></i> <span>Home</span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="orders.php">
                        <i class="fa fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-bullseye"></i>
                        <span>Categories</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="categories.php"><i class="fa fa-sliders"></i> Manage Categories</a></li>
                        <li><a href="categories-order.php"><i class="fa fa-reorder"></i> Categories Order</a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-bullseye"></i>
                        <span>Sub Categories</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="subcategories.php"><i class="fa fa-sliders"></i> Manage Sub Categories</a></li>
                        <li><a href="subcategories-order.php"><i class="fa fa-reorder"></i> Sub Categories Order</a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-cubes"></i>
                        <span>Products</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="add-product.php"><i class="fa fa-plus"></i> Add Product</a></li>
                        <li><a href="products.php"><i class="fa fa-sliders"></i> Manage Products</a></li>
                        <li><a href="bulk-upload.php"><i class="fa fa-upload"></i> Bulk Upload</a></li>
                        <li><a href="bulk-update.php"><i class="fa fa-pencil"></i> Bulk Update</a></li>
                        <li><a href="products-taxes.php"><i class="fa fa-plus"></i> Taxes</a></li>
                        <li><a href="products-order.php"><i class="fa fa-reorder"></i> Products Order</a></li>
                        <li><a href="products-advt.php"><i class="fa fa-reorder"></i> Product Advertisement</a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="glyphicon glyphicon-comment"></i>
                        <span>Support Tickets</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="ticket-type.php"><i class="fa fa-ticket"></i> Ticket Types</a></li>
                        <li><a href="support-system.php"><i class="fa fa-comments-o"></i> Tickets </a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="main-slider.php">
                        <i class="fa fa-picture-o"></i>
                        <span>Home Slider Images</span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="new-offers.php">
                        <i class="fa fa-gift"></i>
                        <span>New Offers Images</span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="media.php">
                        <i class="fa fa-file-image-o"></i>
                        <span>Media</span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="promo-code.php">
                        <i class="fa fa-gift"></i>
                        <span>Promo code</span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-rss"></i>
                        <span>Blogs</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="blog-category.php"><i class="fa fa-bullseye"></i> Categories </a></li>
                        <li><a href="blogs.php"><i class="fa fa-rss" aria-hidden="true"></i> Blogs </a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-puzzle-piece"></i>
                        <span>Featured Sections</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="sections.php"><i class="fa fa-sliders"></i> Manage Featured Sections</a></li>
                        <li><a href="sections-order.php"><i class="fa fa-reorder"></i> Featured Sections Order</a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-credit-card"></i>
                        <span>Flash Sales</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="flash-sales.php"><i class="fa fa-users"></i> Flash Sales </a></li>
                        <li><a href="flash-sales-products.php"><i class="fa fa-exchange"></i> Flash Sales Products </a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="newsletter.php">
                        <i class="fa fa-envelope"></i>
                        <span>Newsletter</span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-male"></i>
                        <span>Customers</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="customers.php"><i class="fa fa-users"></i> Customers </a></li>
                        <li><a href="wishlists.php"><i class="fa fa-heart"></i> Wishlists </a></li>
                        <li><a href="transaction.php"><i class="fa fa-exchange"></i> Transactions</a></li>
                        <li><a href="wallet-transactions.php"><i class="fa fa-exchange"></i> Wallet Transactions</a></li>
                        <li><a href="manage-customer-wallet.php"><i class="fa fa-line-chart"></i> Manage Customer Wallet</a></li>

                    </ul>
                </li>
                <li class="treeview">
                    <a href="return-requests.php">
                        <i class="fa fa-retweet"></i> <span>Return Requests</span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="withdrawal-requests.php">
                        <i class="fa fa-credit-card"></i> <span> Withdrawal Requests </span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-male"></i>
                        <span>Delivery Boys</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="delivery-boys.php"><i class="fa fa-line-chart"></i> Manage Delivery Boys </a></li>
                        <li><a href="fund-transfers.php"><i class="fa fa-inr"></i> Fund Transfers</a></li>
                        <li><a href="delivery-boy-cash.php"><i class="fa fa-money"></i> Delivery Boy Cash</a></li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="notification.php"> <i class="fa fa-share-square-o"></i><span>Send Notification</span>
                    </a>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-wrench"></i>
                        <span>Web Front-End Settings</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="web-header.php"><i class="fa fa-chevron-circle-up"></i> Header</i></a></li>
                        <li><a href="web-category.php"><i class="fa fa-picture-o"></i> Category Image </a></li>
                        <li class=""><a href="#"><i class="fa fa-chevron-circle-down"></i> Footer <i class="fa fa-angle-right pull-right"></i></a>
                            <ul class="treeview-menu">
                                <li><a href="social-media.php"><i class="fa fa-facebook"></i> Social Media </a></li>
                                <li><a href="web-footer.php"><i class="fa fa-life-ring"></i> About </a></li>
                            </ul>
                        </li>
                        <li class="treeview"><a href="front-end-policies.php"><i class="fa fa-money"></i><span>Policies</span></a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-wrench"></i>
                        <span>System</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="settings.php"><i class="fa fa-cogs"></i> Store Settings </a></li>
                        <li><a href="payment-methods-settings.php"><i class="fa fa-dollar"></i> Payment Methods </a></li>
                        <li><a href="time-slots.php"><i class="fa fa-clock-o"></i> Time slots </a></li>
                        <li><a href="notification-settings.php"><i class="fa fa-bell-o"></i> Notification Settings</a></li>
                        <li><a href="units.php"><i class="fa fa-dot-circle-o"></i> Units</a></li>
                        <li><a href="contact-us.php"><i class="fa fa-phone"></i> Contact Us </a></li>
                        <li><a href="privacy-policy.php"><i class="fa fa-user-secret"></i> Privacy Policy </a></li>
                        <li><a href="delivery-boy-privacy-policy.php"><i class="fa fa-exclamation-triangle"></i> Delivery Boy Privacy Policy </a></li>
                        <li><a href="manager-app-privacy-policy.php"><i class="fa fa-lock"></i> Manager App Privacy Policy </a></li>
                        <li><a href="update.php"><i class="fa fa-upload"></i> System Updater </a></li>
                        <li><a href="api-key.php"><i class="fa fa-lock"></i> Secret Key </a></li>
                        <li><a href="about-us.php"><i class="fa fa-info"></i> About Us </a></li>
                        <li><a href="purchase-code.php"><i class="fa fa-check"></i> System Registration </a></li>

                    </ul>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-map-marker"></i>
                        <span>Location</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="city.php"><i class="fa fa-location-arrow"></i> Cities</a></li>
                        <li><a href="areas.php"><i class="fa fa-reorder"></i> Areas </a></li>
                        <li><a href="location-bulk-upload.php"><i class="fa fa-upload"></i> Bulk Upload </a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="#">
                        <i class="fa fa-folder-open"></i>
                        <span>Reports</span>
                        <i class="fa fa-angle-right pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li><a href="product-sales-report.php"><i class="fa fa-money"></i>Product Sales Report</a></li>
                        <li><a href="sales-report.php"><i class="fa fa-money"></i>Sales Report</a></li>
                        <li><a href="invoices.php"><i class="fa fa-money"></i>Invoice Report</a></li>
                    </ul>
                </li>
                <li class="treeview">
                    <a href="faq.php">
                        <i class="fa fa-info"></i> <span>FAQs</span>
                        <?php
                        $query = "select * from faq where status=1 ";
                        $db->sql($query);
                        $result = $db->getResult();
                        $count = $db->numRows($result);
                        if ($count) { ?>
                            <span class="label label-primary pull-right"><?= $count; ?></span>
                        <?php } ?>
                    </a>
                </li>
                <?php
                if ($role == 'admin' || $role == 'super admin') {
                ?>
                    <li class="treeview">
                        <a href="system-users.php">
                            <i class="fa fa-users" class="active"></i> <span>System Users</span>
                        </a>
                    </li>
                <?php }
                $query = "SELECT version FROM updates ORDER BY id DESC LIMIT 1";
                $db->sql($query);
                $result = $db->getResult();
                if (!empty($result)) {
                ?>
                    <center><a href="home.php" class="label label-success"><?= $result[0]['version'] ?></a></center>
                <?php } ?>
            </ul>
            </section>
            <!-- /.sidebar -->
        </aside>
</body>

</html>