<?php
include_once('../includes/functions.php');
include_once('../includes/custom-functions.php');
$fn = new custom_functions;

require_once '../includes/crud.php';
$db = new Database();
$db->connect();

if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
    echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
    return false;
}

if (!empty($_FILES['zip_file']['name'][0])) {
    $filename = $db->escapeString($fn->xss_clean($_FILES["zip_file"]["name"][0]));
    $source = $db->escapeString($fn->xss_clean($_FILES["zip_file"]["tmp_name"][0]));
    $type = $db->escapeString($fn->xss_clean($_FILES["zip_file"]["type"][0]));

    $name = explode(".", $filename);
    $continue = strtolower($name[1]) == 'zip' ? true : false;
    if (!$continue) {
        $response['error'] = true;
        $response['message'] =  "The file you are trying to upload is not a .zip file. Please try again.";
    }

    $target_path = "../" . $filename;  // change this to the correct site path
    if (move_uploaded_file($source, $target_path)) {
        $zip = new ZipArchive();
        $x = $zip->open($target_path);
        if ($x === true) {
            $zip->renameName($filename, "update");
            $zip->extractTo("../"); // change this to the correct site path
            $zip->close();

            unlink($target_path);
        }
        $response['error'] = false;
        $response['message'] =  "Your .zip file was uploaded and unpacked.";
    } else {
        $response['error'] = true;
        $response['message'] =  "There was a problem with the upload. Please try again.";
    }
    print_r(json_encode($response));
    return false;
}
