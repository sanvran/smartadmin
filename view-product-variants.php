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
include 'header.php';
?>

<head>
    <title>View Products Variants| <?= $settings['app_name'] ?> - Dashboard</title>
</head>
<?php
include_once('includes/custom-functions.php');
$fn = new custom_functions;

if (isset($_GET['id'])) {
    $ID = $db->escapeString($fn->xss_clean($_GET['id']));
} else {
    $ID = "";
}

$sql = "SELECT p.*,v.*,v.id as variant_id,(SELECT short_code FROM unit u where u.id=v.measurement_unit_id)as mesurement_unit_name,(SELECT short_code FROM unit u where u.id=v.stock_unit_id)as stock_unit_name FROM products p JOIN product_variant v ON v.product_id=p.id where p.id=" . $ID;
$db->sql($sql);
$res = $db->getResult();
?>
<?php
if ($db->numRows($result) == 0) { ?>
    <div class="content-wrapper">
        <section class="content-header">
            <h1> No Variants Available
                <small><a href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small>
            </h1>
        </section>
    </div>
<?php } else {
?>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>View Products Variants /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
        </section>
        <?php
        if ($permissions['products']['read'] == 1) { ?>

            <section class="content">
                <!-- Main row -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title">Product <?= ($res[0]['name'] != '') ?  ' : ' . $res[0]['name'] : ''; ?></h3>
                            </div>
                            <div class="box-body table-responsive">
                                <table class="table table-hover" data-toggle="table" id="product_variants_table" data-url="api-firebase/get-bootstrap-table-data.php?table=product_variants" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-filter-control="true" data-query-params="queryParams" data-sort-order="desc">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true">ID</th>
                                            <th data-field="name">Name</th>
                                            <th data-field="image">Image</th>
                                            <th data-field="measurement">Measurement</th>
                                            <th data-field="stock">Stock</th>
                                            <th data-field="price" data-sortable="true">Price(<?= $settings['currency'] ?>)</th>
                                            <th data-field="discounted_price" data-sortable="true">Discounted Price(<?= $settings['currency'] ?>)</th>
                                            <th data-field="indicator">Indicator</th>
                                            <th data-field="manufacturer">Manufacturer</th>
                                            <th data-field="made_in">Made In</th>
                                            <th data-field="description">Description</th>
                                            <th data-field="return_status">Return</th>
                                            <th data-field="cancelable_status">Cancellation</th>
                                            <th data-field="till_status">Till Status</th>
                                            <th data-field="serve_for">Availability</th>
                                            <th data-field="status">Status</th>
                                            <th data-field="operate" data-events="actionEvents">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php } else { ?>
            <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view product variant.</div>
            <a href='products.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to products</a>
        <?php } ?>
        <!-- /.content -->
    </div>
<?php } ?>
<script>
    function queryParams(p) {
        return {
            "id": <?= $_GET['id'] ?>,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }
</script>
<?php $db->disconnect(); ?>
<?php include 'footer.php'; ?>