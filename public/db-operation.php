<?php
session_start();
include('../includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");
$auth_username = $db->escapeString($_SESSION["user"]);

include_once('../includes/custom-functions.php');
$fn = new custom_functions;
include_once('../includes/functions.php');
$function = new functions;
$permissions = $fn->get_permissions($_SESSION['id']);
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
function checkadmin($auth_username)
{
    $db = new Database();
    $db->connect();
    $db->sql("SELECT `username` FROM `admin` WHERE `username`='$auth_username' LIMIT 1");
    $res = $db->getResult();
    if (!empty($res)) {
        return true;
    } else {
        return false;
    }
}

if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
    echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
    return false;
}

if (isset($_POST['change_category'])) {
    if ($permissions['subcategories']['read'] == 1) {
        if ($_POST['category_id'] == '') {
            $sql = "SELECT * FROM subcategory";
        } else {
            $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
            $sql = "SELECT * FROM subcategory WHERE category_id=" . $category_id;
        }
    } else {
        echo "<option value=''>--Select Subcategory--</option>";
        return false;
    }

    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row) {
            echo "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
    } else {
        echo "<option value=''>--No Sub Category is added--</option>";
    }
}

if (isset($_POST['category'])) {
    if ($permissions['subcategories']['read'] == 1) {
        if ($_POST['category_id'] == '') {
            $sql = "SELECT * FROM subcategory";
        } else {
            $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
            $sql = "SELECT * FROM subcategory WHERE category_id=" . $category_id;
        }

        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            echo "<option value=''>All</option>";
            foreach ($res as $row) {
                echo "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
            }
        } else {
            echo "<option value=''>--No Sub Category is added--</option>";
        }
    } else {
        echo "<option value=''>All</option>";
    }
}

if (isset($_POST['find_subcategory'])) {
    $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
    $sql = "SELECT * FROM subcategory WHERE category_id=" . $category_id;
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row) {
            echo "<option value=" . $row['id'] . ">" . $row['name'] . "</option>";
        }
    } else {
        echo "<option value=''>--No Sub Category is added--</option>";
    }
}

if (isset($_POST['delete_variant'])) {
    $id = $db->escapeString($fn->xss_clean($_POST['id']));

    $sql = "SELECT images FROM product_variant WHERE id =" . $id;
    $db->sql($sql);
    $res = $db->getResult();

    foreach ($res as $row)
        $other_images = $row['images']; /*get other images json array*/

    $variant_images = str_replace("'", '"', $other_images);
    $other_images = json_decode($variant_images); /*decode from json to array*/
    foreach ($other_images as $other_image) {
        unlink('../' . $other_image);
    }

    $sql = "DELETE FROM product_variant WHERE id=" . $id;
    $db->sql($sql);
}

if (isset($_POST['delete_variant_images']) && $_POST['delete_variant_images'] == 1) {
    $vid = $db->escapeString($fn->xss_clean($_POST['vid']));
    $i = $db->escapeString($fn->xss_clean($_POST['i']));

    $res = $fn->delete_variant_images($vid, $i);
    print_r($res);
}

if (isset($_POST['system_configurations'])) {
    if ($permissions['settings']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to update settings</label>';
        return false;
    }
    $date = $db->escapeString(date('Y-m-d'));
    $currency = (empty($_POST['currency'])) ? '₹' : $db->escapeString($fn->xss_clean($_POST['currency']));
    $sql = "UPDATE `settings` SET `value`='" . $currency . "' WHERE `variable`='currency'";
    $db->sql($sql);
    $message = "<div class='alert alert-success'> Settings updated successfully!</div>";
    $_POST['system_timezone_gmt'] = (trim($_POST['system_timezone_gmt']) == '00:00') ? "+" . trim($db->escapeString($fn->xss_clean($_POST['system_timezone_gmt']))) : $db->escapeString($fn->xss_clean($_POST['system_timezone_gmt']));

    if (preg_match("/[a-z]/i", $db->escapeString($fn->xss_clean($_POST['current_version'])))) {
        $_POST['current_version'] = 0;
    }
    if (preg_match("/[a-z]/i", $db->escapeString($fn->xss_clean($_POST['minimum_version_required'])))) {
        $_POST['minimum_version_required'] = 0;
    }
    if (preg_match("/[a-z]/i", $db->escapeString($fn->xss_clean($_POST['delivery_charge'])))) {
        $_POST['delivery_charge'] = 0;
    }
    if (preg_match("/[a-z]/i", $db->escapeString($fn->xss_clean($_POST['min-refer-earn-order-amount'])))) {
        $_POST['min-refer-earn-order-amount'] = 0;
    }
    if (preg_match("/[a-z]/i", $db->escapeString($fn->xss_clean($_POST['min_amount'])))) {
        $_POST['min_amount'] = 0;
    }
    if (preg_match("/[a-z]/i", $db->escapeString($fn->xss_clean($_POST['max-refer-earn-amount'])))) {
        $_POST['max-refer-earn-amount'] = 0;
    }
    if (preg_match("/[a-z]/i", $db->escapeString($fn->xss_clean($_POST['minimum-withdrawal-amount'])))) {
        $_POST['minimum-withdrawal-amount'] = 0;
    }
    if (preg_match("/[a-z]/i", $db->escapeString($fn->xss_clean($_POST['refer-earn-bonus'])))) {
        $_POST['refer-earn-bonus'] = 0;
    }

    $_POST['store_address'] = (!empty($_POST['store_address'])) ? preg_replace("/[\r\n]{2,}/", "<br>", $_POST['store_address']) : "";

    $settings_value = json_encode($fn->xss_clean_array($_POST));

    $sql = "UPDATE settings SET value='" . $settings_value . "' WHERE variable='system_timezone'";
    $db->sql($sql);
    $res = $db->getResult();
    $sql_logo = "select value from `settings` where variable='Logo' OR variable='logo'";
    $db->sql($sql_logo);
    $res_logo = $db->getResult();
    $file_name = $_FILES['logo']['name'];

    if (!empty($_FILES["logo"]["tmp_name"]) && $_FILES["logo"]["size"] > 0) {
        $tmp = explode('.', $file_name);
        $ext = end($tmp);

        $result = $fn->validate_image($_FILES["logo"]);
        if ($result) {
            echo " <span class='label label-danger'>Logo Image type must jpg, jpeg, gif, or png!</span>";
            return false;
        } else {
            $old_image = '../dist/img/' . $res_logo[0]['value'];
            if (file_exists($old_image)) {
                unlink($old_image);
            }

            $target_path = '../dist/img/';
            $filename = "logo." . strtolower($ext);
            $full_path = $target_path . '' . $filename;
            if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $full_path)) {
                $message = "Image could not be uploaded<br/>";
            } else {
                //Update Logo - id = 5
                $sql = "UPDATE `settings` SET `value`='" . $filename . "' WHERE `variable` = 'logo'";
                $db->sql($sql);
            }
        }
    }
    echo "<p class='alert alert-success'>Settings Saved!</p>";
}

if (isset($_POST['payment_method_settings'])) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if ($permissions['settings']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to update settings</label>';
        return false;
    }
    $data = $fn->get_settings('payment_methods', true);
    if (empty($data)) {
        $json_data = json_encode($fn->xss_clean_array($_POST));
        $sql = "INSERT INTO `settings`(`variable`, `value`) VALUES ('payment_methods','$json_data')";
        $db->sql($sql);
        echo "<div class='alert alert-success'> Settings created successfully!</div>";
    } else {
        $json_data = json_encode($fn->xss_clean_array($_POST));
        $sql = "UPDATE `settings` SET `value`='$json_data' WHERE `variable`='payment_methods'";
        $db->sql($sql);
        echo "<div class='alert alert-success'> Settings updated successfully!</div>";
    }
}

if (isset($_POST['time_slot_config']) && $_POST['time_slot_config'] == 1) {
    if ($permissions['settings']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to update settings</label>';
        return false;
    }
    $_POST['allowed_days'] = empty($_POST['allowed_days']) ? 1 : $db->escapeString($fn->xss_clean($_POST['allowed_days']));
    if (!$time_slot_config) {
        $settings_value = json_encode($fn->xss_clean_array($_POST));
        $sql = "INSERT INTO settings (`variable`,`value`) VALUES ('time_slot_config','" . $settings_value . "')";
    } else {
        $settings_value = json_encode($fn->xss_clean_array($_POST));
        $sql = "UPDATE settings SET value='" . $settings_value . "' WHERE variable='time_slot_config'";
    }
    if ($db->sql($sql)) {
        echo "<p class='alert alert-success'>Saved Successfully!</p>";
    } else {
        echo "<p class='alert alert-danger'>Something went wrong please try again!</p>";
    }
}
if (isset($_POST['add_category_settings']) && $_POST['add_category_settings'] == 1) {
    if ($permissions['settings']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to update settings</label>';
        return false;
    }
    $sql = "select variable from settings where variable='categories_settings' ";
    $db->sql($sql);
    $res = $db->getResult();
    if (empty($res)) {
        $settings_value = json_encode($fn->xss_clean_array($_POST));
        $sql = "INSERT INTO settings (`variable`,`value`) VALUES ('categories_settings','" . $settings_value . "')";
    } else {
        $settings_value = json_encode($fn->xss_clean_array($_POST));
        $sql = "UPDATE settings SET value='" . $settings_value . "' WHERE variable='categories_settings'";
    }
    if ($db->sql($sql)) {
        echo "<p class='alert alert-success'>Saved Successfully!</p>";
    } else {
        echo "<p class='alert alert-danger'>Something went wrong please try again!</p>";
    }
}

