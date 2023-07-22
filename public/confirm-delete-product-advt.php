<div id="content" class="container col-md-12">
    <?php
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;

    if (isset($_POST['btnDelete'])) {
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
            return false;
        }
        $target_path = 'upload/product-advt/';

        $ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";

        $sql_query = "SELECT ad1,ad2,ad3 FROM product_ads WHERE id =" . $ID;
        $db->sql($sql_query);
        $res = $db->getResult();
        unlink($target_path . $res[0]['ad1']);
        unlink($target_path . $res[0]['ad2']);
        unlink($target_path . $res[0]['ad3']);

        $sql_query = "DELETE FROM product_ads WHERE id =" . $ID;
        $db->sql($sql_query);
        $delete_advt_result = $db->getResult();
        if (!empty($delete_advt_result)) {
            $delete_advt_result = 0;
        } else {
            $delete_advt_result = 1;
        }
        if ($delete_advt_result == 1) {
            header("location: products-advt.php");
        }
    }

    if (isset($_POST['btnNo'])) {
        header("location: products-advt.php");
    }
    if (isset($_POST['btncancel'])) {
        header("location: products-advt.php");
    }

    ?>
    <h1>Confirm Action</h1>
    <?php
    if ($permissions['categories']['delete'] == 1) { ?>
        <hr />
        <form method="post">
            <p>Are you sure want to delete this Product Advertisement?All the images will be Deleted.</p>
            <input type="submit" class="btn btn-primary" value="Delete" name="btnDelete" />
            <input type="submit" class="btn btn-danger" value="Cancel" name="btnNo" />
        </form>
        <div class="separator"> </div>
    <?php } else { ?>
        <div class="alert alert-danger topmargin-sm">You have no permission to delete Product Advertisement.</div>
        <form method="post">
            <input type="submit" class="btn btn-danger" value="Back" name="btncancel" />
        </form>
    <?php } ?>
</div>

<?php $db->disconnect(); ?>