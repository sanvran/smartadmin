<?php
header('Access-Control-Allow-Origin: *');
session_start();
include '../includes/crud.php';
include '../includes/variables.php';
include_once('verify-token.php');
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$db = new Database();
$function = new functions;
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
-------------------------------------------
APIs for eCart
-------------------------------------------
1. get-all-sections
2. get-notifications
3. get-delivery-boy-notifications
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

if ((isset($_POST['add-section'])) && ($_POST['add-section'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response["message"] =  '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        echo json_encode($response);
        return false;
    }
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['featured']['create'] == 0) {
        $response["message"] = "<p class='alert alert-danger'>You have no permission to create featured section.</p>";
        echo json_encode($response);
        return false;
    }

    $title = $db->escapeString($fn->xss_clean($_POST['title']));
    $short_description = $db->escapeString($fn->xss_clean($_POST['short_description']));
    $style = $db->escapeString($fn->xss_clean($_POST['style']));
    $product_type = $db->escapeString($fn->xss_clean($_POST['product_type']));
    $product_id = isset($_POST['product_ids']) ? $fn->xss_clean_array($_POST['product_ids']) : "";
    $product_ids = !empty($product_id) ? implode(',', $product_id) : "";

    $category_id = isset($_POST['category_ids']) ? $fn->xss_clean_array($_POST['category_ids']) : "";
    $category_ids = !empty($category_id) ? implode(',', $category_id) : "";

    $product_ids = $product_type == 'custom_products' ?  implode(',', $product_id) : "";

    $sql = "INSERT INTO `sections` (`title`,`style`,`short_description`,`product_ids`,`category_ids`,`product_type`) VALUES ('$title','$style','$short_description','$product_ids','$category_ids','$product_type')";
    $db->sql($sql);
    $res = $db->getResult();
    $response["message"] = "<p class = 'alert alert-success'>Section created Successfully</p>";
    $sql = "SELECT id FROM sections ORDER BY id DESC";
    $db->sql($sql);
    $res = $db->getResult();
    $response["id"] = $res[0]['id'];
    echo json_encode($response);
}

if ((isset($_POST['edit-section'])) && ($_POST['edit-section'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response["message"] =  '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        echo json_encode($response);
        return false;
    }
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['featured']['update'] == 0) {
        $response["message"] = "<p class='alert alert-danger'>You have no permission to update featured section.</p>";
        echo json_encode($response);
        return false;
    }

    $id = $db->escapeString($fn->xss_clean($_POST['section-id']));
    $style = $db->escapeString($fn->xss_clean($_POST['style']));
    $product_type = $db->escapeString($fn->xss_clean($_POST['product_type']));
    $short_description = $db->escapeString($fn->xss_clean($_POST['short_description']));
    $title = $db->escapeString($fn->xss_clean($_POST['title']));
    $product_id = isset($_POST['product_ids']) ? $fn->xss_clean_array($_POST['product_ids']) : "";
    $product_ids = !empty($product_id) ? implode(',', $product_id) : "";
    $category_id = isset($_POST['category_ids']) ? $fn->xss_clean_array($_POST['category_ids']) : "";
    $category_ids = !empty($category_id) ? implode(',', $category_id) : "";

    $product_ids = $product_type == 'custom_products' ?  implode(',', $product_id) : "";

    $sql = "UPDATE `sections` SET `title`='$title', `short_description`='$short_description', `style`='$style', `product_ids` = '$product_ids',`category_ids` = '$category_ids',`product_type` = '$product_type' WHERE `sections`.`id` = " . $id;
    $db->sql($sql);
    $res = $db->getResult();
    $response["message"] = "<p class='alert alert-success'>Section updated Successfully</p>";
    $response["id"] = $id;
    echo json_encode($response);
}