if (isset($_POST['add_dr_gold']) && $_POST['add_dr_gold'] == 1) {
    if ($permissions['settings']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to update settings</label>';
        return false;
    }
    $sql = "select * from settings where variable = 'doctor_brown'";
    $db->sql($sql);
    $res = $db->getResult();
    // print_r($res);
    if (empty($res)) {
        $settings_value = json_encode($fn->xss_clean_array($_POST));
        $sql = "INSERT INTO settings (`variable`,`value`) VALUES ('doctor_brown','$settings_value ')";
        if ($db->sql($sql)) {
            $response['error'] = false;
            $response['message'] = "Your system is registered and activated successfully!";
        } else {
            $response['error'] = true;
            $response['message'] = "Something went wrong please try again!";
        }
    } else {
        /* delete if token are different */
        $token = json_decode($res[0]['value'], true);
        $db_token = (isset($token['time_check']) && !empty($token['time_check'])) ? $token['time_check'] : "";

        $vali_token = $fn->xss_clean_array($_POST['time_check']);
        if ($db_token != $vali_token) {
            $sql = "DELETE FROM `settings` WHERE variable = 'doctor_brown'";
            if ($db->sql($sql)) {
                $settings_value = json_encode($fn->xss_clean_array($_POST));
                $sql = "INSERT INTO settings (`variable`,`value`) VALUES ('doctor_brown','$settings_value ')";

                if ($db->sql($sql)) {
                    $response['error'] = false;
                    $response['message'] = "Your system is registered and activated successfully!";
                } else {
                    $response['error'] = true;
                    $response['message'] = "Something went wrong please try again!";
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Something went wrong please try again!";
            }
        } else {
            $response['error'] = false;
            $response['message'] = "Your system is already activated!";
        }
    }
    print_r(json_encode($response));
}

if (isset($_POST['front_end_settings']) && $_POST['front_end_settings'] == 1) {
    if ($permissions['settings']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to update settings</label>';
        return false;
    }
    $res = $res_data = array();
    $loading_old = "";
    $web_logo_old = "";
    $favicon_old = "";
    $screenshots_old = "";
    $google_play_old = "";

    $sql = "select * from settings where variable = 'front_end_settings'";
    $db->sql($sql);
    $res = $db->getResult();
    $res_data = (!empty($res)) ? json_decode($res[0]['value'], true) : array();

    $loading_old = $res_data['loading'];
    $favicon_old = $res_data['favicon'];
    $web_logo_old = $res_data['web_logo'];
    $screenshots_old = $res_data['screenshots'];
    $google_play_old = $res_data['google_play'];

    if (isset($_FILES['favicon']) && !empty($_FILES['favicon']) && $_FILES['favicon']['error'] == 0 && $_FILES['favicon']['size'] > 0) {
        $favicon = $db->escapeString($fn->xss_clean($_FILES['favicon']['name']));
        $extension = pathinfo($_FILES["favicon"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["favicon"]);
        if ($result) {
            echo "<p class='alert alert-danger'>Image type must jpg, jpeg, gif, or png!</p>";
            return false;
        }

        $target_path = '../dist/img/';
        if (!empty($favicon_old) && $favicon_old != '') {
            unlink($target_path . $favicon_old);
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;

        if (!move_uploaded_file($_FILES["favicon"]["tmp_name"], $full_path)) {
            echo "<p class='alert alert-danger'>Invalid directory to load favicon!</p>";
            return false;
        }
        $_POST['favicon'] = $filename;
    } else {
        $_POST['favicon'] = $favicon_old;
    }

    if (isset($_FILES['web_logo']) && !empty($_FILES['web_logo']) && $_FILES['web_logo']['error'] == 0 && $_FILES['web_logo']['size'] > 0) {
        $web_logo = $db->escapeString($fn->xss_clean($_FILES['web_logo']['name']));
        $extension = pathinfo($_FILES["web_logo"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["web_logo"]);
        if ($result) {
            echo "<p class='alert alert-danger'>Image type must jpg, jpeg, gif, or png!</p>";
            return false;
        }

        $target_path = '../dist/img/';
        if (!empty($fweb_logo_old) && $web_logo_old != '') {
            unlink($target_path . $web_logo_old);
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;

        if (!move_uploaded_file($_FILES["web_logo"]["tmp_name"], $full_path)) {
            echo "<p class='alert alert-danger'>Invalid directory to load Web Logo!</p>";
            return false;
        }
        $_POST['web_logo'] = $filename;
    } else {
        $_POST['web_logo'] = $web_logo_old;
    }

    if (isset($_FILES['loading']) && !empty($_FILES['loading']) && $_FILES['loading']['error'] == 0 && $_FILES['loading']['size'] > 0) {
        $loading = $db->escapeString($fn->xss_clean($_FILES['loading']['name']));
        $extension = pathinfo($_FILES["loading"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["loading"]);
        if ($result) {
            echo "<p class='alert alert-danger'>Image type must jpg, jpeg, gif, or png!</p>";
            return false;
        }

        $target_path = '../dist/img/';
        if (!empty($loading_old) && $loading_old != '') {
            unlink($target_path . $loading_old);
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;

        if (!move_uploaded_file($_FILES["loading"]["tmp_name"], $full_path)) {
            echo "<p class='alert alert-danger'>Invalid directory to load loading Image!</p>";
            return false;
        }
        $_POST['loading'] = $filename;
    } else {
        $_POST['loading'] = $loading_old;
    }


    if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']) && $_FILES['screenshots']['error'] == 0 && $_FILES['screenshots']['size'] > 0) {
        $screenshots = $db->escapeString($fn->xss_clean($_FILES['screenshots']['name']));
        $extension = pathinfo($_FILES["screenshots"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["screenshots"]);
        if ($result) {
            echo "<p class='alert alert-danger'>Image type must jpg, jpeg, gif, or png!</p>";
            return false;
        }

        $target_path = '../dist/img/';
        if (!empty($screenshots_old) && $screenshots_old != '') {
            unlink($target_path . $screenshots_old);
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;

        if (!move_uploaded_file($_FILES["screenshots"]["tmp_name"], $full_path)) {
            echo "<p class='alert alert-danger'>Invalid directory to load App Screenshots!</p>";
            return false;
        }
        $_POST['screenshots'] = $filename;
    } else {
        $_POST['screenshots'] = $screenshots_old;
    }


    if (isset($_FILES['google_play']) && !empty($_FILES['google_play']) && $_FILES['google_play']['error'] == 0 && $_FILES['google_play']['size'] > 0) {
        $google_play = $db->escapeString($fn->xss_clean($_FILES['google_play']['name']));
        $extension = pathinfo($_FILES["google_play"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["google_play"]);
        if ($result) {
            echo "<p class='alert alert-danger'>Image type must jpg, jpeg, gif, or png!</p>";
            return false;
        }

        $target_path = '../dist/img/';
        if (!empty($google_play_old) && $google_play_old != '') {
            unlink($target_path . $google_play_old);
        }
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;

        if (!move_uploaded_file($_FILES["google_play"]["tmp_name"], $full_path)) {
            echo "<p class='alert alert-danger'>Invalid directory to load Google Play Image!</p>";
            return false;
        }
        $_POST['google_play'] = $filename;
    } else {
        $_POST['google_play'] = $google_play_old;
    }
    $_POST['show_color_picker_in_website'] = str_replace("'", "''", $_POST['show_color_picker_in_website']);
    if (empty($res)) {
        $settings_value = json_encode($fn->xss_clean_array($_POST));
        $sql = "INSERT INTO settings (`variable`,`value`) VALUES ('front_end_settings','$settings_value ')";
        if ($db->sql($sql)) {
            echo "<p class='alert alert-success'>Saved Successfully!</p>";
        } else {
            echo "<p class='alert alert-danger'>Something went wrong please try again!</p>";
        }
    } else {
        $settings_value = json_encode($fn->xss_clean_array($_POST));
        $sql = "UPDATE settings SET value='" . $settings_value . "' WHERE variable='front_end_settings'";
        if ($db->sql($sql)) {
            echo "<p class='alert alert-success'>Saved Successfully!</p>";
        } else {
            echo "<p class='alert alert-danger'>Something went wrong please try again!</p>";
        }
    }
}

if (isset($_POST['add_delivery_boy']) && $_POST['add_delivery_boy'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['delivery_boys']['create'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to create delivery boy</label>';
        return false;
    }
    $name = $db->escapeString($fn->xss_clean($_POST['name']));
    $mobile = $db->escapeString($fn->xss_clean($_POST['mobile']));
    $address = $db->escapeString($fn->xss_clean($_POST['address']));
    $bonus = $db->escapeString($fn->xss_clean($_POST['bonus']));
    $dob = $db->escapeString($fn->xss_clean($_POST['dob']));
    $bank_name = $db->escapeString($fn->xss_clean($_POST['bank_name']));
    $bonus_method = $db->escapeString($fn->xss_clean($_POST['bonus_method']));
    $other_payment_info = (isset($_POST['other_payment_info']) && !empty($_POST['other_payment_info'])) ? $db->escapeString($fn->xss_clean($_POST['other_payment_info'])) : '';
    $account_number = $db->escapeString($fn->xss_clean($_POST['account_number']));
    $account_name = $db->escapeString($fn->xss_clean($_POST['account_name']));
    $ifsc_code = $db->escapeString($fn->xss_clean($_POST['ifsc_code']));
    $password = $db->escapeString($fn->xss_clean($_POST['password']));
    $password = md5($password);
    $sql = 'SELECT id FROM delivery_boys WHERE mobile=' . $mobile;
    $db->sql($sql);
    $res = $db->getResult();
    $count = $db->numRows($res);
    if ($count > 0) {
        echo '<label class="alert alert-danger">Mobile Number Already Exists!</label>';
        return false;
    }
    $target_path = '../upload/delivery-boy/';
    if ($_FILES['driving_license']['error'] == 0 && $_FILES['driving_license']['size'] > 0) {
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $extension = pathinfo($_FILES["driving_license"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["driving_license"]);
        if ($result) {
            echo " <span class='label label-danger'>Driving License image type must jpg, jpeg, gif, or png!</span>";
            return false;
            exit();
        }

        $dr_filename = microtime(true) . '.' . strtolower($extension);
        $dr_full_path = $target_path . "" . $dr_filename;
        if (!move_uploaded_file($_FILES["driving_license"]["tmp_name"], $dr_full_path)) {
            echo "<p class='alert alert-danger'>Invalid directory to load image!</p>";
            return false;
        }
    }
    if ($_FILES['national_identity_card']['error'] == 0 && $_FILES['national_identity_card']['size'] > 0) {
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $extension = pathinfo($_FILES["national_identity_card"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["national_identity_card"]);
        if ($result) {
            echo " <span class='label label-danger'>National Identity Card image type must jpg, jpeg, gif, or png!</span>";
            return false;
            exit();
        }

        $nic_filename = microtime(true) . '.' . strtolower($extension);
        $nic_full_path = $target_path . "" . $nic_filename;
        if (!move_uploaded_file($_FILES["national_identity_card"]["tmp_name"], $nic_full_path)) {
            echo "<p class='alert alert-danger'>Invalid directory to load image!</p>";
            return false;
        }
    }
    $sql = "INSERT INTO delivery_boys (`name`,`mobile`,`password`,`address`,`bonus`, `driving_license`, `national_identity_card`, `dob`, `bank_account_number`, `bank_name`, `account_name`, `ifsc_code`,`other_payment_information`,`bonus_method`) VALUES ('$name', '$mobile', '$password', '$address','$bonus','$dr_filename', '$nic_filename', '$dob','$account_number','$bank_name','$account_name','$ifsc_code','$other_payment_info','$bonus_method')";
    if ($db->sql($sql)) {
        echo '<label class="alert alert-success">Delivery Boy Added Successfully!</label>';
    } else {
        echo '<label class="alert alert-danger">Some Error Occrred! please try again.</label>';
    }
}
if (isset($_POST['update_delivery_boy']) && $_POST['update_delivery_boy'] == 1) {

    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['delivery_boys']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to update delivery boy</label>';
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
    if ($id == 104 && ALLOW_MODIFICATION == 0) {
        echo '<label class="alert alert-danger">Sorry you can not update this delivery boy.</label>';
        return false;
    }
    $name = $db->escapeString($fn->xss_clean($_POST['update_name']));
    $password = !empty($_POST['update_password']) ? $db->escapeString($fn->xss_clean($_POST['update_password'])) : '';
    $update_other_payment_info = !empty($_POST['update_other_payment_info']) ? $db->escapeString($fn->xss_clean($_POST['update_other_payment_info'])) : '';
    $address = $db->escapeString($fn->xss_clean($_POST['update_address']));
    $bonus = $db->escapeString($fn->xss_clean($_POST['update_bonus']));
    $status = $db->escapeString($fn->xss_clean($_POST['status']));
    $available = $db->escapeString($fn->xss_clean($_POST['available']));
    $update_dob = $db->escapeString($fn->xss_clean($_POST['update_dob']));
    $bonus_method = $db->escapeString($fn->xss_clean($_POST['bonus_method']));
    $update_bank_name = $db->escapeString($fn->xss_clean($_POST['update_bank_name']));
    $update_account_number = $db->escapeString($fn->xss_clean($_POST['update_account_number']));
    $update_account_name = $db->escapeString($fn->xss_clean($_POST['update_account_name']));
    $update_ifsc_code = $db->escapeString($fn->xss_clean($_POST['update_ifsc_code']));
    $password = !empty($password) ? md5($password) : '';
    $dr_image = $nic_image = "";
    if ($_FILES['update_driving_license']['size'] != 0 && $_FILES['update_driving_license']['error'] == 0 && !empty($_FILES['update_driving_license'])) {
        //image isn't empty and update the image
        $dr_image = $db->escapeString($fn->xss_clean($_POST['dr_image1']));
        $extension = pathinfo($_FILES["update_driving_license"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["update_driving_license"]);
        if ($result) {
            echo " <span class='label label-danger'>Driving License image type must jpg, jpeg, gif, or png!</span>";
            return false;
            exit();
        }
        $target_path = '../upload/delivery-boy/';
        $dr_filename = microtime(true) . '.' . strtolower($extension);
        $dr_full_path = $target_path . "" . $dr_filename;
        if (!move_uploaded_file($_FILES["update_driving_license"]["tmp_name"], $dr_full_path)) {
            echo '<p class="alert alert-danger">Can not upload image.</p>';
            return false;
            exit();
        }
        if (!empty($dr_image)) {
            unlink($target_path . $dr_image);
        }
        $sql = "UPDATE delivery_boys SET `driving_license`='" . $dr_filename . "' WHERE `id`=" . $id;
        $db->sql($sql);
    }
    if ($_FILES['update_national_identity_card']['size'] != 0 && $_FILES['update_national_identity_card']['error'] == 0 && !empty($_FILES['update_national_identity_card'])) {
        //image isn't empty and update the image
        $nic_image = $db->escapeString($fn->xss_clean($_POST['nic_image']));
        $extension = pathinfo($_FILES["update_national_identity_card"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["update_national_identity_card"]);
        if ($result) {
            echo " <span class='label label-danger'>National Identity Card image type must jpg, jpeg, gif, or png!</span>";
            return false;
            exit();
        }
        $target_path = '../upload/delivery-boy/';
        $nic_filename = microtime(true) . '.' . strtolower($extension);
        $nic_full_path = $target_path . "" . $nic_filename;
        if (!move_uploaded_file($_FILES["update_national_identity_card"]["tmp_name"], $nic_full_path)) {
            echo '<p class="alert alert-danger">Can not upload image.</p>';
            return false;
            exit();
        }
        if (!empty($nic_image)) {
            unlink($target_path . $nic_image);
        }
        $sql = "UPDATE delivery_boys SET `national_identity_card`='" . $nic_filename . "' WHERE `id`=" . $id;
        $db->sql($sql);
    }

    if (!empty($password)) {
        $sql = "Update delivery_boys set `name`='" . $name . "',password='" . $password . "',`address`='" . $address . "',`bonus`='" . $bonus . "',`bonus_method` = '" . $bonus_method . "',`status`='" . $status . "',`is_available`='" . $available . "',`dob`='$update_dob',`bank_account_number`='$update_account_number',`bank_name`='$update_bank_name',`account_name`='$update_account_name',`ifsc_code`='$update_ifsc_code',`other_payment_information`='$update_other_payment_info' where `id`=" . $id;
    } else {
        $sql = "Update delivery_boys set `name`='" . $name . "',`address`='" . $address . "',`bonus`='" . $bonus . "',`bonus_method` = '" . $bonus_method . "',`status`='" . $status . "',`is_available`='" . $available . "',`dob`='$update_dob',`bank_account_number`='$update_account_number',`bank_name`='$update_bank_name',`account_name`='$update_account_name',`ifsc_code`='$update_ifsc_code',`other_payment_information`='$update_other_payment_info'  where `id`=" . $id;
    }
    if ($db->sql($sql)) {
        echo "<label class='alert alert-success'>Information Updated Successfully.</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
    }
}

if (isset($_GET['delete_delivery_boy']) && $_GET['delete_delivery_boy'] == 1) {
    if ($permissions['delivery_boys']['delete'] == 0) {
        echo 2;
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_GET['id']));
    $target_path = '../upload/delivery-boy/';
    $driving_license = $db->escapeString($fn->xss_clean($_GET['driving_license']));
    $national_identity_card = $db->escapeString($fn->xss_clean($_GET['national_identity_card']));
    if ($id == 104) {
        echo 3;
        return false;
    }
    $sql = "DELETE FROM `delivery_boys` WHERE id=" . $id;
    if ($db->sql($sql)) {
        // delete fund_transfers
        $sql = "DELETE FROM `fund_transfers` WHERE delivery_boy_id=" . $id;
        $db->sql($sql);
        // delete withdrawal requests
        $sql = "DELETE FROM `withdrawal_requests` WHERE `type_id`=" . $id . " AND `type`='delivery_boy'";
        $db->sql($sql);
        if (!empty($driving_license)) {
            unlink($target_path . $driving_license);
        }
        if (!empty($national_identity_card)) {
            unlink($target_path . $national_identity_card);
        }
        echo 0;
    } else {
        echo 1;
    }
}

if (isset($_POST['update_web_category']) && $_POST['update_web_category'] == 1) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if ($permissions['categories']['update'] == 1) {
        $category_id = $db->escapeString($fn->xss_clean($_POST['web_category_id']));
        $c_image = $db->escapeString($fn->xss_clean($_FILES['c_image']['name']));
        $c_image_temp = $db->escapeString($fn->xss_clean($_FILES['c_image']['tmp_name']));
        $extension = pathinfo($_FILES["c_image"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["c_image"]);
        if ($result) {
            echo '<p class="alert alert-danger">Image type must jpg, jpeg, gif, or png!</p>';
            return false;
            exit();
        }

        if ($c_image_temp != "") {

            $target_path = '../upload/web-category-image/';
            if (!is_dir($target_path)) {
                mkdir($target_path, 0777, true);
            }
            $nic_filename = microtime(true) . '.' . strtolower($extension);
            $nic_full_path = $target_path . "" . $nic_filename;

            $target_path_db = 'upload/web-category-image/';
            $nic_filename_db = microtime(true) . '.' . strtolower($extension);
            $nic_full_path_db = $target_path_db . "" . $nic_filename_db;
            if (!move_uploaded_file($_FILES["c_image"]["tmp_name"], $nic_full_path)) {
                echo '<p class="alert alert-danger">Can not upload image.</p>';
                return false;
                exit();
            }
            $c_update = "update category set  web_image= '$nic_full_path_db' where id='$category_id'";
        }

        $db->sql($c_update);
        $update_result = $db->getResult();
    }
}

if (isset($_POST['add_social_media']) && $_POST['add_social_media'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['settings']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to add social media</label>';
        return false;
    }
    $icon = $db->escapeString($fn->xss_clean($_POST['icon']));
    $link = $db->escapeString($fn->xss_clean($_POST['link']));

    $sql = "INSERT INTO social_media (`icon`,`link`) VALUES ('$icon', '$link')";
    if ($db->sql($sql)) {
        echo '<label class="alert alert-success">Social Media Added Successfully!</label>';
    } else {
        echo '<label class="alert alert-danger">Some Error Occrred! please try again.</label>';
    }
}
if (isset($_POST['update_social_media']) && $_POST['update_social_media'] == 1) {

    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['settings']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to update social media</label>';
        return false;
    }

    $icon = $db->escapeString($fn->xss_clean($_POST['update_icon']));
    $link = $db->escapeString($fn->xss_clean($_POST['update_link']));
    $id = $db->escapeString($fn->xss_clean($_POST['social_media_id']));
    $sql = "Update social_media set `icon`='" . $icon . "', link='" . $link . "' where `id`=" . $id;

    $db->sql($sql);

    if ($db->sql($sql)) {
        echo "<label class='alert alert-success'>Information Updated Successfully.</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
    }
}
if (isset($_GET['delete_social_media']) && $_GET['delete_social_media'] == 1) {
    if ($permissions['settings']['update'] == 0) {
        echo 2;
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_GET['id']));


    $sql = "DELETE FROM `social_media` WHERE id=" . $id;
    if ($db->sql($sql)) {
        echo 0;
    } else {
        echo 1;
    }
}

if (isset($_POST['update_payment_request']) && $_POST['update_payment_request'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['payment']['update'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update payment request.</label>";
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_POST['payment_request_id']));
    $remarks = $db->escapeString($fn->xss_clean($_POST['update_remarks']));
    $status = $db->escapeString($fn->xss_clean($_POST['status']));
    $sql = "select status from payment_requests where id=" . $id;
    $db->sql($sql);
    $res = $db->getResult();
    if ($res[0]['status'] == 1) {
        echo "<label class='alert alert-danger'>Payment request already approved.</label>";
        return false;
    }
    if ($res[0]['status'] == 2) {
        echo "<label class='alert alert-danger'>Payment request already cancelled.</label>";
        return false;
    }
    if ($status == '2') {
        $sql = "SELECT user_id,amount_requested FROM payment_requests WHERE id=" . $id;
        $db->sql($sql);
        $res = $db->getResult();
        $user_id = $res[0]['user_id'];
        $amount = $res[0]['amount_requested'];

        $sql = "UPDATE users SET balance = balance + $amount WHERE id=" . $user_id;
        $db->sql($sql);
    }
    $sql = "Update payment_requests set `remarks`='" . $remarks . "',`status`='" . $status . "' where `id`=" . $id;
    if ($db->sql($sql)) {
        echo "<label class='alert alert-success'>Updated Successfully.</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
    }
}
if (isset($_POST['boy_id']) && isset($_POST['transfer_fund'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['payment']['update'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update delivery boy.</label>";
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_POST['boy_id']));
    $balance = $db->escapeString($fn->xss_clean($_POST['delivery_boy_balance']));
    if (!is_numeric($_POST['amount'])) {

        echo "<label class='alert alert-danger'>Amount must be number.</label>";
        return false;
    }
    $amount = $db->escapeString($fn->xss_clean($_POST['amount']));

    $message = (!empty($_POST['message'])) ? $db->escapeString($fn->xss_clean($_POST['message'])) : 'Fund Transferred By Admin';
    $bal = $balance - $amount;
    $sql = "Update delivery_boys set `balance`='" . $bal . "' where `id`=" . $id;
    $db->sql($sql);
    $sql = "INSERT INTO `fund_transfers` (`delivery_boy_id`,`amount`,`opening_balance`,`closing_balance`,`status`,`message`) VALUES ('" . $id . "','" . $amount . "','" . $balance . "','" . $bal . "','SUCCESS','" . $message . "')";
    $db->sql($sql);
    echo "<p class='alert alert-success'>Amount Transferred Successfully!</p>";
}
if (isset($_POST['add_promo_code']) && $_POST['add_promo_code'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['promo_codes']['create'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to create promo code</label>';
        return false;
    }
    $promo_code = $db->escapeString($fn->xss_clean($_POST['promo_code']));
    $message = $db->escapeString($fn->xss_clean($_POST['message']));
    $start_date = $db->escapeString($fn->xss_clean($_POST['start_date']));
    $end_date = $db->escapeString($fn->xss_clean($_POST['end_date']));
    $no_of_users = $db->escapeString($fn->xss_clean($_POST['no_of_users']));
    $minimum_order_amount = $db->escapeString($fn->xss_clean($_POST['minimum_order_amount']));
    $discount = $db->escapeString($fn->xss_clean($_POST['discount']));
    $discount_type = $db->escapeString($fn->xss_clean($_POST['discount_type']));
    $max_discount_amount = $db->escapeString($fn->xss_clean($_POST['max_discount_amount']));
    $repeat_usage = $db->escapeString($fn->xss_clean($_POST['repeat_usage']));
    $no_of_repeat_usage = !empty($_POST['repeat_usage']) ? $db->escapeString($fn->xss_clean($_POST['no_of_repeat_usage'])) : 0;
    $status = $db->escapeString($fn->xss_clean($_POST['status']));

    $sql = "INSERT INTO promo_codes (promo_code,message,start_date,end_date,no_of_users,minimum_order_amount,discount,discount_type,max_discount_amount,repeat_usage,no_of_repeat_usage,status)
                        VALUES('$promo_code', '$message', '$start_date', '$end_date','$no_of_users','$minimum_order_amount','$discount','$discount_type','$max_discount_amount','$repeat_usage','$no_of_repeat_usage','$status')";
    if ($db->sql($sql)) {
        echo '<label class="alert alert-success">Promo Code Added Successfully!</label>';
    } else {
        echo '<label class="alert alert-danger">Some Error Occrred! please try again.</label>';
    }
}
if (isset($_POST['update_promo_code']) && $_POST['update_promo_code'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['promo_codes']['update'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to update promo code</label>';
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_POST['promo_code_id']));
    $promo_code = $db->escapeString($fn->xss_clean($_POST['update_promo']));
    $message = $db->escapeString($fn->xss_clean($_POST['update_message']));
    $start_date = $db->escapeString($fn->xss_clean($_POST['update_start_date']));
    $end_date = $db->escapeString($fn->xss_clean($_POST['update_end_date']));
    $no_of_users = $db->escapeString($fn->xss_clean($_POST['update_no_of_users']));
    $minimum_order_amount = $db->escapeString($fn->xss_clean($_POST['update_minimum_order_amount']));
    $discount = $db->escapeString($fn->xss_clean($_POST['update_discount']));
    $discount_type = $db->escapeString($fn->xss_clean($_POST['update_discount_type']));
    $max_discount_amount = $db->escapeString($fn->xss_clean($_POST['update_max_discount_amount']));
    $repeat_usage = $db->escapeString($fn->xss_clean($_POST['update_repeat_usage']));
    $no_of_repeat_usage = $repeat_usage == 0 ? '0' : $db->escapeString($fn->xss_clean($_POST['update_no_of_repeat_usage']));
    $status = $db->escapeString($fn->xss_clean($_POST['status']));

    $sql = "Update promo_codes set `promo_code`='" . $promo_code . "',`message`='" . $message . "',`start_date`='" . $start_date . "',`end_date`='" . $end_date . "',`no_of_users`='" . $no_of_users . "',`minimum_order_amount`='" . $minimum_order_amount . "',`discount`='" . $discount . "',`discount_type`='" . $discount_type . "',`max_discount_amount`='" . $max_discount_amount . "',`repeat_usage`='" . $repeat_usage . "',`no_of_repeat_usage`='" . $no_of_repeat_usage . "',`status`='" . $status . "' where `id`=" . $id;

    if ($db->sql($sql)) {
        echo "<label class='alert alert-success'>Promo Code Updated Successfully.</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
    }
}
if (isset($_GET['delete_promo_code']) && $_GET['delete_promo_code'] == 1) {
    if ($permissions['promo_codes']['delete'] == 0) {
        echo 2;
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_GET['id']));
    $sql = "DELETE FROM `promo_codes` WHERE id=" . $id;
    if ($db->sql($sql)) {
        echo 0;
    } else {
        echo 1;
    }
}
if (isset($_POST['add_time_slot']) && $_POST['add_time_slot'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['settings']['update'] == 0) {

        echo '<label class="alert alert-danger">You have no permission to add time slot</label>';
        return false;
    }
    $title = $db->escapeString($fn->xss_clean($_POST['title']));
    $from_time = $db->escapeString($fn->xss_clean($_POST['from_time']));
    $to_time = $db->escapeString($fn->xss_clean($_POST['to_time']));
    $last_order_time = $db->escapeString($fn->xss_clean($_POST['last_order_time']));
    $status = $db->escapeString($fn->xss_clean($_POST['status']));
    $sql = "INSERT INTO time_slots (title,from_time,to_time,last_order_time,status)
                        VALUES('$title', '$from_time', '$to_time', '$last_order_time','$status')";
    if ($db->sql($sql)) {
        echo '<label class="alert alert-success">Time Slot Added Successfully!</label>';
    } else {
        echo '<label class="alert alert-danger">Some Error Occrred! please try again.</label>';
    }
}
if (isset($_POST['update_time_slot']) && $_POST['update_time_slot'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['settings']['update'] == 0) {

        echo '<label class="alert alert-danger">You have no permission to update time slot</label>';
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_POST['time_slot_id']));
    $title = $db->escapeString($fn->xss_clean($_POST['update_title']));
    $from_time = $db->escapeString($fn->xss_clean($_POST['update_from_time']));
    $to_time = $db->escapeString($fn->xss_clean($_POST['update_to_time']));
    $last_order_time = $db->escapeString($fn->xss_clean($_POST['update_last_order_time']));
    $status = $db->escapeString($fn->xss_clean($_POST['status']));
    $sql = "Update time_slots set `title`='" . $title . "',`from_time`='" . $from_time . "',`to_time`='" . $to_time . "',`last_order_time`='" . $last_order_time . "',`status`='" . $status . "' where `id`=" . $id;
    if ($db->sql($sql)) {
        echo "<label class='alert alert-success'>Time Slot Updated Successfully.</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
    }
}
if (isset($_GET['delete_time_slot']) && $_GET['delete_time_slot'] == 1) {
    if ($permissions['settings']['update'] == 0) {

        echo 2;
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_GET['id']));
    $sql = "DELETE FROM `time_slots` WHERE id=" . $id;
    if ($db->sql($sql)) {
        echo 0;
    } else {
        echo 1;
    }
}
if (isset($_POST['update_return_request']) && $_POST['update_return_request'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['return_requests']['update'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update return request.</label>";
        return false;
    }

    $id = $db->escapeString($fn->xss_clean($_POST['return_request_id']));
    $order_item_id = $db->escapeString($fn->xss_clean($_POST['order_item_id']));
    $order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
    $remarks = $db->escapeString($fn->xss_clean($_POST['update_remarks']));
    $return_status = $db->escapeString($fn->xss_clean($_POST['status']));

    $sql = "select status from return_requests where id=" . $id;
    $db->sql($sql);
    $res = $db->getResult();
    if ($res[0]['status'] == 1) {
        echo "<label class='alert alert-danger'>Return request already approved.</label>";
        return false;
    }
    if ($return_status == 1) {
        $sql = "SELECT user_id,status,sub_total FROM order_items WHERE id =" . $order_item_id;
        $db->sql($sql);
        $result = $db->getResult();
        $status = json_decode($result[0]['status']);
        $status[] = array('returned', date("d-m-Y h:i:sa"));
        $status = $db->escapeString(json_encode($status));
        $sql = "UPDATE order_items SET status = '" . $status . "', active_status = 'returned' WHERE id = " . $order_item_id;
        $db->sql($sql);

        /* check for other item status and summery of order */
        $sql = "SELECT id FROM order_items WHERE order_id=" . $order_id;
        $db->sql($sql);
        $total = $db->numRows();
        $sql = "SELECT id FROM `order_items` WHERE order_id=" . $order_id . " && (`active_status` LIKE '%cancelled%' OR `active_status` LIKE '%returned%' )";
        $db->sql($sql);
        $returned = $db->numRows();
        if ($returned == $total) {
            $sql = "SELECT status FROM orders WHERE id =" . $order_id;
            $db->sql($sql);
            $res = $db->getResult();
            $status_order = json_decode($res[0]['status']);
            $status_order[] = array('returned', date("d-m-Y h:i:sa"));
            $status_order = $db->escapeString(json_encode($status_order));
            $sql = "UPDATE orders SET status = '" . $status_order . "', active_status = 'returned' WHERE id = " . $order_id;
            $db->sql($sql);
            return false;
        }
        $sql = 'SELECT oi.`product_variant_id`,oi.`quantity`,oi.`discounted_price`,oi.`price`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE oi.`id`=' . $order_item_id;

        $db->sql($sql);
        $res_oi = $db->getResult();
        if ($res_oi[0]['type'] == 'packet') {
            $sql = "UPDATE product_variant SET stock = stock + " . $res_oi[0]['quantity'] . " WHERE id='" . $res_oi[0]['product_variant_id'] . "'";
            $db->sql($sql);

            $sql = "select stock from product_variant where id=" . $res_oi[0]['product_variant_id'];
            $db->sql($sql);
            $res_stock = $db->getResult();
            if ($res_stock[0]['stock'] > 0) {
                $sql = "UPDATE product_variant set serve_for='Available' WHERE id='" . $res_oi[0]['product_variant_id'] . "'";
                $db->sql($sql);
            }
        } else {
            /* When product type is loose */
            if ($res_oi[0]['measurement_unit_id'] != $res_oi[0]['stock_unit_id']) {
                $stock = $fn->convert_to_parent($res_oi[0]['measurement'], $res_oi[0]['measurement_unit_id']);
                $stock = $stock * $res_oi[0]['quantity'];
                $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'";
                $db->sql($sql);
            } else {
                $stock = $res_oi[0]['measurement'] * $res_oi[0]['quantity'];
                $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'";
                $db->sql($sql);
            }
            $sql = "select stock from product_variant where product_id=" . $res_oi[0]['product_id'];
            $db->sql($sql);
            $res_stck = $db->getResult();
            if ($res_stck[0]['stock'] > 0) {
                $sql = "UPDATE product_variant set serve_for='Available' WHERE product_id='" . $res_oi[0]['product_id'] . "'";
                $db->sql($sql);
            }
        }
        /* update user's wallet */
        $total = $res_oi[0]['discounted_price'] == 0 ? $res_oi[0]['price'] * $res_oi[0]['quantity'] : $res_oi[0]['discounted_price'] * $res_oi[0]['quantity'];
        $sql = "select user_id from return_requests where id=" . $id;
        $db->sql($sql);
        $res_user = $db->getResult();
        $user_id = $res_user[0]['user_id'];
        $sql = "update users set balance = balance + $total where id=" . $user_id;
        $db->sql($sql);
        /* add wallet transaction */
        $sql = "insert into wallet_transactions (`order_id`,`user_id`,`type`,`amount`,`message`,`status`)values(" . $order_id . "," . $user_id . ",'credit'," . $total . ",'Balance credited on return request approved.',1)";
        $db->sql($sql);
    }
    $sql_query = "Update return_requests set `remarks`='" . $remarks . "',`status`='" . $return_status . "' where `id`=" . $id;
    if ($db->sql($sql_query)) {
        echo "<label class='alert alert-success'>Return request updated successfully.</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
    }
}
if (isset($_GET['delete_return_request']) && $_GET['delete_return_request'] == 1) {
    if ($permissions['return_requests']['delete'] == 0) {
        echo 2;
        return false;
    }
    $id = $db->escapeString($_GET['id']);
    $sql = "DELETE FROM `return_requests` WHERE id=" . $id;
    if ($db->sql($sql)) {
        echo 0;
    } else {
        echo 1;
    }
}
if (isset($_POST['manage_customer_wallet']) && isset($_POST['user_id'])) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['customers']['read'] == 0) {
        echo '<label class="alert alert-danger">You have no permission to manage wallet balance</label>';
        return false;
    }

    $user_id = $db->escapeString($fn->xss_clean($_POST['user_id']));
    $amount = $db->escapeString($fn->xss_clean($_POST['amount']));
    $type = $db->escapeString($fn->xss_clean($_POST['type']));
    $message = !empty($_POST['message']) ? $db->escapeString($fn->xss_clean($_POST['message'])) : 'Transaction by admin';

    $balance = $fn->get_wallet_balance($user_id);
    if ($type == 'debit' && $balance <= 0) {
        echo "<label class='alert alert-danger'>Balance should be greater than 0.</label>";
        return false;
    }
    if ($type == 'debit' && $amount > $balance) {
        echo "<label class='alert alert-danger'>Amount should not be greater than balance.</label>";
        return false;
    }
    $new_balance = $type == 'credit' ? $balance + $amount : $balance - $amount;
    $fn->update_wallet_balance($new_balance, $user_id);
    if ($fn->add_wallet_transaction($order_id = "", $user_id, $type, $amount, $message)) {
        echo "<label class='alert alert-success'>Balance Updated Successfully.</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
    }
}
if (isset($_POST['add_system_user']) && $_POST['add_system_user'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $_SESSION['id'];
    $username = $db->escapeString($fn->xss_clean($_POST['username']));
    $email = $db->escapeString($fn->xss_clean($_POST['email']));
    if (empty($email)) {
        echo " <label class='alert alert-danger'>Email required!</label>";
        return false;
    }
    $valid_mail = "/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i";
    if (!preg_match($valid_mail, $email)) {
        echo " <label class='alert alert-danger'>Wrong email format!</label>";
        return false;
    }

    $password = $db->escapeString($fn->xss_clean($_POST['password']));
    $password = md5($password);
    $role = $db->escapeString($fn->xss_clean($_POST['role']));


    $sql = "SELECT id FROM admin WHERE username='" . $username . "'";
    $db->sql($sql);
    $res = $db->getResult();
    $count = $db->numRows($res);
    if ($count > 0) {
        echo '<label class="alert alert-danger">Username Already Exists!</label>';
        return false;
    }

    $sql = "SELECT id FROM admin WHERE email='" . $email . "'";
    $db->sql($sql);
    $res = $db->getResult();
    $count = $db->numRows($res);
    if ($count > 0) {
        echo '<label class="alert alert-danger">Email Already Exists!</label>';
        return false;
    }
    $permissions['orders'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-order'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-order'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-order'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-order'])));

    $permissions['categories'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-category'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-category'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-category'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-category'])));

    $permissions['subcategories'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-subcategory'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-subcategory'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-subcategory'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-subcategory'])));

    $permissions['products'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-product'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-product'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-product'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-product'])));

    $permissions['products_order'] = array("read" => $db->escapeString($fn->xss_clean($_POST['is-read-products-order'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-products-order'])));

    $permissions['home_sliders'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-home-slider'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-home-slider'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-home-slider'])));

    $permissions['new_offers'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-new-offer'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-new-offer'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-new-offer'])));

    $permissions['promo_codes'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-promo'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-promo'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-promo'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-promo'])));

    $permissions['featured'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-featured'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-featured'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-featured'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-featured'])));

    $permissions['customers'] = array("read" => $db->escapeString($fn->xss_clean($_POST['is-read-customers'])));

    $permissions['payment'] = array("read" => $db->escapeString($fn->xss_clean($_POST['is-read-payment'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-payment'])));

    $permissions['return_requests'] = array("read" => $db->escapeString($fn->xss_clean($_POST['is-read-return'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-return'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-return'])));

    $permissions['delivery_boys'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-delivery'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-delivery'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-delivery'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-delivery'])));

    $permissions['notifications'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-notification'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-notification'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-notification'])));

    $permissions['transactions'] = array("read" => $db->escapeString($fn->xss_clean($_POST['is-read-transaction'])));

    $permissions['settings'] = array("read" => $db->escapeString($fn->xss_clean($_POST['is-read-settings'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-settings'])));

    $permissions['locations'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-location'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-location'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-location'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-location'])));

    $permissions['reports'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-report'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-report'])));

    $permissions['faqs'] = array("create" => $db->escapeString($fn->xss_clean($_POST['is-create-faq'])), "read" => $db->escapeString($fn->xss_clean($_POST['is-read-faq'])), "update" => $db->escapeString($fn->xss_clean($_POST['is-update-faq'])), "delete" => $db->escapeString($fn->xss_clean($_POST['is-delete-faq'])));

    $encoded_permissions = json_encode($permissions);
    $sql = "INSERT INTO admin (username,email,password,role,permissions,created_by)
                        VALUES('$username', '$email', '$password', '$role','$encoded_permissions','$id')";
    if ($db->sql($sql)) {
        echo '<label class="alert alert-success">' . $role . ' Added Successfully!</label>';
    } else {
        echo '<label class="alert alert-danger">Some Error Occrred! please try again.</label>';
    }
}
if (isset($_GET['delete_system_user']) && $_GET['delete_system_user'] == 1) {
    $id = $db->escapeString($_GET['id']);
    $sql = "DELETE FROM `admin` WHERE id=" . $id;
    if ($db->sql($sql)) {
        echo 0;
    } else {
        echo 1;
    }
}
if (isset($_POST['update_system_user']) && $_POST['update_system_user'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_POST['system_user_id']));
    $permissions['orders'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-order'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-order'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-order'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-order'])));

    $permissions['categories'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-category'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-category'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-category'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-category'])));

    $permissions['subcategories'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-subcategory'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-subcategory'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-subcategory'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-subcategory'])));

    $permissions['products'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-product'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-product'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-product'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-product'])));

    $permissions['products_order'] = array("read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-products-order'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-products-order'])));

    $permissions['home_sliders'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-home-slider'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-home-slider'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-home-slider'])));

    $permissions['new_offers'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-new-offer'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-new-offer'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-new-offer'])));

    $permissions['promo_codes'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-promo'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-promo'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-promo'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-promo'])));

    $permissions['featured'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-featured'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-featured'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-featured'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-featured'])));

    $permissions['customers'] = array("read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-customers'])));

    $permissions['payment'] = array("read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-payment'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-payment'])));

    $permissions['return_requests'] = array("read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-return'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-return'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-return'])));

    $permissions['delivery_boys'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-delivery'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-delivery'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-delivery'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-delivery'])));

    $permissions['notifications'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-notification'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-notification'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-notification'])));

    $permissions['transactions'] = array("read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-transaction'])));

    $permissions['fund_transfer_delivery_boy'] = array("read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-fund'])));

    $permissions['settings'] = array("read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-settings'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-settings'])));

    $permissions['locations'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-location'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-location'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-location'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-location'])));

    $permissions['reports'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-report'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-report'])));

    $permissions['faqs'] = array("create" => $db->escapeString($fn->xss_clean($_POST['permission-is-create-faq'])), "read" => $db->escapeString($fn->xss_clean($_POST['permission-is-read-faq'])), "update" => $db->escapeString($fn->xss_clean($_POST['permission-is-update-faq'])), "delete" => $db->escapeString($fn->xss_clean($_POST['permission-is-delete-faq'])));

    $permissions = json_encode($permissions);
    $sql = "UPDATE admin SET permissions='" . $permissions . "' WHERE id=" . $id;
    if ($db->sql($sql)) {
        echo '<label class="alert alert-success">Updated Successfully!</label>';
    } else {
        echo '<label class="alert alert-danger">Some Error Occrred! please try again.</label>';
    }
}

