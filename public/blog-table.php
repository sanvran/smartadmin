<?php
include_once('includes/functions.php');
?>
<section class="content-header">
    <h1>Blogs /<small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
    <ol class="breadcrumb">
        <a class="btn btn-block btn-default" href="add-blog.php"><i class="fa fa-plus-square"></i> Add New Blog</a>
    </ol>
</section>
<?php
if ($permissions['categories']['read'] == 1) {
?>
    <!-- Main content -->
    <section class="content">
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <form method="POST" id="filter_form" name="filter_form">
                            <div class="form-group col-md-3">
                            </div>
                        </form>
                    </div>
                    <div class="box-header">
                        <h3 class="box-title">Blogs</h3>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <form id="add_form" action="" method="POST" enctype="multipart/form-data">
                                <div class="box-body">
                                    <?php $sql = "Select * from blog_categories";
                                    $db->sql($sql);
                                    $result = $db->getResult();
                                    if ($result) {
                                    ?>
                                        <div class="form-group">
                                            <label class="control-label " for='category_id'>Blog Categories</label>
                                            <select name='category_id' id='category_id' class='form-control'>
                                                <option value="">Select Category</option>
                                                <?php foreach ($result as $row) { ?>
                                                    <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                            <?php }
                                            } ?>
                                            </select>
                                            <br>
                                        </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover" data-toggle="table" id="cateory_list" data-url="api-firebase/get-bootstrap-table-data.php?table=blogs" data-page-list="[5, 10, 20, 50, 100, 200]" data-show-refresh="true" data-show-columns="true" data-side-pagination="server" data-pagination="true" data-search="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-query-params="queryParams_1">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">ID</th>
                                    <th data-field="title" data-sortable="true">Title</th>
                                    <th data-field="category_name" data-sortable="true">Category Name</th>
                                    <th data-field="description" data-sortable="true">Description</th>
                                    <th data-field="image">Image</th>
                                    <th data-field="status">Status</th>
                                    <th data-field="date_created">Date Created</th>
                                    <th data-field="operate">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="separator"> </div>
        </div>
    </section>
    <script>
        $('#category_id').on('change', function() {
            id = $('#category_id').val();
            $('#cateory_list').bootstrapTable('refresh');
        });

        function queryParams_1(p) {
            return {
                "category_id": $('#category_id').val(),
                limit: p.limit,
                sort: p.sort,
                order: p.order,
                offset: p.offset,
                search: p.search
            };
        }
    </script>
<?php } else { ?>
    <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view Blogs.</div>
<?php } ?>