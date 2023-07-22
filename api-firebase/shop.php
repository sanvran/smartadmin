<?php
header('Access-Control-Allow-Origin: *');
include_once('../includes/crud.php');
$db = new Database();
$db->connect();
include_once('../includes/variables.php');
include_once('verify-token.php');
include_once('../includes/custom-functions.php');
$fn = new custom_functions;

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey'])) {
    $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : 'id';

    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : '12';
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : '0';

    if ($access_key_received == $access_key) {

        if ($sort == 'new') {
            $sort = 'ORDER BY date_added DESC';
            $price = 'MIN(discounted_price)';
            $price_sort = 'pv.discounted_price  ASC';
        } elseif ($sort == 'old') {
            $sort = 'ORDER BY date_added ASC';
            $price = 'MIN(discounted_price)';
            $price_sort = 'pv.discounted_price  ASC';
        } elseif ($sort == 'high') {
            $sort = 'ORDER BY price DESC';
            $price = 'MAX(if(pv.discounted_price > 0 && pv.discounted_price != 0, pv.discounted_price, pv.price))';
            $price_sort = 'pv.discounted_price DESC';
        } elseif ($sort == 'low') {
            $sort = 'ORDER BY price ASC';
            $price = 'MIN(if(pv.discounted_price > 0 && pv.discounted_price != 0, pv.discounted_price, pv.price))';
            $price_sort = 'pv.discounted_price  ASC';
        } else {
            $sort = 'ORDER BY p.row_order ASC';
            $price = 'MIN(discounted_price)';
            $price_sort = 'pv.id  ASC';
        }

        $where = '';
        $where1 = '';
        if (isset($_POST['s']) && $_POST['s'] != '') {
            $search = $db->escapeString($fn->xss_clean($_POST['s']));
            if (empty($where)) {
                $where .= " WHERE (p.`id` like '%" . $search . "%' OR p.`name` like '%" . $search . "%' OR p.`image` like '%" . $search . "%' OR p.`subcategory_id` like '%" . $search . "%' OR p.`slug` like '%" . $search . "%' OR p.`description` like '%" . $search . "%')";
            } else {
                $where .= " AND (p.`id` like '%" . $search . "%' OR p.`name` like '%" . $search . "%' OR p.`image` like '%" . $search . "%' OR p.`subcategory_id` like '%" . $search . "%' OR p.`slug` like '%" . $search . "%' OR p.`description` like '%" . $search . "%')";
            }
        }

        if (isset($_POST['section']) && intval($_POST['section']) && trim($_POST['section']) != "") {
            $sql = "select * from `sections` where sections.id = '" . intval($_POST['section']) . "'";
            $db->sql($sql);
            $categoriesult = $db->getResult();
            if (!empty($categoriesult)) {
                $product_ids = [];
                $category_ids = [];
                if (!empty($categoriesult[0]['product_ids'])) {
                    if (isset($categoriesult[0]['product_ids'])) {
                        $product_ids = explode(',', $categoriesult[0]['product_ids']);
                        $product_ids = implode(',', $product_ids);

                        $where .= empty($where) ? " WHERE p.id IN ($product_ids) " : " AND p.id IN ($product_ids)";
                    }
                }

                if (!empty($categoriesult[0]['category_ids'])) {
                    if (isset($categoriesult[0]['category_ids'])) {
                        $category_ids = explode(',', $categoriesult[0]['category_ids']);
                        $category_ids = implode(',', $category_ids);

                        $where .= empty($where) ? " WHERE p.category_id IN ($category_ids) " : " AND p.category_id IN ($category_ids)";
                    }
                }
            }
        }

        if (isset($_POST['category']) && trim($_POST['category']) != "") {
            $category_ids = explode(',', $_POST['category']);
            $category_id = implode(',', $category_ids);

            $where .= empty($where) ? " WHERE p.category_id IN($category_id)" : " AND p.category_id IN($category_id)";
        }

        if (isset($_POST['sub-category']) && trim($_POST['sub-category']) != "") {
            $category_ids = explode(',', $_POST['sub-category']);
            $category_id = implode(',', $category_ids);
            if (empty($where)) {
                $where .= " WHERE p.subcategory_id IN($category_id)";
            } elseif (isset($_POST['category']) && trim($_POST['category']) != "") {
                $where .= " AND p.subcategory_id IN($category_id)";
            } else {
                $where .= " AND p.subcategory_id IN($category_id)";
            }
        }

        if (isset($_POST['out_of_stock']) && $_POST['out_of_stock'] == 1) {
            $where .=  empty($where) ? " WHERE p.status = 1" : " AND  p.status = 1";

            if (isset($_POST['discount_filter']) && trim($_POST['discount_filter']) != "") {
                $where .= empty($where) ? " WHERE pv.discounted_price != 0" : " AND pv.discounted_price != 0";
            } else {
                $where .= empty($where) ? " WHERE pv.stock >= 0 AND pv.serve_for = 'Available'" : " AND pv.stock >= 0 AND pv.serve_for = 'Available'";
            }
        } elseif (isset($_POST['out_of_stock']) && $_POST['out_of_stock'] == 0) {
            if (isset($_POST['discount_filter']) && trim($_POST['discount_filter']) != "") {
                $where .=  empty($where) ? " WHERE pv.discounted_price != 0 " : " AND pv.discounted_price != 0";
            }
        } else {
            $where .= empty($where) ? " WHERE p.status = 1  " : " AND p.status = 1  ";
        }

        $sql = "SELECT count(p.id) as total,MIN((select MIN(if(discounted_price > 0, discounted_price, price)) from product_variant where product_variant.product_id = p.id)) as min_price, MAX((select MAX(if(discounted_price > 0, discounted_price, price)) from product_variant where product_variant.product_id = p.id)) as max_price FROM products p JOIN product_variant pv on p.id=pv.product_id $where ";

        $db->sql($sql);
        $totalResult = $db->getResult();
        $total = $totalResult[0]['total'];
        $min_price = $totalResult[0]['min_price'];
        $max_price = $totalResult[0]['max_price'];

        $sql = "SELECT count(p.id) as total,MIN((select MIN(if(discounted_price > 0, discounted_price, price)) from product_variant where product_variant.product_id = p.id)) as min_price, MAX((select MAX(if(discounted_price > 0, discounted_price, price)) from product_variant where product_variant.product_id = p.id)) as max_price FROM products p JOIN product_variant pv on p.id=pv.product_id";
        $db->sql($sql);
        $totalResult1 = $db->getResult();
        $total_min_price = $totalResult1[0]['min_price'];
        $total_max_price = $totalResult1[0]['max_price'];

        $sql = "SELECT p.*, c.name as category_name,ceil(((price-discounted_price)/price)*100) as cal_discount_percentage,(SELECT ceil(if(t.percentage > 0 , " . $price . " + ( " . $price . " * (t.percentage / 100)), " . $price . ")) FROM product_variant pv WHERE pv.product_id=p.id) as price, (select MIN(if(discounted_price > 0, discounted_price, price)) from product_variant where product_variant.product_id = p.id) as min_price, (select MAX(if(discounted_price > 0, discounted_price, price)) from product_variant where product_variant.product_id = p.id) as max_price FROM products p LEFT JOIN category c on c.id=p.category_id LEFT JOIN product_variant pv ON pv.product_id = p.id LEFT JOIN taxes t ON t.id = p.tax_id $where GROUP BY p.id";
        if (isset($_POST['min_price']) && isset($_POST['max_price']) && intval($_POST['max_price'])) {
            $sql .= " Having min_price > " . intval(intval($_POST['min_price']) - 1) . " and max_price < " . intval(intval($_POST['max_price']) + 1);
        }
        if (isset($_POST['discount_filter']) && isset($_POST['discount_filter']) && intval($_POST['discount_filter'])) {
            $sql .= empty($_POST['min_price']) && empty($_POST['max_price']) ? " HAVING  cal_discount_percentage >= " . $_POST['discount_filter'] : " AND cal_discount_percentage >= " . $_POST['discount_filter'];
        }
        $sql .= " $sort LIMIT $offset, $limit";
        // echo $sql;
        $db->sql($sql);
        $products = $db->getResult();

        $sql = "SELECT p.id, ceil(((price-discounted_price)/price)*100) as cal_discount_percentage,(SELECT " . $price . " FROM product_variant pv WHERE pv.product_id=p.id) as price, (select MIN(if(discounted_price > 0, discounted_price, price)) from product_variant where product_variant.product_id = p.id) as min_price, (select MAX(if(discounted_price > 0, discounted_price, price)) from product_variant where product_variant.product_id = p.id) as max_price FROM products p LEFT JOIN product_variant pv ON pv.product_id = p.id $where GROUP BY p.id";
        if (isset($_POST['min_price']) && isset($_POST['max_price']) && intval($_POST['max_price'])) {
            $sql .= " Having min_price > " . intval(intval($_POST['min_price']) - 1) . " and max_price < " . intval(intval($_POST['max_price']) + 1);
        }
        if (isset($_POST['discount_filter']) && isset($_POST['discount_filter']) && intval($_POST['discount_filter'])) {
            $sql .= empty($_POST['min_price']) && empty($_POST['max_price']) ? " HAVING  cal_discount_percentage >= " . $_POST['discount_filter'] : " AND cal_discount_percentage >= " . $_POST['discount_filter'];
        }
        $db->sql($sql);
        $totals = $db->getResult();
        $total = $db->numRows($totals);

        $addresses = array();
        foreach ($products as $key => $value) {
            $id = $value['id'];
            if (in_array($id, $addresses)) {
                unset($products[$key]);
            } else {
                $addresses[] = $id;
            }
        }
        unset($addresses);
        $product = array();
        $i = 0;
        foreach ($products as $row) {
            $row['other_images'] = json_decode($row['other_images'], 1);
            $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
            $row['price'] = ($row['price'] == 0) ? "0" : $row['price'];
            $row['shipping_delivery'] = (!empty($row['shipping_delivery'])) ? $row['shipping_delivery'] : '';
            $row['size_chart'] = (!empty($row['size_chart'])) ? DOMAIN_URL . $row['size_chart'] : "";
            $row['cal_discount_percentage'] = (!empty($row['cal_discount_percentage'])) ?  $row['cal_discount_percentage'] : "";

            for ($j = 0; $j < count($row['other_images']); $j++) {
                $row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
            }
            $row['image'] = DOMAIN_URL . $row['image'];
            if ($row['tax_id'] == 0) {
                $row['tax_title'] = "";
                $row['tax_percentage'] = "0";
            } else {
                $t_id = $row['tax_id'];
                $sql_tax = "SELECT * from taxes where id= $t_id";
                $db->sql($sql_tax);
                $res_tax1 = $db->getResult();
                foreach ($res_tax1 as $tax1) {
                    $row['tax_title'] = (!empty($tax1['title'])) ? $tax1['title'] : "";
                    $row['tax_percentage'] =  (!empty($tax1['percentage'])) ? $tax1['percentage'] : "0";
                }
            }
            if (!empty($user_id)) {
                $sql = "SELECT id from favorites where product_id = " . $row['id'] . " AND user_id = " . $user_id;
                $db->sql($sql);
                $categoriesult = $db->getResult();

                $row['is_favorite'] = !empty($categoriesult) ? true : false;
            } else {
                $row['is_favorite'] = false;
            }

            $product[$i] = $row;
            $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " $where1 ORDER BY serve_for ASC ," . $price_sort . "";

            $db->sql($sql);
            $variants = $db->getResult();
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
                    $categories = $db->getResult();
                    if (!empty($categories)) {
                        foreach ($categories as $row1) {
                            $variants[$k]['cart_count'] = $row1['cart_count'];
                        }
                    } else {
                        $variants[$k]['cart_count'] = "0";
                    }
                } else {
                    $variants[$k]['cart_count'] = "0";
                }
                $sql = "SELECT fp.*,fs.title as flash_sales_name FROM flash_sales_products fp LEFT JOIN flash_sales fs ON fs.id=fp.flash_sales_id where fp.product_variant_id= " . $variants[$k]['id'] . " AND  fp.product_id = " . $variants[$k]['product_id'];
                $db->sql($sql);
                $result1 = $db->getResult();

                $variants[$k]['is_flash_sales'] = !empty($result1) ? "true" : "false";

                $variants[$k]['flash_sales'] = array();
                $temp = array('id' => "", 'flash_sales_id' => "", 'product_id' => "", 'product_variant_id' => "", 'price' => "", 'discounted_price' => "", 'start_date' => "", 'end_date' => "", 'date_created' => "", 'status' => "", 'flash_sales_name' => "");
                $variants[$k]['flash_sales'] = array($temp);
                foreach ($result1 as $rows) {
                    if ($variants[$k]['is_flash_sales'] = "true") {
                        $variants[$k]['flash_sales'] = array($rows);
                    }
                }
            }
            $product[$i]['variants'] = $variants;
            $i++;
        }
        if (!empty($product)) {
            $sql_query = "SELECT *, (select count(*) from products where products.category_id = category.id) as total FROM category ORDER BY id ASC ";
            $db->sql($sql_query);
            $categories = $db->getResult();
            if (!empty($categories)) {
                for ($i = 0; $i < count($categories); $i++) {
                    $categories[$i]['image'] = (!empty($categories[$i]['image'])) ? DOMAIN_URL . '' . $categories[$i]['image'] : '';
                    $categories[$i]['web_image'] = (!empty($categories[$i]['web_image'])) ? DOMAIN_URL . '' . $categories[$i]['web_image'] : '';
                }
                $tmp = [];
                foreach ($categories as $r) {
                    $r['childs'] = [];

                    $db->sql("SELECT *, (select count(*) from products where products.subcategory_id = subcategory.id) as total FROM subcategory WHERE category_id = '" . $r['id'] . "' ORDER BY id ASC");
                    $childs = $db->getResult();
                    if (!empty($childs)) {
                        for ($i = 0; $i < count($childs); $i++) {
                            $childs[$i]['image'] = (!empty($childs[$i]['image'])) ? DOMAIN_URL . '' . $childs[$i]['image'] : '';
                            $r['childs'][$childs[$i]['slug']] = (array)$childs[$i];
                        }
                    }
                    $tmp[] = $r;
                }
                $categories = $tmp;
            }
            $output = json_encode(array(
                'error' => false,
                'category' => $categories,
                'total' => ($total != "") ? $total : 0,
                'min_price' => $min_price ?? 0,
                'max_price' => $max_price ?? 0,
                'total_min_price' => $total_min_price  ?? 0,
                'total_max_price' => $total_max_price  ?? 0,
                'data' => $product
            ));
        } else {
            $output = json_encode(array(
                'error' => true,
                'data' => 'No products available',
                'min_price' => $min_price ?? 0,
                'max_price' => $max_price ?? 0,
                'total_min_price' => $total_min_price  ?? 0,
                'total_max_price' => $total_max_price  ?? 0,
            ));
        }
    } else {
        $output = json_encode(array(
            'error' => true,
            'message' => 'accesskey is incorrect.'
        ));
    }
} else {
    $output = json_encode(array(
        'error' => true,
        'message' => 'accesskey is required.'
    ));
}
echo $output;

$db->disconnect();
