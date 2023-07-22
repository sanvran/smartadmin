<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
$function = new functions;
?>
<?php
$ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";

$sql_query = "SELECT * FROM slider WHERE id =" . $ID;
$db->sql($sql_query);
$row = $db->getResult();

if (isset($_POST['btnEdit'])) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }

    if ($permissions['categories']['update'] == 1) {
        $target_path = './upload/slider/';
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $type = $db->escapeString($fn->xss_clean($_POST['type']));
        $title = $db->escapeString($_POST['title']);
        $short_description = $db->escapeString($_POST['short_description']);
        $slider_url = $db->escapeString($fn->xss_clean($_POST['slider_url']));
        $id = ($type != 'default' && $type != 'slider_url') ? $db->escapeString($fn->xss_clean($_POST[$type])) : "0";

        $menu_image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
        $image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
        $image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));

        $menu_image2 = $db->escapeString($fn->xss_clean($_FILES['image2']['name']));
        $image_error2 = $db->escapeString($fn->xss_clean($_FILES['image2']['error']));
        $image_type2 = $db->escapeString($fn->xss_clean($_FILES['image2']['type']));
        $error = array();

        error_reporting(E_ERROR | E_PARSE);
        $extension = end(explode(".", $_FILES["image"]["name"]));

        if (!empty($menu_image)) {
            $result = $fn->validate_image($_FILES["image"]);
            if ($result) {
                $error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            }
        }

        $extension2 = end(explode(".", $_FILES["image2"]["name"]));

        if (!empty($menu_image2)) {
            $result = $fn->validate_image($_FILES["image2"]);
            if ($result) {
                $error['image2'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            }
        }

        if (empty($error['image'])) {

            if (!empty($menu_image)) {
                unlink('./' . $row[0]['image']);
                $string = '0123456789';
                $image = preg_replace("/\s+/", "_", $_FILES['image']['name']);


                $upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/slider/' . $image);
                $upload_image = 'upload/slider/' . $image;

                $sql_query = "UPDATE slider SET type = '" . $type . "' , type_id = '$id', title = '$title', short_description = '$short_description',image = '$upload_image',slider_url = '$slider_url' WHERE id = " . $ID;
                if ($db->sql($sql_query)) {
                    $db->sql($sql_query);
                    $update_result = $db->getResult();
                }
            }

            if (!empty($menu_image2)) {
                unlink('./' . $row[0]['image2']);
                $string = '0123456789';
                $image2 = preg_replace("/\s+/", "_", $_FILES['image2']['name']);
                $upload2 = move_uploaded_file($_FILES['image2']['tmp_name'], 'upload/slider/' . $image2);
                $upload_image2 = 'upload/slider/' . $image2;

                $sql_query = "UPDATE slider SET type = '" . $type . "' , type_id = '$id', title = '$title', short_description = '$short_description',image2 = '$upload_image2',slider_url = '$slider_url' WHERE id = " . $ID;
                if ($db->sql($sql_query)) {
                    $db->sql($sql_query);
                    $update_result = $db->getResult();
                }
            } else {
                $sql_query = "UPDATE slider SET type = '" . $type . "' , type_id = '" . $id . "',title = '" . $title . "' , short_description = '" . $short_description . "',slider_url = '$slider_url' WHERE id =" . $ID;
                $db->sql($sql_query);
                $update_result = $db->getResult();
            }
            if (!empty($update_result)) {
                $update_result = 0;
            } else {
                $update_result = 1;
            }

            if ($update_result == 1) {
                $error['update_category'] = " <section class='content-header'><span class='label label-success'>Slider updated Successfully</span></section>";
            } else {
                $error['update_category'] = " <span class='label label-danger'>Failed update Slider</span>";
            }
        }
    } else {
        $error['check_permission'] = " <section class='content-header'><span class='label label-danger'>You have no permission to update slider</span></section>";
    }
}

$sql_query = "SELECT s.*,(SELECT name from category c WHERE c.id = s.type_id ) as category,(SELECT name from products p WHERE p.id = s.type_id ) as product FROM `slider`s WHERE s.id = $ID AND s.type_id =" . $row[0]['type_id'];
$db->sql($sql_query);
$res = $db->getResult();

