<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('send-email.php');
include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('../includes/variables.php');
include_once('verify-token.php');
include_once('../includes/functions.php');
$function = new functions;
$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$fn = new custom_functions();

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

if (!verify_token()) {
    return false;
}

if (!isset($_POST['accesskey'])  || trim($_POST['accesskey']) != $access_key) {
    $response['error'] = true;
    $response['message'] = "No Accsess key found!";
    print_r(json_encode($response));
    return false;
    exit();
}

/* 
1. get-all-data.php
	accesskey:90336
	user_id:413     // {optional}
	
*/
$user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
$limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 12;
$offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;

//categories
$sql = "SELECT * FROM category ORDER BY row_order ASC ";
$db->sql($sql);
$res_categories = $db->getResult();

for ($i = 0; $i < count($res_categories); $i++) {
    $res_categories[$i]['status'] = ($res_categories[$i]['status'] == NULL) ? 1 : 1;
    $res_categories[$i]['image'] = (!empty($res_categories[$i]['image'])) ? DOMAIN_URL . '' . $res_categories[$i]['image'] : '';
    $res_categories[$i]['web_image'] = (!empty($res_categories[$i]['web_image'])) ? DOMAIN_URL . '' . $res_categories[$i]['web_image'] : '';
}

// slider images
$sql = 'SELECT * from slider where status = 1 order by id DESC';
$db->sql($sql);
$res_slider_image = $db->getResult();
$temp = $slider_images = array();
if (!empty($res_slider_image)) {
    $response['error'] = false;
    foreach ($res_slider_image as $row) {
        $name = "";
        if ($row['type'] == 'category') {
            $sql = 'select `name` from category where id = ' . $row['type_id'] . ' order by id desc';
            $db->sql($sql);
            $result1 = $db->getResult();
            $name = (!empty($result1[0]['name'])) ? $result1[0]['name'] : "";
            $slug = $function->slugify($db->escapeString($fn->xss_clean($name)));
        }
        if ($row['type'] == 'product') {
            $sql = 'select `name`,`slug` from products where id = ' . $row['type_id'] . ' order by id desc';
            $db->sql($sql);
            $result2 = $db->getResult();
            $name = (!empty($result2[0]['name'])) ? $result2[0]['name'] : "";
            $slug = (!empty($result2[0]['slug'])) ? $result2[0]['slug'] : "";
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
        $slider_images[] = $temp;
    }
}

// featured sections
$sql = 'select * from `sections` order by row_order ASC';
$db->sql($sql);
$result = $db->getResult();
$response = $product_ids = $category_ids = $section = $variations = $featured_sections = $section_temp = array();
foreach ($result as $row) {
    $product_ids = !empty($row['product_ids']) ? explode(',', $row['product_ids']) : array();
    $category_ids = !empty($row['category_ids']) ? explode(',', $row['category_ids']) : array();

    $section['id'] = $row['id'];
    $section['row_order'] = $row['row_order'];
    $section['title'] = $row['title'];
    $section['short_description'] = $row['short_description'];
    $section['style'] = $row['style'];
    $section['product_type'] = $row['product_type'];

    $sort = "";
    $where = "";
    $group = "";
    $section_offer_images = [];
    $cate_ids = $row['category_ids'];

    $res_section_images = $fn->get_offers('below_section', $row['title']);
    foreach ($res_section_images as $offer_row) {
        $section_temp['type'] = !empty($offer_row['type']) && isset($offer_row['type']) ? $offer_row['type'] : "image";
        $section_temp['image'] = !empty($offer_row['image']) && $offer_row['image'] != 'null' ? DOMAIN_URL . $offer_row['image'] : "";
        $section_offer_images[] = $section_temp;
    }

    if ($row['product_type'] == 'all_products') {
        if (empty($row['category_ids'])) {
            $sql = "SELECT id as product_id FROM `products` WHERE status = 1 ORDER BY product_id DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY p.id DESC ";
        } else {
            $sql = "SELECT id as product_id FROM `products` WHERE status = 1 AND category_id IN($cate_ids) ORDER BY product_id DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY p.id DESC ";
        }
    } elseif ($row['product_type'] == 'new_added_products') {
        if (empty($row['category_ids'])) {
            $sql = "SELECT id as product_id FROM `products` WHERE status = 1 ORDER BY product_id DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY p.date_added DESC ";
        } else {
            $sql = "SELECT id as product_id FROM `products` WHERE status = 1 AND category_id IN($cate_ids) ORDER BY product_id DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY p.date_added DESC ";
        }
    } elseif ($row['product_type'] == 'products_on_sale') {
        if (empty($row['category_ids'])) {
            $sql = "SELECT p.id as product_id FROM `products` p LEFT JOIN product_variant pv ON p.id=pv.product_id WHERE p.status = 1 AND pv.discounted_price > 0 AND pv.price > pv.discounted_price ORDER BY p.id DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY p.id DESC ";
            $where .= " AND pv.discounted_price > 0 AND pv.price > pv.discounted_price";
        } else {
            $sql = "SELECT p.id as product_id FROM `products` p LEFT JOIN product_variant pv ON p.id=pv.product_id WHERE p.status = 1 AND p.category_id IN($cate_ids) AND pv.discounted_price > 0 AND pv.price > pv.discounted_price ORDER BY p.id DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY p.id DESC ";
            $where .= " AND pv.discounted_price > 0 AND pv.price > pv.discounted_price";
        }
    } elseif ($row['product_type'] == 'top_rated_products') {
        if (empty($row['category_ids'])) {
            $sql = "SELECT pr.product_id FROM `product_reviews` pr LEFT JOIN products p ON p.id=pr.product_id WHERE p.status = 1 ORDER BY rate DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY pr.rate DESC ";
        } else {
            $sql = "SELECT pr.product_id FROM `product_reviews` pr LEFT JOIN products p ON p.id=pr.product_id WHERE p.status = 1 AND p.category_id IN ($cate_ids) ORDER BY rate DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY pr.rate DESC ";
        }
    } elseif ($row['product_type'] == 'most_selling_products') {
        if (empty($row['category_ids'])) {
            $sql = "SELECT p.id as product_id,oi.product_variant_id, COUNT(oi.product_variant_id) AS total FROM order_items oi LEFT JOIN product_variant pv ON oi.product_variant_id = pv.id LEFT JOIN products p ON pv.product_id = p.id WHERE oi.product_variant_id != 0 AND p.id != '' GROUP BY pv.id,p.id ORDER BY total DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY COUNT(oi.product_variant_id) DESC ";
            $where .= " AND oi.product_variant_id != 0 AND p.id != ''";
        } else {
            $sql = "SELECT p.id as product_id,oi.product_variant_id, COUNT(oi.product_variant_id) AS total FROM order_items oi LEFT JOIN product_variant pv ON oi.product_variant_id = pv.id LEFT JOIN products p ON pv.product_id = p.id WHERE oi.product_variant_id != 0 AND p.id != '' AND p.category_id IN ($cate_ids) GROUP BY pv.id,p.id ORDER BY total DESC LIMIT $offset,$limit";
            $sort .= " ORDER BY COUNT(oi.product_variant_id) DESC ";
            $where .= " AND oi.product_variant_id != 0 AND p.id != ''";
        }
    } else {
        $product_ids = implode(',', $product_ids);
    }

    if ($row['product_type'] != 'custom_products' && empty($row['product_type'] == '')) {
        $db->sql($sql);
        $product = $db->getResult();
        $rows = $tempRow = array();
        foreach ($product as $row1) {
            $tempRow['product_id'] = $row1['product_id'];
            $rows[] = $tempRow;
        }
        $pro_id = array_column($rows, 'product_id');
        $product_ids = implode(",", $pro_id);
    }
    $group .= $row['product_type'] == 'most_selling_products' ? " GROUP BY pv.id" : " GROUP BY p.id";

    $sql = "SELECT count(id) as total FROM sections";
    $db->sql($sql);
    $total = $db->getResult();

    if (!empty($product_ids)) {

        $sql = "SELECT p.*,p.id as product_id,(SELECT name FROM category c WHERE c.id=p.category_id) as category_name
           FROM `products` p left join product_reviews pr on p.id = pr.product_id left join product_variant pv on p.id = pv.product_id left join order_items oi ON pv.id=oi.product_variant_id WHERE p.status = 1 AND p.id IN ($product_ids) $where $group $sort LIMIT $offset,$limit";

        $db->sql($sql);
        $result1 = $db->getResult();
        $product = array();
        $i = 0;
        foreach ($result1 as $row) {
            if (!empty($row['product_id'])) {
                $sql1 = "SELECT count(p.id) as total FROM products p WHERE p.status = 1 AND p.id IN ($product_ids)";
                $db->sql($sql1);
                $count = $db->getResult();
                $section['count_of_products'] = $count[0]['total'];

                $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['product_id'] . " ORDER BY serve_for ASC";
                $db->sql($sql);
                $variants = $db->getResult();

                $row['other_images'] = json_decode($row['other_images'], 1);
                $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
                $row['shipping_delivery'] = (!empty($row['shipping_delivery'])) ? $row['shipping_delivery'] : "";
                $row['made_in'] = (!empty($row['made_in'])) ? $row['made_in'] : "";
                $row['return_status'] = (!empty($row['return_status'])) ? $row['return_status'] : "";
                $row['cancelable_status'] = (!empty($row['cancelable_status'])) ? $row['cancelable_status'] : "";
                $row['till_status'] = (!empty($row['till_status'])) ? $row['till_status'] : "";
                $row['manufacturer'] = (!empty($row['manufacturer'])) ? $row['manufacturer'] : "";
                $row['size_chart'] = (!empty($row['size_chart'])) ? DOMAIN_URL . $row['size_chart'] : "";
                $row['review'] = (!empty($row['review'])) ? $row['review'] : "";
                $row['rate'] = (!empty($row['rate'])) ? $row['rate'] : "";
                $row['number_of_ratings'] = (!empty($row['number_of_ratings'])) ? $row['number_of_ratings'] : "0";
                $row['image'] = !empty($row['image']) ? DOMAIN_URL . $row['image'] : "";

                for ($j = 0; $j < count($row['other_images']); $j++) {
                    $row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
                }
                if ($row['tax_id'] == 0) {
                    $row['tax_title'] = "";
                    $row['tax_percentage'] = "0";
                } else {
                    $t_id = $row['tax_id'];
                    $sql_tax = "SELECT * from taxes where id= $t_id";
                    $db->sql($sql_tax);
                    $res_tax = $db->getResult();
                    foreach ($res_tax as $tax) {
                        $row['tax_title'] = $tax['title'];
                        $row['tax_percentage'] = $tax['percentage'];
                    }
                }

                for ($k = 0; $k < count($variants); $k++) {

                    $variants[$k]['images'] = json_decode($variants[$k]['images'], 1);
                    $variants[$k]['images'] = (empty($variants[$k]['images'])) ? array() : $variants[$k]['images'];
                    for ($j = 0; $j < count($variants[$k]['images']); $j++) {
                        $variants[$k]['images'][$j] = !empty(DOMAIN_URL . $variants[$k]['images'][$j]) ? DOMAIN_URL . $variants[$k]['images'][$j] : "";
                    }

                    $variants[$k]['sku'] = !empty($variants[$k]['sku']) ?  $variants[$k]['sku'] : "";

                    if (!empty($user_id)) {
                        $sql = "SELECT qty as cart_count FROM cart where product_variant_id= " . $variants[$k]['id'] . " AND user_id=" . $user_id;
                        $db->sql($sql);
                        $res = $db->getResult();
                        if (!empty($res)) {
                            foreach ($res as $row1) {
                                $variants[$k]['cart_count'] = $row1['cart_count'];
                            }
                        } else {
                            $variants[$k]['cart_count'] = "0";
                        }
                    } else {
                        $variants[$k]['cart_count'] = "0";
                    }

                    $sql = "SELECT fp.*,fs.title as flash_sales_name FROM flash_sales_products fp LEFT JOIN flash_sales fs ON fs.id=fp.flash_sales_id where fp.status = 1 AND fp.product_variant_id= " . $variants[$k]['id'] . " AND  fp.product_id = " . $variants[$k]['product_id'];
                    $db->sql($sql);
                    $result1 = $db->getResult();
                    if (!empty($result1)) {
                        $variants[$k]['is_flash_sales'] = true;
                    } else {
                        $variants[$k]['is_flash_sales'] = false;
                    }
                    $temp = array('id' => "", 'flash_sales_id' => "", 'product_id' => "", 'product_variant_id' => "", 'price' => "", 'discounted_price' => "", 'start_date' => "", 'end_date' => "", 'date_created' => "", 'status' => "", 'flash_sales_name' => "");
                    $variants[$k]['flash_sales'] = array($temp);
                    foreach ($result1 as $rows) {
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
                            $variants[$k]['flash_sales'] = array($rows);
                        }
                    }

                    if (!empty($user_id)) {
                        $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
                        $db->sql($sql);
                        $favorite = $db->getResult();
                        if (!empty($favorite)) {
                            $row['is_favorite'] = true;
                        } else {
                            $row['is_favorite'] = false;
                        }
                    } else {
                        $row['is_favorite'] = false;
                    }
                }
                $product[$i] = $row;
                $product[$i]['variants'] = $variants;
                $i++;
            }
        }
    }
    $section['offer_images'] = $section_offer_images;
    $section['products'] = $product;
    $featured_sections[] = $section;
    unset($section['products']);
    $section_offer_images = [];
}

