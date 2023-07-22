<div id="content" class="container col-md-12">
    <?php
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;

    if (isset($_POST['btnDelete'])) {
        if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
            echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
            return false;
        }

        $ID = (isset($_GET['id'])) ? $db->escapeString($fn->xss_clean($_GET['id'])) : "";

        $sql_query = "SELECT image FROM blogs WHERE id =" . $ID;
        $db->sql($sql_query);
        $res = $db->getResult();
        unlink($res[0]['image']);
        $sql_query = "DELETE FROM blogs WHERE id =" . $ID;
        $db->sql($sql_query);
        $delete_category_result = $db->getResult();
        if (!empty($delete_category_result)) {
            $delete_category_result = 0;
        } else {
            $delete_category_result = 1;
        }


        if ($delete_category_result == 1) {
            header("location: blogs.php");
        }
    }

    if (isset($_POST['btnNo'])) {
        header("location: blogs.php");
    }
    if (isset($_POST['btncancel'])) {
        header("location: blogs.php");
    }

    ?>
    <h1>Confirm Action</h1>
    <?php
    if ($permissions['categories']['delete'] == 1) { ?>
        <hr />
        <form method="post">
            <p>Are you sure want to delete this blog?All the images will also be Deleted.</p>
            <input type="submit" class="btn btn-primary" value="Delete" name="btnDelete" />
            <input type="submit" class="btn btn-danger" value="Cancel" name="btnNo" />
        </form>
        <div class="separator"> </div>
    <?php } else { ?>
        <div class="alert alert-danger topmargin-sm">You have no permission to delete blog.</div>
        <form method="post">
            <input type="submit" class="btn btn-danger" value="Back" name="btncancel" />
        </form>
    <?php } ?>
</div>

<?php $db->disconnect(); ?>