<?php
include_once('includes/functions.php');
$function = new functions;
include_once('includes/custom-functions.php');
$fn = new custom_functions;

?>
<?php
if (isset($_POST['btnAdd'])) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if ($permissions['categories']['create'] == 1) {
        // advt1 image
        $target_path = './upload/product-advt/';
        $advt3_filename = $advt1_filename = $advt2_filename = "";
        $error = array();
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        if ($_FILES['advt1']['error'] == 0 && $_FILES['advt1']['size'] > 0) {

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
        }
        // advt2
        if ($_FILES['advt2']['error'] == 0 && $_FILES['advt2']['size'] > 0) {
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
                $error['advt2'] = " <span class='label label-danger'>Invalid directory to load Advertisement image-2 !</span>";
            }
        }

        // advt3
        if ($_FILES['advt3']['error'] == 0 && $_FILES['advt3']['size'] > 0) {
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
        }
        if (!empty($advt1_filename) && !empty($advt2_filename) && !empty($advt3_filename) && empty($error)) {


            $sql_query = "INSERT INTO product_ads (ad1,ad2,ad3)VALUES('$advt1_filename', '$advt2_filename', '$advt3_filename')";
            $db->sql($sql_query);
            $result = $db->getResult();
            if (!empty($result)) {
                $result = 0;
            } else {
                $result = 1;
            }
            if ($result == 1) {
                $error['add_advt'] = " <section class='content-header'><span class='label label-success'>Product Advertisement Added Successfully</span></section>";
            } else {
                $error['add_advt'] = " <span class='label label-danger'>Failed add Product Advertisement</span>";
            }
        }
    } else {
        $error['check_permission'] = " <section class='content-header'><span class='label label-danger'>You have no permission to create Product Advertisement</span></section>";
    }
}
?>
<section class="content-header">
    <h1>Add Product Advertisement <small><a href='products-advt.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Product Advertisement</a></small></h1>

    <?php echo isset($error['add_advt']) ? $error['add_advt'] : ''; ?>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <hr />
</section>
<section class="content">
    <div class="row">
        <div class="col-md-6">
            <?php if ($permissions['categories']['create'] == 0) { ?>
                <div class="alert alert-danger">You have no permission to create product advertisement.</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Add Product Advertisement</h3>

                </div><!-- /.box-header -->
                <!-- form start -->
                <form method="post" id="add_form" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="advt1">Image 1</label><?php echo isset($error['advt1']) ? $error['advt1'] : ''; ?>
                            <input type="file" name="advt1" id="advt1" required />
                        </div>
                        <div class="form-group">
                            <label for="advt2">Image 2</label><?php echo isset($error['advt2']) ? $error['advt2'] : ''; ?>
                            <input type="file" name="advt2" id="advt12" required />
                        </div>

                        <div class="form-group">
                            <label for="advt3">Image 3</label><?php echo isset($error['advt3']) ? $error['advt3'] : ''; ?>
                            <input type="file" name="advt3" id="advt3" required />
                        </div>
                    </div><!-- /.box-body -->

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary" name="btnAdd">Add</button>
                        <input type="reset" class="btn-warning btn" value="Clear" />

                    </div>

                </form>

            </div><!-- /.box -->
            <?php echo isset($error['check_permission']) ? $error['check_permission'] : ''; ?>
        </div>
    </div>
</section>

<div class="separator"> </div>

<?php $db->disconnect(); ?>
<script src="dist/js/jquery.validate.min.js"></script>
<script>
    $('#add_form').validate({
        rules: {
            advt1: "required",
            advt2: "required",
            advt3: "required"
        }
    });
</script>