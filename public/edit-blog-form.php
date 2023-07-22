<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
$function = new functions;
?>
<?php
$ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";
$category_data = array();

$sql_query = "SELECT image FROM blogs WHERE id =" . $ID;
$db->sql($sql_query);
$res = $db->getResult();
if (isset($_POST['btnEdit'])) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if ($permissions['categories']['update'] == 1) {
        $target_path = './upload/blogs/';
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $title = $db->escapeString($fn->xss_clean($_POST['title']));
        if (strpos($title, '-') !== false) {
            $temp = (explode("-", $title)[1]);
        } else {
            $temp = $title;
        }
        $slug = $function->slugify($temp, 'blogs');
        $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
        $description = $db->escapeString($fn->xss_clean($_POST['description']));
        $status =  (isset($_POST['status'])) ? $db->escapeString($fn->xss_clean($_POST['status'])) : 1;
        $menu_image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
        $image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
        $image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
        $error = array();

        if (empty($title)) {
            $error['title'] = " <span class='label label-danger'>Required!</span>";
        }
        if (empty($description)) {
            $error['description'] = " <span class='label label-danger'>Required!</span>";
        }

        // get image file extension
        error_reporting(E_ERROR | E_PARSE);
        $extension = end(explode(".", $_FILES["image"]["name"]));

        if (!empty($menu_image)) {
            $result = $fn->validate_image($_FILES["image"]);
            if ($result) {
                $error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            }
        }
        if (!empty($title) && !empty($description) && empty($error['image'])) {

            if (!empty($menu_image)) {

                $string = '0123456789';
                $file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
                $function = new functions;
                $image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

                // delete previous image
                $delete = unlink($res[0]['image']);

                // upload new image
                $upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/blogs/' . $image);
                $upload_image = 'upload/blogs/' . $image;
                $sql_query = "UPDATE blogs SET category_id = '" . $category_id . "' , title = '$title', slug = '$slug', description = '$description',image = '$upload_image',status = '$status' WHERE id = " . $ID;
                if ($db->sql($sql_query)) {
                    $update_result = $db->getResult();
                }
            } else {

                $sql_query = "UPDATE blogs SET category_id = '" . $category_id . "' , title = '" . $title . "',slug = '" . $slug . "' , description = '" . $description . "', image = '" . $res[0]['image'] . "' ,status = '" . $status . "' WHERE id =" . $ID;
                $db->sql($sql_query);
                $update_result = $db->getResult();
            }

            if (!empty($update_result)) {
                $update_result = 0;
            } else {
                $update_result = 1;
            }

            // check update result
            if ($update_result == 1) {
                $error['update_category'] = " <section class='content-header'><span class='label label-success'>Blog updated Successfully</span></section>";
            } else {
                $error['update_category'] = " <span class='label label-danger'>Failed update blog</span>";
            }
        } else {
            $error['title'] = " <span class='label label-danger'>Required!</span>";
            $error['description'] = " <span class='label label-danger'>Required!</span>";
        }
    } else {
        $error['check_permission'] = " <section class='content-header'><span class='label label-danger'>You have no permission to update blog</span></section>";
    }
}

// create array variable to store previous data
$data = array();

$sql_query = "SELECT *,(SELECT name from blog_categories bc WHERE bc.id = b.category_id ) as category_name FROM blogs b WHERE id=" . $ID;
$db->sql($sql_query);
$res = $db->getResult();

if (isset($_POST['btnCancel'])) { ?>
    <script>
        window.location.href = "blogs.php";
    </script>
<?php } ?>
<section class="content-header">
    <h1>
        Edit Blog<small><a href='blogs.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Blogs</a></small></h1>
    <small><?php echo isset($error['update_category']) ? $error['update_category'] : ''; ?></small>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