if (isset($_POST['update_withdrawal_request']) && $_POST['update_withdrawal_request'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['return_requests']['update'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update withdrawal request.</label>";
        return false;
    }

    $id = $db->escapeString($fn->xss_clean($_POST['withdrawal_request_id']));
    $type1 = $db->escapeString($fn->xss_clean($_POST['type']));
    $type = $type1 == 'Delivery Boy' ? 'delivery_boy' : 'user';
    $type_id = $db->escapeString($fn->xss_clean($_POST['type_id']));
    $amount = $db->escapeString($fn->xss_clean($_POST['amount']));
    $status = $db->escapeString($fn->xss_clean($_POST['status']));
    $sql = "select status from withdrawal_requests where id=" . $id;
    $db->sql($sql);
    $res = $db->getResult();
    if ($res[0]['status'] == 1) {
        echo "<label class='alert alert-danger'>Withdrawal request already approved.</label>";
        return false;
    }
    if ($res[0]['status'] == 2) {
        echo "<label class='alert alert-danger'>Withdrawal request already cancelled.</label>";
        return false;
    }
    if ($status == 1) {
        if ($type == 'user') {
            $balance = $fn->get_wallet_balance($type_id);
            $new_balance = $balance + $amount;
            $fn->update_wallet_balance($new_balance, $type_id);
            $fn->add_wallet_transaction($order_id = "", $type_id, 'credit', $amount, 'Balance credited on withdrawal request cancellation.');
        }
        if ($type == 'delivery_boy') {
            $balance = $fn->get_balance($type_id);
            $new_balance = $balance + $amount;
            $fn->update_delivery_boy_wallet_balance($new_balance, $type_id);
            $sql = "INSERT INTO `fund_transfers` (`delivery_boy_id`,`type`,`amount`,`opening_balance`,`closing_balance`,`status`,`message`) VALUES ('" . $type_id . "','credit','" . $amount . "','" . $balance . "','" . $new_balance . "','SUCCESS','Balance credited on withdrawal request cancellation.')";
            $db->sql($sql);
        }
    }
    $sql_query = "Update withdrawal_requests set `status`='" . $status . "' where `id`=" . $id;
    if ($db->sql($sql_query)) {
        echo "<label class='alert alert-success'>Withdrawal request updated successfully.</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
    }
}

if (isset($_GET['delete_withdrawal_request']) && $_GET['delete_withdrawal_request'] == 1) {
    if ($permissions['return_requests']['delete'] == 0) {
        echo 2;
        return false;
    }
    $id = $db->escapeString($_GET['id']);
    $sql = "DELETE FROM `withdrawal_requests` WHERE id=" . $id;
    if ($db->sql($sql)) {
        echo 0;
    } else {
        echo 1;
    }
}

// upload bulk product - upload products in bulk using  a CSV file
if (isset($_POST['bulk_upload']) && $_POST['bulk_upload'] == 1 && (isset($_POST['type']) && $_POST['type'] == 'products')) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['products']['create'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to upload products.</label>";
        return false;
    }
    $count = 0;
    $count1 = 0;
    $error = false;
    $filename = $_FILES["upload_file"]["tmp_name"];
    $result = $fn->validate_image($_FILES["upload_file"], false);
    if ($result) {
        $error = true;
    }

    $allowed_status = array("received", "processed", "shipped");
    if ($_FILES["upload_file"]["size"] > 0  && $error == false) {
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count != 0) {
                $till_status = strtolower($emapData[8]);
                $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // product name
                $emapData[1] = trim($db->escapeString($fn->xss_clean($emapData[1]))); // category id
                $emapData[2] = trim($db->escapeString($fn->xss_clean($emapData[2]))); // subcategory id
                $emapData[3] = trim($db->escapeString($fn->xss_clean($emapData[3]))); // indicator
                $emapData[4] = trim($db->escapeString($fn->xss_clean($emapData[4]))); // manufacturer
                $emapData[5] = trim($db->escapeString($fn->xss_clean($emapData[5]))); // made in
                $emapData[6] = trim($db->escapeString($fn->xss_clean($emapData[6]))); // return status
                $emapData[7] = trim($db->escapeString($fn->xss_clean($emapData[7]))); // cancel status
                $emapData[8] = trim($db->escapeString($fn->xss_clean($till_status))); // till status
                $emapData[9] = trim($db->escapeString($fn->xss_clean($emapData[9]))); // description

                if (empty($emapData[0])) {
                    echo '<p class="alert alert-danger">Product Name  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[1])) {
                    echo '<p class="alert alert-danger">Category ID  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[6]) && $emapData[6] != 1) {
                    echo '<p class="alert alert-danger">Is Returnable is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[7]) && $emapData[7] != 1) {
                    echo '<p class="alert alert-danger">Is cancel-able is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[7]) && $emapData[7] == 1 && (empty($emapData[8]) || !in_array($emapData[8], $allowed_status))) {
                    echo '<p class="alert alert-danger">Till status is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[7]) && !(empty($emapData[8]))) {
                    echo '<p class="alert alert-danger">Till status is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[9])) {
                    echo '<p class="alert alert-danger">Description  is empty at row - ' . $count . '</div>';
                    return false;
                }
            }
            $count++;
        }
        fclose($file);
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count1 != 0) {
                $till_status = strtolower($emapData[8]);
                $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // product name
                $emapData[1] = trim($db->escapeString($fn->xss_clean($emapData[1]))); // category id
                $emapData[2] = trim($db->escapeString($fn->xss_clean($emapData[2]))); // subcategory id
                $emapData[3] = trim($db->escapeString($fn->xss_clean($emapData[3]))); // indicator
                $emapData[4] = trim($db->escapeString($fn->xss_clean($emapData[4]))); // manufacturer
                $emapData[5] = trim($db->escapeString($fn->xss_clean($emapData[5]))); // made in
                $emapData[6] = !empty($emapData[6]) ? trim($db->escapeString($fn->xss_clean($emapData[6]))) : 0; // return status
                $emapData[7] = !empty($emapData[7]) ? trim($db->escapeString($fn->xss_clean($emapData[7]))) : 0; // cancel status
                $emapData[8] = trim($db->escapeString($fn->xss_clean($till_status))); // till status
                $emapData[9] = trim($db->escapeString($fn->xss_clean($emapData[9]))); // description
                $emapData[10] = trim($db->escapeString($fn->xss_clean($emapData[10]))); // image
                $slug = $function->slugify($emapData[0]);
                $sql = "INSERT INTO products (`name`,`slug`,`category_id`,`subcategory_id`,`indicator`,`manufacturer`,`made_in`,`return_status`,`cancelable_status`,`till_status`,`description`,`image`) VALUES ('" . $emapData[0] . "','" . $slug . "','" . $emapData[1] . "','" . $emapData[2] . "','" . $emapData[3] . "','" . $emapData[4] . "','" . $emapData[5] . "','" . $emapData[6] . "','" . $emapData[7] . "','" . $emapData[8] . "','" . $emapData[9] . "','" . $emapData[10] . "')";
                $db->sql($sql);
            }

            $count1++;
        }
        fclose($file);
        echo "<p class='alert alert-success'>CSV file is successfully imported!</p><br>";
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p><br>";
    }
}
// upload bulk product - upload products in bulk using  a CSV file
if (isset($_POST['bulk_update']) && $_POST['bulk_update'] == 1 && (isset($_POST['type']) && $_POST['type'] == 'products')) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['products']['update'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update products.</label>";
        return false;
    }
    $count = 0;
    $count1 = 0;
    $filename = $_FILES["upload_file"]["tmp_name"];
    $error = false;
    $filename = $_FILES["upload_file"]["tmp_name"];
    $result = $fn->validate_image($_FILES["upload_file"], false);
    if ($result) {
        $error = true;
    }

    $allowed_status = array("received", "processed", "shipped");
    if ($_FILES["upload_file"]["size"] > 0  && $error == false) {
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count != 0) {
                $till_status = strtolower($emapData[9]);
                $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // product ID
                $emapData[1] = trim($db->escapeString($fn->xss_clean($emapData[1]))); // product name
                $emapData[2] = trim($db->escapeString($fn->xss_clean($emapData[2]))); // category id
                $emapData[3] = trim($db->escapeString($fn->xss_clean($emapData[3]))); // subcategory id
                $emapData[4] = trim($db->escapeString($fn->xss_clean($emapData[4]))); // indicator
                $emapData[5] = trim($db->escapeString($fn->xss_clean($emapData[5]))); // manufacturer
                $emapData[6] = trim($db->escapeString($fn->xss_clean($emapData[6]))); // made in
                $emapData[7] = trim($db->escapeString($fn->xss_clean($emapData[7]))); // return status
                $emapData[8] = trim($db->escapeString($fn->xss_clean($emapData[8]))); // cancel status
                $emapData[9] = trim($db->escapeString($fn->xss_clean($till_status))); // till status
                $emapData[10] = trim($db->escapeString($fn->xss_clean($emapData[10]))); // description
                $emapData[11] = $db->escapeString($fn->xss_clean($emapData[11])); // image
                if (empty($emapData[0])) {
                    echo '<p class="alert alert-danger">Product ID  is empty at row - ' . $count . '</div>';
                    return false;
                }
                $sql = "SELECT * FROM products WHERE id=" . $emapData[0];
                $db->sql($sql);
                $result = $db->getResult();
                if (empty($result)) {
                    echo '<p class="alert alert-danger">Product ID  is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[7]) && $emapData[7] != 1) {
                    echo '<p class="alert alert-danger">Is Returnable is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[8]) && $emapData[8] != 1) {
                    echo '<p class="alert alert-danger">Is cancel-able is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[8]) && $emapData[8] == 1 && (empty($emapData[9]) || !in_array($emapData[9], $allowed_status))) {
                    echo '<p class="alert alert-danger">Till status is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[8]) && !(empty($emapData[9]))) {
                    echo '<p class="alert alert-danger">Till status is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[8]) && (empty($emapData[9]))) {
                    echo '<p class="alert alert-danger">Till status is invalid or empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[11])) {
                    echo '<p class="alert alert-danger">Image  is empty at row - ' . $count . '</div>';
                    return false;
                }
            }
            $count++;
        }
        fclose($file);
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count1 != 0) {
                $emapData[0] = trim($db->escapeString($emapData[0])); // product ID
                $sql = "SELECT * FROM products WHERE id=" . $emapData[0];
                $db->sql($sql);
                $result = $db->getResult();
                $till_status = strtolower($emapData[9]);
                $emapData[1] = !empty($emapData[1]) ? trim($db->escapeString($fn->xss_clean($emapData[1]))) : $result[0]['name']; // product name
                $emapData[2] = !empty($emapData[2]) ? trim($db->escapeString($fn->xss_clean($emapData[2]))) : $result[0]['category_id']; // category id
                $emapData[3] = !empty($emapData[3]) ? trim($db->escapeString($fn->xss_clean($emapData[3]))) : $result[0]['subcategory_id']; // subcategory id
                $emapData[4] = !empty($emapData[4]) ? trim($db->escapeString($fn->xss_clean($emapData[4]))) : $result[0]['indicator']; // indicator
                $emapData[5] = !empty($emapData[5]) ? trim($db->escapeString($fn->xss_clean($emapData[5]))) : $result[0]['manufacturer']; // manufacturer
                $emapData[6] = !empty($emapData[6]) ? trim($db->escapeString($fn->xss_clean($emapData[6]))) : $result[0]['made_in']; // made in
                $emapData[7] = !empty($emapData[7]) ? trim($db->escapeString($fn->xss_clean($emapData[7]))) : $result[0]['return_status']; // return status
                $emapData[8] = trim($db->escapeString($fn->xss_clean($emapData[8]))); // cancel status
                $emapData[9] = !empty($emapData[8]) ? trim($db->escapeString($fn->xss_clean($till_status))) : ''; // till status
                $emapData[10] = !empty($emapData[10]) ? trim($db->escapeString($fn->xss_clean($emapData[10]))) : $result[0]['description']; // description
                $emapData[11] = !empty($emapData[11]) ? trim($db->escapeString($fn->xss_clean($emapData[11]))) : $result[0]['image']; // image

                $slug = !empty($emapData[1]) ? $function->slugify($emapData[1]) : $result[0]['slug'];
                $sql = "UPDATE products SET `name`='" . $emapData[1] . "',`slug`='" . $slug . "',`category_id`='" . $emapData[2] . "',`subcategory_id`='" . $emapData[3] . "',`indicator`='" . $emapData[4] . "',`manufacturer`='" . $emapData[5] . "',`made_in`='" . $emapData[6] . "',`return_status`='" . $emapData[7] . "',`cancelable_status`='" . $emapData[8] . "',`till_status`='" . $emapData[9] . "',`description`='" . $emapData[10] . "',`image`='" . $emapData[11] . "' WHERE id=" . $emapData[0];
                $db->sql($sql);
            }

            $count1++;
        }
        fclose($file);
        echo "<p class='alert alert-success'>CSV file is successfully imported!</p><br>";
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p><br>";
    }
}
// upload bulk product variants- upload product variants in bulk using  a CSV file
if (isset($_POST['bulk_upload']) && $_POST['bulk_upload'] == 1 && (isset($_POST['type']) && $_POST['type'] == 'variants')) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['products']['create'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to upload products.</label>";
        return false;
    }
    $count = 0;
    $count1 = 0;
    $filename = $_FILES["upload_file"]["tmp_name"];
    $error = false;
    $result = $fn->validate_image($_FILES["upload_file"], false);
    if ($result) {
        $error = true;
    }

    $file = fopen($filename, "r");
    $emptydata = false;
    $invalid_price = false;
    while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
        if ($count != 0) {
            $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // type
            $emapData[1] = trim($db->escapeString($fn->xss_clean($emapData[1]))); // measurement
            $emapData[2] = trim($db->escapeString($fn->xss_clean($emapData[2]))); // measurement unit id
            $emapData[3] = trim($db->escapeString($fn->xss_clean($emapData[3]))); // price
            $emapData[4] = trim($db->escapeString($fn->xss_clean($emapData[4]))); // discounted price
            $emapData[5] = trim($db->escapeString($fn->xss_clean($emapData[5]))); // serve for
            $emapData[6] = trim($db->escapeString($fn->xss_clean($emapData[6]))); // stock
            $emapData[7] = trim($db->escapeString($fn->xss_clean($emapData[7]))); // stock unit id
            $emapData[8] = trim($db->escapeString($fn->xss_clean($emapData[8]))); // product id

            if (empty($emapData[0]) || ($emapData[0] != 'packet' && $emapData[0] != 'loose')) {
                $emptydata = true;
                echo '<p class="alert alert-danger">Type  is empty or invalid at row - ' . $count . '</div>';
                return false;
            }
            if (empty($emapData[1])) {
                $emptydata = true;
                echo '<p class="alert alert-danger">Measurement  is empty or invalid at row - ' . $count . '</div>';
                return false;
            }
            $sql = "SELECT id FROM unit";
            $db->sql($sql);
            $ids = $db->getResult();
            $invalid_measurement_unit = 1;
            foreach ($ids as $id) {
                if ($emapData[2] == $id['id']) {
                    $invalid_measurement_unit = 0;
                }
            }
            if (empty($emapData[2]) || $invalid_measurement_unit == 1) {
                echo '<p class="alert alert-danger">Measurement Unit ID is empty or invalid at row - ' . $count . '</div>';
                return false;
            }
            if (empty($emapData[3]) || $emapData[3] <= $emapData[4]) {
                $emptydata = true;
                echo '<p class="alert alert-danger">Price is empty or invalid at row - ' . $count . '</div>';
                return false;
            }
            if (empty($emapData[5]) || ($emapData[5] != 'Available' && $emapData[5] != 'Sold Out')) {
                $emptydata = true;
                echo '<p class="alert alert-danger">Serve For  is empty or invalid at row - ' . $count . '</div>';
                return false;
            }
            $invalid_stock_unit = 0;
            foreach ($ids as $id) {
                if ($emapData[7] == $id['id']) {
                    $invalid_stock_unit = 0;
                }
            }
            if (empty($emapData[7]) || $invalid_stock_unit == 1) {
                echo '<p class="alert alert-danger">Stock Unit ID is empty or invalid at row - ' . $count . '</div>';
                return false;
            }
            if (empty($emapData[8])) {
                $emptydata = true;
                echo '<p class="alert alert-danger">Product ID is empty at row - ' . $count . '</div>';
                return false;
            }
            $sql = "SELECT id FROM products WHERE id=" . $emapData[8];
            $db->sql($sql);
            $result = $db->getResult();
            if (empty($result)) {
                echo '<p class="alert alert-danger">Product ID  is invalid at row - ' . $count . '</div>';
                return false;
            }
        }
        $count++;
    }
    fclose($file);
    $file = fopen($filename, "r");
    while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
        if ($count1 != 0) {
            $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // type
            $emapData[1] = trim($db->escapeString($fn->xss_clean($emapData[1]))); // measurement
            $emapData[2] = trim($db->escapeString($fn->xss_clean($emapData[2]))); // measurement unit id
            $emapData[3] = trim($db->escapeString($fn->xss_clean($emapData[3]))); // price
            $emapData[4] = trim($db->escapeString($fn->xss_clean($emapData[4]))); // discounted price
            $emapData[5] = trim($db->escapeString($fn->xss_clean($emapData[5]))); // serve for
            $emapData[6] = trim($db->escapeString($fn->xss_clean($emapData[6]))); // stock
            $emapData[7] = trim($db->escapeString($fn->xss_clean($emapData[7]))); // stock unit id
            $emapData[8] = trim($db->escapeString($fn->xss_clean($emapData[8]))); // product id
            $sql = "INSERT INTO product_variant (`product_id`,`type`,`measurement`,`measurement_unit_id`,`price`,`discounted_price`,`serve_for`,`stock`,`stock_unit_id`) VALUES ('" . $emapData[8] . "','" . $emapData[0] . "','" . $emapData[1] . "','" . $emapData[2] . "','" . $emapData[3] . "','" . $emapData[4] . "','" . $emapData[5] . "','" . $emapData[6] . "','" . $emapData[7] . "')";
            $db->sql($sql);
        }

        $count1++;
    }
    fclose($file);
    echo "<p class='alert alert-success'>CSV file is successfully imported!</p><br>";
    // } else {
    //     echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p><br>";
    // }
}

