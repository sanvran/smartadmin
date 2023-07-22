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
1. add_to_cart
2. add_multiple_items
3. remove_from_cart
4. get_user_cart
5. add_save_for_later
6. remove_save_for_later
7. get_save_for_later
-------------------------------------------
-------------------------------------------
*/

if (!isset($_POST['accesskey'])) {
    $response['error'] = true;
    $response['message'] = "Access key is invalid or not passed!";
    print_r(json_encode($response));
    return false;
}

$accesskey = $db->escapeString($fn->xss_clean_array($_POST['accesskey']));
if ($access_key != $accesskey) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey!";
    print_r(json_encode($response));
    return false;
}

if (!verify_token()) {
    return false;
}

if ((isset($_POST['add_to_cart'])) && ($_POST['add_to_cart'] == 1)) {
    /*
    1.add_to_cart
        accesskey:90336
        add_to_cart:1
        user_id:3
        product_id:1
        product_variant_id:4
        qty:2
    */
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_id'])) : "";
    $product_id = (isset($_POST['product_id']) && !empty($_POST['product_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['product_id'])) : "";
    $product_variant_id  = (isset($_POST['product_variant_id']) && !empty($_POST['product_variant_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['product_variant_id'])) : "";
    $qty = (isset($_POST['qty']) && !empty($_POST['qty'])) ? $db->escapeString($fn->xss_clean_array($_POST['qty'])) : "";

    $sql = "SELECT * FROM users where id = $user_id";
    $db->sql($sql);
    $res1 = $db->getResult();
    if ($res1[0]['status'] == 1) {
        if (!empty($user_id) && !empty($product_id)) {
            if (!empty($product_variant_id)) {
                if ($fn->is_item_available($product_id, $product_variant_id)) {

                    $stock = $fn->get_data($columns = ['serve_for,stock'], 'id =' . $product_variant_id, 'product_variant');

                    if ($stock[0]['stock'] > 0 && $stock[0]['serve_for'] == 'Available') {
                        if ($fn->is_item_available_in_save_for_later($user_id, $product_variant_id)) {
                            $data = array(
                                'save_for_later' => 0
                            );
                            $db->update('cart', $data, 'user_id=' . $user_id . ' AND product_variant_id=' . $product_variant_id);
                        }
                        if ($fn->is_item_available_in_user_cart($user_id, $product_variant_id)) {
                            if (empty($qty) || $qty == 0) {
                                $sql = "DELETE FROM cart WHERE user_id = $user_id AND product_variant_id = $product_variant_id";
                                if ($db->sql($sql)) {
                                    $response['error'] = false;
                                    $response['message'] = 'Item removed user cart due to 0 quantity';
                                } else {
                                    $response['error'] = true;
                                    $response['message'] = 'Something went wrong please try again!';
                                }
                                print_r(json_encode($response));
                                return false;
                            }
                            $data = array(
                                'qty' => $qty
                            );
                            if ($db->update('cart', $data, 'user_id=' . $user_id . ' AND product_variant_id=' . $product_variant_id)) {
                                $response['error'] = false;
                                $response['message'] = 'Item updated in user cart successfully';
                            } else {
                                $response['error'] = true;
                                $response['message'] = 'Something went wrong please try again!';
                            }
                        } else {
                            $data = array(
                                'user_id' => $user_id,
                                'product_id' => $product_id,
                                'product_variant_id' => $product_variant_id,
                                'qty' => $qty
                            );
                            if ($db->insert('cart', $data)) {
                                $response['error'] = false;
                                $response['message'] = 'Item added to user cart successfully';
                            } else {
                                $response['error'] = true;
                                $response['message'] = 'Something went wrong please try again!';
                            }
                        }
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'Opps stock is not available!';
                    }
                } else {
                    $response['error'] = true;
                    $response['message'] = 'No such item available!';
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'Please choose atleast one item!';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'Please pass all the fields!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Your Account is De-active ask on Customer Support!';
    }
    print_r(json_encode($response));
    return false;
}

if (((isset($_POST['add_multiple_items'])) && ($_POST['add_multiple_items'] == 1)) || ((isset($_POST['save_for_later_items'])) && ($_POST['save_for_later_items'] == 1))) {
    /*
    2.add_multiple_items
        accesskey:90336
        add_multiple_items OR save_for_later_items:1
        user_id:3
        product_variant_id:203,198,202
        qty:1,2,1
    */
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_id'])) : "";
    $product_variant_id  = (isset($_POST['product_variant_id']) && !empty($_POST['product_variant_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['product_variant_id'])) : "";
    $qty = (isset($_POST['qty']) && !empty($_POST['qty'])) ? $db->escapeString($fn->xss_clean_array($_POST['qty'])) : "";

    $empty_qty = $is_variant =  $is_product = false;
    $empty_qty_1 = false;
    $item_exists = false;
    $item_exists_1 = false;
    $item_exists_2 = false;

    $res1 = $fn->get_data('', 'id =' . $user_id, 'users');

    if ($res1[0]['status'] == 1) {
        if (!empty($user_id)) {
            if (!empty($product_variant_id)) {
                $product_variant_id = explode(",", $product_variant_id);
                $qty = explode(",", $qty);
                for ($i = 0; $i < count($product_variant_id); $i++) {
                    if ((isset($_POST['add_multiple_items'])) && ($_POST['add_multiple_items'] == 1)) {
                        if ($fn->get_product_id_by_variant_id($product_variant_id[$i])) {
                            $product_id = $fn->get_product_id_by_variant_id($product_variant_id[$i]);
                            if ($fn->is_item_available($product_id, $product_variant_id[$i])) {
                                if ($fn->is_item_available_in_save_for_later($user_id, $product_variant_id[$i])) {
                                    $data = array(
                                        'save_for_later' => 0
                                    );
                                    $db->update('cart', $data, 'user_id=' . $user_id . ' AND product_variant_id=' . $product_variant_id[$i]);
                                }
                                if ($fn->is_item_available_in_user_cart($user_id, $product_variant_id[$i])) {
                                    $item_exists = true;
                                    if (empty($qty[$i]) || $qty[$i] == 0) {
                                        $empty_qty = true;
                                        $sql = "DELETE FROM cart WHERE user_id = $user_id AND product_variant_id = $product_variant_id[$i]";
                                        $db->sql($sql);
                                    } else {
                                        $data = array(
                                            'qty' => $qty[$i]
                                        );
                                        $db->update('cart', $data, 'user_id=' . $user_id . ' AND product_variant_id=' . $product_variant_id[$i]);
                                    }
                                } else {
                                    if (!empty($qty[$i]) && $qty[$i] != 0) {
                                        $data = array(
                                            'user_id' => $user_id,
                                            'product_id' => $product_id,
                                            'product_variant_id' => $product_variant_id[$i],
                                            'qty' => $qty[$i]
                                        );
                                        $db->insert('cart', $data);
                                    } else {
                                        $empty_qty_1 = true;
                                    }
                                }
                            } else {
                                $is_variant = true;
                            }
                        } else {
                            $is_product = true;
                        }
                    } else if ((isset($_POST['save_for_later_items'])) && ($_POST['save_for_later_items'] == 1)) {
                        if ($fn->is_item_available_in_user_cart($user_id, $product_variant_id[$i])) {
                            $item_exists_1 = true;
                            $data = array(
                                'save_for_later' => 1
                            );
                            $db->update('cart', $data, 'user_id=' . $user_id . ' AND product_variant_id=' . $product_variant_id[$i]);
                        } else {
                            $item_exists_2 = true;
                        }
                    }
                }
                $response['error'] = false;
                $response['message'] = $item_exists == true ? 'Cart Updated successfully!' : 'Cart Added Successfully';
                $response['message'] .= $item_exists_1 == true ? 'Item add to save for later!' : '';
                $response['message'] .= $item_exists_2 == true ? 'Item not add into cart!' : '';
                $response['message'] .= $empty_qty == true ? 'Some items removed due to 0 quantity' : '';
                $response['message'] .= $empty_qty_1 == true ? 'Some items not added due to 0 quantity' : '';
                $response['message'] .= $is_variant == true ? 'Some items not present in product list now' : '';
                $response['message'] .= $is_product == true ? 'Some items not present in product list now' : '';
            } else {
                $response['error'] = true;
                $response['message'] = 'Please choose atleast one item!';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'Please pass all the fields!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Your Account is De-active ask on Customer Support!';
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['remove_from_cart'])) && ($_POST['remove_from_cart'] == 1)) {
    /*
    3.remove_from_cart
        accesskey:90336
        remove_from_cart:1
        user_id:3
        product_variant_id:4    // {optional}
    */
    $user_id  = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_id'])) : "";
    $product_variant_id = (isset($_POST['product_variant_id']) && !empty($_POST['product_variant_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['product_variant_id'])) : "";

    if (!empty($user_id)) {
        if ($fn->is_item_available_in_user_cart($user_id, $product_variant_id)) {
            $sql = "DELETE FROM cart WHERE user_id=" . $user_id . " AND save_for_later = 0";
            $sql .= !empty($product_variant_id) ? " AND product_variant_id=" . $product_variant_id : "";
            if ($db->sql($sql) && !empty($product_variant_id)) {
                $response['error'] = false;
                $response['message'] = 'Item removed from user cart successfully';
            } elseif ($db->sql($sql) && empty($product_variant_id)) {
                $response['error'] = false;
                $response['message'] = 'All items removed from user cart successfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Something went wrong please try again!';
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'Item not found in user cart!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }

    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['get_user_cart'])) && ($_POST['get_user_cart'] == 1)) {
    /*
    4.get_user_cart
        accesskey:90336
        get_user_cart:1
        user_id:3
    */

    $user_id  = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_id'])) : "";
    if (!empty($user_id)) {
        if ($fn->is_item_available_in_user_cart($user_id)) {
            // get cart data
            $res = $fn->get_cart_data("c.id,c.qty,c.user_id,c.save_for_later", "c.save_for_later = 0 AND c.user_id=" . $user_id, " LEFT JOIN cart c ON c.product_variant_id = pv.id ", "c.date_created");

            //  get save for later data
            $result = $fn->get_cart_data("c.id,c.qty,c.user_id,c.save_for_later", "c.save_for_later = 1 AND c.user_id=" . $user_id, " LEFT JOIN cart c ON c.product_variant_id = pv.id ", "c.date_created");

            if (!empty($res) || !empty($result)) {
                $response['error'] = false;
                $response['total'] = count($res);
                $response['message'] = 'Cart Data Retrived Successfully!';
                $response['data'] = array_values($res);
                $response['save_for_later'] = array_values($result);
            } else {
                $response['error'] = true;
                $response['message'] = "No item(s) found in users cart!";
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'No item(s) found in user cart!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Please pass all the fields!';
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['add_save_for_later'])) && ($_POST['add_save_for_later'] == 1)) {
    /*
    5.add_save_for_later
        accesskey:90336
        add_save_for_later:1
        user_id:221
        product_variant_id:462
    */

    if (empty($_POST['user_id']) || empty($_POST['product_variant_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_id'])) : "";
    $product_variant_id  = (isset($_POST['product_variant_id']) && !empty($_POST['product_variant_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['product_variant_id'])) : "";

    if ($fn->is_item_available_in_user_cart($user_id, $product_variant_id)) {
        $sql1 = "UPDATE cart SET save_for_later = 1 WHERE user_id = $user_id AND product_variant_id = $product_variant_id";
        if ($db->sql($sql1)) {
            $x = 0;

            $result = $fn->get_data('', 'save_for_later = 1 AND user_id = ' . $user_id . ' AND product_variant_id = ' . $product_variant_id . ' ', 'cart');

            $res1 = $fn->get_data($columns = ['qty,product_variant_id'], 'save_for_later = 1 AND user_id = ' . $user_id . ' ', 'cart');

            foreach ($result as $rows) {
                $result[$x]['item'] = $fn->get_data($columns = ['product_variant.*,p.shipping_delivery,p.name,p.is_cod_allowed,p.slug,p.image,p.other_images,p.size_chart,p.ratings,p.number_of_ratings,p.total_allowed_quantity,pr.review,t.percentage as tax_percentage,t.title as tax_title,product_variant.measurement,(select short_code from unit u where u.id=product_variant.stock_unit_id) as stock_unit_name,(select short_code from unit u where u.id=product_variant.measurement_unit_id) as unit'], 'product_variant.id =' . $rows['product_variant_id'], 'product_variant', 'LEFT JOIN products p ON p.id=product_variant.product_id LEFT JOIN taxes t ON t.id=p.tax_id LEFT JOIN product_reviews pr ON p.id = pr.product_id', 'GROUP BY product_variant.id');

                for ($z = 0; $z < count($result[$x]['item']); $z++) {

                    $res1 = $fn->get_data('', 'status = 1 AND product_id = "' . $result[$x]['item'][$z]['product_id'] . '" AND product_variant_id = "' . $result[$x]['item'][$z]['id'] . '" ', 'flash_sales_products');

                    $variant_images = str_replace("'", '"', $result[$x]['item'][$z]['images']);
                    $result[$x]['item'][$z]['images'] = json_decode($variant_images, 1);
                    $result[$x]['item'][$z]['images'] = (empty($result[$x]['item'][$z]['images'])) ? array() : $result[$x]['item'][$z]['images'];

                    for ($j = 0; $j < count($result[$x]['item'][$z]['images']); $j++) {
                        $result[$x]['item'][$z]['images'][$j] = !empty(DOMAIN_URL . $result[$x]['item'][$z]['images'][$j]) ? DOMAIN_URL . $result[$x]['item'][$z]['images'][$j] : "";
                    }

                    $result[$x]['item'][$z]['other_images'] = json_decode($result[$x]['item'][$z]['other_images']);
                    $result[$x]['item'][$z]['other_images'] = empty($result[$x]['item'][$z]['other_images']) ? array() : $result[$x]['item'][$z]['other_images'];
                    $result[$x]['item'][$z]['tax_percentage'] = empty($result[$x]['item'][$z]['tax_percentage']) ? "0" : $result[$x]['item'][$z]['tax_percentage'];
                    $result[$x]['item'][$z]['is_cod_allowed'] = empty($result[$x]['item'][$z]['is_cod_allowed']) ? "0" : $result[$x]['item'][$z]['is_cod_allowed'];
                    $result[$x]['item'][$z]['tax_title'] = empty($result[$x]['item'][$z]['tax_title']) ? "" : $result[$x]['item'][$z]['tax_title'];
                    $result[$x]['item'][$z]['shipping_delivery'] = empty($result[$x]['item'][$z]['shipping_delivery']) ? "" : $result[$x]['item'][$z]['shipping_delivery'];
                    $result[$x]['item'][$z]['size_chart'] = empty($result[$x]['item'][$z]['size_chart']) ? "" : $result[$x]['item'][$z]['size_chart'];
                    $result[$x]['item'][$z]['stock_unit_name'] = empty($result[$x]['item'][$z]['stock_unit_name']) ? "" : $result[$x]['item'][$z]['stock_unit_name'];
                    $result[$x]['item'][$z]['total_allowed_quantity'] = empty($result[$x]['item'][$z]['total_allowed_quantity']) ? "0" : $result[$x]['item'][$z]['total_allowed_quantity'];
                    $result[$x]['item'][$z]['number_of_ratings'] = !empty($result[$x]['item'][$z]['number_of_ratings']) ? $result[$x]['item'][$z]['number_of_ratings'] : "0";
                    $result[$x]['item'][$z]['ratings'] = !empty($result[$x]['item'][$z]['ratings']) ?  $result[$x]['item'][$z]['ratings'] : "0";
                    $result[$x]['item'][$z]['sku'] = !empty($result[$x]['item'][$z]['sku']) ?  $result[$x]['item'][$z]['sku'] : "";
                    $result[$x]['item'][$z]['review'] = !empty($result[$x]['item'][$z]['review']) ?  $result[$x]['item'][$z]['review'] : "";
                    $result[$x]['item'][$z]['price'] = empty($res1) ?  $result[$x]['item'][$z]['price'] : $res1[0]['price'];
                    $result[$x]['item'][$z]['discounted_price'] = empty($res1) ?  $result[$x]['item'][$z]['discounted_price'] : $res1[0]['discounted_price'];

                    if ($result[$x]['item'][$z]['stock'] <= 0 || $result[$x]['item'][$z]['serve_for'] == 'Sold Out') {
                        $result[$x]['item'][$z]['isAvailable'] = false;
                        $ready_to_add = true;
                    } else {
                        $result[$x]['item'][$z]['isAvailable'] = true;
                    }

                    for ($y = 0; $y < count($result[$x]['item'][$z]['other_images']); $y++) {
                        $other_images = DOMAIN_URL . $result[$x]['item'][$z]['other_images'][$y];
                        $result[$x]['item'][$z]['other_images'][$y] = $other_images;
                    }
                }
                for ($j = 0; $j < count($result[$x]['item']); $j++) {
                    $result[$x]['item'][$j]['image'] = !empty($result[$x]['item'][$j]['image']) ? DOMAIN_URL . $result[$x]['item'][$j]['image'] : "";
                    $result[$x]['item'][$j]['size_chart'] = !empty($result[$x]['item'][$j]['size_chart']) ? DOMAIN_URL . $result[$x]['item'][$j]['size_chart'] : "";
                }
                $x++;
            }
        }

        if (!empty($result)) {
            $response['error'] = false;
            $response['message'] = 'Item add to save for later!';
            $response['data'] = $result;
        } else {
            $response['error'] = true;
            $response['message'] = 'Item cannot add to save for later!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Item not found in user cart!';
        $response['data'] = array();
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['remove_save_for_later'])) && ($_POST['remove_save_for_later'] == 1)) {
    /*
    6.remove_save_for_later
        accesskey:90336
        remove_save_for_later:1
        user_id:3
        product_variant_id:456
    */
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_id'])) : "";
    $product_variant_id  = (isset($_POST['product_variant_id']) && !empty($_POST['product_variant_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['product_variant_id'])) : "";

    if ($fn->is_item_available_in_user_cart($user_id, $product_variant_id)) {
        $sql1 = "UPDATE cart SET save_for_later = 0 WHERE user_id = $user_id AND product_variant_id = $product_variant_id";
        if ($db->sql($sql1)) {
            $x = 0;

            $result = $fn->get_data('', 'user_id = ' . $user_id . ' AND product_variant_id = ' . $product_variant_id . '', 'cart');

            foreach ($result as $rows) {
                $result[$x]['item'] = $fn->get_data($columns = ['product_variant.*,p.shipping_delivery,p.name,p.is_cod_allowed,p.slug,p.image,p.other_images,p.size_chart,p.ratings,p.number_of_ratings,p.total_allowed_quantity,pr.review,t.percentage as tax_percentage,t.title as tax_title,product_variant.measurement,(select short_code from unit u where u.id=product_variant.stock_unit_id) as stock_unit_name,(select short_code from unit u where u.id=product_variant.measurement_unit_id) as unit'], 'product_variant.id =' . $rows['product_variant_id'], 'product_variant', 'LEFT JOIN products p ON p.id=product_variant.product_id LEFT JOIN taxes t ON t.id=p.tax_id LEFT JOIN product_reviews pr ON p.id = pr.product_id', 'GROUP BY product_variant.id');


                for ($z = 0; $z < count($result[$x]['item']); $z++) {
                    $res1 = $fn->get_data('', 'status = 1 AND product_id = "' . $result[$x]['item'][$z]['product_id'] . '" AND product_variant_id = "' . $result[$x]['item'][$z]['id'] . '" ', 'flash_sales_products');

                    $variant_images = str_replace("'", '"', $result[$x]['item'][$z]['images']);
                    $result[$x]['item'][$z]['images'] = json_decode($variant_images, 1);
                    $result[$x]['item'][$z]['images'] = (empty($result[$x]['item'][$z]['images'])) ? array() : $result[$x]['item'][$z]['images'];

                    for ($j = 0; $j < count($result[$x]['item'][$z]['images']); $j++) {
                        $result[$x]['item'][$z]['images'][$j] = !empty(DOMAIN_URL . $result[$x]['item'][$z]['images'][$j]) ? DOMAIN_URL . $result[$x]['item'][$z]['images'][$j] : "";
                    }

                    $result[$x]['item'][$z]['other_images'] = json_decode($result[$x]['item'][$z]['other_images']);
                    $result[$x]['item'][$z]['other_images'] = empty($result[$x]['item'][$z]['other_images']) ? array() : $result[$x]['item'][$z]['other_images'];
                    $result[$x]['item'][$z]['tax_percentage'] = empty($result[$x]['item'][$z]['tax_percentage']) ? "0" : $result[$x]['item'][$z]['tax_percentage'];
                    $result[$x]['item'][$z]['is_cod_allowed'] = empty($result[$x]['item'][$z]['is_cod_allowed']) ? "0" : $result[$x]['item'][$z]['is_cod_allowed'];
                    $result[$x]['item'][$z]['tax_title'] = empty($result[$x]['item'][$z]['tax_title']) ? "" : $result[$x]['item'][$z]['tax_title'];
                    $result[$x]['item'][$z]['shipping_delivery'] = empty($result[$x]['item'][$z]['shipping_delivery']) ? "" : $result[$x]['item'][$z]['shipping_delivery'];
                    $result[$x]['item'][$z]['size_chart'] = empty($result[$x]['item'][$z]['size_chart']) ? "" : $result[$x]['item'][$z]['size_chart'];
                    $result[$x]['item'][$z]['total_allowed_quantity'] = empty($result[$x]['item'][$z]['total_allowed_quantity']) ? "0" : $result[$x]['item'][$z]['total_allowed_quantity'];
                    $result[$x]['item'][$z]['stock_unit_name'] = empty($result[$x]['item'][$z]['stock_unit_name']) ? "0" : $result[$x]['item'][$z]['stock_unit_name'];
                    $result[$x]['item'][$z]['number_of_ratings'] = !empty($result[$x]['item'][$z]['number_of_ratings']) ? $result[$x]['item'][$z]['number_of_ratings'] : "0";
                    $result[$x]['item'][$z]['ratings'] = !empty($result[$x]['item'][$z]['ratings']) ?  $result[$x]['item'][$z]['ratings'] : "0";
                    $result[$x]['item'][$z]['review'] = !empty($result[$x]['item'][$z]['review']) ?  $result[$x]['item'][$z]['review'] : "";
                    $result[$x]['item'][$z]['price'] = empty($res1) ?  $result[$x]['item'][$z]['price'] : $res1[0]['price'];
                    $result[$x]['item'][$z]['discounted_price'] = empty($res1) ?  $result[$x]['item'][$z]['discounted_price'] : $res1[0]['discounted_price'];

                    if ($result[$x]['item'][$z]['stock'] <= 0 || $result[$x]['item'][$z]['serve_for'] == 'Sold Out') {
                        $result[$x]['item'][$z]['isAvailable'] = false;
                        $ready_to_add = true;
                    } else {
                        $result[$x]['item'][$z]['isAvailable'] = true;
                    }

                    for ($y = 0; $y < count($result[$x]['item'][$z]['other_images']); $y++) {
                        $other_images = DOMAIN_URL . $result[$x]['item'][$z]['other_images'][$y];
                        $result[$x]['item'][$z]['other_images'][$y] = $other_images;
                    }
                }
                for ($j = 0; $j < count($result[$x]['item']); $j++) {
                    $result[$x]['item'][$j]['image'] = !empty($result[$x]['item'][$j]['image']) ? DOMAIN_URL . $result[$x]['item'][$j]['image'] : "";
                    $result[$x]['item'][$j]['size_chart'] = !empty($result[$x]['item'][$j]['size_chart']) ? DOMAIN_URL . $result[$x]['item'][$j]['size_chart'] : "";
                }
                $x++;
            }
        }

        if (!empty($result)) {
            $response['error'] = false;
            $response['message'] = 'Item Remove from save for later!';
            $response['data'] = array_values($result);
        } else {
            $response['error'] = true;
            $response['message'] = 'Item cannot Remove from save for later!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Item not found in user cart!';
    }
    print_r(json_encode($response));
    return false;
}

if ((isset($_POST['get_save_for_later'])) && ($_POST['get_save_for_later'] == 1)) {
    /*
    7.get_save_for_later
        accesskey:90336
        get_save_for_later:1
        user_id:3
    */

    if (empty($_POST['user_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean_array($_POST['user_id'])) : "";

    if (!empty($user_id)) {

        $result = $fn->get_cart_data("c.id,c.qty,c.user_id,c.save_for_later", "c.save_for_later = 1 AND c.user_id=" . $user_id, " LEFT JOIN cart c ON c.product_variant_id = pv.id ", "c.date_created");

        if (!empty($result)) {
            $response['error'] = false;
            $response['message'] = 'Data retrived Successfully!';
            $response['total'] = count($result);
            $response['data'] = array_values($result);
        } else {
            $response['error'] = true;
            $response['message'] = 'Data not found!';
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Users not found in save for later!';
    }
    print_r(json_encode($response));
    return false;
}
