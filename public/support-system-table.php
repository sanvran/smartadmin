<?php
include_once('includes/functions.php');
$function = new functions;
include_once('includes/custom-functions.php');
$fn = new custom_functions;
?>
<section class="content-header">
    <h1> Manage Ticket / <small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
</section>

<!-- Main content -->
<section class="content">
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title">Manage Ticket System</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="ticket_table" data-url="api-firebase/get-bootstrap-table-data.php?table=ticket" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-trim-on-search="false" data-show-refresh="true" data-show-columns="true" data-sort-name="id" data-sort-order="asc" data-mobile-responsive="true">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="ticket_type_id" data-sortable="true">Ticket Type ID</th>
                                <th data-field="user_id" data-sortable="true">User ID</th>
                                <th data-field="ticket_type" data-sortable="true">Ticket Type</th>
                                <th data-field="username" data-sortable="true">Username</th>
                                <th data-field="title" data-sortable="true">Title</th>
                                <th data-field="message" data-sortable="true">Message</th>
                                <th data-field="email" data-sortable="true">Email</th>
                                <th data-field="status" data-sortable="true">Status</th>
                                <th data-field="created" data-sortable="true">Date Created</th>
                                <th data-field="operate" data-events="actionEvents">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <div class="separator"> </div>
    </div>
    <!-- /.row (main row) -->

    <div class="modal fade" id='editTicketModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Update Ticket Status</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <form method="POST" id="filter_form" name="filter_form">
                            <input type="hidden" name="ticket_id" id="ticket_id" value="">
                            <input type="hidden" name="change_ticket_status" id="change_ticket_status" value="1">
                            <div class="form-group">
                                <select id="change_status" name="change_status" placeholder="Select Status" required class="form-control ticket_status">
                                    <!-- <option value="">Change Ticket Status</option> -->
                                    <option value='pending'>Pending</option>
                                    <option value='opened'>Opened</option>
                                    <option value='resolved'>Resolved</option>
                                    <option value='closed'>Closed</option>
                                    <option value='reopen'>Reopen</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div id="result"></div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    $('#filter_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if (confirm('Are you sure want to change status?')) {
            $.ajax({
                url: "public/db-operation.php",
                data: formData,
                method: "POST",
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    $('#result').html(result);
                    $('#result').show().delay(2000).fadeOut();
                    $('#ticket_table').bootstrapTable('refresh');
                    setTimeout(function() {
                        $('#editTicketModal').modal('hide');
                    }, 1000);
                }
            });
        }
    });

    window.actionEvents = {
        'click .edit-ticket': function(e, value, row, index) {
            $('#ticket_id').val(row.id);
            $('#change_status').val(row.active_status);

        }
    }
</script>