if (isset($_POST['btnCancel'])) { ?>
    <script>
        window.location.href = "main-slider.php";
    </script>
<?php } ?>
<section class="content-header">
    <h1>
        Edit Slider<small><a href='main-slider.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to slider</a></small></h1>
    <small><?php echo isset($error['update_category']) ? $error['update_category'] : ''; ?></small>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if ($permissions['categories']['update'] == 0) { ?>
                <div class="alert alert-danger topmargin-sm">You have no permission to update slider.</div>
            <?php } ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Slider</h3>
                </div>
                <form id="edit_category_form" method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <input type="hidden" id="type1" name="type1" value="<?= $res[0]['type']; ?>" />
                        <div class="form-group">
                            <label for="type">Type :</label>
                            <select name="type" id="type" class="form-control" required>
                                <option value="<?= $res[0]['type']; ?>"><?= $res[0]['type']; ?></option>
                                <option value="default">Default</option>
                                <option value="category">Category</option>
                                <option value="product">Product</option>
                                <option value="slider_url">Slider Url</option>
                            </select>
                        </div>
                        <div class="form-group" id="categories" style="display:none;">
                            <label for="category">Categories :</label>
                            <select name="category" id="category" class="form-control">
                                <?php
                                $sql = "SELECT * FROM `category` order by id DESC";
                                $db->sql($sql);
                                $categories_result = $db->getResult();
                                ?>
                                <?php if ($permissions['categories']['read'] == 1) { ?>
                                    <option value="<?= $res[0]['id']; ?>"><?= $res[0]['category']; ?></option>
                                    <?php foreach ($categories_result as $value) { ?>
                                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <option value="">Select Category</option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group" id="products" style="display:none;">
                            <label for="product">Products :</label>
                            <select name="product" id="product" class="form-control">
                                <?php
                                $sql = "SELECT * FROM `products` order by id DESC";
                                $db->sql($sql);
                                $products_result = $db->getResult();

                                ?>
                                <?php if ($permissions['products']['read'] == 1) { ?>
                                    <option value="<?= $res[0]['id']; ?>"><?= $res[0]['product']; ?></option>
                                    <?php foreach ($products_result as $value) { ?>
                                        <option value="<?= $value['id'] ?>"><?= $value['name'] ?></option>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <option value="">Select Product</option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="image">Slider Image1 : <small> ( Recommended Size : 1024 x 512 pixels )</small></label>
                            <input type='file' name="image" id="image" value="<img src='<?= $data['image']; ?>'>" />
                            <?php if (!empty($res[0]['image'])) { ?>
                                <p class="help-block"><img src="<?= $res[0]['image']; ?>" width="280" height="190" /></p>
                            <?php } ?>
                        </div>
                        <div class="form-group">
                            <label for="image2">Slider Image2 : <small> ( Recommended Size : 700 x 650 pixels )</small></label>
                            <input type='file' name="image2" id="image2" value="<img src='<?= $data['image2'] ?>'>" />
                            <?php if (!empty($res[0]['image2'])) { ?>
                                <p class="help-block"><img src="<?= $res[0]['image2']; ?>" width="280" height="190" /></p>
                            <?php } ?>
                        </div>
                        <div class="form-group" id="link_1" style="display:none;">
                            <label for="slider_url">Link : </label>
                            <input type="text" class="form-control" name="slider_url" id="slider_url" value="<?= $res[0]['slider_url']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="title">Title : </label>
                            <textarea name="title" id="title" class="form-control" rows="2"><?= $res[0]['title']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="short_description">Description : </label>
                            <textarea name="short_description" id="short_description" class="form-control" rows="2"><?= $res[0]['short_description']; ?></textarea>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary" name="btnEdit">Update</button>
                        </div>
                </form>
            </div><!-- /.box -->
        </div>
    </div>
</section>

<div class="separator"> </div>

<script src="dist/js/jquery.validate.min.js"></script>

<script>
    $("#type").change(function() {
        type = $("#type").val();
        if (type == "default") {
            $("#categories").hide();
            $("#products").hide();
            $("#link_1").hide();
        }
        if (type == "category") {
            $("#categories").show();
            $("#products").hide();
            $("#link_1").hide();
        }
        if (type == "product") {
            $("#categories").hide();
            $("#products").show();
            $("#link_1").hide();
        }
        if (type == "slider_url") {
            $("#categories").hide();
            $("#products").hide();
            $("#link_1").show();
        }
    });
</script>
<script>
    type = $("#type1").val();
    if (type == "default") {
        $("#categories").hide();
        $("#products").hide();
        $("#link_1").hide();
    }
    if (type == "category") {
        $("#categories").show();
        $("#products").hide();
        $("#link_1").hide();
    }
    if (type == "product") {
        $("#categories").hide();
        $("#products").show();
        $("#link_1").hide();
    }
    if (type == "slider_url") {
        $("#categories").hide();
        $("#products").hide();
        $("#link_1").show();
    }
</script>