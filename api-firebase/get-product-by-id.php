<?php
header('Access-Control-Allow-Origin: *');
include_once('../includes/variables.php');
include_once('../includes/crud.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');
$fn = new custom_functions;

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
1.get-product-by-id.php
	accesskey:90336
	product_id:230 OR slug:onion-1
	user_id:369     // {optional}
*/
if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey'])) {
    if ((!isset($_POST['product_id'])) && (!isset($_POST['slug']))) {
        $response['error'] = true;
        $response['message'] = "Please pass product id or slug ";
        print_r(json_encode($response));
        return false;
    }

    $product_id = (isset($_POST['product_id']) && is_numeric($_POST['product_id'])) ? $db->escapeString($fn->xss_clean($_POST['product_id'])) : "";
    $slug = (isset($_POST['slug']) && !empty($_POST['slug'])) ? $db->escapeString($fn->xss_clean($_POST['slug'])) : "";
    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

    $where = '';
    $where .= !empty($product_id) ? " AND p.id IN ($product_id)" : "";
    $where .= !empty($slug) ? " AND p.slug = '$slug' " : "";

    $sql = "SELECT p.*,c.name as category_name FROM products p Left join category c on c.id = p.category_id  WHERE p.status = 1 $where";
    $db->sql($sql);
    $res = $db->getResult();

    $products = array();
    $i = 0;
    if (!empty($res)) {
        foreach ($res as $row) {
            $row['other_images'] = json_decode($row['other_images'], 1);
            $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
            for ($j = 0; $j < count($row['other_images']); $j++) {
                $row['other_images'][$j] = !empty(DOMAIN_URL . $row['other_images'][$j]) ? DOMAIN_URL . $row['other_images'][$j] : "";
            }

            $row['size_chart'] = (empty($row['size_chart'])) ? '' : DOMAIN_URL . $row['size_chart'];
            $row['image'] = (empty($row['image'])) ? '' : DOMAIN_URL . $row['image'];
            $row['shipping_delivery'] = (!empty($row['shipping_delivery'])) ? $row['shipping_delivery'] : "";
            $row['subcategory_name'] = (!empty($row['subcategory_name'])) ? $row['subcategory_name'] : "";
            $row['tax_title'] = (isset($row['tax_title']) && !empty($row['tax_title'])) ? $row['tax_title'] : "";
            $row['tax_percentage'] = (isset($row['tax_percentage']) && !empty($row['tax_percentage'])) ? $row['tax_percentage'] : "0";
            $row['number_of_ratings'] = (isset($row['number_of_ratings']) && !empty($row['number_of_ratings'])) ? $row['number_of_ratings'] : "0";

            $sql = "SELECT pv.type,pv.id,pv.product_id,pv.price,pv.discounted_price,pv.serve_for,pv.stock,pv.measurement,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name,pv.images FROM product_variant pv WHERE pv.product_id=" . $row['id'] . "";
            $db->sql($sql);
            $variants = $db->getResult();
            // echo $sql;
            for ($k = 0; $k < count($variants); $k++) {
                $variants[$k]['images'] = json_decode($variants[$k]['images'], 1);
                $variants[$k]['images'] = (empty($variants[$k]['images'])) ? array() : $variants[$k]['images'];
                for ($j = 0; $j < count($variants[$k]['images']); $j++) {
                    $variants[$k]['images'][$j] = !empty(DOMAIN_URL . $variants[$k]['images'][$j]) ? DOMAIN_URL . $variants[$k]['images'][$j] : "";
                }

                if (!empty($user_id)) {
                    $sql = "SELECT qty as cart_count FROM cart where product_variant_id= " . $variants[$k]['id'] . " AND user_id= '$user_id' ";
                    $db->sql($sql);
                    $res_cart = $db->getResult();
                    $variants[$k]['cart_count'] = (!empty($res_cart[0]['cart_count'])) ? $res_cart[0]['cart_count'] : "0";
                    $row['is_added_to_cart'] = ($variants[$k]['cart_count'] != NULL) ? true : false;
                } else {
                    $variants[$k]['cart_count'] = "0";
                    $row['is_added_to_cart'] = ($variants[$k]['cart_count'] != 0) ? true : false;
                }

                if (!empty($user_id)) {
                    $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
                    $db->sql($sql);
                    $favorite = $db->getResult();
                    $row['is_favorite'] = !empty($favorite) ? true : false;
                } else {
                    $row['is_favorite'] = false;
                }

                $sql = "SELECT fp.price,fp.discounted_price,fp.start_date,fp.end_date FROM flash_sales_products fp LEFT JOIN flash_sales fs ON fs.id=fp.flash_sales_id where fp.status = 1 AND fp.product_variant_id= " . $variants[$k]['id'] . " AND  fp.product_id = " . $variants[$k]['product_id'] . " GROUP BY fp.id";
                $db->sql($sql);
                $res_flash_sale = $db->getResult();
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
                    }
                }
            }
            $products[$i] = $row;
            $products[$i]['variants'] = $variants;
            $i++;
        }
    }

    // $product = $fn->get_products($user_id, $product_id, $slug);
    $response['error'] = false;
    $response['message'] = "Products Retrived Successfully.";
    $response['total'] = count($res);;
    $response['data'] = $products;
    print_r(json_encode($response));
    return false;
} else {
    $response['error'] = true;
    $response['message'] = "accesskey is required.";
    print_r(json_encode($response));
}
