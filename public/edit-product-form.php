<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');

$fn = new custom_functions;
$function = new Functions;
if (isset($_GET['id'])) {
    $ID = $db->escapeString($fn->xss_clean($_GET['id']));
} else {
    return false;
    exit(0);
}
$config = $fn->get_configurations();
$settings['currency'] = $fn->get_settings('currency');

// create array variable to store category data
$category_data = array();
$product_status = "";
$sql = "select id,name from category order by id asc";
$db->sql($sql);
$category_data = $db->getResult();
$sql = "select * from subcategory";
$db->sql($sql);
$subcategory = $db->getResult();
$sql = "SELECT image, other_images , size_chart FROM products WHERE id =" . $ID;
$db->sql($sql);
$res = $db->getResult();
foreach ($res as $row) {
    $previous_menu_image = $row['image'];
    $other_images = $row['other_images'];
    $previous_size_chart = $row['size_chart'];
}
if (isset($_POST['btnEdit'])) {

    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
        return false;
    }

    if ($permissions['products']['update'] == 1) {
        $name = $_POST['name'];
        if (strpos($name, '-') !== false) {
            $temp = (explode("-", $name)[1]);
        } else {
            $temp = $name;
        }

        $slug = $function->slugify($temp);
        $id = $db->escapeString($fn->xss_clean($_GET['id']));
        $sql = "SELECT slug FROM products where id!=" . $id;
        $db->sql($sql);
        $res = $db->getResult();
        $i = 1;
        foreach ($res as $row) {
            if ($slug == $row['slug']) {
                $slug = $slug . '-' . $i;
                $i++;
            }
        }

        $subcategory_id = (isset($_POST['subcategory_id']) && $_POST['subcategory_id'] != '') ? $db->escapeString($fn->xss_clean($_POST['subcategory_id'])) : 0;
        $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
        $serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
        $description = $db->escapeString($fn->xss_clean($_POST['description']));
        $shipping_delivery = (isset($_POST['shipping_delivery']) && $_POST['shipping_delivery'] != '') ? $db->escapeString($fn->xss_clean($_POST['shipping_delivery'])) : '';
        $pr_status = $db->escapeString($fn->xss_clean($_POST['pr_status']));
        $manufacturer = (isset($_POST['manufacturer']) && $_POST['manufacturer'] != '') ? $db->escapeString($fn->xss_clean($_POST['manufacturer'])) : '';
        $made_in = (isset($_POST['made_in']) && $_POST['made_in'] != '') ? $db->escapeString($fn->xss_clean($_POST['made_in'])) : '';
        $indicator = (isset($_POST['indicator']) && $_POST['indicator'] != '') ? $db->escapeString($fn->xss_clean($_POST['indicator'])) : '0';
        $return_status = (isset($_POST['return_status']) && $_POST['return_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['return_status'])) : '0';
        $cancelable_status = (isset($_POST['cancelable_status']) && $_POST['cancelable_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['cancelable_status'])) : '0';
        $cod_allowed = (isset($_POST['cod_allowed_status']) && $_POST['cod_allowed_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['cod_allowed_status'])) : '0';
        $till_status = (isset($_POST['till_status']) && $_POST['till_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['till_status'])) : '';
        $total_allowed_quantity = (isset($_POST['total_allowed_quantity']) && !empty($_POST['total_allowed_quantity'])) ? $db->escapeString($fn->xss_clean($_POST['total_allowed_quantity'])) : 0;

        $tax_id = (isset($_POST['tax_id']) && $_POST['tax_id'] != '') ? $db->escapeString($fn->xss_clean($_POST['tax_id'])) : 0;

        $error = array();
        if (empty($name)) {
            $error['name'] = " <span class='label label-danger'>Required!</span>";
        }
        if ($cancelable_status == 1 && $till_status == '') {
            $error['cancelable'] = " <span class='label label-danger'>Required!</span>";
        }

        if (empty($category_id)) {
            $error['category_id'] = " <span class='label label-danger'>Required!</span>";
        }
        if (empty($serve_for)) {
            $error['serve_for'] = " <span class='label label-danger'>Not choosen</span>";
        }

        if (empty($description)) {
            $error['description'] = " <span class='label label-danger'>Required!</span>";
        }

        error_reporting(E_ERROR | E_PARSE);

        // get image info
        if (isset($_FILES['image']) && !empty($_FILES['image'])) {
            $image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
            $image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
            $image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));

            $extension = end(explode(".", $_FILES["image"]["name"]));

            if (!empty($image)) {
                $result = $fn->validate_image($_FILES["image"]);
                if ($result) {
                    $error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                }
            }
        }

        //size chart 
        if (isset($_FILES['size_chart']) && !empty($_FILES['size_chart'])) {
            $size_chart = $db->escapeString($fn->xss_clean($_FILES['size_chart']['name']));
            $image_error1 = $db->escapeString($fn->xss_clean($_FILES['size_chart']['error']));
            $image_type1 = $db->escapeString($fn->xss_clean($_FILES['size_chart']['type']));

            $extension1 = end(explode(".", $_FILES["size_chart"]["name"]));

            if (!empty($size_chart)) {
                $result = $fn->validate_image($_FILES["size_chart"]);
                if ($result) {
                    $error['size_chart'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                }
            }
        }

        /*updating other_images if any*/
        if (isset($_FILES['other_images']) && ($_FILES['other_images']['size'][0] > 0)) {
            $file_data = array();
            $target_path = 'upload/other_images/';

            for ($i = 0; $i < count($_FILES["other_images"]["name"]); $i++) {
                if ($_FILES["other_images"]["error"][$i] > 0) {
                    $error['other_images'] = " <span class='label label-danger'>Images not uploaded!</span>";
                } else {
                    $result = $fn->validate_other_images($_FILES["other_images"]["tmp_name"][$i], $_FILES["other_images"]["type"][$i]);
                    if ($result) {
                        $error['other_images'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                    }
                }
                $filename = $_FILES["other_images"]["name"][$i];
                $temp = explode('.', $filename);
                $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                $file_data[] = $target_path . '' . $filename;

                if (!move_uploaded_file($_FILES["other_images"]["tmp_name"][$i], $target_path . '' . $filename))
                    echo "{$_FILES['image']['name'][$i]} not uploaded<br/>";
            }
            if (!empty($other_images)) {
                $arr_old_images = json_decode($other_images);
                $all_images = array_merge($arr_old_images, $file_data);
                $all_images = json_encode(array_values($all_images));
            } else {
                $all_images = $db->escapeString(json_encode($file_data));
            }
            if (empty($error)) {
                $sql = "update `products` set `other_images`='" . $all_images . "' where `id`=" . $ID;
                $db->sql($sql);
            }
        }

        if (!empty($name) && !empty($category_id) &&  !empty($serve_for) && !empty($description) && empty($error['cancelable']) && empty($error)) {
            if (strpos($name, "'") !== false) {
                $name = str_replace("'", "''", "$name");
                if (strpos($description, "'") !== false)
                    $description = str_replace("'", "''", "$description");
            }

            if (isset($_FILES['size_chart']) && !empty($_FILES['size_chart']['name'])) {
                // create random image file name
                $string = '0123456789';
                $file = preg_replace("/\s+/", "_", $_FILES['size_chart']['name']);
                $size_chart = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension1;
                // delete previous image
                $delete = unlink("$previous_size_chart");
                //upload new image
                $upload = move_uploaded_file($_FILES['size_chart']['tmp_name'], 'upload/images/' . $size_chart);
                //upload size chart
                $upload_image1 = 'upload/images/' . $size_chart;
                //update size chart
                $sql = "update `products` set `size_chart`='" . $upload_image1 . "' where `id`=" . $ID;
                $db->sql($sql);
            }

            if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
                // create random image file name
                $string = '0123456789';
                $file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
                $image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;
                // delete previous image
                $delete = unlink("$previous_menu_image");

                // upload new image
                $upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/images/' . $image);

                $upload_image = 'upload/images/' . $image;
                $sql_query = "UPDATE products SET name = '$name' , shipping_delivery = '$shipping_delivery' , tax_id = '$tax_id' ,slug = '$slug' , subcategory_id = '$subcategory_id', image = '$upload_image', description = '$description', indicator = '$indicator', manufacturer = '$manufacturer', made_in = '$made_in', return_status = '$return_status', cancelable_status = '$cancelable_status', till_status = '$till_status',`status` = $pr_status,`is_cod_allowed` = $cod_allowed,`total_allowed_quantity` = $total_allowed_quantity WHERE id = $ID";
                $db->sql($sql_query);
            } else {
                $sql_query = "UPDATE products SET name = '$name' ,shipping_delivery = '$shipping_delivery' ,tax_id = '$tax_id' ,slug = '$slug' ,category_id = '$category_id' ,subcategory_id = '$subcategory_id' ,description = '$description', indicator = '$indicator', manufacturer = '$manufacturer', made_in = '$made_in', return_status = '$return_status', cancelable_status = '$cancelable_status', till_status = '$till_status' ,`status` = $pr_status,`is_cod_allowed` = $cod_allowed,`total_allowed_quantity` = $total_allowed_quantity WHERE id = $ID";
                $db->sql($sql_query);
            }

            $res = $db->getResult();
            $product_variant_id = $db->escapeString($fn->xss_clean($_POST['product_variant_id']));
            if (isset($_POST['loose_measurement_unit_id']) && isset($_POST['packate_measurement_unit_id']) && $_POST['loose_measurement_unit_id'] != 0 && $_POST['packate_measurement_unit_id'] != 0 && $_POST['packate_measurement_unit_id'] < $_POST['loose_measurement_unit_id']) {
                $count = count($_POST['loose_measurement_unit_id']);
            } else {
                $count = count($_POST['packate_measurement_unit_id']);
            }
            for ($i = 0; $i < $count; $i++) {
                $vari_image = $fn->get_data($columns = ['id', 'images'], 'id=' . $fn->xss_clean($_POST['product_variant_id'][$i]), 'product_variant');
                $previous_variant_other_image = $vari_image[0]['images'];
                // print_r($previous_variant_other_image);

                if ($_POST['type'] == "packet") {
                    $stock = $db->escapeString($fn->xss_clean($_POST['packate_stock'][$i]));
                    $serve_for = ($stock == 0 || $stock <= 0) ? 'Sold Out' : $db->escapeString($fn->xss_clean($_POST['packate_serve_for'][$i]));
                    $all_images = '';

                    /*updating variant images if any*/
                    if (isset($_FILES['packet_variant_images']) && ($_FILES['packet_variant_images']['size'][$i][0] > 0)) {

                        $vari_id = $fn->xss_clean($_POST['product_variant_id'][$i]);

                        $file_data = array();
                        $target_path1 = 'upload/variant_images/';
                        if (!is_dir($target_path1)) {
                            mkdir($target_path1, 0777, true);
                        }

                        for ($k = 0; $k < count($_FILES["packet_variant_images"]["name"][$i]); $k++) {
                            if ($_FILES["packet_variant_images"]["error"][$i][$k] > 0) {
                                $error['packet_variant_images'] = " <span class='label label-danger'>Images not uploaded!</span>";
                            } else {
                                $result = $fn->validate_other_images($_FILES["packet_variant_images"]["tmp_name"][$i][$k], $_FILES["packet_variant_images"]["type"][$i][$k]);
                                if ($result) {
                                    $error['packet_variant_images'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                                }
                            }
                            $filename = $_FILES["packet_variant_images"]["name"][$i][$k];
                            $temp = explode('.', $filename);
                            $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                            $file_data[] = $target_path1 . '' . $filename;

                            if (!move_uploaded_file($_FILES["packet_variant_images"]["tmp_name"][$i][$k], $target_path1 . '' . $filename))
                                echo "{$_FILES['packet_variant_images']['name'][$i][$k]} not uploaded<br/>";
                        }
                        if (!empty($previous_variant_other_image) && $previous_variant_other_image != 'null') {
                            $variant_images = str_replace("'", '"', $previous_variant_other_image);
                            $arr_old_images = json_decode($variant_images);
                            $all_images = array_merge($arr_old_images, $file_data);
                            $all_images = json_encode(array_values($all_images));
                        } else {
                            $all_images = $db->escapeString(json_encode($file_data));
                        }

                        if (empty($error)) {
                            $sql = "update `product_variant` set `images`='" . $all_images . "' where `id`=" . $vari_id;
                            $db->sql($sql);
                        }
                    }

                    $data = array(
                        'type' => $db->escapeString($fn->xss_clean($_POST['type'])),
                        'measurement' => $db->escapeString($fn->xss_clean($_POST['packate_measurement'][$i])),
                        'measurement_unit_id' => $db->escapeString($fn->xss_clean($_POST['packate_measurement_unit_id'][$i])),
                        'price' => $db->escapeString($fn->xss_clean($_POST['packate_price'][$i])),
                        'discounted_price' => $db->escapeString($fn->xss_clean($_POST['packate_discounted_price'][$i])),
                        'stock' => $stock,
                        'stock_unit_id' => $db->escapeString($fn->xss_clean($_POST['packate_stock_unit_id'][$i])),
                        'serve_for' => $serve_for,
                        'sku' => $db->escapeString($fn->xss_clean($_POST['packate_product_sku'][$i])),
                    );

                    $db->update('product_variant', $data, 'id=' . $fn->xss_clean($_POST['product_variant_id'][$i]));
                    $res = $db->getResult();
                } else if ($_POST['type'] == "loose") {
                    $stock = $db->escapeString($fn->xss_clean($_POST['loose_stock']));
                    $serve_for = ($stock == 0 || $stock <= 0) ? 'Sold Out' : $db->escapeString($fn->xss_clean($_POST['serve_for']));
                    $all_images = '';

                    /*updating variant images if any*/
                    if (isset($_FILES['loose_variant_images']) && ($_FILES['loose_variant_images']['size'][$i][0] > 0)) {
                        $vari_ids = $fn->xss_clean($_POST['product_variant_id'][$i]);
                        $file_data = array();
                        $target_path1 = 'upload/variant_images/';
                        if (!is_dir($target_path1)) {
                            mkdir($target_path1, 0777, true);
                        }

                        for ($k = 0; $k < count($_FILES["loose_variant_images"]["name"][$i]); $k++) {

                            if ($_FILES["loose_variant_images"]["error"][$i][$k] > 0) {
                                $error['loose_variant_images'] = " <span class='label label-danger'>Images not uploaded!</span>";
                            } else {
                                $result = $fn->validate_other_images($_FILES["loose_variant_images"]["tmp_name"][$i][$k], $_FILES["loose_variant_images"]["type"][$i][$k]);
                                if ($result) {
                                    $error['loose_variant_images'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                                }
                            }
                            $filename = $_FILES["loose_variant_images"]["name"][$i][$k];
                            $temp = explode('.', $filename);
                            $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                            $file_data[] = $target_path1 . '' . $filename;

                            if (!move_uploaded_file($_FILES["loose_variant_images"]["tmp_name"][$i][$k], $target_path1 . '' . $filename))
                                echo "{$_FILES['loose_variant_images']['name'][$i][$k]} not uploaded<br/>";
                        }
                        if (!empty($previous_variant_other_image)) {
                            $variant_images = str_replace("'", '"', $previous_variant_other_image);
                            $arr_old_images = json_decode($variant_images);
                            $all_images = array_merge($arr_old_images, $file_data);
                            $all_images = json_encode(array_values($all_images));
                        } else {
                            $all_images = $db->escapeString(json_encode($file_data));
                        }
                        if (empty($error)) {
                            $sql = "update `product_variant` set `images`='" . $all_images . "' where `id`=" . $vari_ids;
                            $db->sql($sql);
                        }
                    }

                    $data = array(
                        'type' => $db->escapeString($fn->xss_clean($_POST['type'])),
                        'measurement' => $db->escapeString($fn->xss_clean($_POST['loose_measurement'][$i])),
                        'measurement_unit_id' => $db->escapeString($fn->xss_clean($_POST['loose_measurement_unit_id'][$i])),
                        'price' => $db->escapeString($fn->xss_clean($_POST['loose_price'][$i])),
                        'discounted_price' => $db->escapeString($fn->xss_clean($_POST['loose_discounted_price'][$i])),
                        'stock' => $stock,
                        'stock_unit_id' => $db->escapeString($fn->xss_clean($_POST['loose_stock_unit_id'])),
                        'serve_for' => $serve_for,
                        'sku' => $db->escapeString($fn->xss_clean($_POST['loose_product_sku'][$i])),
                    );
                    $db->update('product_variant', $data, 'id=' . $fn->xss_clean($_POST['product_variant_id'][$i]));
                    $res = $db->getResult();
                }
            }

            if (
                isset($_POST['insert_packate_measurement']) && isset($_POST['insert_packate_measurement_unit_id'])
                && isset($_POST['insert_packate_price']) && isset($_POST['insert_packate_discounted_price'])
                && isset($_POST['insert_packate_stock']) && isset($_POST['insert_packate_stock_unit_id'])
            ) {
                for ($i = 0; $i < count($_POST['insert_packate_measurement']); $i++) {
                    $stock = $db->escapeString($fn->xss_clean($_POST['insert_packate_stock'][$i]));
                    $serve_for = ($stock == 0 || $stock <= 0) ? 'Sold Out' : $db->escapeString($fn->xss_clean($_POST['insert_packate_serve_for'][$i]));

                    $variant_images = '';

                    if ($_FILES["insert_packet_variant_images"]["error"][$i][0] == 0) {
                        for ($j = 0; $j < count($_FILES["insert_packet_variant_images"]["name"][$i]); $j++) {
                            if ($_FILES["insert_packet_variant_images"]["error"][$i][$j] > 0) {
                                $error['insert_packet_variant_images'] = " <span class='label label-danger'>Images not uploaded!</span>";
                            } else {
                                $result = $fn->validate_other_images($_FILES["insert_packet_variant_images"]["tmp_name"][$i][$j], $_FILES["insert_packet_variant_images"]["type"][$i][$j]);
                                if ($result) {
                                    $error['insert_packet_variant_images'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                                }
                            }
                        }
                    }

                    if ((isset($_FILES['insert_packet_variant_images'])) && (!empty($_FILES['insert_packet_variant_images']['name'][$i][0])) && ($_FILES['insert_packet_variant_images']['size'][$i][0] > 0)) {
                        $file_data = array();
                        $target_path1 = 'upload/variant_images/';
                        if (!is_dir($target_path1)) {
                            mkdir($target_path1, 0777, true);
                        }

                        for ($k = 0; $k < count($_FILES["insert_packet_variant_images"]["name"][$i]); $k++) {
                            $filename = $_FILES["insert_packet_variant_images"]["name"][$i][$k];
                            $temp = explode('.', $filename);
                            $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                            $file_data[] = $target_path1 . '' . $filename;
                            if (!move_uploaded_file($_FILES["insert_packet_variant_images"]["tmp_name"][$i][$k], $target_path1 . '' . $filename))
                                echo "{$_FILES['insert_packet_variant_images']['name'][$i][$k]} not uploaded<br/>";
                        }
                        $variant_images = json_encode($file_data);
                    }

                    $product_id = $db->escapeString($ID);
                    $type = $db->escapeString($fn->xss_clean($_POST['type']));
                    $measurement = $db->escapeString($fn->xss_clean($_POST['insert_packate_measurement'][$i]));
                    $measurement_unit_id = $db->escapeString($fn->xss_clean($_POST['insert_packate_measurement_unit_id'][$i]));
                    $price = $db->escapeString($fn->xss_clean($_POST['insert_packate_price'][$i]));
                    $discounted_price = $db->escapeString($fn->xss_clean($_POST['insert_packate_discounted_price'][$i]));
                    $stock_unit_id = $db->escapeString($fn->xss_clean($_POST['insert_packate_stock_unit_id'][$i]));
                    $product_sku = $db->escapeString($fn->xss_clean($_POST['insert_packate_product_sku'][$i]));

                    $sql = "INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,price,discounted_price,stock,stock_unit_id,serve_for,images,sku) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$price','$discounted_price','$stock','$stock_unit_id','$serve_for','$variant_images','$product_sku')";
                    $db->sql($sql);
                }
            }

            if (
                isset($_POST['insert_loose_measurement']) && isset($_POST['insert_loose_measurement_unit_id'])
                && isset($_POST['insert_loose_price']) && isset($_POST['insert_loose_discounted_price'])
            ) {
                for ($i = 0; $i < count($_POST['insert_loose_measurement_unit_id']); $i++) {
                    $file_data = '';
                    $variant_images = '';

                    if ($_FILES["insert_loose_variant_images"]["error"][$i][0] == 0) {
                        for ($j = 0; $j < count($_FILES["insert_loose_variant_images"]["name"][$i]); $j++) {
                            if ($_FILES["insert_loose_variant_images"]["error"][$i][$j] > 0) {
                                $error['insert_loose_variant_images'] = " <span class='label label-danger'>Images not uploaded!</span>";
                            } else {
                                $result = $fn->validate_other_images($_FILES["insert_loose_variant_images"]["tmp_name"][$i][$j], $_FILES["insert_loose_variant_images"]["type"][$i][$j]);
                                if ($result) {
                                    $error['insert_loose_variant_images'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                                }
                            }
                        }
                    }

                    if (isset($_FILES['insert_loose_variant_images']) && (!empty($_FILES['insert_loose_variant_images']['name'][$i][0])) && ($_FILES['insert_loose_variant_images']['size'][$i][0] > 0)) {
                        $file_data = array();
                        $target_path1 = 'upload/variant_images/';
                        if (!is_dir($target_path1)) {
                            mkdir($target_path1, 0777, true);
                        }

                        for ($k = 0; $k < count($_FILES["insert_loose_variant_images"]["name"][$i]); $k++) {
                            $filename = $_FILES["insert_loose_variant_images"]["name"][$i][$k];
                            $temp = explode('.', $filename);
                            $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                            $file_data[] = $target_path1 . '' . $filename;
                            if (!move_uploaded_file($_FILES["insert_loose_variant_images"]["tmp_name"][$i][$k], $target_path1 . '' . $filename))
                                echo "{$_FILES['insert_loose_variant_images']['name'][$i][$k]} not uploaded<br/>";
                        }
                        $variant_images = json_encode($file_data);
                    }

                    $product_id = $db->escapeString($ID);
                    $type = $db->escapeString($fn->xss_clean($_POST['type']));
                    $measurement = $db->escapeString($fn->xss_clean($_POST['insert_loose_measurement'][$i]));
                    $measurement_unit_id = $db->escapeString($fn->xss_clean($_POST['insert_loose_measurement_unit_id'][$i]));
                    $price = $db->escapeString($fn->xss_clean($_POST['insert_loose_price'][$i]));
                    $discounted_price = $db->escapeString($fn->xss_clean($_POST['insert_loose_discounted_price'][$i]));
                    $stock = $db->escapeString($fn->xss_clean($_POST['loose_stock']));
                    $stock_unit_id = $db->escapeString($fn->xss_clean($_POST['loose_stock_unit_id'][$i]));
                    $serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
                    $product_sku = $db->escapeString($fn->xss_clean($_POST['insert_loose_product_sku'][$i]));

                    $sql = "INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,price,discounted_price,stock,stock_unit_id,serve_for,images,sku) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$price','$discounted_price','$stock','$stock_unit_id','$serve_for','$variant_images','$product_sku')";
                    $db->sql($sql);
                }
            }
            $error['update_data'] = "<span class='label label-success'>Product updated Successfully</span>";
        }
    } else {
        $error['check_permission'] = " <section class='content-header'><span class='alert alert-danger'>You have no permission to update product</span></section>";
    }
}
// create array variable to store previous data
$data = array();
$sql_query = "SELECT v.*,p.*,v.id as product_variant_id,v.images AS variant_images FROM product_variant v JOIN products p ON p.id=v.product_id WHERE p.id=" . $ID;
$db->sql($sql_query);
$res = $db->getResult();

$product_status = $res[0]['status'];
foreach ($res as $row)
    $data = $row;
function isJSON($string)
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}
?>
<section class="content-header">
    <h1>Edit Product <small><a href='products.php'> <i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h1>
    <small><?= isset($error['update_data']) ? $error['update_data'] : ''; ?></small>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
    </ol>
    <br>
</section>
<section class="content">
    <!-- Main row -->
    <div class="row">
        <div class="col-md-12">
            <?php if ($permissions['products']['update'] == 0) { ?>
                <div class="alert alert-danger topmargin-sm">You have no permission to update product.</div>
            <?php } ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Product</h3>
                </div>
                <div class="box-header">
                    <?= isset($error['cancelable']) ? '<span class="label label-danger">Till status is required.</span>' : ''; ?>
                </div>
                <!-- form start -->
                <form id='edit_product_form' method="post" enctype="multipart/form-data">
                    <?php
                    $db->select('unit', '*');
                    $unit_data = $db->getResult();
                    $k = 0;
                    ?>
                    <div class="box-body">
                        <div class="form-group">
                            <div class='col-md-6'>
                                <label for="exampleInputEmail1">Product Name</label> <i class="text-danger asterik">*</i> <?= isset($error['name']) ? $error['name'] : ''; ?>
                                <input type="text" name="name" class="form-control" value="<?= $data['name']; ?>" />
                            </div>
                            <?php $db->sql("SET NAMES 'utf8'");
                            $sql = "SELECT * FROM `taxes` WHERE status = 1 ORDER BY id DESC";
                            $db->sql($sql);
                            $taxes = $db->getResult();
                            ?>
                            <div class='col-md-6'>
                                <label class="control-label " for="taxes">Tax</label>
                                <select id='tax_id' name="tax_id" class='form-control'>
                                    <option value="">Select Tax</option>
                                    <?php foreach ($taxes as $tax) { ?>
                                        <option value='<?= $tax['id'] ?>' <?= ($data['tax_id'] == $tax['id']) ? 'selected' : ''; ?>><?= $tax['title'] . " - " . $tax['percentage'] . " %" ?></option>
                                    <?php } ?>
                                </select><br>
                            </div>
                        </div>
                        <label for="type">Type</label><?= isset($error['type']) ? $error['type'] : ''; ?>
                        <div class="form-group">
                            <label class="radio-inline"><input type="radio" name="type" id="packate" value="packet" <?= ($res[0]['type'] == "packet") ? "checked" : ""; ?>>Packet</label>
                            <label class="radio-inline"><input type="radio" name="type" id="loose" value="loose" <?= ($res[0]['type'] == "loose") ? "checked" : ""; ?>>Loose</label>
                        </div>
                        <hr>
                        <div id="variations">
                            <h5>Product Variations</h5>
                            <hr>
                            <?php
                            if (isJSON($data['price'])) {
                                $price = json_decode($data['price'], 1);
                                $measurement = json_decode($data['measurement'], 1);
                                $discounted_price = json_decode($data['discounted_price'], 1);
                            } else {
                                $price = array('0' => $data['price']);
                                $measurement = array('0' => $data['measurement']);
                                $discounted_price = array('0' => $data['discounted_price']);
                            }
                            $i = 0;
                            if ($res[0]['type'] == "packet") {
                                foreach ($res as $row) {
                            ?>
                                    <div class="row packate_div">
                                        <input type="hidden" class="form-control" name="product_variant_id[]" id="product_variant_id" value='<?= $row['product_variant_id']; ?>' />
                                        <div class="col-md-2">
                                            <div class="form-group packate_div">
                                                <label for="exampleInputEmail1">Measurement</label> <i class="text-danger asterik">*</i> <input type="number" step="any" min="0" class="form-control measurement" name="packate_measurement[]" value='<?= $row['measurement']; ?>' required />
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group packate_div">
                                                <label for="unit">Unit:</label>
                                                <select class="form-control units" name="packate_measurement_unit_id[]">
                                                    <?php
                                                    foreach ($unit_data as  $unit) {
                                                        echo "<option";
                                                        if ($unit['id'] == $row['measurement_unit_id']) {
                                                            echo " selected ";
                                                        }
                                                        echo " value='" . $unit['id'] . "'>" . $unit['short_code'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group packate_div">
                                                <label for="price">Price (<?= $settings['currency'] ?>):</label> <i class="text-danger asterik">*</i> <input type="number" step="any" min="0" class="form-control" name="packate_price[]" id="packate_price" value='<?= $row['price']; ?>' required />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group packate_div">
                                                <label for="discounted_price">Discounted Price(<?= $settings['currency'] ?>):</label>
                                                <input type="number" step="any" min="0" class="form-control" name="packate_discounted_price[]" id="discounted_price" value='<?= $row['discounted_price']; ?>' />
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group packate_div">
                                                <label for="qty">Stock:</label> <i class="text-danger asterik">*</i>
                                                <input type="number" step="any" min="0" class="form-control" name="packate_stock[]" required value='<?= $row['stock']; ?>' />
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group packate_div">
                                                <label for="unit">Unit:</label>
                                                <select class="form-control" name="packate_stock_unit_id[]">
                                                    <?php
                                                    foreach ($unit_data as  $unit) {
                                                        echo "<option";
                                                        if ($unit['id'] == $row['stock_unit_id']) {
                                                            echo " selected ";
                                                        }
                                                        echo " value='" . $unit['id'] . "'>" . $unit['short_code'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group packate_div">
                                                <label for="qty">Status:</label>
                                                <select name="packate_serve_for[]" class="form-control">
                                                    <option value="Available" <?php (strtolower($row['serve_for']) == "availabel") ? "selected" : ''; ?>>Available</option>
                                                    <option value="Sold Out" <?php (strtolower($row['serve_for']) == "sold out")  ? "selected" : ''; ?>>Sold Out</option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php if ($i == 0) { ?>
                                            <div class='col-md-1'>
                                                <label>Variation</label>
                                                <a id='add_packate_variation' title='Add variation of product' style='cursor: pointer;'><i class="fa fa-plus-square-o fa-2x"></i></a>
                                            </div>
                                        <?php } else { ?>
                                            <div class="col-md-1" style="display: grid;">
                                                <label>Remove</label>
                                                <a class="remove_variation text-danger" data-id="data_delete" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a>
                                            </div>
                                        <?php } ?>


                                        <div class="col-md-4">
                                            <div class="form-group packate_div">
                                                <label for="packate_product_sku">SKU:</label>
                                                <input type="text" class="form-control" name="packate_product_sku[]" value='<?= $row['sku']; ?>' />
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group packate_div">
                                                <label for="exampleInputFile">Variant Images &nbsp;&nbsp;&nbsp;(Please choose square image of larger than 350px*350px & smaller than 550px*550px.)</label><?= isset($error['variant_images']) ? $error['variant_images'] : ''; ?>
                                                <input type="file" name="packet_variant_images[<?= $k++; ?>][]" id="packet_variant_images" multiple title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." /><br />
                                                <?php
                                                if (!empty($row['variant_images']) && $row['variant_images'] != 'NULL' && $row['variant_images'] != 'null') {
                                                    $variant_images = str_replace("'", '"', $row['variant_images']);
                                                    $variant_images = json_decode($variant_images);
                                                    for ($i = 0; $i < count($variant_images); $i++) { ?>
                                                        <img src="<?= $variant_images[$i]; ?>" height="100" />
                                                        <a class='btn btn-xs btn-danger delete-variant-image' data-i='<?= $i; ?>'>Delete</a>
                                                <?php }
                                                } ?>
                                            </div>
                                        </div>

                                    </div>
                                <?php $i++;
                                }
                            } else {
                                $db->select('unit', '*');
                                $resedit = $db->getResult();
                                $j = 0;
                                ?>
                                <div id="packate_div" style="display:none">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group packate_div">
                                                <label for="exampleInputEmail1">Measurement</label> <i class="text-danger asterik">*</i> <input type="number" step="any" min="0" class="form-control measurement" name="packate_measurement[]" required />
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group packate_div">
                                                <label for="unit">Unit:</label>
                                                <select class="form-control units" name="packate_measurement_unit_id[]">
                                                    <?php
                                                    foreach ($resedit as  $row) {
                                                        echo "<option value='" . $row['id'] . "'>" . $row['short_code'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group packate_div">
                                                <label for="price">Price (<?= $settings['currency'] ?>):</label> <i class="text-danger asterik">*</i> <input type="number" step="any" min="0" class="form-control" name="packate_price[]" id="packate_price" required />
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group packate_div">
                                                <label for="discounted_price">Discount Price (<?= $settings['currency'] ?>):</label>
                                                <input type="number" step="any" min="0" class="form-control" name="packate_discounted_price[]" id="discounted_price" />
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group packate_div">
                                                <label for="qty">Stock:</label> <i class="text-danger asterik">*</i>
                                                <input type="number" step="any" min="0" class="form-control" name="packate_stock[]" />
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group packate_div">
                                                <label for="unit">Unit:</label>
                                                <select class="form-control" name="packate_stock_unit_id[]">
                                                    <?php
                                                    foreach ($resedit as  $row) {
                                                        echo "<option value='" . $row['id'] . "'>" . $row['short_code'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group packate_div">
                                                <label for="qty">Status:</label>
                                                <select name="packate_serve_for[]" class="form-control" required>
                                                    <option value="Available">Available</option>
                                                    <option value="Sold Out">Sold Out</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <label>Variation</label>
                                            <a id="add_packate_variation" title="Add variation of product" style="cursor: pointer;"><i class="fa fa-plus-square-o fa-2x"></i></a>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group packate_div">
                                                <label for="packate_product_sku">SKU:</label>
                                                <input type="text" class="form-control" name="packate_product_sku[]" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group packate_div">
                                                <label for="exampleInputFile">Variant Other Images <i class="text-danger asterik">*</i> &nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?= isset($error['variant_images']) ? $error['variant_images'] : ''; ?>
                                                <input type="file" name="packet_variant_images[<?= $k++; ?>][]" id="packet_variant_images" multiple /><br />
                                                <?php if (!empty($row['variant_images'])) {
                                                    $variant_images = str_replace("'", '"', $row['variant_images']);
                                                    $variant_images = json_decode($variant_images);
                                                    for ($i = 0; $i < count($variant_images); $i++) { ?>
                                                        <img src="<?= $variant_images[$i]; ?>" height="160" />
                                                        <a class='btn btn-xs btn-danger delete-variant-image' data-i='<?= $i; ?>' data-pid='<?= $_GET['id']; ?>'>Delete</a>
                                                <?php }
                                                } ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            <?php } ?>
                            <div id="packate_variations"></div>
                            <?php
                            $i = 0;
                            $j = 0;
                            if ($res[0]['type'] == "loose") {
                                foreach ($res as $row) { ?>
                                    <div class="row loose_div">
                                        <input type="hidden" class="form-control" name="product_variant_id[]" id="product_variant_id" value='<?= $row['product_variant_id']; ?>' />
                                        <div class="col-md-4">
                                            <div class="form-group loose_div">
                                                <label for="exampleInputEmail1">Measurement</label> <i class="text-danger asterik">*</i>
                                                <input type="number" step="any" min="0" class="form-control measurement" name="loose_measurement[]" required="" value='<?= $row['measurement']; ?>'>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group loose_div">
                                                <label for="unit">Unit:</label>
                                                <select class="form-control loose_units" name="loose_measurement_unit_id[]">
                                                    <?php
                                                    foreach ($unit_data as  $unit) {
                                                        echo "<option";
                                                        if ($unit['id'] == $row['measurement_unit_id']) {
                                                            echo " selected ";
                                                        }
                                                        echo " value='" . $unit['id'] . "'>" . $unit['short_code'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group loose_div">
                                                <label for="price">Price (<?= $settings['currency'] ?>):</label> <i class="text-danger asterik">*</i>
                                                <input type="number" step="any" min="0" class="form-control" name="loose_price[]" id="loose_price" required="" value='<?= $row['price']; ?>'>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group loose_div">
                                                <label for="discounted_price">Discounted Price(<?= $settings['currency'] ?>):</label>
                                                <input type="number" step="any" min="0" class="form-control" name="loose_discounted_price[]" id="discounted_price" value='<?= $row['discounted_price']; ?>' />
                                            </div>
                                        </div>
                                        <?php if ($i == 0) { ?>
                                            <div class='col-md-1'>
                                                <label>Variation</label>
                                                <a id='add_loose_variation' title='Add variation of product' style='cursor: pointer;'><i class="fa fa-plus-square-o fa-2x"></i></a>
                                            </div>
                                        <?php } else { ?>
                                            <div class="col-md-1" style="display: grid;">
                                                <label>Remove</label>
                                                <a class="remove_variation text-danger" data-id="data_delete" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a>
                                            </div>
                                        <?php }
                                        $i++; ?>

                                        <div class="col-md-4">
                                            <div class="form-group loose_div">
                                                <label for="loose_product_sku">SKU:</label>
                                                <input type="text" class="form-control" name="loose_product_sku[]" value='<?= $row['sku']; ?>' />
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group loose_div">
                                                <label for="exampleInputFile">Variant Other Images <i class="text-danger asterik">*</i> &nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?= isset($error['variant_images']) ? $error['variant_images'] : ''; ?>
                                                <input type="file" name="loose_variant_images[<?= $j++; ?>][]" id="loose_variant_images" multiple title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." /><br />
                                                <?php if (!empty($row['variant_images'])) {
                                                    $variant_images = str_replace("'", '"', $row['variant_images']);
                                                    $variant_images = json_decode($variant_images);
                                                    for ($i = 0; $i < count($variant_images); $i++) { ?>
                                                        <img src="<?= $variant_images[$i]; ?>" height="160" />
                                                        <a class='btn btn-xs btn-danger delete-variant-image' data-i='<?= $i; ?>' data-pid='<?= $_GET['id']; ?>'>Delete</a>
                                                <?php }
                                                } ?>
                                            </div>
                                        </div>

                                    </div>
                                <?php } ?>
                                <div id="loose_variations"></div>

                                <hr>
                                <div class="form-group" id="loose_stock_div" style="display:block;">
                                    <label for="quantity">Stock :</label> <i class="text-danger asterik">*</i> <?= isset($error['quantity']) ? $error['quantity'] : ''; ?>
                                    <input type="number" step="any" min="0" class="form-control" name="loose_stock" required value='<?= $row['stock']; ?>'>
                                </div>
                                <div class="form-group">
                                    <label for="stock_unit">Unit :</label><?= isset($error['stock_unit']) ? $error['stock_unit'] : ''; ?>
                                    <select class="form-control" name="loose_stock_unit_id" id="loose_stock_unit_id">
                                        <?php
                                        foreach ($unit_data as  $unit) {
                                            echo "<option";
                                            if ($unit['id'] == $row['stock_unit_id']) {
                                                echo " selected ";
                                            }
                                            echo " value='" . $unit['id'] . "'>" . $unit['short_code'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php } else {
                                $db->select('unit', '*');
                                $resedit = $db->getResult();
                            ?>
                                <div id="loose_div" style="display:none;">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group loose_div">
                                                <label for="exampleInputEmail1">Measurement</label> <i class="text-danger asterik">*</i>
                                                <input type="number" step="any" min="0" class="form-control measurement" name="loose_measurement[]" required="">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group loose_div">
                                                <label for="unit">Unit:</label>
                                                <select class="form-control loose_units" name="loose_measurement_unit_id[]">
                                                    <?php
                                                    foreach ($resedit as  $row) {
                                                        echo "<option value='" . $row['id'] . "'>" . $row['short_code'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group loose_div">
                                                <label for="price">Price (<?= $settings['currency'] ?>):</label> <i class="text-danger asterik">*</i>
                                                <input type="number" step="any" min="0" class="form-control" name="loose_price[]" id="loose_price" required="">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group loose_div">
                                                <label for="discounted_price">Discounted Price:</label>
                                                <input type="number" step="any" min="0" class="form-control" name="loose_discounted_price[]" id="discounted_price" />
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <label>Variation</label>
                                            <a id="add_loose_variation" title="Add variation of product" style="cursor: pointer;"><i class="fa fa-plus-square-o fa-2x"></i></a>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group loose_div">
                                                <label for="loose_product_sku">SKU:</label>
                                                <input type="text" class="form-control" name="loose_product_sku[]" />
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group loose_div">
                                                <label for="exampleInputFile">Variant Other Images <i class="text-danger asterik">*</i> &nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?= isset($error['variant_images']) ? $error['variant_images'] : ''; ?>
                                                <input type="file" name="loose_variant_images[<?= $j++; ?>][]" id="loose_variant_images" multiple title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." /><br />
                                                <?php if (!empty($row['variant_images'])) {
                                                    $variant_images = str_replace("'", '"', $row['variant_images']);
                                                    $variant_images = json_decode($variant_images);
                                                    for ($i = 0; $i < count($variant_images); $i++) { ?>
                                                        <img src="<?= $variant_images[$i]; ?>" height="160" />
                                                        <a class='btn btn-xs btn-danger delete-variant-image' data-i='<?= $i; ?>' data-pid='<?= $_GET['id']; ?>'>Delete</a>
                                                <?php }
                                                } ?>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                        </div>
                        <div id="variations">
                        </div>
                        <hr>
                        <div class="form-group" id="loose_stock_div" style="display:none;">
                            <label for="quantity">Stock :</label> <i class="text-danger asterik">*</i> <?= isset($error['quantity']) ? $error['quantity'] : ''; ?>
                            <input type="number" step="any" min="0" class="form-control" name="loose_stock" required>

                            <label for="stock_unit">Unit :</label><?= isset($error['stock_unit']) ? $error['stock_unit'] : ''; ?>
                            <select class="form-control" name="loose_stock_unit_id" id="loose_stock_unit_id">
                                <?php
                                foreach ($resedit as $row) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['short_code'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    <?php } ?>
                    <hr>

                    <div class="form-group">
                        <div class="form-group" id="status_div" <?php ($res[0]['type'] == "packet") ? "style='display:none'" : ''; ?>>
                            <label for="exampleInputEmail1">Status :</label><?= isset($error['serve_for']) ? $error['serve_for'] : ''; ?>
                            <select name="serve_for" class="form-control">
                                <option value="Available" <?php (strtolower($res[0]['serve_for']) == "available") ? "selected" : ''; ?>>Available</option>
                                <option value="Sold Out" <?php (strtolower($res[0]['serve_for']) == "sold out") ? "selected" : ''; ?>>Sold Out</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Category :</label> <i class="text-danger asterik">*</i> <?= isset($error['category_id']) ? $error['category_id'] : ''; ?>
                                <select name="category_id" id="category_id" class="form-control">
                                    <?php
                                    if ($permissions['categories']['read'] == 1) {
                                        foreach ($category_data as $row) { ?>
                                            <option value="<?= $row['id']; ?>" <?= ($row['id'] == $data['category_id']) ? "selected" : ""; ?>><?= $row['name']; ?></option>
                                        <?php }
                                    } else { ?>
                                        <option value="">---Select Category---</option>
                                        <?php } ?>?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="exampleInputEmail1">Sub Category :</label>
                                <select name="subcategory_id" id="subcategory_id" class="form-control">

                                    <?php
                                    if ($permissions['subcategories']['read'] == 1) { ?>
                                        <option value="">---Select Subcategory---</option>
                                        <?php foreach ($subcategory as $subcategories) { ?>

                                            <option value="<?= $subcategories['id']; ?>" <?= $res[0]['subcategory_id'] == $subcategories['id'] ? 'selected' : '' ?>><?= $subcategories['name']; ?></option>
                                        <?php }
                                    } else { ?>
                                        <option value="">---Select Subcategory---</option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Product Type :</label>
                                <select name="indicator" id="indicator" class="form-control">
                                    <option value="">--Select Type--</option>
                                    <option value="1" <?= ($res[0]['indicator'] == 1) ? 'selected' : ''; ?>>Veg</option>
                                    <option value="2" <?= ($res[0]['indicator'] == 2) ? 'selected' : ''; ?>>Non Veg</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Manufacturer :</label>
                                <input type="text" name="manufacturer" value="<?= $res[0]['manufacturer'] ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="">Made In :</label>
                                <input type="text" name="made_in" value="<?= $res[0]['made_in'] ?>" class="form-control">
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Is Returnable? :</label><br>
                                        <input type="checkbox" id="return_status_button" class="js-switch" <?= $res[0]['return_status'] == 1 ? 'checked' : '' ?>>
                                        <input type="hidden" id="return_status" name="return_status" value="<?= $res[0]['return_status'] == 1 ? 1 : 0 ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Is cancel-able? :</label><br>
                                        <input type="checkbox" id="cancelable_button" class="js-switch" <?= $res[0]['cancelable_status'] == 1 ? 'checked' : '' ?>>
                                        <input type="hidden" id="cancelable_status" name="cancelable_status" value="<?= $res[0]['cancelable_status'] == 1 ? 1 : 0 ?>">
                                    </div>
                                </div>
                                <?php
                                $style = $res[0]['cancelable_status'] == 1 ? "" : "display:none;";
                                ?>
                                <div class="col-md-3" id="till-status" style="<?= $style; ?>">
                                    <div class="form-group">
                                        <label for="">Till which status? :</label> <i class="text-danger asterik">*</i> <?= isset($error['cancelable']) ? $error['cancelable'] : ''; ?><br>
                                        <select id="till_status" name="till_status" class="form-control">
                                            <option value="">Select</option>
                                            <option value="received" <?= $res[0]['till_status'] == 'received' ? 'selected' : '' ?>>Received</option>
                                            <option value="processed" <?= $res[0]['till_status'] == 'processed' ? 'selected' : '' ?>>Processed</option>
                                            <option value="shipped" <?= $res[0]['till_status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="">Is Cod Allowed? :</label><br>
                                        <input type="checkbox" id="cod_allowed_button" class="js-switch" <?= $res[0]['is_cod_allowed'] == 1 ? 'checked' : '' ?>>
                                        <input type="hidden" id="cod_allowed_status" name="cod_allowed_status" value="<?= $res[0]['is_cod_allowed'] == 1 ? 1 : 0 ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="">Total allowed quantity : <small>[Keep blank if no such limit]</small></label>
                                        <input type="number" min="1" class="form-control" name="total_allowed_quantity" value="<?= isset($res[0]['total_allowed_quantity']) && !empty($res[0]['total_allowed_quantity']) ? $res[0]['total_allowed_quantity'] : '' ?>" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="exampleInputFile">Image <i class="text-danger asterik">*</i> &nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?= isset($error['image']) ? $error['image'] : ''; ?>
                                <input type="file" name="image" id="image" title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." /><br />
                                <img src="<?= $data['image']; ?>" width="210" height="160" />
                            </div>
                            <div class="form-group">
                                <label for="exampleInputFile">Other Images *Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?= isset($error['other_images']) ? $error['other_images'] : ''; ?>
                                <input type="file" name="other_images[]" id="other_images" multiple title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." /><br />
                                <?php
                                if (!empty($data['other_images'])) {
                                    $other_images = json_decode($data['other_images']);
                                    for ($i = 0; $i < count($other_images); $i++) { ?>
                                        <img src="<?= $other_images[$i]; ?>" height="160" />
                                        <a class='btn btn-xs btn-danger delete-image' data-i='<?= $i; ?>' data-pid='<?= $_GET['id']; ?>'>Delete</a>
                                <?php }
                                } ?>
                            </div>

                            <div class="form-group">
                                <label for="size_chart">Size Chart : &nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?= isset($error['size_chart']) ? $error['size_chart'] : ''; ?>
                                <input type="file" name="size_chart" id="size_chart" title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." /><br />
                                <?php if (!empty($data['size_chart'])) { ?>
                                    <img src="<?= $data['size_chart']; ?>" width="210" height="160" />
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Description :</label> <i class="text-danger asterik">*</i><i class="address_note"></i> <?= isset($error['description']) ? $error['description'] : ''; ?>
                                <textarea name="description" id="description" class="form-control addr_editor" rows="16"><?= $data['description']; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">shipping_delivery : </label><i class="address_note"></i><?= isset($error['shipping_delivery']) ? $error['shipping_delivery'] : ''; ?>
                                <textarea name="shipping_delivery" id="shipping_delivery" class="form-control addr_editor"><?= $data['shipping_delivery']; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label class="control-label ">Status :</label>
                                <div id="product_status" class="btn-group">
                                    <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                        <input type="radio" name="pr_status" value="0"> Deactive
                                    </label>
                                    <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                        <input type="radio" name="pr_status" value="1"> Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <input type="submit" class="btn-primary btn" value="Update" name="btnEdit" />
                    </div>
                </form>
            </div><!-- /.box -->
        </div>
    </div>
</section>
<div class="separator"> </div>
<script type="text/javascript">
    $(document).ready(function() {
        ltr = '<svg width="20" height="20"><path d="M11 5h7a1 1 0 010 2h-1v11a1 1 0 01-2 0V7h-2v11a1 1 0 01-2 0v-6c-.5 0-1 0-1.4-.3A3.4 3.4 0 017.8 10a3.3 3.3 0 010-2.8 3.4 3.4 0 011.8-1.8L11 5zM4.4 16.2L6.2 15l-1.8-1.2a1 1 0 011.2-1.6l3 2a1 1 0 010 1.6l-3 2a1 1 0 11-1.2-1.6z" fill-rule="evenodd"></path></svg>';
        rtl = '<svg width="20" height="20"><path d="M8 5h8v2h-2v12h-2V7h-2v12H8v-7c-.5 0-1 0-1.4-.3A3.4 3.4 0 014.8 10a3.3 3.3 0 010-2.8 3.4 3.4 0 011.8-1.8L8 5zm12 11.2a1 1 0 11-1 1.6l-3-2a1 1 0 010-1.6l3-2a1 1 0 111 1.6L18.4 15l1.8 1.2z" fill-rule="evenodd"></path></svg>';
        html = '( Use ' + ltr + ' for LTR and use ' + rtl + ' for RTL )';
        $('.address_note').append(html);
    });
</script>
<script>
    $(document).on('click', '.delete-image', function() {
        var pid = $(this).data('pid');
        var i = $(this).data('i');
        if (confirm('Are you sure want to delete the image?')) {
            $.ajax({
                type: 'POST',
                url: 'public/delete-other-images.php',
                data: 'i=' + i + '&pid=' + pid,
                success: function(result) {
                    if (result == '1') {
                        alert('Image deleted successfully');
                        location.reload();
                    } else
                        alert('Image could not be deleted!');

                }
            });
        }
    });

    $(document).on('click', '.delete-variant-image', function() {
        var pid = $(this).data('pid');
        var i = $(this).data('i');
        var vid = $(this).closest('div.row').find("input[id='product_variant_id']").val();
        if (confirm('Are you sure want to delete the image?')) {
            $.ajax({
                type: 'POST',
                url: 'public/db-operation.php',
                data: 'i=' + i + '&vid=' + vid + '&delete_variant_images=1',
                success: function(result) {
                    if (result == 1) {
                        alert('Image deleted successfully');
                        location.reload();
                    } else {
                        alert('Image could not be deleted!');
                        location.reload();
                    }
                }
            });
        }
    });
</script>
<script src="dist/js/jquery.validate.min.js"></script>
<script>
    var changeCheckbox = document.querySelector('#cod_allowed_button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#cod_allowed_status').val(1);
        } else {
            $('#cod_allowed_status').val(0);
        }
    };
</script>
<script>
    var changeCheckbox = document.querySelector('#return_status_button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#return_status').val(1);
        } else {
            $('#return_status').val(0);
        }
    };
</script>
<script>
    var changeCheckbox = document.querySelector('#cancelable_button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
            $('#cancelable_status').val(1);
            $('#till-status').show();

        } else {
            $('#cancelable_status').val(0);
            $('#till-status').hide();
            $('#till_status').val('');
        }
    };
</script>
<script>
    $.validator.addMethod('lessThanEqual', function(value, element, param) {
        return this.optional(element) || parseInt(value) < parseInt($(param).val());
    }, "Discounted Price should be lesser than Price");

    $('#edit_product_form').validate({
        rules: {
            name: "required",
            measurement: "required",
            price: "required",
            quantity: "required",
            discounted_price: "required",
            stock: "required",
            discounted_price: {
                lessThanEqual: "#price"
            },
            description: {
                required: function(textarea) {
                    CKEDITOR.instances[textarea.id].updateElement();
                    var editorcontent = textarea.value.replace(/<[^>]*>/gi, '');
                    return editorcontent.length === 0;
                }
            }
        }
    });
</script>
<script>

</script>
<script>
    var x = 0;
    $('#add_loose_variation').on('click', function() {
        html = '<div class="row"><hr><div class="col-md-4"><div class="form-group loose_div">' +
            '<label for="exampleInputEmail1">Measurement</label> <i class="text-danger asterik">*</i> <input type="number" step="any" min="0" class="form-control measurement" name="insert_loose_measurement[]" required="">' +
            '</div></div>' +
            '<div class="col-md-2"><div class="form-group loose_div">' +
            '<label for="unit">Unit:</label>' +
            '<select class="form-control loose_units" name="insert_loose_measurement_unit_id[]">' +
            '<?php foreach ($unit_data as  $unit) {
                    echo "<option value=" . $unit['id'] . ">" . $unit['short_code'] . "</option>";
                } ?>' +
            '</select></div></div>' +
            '<div class="col-md-3"><div class="form-group loose_div">' +
            '<label for="price">Price  (<?= $settings['currency'] ?>):</label> <i class="text-danger asterik">*</i> ' +
            '<input type="number" step="any" min="0" class="form-control" name="insert_loose_price[]" id="loose_price" required="">' +
            '</div></div>' +

            '<div class="col-md-2"><div class="form-group loose_div">' +
            '<label for="discounted_price">Discounted Price(<?= $settings['currency'] ?>):</label>' +
            '<input type="number" step="any" min="0" class="form-control" name="insert_loose_discounted_price[]" id="discounted_price"/>' +
            '</div></div>' +

            '<div class="col-md-1" style="display: grid;">' +
            '<label>Remove</label><a class="remove_variation text-danger" data-id="remove" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a>' +
            '</div>' +

            '<div class="col-md-4"><div class="form-group loose_div">' +
            '<label for="insert_loose_product_sku">SKU:</label>' +
            '<input type="text" class="form-control" name="insert_loose_product_sku[]" />' +
            '</div></div>' +

            '<div class="col-md-6"><div class="form-group loose_div">' +
            '<label for="exampleInputFile">Variant Other Images <i class="text-danger asterik">*</i> &nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?= isset($error['variant_images']) ? $error['variant_images'] : ''; ?>' +
            '<input type="file" name="insert_loose_variant_images[' + x++ + '][]" id="insert_loose_variant_images" multiple title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." /><br />' +
            '</div></div>' +

            '</div>';

        $('#loose_variations').append(html);
    });

    $('#add_packate_variation').on('click', function() {
        html = '<div class="row"><hr><div class="col-md-2"><div class="form-group packate_div">' +
            '<label for="exampleInputEmail1">Measurement</label> <i class="text-danger asterik">*</i> <input type="number" step="any" min="0" class="form-control measurement" name="insert_packate_measurement[]" required />' +
            '</div></div>' +
            '<div class="col-md-1"><div class="form-group packate_div">' +
            '<label for="unit">Unit:</label>' +
            '<select class="form-control units" name="insert_packate_measurement_unit_id[]">' +
            '<?php foreach ($unit_data as  $unit) {
                    echo "<option value=" . $unit['id'] . ">" . $unit['short_code'] . "</option>";
                } ?>' +
            '</select></div></div>' +
            '<div class="col-md-2"><div class="form-group packate_div">' +
            '<label for="price">Price  (<?= $settings['currency'] ?>):</label> <i class="text-danger asterik">*</i> <input type="number" step="any" min="0" class="form-control" name="insert_packate_price[]" id="packate_price" required />' +
            '</div></div>' +
            '<div class="col-md-2"><div class="form-group packate_div">' +
            '<label for="discounted_price">Discounted Price(<?= $settings['currency'] ?>):</label>' +
            '<input type="number" step="any" min="0" class="form-control" name="insert_packate_discounted_price[]" id="discounted_price"/>' +
            '</div></div>' +
            '<div class="col-md-1"><div class="form-group packate_div">' +
            '<label for="qty">Stock:</label> <i class="text-danger asterik">*</i> ' +
            '<input type="number" step="any" min="0" class="form-control" name="insert_packate_stock[]"/>' +
            '</div></div>' +
            '<div class="col-md-1"><div class="form-group packate_div">' +
            '<label for="unit">Unit:</label><select class="form-control" name="insert_packate_stock_unit_id[]">' +
            '<?php foreach ($unit_data as  $unit) {
                    echo "<option value=" . $unit['id'] . ">" . $unit['short_code'] . "</option>";
                } ?>' +
            '</select></div></div>' +
            '<div class="col-md-2"><div class="form-group packate_div"><label for="insert_packate_serve_for">Status:</label>' +
            '<select name="insert_packate_serve_for[]" class="form-control valid" required="" aria-invalid="false"><option value="Available">Available</option><option value="Sold Out">Sold Out</option></select></div></div>' +
            '<div class="col-md-1" style="display: grid;">' +
            '<label>Remove</label><a class="remove_variation text-danger" data-id="remove" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a>' +
            '</div>' +

            '<div class="col-md-4"><div class="form-group packate_div">' +
            '<label for="insert_packate_product_sku">SKU:</label>' +
            '<input type="text" class="form-control" name="insert_packate_product_sku[]" />' +
            '</div></div>' +

            '<div class="col-md-6"><div class="form-group packate_div">' +
            '<label for="exampleInputFile">Variant Other Images <i class="text-danger asterik">*</i> &nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?= isset($error['variant_images']) ? $error['variant_images'] : ''; ?>' +
            '<input type="file" name="insert_packet_variant_images[' + x++ + '][]" id="insert_packet_variant_images" multiple title="Please choose square image of larger than 350px*350px & smaller than 550px*550px." /><br />' +
            '</div></div>' +

            '</div>';

        $('#packate_variations').append(html);
    });
</script>
<script>
    $(document).on('click', '.remove_variation', function() {
        if ($(this).data('id') == 'data_delete') {
            if (confirm('Are you sure? Want to delete this row')) {
                var id = $(this).closest('div.row').find("input[id='product_variant_id']").val();
                $.ajax({
                    url: 'public/db-operation.php',
                    type: "post",
                    data: 'id=' + id + '&delete_variant=1',
                    success: function(result) {
                        location.reload();
                    }
                });
            }
        } else {
            $(this).closest('.row').remove();
        }
    });

    $(document).on('change', '#category_id', function() {
        $.ajax({
            url: 'public/db-operation.php',
            method: 'POST',
            data: 'category_id=' + $('#category_id').val() + '&find_subcategory=1',
            success: function(data) {
                $('#subcategory_id').html("<option value=''>---Select Subcategory---</option>" + data);
            }
        });
    });
    $(document).on('change', '#packate', function() {
        $('#packate_div').show();
        $('.packate_div').show();
        $('#loose_div').hide();
        $('.loose_div').hide();
        $('#status_div').hide();
        $('#loose_stock_div').hide();
    });
    $(document).on('change', '#loose', function() {
        $('#packate_div').hide();
        $('.packate_div').hide();
        $('#loose_div').show();
        $('.loose_div').show();
        $('#status_div').show();
        $('#loose_stock_div').show();
    });
    $(document).ready(function() {
        var product_status = '<?= $product_status ?>';
        $("input[name=pr_status][value=1]").prop('checked', true);
        if (product_status == 0)
            $("input[name=pr_status][value=0]").prop('checked', true);
    });
</script>