// offer images
$res_offer_images = $fn->get_offers('top');
$response = $temp = $offer_images = array();
foreach ($res_offer_images as $row1) {
    $temp['type'] = !empty($row1['type']) && isset($row1['type']) ? $row1['type'] : "image";
    $temp['image'] = DOMAIN_URL . $row1['image'];
    $offer_images[] = $temp;
}

// category offer images
$res_offer_images = $fn->get_offers('below_category');
foreach ($res_offer_images as $row2) {
    $cate_temp['type'] = !empty($row2['type']) && isset($row2['type']) ? $row2['type'] : "image";
    $cate_temp['image'] = DOMAIN_URL . $row2['image'];
    $cate_offer_images[] = $cate_temp;
}

// slider offer images
$res_seller_images = $fn->get_offers('below_slider');
foreach ($res_seller_images as $row3) {
    $slider_temp['type'] = !empty($row3['type']) && isset($row3['type']) ? $row3['type'] : "image";
    $slider_temp['image'] = DOMAIN_URL . $row3['image'];
    $slider_offer_images[] = $slider_temp;
}

// flash sales offer images
$res_flash_sales_images = $fn->get_offers('below_flash_sales');
foreach ($res_flash_sales_images as $flash_sale_row) {
    $flash_sales_temps['type'] = !empty($flash_sale_row['type']) && isset($flash_sale_row['type']) ? $flash_sale_row['type'] : "image";
    $flash_sales_temps['image'] = DOMAIN_URL . $flash_sale_row['image'];
    $flash_sales_offer_images[] = $flash_sales_temps;
}

