<?php
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

include "header.php";
include_once('includes/functions.php');
$allowed = ALLOW_MODIFICATION; ?>
<html>

<head>
    <title>Ticket Types | <?= $settings['app_name'] ?> - Dashboard</title>
    <script src="dist/js/jquery.min.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Ticket Types /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
            <ol class="breadcrumb">
                <button type="submit" class="btn btn-xl btn-primary" id="add_ticket_types" data-toggle='modal' data-backdrop='static' data-keyboard='false' data-target='#addTicketTypes'><i class="fa fa-plus-square"></i> Add Ticket Types</button>
            </ol>
            <br>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <?php if ($permissions['home_sliders']['read'] == 1) { ?>
                            <div class="box box-info">
                                <div class="box-header">
                                    <h3 class="box-title">Manage Ticket Types</h3>
                                </div>

                                <div class="box-body table-responsive">
                                    <table id="ticket_types_table" class="table table-hover" data-toggle="table" data-url="api-firebase/get-bootstrap-table-data.php?table=ticket_types" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc">
                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable="true">ID</th>
                                                <th data-field="type">Title</th>
                                                <th data-field="operate" data-events="actionEvents">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class=" alert alert-danger">You have no permission to view Ticket Types.
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id='addTicketTypes' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Add Ticket Types</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <form id="add_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                            <input type='hidden' name="add_ticket_types" id="add_ticket_types" value='1' />
                            <div class="form-group">
                                <label for="add_title">Title</label><i class="text-danger asterik">*</i>
                                <input type="text" id="add_title" name="add_title" class="form-control col-md-7 col-xs-12">
                            </div>
                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-md-offset-9">
                                    <button type="submit" id="add_btn" class="btn btn-primary">Add</button>
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-8" style="display:none;" id="result"></div>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id='editTicketTypes' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Edit Ticket Types</h4>
                </div>
                <div class="modal-body">
                    <div class="box-body">
                        <form id="edit_form" method="POST" action="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                            <input type='hidden' name="edit_ticket_types" id="edit_ticket_types" value='1' />
                            <input type='hidden' name="id" id="id" value="" />
                            <div class="form-group">
                                <label for="edit_title">Title</label><i class="text-danger asterik">*</i>
                                <input type="text" id="edit_title" name="edit_title" class="form-control col-md-7 col-xs-12" value="">
                            </div>
                            <div class="form-group">
                                <div class="col-md-6 col-sm-5 col-md-offset-8">
                                    <button type="submit" id="edit_btn" class="btn btn-primary">Update</button>
                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-8" style="display:none;" id="edit_result"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script src="plugins/jQuery/jquery.validate.min.js"></script>
<script>
    // add ticket types validation
    $('#add_form').validate({
        rules: {
            add_title: "required",
        }
    });

    // edit ticket types validation
    $('#edit_form').validate({
        rules: {
            edit_title: "required",
        }
    });
</script>
<script>
    $('#add_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#add_form").validate().form()) {
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    $('#result').html(result);
                    $('#result').show().delay(6000).fadeOut();
                    $('#add_form')[0].reset();
                    setTimeout(function() {
                        $('#addTicketTypes').modal('hide');
                    }, 1000);
                    window.location.reload();
                }
            });
        }
    });
</script>
<script>
    $('#edit_form').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        if ($("#edit_form").validate().form()) {
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(result) {
                    $('#edit_result').html(result);
                    $('#edit_result').show().delay(6000).fadeOut();
                    $('#edit_form')[0].reset();
                    setTimeout(function() {
                        $('#editTicketTypes').modal('hide');
                        window.location.reload();
                    }, 1000);
                }
            });
        }
    });
</script>
<script>
    window.actionEvents = {
        'click .edit-ticket-types': function(e, value, row, index) {
            $('#id').val(row.id);
            $('#edit_title').val(row.type);
        }
    }
</script>
<script>
    $(document).on('click', '.delete-ticket-types', function() {
        if (confirm('Are you sure? Want to delete Ticket Types.')) {
            id = $(this).data("id");
            $.ajax({
                url: 'public/db-operation.php',
                type: "get",
                data: 'id=' + id + '&delete_ticket_types=1',
                success: function(result) {
                    if (result == 0) {
                        $('#ticket_types_table').bootstrapTable('refresh');
                    }
                    if (result == 2) {
                        alert('You have no permission to delete Ticket Types');
                    }
                    if (result == 1) {
                        alert('Error! Ticket Types could not be deleted.');
                    }
                }
            });
        }
    });
</script>

</html>
<?php include "footer.php"; ?>