if (isset($_POST['bulk_update']) && $_POST['bulk_update'] == 1 && (isset($_POST['type']) && $_POST['type'] == 'variants')) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['products']['update'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update products.</label>";
        return false;
    }
    $count = 0;

    $count1 = 0;
    $filename = $_FILES["upload_file"]["tmp_name"];
    $error = false;
    $result = $fn->validate_image($_FILES["upload_file"], false);
    if ($result) {
        $error = true;
    }

    if ($_FILES["upload_file"]["size"] > 0  && $error == false) {
        $file = fopen($filename, "r");
        $emptydata = false;
        $invalid_price = false;
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count != 0) {
                $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // ID
                $emapData[1] = trim($db->escapeString($fn->xss_clean($emapData[1]))); // type
                $emapData[2] = trim($db->escapeString($fn->xss_clean($emapData[2]))); // measurement
                $emapData[3] = trim($db->escapeString($fn->xss_clean($emapData[3]))); // measurement unit id
                $emapData[4] = trim($db->escapeString($fn->xss_clean($emapData[4]))); // price
                $emapData[5] = trim($db->escapeString($fn->xss_clean($emapData[5]))); // discounted price
                $emapData[6] = trim($db->escapeString($fn->xss_clean($emapData[6]))); // serve for
                $emapData[7] = trim($db->escapeString($fn->xss_clean($emapData[7]))); // stock
                $emapData[8] = trim($db->escapeString($fn->xss_clean($emapData[8]))); // stock unit id
                $emapData[9] = trim($db->escapeString($fn->xss_clean($emapData[9]))); // product id

                if (empty($emapData[0])) {
                    echo '<p class="alert alert-danger">Variant ID  is empty at row - ' . $count . '</div>';
                    return false;
                }
                $sql = "SELECT * FROM product_variant WHERE id=" . $emapData[0];
                $db->sql($sql);
                $result = $db->getResult();
                if (empty($result)) {
                    echo '<p class="alert alert-danger">Variant ID  is invalid at row - ' . $count . '</div>';
                    return false;
                }


                if (!empty($emapData[1]) && $emapData[1] != 'packet' && $emapData[1] != 'loose') {
                    echo '<p class="alert alert-danger">Type  is invalid at row - ' . $count . '</div>';
                    return false;
                }

                $sql = "SELECT id FROM unit";
                $db->sql($sql);
                $ids = $db->getResult();
                if (!empty($emapData[3])) {
                    $invalid_measurement_unit = 1;
                    foreach ($ids as $id) {
                        if ($emapData[3] == $id['id']) {
                            $invalid_measurement_unit = 0;
                        }
                    }
                    if ($invalid_measurement_unit == 1) {
                        echo '<p class="alert alert-danger">Measurement Unit ID is invalid at row - ' . $count . '</div>';
                        return false;
                    }
                }

                if ($emapData[4] <= $emapData[5]) {
                    echo '<p class="alert alert-danger">Price is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[6]) && $emapData[6] != 'Available' && $emapData[6] != 'Sold Out') {
                    echo '<p class="alert alert-danger">Serve For  is invalid at row - ' . $count . '</div>';
                    return false;
                }

                if (!empty($emapData[8])) {
                    $invalid_stock_unit = 1;
                    foreach ($ids as $id) {
                        if ($emapData[8] == $id['id']) {
                            $invalid_stock_unit = 0;
                        }
                    }
                    if ($invalid_stock_unit == 1) {
                        echo '<p class="alert alert-danger">Stock Unit ID is invalid at row - ' . $count . '</div>';
                        return false;
                    }
                }
                if (!empty($emapData[9])) {
                    $sql = "SELECT id FROM products WHERE id=" . $emapData[9];
                    $db->sql($sql);
                    $result = $db->getResult();
                    if (empty($result)) {
                        echo '<p class="alert alert-danger">Product ID  is invalid at row - ' . $count . '</div>';
                        return false;
                    }
                }
            }
            $count++;
        }
        fclose($file);
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count1 != 0) {
                $emapData[0] = trim($db->escapeString($emapData[0])); // ID
                $sql = "SELECT * FROM product_variant WHERE id=" . $emapData[0];
                $db->sql($sql);
                $result = $db->getResult();
                $emapData[1] = !empty($emapData[1]) ? trim($db->escapeString($fn->xss_clean($emapData[1]))) : $result[0]['type']; // type
                $emapData[2] = !empty($emapData[2]) ? trim($db->escapeString($fn->xss_clean($emapData[2]))) : $result[0]['measurement']; // measurement
                $emapData[3] = !empty($emapData[3]) ? trim($db->escapeString($fn->xss_clean($emapData[3]))) : $result[0]['measurement_unit_id']; // measurement unit id
                $emapData[4] = !empty($emapData[4]) ? trim($db->escapeString($fn->xss_clean($emapData[4]))) : $result[0]['price']; // price
                $emapData[5] = $result[0]['discounted_price'] == 0 && !empty($emapData[5]) ? trim($db->escapeString($fn->xss_clean($emapData[5]))) : trim($db->escapeString($fn->xss_clean($emapData[5]))); // discounted price
                $emapData[6] = !empty($emapData[6]) ? trim($db->escapeString($fn->xss_clean($emapData[6]))) : $result[0]['serve_for']; // serve for
                $emapData[7] = !empty($emapData[7]) ? trim($db->escapeString($fn->xss_clean($emapData[7]))) : $result[0]['stock']; // stock
                $emapData[8] = !empty($emapData[8]) ? trim($db->escapeString($fn->xss_clean($emapData[8]))) : $result[0]['stock_unit_id']; // stock unit id
                $emapData[9] = !empty($emapData[9]) ? trim($db->escapeString($fn->xss_clean($emapData[9]))) : $result[0]['product_id']; // product id
                $sql = "UPDATE product_variant SET `product_id`='" . $emapData[9] . "',`type`='" . $emapData[1] . "',`measurement`='" . $emapData[2] . "',`measurement_unit_id`='" . $emapData[3] . "',`price`='" . $emapData[4] . "',`discounted_price`='" . $emapData[5] . "',`serve_for`='" . $emapData[6] . "',`stock`='" . $emapData[7] . "',`stock_unit_id`='" . $emapData[8] . "' WHERE id=" . $emapData[0];
                $db->sql($sql);
            }

            $count1++;
        }
        fclose($file);
        echo "<p class='alert alert-success'>CSV file is successfully imported!</p><br>";
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p><br>";
    }
}