$data = $fn->get_settings('categories_settings', true);
$response['error'] = false;
$response['message'] = "Data fetched successfully";
if (!empty($data)) {
    $response['style'] =  $data['cat_style'];
    $response['visible_count'] = $data['max_visible_categories'];
    $response['column_count'] = ($data['cat_style'] == "style_2") ? 0 : $data['max_col_in_single_row'];
} else {
    $response['style'] =  "";
    $response['visible_count'] = 0;
    $response['column_count'] = 0;
}

// Flash sales products
$sql = "SELECT fs.* FROM `flash_sales` fs WHERE fs.status = 1 order by id ASC";
$db->sql($sql);
$result = $db->getResult();

$flash_sales = $variations = $flash_sales_section = $flash_sales_temp = array();
foreach ($result as $res) {
    $flash_sales['id'] = $res['id'];
    $flash_sales['title'] = $res['title'];
    $flash_sales['slug'] = $res['slug'];
    $flash_sales['short_description'] = $res['short_description'];
    $flash_sales['status'] = $res['status'];

    $sql_result = "SELECT product_id FROM flash_sales_products WHERE flash_sales_id IN (" . $res['id'] . ")";
    $db->sql($sql_result);
    $pro = $db->getResult();

    $b = array_column($pro, 'product_id');
    $a = implode(',', $b);
    if (!empty($pro)) {
        $sql = "select p.*,fp.id as flash_sales_id,fp.product_id,fp.product_variant_id,fp.price,fp.discounted_price,fp.end_date,fp.start_date,fp.status as sales_status,fs.title,fs.title as flash_sales_Name,fs.slug as flash_sales_slug,c.name as category_name from `flash_sales_products` fp LEFT JOIN flash_sales fs ON fs.id=fp.flash_sales_id LEFT JOIN product_reviews pr ON pr.id = fp.product_id LEFT JOIN products p ON p.id=fp.product_id JOIN category c ON p.category_id=c.id WHERE fp.status=1 AND fp.flash_sales_id = " . $flash_sales['id'] . " AND p.id IN ($a) order by fp.`id` DESC LIMIT $offset,$limit";
        $db->sql($sql);
        $result1 = $db->getResult();
        $product = array();
        $i = 0;
        foreach ($result1 as $row) {
            $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.id = " . $row['product_variant_id'] . " AND pv.product_id=" . $row['product_id'] . " ORDER BY serve_for ASC";
            $db->sql($sql);
            $variants = $db->getResult();

            $row['other_images'] = json_decode($row['other_images'], 1);
            $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
            $row['shipping_delivery'] = (!empty($row['shipping_delivery'])) ? $row['shipping_delivery'] : "";
            $row['made_in'] = (!empty($row['made_in'])) ? $row['made_in'] : "";
            $row['return_status'] = (!empty($row['return_status'])) ? $row['return_status'] : "";
            $row['cancelable_status'] = (!empty($row['cancelable_status'])) ? $row['cancelable_status'] : "";
            $row['till_status'] = (!empty($row['till_status'])) ? $row['till_status'] : "";
            $row['manufacturer'] = (!empty($row['manufacturer'])) ? $row['manufacturer'] : "";
            $row['size_chart'] = (!empty($row['size_chart'])) ? DOMAIN_URL . $row['size_chart'] : "";
            $row['review'] = (!empty($row['review'])) ? $row['review'] : "";
            $row['rate'] = (!empty($row['rate'])) ? $row['rate'] : "";
            $row['number_of_ratings'] = (!empty($row['number_of_ratings'])) ? $row['number_of_ratings'] : "0";
            $row['image'] = DOMAIN_URL . $row['image'];

            for ($j = 0; $j < count($row['other_images']); $j++) {
                $row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
            }
            if ($row['tax_id'] == 0) {
                $row['tax_title'] = "";
                $row['tax_percentage'] = "0";
            } else {
                $t_id = $row['tax_id'];
                $sql_tax = "SELECT * from taxes where id= $t_id";
                $db->sql($sql_tax);
                $res_tax = $db->getResult();
                foreach ($res_tax as $tax) {
                    $row['tax_title'] = $tax['title'];
                    $row['tax_percentage'] = $tax['percentage'];
                }
            }

            for ($k = 0; $k < count($variants); $k++) {

                $variants[$k]['images'] = json_decode($variants[$k]['images'], 1);
                $variants[$k]['images'] = (empty($variants[$k]['images'])) ? array() : $variants[$k]['images'];
                for ($j = 0; $j < count($variants[$k]['images']); $j++) {
                    $variants[$k]['images'][$j] = !empty(DOMAIN_URL . $variants[$k]['images'][$j]) ? DOMAIN_URL . $variants[$k]['images'][$j] : "";
                }

                $variants[$k]['sku'] = !empty($variants[$k]['sku']) ?  $variants[$k]['sku'] : "";

                if (!empty($user_id)) {
                    $sql = "SELECT qty as cart_count FROM cart where product_variant_id= " . $variants[$k]['id'] . " AND user_id=" . $user_id;
                    $db->sql($sql);
                    $res = $db->getResult();
                    if (!empty($res)) {
                        foreach ($res as $row1) {
                            $variants[$k]['cart_count'] = $row1['cart_count'];
                        }
                    } else {
                        $variants[$k]['cart_count'] = "0";
                    }
                } else {
                    $variants[$k]['cart_count'] = "0";
                }

                $sql = "SELECT fp.*,fs.title as flash_sales_name FROM flash_sales_products fp LEFT JOIN flash_sales fs ON fs.id=fp.flash_sales_id where fp.status = 1 AND fp.product_variant_id= " . $variants[$k]['id'] . " AND  fp.product_id = " . $variants[$k]['product_id'] . " AND fp.flash_sales_id = " . $flash_sales['id'] . " GROUP BY fp.id";
                $db->sql($sql);
                $result1 = $db->getResult();
                if (!empty($result1)) {
                    $variants[$k]['is_flash_sales'] = true;
                } else {
                    $variants[$k]['is_flash_sales'] = false;
                }
                $variants[$k]['flash_sales'] = array();
                $temp_data = array('id' => "", 'flash_sales_id' => "", 'product_id' => "", 'product_variant_id' => "", 'price' => "", 'discounted_price' => "", 'start_date' => "", 'end_date' => "", 'date_created' => "", 'status' => "", 'flash_sales_name' => "");
                $variants[$k]['flash_sales'] = array($temp_data);
                foreach ($result1 as $sales_result) {
                    $time = date("Y-m-d H:i:s");
                    $time1 = $sales_result['start_date'];
                    $time2 = $sales_result['end_date'];
                    $row_time['is_date_created'] = strtotime("$time");
                    $row_time['is_start_date'] = strtotime("$time1");
                    $row_time['is_end_date'] = strtotime("$time2");
                    if ($row_time['is_start_date'] > $row_time['is_date_created'] && $row_time['is_end_date'] > $row_time['is_date_created']) {
                        $sales_result['is_start'] = false;
                    } else {
                        $sales_result['is_start'] = true;
                    }
                    if ($variants[$k]['is_flash_sales'] = true) {
                        $variants[$k]['flash_sales'] = array($sales_result);
                    }
                }

                if (!empty($user_id)) {
                    $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
                    $db->sql($sql);
                    $favorite = $db->getResult();
                    if (!empty($favorite)) {
                        $row['is_favorite'] = true;
                    } else {
                        $row['is_favorite'] = false;
                    }
                } else {
                    $row['is_favorite'] = false;
                }
            }
            $product[$i] = $row;
            $product[$i]['variants'] = $variants;
            $i++;
        }
        $flash_sales['products'] = $product;
        $flash_sales_section[] = $flash_sales;
        unset($flash_sales['products']);
    }
}

