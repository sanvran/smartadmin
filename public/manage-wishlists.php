<?php
include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");

include('includes/variables.php');
include_once('includes/custom-functions.php');

$fn = new custom_functions;
$config = $fn->get_configurations();
?>

<script src="plugins/jQuery/jquery.validate.min.js"></script>
<section class="content-header">
    <h1>Wishlists /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Wishlists </h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="wishlists_id" data-url="api-firebase/get-bootstrap-table-data.php?table=wishlists" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="total_qty" data-sort-order="desc" data-query-params="queryParams_1">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="user_id" data-sortable="true">User_id</th>
                                <th data-field="user_name" data-sortable="true">User_name</th>
                                <th data-field="product_id" data-sortable="true">Product ID</th>
                                <th data-field="product_name" data-sortable="true">Product</th>
                                <th data-field="total_qty" data-sortable="true">Total QTY</th>
                                <th data-field="operate">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function queryParams_1(p) {
        return {
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }
</script>