if (isset($_GET['product_status']) && !empty($_GET['product_status']) && isset($_GET['type']) && !empty($_GET['type'])) {
    if (ALLOW_MODIFICATION == 0) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['products']['update'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update products.</label>";
        return false;
    }
    $type = $db->escapeString($fn->xss_clean($_GET['type']));
    $product_id = $db->escapeString($fn->xss_clean($_GET['id']));

    if ($type == 'deactive') {
        $sql = "UPDATE `products` SET `status`= 0 WHERE id = $product_id";
        if ($db->sql($sql)) {
            echo 1;
        } else {
            echo 0;
        }
    }
    if ($type == 'active') {
        $sql = "UPDATE `products` SET `status`= 1 WHERE id = $product_id";
        if ($db->sql($sql)) {
            echo 1;
        } else {
            echo 0;
        }
    }
}

if (isset($_POST['delete_media']) && !empty($_POST['id']) && $_POST['delete_media'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    $id     = $db->escapeString($fn->xss_clean($_POST['id']));
    $image  = $db->escapeString($fn->xss_clean($_POST['image']));
    if (!empty($image))
        unlink('../' . $image);

    $sql = "DELETE FROM `media` WHERE `id`='" . $id . "'";

    if ($db->sql($sql)) {
        echo 1;
        echo "<p class='alert alert-success'>Media Deleted successfully!</p><br>";
    } else {
        echo 0;
        echo "<p class='alert alert-success'>Media is not Deleted!</p><br>";
    }
}

if (isset($_POST['change_product'])) {
    if ($permissions['products']['read'] == 1) {
        if ($_POST['product_id'] == '') {
            $sql = "SELECT pv.*,u.short_code FROM product_variant pv LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id";
        } else {
            $product_id = $db->escapeString($fn->xss_clean($_POST['product_id']));
            $sql = "SELECT pv.*,u.short_code FROM product_variant pv LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id WHERE pv.product_id=" . $product_id;
        }
    } else {
        echo "<option value=''>--Select Product Variants--</option>";
        return false;
    }

    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row) {
            echo "<option value=" . $row['id'] . ">" . $row['measurement'] . " " . $row['short_code'] . "</option>";
        }
    } else {
        echo "<option value=''>--No Product Variants added--</option>";
    }
}

