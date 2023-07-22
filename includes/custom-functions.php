<?php
/*
functions
---------------------------------------------
0. xss_clean($data)
1. get_product_by_id($id=null)
2. get_product_by_variant_id($arr)
3. convert_to_parent($measurement,$measurement_unit_id)
4. rows_count($table,$field = '*',$where = '')
5. get_configurations()
6. get_balance($id)
7. get_bonus($id)
8. get_wallet_balance($id)
9. update_wallet_balance($balance,$id)
10. add_wallet_transaction($order_id="",$id,$type,$amount,$message,$status = 1)
11. update_order_item_status($order_item_ids,$order_id,$status)
12. validate_promo_code($user_id,$promo_code,$total)
13. get_settings($variable,$is_json = false)
14. send_order_update_notification($uid,$title,$message,$type)
15. send_notification_to_delivery_boy($uid,$title,$message,$type,$order_id)
16. get_promo_details($promo_code)
17. store_return_request($user_id,$order_id,$order_item_id)
18. get_role($id)
19. get_permissions($id)
20. add_delivery_boy_commission($id,$type,$amount,$message,$status = "SUCCESS")
21. store_delivery_boy_notification($delivery_boy_id,$order_id,$title,$message,$type)
22. is_item_available_in_cart($user_id,$product_variant_id)
23. time_slot_config()
24. is_address_exists($id="",$user_id="")
25. is_user_or_dboy_exists($type,$type_id)
26. get_user_or_delivery_boy_balance($type,$type_id)
27. store_withdrawal_request($type, $type_id, $amount, $message)
28. debit_balance($type, $type_id, $new_balance)
29. is_records_exists($type, $type_id,$offset,$limit)
30. get_product_id_by_variant_id($product_variant_id)
31. update_delivery_boy_wallet_balance($balance, $id)
32. low_stock_count($low_stock_limit)
33. sold_out_count()
34. is_product_available($product_id)
35. is_product_added_as_favorite($user_id, $product_id)
36. validate_email($email)
37. update_forgot_password_code($email,$code)
38. validate_code($code)
39. get_user($code)
40. update_password($code,$password_hash)
41. is_return_request_exists($user_id, $order_item_id)
42. get_last_inserted_id($table)
43. is_product_cancellable($order_item_id)
44. is_default_address_exists($user_id)
44. get_data($fields=[], $where,$table)
45. update_order_status($id,$status,$delivery_boy_id=0)
46. verify_paystack_transaction($reference, $email, $amount)
47. get_variant_id_by_product_id($product_id)
48. get_order_item_by_order_id($id)
49. add_wallet_balance($order_id, $user_id, $amount, $type,$message)
50. send_notification_to_admin($id, $title, $message, $type, $order_id)
51. get_products($user_id = NULL, $id = NULL, $slug = NULL, $category_id = NULL, $subcategory_id = NULL, $where = '', $limit = 10, $offset = 0, $sort = "p.id", $order = "DESC", $group_by_product = "", $group_by_variant = "", $context = 0, $search = '')
52. product_reviews($user_id = '', $product_id = '', $field = '*', $limit = 10, $offset = 0, $sort = "pr.id", $order = "ASC")
*/
include_once('crud.php');
require_once('firebase.php');
require_once('push.php');
include_once('functions.php');

class custom_functions
{
    protected $db;
    protected $fn;
    function __construct()
    {
        $this->fn = new functions();
        $this->db = new Database();
        $this->db->connect();
    }