if (isset($_GET['type']) && $_GET['type'] != '' && $_GET['type'] == 'delete-section') {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        return 2;
    }
    $permissions = $fn->get_permissions($_SESSION['id']);
    if ($permissions['featured']['delete'] == 0) {
        echo 2;
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_GET['id']));

    $sql = 'DELETE FROM `sections` WHERE `id`=' . $id;
    if ($db->sql($sql)) {
        echo 1;
    } else {
        echo 0;
    }
}

if (isset($_POST['get-all-sections']) && $_POST['get-all-sections'] == 1) {
    if (!verify_token()) {
        return false;
    }
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;
    $section_id = (isset($_POST['section_id']) && is_numeric($_POST['section_id'])) ? $db->escapeString($fn->xss_clean($_POST['section_id'])) : "";
    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

    $sql = "select * from `sections` ";
    $sql .= (!empty($section_id)) ? " where `id` = $section_id " : "";
    $sql .= " order by `row_order` asc";
    $db->sql($sql);
    $result = $db->getResult();
    $response = $product_ids = $category_ids = $section = $variations = $temp = array();

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
        $cate_ids = $row['category_ids'];
        if ($row['product_type'] == 'all_products') {
            if (empty($row['category_ids'])) {
                $sql = "SELECT id as product_id FROM `products` WHERE status = 1 ORDER BY product_id DESC";
                $sort .= " ORDER BY p.date_added DESC ";
            } else {
                $sql = "SELECT id as product_id FROM `products` WHERE status = 1 AND category_id IN($cate_ids) ORDER BY product_id DESC";
                $sort .= " ORDER BY p.date_added DESC ";
            }
        } elseif ($row['product_type'] == 'new_added_products') {
            if (empty($row['category_ids'])) {
                $sql = "SELECT id as product_id FROM `products` WHERE status = 1 ORDER BY product_id DESC";
                $sort .= " ORDER BY p.date_added DESC ";
            } else {
                $sql = "SELECT id as product_id FROM `products` WHERE status = 1 AND category_id IN($cate_ids) ORDER BY product_id DESC";
                $sort .= " ORDER BY p.date_added DESC ";
            }
        } elseif ($row['product_type'] == 'products_on_sale') {
            if (empty($row['category_ids'])) {
                $sql = "SELECT p.id as product_id FROM `products` p LEFT JOIN product_variant pv ON p.id=pv.product_id WHERE p.status = 1 AND pv.discounted_price > 0 AND pv.price > pv.discounted_price ORDER BY p.id DESC";
                $sort .= " ORDER BY p.id DESC ";
                $where .= " AND pv.discounted_price > 0 AND pv.price > pv.discounted_price";
            } else {
                $sql = "SELECT p.id as product_id FROM `products` p LEFT JOIN product_variant pv ON p.id=pv.product_id WHERE p.status = 1 AND p.category_id IN($cate_ids) AND pv.discounted_price > 0 AND pv.price > pv.discounted_price ORDER BY p.id DESC";
                $sort .= " ORDER BY p.id DESC ";
                $where .= " AND pv.discounted_price > 0 AND pv.price > pv.discounted_price";
            }
        } elseif ($row['product_type'] == 'top_rated_products') {
            if (empty($row['category_ids'])) {
                $sql = "SELECT pr.product_id FROM `product_reviews` pr LEFT JOIN products p ON p.id=pr.product_id WHERE p.status = 1 ORDER BY rate DESC";
                $sort .= " ORDER BY pr.rate DESC ";
            } else {
                $sql = "SELECT pr.product_id FROM `product_reviews` pr LEFT JOIN products p ON p.id=pr.product_id WHERE p.status = 1 AND p.category_id IN ($cate_ids) ORDER BY rate DESC";
                $sort .= " ORDER BY pr.rate DESC ";
            }
        } elseif ($row['product_type'] == 'most_selling_products') {
            if (empty($row['category_ids'])) {
                $sql = "SELECT p.id as product_id,oi.product_variant_id, COUNT(oi.product_variant_id) AS total FROM order_items oi LEFT JOIN product_variant pv ON oi.product_variant_id = pv.id LEFT JOIN products p ON pv.product_id = p.id WHERE oi.product_variant_id != 0 AND p.id != '' GROUP BY pv.id,p.id ORDER BY total DESC";
                $sort .= " ORDER BY COUNT(oi.product_variant_id) DESC ";
                $where .= " AND oi.product_variant_id != 0 AND p.id != ''";
            } else {
                $sql = "SELECT p.id as product_id,oi.product_variant_id, COUNT(oi.product_variant_id) AS total FROM order_items oi LEFT JOIN product_variant pv ON oi.product_variant_id = pv.id LEFT JOIN products p ON pv.product_id = p.id WHERE oi.product_variant_id != 0 AND p.id != '' AND p.category_id IN ($cate_ids) GROUP BY pv.id,p.id ORDER BY total DESC";
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
            $sql1 = "SELECT count(p.id) as total FROM products p WHERE p.status = 1 AND p.id IN ($product_ids)";
            $db->sql($sql1);
            $count = $db->getResult();
            $section['count_of_products'] = $count[0]['total'];

            $sql = "SELECT p.*,p.id as product_id,pr.review,pr.rate,(SELECT name FROM category c WHERE c.id=p.category_id) as category_name
            FROM `products` p left join product_reviews pr on p.id = pr.product_id left join product_variant pv on p.id = pv.product_id left join order_items oi ON pv.id=oi.product_variant_id WHERE p.status = 1 AND p.id IN ($product_ids) $where $group $sort LIMIT $offset,$limit";
            $db->sql($sql);
            $result1 = $db->getResult();
            $product = array();
            $i = 0;
            foreach ($result1 as $row) {
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
                $row['number_of_ratings'] = (!empty($row['number_of_ratings'])) ? $row['number_of_ratings'] : "";
                $row['rate'] = (!empty($row['rate'])) ? $row['rate'] : "";
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
                        $variants[$k]['is_flash_sales'] = "true";
                    } else {
                        $variants[$k]['is_flash_sales'] = "false";
                    }
                    $flash_sales_temp = array('id' => "", 'flash_sales_id' => "", 'product_id' => "", 'product_variant_id' => "", 'price' => "", 'discounted_price' => "", 'start_date' => "", 'end_date' => "", 'date_created' => "", 'status' => "", 'flash_sales_name' => "");
                    $variants[$k]['flash_sales'] = array($flash_sales_temp);
                    foreach ($result1 as $rows) {
                        if ($variants[$k]['is_flash_sales'] = "true") {
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
        if (empty($section_id)) {
            $section['products'] = $product;
            $temp[] = $section;
            unset($section['products']);
        } else {
            $temp = $product;
        }
    }
    if (!empty($result)) {
        $response['error'] = false;
        $response['message'] = "Sections retrived successfully";
        $response['total'] = empty($section_id) ? $total[0]['total'] : $count[0]['total'];
        if (empty($section_id)) {
            $response['sections'] = $temp;
        } else {
            $response['data'] = $temp;
        }
    } else {
        $response['error'] = true;
        $response['message'] = "No section has been created yet";
    }
    print_r(json_encode($response));
}


if (isset($_POST['get-notifications']) && $_POST['get-notifications'] == 1) {
    if (!verify_token()) {
        return false;
    }

    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'DESC';
    $where = '';
    if (isset($_POST['offset']))
        $offset = $db->escapeString($fn->xss_clean($_POST['offset']));
    if (isset($_POST['limit']))
        $limit = $db->escapeString($fn->xss_clean($_POST['limit']));

    if (isset($_POST['sort']))
        $sort = $db->escapeString($fn->xss_clean($_POST['sort']));
    if (isset($_POST['order']))
        $order = $db->escapeString($fn->xss_clean($_POST['order']));

    if (isset($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_GET['search']));
        $where = " Where `id` like '%" . $search . "%' OR `title` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR `image` like '%" . $search . "%' OR `date_sent` like '%" . $search . "%' ";
    }

    $sql = "SELECT COUNT(`id`) as total FROM `notifications` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `notifications` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    if (empty($res)) {
        $response['error'] = true;
        $response['message'] = "Data not found!";
        print_r(json_encode($response));
        return false;
    }
    $bulkData = array();
    $bulkData['error'] = false;
    $bulkData['total'] = $total;
    $rows = array();
    $tempRow = array();

    foreach ($res as $row) {
        if ($row['type'] == 'product') {
            $sql = "SELECT * FROM `products` WHERE id= " . $row['type_id'];
            $db->sql($sql);
            $product = $db->getResult();
            $slug = !empty($product[0]['slug']) ? $product[0]['slug'] : "";
        } elseif ($row['type'] == 'category') {
            $sql = "SELECT * FROM `category` WHERE id= " . $row['type_id'];
            $db->sql($sql);
            $category = $db->getResult();
            $slug = $function->slugify($category[0]['name']);
        } else {
            $slug = '';
        }

        $tempRow['id'] = $row['id'];
        $tempRow['name'] = $row['title'];
        $tempRow['subtitle'] = $row['message'];
        $tempRow['slug'] = !empty($slug) ? $slug : "";
        $tempRow['type'] = $row['type'];
        $tempRow['type_id'] = $row['type_id'];
        $tempRow['image'] = (!empty($row['image'])) ? DOMAIN_URL . $row['image'] : "";
        $rows[] = $tempRow;
    }
    $bulkData['data'] = $rows;
    print_r(json_encode($bulkData));
}

if (isset($_POST['get-delivery-boy-notifications']) && $_POST['get-delivery-boy-notifications'] == 1) {
    $offset = 0;
    $limit = 10;
    $sort = 'id';
    $order = 'DESC';
    $where = '';
    if (isset($_POST['offset']))
        $offset = $db->escapeString($fn->xss_clean($_POST['offset']));
    if (isset($_POST['limit']))
        $limit = $db->escapeString($fn->xss_clean($_POST['limit']));

    if (isset($_POST['sort']))
        $sort = $db->escapeString($fn->xss_clean($_POST['sort']));
    if (isset($_POST['order']))
        $order = $db->escapeString($fn->xss_clean($_POST['order']));

    if (isset($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        $where = " Where `id` like '%" . $search . "%' OR `title` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR `date_created` like '%" . $search . "%' ";
    }
    if (isset($_POST['delivery_boy_id']) && !empty($_POST['delivery_boy_id'])) {
        $delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
        $where .= empty($where) ? ' where delivery_boy_id=' . $delivery_boy_id : 'and delivery_boy_id=' . $delivery_boy_id;
    }
    if (isset($_POST['type']) && !empty($_POST['type'])) {
        $type = $db->escapeString($fn->xss_clean($_POST['type']));
        $where .= empty($where) ? " where type='" . $type . "'" : " and type='" . $type . "'";
    }
    $sql = "SELECT COUNT(`id`) as total FROM `delivery_boy_notifications` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row)
        $total = $row['total'];

    $sql = "SELECT * FROM `delivery_boy_notifications` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    if (empty($res)) {
        $response['error'] = true;
        $response['message'] = "Data not found!";
        print_r(json_encode($response));
        return false;
    }
    $bulkData = $rows = $tempRow = array();
    $bulkData['error'] = false;
    $bulkData['total'] = $total;

    foreach ($res as $row) {
        $tempRow['id'] = $row['id'];
        $tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
        $tempRow['title'] = $row['title'];
        $tempRow['message'] = $row['message'];
        $tempRow['type'] = $row['type'];
        $tempRow['date_sent'] = $row['date_created'];
        $rows[] = $tempRow;
    }
    $bulkData['data'] = $rows;
    print_r(json_encode($bulkData));
}

function isJSON($string)
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}