</section>
<section class="content">
    <!-- Main row -->

    <div class="row">
        <div class="col-md-12">
            <?php if ($permissions['categories']['update'] == 0) { ?>
                <div class="alert alert-danger topmargin-sm">You have no permission to update blog.</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Blog</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form id="edit_category_form" method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <?php $sql = "Select * from blog_categories";
                        $db->sql($sql);
                        $result = $db->getResult();
                        ?>
                        <div class="form-group">
                            <label class="control-label " for='category'>Blog Categories</label>
                            <select name='category_id' id='category_id' class='form-control'>
                                <option value="<?= $res[0]['category_id']; ?>"><?= $res[0]['category_name']; ?></option>
                                <?php
                                foreach ($result as $row) {
                                    if ($res[0]['category_id'] !== $row['id']) { ?>
                                        <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                                <?php }
                                } ?>
                            </select>
                            <br>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Blog Name</label><?php echo isset($error['title']) ? $error['title'] : ''; ?>
                            <input type="text" class="form-control" name="title" value="<?php echo $res[0]['title']; ?>" required>
                        </div></br>
                        <div class="form-group">
                            <label for="app_name">Description:</label><i class="address_note"></i><?= isset($error['description']) ? $error['description'] : ''; ?>
                            <textarea name="description" id="description" class="form-control addr_editor" required><?php echo $res[0]['description']; ?></textarea>
                        </div><br>
                        <div class="form-group">
                            <label for="exampleInputFile">Image&nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['image']) ? $error['image'] : ''; ?>
                            <input type="file" name="image" id="image" title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." value="<img src='<?php echo $data['image']; ?>'/>">
                            <p class="help-block"><img src="<?php echo $res[0]['image']; ?>" width="280" height="190" /></p>
                        </div></br>
                        <div class="form-group">
                            <label for="statsu">Status <small>[ Enable / Disable ] </small></label><br>
                            <input type="checkbox" id="status_btn" class="js-switch" <?= (isset($res[0]['status']) && !empty($res[0]['status']) && $res[0]['status'] == '1') ? 'checked' : ''; ?>>
                            <input type="hidden" id="status" name="status" value="<?= (isset($res[0]['status']) && !empty($res[0]['status'])) ? $res[0]['status'] : 0; ?>">
                        </div>
                    </div><!-- /.box-body -->

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary" name="btnEdit">Update</button>
                        <button type="submit" class="btn btn-danger" name="btnCancel">Cancel</button>
                    </div>
                </form>
            </div><!-- /.box -->
        </div>
    </div>
</section>

<div class="separator"> </div>
<?php $db->disconnect(); ?>
<script src="dist/js/jquery.validate.min.js"></script>
<script>
    $('#edit_category_form').validate({
        rules: {
            title: "required",
        }
    });
</script>
<script type="text/javascript" src="css/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    /* Midtrans button value */
    var changeCheckbox = document.querySelector('#status_btn');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#status').val(1);
        } else {
            $('#status').val(0);
        }
    };
</script>
<script type="text/javascript">
    $(document).ready(function() {
        ltr = '<svg width="20" height="20"><path d="M11 5h7a1 1 0 010 2h-1v11a1 1 0 01-2 0V7h-2v11a1 1 0 01-2 0v-6c-.5 0-1 0-1.4-.3A3.4 3.4 0 017.8 10a3.3 3.3 0 010-2.8 3.4 3.4 0 011.8-1.8L11 5zM4.4 16.2L6.2 15l-1.8-1.2a1 1 0 011.2-1.6l3 2a1 1 0 010 1.6l-3 2a1 1 0 11-1.2-1.6z" fill-rule="evenodd"></path></svg>';
        rtl = '<svg width="20" height="20"><path d="M8 5h8v2h-2v12h-2V7h-2v12H8v-7c-.5 0-1 0-1.4-.3A3.4 3.4 0 014.8 10a3.3 3.3 0 010-2.8 3.4 3.4 0 011.8-1.8L8 5zm12 11.2a1 1 0 11-1 1.6l-3-2a1 1 0 010-1.6l3-2a1 1 0 111 1.6L18.4 15l1.8 1.2z" fill-rule="evenodd"></path></svg>';
        html = '( Use ' + ltr + ' for LTR and use ' + rtl + ' for RTL )';
        $('.address_note').append(html);
    });
</script>