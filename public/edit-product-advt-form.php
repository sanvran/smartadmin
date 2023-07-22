<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
?>
<?php
$ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";
$category_data = array();

$sql_query = "SELECT ad1,ad2,ad3 FROM product_ads WHERE id =" . $ID;
$db->sql($sql_query);
$res = $db->getResult();
if (isset($_POST['btnEdit'])) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if ($permissions['categories']['update'] == 1) {
        $error = array();

        // advt1 image
        $target_path = './upload/product-advt/';
        $advt3_filename = $advt1_filename = $advt2_filename =  "";
        $success1 = $success2 = $success3 = 0;
        $error = array();
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        if (isset($_FILES['advt1']) && $_FILES['advt1']['error'] == 0 && $_FILES['advt1']['size'] > 0) {

            unlink('upload/product-advt/' . $res[0]['ad1']);

            $allowedExts = array("gif", "jpeg", "jpg", "png");

            error_reporting(E_ERROR | E_PARSE);
            $extension = end(explode(".", $_FILES["advt1"]["name"]));

            $result = $fn->validate_image($_FILES["advt1"]);
            if ($result) {
                $error['advt1'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            }

            $advt1_filename = microtime(true) . '.' . strtolower($extension);
            $advt1_full_path = $target_path . "" . $advt1_filename;
            if (!move_uploaded_file($_FILES["advt1"]["tmp_name"], $advt1_full_path)) {
                $error['advt1'] = " <span class='label label-danger'>Invalid directory to load Advertisement image-1 !</span>";
            }
            if (empty($error)) {
                $sql = "UPDATE product_ads SET ad1='$advt1_filename' WHERE id=" . $ID;
                if ($db->sql($sql)) {
                    $success1 = 1;
                }
            }
        }

        if (isset($_FILES['advt2']) && $_FILES['advt2']['error'] == 0 && $_FILES['advt2']['size'] > 0) {

            unlink('upload/product-advt/' . $res[0]['ad2']);

            $allowedExts = array("gif", "jpeg", "jpg", "png");

            error_reporting(E_ERROR | E_PARSE);
            $extension = end(explode(".", $_FILES["advt2"]["name"]));

            $result = $fn->validate_image($_FILES["advt2"]);
            if ($result) {
                $error['advt2'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            }
            $advt2_filename = microtime(true) . '.' . strtolower($extension);
            $advt2_full_path = $target_path . "" . $advt2_filename;
            if (!move_uploaded_file($_FILES["advt2"]["tmp_name"], $advt2_full_path)) {
                $error['advt2'] = " <span class='label label-danger'>Invalid directory to load Advertisement image-1 !</span>";
            }
            if (empty($error)) {
                $sql = "UPDATE product_ads SET ad2='$advt2_filename' WHERE id=" . $ID;
                if ($db->sql($sql)) {
                    $success2 = 1;
                }
            }
        }

        if (isset($_FILES['advt3']) && $_FILES['advt3']['error'] == 0 && $_FILES['advt3']['size'] > 0) {

            unlink('upload/product-advt/' . $res[0]['ad3']);

            $allowedExts = array("gif", "jpeg", "jpg", "png");

            error_reporting(E_ERROR | E_PARSE);
            $extension = end(explode(".", $_FILES["advt3"]["name"]));

            $result = $fn->validate_image($_FILES["advt3"]);
            if ($result) {
                $error['advt3'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            }
            $advt3_filename = microtime(true) . '.' . strtolower($extension);
            $advt3_full_path = $target_path . "" . $advt3_filename;
            if (!move_uploaded_file($_FILES["advt3"]["tmp_name"], $advt3_full_path)) {
                $error['advt3'] = " <span class='label label-danger'>Invalid directory to load Advertisement image-3 !</span>";
            }
            if (empty($error)) {
                $sql = "UPDATE product_ads SET ad3='$advt3_filename' WHERE id=" . $ID;
                if ($db->sql($sql)) {
                    $success3 = 1;
                }
            }
        }

        if (!empty($success1) || !empty($success2) || !empty($success3)) {
            $update_result = 1;
        } else {
            $update_result = 0;
        }

        // check update result
        if ($update_result == 1) {
            $error['update_category'] = " <section class='content-header'><span class='label label-success'>Product Advertisement updated Successfully</span></section>";
        } else {
            $error['update_category'] = " <span class='label label-danger'>Failed update Product Advertisement</span>";
        }
    } else {
        $error['check_permission'] = " <section class='content-header'><span class='label label-danger'>You have no permission to update Product Advertisement</span></section>";
    }
}

// create array variable to store previous data
$data = array();

$sql_query = "SELECT ad1,ad2,ad3 FROM product_ads WHERE id =" . $ID;
$db->sql($sql_query);
$res = $db->getResult();

if (isset($_POST['btnCancel'])) { ?>
    <script>
        window.location.href = "products-advt.php";
    </script>
<?php } ?>
<section class="content-header">
    <h1>
        Edit Product Advertisement<small><a href='products-advt.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Product Advertisement</a></small></h1>
    <small><?php echo isset($error['update_category']) ? $error['update_category'] : ''; ?></small>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
</section>
<section class="content">
    <!-- Main row -->

    <div class="row">
        <div class="col-md-6">
            <?php if ($permissions['categories']['update'] == 0) { ?>
                <div class="alert alert-danger topmargin-sm">You have no permission to update Product Advertisement.</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Product Advertisement</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form id="edit_category_form" method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="advt1">Image 1&nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['advt1']) ? $error['advt1'] : ''; ?>
                            <input type="file" name="advt1" id="advt1" title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." />
                            <p class="help-block"><img src="<?php echo DOMAIN_URL . 'upload/product-advt/' . $res[0]['ad1']; ?>" width="280" height="190" /></p>
                        </div>
                        <div class="form-group">
                            <label for="advt2">Image 2&nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['advt2']) ? $error['advt2'] : ''; ?>
                            <input type="file" name="advt2" id="advt2" title="Please choose square image of larger than 350px*350px & smaller than 550px*550px.">
                            <p class="help-block"><img src="<?php echo DOMAIN_URL . 'upload/product-advt/' . $res[0]['ad2']; ?>" width="280" height="190" /></p>
                        </div>
                        <div class="form-group">
                            <label for="advt3">Image 3&nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['advt3']) ? $error['advt3'] : ''; ?>
                            <input type="file" name="advt3" id="advt3" title="Please choose square image of larger than 350px*350px & smaller than 550px*550px.">
                            <p class="help-block"><img src="<?php echo DOMAIN_URL . 'upload/product-advt/' . $res[0]['ad3']; ?>" width="280" height="190" /></p>
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