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
1. add_to_favorites
2. remove_from_favorites
3. get_favorites
-------------------------------------------
-------------------------------------------
*/

if (!isset($_POST['accesskey'])) {
    $response['error'] = true;
    $response['message'] = "Access key is invalid or not passed!";
    print_r(json_encode($response));
    return false;
}
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey!";
    print_r(json_encode($response));
    return false;
}

if (!verify_token()) {
    return false;
}

if ((isset($_POST['add_to_favorites'])) && ($_POST['add_to_favorites'] == 1)) {
    /*
    1.add_to_favorites
        accesskey:90336
        add_to_favorites:1
        user_id:3
        product_id:1
    */
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $product_id = (isset($_POST['product_id']) && !empty($_POST['product_id'])) ? $db->escapeString($fn->xss_clean($_POST['product_id'])) : "";
    if (!empty($user_id) && !empty($product_id)) {
        if ($fn->is_product_available($product_id)) {
            if (!$fn->is_product_added_as_favorite($user_id, $product_id)) {
                $data = array(
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                );
                if ($db->insert('favorites', $data)) {
                    $response['error'] = false;
                    $response['message'] = 'Item added in user\'s favorite list successfully';
                } else {
                    $response['error'] = true;
                    $response['message'] = 'Something went wrong please try again!';
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'Product already added as favorite!';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'No such product available!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['remove_from_favorites'])) && ($_POST['remove_from_favorites'] == 1)) {
    /*
    2.remove_from_favorites
        accesskey:90336
        remove_from_favorites:1
        id:3 OR user_id : 413 OR (user_id and product_id)
    */
    $id  = (isset($_POST['id']) && !empty($_POST['id'])) ? $db->escapeString($fn->xss_clean($_POST['id'])) : "";
    $user_id  = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $product_id  = (isset($_POST['product_id']) && !empty($_POST['product_id'])) ? $db->escapeString($fn->xss_clean($_POST['product_id'])) : "";
    if (!empty($id) || !empty($user_id)) {
        $where = '';
        $where .= !empty($id) ? ' WHERE id = ' . $id : '';
        $where .= !empty($user_id) && empty($product_id) ? ' WHERE user_id = ' . $user_id : '';
        $where .= !empty($user_id) && !empty($product_id) ? ' WHERE user_id = ' . $user_id . ' AND product_id = ' . $product_id : '';
        $sql = "DELETE FROM favorites" . $where;
        if ($db->sql($sql) && empty($user_id)) {
            $response['error'] = false;
            $response['message'] = 'Item removed from user\'s favorite list successfully';
        } elseif ($db->sql($sql) && !empty($user_id) && empty($product_id)) {
            $response['error'] = false;
            $response['message'] = 'All items removed from user\'s favorite list successfully';
        } elseif ($db->sql($sql) && !empty($user_id) && !empty($product_id)) {
            $response['error'] = false;
            $response['message'] = 'Item removed from user\'s favorite list successfully';
        } else {
            $response['error'] = true;
            $response['message'] = 'Something went wrong please try again!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass id or user id!';
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['get_favorites'])) && ($_POST['get_favorites'] == 1)) {
    /*
    3.get_favorites
        accesskey:90336
        get_favorites:1
        user_id:3
        offset:0 {optional}
        limit:5 {optional}
    */
    $user_id  = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;
    if (!empty($user_id)) {

        $sql = "SELECT count(id) as total from favorites where user_id=" . $user_id;
        $db->sql($sql);
        $total = $db->getResult();

        $res = $fn->get_data($columns = ['favorites.id AS favourite_id,favorites.user_id,favorites.product_id as id,p.row_order,p.name,p.slug,p.category_id,p.total_allowed_quantity ,p.is_cod_allowed,p.tax_id,(SELECT name from category c where c.id=p.category_id) as category_name,p.subcategory_id,p.indicator,p.manufacturer,p.made_in,p.return_status,p.cancelable_status,p.till_status,p.image,p.other_images,p.size_chart,p.description,p.shipping_delivery,p.status,p.date_added,p.ratings,p.number_of_ratings,pr.review,pr.rate'], 'favorites.user_id=' . $user_id, 'favorites', 'LEFT JOIN products p ON favorites.product_id=p.id left join product_reviews pr on p.id = pr.product_id', 'GROUP BY p.id', 'ORDER BY favorites.date_created DESC', 'LIMIT ' . $offset . ',' . $limit);

        $i = 0;
        $j = 0;
        $product = [];
        if (!empty($res)) {
            foreach ($res as $row) {
                if ($fn->is_product_available($row['id'])) {
                    $result = $fn->get_data($columns = ['id'], 'product_id = "' . $row['id'] . '" AND user_id = "'  . $user_id . '" ', 'favorites');

                    if (!empty($result)) {
                        $row['is_favorite'] = true;
                    } else {
                        $row['is_favorite'] = false;
                    }

                    $variants = $fn->get_data($columns = ['*,(SELECT short_code FROM unit u WHERE u.id=product_variant.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=product_variant.stock_unit_id) as stock_unit_name'], 'product_variant.product_id="' . $row['id'] . '" ', 'product_variant');

                    $row['other_images'] = json_decode($row['other_images'], 1);
                    $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
                    $row['shipping_delivery'] = !(empty($row['shipping_delivery'])) ? $row['shipping_delivery'] : "";
                    $row['size_chart'] = (empty($row['size_chart'])) ? '' : DOMAIN_URL . $row['size_chart'];
                    $row['number_of_ratings'] = !empty($row['number_of_ratings']) ? $row['number_of_ratings'] : "0";
                    $row['ratings'] = !empty($row['ratings']) ?  $row['ratings'] : "0";
                    $row['review'] = !empty($row['review']) ?  $row['review'] : "";
                    $row['rate'] = !empty($row['rate']) ?  $row['rate'] : "";

                    for ($j = 0; $j < count($row['other_images']); $j++) {
                        $row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
                    }
                    $row['image'] = !empty($row['image']) ? DOMAIN_URL . $row['image'] : "";

                    if ($row['tax_id'] == 0) {
                        $row['tax_title'] = "";
                        $row['tax_percentage'] = "0";
                    } else {
                        $t_id = $row['tax_id'];
                        $res_tax1 = $fn->get_data('', 'id = ' . $t_id, 'taxes');
                        foreach ($res_tax1 as $tax1) {
                            $row['tax_title'] = (!empty($tax1['title'])) ? $tax1['title'] : "";
                            $row['tax_percentage'] =  (!empty($tax1['percentage'])) ? $tax1['percentage'] : "0";
                        }
                    }

                    for ($k = 0; $k < count($variants); $k++) {

                        $variant_images = str_replace("'", '"', $variants[$k]['images']);
                        $variants[$k]['images'] = json_decode($variant_images, 1);
                        $variants[$k]['images'] = (empty($variants[$k]['images'])) ? array() : $variants[$k]['images'];

                        for ($j = 0; $j < count($variants[$k]['images']); $j++) {
                            $variants[$k]['images'][$j] = !empty(DOMAIN_URL . $variants[$k]['images'][$j]) ? DOMAIN_URL . $variants[$k]['images'][$j] : "";
                        }

                        $variants[$k]['sku'] = !empty($variants[$k]['sku']) ?  $variants[$k]['sku'] : "";

                        $res = $fn->get_data($columns = ['qty as cart_count'], ' product_variant_id = "' . $variants[$k]['id'] . '" AND user_id = "'  . $user_id . '" ', 'cart');

                        if (!empty($res)) {
                            foreach ($res as $row1) {
                                $variants[$k]['cart_count'] = $row1['cart_count'];
                            }
                        } else {
                            $variants[$k]['cart_count'] = "0";
                        }

                        $result = $fn->get_data($columns=['*,fs.title as flash_sales_name'], ' flash_sales_products.product_variant_id = "' . $variants[$k]['id'] . '" AND flash_sales_products.product_id = "'  . $variants[$k]['product_id'] . '" ', 'flash_sales_products', 'LEFT JOIN flash_sales fs ON fs.id=flash_sales_products.flash_sales_id');

                        if (!empty($result)) {
                            $variants[$k]['is_flash_sales'] = "true";
                        } else {
                            $variants[$k]['is_flash_sales'] = "false";
                        }
                        $variants[$k]['flash_sales'] = array();
                        $temps = array('id' => "", 'flash_sales_id' => "", 'product_id' => "", 'product_variant_id' => "", 'price' => "", 'discounted_price' => "", 'start_date' => "", 'end_date' => "", 'date_created' => "", 'status' => "", 'flash_sales_name' => "");
                        $variants[$k]['flash_sales'] = array($temps);
                        foreach ($result as $rows) {
                            if ($variants[$k]['is_flash_sales'] = "true") {
                                $variants[$k]['flash_sales'] = array($rows);
                            }
                        }
                        $product[$i] = $row;
                        $product[$i]['variants'] = $variants;
                    }
                    $i++;

                    $response['error'] = false;
                    $response['total'] = $total[0]['total'];
                    $response['data'] = $product;
                }
            }
        } else {
            $response['error'] = true;
            $response['data'] = 'No item(s) found in user\'s favorite list!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }
    print_r(json_encode($response));
    return false;
}

/*
4.add_multiple_products_to_favorites
    accesskey:90336
    add_multiple_products_to_favorites:1
    user_id:3
    product_ids:1,2
*/
if ((isset($_POST['add_multiple_products_to_favorites'])) && ($_POST['add_multiple_products_to_favorites'] == 1)) {
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $product_ids = (isset($_POST['product_ids']) && !empty($_POST['product_ids'])) ? $db->escapeString($fn->xss_clean($_POST['product_ids'])) : "";
    
    if (!empty($user_id) && !empty($product_ids)) {
        $product_ids = explode(',', $product_ids);
        for ($i = 0; $i < count($product_ids); $i++) {
            if ($fn->is_product_available($product_ids[$i])) {
                if (!$fn->is_product_added_as_favorite($user_id, $product_ids[$i])) {
                    $data = array(
                        'user_id' => $user_id,
                        'product_id' => $product_ids[$i],
                    );
                    $db->insert('favorites', $data);
                }
            }
        }
        $response['error'] = false;
        $response['message'] = 'Items added in user\'s favorite list successfully';
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }

    print_r(json_encode($response));
    return false;
}
