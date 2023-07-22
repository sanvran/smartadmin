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
    <h1>Transaction /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
</section>
<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <div class="col-xs-12">

            <?php if ($permissions['settings']['read'] == 1) { ?>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Transaction</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="transaction_table" data-url="api-firebase/get-bootstrap-table-data.php?table=transaction" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-visible="false" data-query-params="queryParams_1">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">Transaction ID</th>
                                    <th data-field="name" data-sortable="true">User Name</th>
                                    <th data-field="order_id" data-sortable="true">Order ID</th>
                                    <th data-field="type" data-sortable="true">Type</th>
                                    <th data-field="txn_id" data-sortable="true">TXN ID</th>
                                    <th data-field="amount" data-sortable="true">Amount</th>
                                    <th data-field="status" data-sortable="true">Status</th>
                                    <th data-field="message" data-sortable="true">Message</th>
                                    <th data-field="transaction_date" data-sortable="true">Transaction Date</th>
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
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }
</script>