if (isset($_POST['change_price'])) {
    if ($permissions['products']['read'] == 1) {
        if ($_POST['product_variant_id'] == '') {
            $sql = "SELECT pv.*,u.short_code FROM product_variant pv LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id";
        } else {
            $product_variant_id = $db->escapeString($fn->xss_clean($_POST['product_variant_id']));
            $sql = "SELECT pv.*,u.short_code FROM product_variant pv LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id WHERE pv.id=" . $product_variant_id;
        }
    }

    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row) {
            echo $row['price'];
        }
    }
}

if (isset($_POST['change_discounted_price'])) {
    if ($permissions['products']['read'] == 1) {
        if ($_POST['product_variant_id'] == '') {
            $sql = "SELECT pv.*,u.short_code FROM product_variant pv LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id";
        } else {
            $product_variant_id = $db->escapeString($fn->xss_clean($_POST['product_variant_id']));
            $sql = "SELECT pv.*,u.short_code FROM product_variant pv LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id WHERE pv.id=" . $product_variant_id;
        }
    }

    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row) {
            echo $row['discounted_price'];
        }
    }
}

if (isset($_POST['get_units_of_products']) && $_POST['get_units_of_products'] != '') {
    $id = $db->escapeString($fn->xss_clean($_POST['unit_id']));
    $sql = "SELECT u.* FROM `unit` u  WHERE u.id=" . $id;
    $db->sql($sql);
    $res = $db->getResult();
    if ($res[0]['short_code'] == 'S' || $res[0]['short_code'] == 'M' || $res[0]['short_code'] == 'L' || $res[0]['short_code'] == 'XL') {
        echo 1;
    } else {
        echo 2;
    }
}