    function xss_clean_array($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->xss_clean($value);
            }
        } else {
            $array = $this->xss_clean($array);
        }
        return $array;
    }

    function xss_clean($data)
    {
        $data = trim($data);
        // Fix &entity\n;
        $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }

    function get_product_by_id($id = null)
    {
        if (!empty($id)) {
            $sql = "SELECT * FROM products WHERE id=" . $id;
        } else {
            $sql = "SELECT * FROM products";
        }
        $this->db->sql($sql);
        $res = $this->db->getResult();
        $product = array();
        $i = 1;
        foreach ($res as $row) {
            $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'];
            $this->db->sql($sql);
            $product[$i] = $row;
            $product[$i]['variant'] = $this->db->getResult();
            $i++;
        }
        if (!empty($product)) {
            return $product;
        }
    }
    function get_product_by_variant_id($arr)
    {
        $arr = stripslashes($arr);
        if (!empty($arr)) {
            $arr = json_decode($arr, 1);
            $i = 0;
            foreach ($arr as $id) {
                $sql = "SELECT *,pv.id,(SELECT t.title FROM taxes t WHERE t.id=p.tax_id) as tax_title,(SELECT t.percentage FROM taxes t WHERE t.id=p.tax_id) as tax_percentage,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv JOIN products p ON pv.product_id=p.id WHERE pv.id=" . $id;
                $this->db->sql($sql);
                $res[$i] = $this->db->getResult()[0];
                $i++;
            }
            if (!empty($res)) {
                return $res;
            }
        }
    }

    function convert_to_parent($measurement, $measurement_unit_id)
    {
        $sql = "SELECT * FROM unit WHERE id=" . $measurement_unit_id;
        $this->db->sql($sql);
        $unit = $this->db->getResult();
        if (!empty($unit[0]['parent_id'])) {
            $stock = $measurement / $unit[0]['conversion'];
        } else {
            $stock = ($measurement) * $unit[0]['conversion'];
        }
        return $stock;
    }
    function rows_count($table, $field = '*', $where = '')
    {
        // Total count
        if (!empty($where)) $where = "Where " . $where;
        $sql = "SELECT COUNT(" . $field . ") as total FROM " . $table . " " . $where;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        foreach ($res as $row)
            return $row['total'];
    }
    public function get_configurations()
    {
        $sql = "SELECT value FROM settings WHERE `variable`='system_timezone'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return json_decode($res[0]['value'], true);
        } else {
            return false;
        }
    }
    public function get_balance($id)
    {
        $sql = "SELECT balance FROM delivery_boys WHERE id=" . $id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0]['balance'];
        } else {
            return false;
        }
    }
    public function get_bonus($id)
    {
        $sql = "SELECT bonus FROM delivery_boys WHERE id=" . $id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0]['bonus'];
        } else {
            return false;
        }
    }
    public function get_wallet_balance($id)
    {
        $sql = "SELECT balance FROM users WHERE id=" . $id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0]['balance'];
        } else {
            return 0;
        }
    }
    public function update_wallet_balance($balance, $id)
    {
        $data = array(
            'balance' => $balance
        );
        if ($this->db->update('users', $data, 'id=' . $id))
            return true;
        else
            return false;
    }

    public function add_wallet_transaction($order_id = "", $id, $type, $amount, $message = 'Used against Order Placement', $status = 1)
    {
        $data = array(
            'order_id' => $order_id,
            'user_id' => $id,
            'type' => $type,
            'amount' => $amount,
            'message' => $message,
            'status' => $status
        );
        $this->db->insert('wallet_transactions', $data);
        $data1 = $this->db->getResult();
        $result = $this->send_order_update_notification($id, "Wallet Transaction", $message, 'wallet_transaction', 0);
        // print_r($result);
        return $data1[0];
    }

    public function update_order_item_status($order_item_id, $order_id, $status)
    {
        $data = array('update_order_item_status' => '1', 'order_item_id' => $order_item_id, 'status' => $status, 'order_id' => $order_id, 'ajaxCall' => 1);
        // print_r($data);

        $jwt_token = generate_token();

        $ch = curl_init();
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                "Authorization: Bearer $jwt_token"
            ]
        );
        curl_setopt($ch, CURLOPT_URL, DOMAIN_URL . "api-firebase/order-process.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ch);
        $header_info = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        curl_close($ch);
        return $response;
    }

    public function validate_promo_code($user_id, $promo_code, $total)
    {
        $sql = "select * from promo_codes where promo_code='" . $promo_code . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (empty($res)) {
            $response['error'] = true;
            $response['message'] = "Invalid promo code.";
            return $response;
            exit();
        }
        if ($res[0]['status'] == 0) {
            $response['error'] = true;
            $response['message'] = "This promo code is either expired / invalid.";
            return $response;
            exit();
        }

        $sql = "select id from users where id='" . $user_id . "'";
        $this->db->sql($sql);
        $res_user = $this->db->getResult();
        if (empty($res_user)) {
            $response['error'] = true;
            $response['message'] = "Invalid user data.";
            return $response;
            exit();
        }

        $start_date = $res[0]['start_date'];
        $end_date = $res[0]['end_date'];
        $date = date('Y-m-d h:i:s a');

        if ($date < $start_date) {
            $response['error'] = true;
            $response['message'] = "This promo code can't be used before " . date('d-m-Y', strtotime($start_date)) . "";
            return $response;
            exit();
        }
        if ($date > $end_date) {
            $response['error'] = true;
            $response['message'] = "This promo code can't be used after " . date('d-m-Y', strtotime($end_date)) . "";
            return $response;
            exit();
        }
        if ($total < $res[0]['minimum_order_amount']) {
            $response['error'] = true;
            $response['message'] = "This promo code is applicable only for order amount greater than or equal to " . $res[0]['minimum_order_amount'] . "";
            return $response;
            exit();
        }
        //check how many users have used this promo code and no of users used this promo code crossed max users or not
        $sql = "select id from orders where promo_code='" . $promo_code . "' GROUP BY user_id";
        $this->db->sql($sql);
        $res_order = $this->db->numRows();

        if ($res_order >= $res[0]['no_of_users']) {
            $response['error'] = true;
            $response['message'] = "This promo code is applicable only for first " . $res[0]['no_of_users'] . " users.";
            return $response;
            exit();
        }
        //check how many times user have used this promo code and count crossed max limit or not
        if ($res[0]['repeat_usage'] == 1) {
            $sql = "select id from orders where user_id=" . $user_id . " and promo_code='" . $promo_code . "'";
            $this->db->sql($sql);
            $total_usage = $this->db->numRows();
            if ($total_usage >= $res[0]['no_of_repeat_usage']) {
                $response['error'] = true;
                $response['message'] = "This promo code is applicable only for " . $res[0]['no_of_repeat_usage'] . " times.";
                return $response;
                exit();
            }
        }
        //check if repeat usage is not allowed and user have already used this promo code 
        if ($res[0]['repeat_usage'] == 0) {
            $sql = "select id from orders where user_id=" . $user_id . " and promo_code='" . $promo_code . "'";
            $this->db->sql($sql);
            $total_usage = $this->db->numRows();
            if ($total_usage >= 1) {
                $response['error'] = true;
                $response['message'] = "This promo code is applicable only for 1 time.";
                return $response;
                exit();
            }
        }
        if ($res[0]['discount_type'] == 'percentage') {
            $percentage = $res[0]['discount'];
            $discount = $total / 100 * $percentage;
            if ($discount > $res[0]['max_discount_amount']) {
                $discount = $res[0]['max_discount_amount'];
            }
        } else {
            $discount = $res[0]['discount'];
        }
        $discounted_amount = $total - $discount;
        $response['error'] = false;
        $response['message'] = "promo code applied successfully.";
        $response['promo_code'] = $promo_code;
        $response['promo_code_message'] = $res[0]['message'];
        $response['total'] = $total;
        $response['discount'] = "$discount";
        $response['discounted_amount'] = "$discounted_amount";
        return $response;
        exit();
    }
    public function get_settings($variable, $is_json = false)
    {
        if ($variable == 'logo' || $variable == 'Logo') {
            $sql = "select value from `settings` where variable='Logo' OR variable='logo'";
        } else {
            $sql = "SELECT value FROM `settings` WHERE `variable`='$variable'";
        }

        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res) && isset($res[0]['value'])) {
            if ($is_json) {
                $res[0]['value'] = preg_replace('/\r|\n/', '\n', trim($res[0]['value']));
                return json_decode($res[0]['value'], true);
            } else {
                return $res[0]['value'];
            }
        } else {
            return false;
        }
    }
    public function send_order_update_notification($uid, $title, $message, $type, $id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            //hecking the required params 
            //creating a new push
            /*dynamically getting the domain of the app*/
            $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $url .= $_SERVER['SERVER_NAME'];
            $url .= $_SERVER['REQUEST_URI'];
            $server_url = dirname($url) . '/';

            $push = null;
            //first check if the push has an image with it
            //if the push don't have an image give null in place of image
            $push = new Push(
                $title,
                $message,
                null,
                $type,
                $id
            );
            //getting the push from push object
            $mPushNotification = $push->getPush();

            //getting the token from database object
            $sql = "SELECT fcm_id FROM users WHERE id = '" . $uid . "'";
            $this->db->sql($sql);
            $res = $this->db->getResult();
            $token = array();
            foreach ($res as $row) {
                array_push($token, $row['fcm_id']);
            }

            //creating firebase class object 
            $firebase = new Firebase();

            //sending push notification and displaying result 
            $firebase->send($token, $mPushNotification);
            $response['error'] = false;
            $response['message'] = "Successfully Send";
        } else {
            $response['error'] = true;
            $response['message'] = 'Invalid request';
        }
    }
    public function send_notification_to_delivery_boy($delivery_boy_id, $title, $message, $type, $order_id)
    {
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        //hecking the required params 
        //creating a new push
        /*dynamically getting the domain of the app*/
        $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'];
        $url .= $_SERVER['REQUEST_URI'];
        $server_url = dirname($url) . '/';

        $push = null;
        //first check if the push has an image with it
        //if the push don't have an image give null in place of image
        $push = new Push(
            $title,
            $message,
            null,
            $type,
            $order_id
        );
        //getting the push from push object
        $m_push_notification = $push->getPush();

        //getting the token from database object
        $sql = "SELECT fcm_id FROM delivery_boys WHERE id = '" . $delivery_boy_id . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        $token = array();
        foreach ($res as $row) {
            array_push($token, $row['fcm_id']);
        }

        //creating firebase class object 
        $firebase = new Firebase();

        //sending push notification and displaying result 
        $firebase->send($token, $m_push_notification);
        $response['error'] = false;
        $response['message'] = "Successfully Send";
    }
    public function get_promo_details($promo_code)
    {
        $sql = "SELECT * FROM `promo_codes` WHERE `promo_code`='$promo_code'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res;
        } else {
            return false;
        }
    }
    public function store_return_request($user_id, $order_id, $order_item_id)
    {
        $sql = "select product_variant_id from order_items where id=" . $order_item_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        $pv_id = $res[0]['product_variant_id'];
        $sql = "select product_id from product_variant where id=" . $pv_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();

        $data = array(
            'user_id' => $user_id,
            'order_id' => $order_id,
            'order_item_id' => $order_item_id,
            'product_id' => $res[0]['product_id'],
            'product_variant_id' => $pv_id
        );
        $this->db->insert('return_requests', $data);
        return $this->db->getResult()[0];
    }
    public function get_role($id)
    {
        $sql = "SELECT role FROM admin WHERE id=" . $id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res) && isset($res[0]['role'])) {
            return $res[0]['role'];
        } else {
            return 0;
        }
    }
    public function get_permissions($id)
    {
        $sql = "SELECT permissions FROM admin WHERE id=" . $id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res) && isset($res[0]['permissions'])) {
            return json_decode($res[0]['permissions'], true);
        } else {
            return 0;
        }
    }

    public function add_delivery_boy_commission($id, $type, $amount, $message, $status = "SUCCESS")
    {
        $balance = $this->get_balance($id);
        $data = array(
            'delivery_boy_id' => $id,
            'type' => $type,
            'opening_balance' => $balance,
            'closing_balance' => $balance + $amount,
            'amount' => $amount,
            'message' => $message,
            'status' => $status
        );
        $this->db->insert('fund_transfers', $data);
        $this->db->getResult()[0];
        return $this->db->getResult()[0];
    }

    public function store_delivery_boy_notification($delivery_boy_id, $order_id, $title, $message, $type)
    {
        $data = array(
            'delivery_boy_id' => $delivery_boy_id,
            'order_id' => $order_id,
            'title' => $title,
            'message' => $message,
            'type' => $type
        );
        $this->db->insert('delivery_boy_notifications', $data);
        return $this->db->getResult()[0];
    }
    public function is_item_available_in_user_cart($user_id, $product_variant_id = "")
    {
        $sql = "SELECT id FROM cart WHERE user_id=" . $user_id;
        $sql .= !empty($product_variant_id) ? " AND product_variant_id=" . $product_variant_id : "";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }
    public function is_item_available_in_save_for_later($user_id, $product_variant_id = "")
    {
        $sql = "SELECT id FROM cart WHERE user_id=" . $user_id;
        $sql .= !empty($product_variant_id) ? " AND product_variant_id=" . $product_variant_id : "";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function is_item_available($product_id, $product_variant_id)
    {
        $sql = "SELECT id FROM product_variant WHERE product_id=" . $product_id . " AND id=" . $product_variant_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            $sql = "SELECT id FROM products WHERE status = 1 AND id=$product_id";
            $this->db->sql($sql);
            $res = $this->db->getResult();
            if (!empty($res)) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
    public function time_slot_config()
    {
        $sql = "SELECT value FROM settings WHERE `variable`='time_slot_config'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return json_decode($res[0]['value'], true);
        } else {
            return false;
        }
    }

    public function is_address_exists($id = "", $user_id = "")
    {
        $sql = "SELECT id FROM user_addresses WHERE ";
        $sql .= !empty($id) ? "id=$id" : "user_id=$user_id";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function is_user_or_dboy_exists($type, $type_id)
    {
        $type1 = $type == 'user' ? 'users' : 'delivery_boys';
        $sql = "SELECT id FROM $type1 WHERE id=" . $type_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function get_user_or_delivery_boy_balance($type, $type_id)
    {
        $type1 = $type == 'user' ? 'users' : 'delivery_boys';
        $sql = "SELECT balance FROM $type1 WHERE id=" . $type_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0]['balance'];
        } else {
            return false;
        }
    }
    public function store_withdrawal_request($type, $type_id, $amount, $message)
    {

        $data = array(
            'type' => $type,
            'type_id' => $type_id,
            'amount' => $amount,
            'message' => $message,
        );
        if ($this->db->insert('withdrawal_requests', $data)) {
            return true;
        } else {
            return false;
        }
    }

    public function debit_balance($type, $type_id, $new_balance)
    {
        $type1 = $type == 'user' ? 'users' : 'delivery_boys';
        $sql = "UPDATE $type1 SET balance=" . $new_balance . " WHERE id=" . $type_id;
        if ($this->db->sql($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function is_records_exists($type, $type_id, $offset, $limit)
    {
        $offset = empty($offset) ? 0 : $offset;
        $sql = "SELECT * FROM withdrawal_requests WHERE `type`= '" . $type . "' AND `type_id` = " . $type_id . " ORDER BY date_created DESC";
        $sql .= !empty($limit) ? " LIMIT $offset,$limit" : "";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        return $res;
    }

    public function get_product_id_by_variant_id($product_variant_id)
    {
        $sql = "SELECT product_id FROM product_variant WHERE `id`= " . $product_variant_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0]['product_id'];
        } else {
            return false;
        }
    }
    public function get_variant_id_by_product_id($product_id)
    {
        $sql = "SELECT id FROM product_variant WHERE `product_id`= " . $product_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        return $res[0]['id'];
    }

    public function update_delivery_boy_wallet_balance($balance, $id)
    {
        $data = array(
            'balance' => $balance
        );
        if ($this->db->update('delivery_boys', $data, 'id=' . $id))
            return true;
        else
            return false;
    }

    function low_stock_count($low_stock_limit)
    {
        $sql = "SELECT COUNT(id) as total FROM product_variant WHERE stock < $low_stock_limit AND serve_for='Available'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        foreach ($res as $row)
            return $row['total'];
    }

    function sold_out_count()
    {
        $sql = "SELECT COUNT(id) as total FROM product_variant WHERE serve_for='Sold Out'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        foreach ($res as $row)
            return $row['total'];
    }

    public function is_product_set_as_rating($product_id)
    {
        // $sql = "select product_rating from category "
        $sql = "SELECT p.id,c.name FROM `products` p join category c on c.id=p.category_id where p.id=$product_id and c.product_rating=1";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function is_user_exists($user_id)
    {
        $sql = "SELECT id FROM users WHERE id=" . $user_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function is_product_available($product_id)
    {
        $sql = "SELECT id FROM products WHERE id=" . $product_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function is_product_added_as_favorite($user_id, $product_id)
    {
        $sql = "SELECT id FROM favorites WHERE product_id=" . $product_id . " AND user_id=" . $user_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function validate_email($email)
    {
        $sql = "SELECT email FROM `admin` WHERE email='" . $email . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0]['email'];
        } else {
            return 0;
        }
    }

    public function update_forgot_password_code($email, $code)
    {
        $sql = "UPDATE admin set forgot_password_code = '" . $code . "' WHERE email='" . $email . "'";
        if ($this->db->sql($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function validate_code($code)
    {
        $sql = "SELECT forgot_password_code FROM `admin` WHERE forgot_password_code='" . $code . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function get_user($code)
    {
        $sql = "SELECT username,email FROM `admin` WHERE forgot_password_code='" . $code . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res;
        } else {
            return 0;
        }
    }

    public function update_password($code, $password_hash)
    {
        $sql = "UPDATE admin set password = '" . $password_hash . "' WHERE forgot_password_code='" . $code . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res;
        } else {
            return 0;
        }
    }

    public function is_return_request_exists($user_id, $order_item_id)
    {
        $sql = "SELECT id FROM return_requests WHERE user_id = '" . $user_id . "' AND order_item_id = '" . $order_item_id . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function get_last_inserted_id($table)
    {
        $sql = "SELECT MAX(id) as id FROM $table";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res[0]['id'];
        } else {
            return 0;
        }
    }

    public function is_product_cancellable($order_item_id)
    {
        $sql = "SELECT product_variant_id,active_status FROM order_items WHERE id = " . $order_item_id;
        $this->db->sql($sql);
        $result = $this->db->getResult();
        $sql = "SELECT p.cancelable_status,p.till_status FROM products p JOIN product_variant pv ON p.id=pv.product_id WHERE pv.id=" . $result[0]['product_variant_id'];
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if ($res[0]['cancelable_status'] == 1) {
            if ($res[0]['till_status'] == 'received' && ($result[0]['active_status'] != 'awaiting_payment' &&  $result[0]['active_status'] != 'received')) {
                $response['error'] = true;
                $response['till_status_error'] = true;
                $response['cancellable_status_error'] = false;
                $response['message'] = 'Sorry this item is only cancelable till status ' . $res[0]['till_status'] . '!';
            } elseif ($res[0]['till_status'] == 'processed' && ($result[0]['active_status'] != 'awaiting_payment' &&  $result[0]['active_status'] != 'received' && $result[0]['active_status'] != 'processed')) {
                $response['error'] = true;
                $response['till_status_error'] = true;
                $response['cancellable_status_error'] = false;
                $response['message'] = 'Sorry this item is only cancelable till status ' . $res[0]['till_status'] . '!';
            } elseif ($res[0]['till_status'] == 'shipped' && ($result[0]['active_status'] != 'awaiting_payment' && $result[0]['active_status'] != 'received' && $result[0]['active_status'] != 'processed' && $result[0]['active_status'] != 'shipped')) {
                $response['error'] = true;
                $response['till_status_error'] = true;
                $response['cancellable_status_error'] = false;
                $response['message'] = 'Sorry this item is only cancelable till status ' . $res[0]['till_status'] . '!';
            } else {
                $response['error'] = false;
                $response['till_status_error'] = false;
                $response['cancellable_status_error'] = false;
                $response['message'] = 'Item Cancellation criteria matched!';
            }
        } else {
            $response['error'] = true;
            $response['cancellable_status_error'] = true;
            $response['till_status_error'] = true;
            $response['message'] = 'Sorry this item is not cancelable!';
        }
        return $response;
    }

    public function is_product_returnable($order_item_id)
    {
        $sql = "SELECT product_variant_id FROM order_items WHERE id = " . $order_item_id;
        $this->db->sql($sql);
        $result = $this->db->getResult();

        $sql = "SELECT p.return_status FROM products p JOIN product_variant pv ON p.id=pv.product_id WHERE pv.id=" . $result[0]['product_variant_id'];
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if ($res[0]['return_status'] == 1) {
            $response['error'] = false;
            $response['return_status_error'] = false;
            $response['message'] = 'Item return criteria matched!';
        } else {
            $response['error'] = true;
            $response['return_status_error'] = true;
            $response['message'] = 'Sorry this item is not returnable!';
        }

        return $response;
    }

    public function remove_other_addresses_from_default($user_id)
    {
        $sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = " . $user_id;
        $this->db->sql($sql);
    }

    public function verifyTransaction($data)
    {
        global $paypalUrl;

        $req = 'cmd=_notify-validate';
        foreach ($data as $key => $value) {
            $value = urlencode(stripslashes($value));
            $value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value); // IPN fix
            $req .= "&$key=$value";
        }
        $ch = curl_init($paypalUrl);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        $res = curl_exec($ch);

        if (!$res) {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: [$errno] $errstr");
        }

        $info = curl_getinfo($ch);

        // Check the http response
        $httpCode = $info['http_code'];
        if ($httpCode != 200) {
            throw new Exception("PayPal responded with http code $httpCode");
        }

        curl_close($ch);

        return $res === 'VERIFIED';
    }
    public function checkTxnid($txnid)
    {
        $txnid = $this->db->escapeString($txnid);
        $sql = 'SELECT * FROM `payments` WHERE txnid = \'' . $txnid . '\'';
        $result = $this->db->getResult();
        return !$this->db->numRows();;
    }

    // public function get_data($columns = [], $where, $table)
    // {
    //     $sql = "select ";
    //     if (!empty($columns)) {
    //         $columns = implode(",", $columns);
    //         $sql .= " $columns from ";
    //     } else {
    //         $sql .= " * from ";
    //     }
    //     $sql .= " `$table` WHERE $where";

    //     $this->db->sql($sql);
    //     $res = $this->db->getResult();
    //     return $res;
    // }

    public function get_data($columns = [], $where = '', $table, $join_table = '', $group_by = '', $order_by = '', $limit = '')
    {
        $sql = "select ";
        if (!empty($columns)) {
            $columns = implode(",", $columns);
            $sql .= " $columns from ";
        } else {
            $sql .= " * from ";
        }
        $where = (!empty($where)) ?  " WHERE $where " : "";
        $sql .= " `$table` $join_table $where $group_by $order_by $limit";
        // echo $sql;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        return $res;
    }

    public function update_order_status($id, $status, $delivery_boy_id = 0)
    {
        $data = array('update_order_status' => '1', 'id' => $id, 'status' => $status, 'delivery_boy_id' => $delivery_boy_id, 'ajaxCall' => 1);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, DOMAIN_URL . "api-firebase/order-process.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function verify_paystack_transaction($reference, $email, $amount)
    {
        $payment_methods = $this->get_settings('payment_methods', true);
        //The parameter after verify/ is the transaction reference to be verified
        $url = 'https://api.paystack.co/transaction/verify/' . $reference;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            [
                'Authorization: Bearer ' . $payment_methods['paystack_secret_key']
            ]
        );

        //send request
        $request = curl_exec($ch);
        //close connection
        curl_close($ch);
        //declare an array that will contain the result 
        $result = array();

        if ($request) {
            $result = json_decode($request, true);
        }

        if ($result['status'] == true) {

            if (array_key_exists('data', $result) && array_key_exists('status', $result['data']) && ($result['data']['status'] === 'success')) {
                if ($result['data']['customer']['email'] == $email && $result['data']['amount'] == $amount) {
                    $response['error'] = false;
                    $response['message'] = "Transaction verified successfully.";
                    $response['status'] = $result['data']['status'];
                } else {
                    $response['error'] = true;
                    $response['message'] = "Transaction verified but does not belong to specified customer or invalid amount sent.";
                    $response['status'] = $result['data']['status'];
                }
            } else {
                $response['error'] = true;
                $response['message'] = "Transaction was unsuccessful. try again";
                $response['status'] = $result['data']['status'];
            }
        } else {
            $response['error'] = true;
            $response['message'] = "Could not initiate verification. " . $result['message'];
            $response['status'] = "failed";
        }
        return $response;
    }
    public function get_payment_methods()
    {
        $sql = "SELECT value FROM settings WHERE `variable`='payment_methods'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return json_decode($res[0]['value'], true);
        } else {
            return false;
        }
    }
    public function get_order_item_by_order_id($id)
    {
        $sql = "SELECT * FROM `order_items` where order_id=$id";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return $res;
        } else {
            return false;
        }
    }
    public function add_wallet_balance($order_id, $user_id, $amount, $type, $message)
    {
        $data = array('add_wallet_balance' => '1', 'user_id' => $user_id, 'order_id' => $order_id, 'amount' => $amount, 'type' => $type, 'message' => $message, 'ajaxCall' => 1);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, DOMAIN_URL . "api-firebase/get-user-transactions.php");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function send_notification_to_admin($title, $message, $type, $order_id)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            /*dynamically getting the domain of the app*/
            $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $url .= $_SERVER['SERVER_NAME'];
            $url .= $_SERVER['REQUEST_URI'];
            $server_url = dirname($url) . '/';
            $push = null;
            $push = new Push(
                $title,
                $message,
                "",
                $type,
                $order_id
            );
            $m_push_notification = $push->getPush();
            $sql = "SELECT fcm_id FROM admin";
            $this->db->sql($sql);
            $res = $this->db->getResult();
            $token = array();
            foreach ($res as $row) {
                array_push($token, $row['fcm_id']);
            }
            //creating firebase class object 
            $firebase = new Firebase();
            //sending push notification and displaying result 
            $firebase->send($token, $m_push_notification);
            $response['error'] = false;
            $response['message'] = "Successfully Send";
            //print_r(json_encode($response));
        } else {
            $response['error'] = true;
            $response['message'] = 'Invalid request';
        }
    }

    public function send_notification_to_user($title, $message, $type, $user_id, $ticket_id, $message_res = '')
    {
        // if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        //hecking the required params 
        //creating a new push
        /*dynamically getting the domain of the app*/
        $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'];
        $url .= $_SERVER['REQUEST_URI'];
        $server_url = dirname($url) . '/';

        $push = null;
        //first check if the push has an image with it
        //if the push don't have an image give null in place of image
        $push = new Push(
            $title,
            $message,
            null,
            $type,
            $ticket_id,
            $message_res,
        );
        //getting the push from push object
        $m_push_notification = $push->getPush();

        //getting the token from database object
        $sql = "SELECT fcm_id FROM users WHERE id = '" . $user_id . "'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        $token = array();
        foreach ($res as $row) {
            array_push($token, $row['fcm_id']);
        }

        //creating firebase class object 
        $firebase = new Firebase();

        //sending push notification and displaying result 
        $firebase->send($token, $m_push_notification);
        $response['error'] = false;
        $response['message'] = "Successfully Send";
        // print_r(json_encode($response));
    }

    public function update_product_ratings($product_id, $user_id, $ratings)
    {
        // 1. find avg of rate  2. increment number of ratings

        $sql = "SELECT id FROM products WHERE id=" . $product_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function validate_image($file, $is_image = true)
    {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $file['tmp_name']);
        } else if (function_exists('mime_content_type')) {
            $type = mime_content_type($file['tmp_name']);
        } else {
            $type = $file['type'];
        }
        $type = strtolower($type);
        if ($is_image == false) {
            if (!in_array($type, array('text/plain'))) {
                return true;
            } else {
                return false;
            }
        } else if ($is_image == true) {
            if (!in_array($type, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'application/octet-stream'))) {
                return true;
            } else {
                return false;
            }
        } else {
            if (!in_array($type, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'application/octet-stream'))) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function validate_video($file, $is_image = true)
    {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $file['tmp_name']);
        } else if (function_exists('mime_content_type')) {
            $type = mime_content_type($file['tmp_name']);
        } else {
            $type = $file['type'];
        }
        $type = strtolower($type);
        if ($is_image == false) {
            if (!in_array($type, array('text/plain'))) {
                return true;
            } else {
                return false;
            }
        } else if ($is_image == true) {
            if (!in_array($type, array('video/mp4', 'video/mpeg', 'video/webm', 'video/mpg', 'application/octet-stream'))) {
                return true;
            } else {
                return false;
            }
        } else {
            if (!in_array($type, array('video/mp4', 'video/mpeg', 'video/webm', 'video/mpg', 'application/octet-stream'))) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function validate_other_images($tmp_name, $type)
    {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $tmp_name);
        } else if (function_exists('mime_content_type')) {
            $type = mime_content_type($tmp_name);
        } else {
            $type = $tmp_name;
        }

        $type = strtolower($type);

        if (!in_array($type, array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'application/octet-stream'))) {
            return true;
        } else {
            return false;
        }
    }

    public function validate_multiple_video($tmp_name, $type)
    {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $tmp_name);
        } else if (function_exists('mime_content_type')) {
            $type = mime_content_type($tmp_name);
        } else {
            $type = $tmp_name;
        }

        $type = strtolower($type);

        if (!in_array($type, array('video/mp4', 'video/mpeg', 'video/webm', 'video/mpg', 'application/octet-stream'))) {
            return true;
        } else {
            return false;
        }
    }

    public function set_timezone($config)
    {
        $result = false;
        if (isset($config['system_timezone']) && isset($config['system_timezone_gmt']) && $config['system_timezone_gmt'] != "" && $config['system_timezone'] != "") {
            date_default_timezone_set($config['system_timezone']);
            $this->db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
            $result = true;
        } else {
            date_default_timezone_set('Asia/Kolkata');
            $this->db->sql("SET `time_zone` = '+05:30'");
            $result = true;
        }
        return $result;
    }
    public function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public function delete_variant($v_id)
    {
        $sql = "SELECT id FROM product_variant WHERE id=" . $v_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (!empty($res)) {
            $sql = "DELETE FROM product_variant WHERE id=" . $v_id;
            if ($this->db->sql($sql)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function delete_other_images($pid, $i)
    {
        $sql = "SELECT other_images FROM products WHERE id =" . $pid;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        foreach ($res as $row)
            $other_images = $row['other_images']; /*get other images json array*/
        $other_images = json_decode($other_images); /*decode from json to array*/

        unlink("../../" . $other_images[$i]); /*remove the image from the folder*/

        unset($other_images[$i]); /*remove image from the array*/
        $other_images = json_encode(array_values($other_images)); /*convert back to JSON */

        /*update the table*/
        $sql = "UPDATE `products` set `other_images`='" . $other_images . "' where id=" . $pid;
        if ($this->db->sql($sql))
            return 1;
        else
            return 0;
    }

    public function delete_variant_images($vid, $i)
    {
        $sql = "SELECT images FROM product_variant WHERE id =" . $vid;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        foreach ($res as $row)
            $other_images = $row['images']; /*get images json array*/

        $variant_images = str_replace("'", '"', $other_images);
        $other_images = json_decode($variant_images); /*decode from json to array*/

        unlink("../" . $other_images[$i]); /*remove the image from the folder*/

        unset($other_images[$i]); /*remove image from the array*/
        $other_images = json_encode(array_values($other_images)); /*convert back to JSON */

        /*update the table*/
        $sql = "UPDATE `product_variant` set `images`='" . $other_images . "' where id=" . $vid;
        if ($this->db->sql($sql))
            return 1;
        else
            return 0;
    }

    public function get_delivery_charge($area_id, $total = 0)
    {
        $total = str_replace(',', '', $total);
        $config = $this->get_configurations();

        if ($config['area-wise-delivery-charge'] == 0) {
            $area = $this->get_data(['delivery_charges', 'minimum_free_delivery_order_amount'], 'id=' . $area_id, 'area');
            if (isset($area[0]['minimum_free_delivery_order_amount'])) {
                $min_amount = $area[0]['minimum_free_delivery_order_amount'];
                $delivery_charge = $area[0]['delivery_charges'];
            }
        } else {
            $min_amount = $config['min_amount'];
            $delivery_charge = $config['delivery_charge'];
        }
        if ($total < $min_amount || $total = 0) {
            $d_charge = $delivery_charge;
        } else {
            $d_charge = 0;
        }

        return $d_charge;
    }

    public function get_products($user_id = NULL, $id = NULL, $slug = NULL, $category_id = NULL, $subcategory_id = NULL, $where = '', $limit = 10, $offset = 0, $sort = "p.row_order", $order = "DESC", $group_by_product = "", $group_by_variant = "", $context = 1, $search = '', $field = "", $join_table = "", $where1 = "")
    {
        if ($sort == 'new') {
            $sort = ' p.date_added ';
            $order = 'DESC';
            $price = 'MIN(price)';
            $price_sort = 'ORDER BY pv.price ASC';
        } elseif ($sort == 'old') {
            $sort = ' p.date_added ';
            $order = 'ASC';
            $price = 'MIN(price)';
            $price_sort = 'ORDER BY pv.price ASC';
        } elseif ($sort == 'high') {
            $sort = ' price ';
            $order = 'DESC';
            $price = 'MAX(if(pv.discounted_price > 0 && pv.discounted_price != 0, pv.discounted_price, pv.price))';
            $price_sort = 'ORDER BY pv.discounted_price DESC';
        } elseif ($sort == 'low') {
            $sort = ' price ';
            $order = 'ASC';
            $price = 'MIN(if(pv.discounted_price > 0 && pv.discounted_price != 0, pv.discounted_price, pv.price))';
            $price_sort = 'ORDER BY pv.discounted_price ASC';
        } else {
            $sort = !empty($sort) ?  $sort : ' p.row_order ';
            $order = !empty($order) ? $order : 'DESC';
            $price = 'MIN(price)';
            $price_sort = 'ORDER BY pv.price ASC';
        }

        $group_by_product = (!empty($group_by_product)) ? " GROUP BY " . $group_by_product : "GROUP BY p.id";
        $group_by_variant = (!empty($group_by_variant)) ? " GROUP BY " . $group_by_variant : "";
        $field = (!empty($field)) ? " , " . $field : "";
        $join_table = (!empty($join_table)) ? $join_table : "";

        if (isset($id) && !empty($id)) {
            if ($context == 1) {
                $where .=  !empty($where) ? " AND p.`id` IN( " . $id . ") " :  " p.`id` IN (" . $id . ")";
            } else {
                $where .=  !empty($where) ? " AND p.`id` NOT IN( " . $id . ") " :  " p.`id` NOT IN (" . $id . ")";
            }
        }

        if (isset($slug) && !empty($slug)) {
            $where .=  !empty($where) ? " AND p.`slug` = '$slug' " :  " p.`slug`= '$slug'";
        }

        if (isset($category_id) && !empty($category_id) && is_numeric($category_id)) {
            $where .=  !empty($where) ? " AND p.`category_id` IN(" . $category_id . ")" : " p.`category_id` IN (" . $category_id . ") ";
        }
        if (isset($subcategory_id) && !empty($subcategory_id) && is_numeric($subcategory_id)) {
            $where .=  !empty($where) ? " AND p.`subcategory_id` IN (" . $subcategory_id . ") " : " p.`subcategory_id` IN (" . $subcategory_id . ")";
        }

        if (isset($search) && !empty($search)) {
            if (!empty($where)) {
                $where .= " AND (p.`id` like '%" . $search . "%' OR p.`name` like '%" . $search . "%' OR p.`subcategory_id` like '%" . $search . "%' OR p.`slug` like '%" . $search . "%' OR p.`description` like '%" . $search . "%')";
            } else {
                $where .= " (p.`id` like '%" . $search . "%' OR p.`name` like '%" . $search . "%' OR p.`subcategory_id` like '%" . $search . "%' OR p.`slug` like '%" . $search . "%' OR p.`description` like '%" . $search . "%')";
            }
        }
        if (!empty($where)) {
            $where .= " AND p.status = 1 ";
        } else {
            $where .= " p.status = 1";
        }
        $where = (!empty($where)) ?  "WHERE " . $where : "";
        $where1 = (!empty($where1)) ?  "AND " . $where1 : "";

        $join = "
        Left join category c on c.id = p.category_id 
        Left join subcategory s on s.id = p.subcategory_id 
        Left join taxes t on t.id = p.tax_id 
        left join product_variant pv on p.id = pv.product_id
        left join order_items oi ON pv.id=oi.product_variant_id
        $join_table
        ";

        $sql = "SELECT count(p.id) as total FROM products p $where ";
        $this->db->sql($sql);
        $total = $this->db->getResult();
        // echo $sql;
        $sql = "SELECT p.id,p.name,p.indicator,p.image,p.ratings,p.number_of_ratings,p.total_allowed_quantity,p.slug,p.description,p.status,c.name as category_name, t.percentage as tax_percentage,
            (select " . $price . " from product_variant pv where pv.product_id = p.id ) as price $field
            FROM products p $join $where $group_by_product ORDER BY $sort $order LIMIT $offset,$limit ";
        // echo $sql;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        $products = array();
        $i = 0;
        if (!empty($res)) {
            foreach ($res as $row) {
                $row['image'] = (empty($row['image'])) ? '' : DOMAIN_URL . $row['image'];
                $row['tax_percentage'] = (isset($row['tax_percentage']) && !empty($row['tax_percentage'])) ? $row['tax_percentage'] : "0";
                $row['number_of_ratings'] = (isset($row['number_of_ratings']) && !empty($row['number_of_ratings'])) ? $row['number_of_ratings'] : "0";

                $sql = "SELECT pv.type,pv.id,pv.product_id,pv.price,pv.discounted_price,pv.serve_for,pv.stock,pv.measurement,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name,pv.images FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " $where1 $group_by_variant $price_sort";
                $this->db->sql($sql);
                $variants = $this->db->getResult();
                // echo $sql;
                for ($k = 0; $k < count($variants); $k++) {
                    $variants[$k]['images'] = json_decode($variants[$k]['images'], 1);
                    $variants[$k]['images'] = (empty($variants[$k]['images'])) ? array() : $variants[$k]['images'];
                    for ($j = 0; $j < count($variants[$k]['images']); $j++) {
                        $variants[$k]['images'][$j] = !empty(DOMAIN_URL . $variants[$k]['images'][$j]) ? DOMAIN_URL . $variants[$k]['images'][$j] : "";
                    }

                    if (!empty($user_id)) {
                        $sql = "SELECT qty as cart_count FROM cart where product_variant_id= " . $variants[$k]['id'] . " AND user_id= '$user_id' ";
                        $this->db->sql($sql);
                        $res_cart = $this->db->getResult();
                        $variants[$k]['cart_count'] = (!empty($res_cart[0]['cart_count'])) ? $res_cart[0]['cart_count'] : "0";
                    } else {
                        $variants[$k]['cart_count'] = "0";
                    }

                    if (!empty($user_id)) {
                        $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
                        $this->db->sql($sql);
                        $favorite = $this->db->getResult();
                        $row['is_favorite'] = !empty($favorite) ? true : false;
                    } else {
                        $row['is_favorite'] = false;
                    }

                    $sql = "SELECT fp.price,fp.discounted_price,fp.start_date,fp.end_date FROM flash_sales_products fp LEFT JOIN flash_sales fs ON fs.id=fp.flash_sales_id where fp.status = 1 AND fp.product_variant_id= " . $variants[$k]['id'] . " AND  fp.product_id = " . $variants[$k]['product_id'] . " GROUP BY fp.id";
                    $this->db->sql($sql);
                    $res_flash_sale = $this->db->getResult();
                    $variants[$k]['is_flash_sales'] = (!empty($res_flash_sale)) ? true : false;
                    $variants[$k]['flash_sales'] = array();
                    $temp = array('price' => "", 'discounted_price' => "", 'start_date' => "", 'end_date' => "", 'is_start' => false);
                    $variants[$k]['flash_sales'] = array($temp);
                    foreach ($res_flash_sale as $rows) {
                        $time = date("Y-m-d H:i:s");
                        $time1 = $rows['start_date'];
                        $time2 = $rows['end_date'];

                        $row_time['is_date_created'] = strtotime("$time");
                        $row_time['is_start_date'] = strtotime("$time1");
                        $row_time['is_end_date'] = strtotime("$time2");
                        if ($row_time['is_start_date'] > $row_time['is_date_created'] && $row_time['is_end_date'] > $row_time['is_date_created']) {
                            $rows['is_start'] = false;
                        } else {
                            $rows['is_start'] = true;
                        }
                        if ($variants[$k]['is_flash_sales'] = true) {
                            $variants[$k]['flash_sales'] =  array($rows);
                        } else {
                            $variants[$k]['flash_sales'] = false;
                        }
                    }
                }
                $products[$i] = $row;
                $products[$i]['variants'] = $variants;
                $i++;
            }
        }

        if (!empty($products)) {
            $response['error'] = false;
            $response['message'] = "Products retrieved successfully";
            $response['total'] = $total[0]['total'];
            $response['limit'] = $limit;
            $response['offset'] = $offset;
            $response['data'] = $products;
        } else {
            $response['error'] = true;
            $response['message'] = "No products available";
            $response['total'] = $total[0]['total'];
            $response['limit'] = $limit;
            $response['offset'] = $offset;
            $response['data'] = array();
        }
        return $response;
    }

    public function get_cart_data($fields = '', $where = '', $join_table = '', $sort = 'p.id', $order = 'DESC')
    {
        $fields = (!empty($fields)) ? " , " . $fields : "";
        $join_table = (!empty($join_table)) ? $join_table : "";
        $where = (!empty($where)) ? "WHERE " . $where : "";

        $sql = "select p.id as product_id,pv.id as product_variant_id,p.is_cod_allowed,pv.type,pv.measurement,pv.price,pv.discounted_price,pv.serve_for,pv.stock,p.name,p.slug,p.image,t.percentage as tax_percentage,t.title as tax_title,p.total_allowed_quantity,pv.images,(select short_code from unit u where u.id=pv.measurement_unit_id) as unit,(select short_code from unit u where u.id=pv.stock_unit_id) as stock_unit_name $fields FROM products p JOIN product_variant pv ON pv.product_id = p.id LEFT JOIN taxes t ON t.id=p.tax_id $join_table $where ORDER BY $sort $order ";

        $this->db->sql($sql);
        $res = $this->db->getResult();

        for ($j = 0; $j < count($res); $j++) {
            $res[$j]['image'] = !empty($res[$j]['image']) ? DOMAIN_URL . $res[$j]['image'] : "";
            $res[$j]['tax_percentage'] = !empty($res[$j]['tax_percentage']) ? $res[$j]['tax_percentage'] : "0";
            $res[$j]['tax_title'] = !empty($res[$j]['tax_title']) ? $res[$j]['tax_title'] : "";

            $res[$j]['id'] = (isset($res[$j]['id']) && !empty($res[$j]['id'])) ? $res[$j]['id'] : "";
            $res[$j]['qty'] = (isset($res[$j]['qty']) && !empty($res[$j]['qty'])) ? $res[$j]['qty'] : "0";
            $res[$j]['user_id'] = (isset($res[$j]['user_id']) && !empty($res[$j]['user_id'])) ? $res[$j]['user_id'] : "";
            $res[$j]['save_for_later'] = (isset($res[$j]['save_for_later'])) ? $res[$j]['save_for_later'] : "";

            $variant_images = str_replace("'", '"', $res[$j]['images']);
            $res[$j]['images'] = json_decode($variant_images, 1);
            $res[$j]['images'] = (empty($res[$j]['images'])) ? array() : $res[$j]['images'];

            for ($i = 0; $i < count($res[$j]['images']); $i++) {
                $res[$j]['images'][$i] = !empty(DOMAIN_URL . $res[$j]['images'][$i]) ? DOMAIN_URL . $res[$j]['images'][$i] : "";
            }
        }

        return $res;
    }

    public function product_reviews($user_id = '', $product_id = '', $field = '*', $limit = 10, $offset = 0, $sort = "pr.id", $order = "ASC")
    {
        $sql = "SELECT count(pr.id) as total FROM product_reviews pr WHERE pr.product_id = $product_id";
        $this->db->sql($sql);
        $total = $this->db->getResult();

        if ($user_id == '') {
            $sql = "SELECT $field FROM product_reviews pr WHERE pr.product_id = $product_id ORDER BY $sort $order LIMIT $offset,$limit";
        } else {
            $sql = "SELECT $field FROM product_reviews pr WHERE pr.product_id = $product_id AND pr.user_id = $user_id ORDER BY $sort $order LIMIT $offset,$limit";
        }
        // echo $sql;
        $this->db->sql($sql);
        $res = $this->db->getResult();

        if (!empty($res)) {
            $response = $res;
        } else {
            $response = array();
        }
        return $response;
    }

    public function is_lockup($id)
    {
        $sql = "SELECT local_pickup FROM orders WHERE id = $id";
        $this->db->sql($sql);
        $res = $this->db->getResult();

        return $res[0]['local_pickup'];
    }

    public function get_offers($position, $section_position = '')
    {
        if (!empty($section_position)) {
            $sql = "SELECT * FROM offers WHERE status = 1 AND position = '" . $position . "' AND section_position = '" . $section_position . "' ";
        } else {
            $sql = "SELECT * FROM offers WHERE status = 1 AND position = '" . $position . "' ";
        }
        $this->db->sql($sql);
        $res = $this->db->getResult();

        return $res;
    }

    public function add_transaction($order_id = "", $id = "", $type = '', $amount, $message = '', $date = '', $status = 1)
    {
        $date = !empty($date) ? $date : date('Y-m-d H:i:s');
        $data = array(
            'order_id' => $order_id,
            'user_id' => $id,
            'type' => $type,
            'amount' => $amount,
            'message' => $message,
            'transaction_date' => $date,
            'status' => $status
        );

        $this->db->insert('transactions', $data);
    }

    public function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}
// $this->db->disconnect();