//  Social Media
$sql_query = "SELECT * FROM social_media ORDER BY id ASC ";
$db->sql($sql_query);
$social_media = $db->getResult();

// Settings
$sql = "select variable, value from `settings` where 1";
$db->sql($sql);
$res = $db->getResult();

if (!empty($res)) {
    foreach ($res as $k => $v) {
        if ($v['variable'] == "system_timezone") {
            $system_timezone = (array)json_decode($v['value']);
            foreach ($system_timezone as $k => $v) {
                $settings[$k] = (is_array(json_decode($v, 1))) ? json_decode($v, 1) : $v;
            }
        } else {
            $settings[$v['variable']] = (is_array(json_decode($v['value'], 1))) ? json_decode($v['value'], 1) : $v['value'];
        }
    }
}

$response['category_offer_images'] = (!empty($cate_offer_images)) ? $cate_offer_images : [];
$response['slider_offer_images'] = (!empty($slider_offer_images)) ? $slider_offer_images : [];
$response['flash_sales_offer_images'] = (!empty($flash_sales_offer_images)) ? $flash_sales_offer_images : [];
$response['categories'] = (!empty($res_categories)) ? $res_categories : [];
$response['slider_images'] = (!empty($slider_images)) ? $slider_images : [];
$response['sections'] = (!empty($featured_sections)) ? $featured_sections : [];
$response['offer_images'] = (!empty($offer_images)) ? $offer_images : [];
$response['flash_sales'] = (!empty($flash_sales_section)) ? $flash_sales_section : [];
$response['social_media'] = (!empty($social_media)) ? $social_media : [];
$settings['front_end_settings']['favicon'] = DOMAIN_URL . 'dist/img/' . $settings['front_end_settings']['favicon'];
$settings['front_end_settings']['screenshots'] = DOMAIN_URL . 'dist/img/' . $settings['front_end_settings']['screenshots'];
$settings['front_end_settings']['google_play'] = DOMAIN_URL . 'dist/img/' . $settings['front_end_settings']['google_play'];
$settings['front_end_settings']['web_logo'] = DOMAIN_URL . 'dist/img/' . $settings['front_end_settings']['web_logo'];
$settings['front_end_settings']['loading'] = DOMAIN_URL . 'dist/img/' . $settings['front_end_settings']['loading'];
$response['settings'] = (!empty($settings)) ? $settings : [];
print_r(json_encode($response));