if (isset($_POST['bulk_uploads']) && $_POST['bulk_uploads'] == 1 && (isset($_POST['type']) && $_POST['type'] == 'products')) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['products']['create'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to upload products.</label>";
        return false;
    }
    $count = 0;
    $count1 = 0;
    $error = false;
    $filename = $_FILES["upload_file"]["tmp_name"];

    $result = $fn->validate_image($_FILES["upload_file"], false);
    if ($result) {
        $error = true;
    }
    $allowed_status = array("received", "processed", "shipped");
    if ($_FILES["upload_file"]["size"] > 0  && $error == false) {
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {

            if ($count != 0) {
                $emapData[0] = trim($db->escapeString($emapData[0])); // product name
                $emapData[1] = trim($db->escapeString($emapData[1])); // category id
                $emapData[2] = trim($db->escapeString($emapData[2])); // subcategory id
                $emapData[3] = trim($db->escapeString($emapData[3])); // indicator
                $emapData[4] = trim($db->escapeString($emapData[4])); // manufacturer
                $emapData[5] = trim($db->escapeString($emapData[5])); // made in
                $emapData[6] = trim($db->escapeString($emapData[6])); // return status
                $emapData[7] = trim($db->escapeString($emapData[7])); // cancel status
                $emapData[8] = trim($db->escapeString($emapData[8])); // till status
                $emapData[9] = trim($db->escapeString($emapData[9])); // description
                $emapData[10] = trim($db->escapeString($emapData[10])); // image
                $emapData[11] = trim($db->escapeString($emapData[11])); // tax id

                $emapData[12] = trim($db->escapeString($emapData[12])); // type
                $emapData[13] = trim($db->escapeString($emapData[13])); // Measurement
                $emapData[14] = trim($db->escapeString($emapData[14])); // Measurement Unit ID
                $emapData[15] = trim($db->escapeString($emapData[15])); // Price
                $emapData[16] = trim($db->escapeString($emapData[16])); // Discounted Price
                $emapData[17] = trim($db->escapeString($emapData[17])); // Serve For
                $emapData[18] = trim($db->escapeString($emapData[18])); // Stock
                $emapData[19] = trim($db->escapeString($emapData[19])); // Stock Unit ID

                if (empty($emapData[0])) {
                    echo '<p class="alert alert-danger">Product Name  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[1])) {
                    echo '<p class="alert alert-danger">Category ID  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[2])) {
                    echo '<p class="alert alert-danger">Subcategory ID  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }

                if (!empty($emapData[6]) && $emapData[6] != 1) {
                    echo '<p class="alert alert-danger">Is Returnable is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[7]) && $emapData[7] != 1) {
                    echo '<p class="alert alert-danger">Is cancel-able is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[7]) && $emapData[7] == 1 && (empty($emapData[8]) || !in_array($emapData[8], $allowed_status))) {
                    echo '<p class="alert alert-danger">Till status is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[7]) && !(empty($emapData[8]))) {
                    echo '<p class="alert alert-danger">Till status is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[9])) {
                    echo '<p class="alert alert-danger">Description  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[10])) {
                    echo '<p class="alert alert-danger">Image  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[12]) || ($emapData[12] != 'packet' && $emapData[12] != 'loose')) {
                    echo '<p class="alert alert-danger">Type  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[13]) || !is_numeric($emapData[13])) {
                    echo '<p class="alert alert-danger">Measurement  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                $sql = "SELECT id FROM unit";
                $db->sql($sql);
                $ids = $db->getResult();
                $invalid_measurement_unit = 1;
                foreach ($ids as $id) {
                    if ($emapData[14] == $id['id']) {
                        $invalid_measurement_unit = 0;
                    }
                }
                if (empty($emapData[14]) || $invalid_measurement_unit == 1) {
                    echo '<p class="alert alert-danger">Measurement Unit ID is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[15]) || $emapData[15] <= $emapData[16]) {
                    echo '<p class="alert alert-danger">Price is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[17]) || ($emapData[17] != 'Available' && $emapData[17] != 'Sold Out')) {
                    echo '<p class="alert alert-danger">Serve For  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                $invalid_stock_unit = 1;
                foreach ($ids as $id) {
                    if ($emapData[19] == $id['id']) {
                        $invalid_stock_unit = 0;
                    }
                }
                if (empty($emapData[19]) || $invalid_stock_unit == 1) {
                    echo '<p class="alert alert-danger">Stock Unit ID is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
            }
            $count++;
        }
        fclose($file);
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count1 != 0) {
                $emapData[0] = trim($db->escapeString($emapData[0])); // product name
                $emapData[1] = trim($db->escapeString($emapData[1])); // category id
                $emapData[2] = trim($db->escapeString($emapData[2])); // subcategory id
                $emapData[3] = trim($db->escapeString($emapData[3])); // indicator
                $emapData[4] = trim($db->escapeString($emapData[4])); // manufacturer
                $emapData[5] = trim($db->escapeString($emapData[5])); // made in
                $emapData[6] = !empty($emapData[6]) ? trim($db->escapeString($emapData[6])) : 0; // return status
                $emapData[7] = !empty($emapData[7]) ? trim($db->escapeString($emapData[7])) : 0; // cancel status
                $emapData[8] = trim($db->escapeString($emapData[8])); // till status
                $emapData[9] = trim($db->escapeString($emapData[9])); // description
                $emapData[10] = trim($db->escapeString($emapData[10])); // image
                $emapData[11] = trim($db->escapeString($emapData[11])); // tax id

                $emapData[12] = trim($db->escapeString($emapData[12])); // type
                $emapData[13] = trim($db->escapeString($emapData[13])); // Measurement
                $emapData[14] = trim($db->escapeString($emapData[14])); // Measurement Unit ID
                $emapData[15] = trim($db->escapeString($emapData[15])); // Price
                $emapData[16] = trim($db->escapeString($emapData[16])); // Discounted Price
                $emapData[17] = trim($db->escapeString($emapData[17])); // Serve For
                $emapData[18] = trim($db->escapeString($emapData[18])); // Stock
                $emapData[19] = trim($db->escapeString($emapData[19])); // Stock Unit ID
                $slug = $function->slugify($emapData[0]);
                $data = array(
                    'name' => $emapData[0],
                    'slug' => $slug,
                    'category_id' => $emapData[1],
                    'subcategory_id' => $emapData[2],
                    'indicator' => $emapData[3],
                    'manufacturer' => $emapData[4],
                    'made_in' => $emapData[5],
                    'return_status' => $emapData[6],
                    'cancelable_status' => $emapData[7],
                    'till_status' => $emapData[8],
                    'description' => $emapData[9],
                    'image' => $emapData[10],
                    'tax_id' => $emapData[11]
                );
                $total = (count($emapData) / 9);
                $db->insert('products', $data);
                $res = $db->getResult();

                if ($total > 3) {
                    $index = 11;
                    for ($i = 0; $i < ($total - 2); $i++) {
                        $data = array(
                            'product_id' => $res[0],
                            'type' => $emapData[++$index],
                            'measurement' => $emapData[++$index],
                            'measurement_unit_id' => $emapData[++$index],
                            'price' => $emapData[++$index],
                            'discounted_price' => $emapData[++$index],
                            'serve_for' => $emapData[++$index],
                            'stock' => $emapData[++$index],
                            'stock_unit_id' => $emapData[++$index]
                        );
                        $db->insert('product_variant', $data);
                        $res1 = $db->getResult();
                    }
                } else {
                    $data = array(
                        'product_id' => $res[0],
                        'type' => $emapData[12],
                        'measurement' => $emapData[13],
                        'measurement_unit_id' => $emapData[14],
                        'price' => $emapData[15],
                        'discounted_price' => $emapData[16],
                        'discounted_price' => $emapData[16],
                        'serve_for' => $emapData[17],
                        'stock' => $emapData[18],
                        'stock_unit_id' => $emapData[19]
                    );
                    $db->insert('product_variant', $data);
                    $res1 = $db->getResult();
                }
            }

            $count1++;
        }
        fclose($file);
        echo "<p class='alert alert-success'>CSV file is successfully imported!</p><br>";
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p><br>";
    }
}
if (isset($_POST['bulk_updates']) && $_POST['bulk_updates'] == 1 && (isset($_POST['type']) && $_POST['type'] == 'products')) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['products']['create'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to upload products.</label>";
        return false;
    }
    $count = 0;
    $count1 = 0;
    $error = false;
    $filename = $_FILES["upload_file"]["tmp_name"];

    $result = $fn->validate_image($_FILES["upload_file"], false);
    if ($result) {
        $error = true;
    }
    $allowed_status = array("received", "processed", "shipped");
    if ($_FILES["upload_file"]["size"] > 0  && $error == false) {
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {

            if ($count != 0) {
                $emapData[0] = trim($db->escapeString($emapData[0])); // product name
                $emapData[1] = trim($db->escapeString($emapData[1])); // category id
                $emapData[2] = trim($db->escapeString($emapData[2])); // subcategory id
                $emapData[3] = trim($db->escapeString($emapData[3])); // indicator
                $emapData[4] = trim($db->escapeString($emapData[4])); // manufacturer
                $emapData[5] = trim($db->escapeString($emapData[5])); // made in
                $emapData[6] = trim($db->escapeString($emapData[6])); // return status
                $emapData[7] = trim($db->escapeString($emapData[7])); // cancel status
                $emapData[8] = trim($db->escapeString($emapData[8])); // till status
                $emapData[9] = trim($db->escapeString($emapData[9])); // description
                $emapData[10] = trim($db->escapeString($emapData[10])); // image
                $emapData[11] = trim($db->escapeString($emapData[11])); // tax id

                $emapData[12] = trim($db->escapeString($emapData[12])); // type
                $emapData[13] = trim($db->escapeString($emapData[13])); // Measurement
                $emapData[14] = trim($db->escapeString($emapData[14])); // Measurement Unit ID
                $emapData[15] = trim($db->escapeString($emapData[15])); // Price
                $emapData[16] = trim($db->escapeString($emapData[16])); // Discounted Price
                $emapData[17] = trim($db->escapeString($emapData[17])); // Serve For
                $emapData[18] = trim($db->escapeString($emapData[18])); // Stock
                $emapData[19] = trim($db->escapeString($emapData[19])); // Stock Unit ID

                if (empty($emapData[0])) {
                    echo '<p class="alert alert-danger">Product Name  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[1])) {
                    echo '<p class="alert alert-danger">Category ID  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[2])) {
                    echo '<p class="alert alert-danger">Subcategory ID  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }

                if (!empty($emapData[6]) && $emapData[6] != 1) {
                    echo '<p class="alert alert-danger">Is Returnable is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[7]) && $emapData[7] != 1) {
                    echo '<p class="alert alert-danger">Is cancel-able is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[7]) && $emapData[7] == 1 && (empty($emapData[8]) || !in_array($emapData[8], $allowed_status))) {
                    echo '<p class="alert alert-danger">Till status is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[7]) && !(empty($emapData[8]))) {
                    echo '<p class="alert alert-danger">Till status is invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[9])) {
                    echo '<p class="alert alert-danger">Description  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[10])) {
                    echo '<p class="alert alert-danger">Image  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[12]) || ($emapData[12] != 'packet' && $emapData[12] != 'loose')) {
                    echo '<p class="alert alert-danger">Type  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[13]) || !is_numeric($emapData[13])) {
                    echo '<p class="alert alert-danger">Measurement  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                $sql = "SELECT id FROM unit";
                $db->sql($sql);
                $ids = $db->getResult();
                $invalid_measurement_unit = 1;
                foreach ($ids as $id) {
                    if ($emapData[14] == $id['id']) {
                        $invalid_measurement_unit = 0;
                    }
                }
                if (empty($emapData[14]) || $invalid_measurement_unit == 1) {
                    echo '<p class="alert alert-danger">Measurement Unit ID is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[15]) || $emapData[15] <= $emapData[16]) {
                    echo '<p class="alert alert-danger">Price is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[17]) || ($emapData[17] != 'Available' && $emapData[17] != 'Sold Out')) {
                    echo '<p class="alert alert-danger">Serve For  is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
                $invalid_stock_unit = 1;
                foreach ($ids as $id) {
                    if ($emapData[19] == $id['id']) {
                        $invalid_stock_unit = 0;
                    }
                }
                if (empty($emapData[19]) || $invalid_stock_unit == 1) {
                    echo '<p class="alert alert-danger">Stock Unit ID is empty or invalid at row - ' . $count . '</div>';
                    return false;
                }
            }
            $count++;
        }
        fclose($file);
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count1 != 0) {
                $emapData[0] = trim($db->escapeString($emapData[0])); // product name
                $emapData[1] = trim($db->escapeString($emapData[1])); // category id
                $emapData[2] = trim($db->escapeString($emapData[2])); // subcategory id
                $emapData[3] = trim($db->escapeString($emapData[3])); // indicator
                $emapData[4] = trim($db->escapeString($emapData[4])); // manufacturer
                $emapData[5] = trim($db->escapeString($emapData[5])); // made in
                $emapData[6] = !empty($emapData[6]) ? trim($db->escapeString($emapData[6])) : 0; // return status
                $emapData[7] = !empty($emapData[7]) ? trim($db->escapeString($emapData[7])) : 0; // cancel status
                $emapData[8] = trim($db->escapeString($emapData[8])); // till status
                $emapData[9] = trim($db->escapeString($emapData[9])); // description
                $emapData[10] = trim($db->escapeString($emapData[10])); // image
                $emapData[11] = trim($db->escapeString($emapData[11])); // tax id

                $emapData[12] = trim($db->escapeString($emapData[12])); // type
                $emapData[13] = trim($db->escapeString($emapData[13])); // Measurement
                $emapData[14] = trim($db->escapeString($emapData[14])); // Measurement Unit ID
                $emapData[15] = trim($db->escapeString($emapData[15])); // Price
                $emapData[16] = trim($db->escapeString($emapData[16])); // Discounted Price
                $emapData[17] = trim($db->escapeString($emapData[17])); // Serve For
                $emapData[18] = trim($db->escapeString($emapData[18])); // Stock
                $emapData[19] = trim($db->escapeString($emapData[19])); // Stock Unit ID
                $slug = $function->slugify($emapData[0]);
                $data = array(
                    'name' => $emapData[0],
                    'slug' => $slug,
                    'category_id' => $emapData[1],
                    'subcategory_id' => $emapData[2],
                    'indicator' => $emapData[3],
                    'manufacturer' => $emapData[4],
                    'made_in' => $emapData[5],
                    'return_status' => $emapData[6],
                    'cancelable_status' => $emapData[7],
                    'till_status' => $emapData[8],
                    'description' => $emapData[9],
                    'image' => $emapData[10],
                    'tax_id' => $emapData[11]
                );
                $db->insert('products', $data);
                $res = $db->getResult();
                $total = (count($emapData) / 8);
                if ($total > 3) {
                    $index = 11;
                    for ($i = 0; $i < ($total - 2); $i++) {
                        $data = array(
                            'product_id' => $res[0],
                            'type' => $emapData[++$index],
                            'measurement' => $emapData[++$index],
                            'measurement_unit_id' => $emapData[++$index],
                            'price' => $emapData[++$index],
                            'discounted_price' => $emapData[++$index],
                            'serve_for' => $emapData[++$index],
                            'stock' => $emapData[++$index],
                            'stock_unit_id' => $emapData[++$index]
                        );
                        $db->insert('product_variant', $data);
                        $res1 = $db->getResult();
                    }
                } else {
                    $data = array(
                        'product_id' => $res[0],
                        'type' => $emapData[12],
                        'measurement' => $emapData[13],
                        'measurement_unit_id' => $emapData[14],
                        'price' => $emapData[15],
                        'discounted_price' => $emapData[16],
                        'serve_for' => $emapData[17],
                        'stock' => $emapData[18],
                        'stock_unit_id' => $emapData[19]
                    );
                    $db->insert('product_variant', $data);
                    $res1 = $db->getResult();
                }
            }

            $count1++;
        }
        fclose($file);
        echo "<p class='alert alert-success'>CSV file is successfully imported!</p><br>";
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p><br>";
    }
}

if (isset($_GET['status']) && !empty($_GET['status']) && isset($_GET['type']) && !empty($_GET['type'])) {
    if (ALLOW_MODIFICATION == 0) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['customers']['read'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update users.</label>";
        return false;
    }
    $type = $db->escapeString($fn->xss_clean($_GET['type']));
    $user_id = $db->escapeString($fn->xss_clean($_GET['id']));

    if ($type == 'deactive') {
        $sql = "UPDATE `users` SET `status`= 0 WHERE id = $user_id";
        if ($db->sql($sql)) {
            echo 1;
        } else {
            echo 0;
        }
    }
    if ($type == 'active') {
        $sql = "UPDATE `users` SET `status`= 1 WHERE id = $user_id";
        if ($db->sql($sql)) {
            echo 1;
        } else {
            echo 0;
        }
    }
}

if (isset($_POST['update_bank_transfer']) && $_POST['update_bank_transfer'] == 1) {
    $message = isset($_POST['message']) && !empty($_POST['message']) ? $db->escapeString($fn->xss_clean($_POST['message'])) : "";
    $status = isset($_POST['status']) && !empty($_POST['status']) ? $db->escapeString($fn->xss_clean($_POST['status'])) : "";
    $order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));

    $sql = "SELECT * FROM `order_bank_transfers` WHERE order_id=" . $order_id;
    $db->sql($sql);
    $res = $db->getResult();
    if ($res[0]['status'] == 0) {
        $atta_status = 'Pending';
    } elseif ($res[0]['status'] == 1) {
        $atta_status = 'Accepted';
    } elseif ($res[0]['status'] == 2) {
        $atta_status = 'Rejected';
    }

    if (!empty($_POST['status']) && $status == 0 && $status != '') {
        echo "<label class='alert alert-danger'>status already Accepted.</label>";
        return false;
    }

    if (($res[0]['status'] == 0 && $status == 0) || ($res[0]['status'] == 1 && $status == 1) || ($res[0]['status'] == 2 && $status == 2)) {
        echo  "<label class='alert alert-danger'>status already $atta_status. </label>";
        return false;
    }

    if ($res[0]['status'] < $status) {
        if (!empty($message)) {
            $sql_query = "update order_bank_transfers set `message`='" . $message . "',`status`='" . $status . "' where `order_id`=" . $order_id;
        } else {
            $sql_query = "update order_bank_transfers set `status`='" . $status . "' where `order_id`=" . $order_id;
        }

        if ($db->sql($sql_query)) {
            echo "<label class='alert alert-success'>Bank Transfer Details Updated successfully.</label>";
        } else {
            echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
        }
    } else {
        echo "<label class='alert alert-danger'>Some Error Occurred! Please Try Again.</label>";
    }
}

