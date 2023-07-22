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
<!-- Main content -->
<section class="content-header">
    <h1>View Product Ratings & Reviews <small><a href='products.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h1>
    </hr>
</section>
</hr>
<section class="content">
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <div class="col-xs-12">
            <?php if ($permissions['settings']['read'] == 1) {
                $id = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";
            ?>
                <div class="box">
                    <table class="table table-hover" data-toggle="table" id="ratings_list" data-url="api-firebase/get-bootstrap-table-data.php?table=ratings" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-columns="true" data-show-refresh="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-query-params="queryParams_1" data-show-footer="true" data-footer-style="footerStyle">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="product_id" data-sortable="true">Product Id</th>
                                <th data-field="user_id" data-sortable="true">User Id</th>
                                <th data-field="rate" data-sortable="true">Rate</th>
                                <th data-field="review" data-sortable="true">Review</th>
                                <th data-field="images" data-sortable="true">Image</th>
                                <th data-field="status" data-sortable="true">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
        </div>
    <?php } else { ?>
        <div class="alert alert-danger">You have no permission to view settings.</div>
    <?php } ?>
    </div>
    <div class="separator"> </div>
    </div>
</section>
<script>
    function queryParams_1(p) {
        return {
            "product_id": <?= $id ?>,
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }
</script>