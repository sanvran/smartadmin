<?php
session_start();
include '../includes/crud.php';
include_once('../includes/variables.php');
include_once('../includes/custom-functions.php');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');
$fn = new custom_functions;
include_once('verify-token.php');
include_once('../includes/functions.php');
$function = new functions;
$db = new Database();
$db->connect();
$response = array();

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
$time_zone = $fn->set_timezone($config);
if (!$time_zone) {
    $response['error'] = true;
    $response['message'] = "Time Zone is not set.";
    print_r(json_encode($response));
    return false;
    exit();
}

/* 
1. get-slider-images
    accesskey:90336
    get-slider-images:1
*/

if (!isset($_POST['accesskey'])) {
    if (!isset($_GET['accesskey'])) {
        $response['error'] = true;
        $response['message'] = "Access key is invalid or not passed!";
        print_r(json_encode($response));
        return false;
    }
}

if (isset($_POST['accesskey'])) {
    $accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
} else {
    $accesskey = $db->escapeString($fn->xss_clean($_GET['accesskey']));
}

if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey!";
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['add-image'])) && ($_POST['add-image'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['home_sliders']['create'] == 0) {
        $response["message"] = "<p class='alert alert-danger'>You have no permission to create home slider.</p>";
        echo json_encode($response);
        return false;
    }
    $image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
    $image_error1 = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
    $image_type1 = $db->escapeString($fn->xss_clean($_FILES['image']['type']));

    $image2 = $db->escapeString($fn->xss_clean($_FILES['image2']['name']));
    $image_error2 = $db->escapeString($fn->xss_clean($_FILES['image2']['error']));
    $image_type2 = $db->escapeString($fn->xss_clean($_FILES['image2']['type']));

    $type = $db->escapeString($fn->xss_clean($_POST['type']));
    $title = $db->escapeString($fn->xss_clean($_POST['title']));
    $short_description = $db->escapeString($fn->xss_clean($_POST['short_description']));
    $id = ($type != 'default') ? $db->escapeString($fn->xss_clean($_POST[$type])) : "0";

    $error = array();
    $allowedExts = array("gif", "jpeg", "jpg", "png");
    error_reporting(E_ERROR | E_PARSE);

    $extension1 = end(explode(".", $_FILES["image"]["name"]));
    if ($image_error1 > 0) {
        $error['image'] = " <span class='label label-danger'>Not uploaded!</span>";
    } else {
        $result = $fn->validate_image($_FILES['image']);
        if ($result) {
            $response["message"] = "<span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            echo json_encode($response);
            $error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            return false;
        }
    }
    $extension2 = end(explode(".", $_FILES["image2"]["name"]));
    if ($image_error2 > 0) {
        $error['image2'] = " <span class='label label-danger'>Not uploaded!</span>";
    } else {
        $result = $fn->validate_image($_FILES['image2']);
        if ($result) {
            $response["message"] = "<span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            echo json_encode($response);
            $error['image2'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
            return false;
        }
    }
    if (empty($error['image']) && empty($error['image2'])) {
        $mt1 = explode(' ', microtime());
        $microtime1 = ((int)$mt1[1]) * 1000 + ((int)round($mt1[0] * 1000));
        $file1 = preg_replace("/\s+/", "_", $_FILES['image']['name']);

        $image = $microtime1 . "." . $extension1;

        $upload1 = move_uploaded_file($_FILES['image']['tmp_name'], '../upload/slider/' . $image);

        $mt2 = explode(' ', microtime());
        $microtime2 = ((int)$mt2[1]) * 1000 + ((int)round($mt2[0] * 1000));
        $file2 = preg_replace("/\s+/", "_", $_FILES['image2']['name']);

        $image2 = $microtime2 . "." . $extension2;

        $upload2 = move_uploaded_file($_FILES['image2']['tmp_name'], '../upload/slider/' . $image2);

        $upload_image = 'upload/slider/' . $image;
        $upload_image2 = 'upload/slider/' . $image2;
        $sql = "INSERT INTO `slider`(`image`,`image2`,`type`, `type_id`,`title`,`short_description`) VALUES ('$upload_image','$upload_image2','" . $type . "','" . $id . "','" . $title . "','" . $short_description . "')";
        $db->sql($sql);
        $res = $db->getResult();
        $sql = "SELECT id FROM `slider` ORDER BY id DESC";
        $db->sql($sql);
        $res = $db->getResult();
        $response["message"] = "<span class='label label-success'>Image Uploaded Successfully!</span>";
        $response["id"] = $res[0]['id'];
    } else {
        $response["message"] = "<span class='label label-daner'>Image could not be Uploaded!Try Again!</span>";
    }
    echo json_encode($response);
}
if (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] == 'delete-slider') {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['home_sliders']['delete'] == 0) {
        echo 2;
        return false;
    }

    $id        = $_GET['id'];
    $image    = $_GET['image'];
    $image2    = $_GET['image2'];

    if (!empty($image))
        unlink('../' . $image);

    if (!empty($image2))
        unlink('../' . $image2);

    $sql = 'DELETE FROM `slider` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}
if (isset($_POST['get-slider-images']) && $_POST['get-slider-images'] == 1) {
    if (!verify_token()) {
        return false;
    }
    $sql = 'select * from slider where status = 1 order by id desc';
    $db->sql($sql);
    $result = $db->getResult();
    $response = $temp = $temp1 = array();
    if (!empty($result)) {
        $response['error'] = false;
        $response['message'] = "Slider Images Retrived Successfully!";
        foreach ($result as $row) {
            $name = "";
            if ($row['type'] == 'category') {
                $sql = 'select `name` from category where id = ' . $row['type_id'] . ' order by id desc';
                $db->sql($sql);
                $cate_result = $db->getResult();
                $name = (!empty($cate_result[0]['name'])) ? $cate_result[0]['name'] : "";
                $slug = $function->slugify($db->escapeString($fn->xss_clean($name)));
            }
            if ($row['type'] == 'product') {
                $sql = 'select `name`,`slug` from products where id = ' . $row['type_id'] . ' order by id desc';
                $db->sql($sql);
                $pro_result = $db->getResult();
                $name = (!empty($pro_result[0]['name'])) ? $pro_result[0]['name'] : "";
                $slug = (!empty($pro_result[0]['slug'])) ? $pro_result[0]['slug'] : "";
            }

            $temp['type'] = $row['type'];
            $temp['type_id'] = $row['type_id'];
            $temp['name'] = $name;
            $temp['slug'] = $row['type_id'] == 0 ? "" : $slug;
            $temp['slider_url'] = !empty($row['slider_url']) ? $row['slider_url'] : "";
            $temp['title'] = !empty($row['title']) ? $row['title'] : "";
            $temp['short_description'] = !empty($row['short_description']) ? $row['short_description'] : "";
            $temp['image'] = DOMAIN_URL . $row['image'];
            $temp['image2'] = !empty($row['image2']) ?  DOMAIN_URL . $row['image2'] : "";
            $temp1[] = $temp;
        }
        $response['data'] = $temp1;
    } else {
        $response['error'] = true;
        $response['message'] = "No slider images uploaded yet!";
    }
    print_r(json_encode($response));
}