if (isset($_POST['get_category_id_by_product_id']) && $_POST['get_category_id_by_product_id'] == 1) {
    if ($_POST['category_id'] == '') {
        $sql = "SELECT id,name from `products` where `status` = 1 order by id desc";
    } else {
        $category_ids = $db->escapeString($fn->xss_clean($_POST['category_id']));
        $sql = "SELECT id,name from `products` where `status` = 1 AND category_id IN( $category_ids ) order by id desc";
    }
    $db->sql($sql);
    $res = $db->getResult();
    $selected = $_POST['selected'];
    $selected = explode(",", $selected);

    for ($i = 0; $i < count($selected); $i++) {
        $selected[$i] = trim($selected[$i]);
    }

    if (!empty($res)) {
        foreach ($res as $row) {
            if (in_array($row['id'], $selected)) {
                echo "<option value=" . $row['id'] . " selected >" . $row['name'] . "</option>";
            } else {
                echo "<option value=" . $row['id'] . " >" . $row['name'] . "</option>";
            }
        }
    }
}

if (isset($_POST['location_bulk_uploads']) && $_POST['location_bulk_uploads'] == 1 && (isset($_POST['type']) && $_POST['type'] == 'cities')) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['locations']['create'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to upload products.</label>";
        return false;
    }

    $count = 0;
    $count1 = 0;
    $error = false;
    $filename = $_FILES["upload_file"]["tmp_name"];

    if ($_FILES["upload_file"]["size"] > 0  && $error == false) {
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count != 0) {
                $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // city name

                if (empty($emapData[0])) {
                    echo '<p class="alert alert-danger">City Name  is empty at row - ' . $count . '</div>';
                    return false;
                }
            }
            $count++;
        }
        fclose($file);
        $file = fopen($filename, "r");

        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count1 != 0) {
                $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // city name

                $sql = "INSERT INTO city (`name`) VALUES ('" . $emapData[0] . "')";
                $db->sql($sql);
            }
            $count1++;
        }
        fclose($file);
        echo "<p class='alert alert-success'>CSV file is successfully imported!</p><br>";
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p><br>";
    }
}

if (isset($_POST['location_bulk_uploads']) && $_POST['location_bulk_uploads'] == 1 && (isset($_POST['type']) && $_POST['type'] == 'areas')) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['locations']['create'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to upload products.</label>";
        return false;
    }

    $count = 0;
    $count1 = 0;
    $error = false;
    $filename = $_FILES["upload_file"]["tmp_name"];

    if ($_FILES["upload_file"]["size"] > 0  && $error == false) {
        $file = fopen($filename, "r");
        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count != 0) {
                $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // area name
                $emapData[1] = trim($db->escapeString($fn->xss_clean($emapData[1]))); // city id
                $emapData[2] = trim($db->escapeString($fn->xss_clean($emapData[2]))); // minimum_free_delivery_order_amount
                $emapData[3] = trim($db->escapeString($fn->xss_clean($emapData[3]))); //minimum_order_amount
                $emapData[4] = trim($db->escapeString($fn->xss_clean($emapData[4]))); //delivery_charges

                if (empty($emapData[0])) {
                    echo '<p class="alert alert-danger">Area Name  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[1])) {
                    echo '<p class="alert alert-danger">City Id  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (!empty($emapData[1])) {
                    $city = $fn->get_data($columns = ['name'], "id=" . $emapData[1], 'city');
                    if (empty($city)) {
                        echo '<p class="alert alert-danger">City is not exist check the city_id at row - ' . $count . '</div>';
                        return false;
                    }
                }
                if (empty($emapData[2])) {
                    echo '<p class="alert alert-danger">Minimum Free Delivery Order Amount  is empty at row - ' . $count . '</div>';
                    return false;
                }
                if (empty($emapData[4])) {
                    echo '<p class="alert alert-danger">Delivery Charges  is empty at row - ' . $count . '</div>';
                    return false;
                }
            }
            $count++;
        }
        fclose($file);
        $file = fopen($filename, "r");

        while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {
            if ($count1 != 0) {
                $emapData[0] = trim($db->escapeString($fn->xss_clean($emapData[0]))); // Area Name
                $emapData[1] = trim($db->escapeString($fn->xss_clean($emapData[1]))); // City Id
                $emapData[2] = trim($db->escapeString($fn->xss_clean($emapData[2]))); // minimum_free_delivery_order_amount
                $emapData[3] = trim($db->escapeString($fn->xss_clean($emapData[3]))); // minimum_order_amount
                $emapData[4] = trim($db->escapeString($fn->xss_clean($emapData[4]))); // delivery_charges

                $sql = "INSERT INTO area (`name`,`city_id`,`minimum_free_delivery_order_amount`,`minimum_order_amount`,`delivery_charges`) VALUES ('" . $emapData[0] . "','" . $emapData[1] . "','" . $emapData[2] . "','" . $emapData[3] . "','" . $emapData[4] . "')";
                $db->sql($sql);
            }
            $count1++;
        }
        fclose($file);
        echo "<p class='alert alert-success'>CSV file is successfully imported!</p><br>";
    } else {
        echo "<p class='alert alert-danger'>Invalid file format! Please upload data in CSV file!</p><br>";
    }
}

if (isset($_GET['offer_status']) && !empty($_GET['offer_status']) && isset($_GET['type']) && !empty($_GET['type'])) {
    if (ALLOW_MODIFICATION == 0) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['new_offers']['read'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update offers.</label>";
        return false;
    }
    $type = $db->escapeString($fn->xss_clean($_GET['type']));
    $id = $db->escapeString($fn->xss_clean($_GET['id']));

    if ($type == 'deactive') {
        $sql = "UPDATE `offers` SET `status`= 0 WHERE id = $id";
        if ($db->sql($sql)) {
            echo 1;
        } else {
            echo 0;
        }
    }
    if ($type == 'active') {
        $sql = "UPDATE `offers` SET `status`= 1 WHERE id = $id";
        if ($db->sql($sql)) {
            echo 1;
        } else {
            echo 0;
        }
    }
}

if (isset($_GET['slider_status']) && !empty($_GET['slider_status']) && isset($_GET['type']) && !empty($_GET['type'])) {
    if (ALLOW_MODIFICATION == 0) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['home_sliders']['read'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update Slider.</label>";
        return false;
    }
    $type = $db->escapeString($fn->xss_clean($_GET['type']));
    $id = $db->escapeString($fn->xss_clean($_GET['id']));

    if ($type == 'deactive') {
        $sql = "UPDATE `slider` SET `status`= 0 WHERE id = $id";
        if ($db->sql($sql)) {
            echo 1;
        } else {
            echo 0;
        }
    }
    if ($type == 'active') {
        $sql = "UPDATE `slider` SET `status`= 1 WHERE id = $id";
        if ($db->sql($sql)) {
            echo 1;
        } else {
            echo 0;
        }
    }
}

if (isset($_POST['boy_id']) && isset($_POST['cash_collection']) && !empty($_POST['cash_collection'])) {
    if (ALLOW_MODIFICATION == 0) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }
    if ($permissions['delivery_boys']['update'] == 0) {
        echo "<label class='alert alert-danger'>You have no permission to update delivery boy.</label>";
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_POST['boy_id']));
    $cash = $fn->get_data($columns = ['cash_received'], "id='" . $id . "'", 'delivery_boys');
    if (isset($cash[0]['cash_received'])) {
        $cash = $cash[0]['cash_received'];
        if (!is_numeric($_POST['amount'])) {
            echo "<label class='alert alert-danger'>Amount must be number.</label>";
            return false;
        }
        if ($_POST['amount'] > $cash) {
            echo "<label class='alert alert-danger'>Amount must be not be greater than cash.</label>";
            return false;
        }
        $amount = $db->escapeString($fn->xss_clean($_POST['amount']));
        $date = $db->escapeString($fn->xss_clean($_POST['date']));

        $message = (!empty($_POST['message'])) ? $db->escapeString($fn->xss_clean($_POST['message'])) : 'Delivery boy cash collection by admin';
        $cash = $cash - $amount;
        $sql = "Update delivery_boys set `cash_received`='" . $cash . "' where `id`=" . $id;
        $db->sql($sql);
        $fn->add_transaction("", $id, 'delivery_boy_cash_collection', $amount, $message, $date);
        echo "<p class='alert alert-success'>Cash collected Successfully!</p>";
    } else {
        echo "<label class='alert alert-danger'>Something went wrong!.</label>";
        return false;
    }
}

if (isset($_POST['add_ticket_types']) && $_POST['add_ticket_types'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }

    $type = $db->escapeString($fn->xss_clean($_POST['add_title']));

    $sql = "INSERT INTO ticket_type (`type`) VALUES ('$type')";
    if ($db->sql($sql)) {
        echo "<label class='alert alert-success'>Ticket Type Added Successfully!</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occrred! please try again.</label>";
    }
}

if (isset($_POST['edit_ticket_types']) && $_POST['edit_ticket_types'] == 1) {
    if (!checkadmin($auth_username)) {
        echo "<label class='alert alert-danger'>Access denied - You are not authorized to access this page.</label>";
        return false;
    }

    $type = $db->escapeString($fn->xss_clean($_POST['edit_title']));
    $id = $db->escapeString($fn->xss_clean($_POST['id']));

    $sql = "UPDATE ticket_type SET `type`= '$type' WHERE id= " . $id;
    if ($db->sql($sql)) {
        echo "<label class='alert alert-success'>Ticket Type Updated Successfully!</label>";
    } else {
        echo "<label class='alert alert-danger'>Some Error Occrred! please try again.</label>";
    }
}

// Delete tickrt types
if (isset($_GET['delete_ticket_types']) && $_GET['delete_ticket_types'] == 1) {
    if ($permissions['return_requests']['delete'] == 0) {
        echo 2;
        return false;
    }
    $id = $db->escapeString($_GET['id']);
    $sql = "DELETE FROM `ticket_type` WHERE id=" . $id;
    if ($db->sql($sql)) {
        echo 0;
    } else {
        echo 1;
    }
}

// Change status of ticket
if ((isset($_POST['change_ticket_status'])) && ($_POST['change_ticket_status'] == 1)) {

    $ticket_id = $db->escapeString($_POST['ticket_id']);
    $status = $db->escapeString($_POST['change_status']);

    $sql = "UPDATE `tickets` SET status = '" . $status . "' WHERE id = " . $ticket_id;
    $db->sql($sql);
}

// Load Ticket messages here 
if (isset($_POST['get_complaint_comments']) && ($_POST['get_complaint_comments'] == 1)) {
    $ticket_id = $db->escapeString($fn->xss_clean($_POST['ticket_id']));

    $where = "";
    if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
        $user_id = $db->escapeString($fn->xss_clean($_POST['user_id']));
        $where .= " AND user_id = $user_id";
    }
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $db->escapeString($fn->xss_clean($_POST['id']));
        $where .= " AND tm.id = $id";
    }
    $offset = (isset($_POST['offset']) && !empty($fn->xss_clean($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;
    $limit = (isset($_POST['limit']) && !empty($fn->xss_clean($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;

    $sort = (isset($_POST['sort']) && !empty($fn->xss_clean($_POST['sort']))) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : 'tm.id';
    $order = (isset($_POST['order']) && !empty($fn->xss_clean($_POST['order']))) ? $db->escapeString($fn->xss_clean($_POST['order'])) : 'DESC';

    if (isset($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        $where .= " AND (t.`title` like '%" . $search . "%' OR tm.`message` like '%" . $search . "%' )";
    }

    if (isset($_POST['ticket_id']) && !empty($_POST['ticket_id'])) {
        $sql = "SELECT count(tm.id) as total FROM `ticket_messages` tm left join tickets t on t.id=tm.ticket_id left join users u on u.id=t.user_id Where tm.ticket_id = $ticket_id" . $where;
        $db->sql($sql);
        $res = $db->getResult();
        $total = $res[0]['total'];

        $sql = "select tm.*,case when tm.type='admin' then a.username else u.name end as username from ticket_messages tm left join admin a on a.id = tm.user_id
        left join users u on u.id = tm.user_id Where tm.ticket_id = $ticket_id " . $where . " ORDER BY $sort $order LIMIT $offset,$limit";
        // echo $sql;
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            foreach ($res as $row) {
                $row['username'] = (!empty($row['username'])) ? $row['username'] : "";
                $tempRow['id'] = $row['id'];
                $tempRow['type'] = $row['type'];
                $tempRow['user_id'] = $row['user_id'];
                $tempRow['username'] = $row['username'];
                $tempRow['ticket_id'] = $row['ticket_id'];
                $tempRow['message'] = $row['message'];
                $tempRow['date_created'] = $row['date_created'];
                $tempRow['attachments'] = (!empty($row['attachments'])) ? $row['attachments'] : '';
                $rows[] = $tempRow;
            }
            $response['error'] = false;
            $response['message'] = 'Messages Retrived Successfully!';
            $response['total'] = $total;
            $response['data'] = $rows;
        } else {
            $response['error'] = true;
            $response['message'] = 'Data not Found!';
            $response['total'] = $total;
            $response['data'] = [];
        }
        print_r(json_encode($response));
    }
}

// send message
if (isset($_POST['send']) && ($_POST['send'] == 1)) {
    if (isset($_POST['message']) && $_POST['message'] != "") {
        $type = $_POST['type'];
        $user_id = $_POST['type_id'];
        $ticket_id = $_POST['ticket_id'];
        $message = $_POST['message'];
        $date_created = date('Y-m-d H:m:s');
    }

    $sql = "INSERT INTO ticket_messages (ticket_id,message,type,user_id,date_created) VALUES ($ticket_id, '$message','$type','$user_id','$date_created')";
    $result = $db->sql($sql);
    $row1 = $db->getResult();


    // get send message data
    $res = $fn->get_data('', '', 'ticket_messages', '', '', 'ORDER BY id DESC', 'LIMIT 0,1');
    $res[0]['last_updated'] = !empty($res[0]['last_updated']) ? $res[0]['last_updated'] : "";
    $res[0]['attachments'] = json_decode($res[0]['attachments'], 1);
    $res[0]['attachments'] = (empty($res[0]['attachments'])) ? array() : $res[0]['attachments'];
    for ($j = 0; $j < count($res[0]['attachments']); $j++) {
        $res[0]['attachments'][$j] = !empty(DOMAIN_URL . $res[0]['attachments'][$j]) ? DOMAIN_URL . $res[0]['attachments'][$j] : "";
    }
    $message_res = json_encode($res);

    // get ticket data
    $title = $fn->get_data('', 'id=' . $ticket_id, 'tickets');
    $t_id = $title[0]['id'];
    $ticket_title = $title[0]['title'];
    $u_id = $title[0]['user_id'];

    // send notification to user
    $fn->send_notification_to_user('Customer Support', $message, 'customer_notification', $u_id, $t_id, $message_res);

    echo json_encode($row1);
}
