<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include('../includes/crud.php');
include('../includes/variables.php');
include_once('verify-token.php');
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
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
}

/* 
-------------------------------------------
APIs for eCart
-------------------------------------------
1. get_payment_methods
2. get_privacy
3. get_terms
4. get_logo
5. get_contact
6. get_about_us
7. get_timezone
8. get_fcm_key
9. get_time_slot_config
10.get_front_end_settings
11.get_time_slots
12.all
-------------------------------------------
-------------------------------------------
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

if (!verify_token()) {
    return false;
}

$settings = $setting = array();

if (isset($_POST['settings']) && $_POST['settings'] == 1) {
    if (isset($_POST['get_payment_methods']) && $_POST['get_payment_methods'] == 1) {
        /*
        1. get_payment_methods
            accesskey:90336
            settings:1
            get_payment_methods:1
        */
        $sql = "select value from `settings` where `variable`='payment_methods'";
        $db->sql($sql);
        $res = $db->getResult();

        if (!empty($res)) {
            $res[0]['value'] = preg_replace('/\r|\n/', '\n', trim($res[0]['value']));
            $payment_methods =  json_decode($res[0]['value'], true);

            if (!isset($payment_methods['paytm_payment_method'])) {
                $payment_methods->paytm_payment_method = 0;
                $payment_methods->paytm_mode = "sandbox";
                $payment_methods->paytm_merchant_key = "";
                $payment_methods->paytm_merchant_id = "";
            }
            if (!isset($payment_methods['ssl_method'])) {
                $payment_methods->ssl_method = 0;
                $payment_methods->ssl_mode = "sandbox";
                $payment_methods->ssl_store_id = "";
                $payment_methods->ssl_store_password = "";
            }
            if (!isset($payment_methods['direct_bank_transfer_method'])) {
                $payment_methods->direct_bank_transfer_method = 0;
                $payment_methods->account_name = "";
                $payment_methods->account_number = "";
                $payment_methods->bank_name = "";
                $payment_methods->bank_code = "";
                $payment_methods->notes = "";
            }
            $settings['error'] = false;
            $settings['payment_methods'] = $payment_methods;
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_privacy']) && $_POST['get_privacy'] == 1) {
        /*
        2. get_privacy
            accesskey:90336
            settings:1
            get_privacy:1
        */
        $sql = "select value from `settings` where variable='privacy_policy'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['privacy'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_terms']) && $_POST['get_terms'] == 1) {
        /*
        3. get_terms
            accesskey:90336
            settings:1
            get_terms:1
        */
        $sql = "select value from `settings` where variable='terms_conditions'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['terms'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_logo']) && $_POST['get_logo'] == 1) {
        /*
        4. get_logo
            accesskey:90336
            settings:1
            get_logo:1
        */
        $sql = "select value from `settings` where variable='Logo' OR variable='logo'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['logo'] = DOMAIN_URL . $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_contact']) && $_POST['get_contact'] == 1) {
        /*
        5. get_contact
            accesskey:90336
            settings:1
            get_contact:1
        */
        $sql = "select value from `settings` where variable='contact_us'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['contact'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_about_us']) && $_POST['get_about_us'] == 1) {
        /*
        6. get_about_us
            accesskey:90336
            settings:1
            get_about_us:1
        */
        $sql = "select value from `settings` where variable='about_us'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['about'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_timezone']) && $_POST['get_timezone'] == 1) {
        /*
        7. get_timezone
            accesskey:90336
            settings:1
            get_timezone:1
        */
        $sql = "select value from `settings` where variable='system_timezone'";
        $db->sql($sql);
        $res = $db->getResult();

        $array = json_decode($res[0]['value'], true);
        $array['welcome-wallet-balance'] = isset($array['welcome-wallet-balance']) && !empty($array['welcome-wallet-balance']) ? $array['welcome-wallet-balance'] : "0";
        $array['wallet_balance'] = isset($array['wallet_balance']) && !empty($array['wallet_balance']) ? $array['wallet_balance'] : "";
        $array['support_system'] = isset($array['support_system']) && !empty($array['support_system']) ? $array['support_system'] : "0";

        $currency = $fn->get_settings('currency');
        $min_amount = $array['min_amount'];
        function replaceArrayKeys($array)
        {
            $replacedKeys = str_replace('-', '_', array_keys($array));
            return array_combine($replacedKeys, $array);
        }
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['settings'] = replaceArrayKeys($array);
            $settings['settings']['currency'] = $currency;
            $settings['settings']['min_order_amount_for_free_delivery'] = $min_amount;
            $settings['settings']['current_date'] = date("Y-m-d H:i:s");
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_fcm_key']) && $_POST['get_fcm_key'] == 1) {
        /*
        8. get_fcm_key
            accesskey:90336
            settings:1
            get_fcm_key:1
        */
        $sql = "select value from `settings` where variable='fcm_server_key'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['fcm'] = $res[0]['value'];
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_time_slot_config']) && $_POST['get_time_slot_config'] == 1) {
        /*
        9. get_time_slot_config
            accesskey:90336
            settings:1
            get_time_slot_config:1
        */
        $sql = "select value from `settings` where variable='time_slot_config'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $settings['error'] = false;
            $settings['time_slot_config'] = json_decode($res[0]['value']);
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['settings'] = "No settings found!";
            $settings['message'] = "Something went wrong!";
            print_r(json_encode($settings));
        }
    }
    if (isset($_POST['get_front_end_settings']) && $_POST['get_front_end_settings'] == 1) {
        /*
        10. get_front_end_settings
            accesskey:90336
            settings:1
            get_front_end_settings:1
        */
        $sql = "select * from `settings` where variable='front_end_settings'";
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res)) {
            $res[0]['value'] = json_decode($res[0]['value'], true);
            $res[0]['value']['favicon'] = DOMAIN_URL . 'dist/img/' . $res[0]['value']['favicon'];
            $res[0]['value']['screenshots'] = DOMAIN_URL . 'dist/img/' . $res[0]['value']['screenshots'];
            $res[0]['value']['google_play'] = DOMAIN_URL . 'dist/img/' . $res[0]['value']['google_play'];
            $res[0]['value']['web_logo'] = DOMAIN_URL . 'dist/img/' . $res[0]['value']['web_logo'];
            $res[0]['value']['loading'] = DOMAIN_URL . 'dist/img/' . $res[0]['value']['loading'];
            $res[0]['value']['show_color_picker_in_website'] = !isset($res[0]['value']['show_color_picker_in_website']) && empty($res[0]['value']['show_color_picker_in_website']) ? 0 : $res[0]['value']['show_color_picker_in_website'];
            $res[0]['value']['show_theme_on_website'] = !isset($res[0]['value']['show_theme_on_website']) && empty($res[0]['value']['show_theme_on_website']) ? 0 : $res[0]['value']['show_theme_on_website'];

            $settings['error'] = false;
            $settings['front_end_settings'] = $res;
            print_r(json_encode($settings));
        } else {
            $settings['error'] = true;
            $settings['front_end_settings'] = null;
            $settings['message'] = "No active time slots found!";
            print_r(json_encode($settings));
        }
    }
} else if (isset($_POST['get_time_slots']) && $_POST['get_time_slots'] == 1) {
    /*
    11. get_time_slots
        accesskey:90336
        settings:1
        get_time_slots:1
    */
    $sql = "select * from `time_slots` where status=1 ORDER BY `last_order_time` ASC";
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        $settings['error'] = false;
        $settings['time_slots'] = $res;
        print_r(json_encode($settings));
    } else {
        $settings['error'] = true;
        $settings['time_slots'] = null;
        $settings['message'] = "No active time slots found!";
        print_r(json_encode($settings));
    }
} elseif (isset($_POST['all']) && $_POST['all'] == 1) {
    /*
    12. all
        accesskey:90336
        settings:1
        all:1
    */
    $sql = "select variable, value from `settings` where 1";
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        $settings['error'] = false;
        $settings['data'] = array();
        foreach ($res as $k => $v) {
            if ($v['variable'] == "system_timezone") {
                $system_timezone = (array)json_decode($v['value']);
                foreach ($system_timezone as $k => $v) {
                    $settings['data'][$k] = $v;
                }
            } else {
                $settings['data'][$v['variable']] = $v['value'];
            }
        }
        $settings['data']['current_time'] = date("Y-m-d H:i:s");
        print_r(json_encode($settings));
    } else {
        $settings['error'] = true;
        $settings['settings'] = "No settings found!";
        $settings['message'] = "Something went wrong!";
        print_r(json_encode($settings));
    }
} else {
    $response['error'] = true;
    $response['message'] = "Something Wrong!!.";
    print_r(json_encode($response));
}
$db->disconnect();
