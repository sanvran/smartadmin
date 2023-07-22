<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');

include_once('../../includes/crud.php');
include_once('../../includes/custom-functions.php');
include_once('verify-token.php');
include_once('../../includes/functions.php');
require_once('../../includes/firebase.php');
require_once('../../includes/push.php');
include_once('../../includes/variables.php');
include_once('../../delivery-boy/api/send-email.php');

$function = new functions;
$fn = new custom_functions();
$db = new Database();
$db->connect();

$config = $fn->get_configurations();
$time_slot_config = $fn->time_slot_config();
$time_zone = $fn->set_timezone($config);

$low_stock_limit = $config['low-stock-limit'];
if (!$time_zone) {
    $response['error'] = true;
    $response['message'] = "Time Zone is not set.";
    print_r(json_encode($response));
    return false;
    exit();
}

/* 
-------------------------------------------
APIs for Admin App
-------------------------------------------
1. add_category
2. update_category
3. delete_category
4. get_categories
5. add_subcategory
6. update_subcategory
7. delete_subcategory
8. get_subcategories
9. add_delivery_boy
10.update_delivery_boy
11.delete_delivery_boy
12.get_delivery_boys
13.add_products
14.update_products
15.delete_products
16.get_products
17.send_notification
18.delete_notification
19.get_notification
20.get_orders
21. get_customers
22. get_financial_statistics
23. login
24. update_admin_fcm_id
25. get_privacy_and_terms
26. update_order_status
27. update_bank_transfer
28. get_permissions
29. update_order_item_status
30. delivery_boy_fund_transfers
31. delivery_boy_transfer_fund
32. get_all_data
33. delete_other_images
34. delete_variant
35. get_units
36. get_taxes
37. upload_bank_transfers_attachment
38. delete_bank_transfers_attachment
39. get_order_invoice
40. delete_variant_images

-------------------------------------------
-------------------------------------------
*/

if (!verify_token()) {
    return false;
}

$cancel_order_from = "";
if (!isset($_POST['accesskey'])  || trim($_POST['accesskey']) != $access_key) {
    $response['error'] = true;
    $response['message'] = "No Accsess key found!";
    print_r(json_encode($response));
    return false;
    exit();
}

/*
1.add_category
    accesskey:90336
    add_category:1
    category_name:Beverages
    category_subtitle:Cold Drinks, Soft Drinks, Sodas
    image:FILE
*/

if (isset($_POST['add_category']) && !empty($_POST['add_category']) && $_POST['add_category'] == 1) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['category_name']) || empty($_POST['category_subtitle'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }
    $category_name = $db->escapeString($fn->xss_clean_array($_POST['category_name']));
    $category_subtitle = $db->escapeString($fn->xss_clean_array($_POST['category_subtitle']));

    $target_path = '../../upload/images/';
    $target_path1 = '../../upload/web-category-image/';
    if (!empty($category_name) && !empty($category_subtitle)) {
        if (isset($_FILES['upload_image'])) {
            if (!is_dir($target_path)) {
                mkdir($target_path, 0777, true);
            }
            $extension = pathinfo($_FILES["upload_image"]["name"])['extension'];

            $result = $fn->validate_image($_FILES["upload_image"]);
            if ($result) {
                $response['error'] = true;
                $response['message'] = "image type must jpg, jpeg, gif, or png!";
                print_r(json_encode($response));
                return false;
            }

            $string = '0123456789';
            $file = preg_replace("/\s+/", "_", $_FILES['upload_image']['name']);
            $menu_image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

            $upload = move_uploaded_file($_FILES['upload_image']['tmp_name'], '../../upload/images/' . $menu_image);
        }
        if (isset($_FILES['web_image'])) {
            if (!is_dir($target_path1)) {
                mkdir($target_path1, 0777, true);
            }
            $extension1 = pathinfo($_FILES["web_image"]["name"])['extension'];

            $result1 = $fn->validate_image($_FILES["web_image"]);
            if ($result1) {
                $response['error'] = true;
                $response['message'] = "web image type must jpg, jpeg, gif, or png!";
                print_r(json_encode($response));
                return false;
            }

            $string1 = '0123456789';
            $file1 = preg_replace("/\s+/", "_", $_FILES['web_image']['name']);
            $web_image = $function->get_random_string($string1, 4) . "-" . date("Y-m-d") . "." . $extension1;

            $upload1 = move_uploaded_file($_FILES['web_image']['tmp_name'], '../../upload/web-category-image/' . $web_image);
        }
        $upload_image = !empty($_FILES['upload_image']["name"]) ? 'upload/images/' . $menu_image : "";
        $web_images = !empty($_FILES["web_image"]["name"]) ? 'upload/web-category-image/' . $web_image : "";

        $sql_query = "INSERT INTO category (name,subtitle, image,status,web_image)VALUES('$category_name', '$category_subtitle', '$upload_image','1','$web_images')";
        if ($db->sql($sql_query)) {
            $res = $fn->get_data('', '', 'category', '', '', 'ORDER BY id DESC', 'LIMIT 0,1');

            // $sql = "SELECT * FROM category ORDER BY id DESC LIMIT 0,1 ";
            // $db->sql($sql);
            // $res = $db->getResult();

            $res[0]['image'] = DOMAIN_URL . $res[0]['image'];
            $res[0]['web_image'] = !empty($res[0]['web_image']) ? DOMAIN_URL . $res[0]['web_image'] : "";

            $response['error'] = false;
            $response['message'] = "Category Added Successfully!";
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = "Some Error Occrred! please try again.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
    }
    print_r(json_encode($response));
}

/*
2.update_category
    accesskey:90336
    update_category:1
    id:122
    category_name:Beverages
    category_subtitle:Cold Drinks, Soft Drinks, Sodas
    upload_image:FILE
*/

if (isset($_POST['update_category']) && !empty($_POST['update_category']) && $_POST['update_category'] == 1) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['category_name']) || empty($_POST['category_subtitle']) || empty($_POST['id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }
    $category_name = $db->escapeString($fn->xss_clean_array($_POST['category_name']));
    $category_subtitle = $db->escapeString($fn->xss_clean_array($_POST['category_subtitle']));

    $id = $db->escapeString($fn->xss_clean_array($_POST['id']));
    $sql = "SELECT * FROM `category` where id=" . $id;
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        if (isset($_FILES['upload_image'])) {
            if (!empty($res[0]['image']) || $res[0]['image'] != '') {
                $old_image = $res[0]['image'];
                if (!empty($old_image)) {
                    unlink('../../' . $old_image);
                }
            }
            $target_path = '../../upload/images/';
            if ($_FILES['upload_image']['error'] == 0) {
                if (!is_dir($target_path)) {
                    mkdir($target_path, 0777, true);
                }
                $extension = pathinfo($_FILES["upload_image"]["name"])['extension'];

                $result = $fn->validate_image($_FILES["upload_image"]);
                if ($result) {
                    $response['error'] = true;
                    $response['message'] = "image type must jpg, jpeg, gif, or png!";
                    print_r(json_encode($response));
                    return false;
                }

                $string = '0123456789';
                $file = preg_replace("/\s+/", "_", $_FILES['upload_image']['name']);
                $menu_image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

                $upload = move_uploaded_file($_FILES['upload_image']['tmp_name'], '../../upload/images/' . $menu_image);

                $upload_image = 'upload/images/' . $menu_image;
                $sql = "UPDATE category SET `image` = '" . $upload_image . "' where `id`=" . $id;
                $db->sql($sql);
            }
            if (isset($_FILES['web_image'])) {
                if (!empty($res[0]['web_image']) || $res[0]['web_image'] != '') {
                    $old_image1 = $res[0]['web_image'];
                    if (!empty($old_image1)) {
                        unlink('../../' . $old_image1);
                    }
                }
                $target_path1 = '../../upload/web-category-image/';
                if ($_FILES['web_image']['error'] == 0) {
                    if (!is_dir($target_path1)) {
                        mkdir($target_path1, 0777, true);
                    }
                    $extension1 = pathinfo($_FILES["web_image"]["name"])['extension'];

                    $result1 = $fn->validate_image($_FILES["web_image"]);
                    if ($result1) {
                        $response['error'] = true;
                        $response['message'] = "Web image type must jpg, jpeg, gif, or png!";
                        print_r(json_encode($response));
                        return false;
                    }

                    $string1 = '0123456789';
                    $file1 = preg_replace("/\s+/", "_", $_FILES['web_image']['name']);
                    $web_image = $function->get_random_string($string1, 4) . "-" . date("Y-m-d") . "." . $extension1;

                    $upload1 = move_uploaded_file($_FILES['web_image']['tmp_name'], '../../upload/web-category-image/' . $web_image);

                    $upload_image1 = 'upload/web-category-image/' . $web_image;
                    $sql1 = "UPDATE category SET  `web_image` = '" . $upload_image1 . "' where `id`=" . $id;
                    $db->sql($sql1);
                }
            }
        }
        $sql_query = "UPDATE category SET `name` =  '" . $category_name . "',`subtitle` = '" . $category_subtitle . "',`status` = '1' where `id`=" . $id;
        if ($db->sql($sql_query)) {
            $sql = "SELECT * FROM category  where id = $id ";
            $db->sql($sql);
            $res = $db->getResult();
            $res[0]['image'] = DOMAIN_URL . $res[0]['image'];
            $res[0]['web_image'] = !empty($res[0]['web_image']) ? DOMAIN_URL . $res[0]['web_image'] : "";

            $response['error'] = false;
            $response['message'] = "Category Updated Successfully!";
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = "Some Error Occrred! please try again.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Id is not found.";
    }
    print_r(json_encode($response));
    return false;
}

/*
3.delete_category
    accesskey:90336
    delete_category:1
    id:122
*/

if ((isset($_POST['delete_category'])) && ($_POST['delete_category'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass id field!";
        print_r(json_encode($response));
        return false;
    }

    $id = $db->escapeString($fn->xss_clean($_POST['id']));

    $sql_query = "SELECT image,web_image FROM category WHERE id =" . $id;
    $db->sql($sql_query);
    $res = $db->getResult();
    if ($res[0]['image']) {
        unlink('../../' . $res[0]['image']);
    }
    if ($res[0]['web_image']) {
        unlink('../../' . $res[0]['web_image']);
    }
    $sql_query = "DELETE FROM `category` WHERE id=" . $id;
    $db->sql($sql_query);
    $delete_category_result = $db->getResult();
    if (!empty($delete_category_result)) {
        $delete_category_result = 0;
    } else {
        $delete_category_result = 1;
    }

    $sql_query = "SELECT image FROM subcategory WHERE category_id =" . $id;
    $db->sql($sql_query);
    $result = $db->getResult();
    foreach ($result as $res) {
        if (!empty($res['image'])) {
            unlink('../../' . $res['image']);
        }
    }
    $sql_query = "DELETE FROM `subcategory` WHERE category_id=" . $id;
    $db->sql($sql_query);
    $delete_subcategory_result = $db->getResult();
    if (!empty($delete_subcategory_result)) {
        $delete_subcategory_result = 0;
    } else {
        $delete_subcategory_result = 1;
    }

    $sql_query = "SELECT pv.product_id,pv.id,p.image,p.other_images,p.size_chart FROM products p LEFT JOIN product_variant pv ON p.id=pv.product_id WHERE category_id =" . $id;
    $db->sql($sql_query);
    $result = $db->getResult();
    foreach ($result as $res) {
        if ($res['image']) {
            unlink('../../' . $res['image']);
        }
        if (!empty($res['size_chart'])) {
            unlink('../../' . $res['size_chart']);
        }
        if (!empty($res['other_images'])) {
            $other_images = json_decode($res['other_images']);
            foreach ($other_images as $other_image) {
                unlink($other_image);
            }
        }
        $sql_query = "DELETE FROM cart WHERE product_id = " . $res['product_id'];
        $db->sql($sql_query);
        $cart_result = $db->getResult();

        $sql_query = "DELETE FROM favorites WHERE product_id = " . $res['product_id'];
        $db->sql($sql_query);
        $favourites_result = $db->getResult();
    }
    $sql_query = "DELETE FROM `products` WHERE category_id=" . $id;
    $db->sql($sql_query);
    $delete_product_result = $db->getResult();
    if (!empty($delete_product_result)) {
        $delete_product_result = 0;
    } else {
        $delete_product_result = 1;
    }

    if ($delete_category_result == 1 && $delete_subcategory_result == 1 && $delete_product_result = 1) {
        $response['error'] = false;
        $response['message'] = "Category Deleted Successfully!";
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
}

/* 
4.get_categories
    accesskey:90336
    get_categories:1
    category_id:28      // {optional}
    limit:10            // {optional}
    offset:0            // {optional}
    sort:id             // {optional}
    order:ASC/DESC      // {optional}
*/

if (isset($_POST['get_categories']) && !empty($_POST['get_categories']) && ($_POST['get_categories'] == 1)) {

    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    $category_id = (isset($_POST['category_id']) && !empty(trim($_POST['category_id']))) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : '';

    if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        $where = " where id = '$category_id' ";
    }

    $sql = "SELECT count(id) as total FROM category $where";
    $db->sql($sql);
    $total = $db->getResult();

    $sql_query = "SELECT * FROM category $where ORDER BY id ASC";
    $db->sql($sql_query);
    $res = $db->getResult();
    foreach ($total as $row) {
        $total = $row['total'];
    }
    if (!empty($res)) {
        for ($i = 0; $i < count($res); $i++) {
            $res[$i]['image'] = (!empty($res[$i]['image'])) ? DOMAIN_URL  . $res[$i]['image'] : "";
            $res[$i]['web_image'] = (!empty($res[$i]['web_image'])) ? DOMAIN_URL . $res[$i]['web_image'] : "";
            $tmp = [];
        }
        foreach ($res as $r) {
            $r['childs'] = [];
            $db->sql("SELECT * FROM subcategory WHERE category_id = '" . $r['id'] . "' ORDER BY id DESC");
            $childs = $db->getResult();
            $temp = array('id' => "0", 'category_id' => "0", 'name' => "Select SubCategory", 'slug' => "", 'subtitle' => "", 'image' => "");
            if (!empty($childs)) {
                for ($i = 0; $i < count($childs); $i++) {
                    $childs[$i]['image'] = (!empty($childs[$i]['image'])) ? DOMAIN_URL  . $childs[$i]['image'] : '';
                    $r['childs'][$i] = (array)$childs[$i];
                }
            }
            array_unshift($r['childs'], $temp);
            $tmp[] = $r;
        }
        $res = $tmp;

        $response['error'] = false;
        $response['message'] = "Categories fetched successfully";
        $response['total'] = $total;
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Categories not fetched";
    }
    print_r(json_encode($response));
    return false;
}

/*
5.add_subcategory
    accesskey:90336
    add_subcategory:1
    subcategory_name:baverages
    subcategory_subtitle:Cold Drinks, Soft Drinks, Sodas
    category_id:46
    upload_image:FILE
*/

if ((isset($_POST['add_subcategory'])) && ($_POST['add_subcategory'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['subcategory_name']) || empty($_POST['subcategory_subtitle']) || empty($_POST['category_id']) || empty($_FILES['upload_image'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
        exit();
    }
    $subcategory_name = $db->escapeString($fn->xss_clean_array($_POST['subcategory_name']));
    $slug = $function->slugify($db->escapeString($fn->xss_clean($_POST['subcategory_name'])));
    $sql = "SELECT slug FROM subcategory";
    $db->sql($sql);
    $res = $db->getResult();
    $i = 1;
    foreach ($res as $row) {
        if ($slug == $row['slug']) {
            $slug = $slug . '-' . $i;
            $i++;
        }
    }

    $subcategory_subtitle = $db->escapeString($fn->xss_clean_array($_POST['subcategory_subtitle']));
    $category_id = $db->escapeString($fn->xss_clean_array($_POST['category_id']));

    $target_path = '../../upload/images/';

    if ($_FILES['upload_image']['error'] == 0) {
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $extension = pathinfo($_FILES["upload_image"]["name"])['extension'];

        $result = $fn->validate_image($_FILES["upload_image"]);
        if ($result) {
            $response['error'] = true;
            $response['message'] = "image type must jpg, jpeg, gif, or png!";
            print_r(json_encode($response));
            return false;
        }

        $string = '0123456789';
        $file = preg_replace("/\s+/", "_", $_FILES['upload_image']['name']);
        $menu_image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

        $upload = move_uploaded_file($_FILES['upload_image']['tmp_name'], '../../upload/images/' . $menu_image);

        $upload_image = 'upload/images/' . $menu_image;

        $sql_query = "INSERT INTO subcategory (category_id, name, slug, subtitle, image)VALUES('$category_id', '$subcategory_name', '$slug', '$subcategory_subtitle', '$upload_image')";
        if ($db->sql($sql_query)) {
            $sql = "SELECT s.*,c.name as category_name FROM subcategory s LEFT JOIN category c ON c.id=s.category_id ORDER BY id DESC LIMIT 0,1 ";
            $db->sql($sql);
            $res = $db->getResult();
            $res[0]['image'] = DOMAIN_URL . $res[0]['image'];

            $response['error'] = false;
            $response['message'] = "Subcategory Added Successfully!";
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = "Some Error Occrred! please try again.";
        }
        print_r(json_encode($response));
    }
}

/*
6.update_subcategory
    accesskey:90336
    update_subcategory:1
    id:122
    subcategory_name:baverages
    subcategory_subtitle:Cold Drinks, Soft Drinks, Sodas
    category_id: 46
    upload_image:FILE
*/

if ((isset($_POST['update_subcategory'])) && ($_POST['update_subcategory'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['id']) || empty($_POST['subcategory_name']) || empty($_POST['subcategory_subtitle']) || empty($_POST['category_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass All fields!";
        print_r(json_encode($response));
        return false;
    }

    $id = $db->escapeString($fn->xss_clean($_POST['id']));

    $sql = "SELECT * FROM `subcategory` where id=" . $id;
    $db->sql($sql);
    $res = $db->getResult();

    $subcategory_name = $db->escapeString($fn->xss_clean_array($_POST['subcategory_name']));
    $slug = $function->slugify($db->escapeString($fn->xss_clean($_POST['subcategory_name'])));
    $sql = "SELECT slug FROM subcategory";
    $db->sql($sql);
    $res = $db->getResult();
    $i = 1;
    foreach ($res as $row) {
        if ($slug == $row['slug']) {
            $slug = $slug . '-' . $i;
            $i++;
        }
    }

    $subcategory_subtitle = $db->escapeString($fn->xss_clean_array($_POST['subcategory_subtitle']));
    $category_id = $db->escapeString($fn->xss_clean_array($_POST['category_id']));

    $sql = "SELECT id,image FROM `subcategory` where id=$id";
    $db->sql($sql);
    $res1 = $db->getResult();

    $target_path = '../../upload/images/';
    if (isset($_FILES['upload_image'])) {
        if ($_FILES['upload_image']['error'] == 0) {
            if (!empty($res1[0]['image'])) {
                $old_image = $res1[0]['image'];
                if (!empty($old_image)) {
                    unlink('../../' . $old_image);
                }
            }
            if (!is_dir($target_path)) {
                mkdir($target_path, 0777, true);
            }
            $extension = pathinfo($_FILES["upload_image"]["name"])['extension'];

            $result = $fn->validate_image($_FILES["upload_image"]);
            if ($result) {
                $response['error'] = true;
                $response['message'] = "image type must jpg, jpeg, gif, or png!";
                print_r(json_encode($response));
                return false;
            }

            $string = '0123456789';
            $file = preg_replace("/\s+/", "_", $_FILES['upload_image']['name']);
            $menu_image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;

            $upload = move_uploaded_file($_FILES['upload_image']['tmp_name'], '../../upload/images/' . $menu_image);

            $upload_image = 'upload/images/' . $menu_image;
            $sql1 = "UPDATE subcategory SET  `image` = '" . $upload_image . "' where `id`=" . $id;
            $db->sql($sql1);
        }
    }
    $sql_query = "UPDATE subcategory SET `category_id` =  '" . $category_id . "',`name` = '" . $subcategory_name . "', `slug` = '" . $slug . "', `subtitle` = '" . $subcategory_subtitle . "' where `id`=" . $id;
    if ($db->sql($sql_query)) {
        $sql = "SELECT s.*,c.name as category_name FROM subcategory s LEFT JOIN category c ON c.id=s.category_id where s.id = $id ";
        $db->sql($sql);
        $res = $db->getResult();
        $res[0]['image'] = DOMAIN_URL . $res[0]['image'];

        $response['error'] = false;
        $response['message'] = "Subcategory updated Successfully!";
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
}

/*
7.delete_subcategory
    accesskey:90336
    delete_subcategory:1
    id:122
*/

if ((isset($_POST['delete_subcategory'])) && ($_POST['delete_subcategory'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass id fields!";
        print_r(json_encode($response));
        return false;
    }

    $id = $db->escapeString($fn->xss_clean($_POST['id']));

    $sql_query = "SELECT image FROM subcategory WHERE id =" . $id;
    $db->sql($sql_query);
    $res = $db->getResult();

    if ($res[0]['image']) {
        unlink('../../' . $res[0]['image']);
    }

    $sql_query = "DELETE FROM `subcategory` WHERE id=" . $id;
    if ($db->sql($sql_query)) {
        $response['error'] = false;
        $response['message'] = "subcategory Deleted Successfully!";
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
}

/* 
8.get_subcategories
    accesskey:90336
    get_subcategories:1
    category_id:28      // {optional}
    limit:10            // {optional}
    offset:0            // {optional}
    sort:id             // {optional}
    order:ASC/DESC      // {optional}
*/
if (isset($_POST['get_subcategories']) && !empty($_POST['get_subcategories']) && ($_POST['get_subcategories'] == 1)) {
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    $category_id = (isset($_POST['category_id']) && !empty(trim($_POST['category_id']))) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : '';

    if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        $where = " where s.category_id = '$category_id' ";
    }

    $sql = "SELECT count(s.id) as total FROM subcategory s $where";
    $db->sql($sql);
    $res1 = $db->getResult();
    $total = $res1[0]['total'];

    $sql_query = "SELECT s.*,c.name as category_name FROM subcategory s LEFT JOIN category c ON c.id = s.category_id $where ORDER BY s.row_order ASC";
    $db->sql($sql_query);
    $res = $db->getResult();
    for ($i = 0; $i < count($res); $i++) {
        $res[$i]['image'] = (!empty($res[$i]['image'])) ? DOMAIN_URL . '' . $res[$i]['image'] : '';
    }

    if (!empty($res)) {
        $response['error'] = false;
        $response['message'] = "Subcategory fetched successfully";
        $response['total'] = $total;
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Subcategory not fetched";
    }
    print_r(json_encode($response));
    return false;
}

/* 
9. add_delivery_boy
    accesskey:90336
    add_delivery_boy:1		
    name:delivery_boy
    mobile:9963258652
    address:time square
    bonus:10
    dob:2020-09-12
    bank_name:SBI
    account_number:12547896523652
    account_name:DEMO
    ifsc_code:254SBIfbfg
    password:asd124
    other_payment_info:description  // {optional}
    driving_license:FILE
    national_identity_card:FILE
*/
if (isset($_POST['add_delivery_boy']) && !empty($_POST['add_delivery_boy'])  && ($_POST['add_delivery_boy'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['name']) || empty($_POST['mobile']) || empty($_POST['address']) || empty($_POST['bonus']) || empty($_POST['dob']) || empty($_POST['bank_name']) ||  empty($_POST['account_number']) || empty($_POST['account_name']) || empty($_POST['ifsc_code']) || empty($_POST['password']) || empty($_FILES['driving_license']) || empty($_FILES['national_identity_card'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    $name = $db->escapeString(trim($fn->xss_clean($_POST['name'])));
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    $address = $db->escapeString(trim($fn->xss_clean($_POST['address'])));
    $bonus = $db->escapeString(trim($fn->xss_clean($_POST['bonus'])));
    $dob = $db->escapeString(trim($fn->xss_clean($_POST['dob'])));
    $bank_name = $db->escapeString(trim($fn->xss_clean($_POST['bank_name'])));
    $other_payment_info = (isset($_POST['other_payment_info']) && !empty(trim($_POST['other_payment_info']))) ? $db->escapeString(trim($fn->xss_clean($_POST['other_payment_info']))) : '';
    $account_number = $db->escapeString(trim($fn->xss_clean($_POST['account_number'])));
    $account_name = $db->escapeString(trim($fn->xss_clean($_POST['account_name'])));
    $ifsc_code = $db->escapeString(trim($fn->xss_clean($_POST['ifsc_code'])));
    $password = $db->escapeString(trim($fn->xss_clean($_POST['password'])));

    $sql = 'SELECT id FROM delivery_boys WHERE mobile=' . $mobile;
    $db->sql($sql);
    $res = $db->getResult();
    $count = $db->numRows($res);

    if ($count > 0) {
        $response['error'] = true;
        $response['message'] = "Mobile Number Already Exists!";
        print_r(json_encode($response));
        return false;
    }

    $target_path = '../../upload/delivery-boy/';
    if ($_FILES['driving_license']['error'] == 0 && $_FILES['driving_license']['size'] > 0) {
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $extension = pathinfo($_FILES["driving_license"]["name"])['extension'];

        $result = $fn->validate_image($_FILES["driving_license"]);
        if ($result) {
            $response['error'] = true;
            $response['message'] = "Driving License image type must jpg, jpeg, gif, or png!";
            print_r(json_encode($response));
            return false;
        }
        $dr_filename = microtime(true) . '.' . strtolower($extension);
        $dr_full_path = $target_path . "" . $dr_filename;
        if (!move_uploaded_file($_FILES["driving_license"]["tmp_name"], $dr_full_path)) {
            $response['error'] = true;
            $response['message'] = "Invalid directory to load image!";
            print_r(json_encode($response));
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
            $response['error'] = true;
            $response['message'] = "National Identity Card image type must jpg, jpeg, gif, or png!";
            print_r(json_encode($response));
            return false;
        }

        $nic_filename = microtime(true) . '.' . strtolower($extension);
        $nic_full_path = $target_path . "" . $nic_filename;
        if (!move_uploaded_file($_FILES["national_identity_card"]["tmp_name"], $nic_full_path)) {
            $response['error'] = true;
            $response['message'] = "Invalid directory to load image!";
            print_r(json_encode($response));
            return false;
        }
    }
    $sql = "INSERT INTO delivery_boys (`name`,`mobile`,`password`,`address`,`bonus`, `driving_license`, `national_identity_card`, `dob`, `bank_account_number`, `bank_name`, `account_name`, `ifsc_code`,`other_payment_information`) VALUES ('$name', '$mobile', '$password', '$address','$bonus','$dr_filename', '$nic_filename', '$dob','$account_number','$bank_name','$account_name','$ifsc_code','$other_payment_info')";
    if ($db->sql($sql)) {
        $sql = "SELECT * FROM delivery_boys ORDER BY id DESC LIMIT 0,1 ";
        $db->sql($sql);
        $res = $db->getResult();
        $res[0]['order_note'] = !empty($res[0]['order_note']) ? $res[0]['order_note'] : "";
        $res[0]['fcm_id'] = !empty($res[0]['fcm_id']) ? $res[0]['fcm_id'] : "";
        $res[0]['driving_license'] = DOMAIN_URL . 'upload/delivery-boy/' . $res[0]['driving_license'];
        $res[0]['national_identity_card'] =  DOMAIN_URL . 'upload/delivery-boy/' . $res[0]['national_identity_card'];

        $response['error'] = false;
        $response['message'] = "Delivery Boy Added Successfully!";
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
}

/* 
10. update_delivery_boy
    accesskey:90336
    update_delivery_boy:1
    id:12
    name:delivery_boy
    mobile:9963258652
    address:time square
    bonus:10
    dob:2020-09-12
    bank_name:SBI
    account_number:12547896523652
    account_name:DEMO
    ifsc_code:254SBIfbfg
    status:1
    other_payment_info:description // {optional}
    password:asd124                // {optional}
    driving_license:FILE           // {optional}
    national_identity_card:FILE    // {optional}
*/
if (isset($_POST['update_delivery_boy']) && !empty($_POST['update_delivery_boy'])  && ($_POST['update_delivery_boy'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['name']) || empty($_POST['mobile']) || empty($_POST['address']) || empty($_POST['bonus']) || empty($_POST['dob']) || empty($_POST['bank_name']) ||  empty($_POST['account_number']) || empty($_POST['account_name']) || empty($_POST['ifsc_code'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }
    $id = $db->escapeString($fn->xss_clean($_POST['id']));
    $sql = "SELECT id FROM `delivery_boys` where id=$id";
    $db->sql($sql);
    $res1 = $db->getResult();

    if (!empty($res1)) {
        if ($id == 104) {
            $response['error'] = true;
            $response['message'] = "Sorry you can not update this delivery boy.";
            print_r(json_encode($response));
            return false;
        }
        $name = $db->escapeString($fn->xss_clean($_POST['name']));
        $mobile = $db->escapeString($fn->xss_clean($_POST['mobile']));
        $password = !empty($_POST['password']) ? $db->escapeString($fn->xss_clean($_POST['password'])) : '';
        $other_payment_info = !empty($_POST['other_payment_info']) ? $db->escapeString($fn->xss_clean($_POST['other_payment_info'])) : '';
        $address = $db->escapeString($fn->xss_clean($_POST['address']));
        $bonus = $db->escapeString($fn->xss_clean($_POST['bonus']));
        $status = $db->escapeString($fn->xss_clean($_POST['status']));
        $dob = $db->escapeString($fn->xss_clean($_POST['dob']));
        $bank_name = $db->escapeString($fn->xss_clean($_POST['bank_name']));
        $account_number = $db->escapeString($fn->xss_clean($_POST['account_number']));
        $account_name = $db->escapeString($fn->xss_clean($_POST['account_name']));
        $ifsc_code = $db->escapeString($fn->xss_clean($_POST['ifsc_code']));
        $password = !empty($password) ? md5($password) : '';
        $target_path = '../../upload/delivery-boy/';

        $sql = "SELECT id,driving_license,national_identity_card,other_payment_information FROM `delivery_boys` where id=$id";
        $db->sql($sql);
        $res = $db->getResult();
        if ($other_payment_info == '') {
            $other_payment_info = $res[0]['other_payment_information'];
        }

        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        if (isset($_FILES['driving_license']) && $_FILES['driving_license']['size'] != 0 && $_FILES['driving_license']['error'] == 0 && !empty($_FILES['driving_license'])) {
            if (!empty($res[0]['driving_license'])) {
                $old_image = $res[0]['driving_license'];
                if (!empty($old_image)) {
                    unlink($target_path . $old_image);
                }
            }
            $extension = pathinfo($_FILES["driving_license"]["name"])['extension'];

            $result = $fn->validate_image($_FILES["driving_license"]);
            if ($result) {
                $response['error'] = true;
                $response['message'] = "Driving License image type must jpg, jpeg, gif, or png!";
                print_r(json_encode($response));
                return false;
            }

            $dr_filename = microtime(true) . '.' . strtolower($extension);
            $dr_full_path = $target_path . "" . $dr_filename;
            if (!move_uploaded_file($_FILES["driving_license"]["tmp_name"], $dr_full_path)) {
                $response['error'] = true;
                $response['message'] = "Can not upload driving license.";
                print_r(json_encode($response));
                return false;
            }
            $sql = "UPDATE delivery_boys SET `driving_license`='" . $dr_filename . "' WHERE `id`=" . $id;
            $db->sql($sql);
        }
        if (isset($_FILES['national_identity_card']) && $_FILES['national_identity_card']['size'] != 0 && $_FILES['national_identity_card']['error'] == 0 && !empty($_FILES['national_identity_card'])) {
            if (!empty($res[0]['national_identity_card'])) {
                $old_image = $res[0]['national_identity_card'];
                if (!empty($old_image)) {
                    unlink($target_path . $old_image);
                }
            }
            $extension = pathinfo($_FILES["national_identity_card"]["name"])['extension'];

            $result = $fn->validate_image($_FILES["national_identity_card"]);
            if ($result) {
                $response['error'] = true;
                $response['message'] = "National Identity Card image type must jpg, jpeg, gif, or png!";
                print_r(json_encode($response));
                return false;
            }
            $nic_filename = microtime(true) . '.' . strtolower($extension);
            $nic_full_path = $target_path . "" . $nic_filename;
            if (!move_uploaded_file($_FILES["national_identity_card"]["tmp_name"], $nic_full_path)) {
                $response['error'] = true;
                $response['message'] = "Can not upload national identity card";
                print_r(json_encode($response));
                return false;
            }
            $sql = "UPDATE delivery_boys SET `national_identity_card`='" . $nic_filename . "' WHERE `id`=" . $id;
            $db->sql($sql);
        }

        if (!empty($password)) {
            $sql = "Update delivery_boys set `name`='" . $name . "',`mobile`='" . $mobile . "',password='" . $password . "',`address`='" . $address . "',`bonus`='" . $bonus . "',`status`='" . $status . "',`dob`='$dob',`bank_account_number`='$account_number',`bank_name`='$bank_name',`account_name`='$account_name',`ifsc_code`='$ifsc_code',`other_payment_information`='$other_payment_info' where `id`=" . $id;
        } else {
            $sql = "Update delivery_boys set `name`='" . $name . "',`mobile`='" . $mobile . "',`address`='" . $address . "',`bonus`='" . $bonus . "',`status`='" . $status . "',`dob`='$dob',`bank_account_number`='$account_number',`bank_name`='$bank_name',`account_name`='$account_name',`ifsc_code`='$ifsc_code',`other_payment_information`='$other_payment_info'  where `id`=" . $id;
        }
        if ($db->sql($sql)) {
            $sql = "SELECT * FROM delivery_boys  where id = $id ";
            $db->sql($sql);
            $res = $db->getResult();
            $res[0]['order_note'] = !empty($res[0]['order_note']) ? $res[0]['order_note'] : "";
            $res[0]['fcm_id'] = !empty($res[0]['fcm_id']) ? $res[0]['fcm_id'] : "";
            $res[0]['driving_license'] = DOMAIN_URL . 'upload/delivery-boy/' . $res[0]['driving_license'];
            $res[0]['national_identity_card'] =  DOMAIN_URL . 'upload/delivery-boy/' . $res[0]['national_identity_card'];

            $response['error'] = false;
            $response['message'] = "Information Updated Successfully!";
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = "Some Error Occurred! Please Try Again!";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Delivery boy does not exist";
    }
    print_r(json_encode($response));
}

/* 
11.delete_delivery_boy
    accesskey:90336
    delete_delivery_boy:1		
    id:302
*/
if (isset($_POST['delete_delivery_boy']) && !empty($_POST['delete_delivery_boy'])  && ($_POST['delete_delivery_boy'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['id'])) {
        $response['error'] = true;
        $response['message'] = "delivery boy id is missing!";
        print_r(json_encode($response));
        return false;
    }

    $id = $db->escapeString($fn->xss_clean($_POST['id']));
    $sql = "SELECT id FROM `delivery_boys` where id=$id";
    $db->sql($sql);
    $res1 = $db->getResult();
    if (!empty($res1)) {
        $target_path = '../../upload/delivery-boy/';

        if ($id == 104) {
            $response['error'] = true;
            $response['message'] = "Sorry you can not delete this delivery boy.";
            print_r(json_encode($response));
            return false;
        }
        $sql1 = "SELECT id,driving_license,national_identity_card,other_payment_information FROM `delivery_boys` where id=$id";
        $db->sql($sql1);
        $res1 = $db->getResult();
        $sql = "DELETE FROM `delivery_boys` WHERE id=" . $id;
        if ($db->sql($sql)) {
            $sql = "DELETE FROM `fund_transfers` WHERE delivery_boy_id=" . $id;
            $db->sql($sql);
            $sql = "DELETE FROM `withdrawal_requests` WHERE `type_id`=" . $id . " AND `type`='delivery_boy'";
            $db->sql($sql);

            if (!empty($res1[0]['driving_license']) || $res1[0]['driving_license'] != '') {
                $old_image = $res1[0]['driving_license'];
                if (!empty($old_image)) {
                    unlink($target_path . $old_image);
                }
            }
            if (!empty($res1[0]['national_identity_card']) || $res1[0]['national_identity_card'] != '') {
                $old_image = $res1[0]['national_identity_card'];
                if (!empty($old_image)) {
                    unlink($target_path . $old_image);
                }
            }
            $response['error'] = false;
            $response['message'] = "Delivery boy deleted successfully";
        } else {
            $response['error'] = true;
            $response['message'] = "Delivery boy not deleted.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Delivery boy does not exist.";
    }
    print_r(json_encode($response));
}

/* 
12.get_delivery_boys
   accesskey:90336
   get_delivery_boys:1
   id:292           // {optional}
   limit:10         // {optional}
   offset:0         // {optional}
   sort:id          // {optional}
   order:ASC/DESC   // {optional}
   search:value     // {optional}
*/
if (isset($_POST['get_delivery_boys']) && !empty($_POST['get_delivery_boys'])  && ($_POST['get_delivery_boys'] == 1)) {
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : "";

    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $db->escapeString($fn->xss_clean($_POST['id']));
        $where .= ' where id=' . $id;
    }
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if (!empty($where)) {
            $where = " AND `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%'";
        } else {
            $where = " WHERE `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%'";
        }
    }
    $sql = "SELECT COUNT(id) as total FROM `delivery_boys` " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row)
            $total = $row['total'];
        if ($limit == "") {
            $sql = "SELECT * FROM `delivery_boys` " . $where . " ORDER BY " . $sort . " " . $order;
        } else {
            $sql = "SELECT * FROM `delivery_boys` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
        }
        $db->sql($sql);
        $res = $db->getResult();
        $rows = array();
        $tempRow = array();

        $path = 'upload/delivery-boy/';
        foreach ($res as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['address'] = !empty($row['address']) ? $row['address'] : "";
            $tempRow['bonus'] = $row['bonus'];
            $tempRow['balance'] = ceil($row['balance']);
            if (!empty($row['driving_license'])) {
                $tempRow['driving_license'] = DOMAIN_URL . $path . $row['driving_license'];
                $tempRow['national_identity_card'] = DOMAIN_URL . $path . $row['national_identity_card'];
            } else {
                $tempRow['national_identity_card'] = "No National Identity Card";
                $tempRow['driving_license'] = "No Driving License";
            }
            $tempRow['dob'] = !empty($row['dob']) ? $row['dob'] : "";
            $tempRow['bank_account_number'] = !empty($row['bank_account_number']) ? $row['bank_account_number'] : "";
            $tempRow['bank_name'] = !empty($row['bank_name']) ? $row['bank_name'] : "";
            $tempRow['account_name'] = !empty($row['account_name']) ? $row['account_name'] : "";
            $tempRow['other_payment_information'] = (!empty($row['other_payment_information'])) ? $row['other_payment_information'] : "";
            $tempRow['ifsc_code'] = !empty($row['ifsc_code']) ? $row['ifsc_code'] : "";
            if ($row['status'] == 0)
                $tempRow['status'] = "Deactive";
            else
                $tempRow['status'] = "Active";
            $rows[] = $tempRow;
        }
        $response['error'] = false;
        $response['message'] = "Delivery Boys fatched successfully.";
        $response['total'] = $total;
        $response['data'] = $rows;
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again leter.";
    }
    print_r(json_encode($response));
}

/* 
13.add_products
    accesskey:90336
    add_products:1
    name:potato
    category_id:31
    subcategory_id:115
    description:potatos
    image:FILE          
    tax_id:4                    // {optional}
    manufacturer:india          // {optional}
    made_in:india               // {optional}
    return_status:0 / 1         // {optional}
    cancelable_status:0 / 1     // {optional}
    other_images[]:FILE         // {optiomal}
    size_chart:FILE             // {optional}     
    shipping_delivery:Potatos   // {optional}  
    is_cod_allowed: 0 / 1       // {optional}
    status: 0 / 1               // {optional}
    indicator:	 1 - veg / 2 - non-veg             // {optional}
    till_status: received / processed / shipped    // {optional}

    type:packet
    measurement:500,400
    measurement_unit_id:4,1
    price:175,145
    serve_for:Available / Sold Out
    stock:992,458
    stock_unit_id:4,1
    sku:60,30                    // {optional}
    discounted_price:60,30       // {optional}
    images[0][] : FILE           // {optional}
    images[1][] : FILE           // {optional}

    type:loose
    measurement:1,1
    measurement_unit_id:1,5
    price:100,500
    serve_for:Available / Sold Out 
    stock:997
    stock_unit_id:1
    sku:60,30                    // {optional}
    discounted_price:20,15       // {optional}
    images[0][] : FILE           // {optional}
    images[1][] : FILE           // {optional}
*/

if (isset($_POST['add_products']) && !empty($_POST['add_products']) && ($_POST['add_products'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['name']) || $_POST['category_id'] == "" || $_POST['subcategory_id'] == "" || empty($_POST['serve_for']) || empty($_POST['description']) || $_POST['type'] == "" || empty($_FILES['image'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    if ($_POST['type']) {
        if ($_POST['measurement'] == "" || empty($_POST['measurement_unit_id']) || empty($_POST['price']) || empty($_POST['serve_for']) || $_POST['stock'] == "" || empty($_POST['stock_unit_id'])) {
            $response['error'] = true;
            $response['message'] = "Please pass product variants fields!";
            print_r(json_encode($response));
            return false;
        }
    }

    $name = $db->escapeString($fn->xss_clean($_POST['name']));
    $tax_id = (isset($_POST['tax_id']) && $_POST['tax_id'] != '') ? $db->escapeString($fn->xss_clean($_POST['tax_id'])) : 0;
    $slug = $function->slugify($db->escapeString($fn->xss_clean($_POST['name'])));
    $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
    $subcategory_id = (isset($_POST['subcategory_id']) && $_POST['subcategory_id'] != '') ? $db->escapeString($fn->xss_clean($_POST['subcategory_id'])) : 0;
    $serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
    $description = $db->escapeString($fn->xss_clean($_POST['description']));
    $manufacturer = (isset($_POST['manufacturer']) && $_POST['manufacturer'] != '') ? $db->escapeString($fn->xss_clean($_POST['manufacturer'])) : '';
    $made_in = (isset($_POST['made_in']) && $_POST['made_in'] != '') ? $db->escapeString($fn->xss_clean($_POST['made_in'])) : '';
    $indicator = (isset($_POST['indicator']) && $_POST['indicator'] != '') ? $db->escapeString($fn->xss_clean($_POST['indicator'])) : '0';
    $return_status = (isset($_POST['return_status']) && $_POST['return_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['return_status'])) : '0';
    $cancelable_status = (isset($_POST['cancelable_status']) && $_POST['cancelable_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['cancelable_status'])) : '0';
    $till_status = (isset($_POST['till_status']) && $_POST['till_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['till_status'])) : '';
    $shipping_delivery = (isset($_POST['shipping_delivery']) && $_POST['shipping_delivery'] != '') ? $db->escapeString($fn->xss_clean($_POST['shipping_delivery'])) : '';
    $is_cod_allowed = (isset($_POST['is_cod_allowed']) && $_POST['is_cod_allowed'] != '') ? $db->escapeString($fn->xss_clean($_POST['is_cod_allowed'])) : '0';
    $status = (isset($_POST['status']) && $_POST['status'] != '') ? $db->escapeString($fn->xss_clean($_POST['status'])) : '1';

    $image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
    $image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
    $image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));

    $allowedExts = array("gif", "jpeg", "jpg", "png");

    error_reporting(E_ERROR | E_PARSE);
    $extension = pathinfo($_FILES["image"]["name"])['extension'];

    $error['other_images'] = $error['image'] = '';

    if ($image_error > 0) {
        $response['error'] = true;
        $response['message'] = "image Not uploaded!";
        print_r(json_encode($response));
        return false;
    } else {
        $result = $fn->validate_image($_FILES["image"]);
        if ($result) {
            $response['error'] = true;
            $response['message'] = "image type must jpg, jpeg, gif, or png!";
            print_r(json_encode($response));
            return false;
        }
    }

    if (isset($_FILES["other_images"]) && $_FILES["other_images"]["error"] == 0) {
        for ($i = 0; $i < count($_FILES["other_images"]["name"]); $i++) {
            if ($_FILES["other_images"]["error"][$i] > 0) {
                $response['error'] = true;
                $response['message'] = "Other Images not uploaded!";
                print_r(json_encode($response));
                return false;
            } else {
                $result = $fn->validate_other_images($_FILES["other_images"]["tmp_name"][$i], $_FILES["other_images"]["type"][$i]);
                if ($result) {
                    $response['error'] = true;
                    $response['message'] = "other image type must jpg, jpeg, gif, or png!";
                    print_r(json_encode($response));
                    return false;
                }
            }
        }
    }

    if (isset($_FILES['size_chart'])) {
        if (!empty($res[0]['size_chart']) || $res[0]['size_chart'] != '') {
            $old_image1 = $res[0]['size_chart'];
            if (!empty($old_image1)) {
                unlink('../../' . $old_image1);
            }
        }
        $target_path1 = '../../upload/images/';
        if ($_FILES['size_chart']['error'] == 0) {
            if (!is_dir($target_path1)) {
                mkdir($target_path1, 0777, true);
            }
            $extension1 = pathinfo($_FILES["size_chart"]["name"])['extension'];

            $result1 = $fn->validate_image($_FILES["size_chart"]);
            if ($result1) {
                $response['error'] = true;
                $response['message'] = "Size chart image type must jpg, jpeg, gif, or png!";
                print_r(json_encode($response));
                return false;
            }

            $string1 = '0123456789';
            $file1 = preg_replace("/\s+/", "_", $_FILES['size_chart']['name']);
            $size_chart = $function->get_random_string($string1, 4) . "-" . date("Y-m-d") . "." . $extension1;

            $upload1 = move_uploaded_file($_FILES['size_chart']['tmp_name'], '../../upload/images/' . $size_chart);
        }
    }

    $string = '0123456789';
    $file = preg_replace("/\s+/", "_", $_FILES['image']['name']);

    $image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;
    $upload = move_uploaded_file($_FILES['image']['tmp_name'], '../../upload/images/' . $image);
    $other_images = '';

    if (isset($_FILES['other_images']) && ($_FILES['other_images']['size'][0] > 0)) {
        $file_data = array();
        $target_path = '../../upload/other_images/';
        $target_path1 = 'upload/other_images/';
        for ($i = 0; $i < count($_FILES["other_images"]["name"]); $i++) {

            $filename = $_FILES["other_images"]["name"][$i];
            $temp = explode('.', $filename);
            $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
            $file_data[] = $target_path1 . '' . $filename;
            if (!move_uploaded_file($_FILES["other_images"]["tmp_name"][$i], $target_path . '' . $filename)) {
                $response['error'] = true;
                $response['message'] = "Other Images not uploaded!";
                print_r(json_encode($response));
                return false;
            }
        }
        $other_images = !empty($file_data) ? json_encode($file_data) : "";
    }
    $upload_image1 = !empty($_FILES["size_chart"]) ? 'upload/images/' . $size_chart : "";
    $upload_image = 'upload/images/' . $image;
    $sql = "INSERT INTO products (size_chart,name,tax_id,slug,category_id,subcategory_id,image,other_images,description,shipping_delivery,indicator,manufacturer,made_in,return_status,cancelable_status, till_status,is_cod_allowed,status) VALUES('$upload_image1','$name','$tax_id','$slug','$category_id','$subcategory_id','$upload_image','$other_images','$description','$shipping_delivery','$indicator','$manufacturer','$made_in','$return_status','$cancelable_status','$till_status','$is_cod_allowed','$status')";
    $db->sql($sql);
    $product_result = $db->getResult();
    if (!empty($product_result)) {
        $product_result = 0;
    } else {
        $product_result = 1;
    }

    $sql = "SELECT id from products ORDER BY id DESC";
    $db->sql($sql);
    $res_inner = $db->getResult();
    if ($product_result == 1) {
        $product_id = $db->escapeString($res_inner[0]['id']);
        $type = $db->escapeString($fn->xss_clean($_POST['type']));

        $measurement = $db->escapeString($fn->xss_clean($_POST['measurement']));
        $measurement_unit_id = $db->escapeString($fn->xss_clean($_POST['measurement_unit_id']));
        $price = $db->escapeString($fn->xss_clean($_POST['price']));
        $discounted_price = ($_POST['discounted_price'] || !empty($_POST['discounted_price']) || $_POST['discounted_price'] != "") ? $db->escapeString($fn->xss_clean($_POST['discounted_price'])) : 0;
        $sku = ($_POST['sku'] || !empty($_POST['sku']) || $_POST['sku'] != "") ? $db->escapeString($fn->xss_clean($_POST['sku'])) :  "";
        $serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
        $stock1 = $db->escapeString($fn->xss_clean($_POST['stock']));
        $serve_for1 = $serve_for;
        $stock_unit_id1 = $db->escapeString($fn->xss_clean($_POST['stock_unit_id']));

        $measurement = explode(",", $measurement);
        $sku = explode(",", $sku);
        $measurement_unit_id = explode(",", $measurement_unit_id);
        $price = explode(",", $price);
        $discounted_price = explode(",", $discounted_price);
        $serve_for = explode(",", $serve_for1);
        $stock = explode(",", $stock1);
        $stock_unit_id = explode(",", $stock_unit_id1);

        if ($_POST['type'] == 'packet') {
            for ($i = 0; $i < count($measurement); $i++) {

                $variant_images = '';

                if ($_FILES["images"]["error"][$i][0] == 0) {
                    for ($j = 0; $j < count($_FILES["images"]["name"][$i]); $j++) {
                        if ($_FILES["images"]["error"][$i][$j] > 0) {
                            $error['images'] = " <span class='label label-danger'>Variant Images not uploaded!</span>";
                        } else {
                            $result = $fn->validate_other_images($_FILES["images"]["tmp_name"][$i][$j], $_FILES["images"]["type"][$i][$j]);
                            if ($result) {
                                $error['images'] = " <span class='label label-danger'>Variant Image type must jpg, jpeg, gif, or png!</span>";
                            }
                        }
                    }
                }

                if (isset($_FILES['images']) && (!empty($_FILES['images']['name'][$i][0])) && ($_FILES['images']['size'][$i][0] > 0)) {
                    $file_data = array();
                    $target_path1 = '../../upload/variant_images/';
                    $target_path = 'upload/variant_images/';
                    if (!is_dir($target_path1)) {
                        mkdir($target_path1, 0777, true);
                    }

                    for ($k = 0; $k < count($_FILES["images"]["name"][$i]); $k++) {
                        $filename = $_FILES["images"]["name"][$i][$k];
                        $temp = explode('.', $filename);
                        $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                        $file_data[] = $target_path . '' . $filename;
                        if (!move_uploaded_file($_FILES["images"]["tmp_name"][$i][$k], $target_path1  . '' . $filename))
                            echo "{$_FILES['images']['name'][$i][$k]} not uploaded<br/>";
                    }
                    $variant_images = json_encode($file_data);
                    $variant_images  = str_replace('"', "'", $variant_images);
                }

                $data = array(
                    'product_id' => $product_id,
                    'type' => $type,
                    'measurement' => $measurement[$i],
                    'measurement_unit_id' => $measurement_unit_id[$i],
                    'price' => $price[$i],
                    'discounted_price' => $discounted_price[$i],
                    'serve_for' => $serve_for[$i],
                    'stock' => $stock[$i],
                    'stock_unit_id' => $stock_unit_id[$i],
                    'sku' => $sku[$i],
                    'images' => $variant_images,
                );
                $db->insert('product_variant', $data);
            }
        } elseif ($_POST['type'] == "loose") {
            for ($i = 0; $i < count($measurement); $i++) {

                $variant_images = '';

                if ($_FILES["images"]["error"][$i][0] == 0) {
                    for ($j = 0; $j < count($_FILES["images"]["name"][$i]); $j++) {
                        if ($_FILES["images"]["error"][$i][$j] > 0) {
                            $error['images'] = " <span class='label label-danger'>Variant Images not uploaded!</span>";
                        } else {
                            $result = $fn->validate_other_images($_FILES["images"]["tmp_name"][$i][$j], $_FILES["images"]["type"][$i][$j]);
                            if ($result) {
                                $error['images'] = " <span class='label label-danger'>Variant Image type must jpg, jpeg, gif, or png!</span>";
                            }
                        }
                    }
                }

                if (isset($_FILES['images']) && (!empty($_FILES['images']['name'][$i][0])) && ($_FILES['images']['size'][$i][0] > 0)) {
                    $file_data = array();
                    $target_path1 = '../../upload/variant_images/';
                    $target_path = 'upload/variant_images/';
                    if (!is_dir($target_path1)) {
                        mkdir($target_path1, 0777, true);
                    }

                    for ($k = 0; $k < count($_FILES["images"]["name"][$i]); $k++) {
                        $filename = $_FILES["images"]["name"][$i][$k];
                        $temp = explode('.', $filename);
                        $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                        $file_data[] = $target_path . '' . $filename;
                        if (!move_uploaded_file($_FILES["images"]["tmp_name"][$i][$k], $target_path1  . '' . $filename))
                            echo "{$_FILES['images']['name'][$i][$k]} not uploaded<br/>";
                    }
                    $variant_images = json_encode($file_data);
                    $variant_images  = str_replace('"', "'", $variant_images);
                }

                $data = array(
                    'product_id' => $product_id,
                    'type' => $type,
                    'measurement' => $measurement[$i],
                    'measurement_unit_id' => $measurement_unit_id[$i],
                    'price' => $price[$i],
                    'discounted_price' => $discounted_price[$i],
                    'sku' => $sku[$i],
                    'serve_for' => $serve_for1,
                    'stock' => $stock1,
                    'stock_unit_id' => $stock_unit_id1,
                    'images' => $variant_images,
                );
                $db->insert('product_variant', $data);
            }
        }

        $sql = "SELECT * from products ORDER BY id DESC LIMIT 0,1";
        $db->sql($sql);
        $res_inner = $db->getResult();

        $product = array();
        $i = 0;
        $a = $fn->get_product_id_by_variant_id($res_inner[0]);

        $sql1 = "SELECT * from products WHERE id = $a";
        $db->sql($sql1);
        $res_inner1 = $db->getResult();
        foreach ($res_inner1 as $row) {
            $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ";
            $db->sql($sql);
            $variants = $db->getResult();

            $row['other_images'] = json_decode($row['other_images'], 1);
            $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
            $row['size_chart'] = (empty($row['size_chart'])) ? '' : DOMAIN_URL . $row['size_chart'];
            $row['image'] = (empty($row['image'])) ? '' : DOMAIN_URL . $row['image'];
            for ($j = 0; $j < count($row['other_images']); $j++) {
                $row['other_images'][$j] = !empty(DOMAIN_URL . $row['other_images'][$j]) ? DOMAIN_URL . $row['other_images'][$j] : "";
            }
            $row['shipping_delivery'] = (!empty($row['shipping_delivery'])) ? $row['shipping_delivery'] : "";


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

            for ($k = 0; $k < count($variants); $k++) {
                $variant_images = str_replace("'", '"', $variants[$k]['images']);
                $variants[$k]['images'] = json_decode($variant_images, 1);
                $variants[$k]['images'] = (empty($variants[$k]['images'])) ? array() : $variants[$k]['images'];

                for ($j = 0; $j < count($variants[$k]['images']); $j++) {
                    $variants[$k]['images'][$j] = !empty(DOMAIN_URL . $variants[$k]['images'][$j]) ? DOMAIN_URL . $variants[$k]['images'][$j] : "";
                }
                $variants[$k]['sku'] = (!empty($variants[$k]['sku'])) ? $variants[$k]['sku'] : "";
            }

            $product[$i] = $row;

            $product[$i]['variants'] = $variants;
            $i++;
        }
        if ($product_result == 1 && !empty($product)) {
            $response['error'] = false;
            $response['message'] = "Product Added Successfully!";
            $response['data'] = $product;
        } else {
            $response['error'] = true;
            $response['message'] = "Product Not Added!";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Product Added fail!";
    }
    print_r(json_encode($response));
    return false;
}

/* 
14.update_products
    accesskey:90336
    update_products:1
    id:507
    name:potato
    category_id:31
    subcategory_id:115
    description:potatos
    product_variant_id:510,209
    tax_id:4                    // {optional}
    manufacturer:india          // {optional}
    made_in:india               // {optional}
    return_status:0 / 1         // {optional}
    cancelable_status:0 / 1     // {optional}
    shipping_delivery:Potatos   // {optional} 
    size_chart:FILE             // {optional}     
    image:FILE                  // {optional} 
    other_images[]:FILE         // {optional} 
    is_cod_allowed: 0 / 1       // {optional}
    status: 0 / 1               // {optional}
    indicator: 1 - veg / 2 - non-veg          // {optional}
    till_status:received / processed / shipped           // {optional}

    type:packet
    measurement:500,100
    measurement_unit_id:4,2
    price:75,50
    serve_for:Available / Sold Out
    stock:992,987
    stock_unit_id:4,2
    sku:4,2                     // {optional}
    discounted_price:10,5       // {optional}
    images[0][] : FILE          // {optional}
    images[1][] : FILE          // {optional}

    type:loose
    measurement:1,1
    measurement_unit_id:1,5
    price:100,400
    serve_for:Available / Sold Out
    stock:997
    stock_unit_id:1
    sku:4,2                      // {optional}
    discounted_price:20,15       // {optional}
    images[0][] : FILE           // {optional}
    images[1][] : FILE           // {optional}
*/

if (isset($_POST['update_products']) && !empty($_POST['update_products']) && ($_POST['update_products'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['id']) || empty($_POST['name']) || $_POST['category_id'] == "" || $_POST['subcategory_id'] == "" || empty($_POST['serve_for']) || empty($_POST['description']) || $_POST['type'] == "") {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    if ($_POST['type']) {
        if (empty($_POST['measurement']) || empty($_POST['measurement_unit_id']) || empty($_POST['price']) || $_POST['discounted_price'] == "" || empty($_POST['serve_for']) || $_POST['stock'] == "" || empty($_POST['stock_unit_id'])) {
            $response['error'] = true;
            $response['message'] = "Please pass product variants fields!";
            print_r(json_encode($response));
            return false;
        }
    }

    $name = $db->escapeString($fn->xss_clean($_POST['name']));
    if (strpos($name, '-') !== false) {
        $temp = (explode("-", $name)[1]);
    } else {
        $temp = $name;
    }

    $slug = $function->slugify($temp);
    $id = $db->escapeString($fn->xss_clean($_POST['id']));
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

    $category_data = array();
    $product_status = "";
    $sql = "select id,name from category order by id asc";
    $db->sql($sql);
    $category_data = $db->getResult();
    $sql = "select * from subcategory";
    $db->sql($sql);
    $subcategory = $db->getResult();
    $sql = "SELECT image, other_images FROM products WHERE id =" . $id;
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row) {
        $previous_menu_image = $row['image'];
        $other_images = $row['other_images'];
    }

    $subcategory_id = (isset($_POST['subcategory_id']) && $_POST['subcategory_id'] != '') ? $db->escapeString($fn->xss_clean($_POST['subcategory_id'])) : 0;
    $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
    $serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
    $description = $db->escapeString($fn->xss_clean($_POST['description']));
    $manufacturer = (isset($_POST['manufacturer']) && $_POST['manufacturer'] != '') ? $db->escapeString($fn->xss_clean($_POST['manufacturer'])) : '';
    $made_in = (isset($_POST['made_in']) && $_POST['made_in'] != '') ? $db->escapeString($fn->xss_clean($_POST['made_in'])) : '';
    $indicator = (isset($_POST['indicator']) && $_POST['indicator'] != '') ? $db->escapeString($fn->xss_clean($_POST['indicator'])) : '0';
    $return_status = (isset($_POST['return_status']) && $_POST['return_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['return_status'])) : '0';
    $cancelable_status = (isset($_POST['cancelable_status']) && $_POST['cancelable_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['cancelable_status'])) : '0';
    $till_status = (isset($_POST['till_status']) && $_POST['till_status'] != '') ? $db->escapeString($fn->xss_clean($_POST['till_status'])) : '';
    $shipping_delivery = (isset($_POST['shipping_delivery']) && $_POST['shipping_delivery'] != '') ? $db->escapeString($fn->xss_clean($_POST['shipping_delivery'])) : '';
    $is_cod_allowed = (isset($_POST['is_cod_allowed']) && $_POST['is_cod_allowed'] != '') ? $db->escapeString($fn->xss_clean($_POST['is_cod_allowed'])) : '0';
    $status = (isset($_POST['status']) && $_POST['status'] != '') ? $db->escapeString($fn->xss_clean($_POST['status'])) : '1';

    $tax_id = (isset($_POST['tax_id']) && $_POST['tax_id'] != '') ? $db->escapeString($fn->xss_clean($_POST['tax_id'])) : 0;

    $error = array();

    $allowedExts = array("gif", "jpeg", "jpg", "png");

    error_reporting(E_ERROR | E_PARSE);
    if (!empty($_FILES['image'])) {
        $image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
        $image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
        $image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));

        $extension = pathinfo($_FILES["image"]["name"])['extension'];

        $result = $fn->validate_image($_FILES["image"]);
        if ($result) {
            $response['error'] = true;
            $response['message'] = "image type must jpg, jpeg, gif, or png!";
            print_r(json_encode($response));
            return false;
        }
    }

    if (isset($_FILES['other_images']) && ($_FILES['other_images']['size'][0] > 0)) {
        $file_data = array();
        $target_path = '../../upload/other_images/';
        $target_path1 = 'upload/other_images/';

        for ($i = 0; $i < count($_FILES["other_images"]["name"]); $i++) {
            if ($_FILES["other_images"]["error"][$i] > 0) {
                $response['error'] = true;
                $response['message'] = "Other Images not uploaded!";
                print_r(json_encode($response));
                return false;
            } else {
                $result = $fn->validate_other_images($_FILES["other_images"]["tmp_name"][$i], $_FILES["other_images"]["type"][$i]);

                if ($result) {
                    $response['error'] = true;
                    $response['message'] = "Other image type must jpg, jpeg, gif, or png!";
                    print_r(json_encode($response));
                    return false;
                }
            }
            $filename = $_FILES["other_images"]["name"][$i];
            $temp = explode('.', $filename);
            $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
            $file_data[] = 'upload/other_images/' . $filename;

            if (!move_uploaded_file($_FILES["other_images"]["tmp_name"][$i], $target_path . $filename)) {
                $response['error'] = true;
                $response['message'] = "Other Images not uploaded!";
                print_r(json_encode($response));
                return false;
            }
        }
        if (!empty($other_images)) {
            $arr_old_images = json_decode($other_images);
            $all_images = array_merge($arr_old_images, $file_data);
            $all_images = json_encode(array_values($all_images));
        } else {
            $all_images = $db->escapeString(json_encode($file_data));
        }

        if (empty($error)) {
            $sql = "update `products` set `other_images`='" . $all_images . "' where `id`=" . $id;
            $db->sql($sql);
        }
    }

    if (isset($_FILES['size_chart']) && !empty($_FILES['size_chart'])) {
        $sql = "SELECT size_chart FROM products WHERE id =" . $id;
        $db->sql($sql);
        $res = $db->getResult();
        if (!empty($res[0]['size_chart'])) {
            if (!empty($res[0]['size_chart'])) {
                unlink('../../' . $res[0]['size_chart']);
            }
        }
        $target_path1 = '../../upload/images/';
        if ($_FILES['size_chart']['error'] == 0) {
            if (!is_dir($target_path1)) {
                mkdir($target_path1, 0777, true);
            }
            $extension1 = pathinfo($_FILES["size_chart"]["name"])['extension'];

            $result1 = $fn->validate_image($_FILES["size_chart"]);
            if ($result1) {
                $response['error'] = true;
                $response['message'] = "Size chart image type must jpg, jpeg, gif, or png!";
                print_r(json_encode($response));
                return false;
            }

            $string1 = '0123456789';
            $file1 = preg_replace("/\s+/", "_", $_FILES['size_chart']['name']);
            $size_chart = $function->get_random_string($string1, 4) . "-" . date("Y-m-d") . "." . $extension1;

            $upload1 = move_uploaded_file($_FILES['size_chart']['tmp_name'], '../../upload/images/' . $size_chart);
            $upload_image1 =  'upload/images/' . $size_chart;
            $sql_query = "UPDATE products SET  size_chart = '$upload_image1' WHERE id = $id";
            $db->sql($sql_query);
        }
    }

    if (strpos($name, "'") !== false) {
        $name = str_replace("'", "''", "$name");
        if (strpos($description, "'") !== false)
            $description = str_replace("'", "''", "$description");
    }
    if (!empty($_FILES['image'])) {
        $string = '0123456789';
        $file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
        $function = new functions;
        $image = $function->get_random_string($string, 4) . "-" . date("Y-m-d") . "." . $extension;
        $delete = unlink('../../' . "$previous_menu_image");
        $upload = move_uploaded_file($_FILES['image']['tmp_name'], '../../upload/images/' . $image);

        $upload_image = 'upload/images/' . $image;
        $sql_query = "UPDATE products SET name = '$name' ,tax_id = '$tax_id' ,slug = '$slug' , subcategory_id = '$subcategory_id', image = '$upload_image', description = '$description', indicator = '$indicator', manufacturer = '$manufacturer', made_in = '$made_in', return_status = '$return_status', cancelable_status = '$cancelable_status', till_status = '$till_status',shipping_delivery = '$shipping_delivery',is_cod_allowed = '$is_cod_allowed',status = '$status' WHERE id = $id";
        $db->sql($sql_query);
    } else {
        $sql_query = "UPDATE products SET name = '$name' ,tax_id = '$tax_id' ,slug = '$slug' ,category_id = '$category_id' ,subcategory_id = '$subcategory_id' ,description = '$description', indicator = '$indicator', manufacturer = '$manufacturer', made_in = '$made_in', return_status = '$return_status', cancelable_status = '$cancelable_status', till_status = '$till_status',shipping_delivery = '$shipping_delivery' ,is_cod_allowed = '$is_cod_allowed',status = '$status' WHERE id = $id";
        $db->sql($sql_query);
    }
    $res = $db->getResult();

    $type = $db->escapeString($fn->xss_clean($_POST['type']));
    $product_variant_id = $db->escapeString($fn->xss_clean($_POST['product_variant_id']));
    $product_variant_id = explode(",", $product_variant_id);

    $measurement = $db->escapeString($fn->xss_clean($_POST['measurement']));
    $measurement_unit_id = $db->escapeString($fn->xss_clean($_POST['measurement_unit_id']));
    $price = $db->escapeString($fn->xss_clean($_POST['price']));
    $discounted_price = ($_POST['discounted_price'] || !empty($_POST['discounted_price']) || $_POST['discounted_price'] != "") ? $db->escapeString($fn->xss_clean($_POST['discounted_price'])) : 0;
    $sku = ($_POST['sku'] || !empty($_POST['sku']) || $_POST['sku'] != "") ? $db->escapeString($fn->xss_clean($_POST['sku'])) : "";
    $serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
    $serve_for1 = $serve_for;
    $stock1 = $db->escapeString($fn->xss_clean($_POST['stock']));
    $stock_unit_id1 = $db->escapeString($fn->xss_clean($_POST['stock_unit_id']));

    $sku = explode(",", $sku);
    $measurement = explode(",", $measurement);
    $measurement_unit_id = explode(",", $measurement_unit_id);
    $price = explode(",", $price);
    $discounted_price = explode(",", $discounted_price);
    $serve_for = explode(",", $serve_for1);
    $stock = explode(",", $stock1);
    $stock_unit_id = explode(",", $stock_unit_id1);

    for ($i = 0; $i < count($measurement); $i++) {
        $previous_variant_image = '';
        $vari_image = $fn->get_data($columns = ['id', 'images'], 'id=' . $product_variant_id[$i], 'product_variant');
        $previous_variant_image = $vari_image[0]['images'];

        if ($_POST['type'] == "packet") {
            /*updating variant images if any*/
            $file_data = array();
            $all_images = array();
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][$i][0]) && ($_FILES['images']['size'][$i][0] > 0)) {

                $target_path1 = '../../upload/variant_images/';
                if (!is_dir($target_path1)) {
                    mkdir($target_path1, 0777, true);
                }
                for ($k = 0; $k < count($_FILES["images"]["name"][$i]); $k++) {
                    if ($_FILES["images"]["error"][$i][$k] > 0) {
                        $error['images'] = " <span class='label label-danger'>Images not uploaded!</span>";
                    } else {
                        $result = $fn->validate_other_images($_FILES["images"]["tmp_name"][$i][$k], $_FILES["images"]["type"][$i][$k]);
                        if ($result) {
                            $error['images'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                        }
                    }
                    $filename = $_FILES["images"]["name"][$i][$k];
                    $temp = explode('.', $filename);
                    $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                    $file_data[] = 'upload/variant_images/' . '' . $filename;

                    if (!move_uploaded_file($_FILES["images"]["tmp_name"][$i][$k], $target_path1 . '' . $filename))
                        echo "{$_FILES['images']['name'][$i][$k]} not uploaded<br/>";
                }
            }

            if (isset($previous_variant_image) && !empty($previous_variant_image && $product_variant_id[$i] != 0)) {
                $variant_images = str_replace("'", '"', $previous_variant_image);
                $arr_old_images = json_decode($variant_images);
                $all_images = !empty($file_data) ? array_merge($arr_old_images, $file_data) : $arr_old_images;
                $all_images = json_encode(array_values($all_images));
                $all_images = str_replace('"', "'", $all_images);
            } else {
                $all_images = $db->escapeString(json_encode($file_data));
                $all_images = str_replace('"', "'", $all_images);
            }

            $data = array(
                'type' => $type,
                'id' => $product_variant_id[$i],
                'measurement' => $measurement[$i],
                'measurement_unit_id' => $measurement_unit_id[$i],
                'price' => $price[$i],
                'discounted_price' => $discounted_price[$i],
                'stock' => $stock[$i],
                'stock_unit_id' => $stock_unit_id[$i],
                'serve_for' => $serve_for[$i],
                'sku' => $sku[$i],
                'images' => $all_images,
            );


            if ($data['id'] == 0) {
                $data['product_id'] = $id;
                $db->insert('product_variant', $data);
            } else {
                $db->update('product_variant', $data, 'id=' . $data['id']);
            }
            $res = $db->getResult();
        } else if ($_POST['type'] == "loose") {

            /*updating variant images if any*/
            $file_data = array();
            $all_images = array();
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][$i][0]) && ($_FILES['images']['size'][$i][0] > 0)) {

                $target_path1 = '../../upload/variant_images/';
                if (!is_dir($target_path1)) {
                    mkdir($target_path1, 0777, true);
                }
                for ($k = 0; $k < count($_FILES["images"]["name"][$i]); $k++) {
                    if ($_FILES["images"]["error"][$i][$k] > 0) {
                        $error['images'] = " <span class='label label-danger'>Images not uploaded!</span>";
                    } else {
                        $result = $fn->validate_other_images($_FILES["images"]["tmp_name"][$i][$k], $_FILES["images"]["type"][$i][$k]);
                        if ($result) {
                            $error['images'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                        }
                    }
                    $filename = $_FILES["images"]["name"][$i][$k];
                    $temp = explode('.', $filename);
                    $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                    $file_data[] = 'upload/variant_images/' . '' . $filename;

                    if (!move_uploaded_file($_FILES["images"]["tmp_name"][$i][$k], $target_path1 . '' . $filename))
                        echo "{$_FILES['images']['name'][$i][$k]} not uploaded<br/>";
                }
            }

            if (isset($previous_variant_image) && !empty($previous_variant_image && $product_variant_id[$i] != 0)) {
                $variant_images = str_replace("'", '"', $previous_variant_image);
                $arr_old_images = json_decode($variant_images);
                $all_images = !empty($file_data) ? array_merge($arr_old_images, $file_data) : $arr_old_images;
                $all_images = json_encode(array_values($all_images));
                $all_images = str_replace('"', "'", $all_images);
            } else {
                $all_images = $db->escapeString(json_encode($file_data));
                $all_images = str_replace('"', "'", $all_images);
            }

            $data = array(
                'type' => $type,
                'id' => $product_variant_id[$i],
                'measurement' => $measurement[$i],
                'measurement_unit_id' => $measurement_unit_id[$i],
                'price' => $price[$i],
                'discounted_price' => $discounted_price[$i],
                'sku' => $sku[$i],
                'stock' => $stock1,
                'stock_unit_id' => $stock_unit_id1,
                'serve_for' => $serve_for1,
                'images' => $all_images,
            );
            if ($data['id'] == 0) {
                $data['product_id'] = $id;
                $db->insert('product_variant', $data);
            } else {
                $db->update('product_variant', $data, 'id=' . $data['id']);
            }
            $res = $db->getResult();
        }
    }
    $sql1 = "SELECT * from products WHERE id = $id";
    $db->sql($sql1);
    $res_inner1 = $db->getResult();
    $product = array();
    $i = 0;
    foreach ($res_inner1 as $row) {
        $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ";
        $db->sql($sql);
        $variants = $db->getResult();

        $row['other_images'] = json_decode($row['other_images'], 1);
        $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
        $row['size_chart'] = (empty($row['size_chart'])) ? '' : DOMAIN_URL . $row['size_chart'];
        $row['image'] = (empty($row['image'])) ? '' : DOMAIN_URL . $row['image'];
        for ($j = 0; $j < count($row['other_images']); $j++) {
            $row['other_images'][$j] = !empty(DOMAIN_URL . $row['other_images'][$j]) ? DOMAIN_URL . $row['other_images'][$j] : "";
        }
        $row['shipping_delivery'] = (!empty($row['shipping_delivery'])) ? $row['shipping_delivery'] : "";
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
        for ($k = 0; $k < count($variants); $k++) {
            $variant_images = str_replace("'", '"', $variants[$k]['images']);
            $variants[$k]['images'] = json_decode($variant_images, 1);
            $variants[$k]['images'] = (empty($variants[$k]['images'])) ? array() : $variants[$k]['images'];

            for ($j = 0; $j < count($variants[$k]['images']); $j++) {
                $variants[$k]['images'][$j] = !empty(DOMAIN_URL . $variants[$k]['images'][$j]) ? DOMAIN_URL . $variants[$k]['images'][$j] : "";
            }

            $variants[$k]['sku'] = (!empty($variants[$k]['sku'])) ? $variants[$k]['sku'] : "";
        }

        $product[$i] = $row;

        $product[$i]['variants'] = $variants;
        $i++;
    }
    if (!empty($product)) {
        $response['error'] = false;
        $response['message'] = "Product Updated Successfully!";
        $response['data'] = $product;
    } else {
        $response['error'] = true;
        $response['message'] = "Product Not Added!";
    }
    print_r(json_encode($response));
    return false;
}

/* 
15.delete_products
    accesskey:90336
    delete_products:1
    product_id:722
*/

if (isset($_POST['delete_products']) && !empty($_POST['delete_products']) && ($_POST['delete_products'] == 1)) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['product_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass product id fields!";
        print_r(json_encode($response));
        return false;
    }
    $product_id = (isset($_POST['product_id'])) ? $db->escapeString($fn->xss_clean($_POST['product_id'])) : "";


    $sql = "SELECT images FROM product_variant WHERE product_id =" . $product_id;
    $db->sql($sql);
    $res = $db->getResult();
    for ($i = 0; $i < count($res); $i++) {
        if (isset($res[$i]['images']) && !empty($res[$i]['images'])) {
            $other_images = $res[$i]['images']; /*get images json array*/
            $variant_images = str_replace("'", '"', $other_images);
            $other_images = json_decode($variant_images); /*decode from json to array*/

            foreach ($other_images as $other_image) {
                unlink('../../' . $other_image);
            }
        }
    }
    $sql_query = "DELETE FROM cart WHERE product_id = $product_id ";
    $db->sql($sql_query);

    $sql_query = "DELETE FROM product_variant WHERE product_id=" . $product_id;
    $db->sql($sql_query);

    $sql = "SELECT count(id) as total from product_variant WHERE product_id=" . $product_id;
    $db->sql($sql);
    $total = $db->getResult();

    if ($total[0]['total'] == 0) {
        $sql_query = "SELECT image FROM products WHERE id =" . $product_id;
        $db->sql($sql_query);
        $res = $db->getResult();
        unlink('../../' . $res[0]['image']);

        $sql_query1 = "SELECT size_chart FROM products WHERE id =" . $product_id;
        $db->sql($sql_query1);
        $res1 = $db->getResult();
        unlink('../../' . $res1[0]['size_chart']);

        $sql_query = "SELECT other_images FROM products WHERE id =" . $product_id;
        $db->sql($sql_query);
        $res = $db->getResult();
        if (!empty($res[0]['other_images'])) {
            $other_images = json_decode($res[0]['other_images']);
            foreach ($other_images as $other_image) {
                unlink('../../' . $other_image);
            }
        }

        $sql_query = "DELETE FROM products WHERE id =" . $product_id;
        $db->sql($sql_query);

        $sql_query = "DELETE FROM favorites WHERE product_id = " . $product_id;
        $db->sql($sql_query);
    }
    $response['error'] = false;
    $response['message'] = "product delete successfully!";
    print_r(json_encode($response));
    return false;
}


/* 
16.get_products
    accesskey:90336
    get_products:1
    id:468              // {optional}
    category_id:30     // {optional}
    subcategory_id:119  // {optional}
    limit:10            // {optional}
    offset:0            // {optional}
    search:value        // {optional}
    filter:low_stock | out_stock    // {optional}
    sort:new / old / high / low     // {optional}
*/
if (isset($_POST['get_products']) && !empty($_POST['get_products']) && ($_POST['get_products'] == 1)) {
    $where = "";
    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : 'id';
    $filter = (isset($_POST['filter']) && !empty($_POST['filter'])) ? $db->escapeString($fn->xss_clean($_POST['filter'])) : '';
    $subcategory_id = (isset($_POST['category_id']) && is_numeric($_POST['category_id'])) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : "";
    $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;

    if ($sort == 'new') {
        $sort = 'ORDER BY date_added DESC';
        $price = 'MIN(pv.price)';
        $price_sort = 'ORDER BY pv.price ASC';
    } elseif ($sort == 'old') {
        $sort = 'ORDER BY date_added ASC';
        $price = 'MIN(pv.price)';
        $price_sort = 'ORDER BY pv.price ASC';
    } elseif ($sort == 'high') {
        $sort = 'ORDER BY price DESC';
        $price = 'MAX(pv.price)';
        $price_sort = 'ORDER BY pv.price DESC';
    } elseif ($sort == 'low') {
        $sort = 'ORDER BY price ASC';
        $price = 'MIN(pv.price)';
        $price_sort = 'ORDER BY pv.price ASC';
    } else {
        $sort = 'ORDER BY p.id DESC';
        $price = 'MIN(pv.price)';
        $price_sort = 'ORDER BY pv.price ASC';
    }
    if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
        $where .= ' and category_id = ' . $category_id;
    }
    if (isset($_POST['category_id']) && !empty($_POST['category_id']) && isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id'])) {
        $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
        $subcategory_id = $db->escapeString($fn->xss_clean($_POST['subcategory_id']));
        $where .= " and category_id = $category_id and subcategory_id = $subcategory_id";
    }
    if (isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id'])) {
        $subcategory_id = $db->escapeString($fn->xss_clean($_POST['subcategory_id']));
        $where .= ' and subcategory_id = ' . $subcategory_id;
    }
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $db->escapeString($fn->xss_clean($_POST['id']));
        $where .= ' and p.id = ' . $id;
    }
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        $where .= " and (p.`id` like '%" . $search . "%' OR p.`name` like '%" . $search . "%' OR p.`slug` like '%" . $search . "%' OR `category_id` like '%" . $search . "%' OR p.`subcategory_id` like '%" . $search . "%' OR p.`manufacturer` like '%" . $search . "%' OR p.`made_in` like '%" . $search . "%' OR p.`return_status` like '%" . $search . "%' OR p.`description` like '%" . $search . "%')";
    }
    $join = "";
    if ($filter == "out_stock") {
        $join = " left join product_variant pv ON pv.product_id=p.id ";
        $where .= " AND pv.serve_for = 'Sold Out'";
    }
    if ($filter == "low_stock") {
        $join = " left join product_variant pv ON pv.product_id=p.id ";
        $where .=  " AND pv.stock < $low_stock_limit AND pv.serve_for = 'Available'";
    }

    $sql = "SELECT count(p.id) as total FROM products p $join WHERE p.`status`=1 $where ";
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row) {
        $total = $row['total'];
    }
    $sql = "SELECT p.* FROM products p $join WHERE p.`status`=1 $where GROUP BY p.id $sort LIMIT $offset, $limit";
    $db->sql($sql);
    $res = $db->getResult();
    $product = array();

    $i = 0;
    $sql = "SELECT id FROM cart limit 1";
    $db->sql($sql);
    $res_cart = $db->getResult();
    foreach ($res as $row) {
        $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ";
        $db->sql($sql);
        $variants = $db->getResult();

        $row['other_images'] = json_decode($row['other_images'], 1);
        $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];
        $row['size_chart'] = (empty($row['size_chart'])) ? '' : DOMAIN_URL . $row['size_chart'];
        $row['image'] = (empty($row['image'])) ? '' : DOMAIN_URL . $row['image'];
        for ($j = 0; $j < count($row['other_images']); $j++) {
            $row['other_images'][$j] = !empty(DOMAIN_URL . $row['other_images'][$j]) ? DOMAIN_URL . $row['other_images'][$j] : "";
        }
        $row['shipping_delivery'] = (!empty($row['shipping_delivery'])) ? $row['shipping_delivery'] : "";
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

        $product[$i] = $row;

        for ($k = 0; $k < count($variants); $k++) {
            $variant_images = str_replace("'", '"', $variants[$k]['images']);
            $variants[$k]['images'] = json_decode($variant_images, 1);
            $variants[$k]['images'] = (empty($variants[$k]['images'])) ? array() : $variants[$k]['images'];

            for ($j = 0; $j < count($variants[$k]['images']); $j++) {
                $variants[$k]['images'][$j] = !empty(DOMAIN_URL . $variants[$k]['images'][$j]) ? DOMAIN_URL . $variants[$k]['images'][$j] : "";
            }
            $variants[$k]['sku'] = (!empty($variants[$k]['sku'])) ? $variants[$k]['sku'] : "";

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
        }

        $product[$i]['variants'] = $variants;
        $i++;
    }
    if (!empty($product)) {
        $response['error'] = false;
        $response['message'] = "products fetched successfully.";
        $response['total'] = $total;
        $response['data'] = $product;
    } else {
        $response['error'] = true;
        $response['message'] = "products not fetched.";
    }
    print_r(json_encode($response));
}

/* 
17.send_notification
    accesskey:90336
    send_notification:1  
    title:test
    message:testing
    type:default / category / product
    type_id:32
    image:FILE          // {optional}
*/

if (isset($_POST['send_notification']) && !empty($_POST['send_notification']) && ($_POST['send_notification'] == 1)) {
    if (empty($_POST['title']) || empty($_POST['message']) || empty($_POST['type'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }
    $title = $db->escapeString($fn->xss_clean($_POST['title']));
    $message = $db->escapeString($fn->xss_clean($_POST['message']));
    $type = $db->escapeString($fn->xss_clean($_POST['type']));
    $id = ($type != 'default') ? $db->escapeString($fn->xss_clean($_POST['type_id'])) : "0";

    $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
    $url .= $_SERVER['SERVER_NAME'];
    $url .= $_SERVER['REQUEST_URI'];
    $server_url = dirname($url) . '/';

    $push = null;
    $include_image = (isset($_FILES['image']));
    if ($include_image) {
        $extension = pathinfo($_FILES["image"]["name"])['extension'];
        $result = $fn->validate_image($_FILES["image"]);
        if ($result) {
            $response['error'] = true;
            $response['message'] = 'Image type must jpg, jpeg, gif, or png!';
            echo json_encode($response);
            return false;
        }
        $target_path = 'upload/notifications/';
        $filename = microtime(true) . '.' . strtolower($extension);
        $full_path = $target_path . "" . $filename;
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], '../../upload/notifications/' . "" . $filename)) {
            $response['error'] = true;
            $response['message'] = 'Image is not uploaded';
            echo json_encode($response);
            return false;
        }
        $sql = "INSERT INTO `notifications`(`title`, `message`,  `type`, `type_id`, `image`) VALUES 
			('" . $title . "','" . $message . "','" . $type . "','" . $id . "','" . $full_path . "')";
    } else {
        $sql = "INSERT INTO `notifications`(`title`, `message`, `type`, `type_id`) VALUES 
        ('" . $title . "','" . $message . "','" . $type . "','" . $id . "')";
    }

    if ($db->sql($sql)) {
        $sql = "SELECT * FROM notifications ORDER BY id DESC LIMIT 0,1 ";
        $db->sql($sql);
        $res = $db->getResult();
        $res[0]['image'] = !empty($res[0]['image']) ? DOMAIN_URL . $res[0]['image'] : "";

        $response['error'] = false;
        $response["message"] = "Notification Sent Successfully!";
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }

    if ($include_image) {
        $push = new Push(
            $db->escapeString($fn->xss_clean($_POST['title'])),
            $db->escapeString($fn->xss_clean($_POST['message'])),
            $server_url . '' . $full_path,
            $type,
            $id
        );
    } else {
        $push = new Push(
            $db->escapeString($fn->xss_clean($_POST['title'])),
            $db->escapeString($fn->xss_clean($_POST['message'])),
            null,
            $type,
            $id
        );
    }
    $mPushNotification = $push->getPush();

    $devicetoken = $function->getAllTokens();
    $devicetoken1 = $function->getAllTokens("devices");
    $final_tokens = array_merge($devicetoken, $devicetoken1);
    $f_tokens = array_unique($final_tokens);
    $devicetoken_chunks = array_chunk($f_tokens, 1000);

    foreach ($devicetoken_chunks as $devicetokens) {
        $firebase = new Firebase();
        $firebase->send($devicetokens, $mPushNotification);
    }
    print_r(json_encode($response));
}

/* 
18.delete_notification
    accesskey:90336
    delete_notification:1    
    id:915
*/

if (isset($_POST['delete_notification']) && !empty($_POST['delete_notification']) && ($_POST['delete_notification'] == 1)) {
    if (empty($_POST['id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass id fields!";
        print_r(json_encode($response));
        return false;
    }

    $id = $db->escapeString($fn->xss_clean($_POST['id']));

    $sql_query = "SELECT image FROM notifications WHERE id =" . $id;
    $db->sql($sql_query);
    $res = $db->getResult();

    if ($res[0]['image']) {
        unlink('../../' . $res[0]['image']);
    }

    $sql_query = "DELETE FROM notifications WHERE id=" . $id;

    if ($db->sql($sql_query)) {
        $response['error'] = false;
        $response['message'] = "Notification delete successfully!";
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
    return false;
}

/* 
19.get_notification
    accesskey:90336
    get_notification:1  
    offset:0    // {optional}
    limit:10    // {optional}
    sort:id     // {optional}
    order:asc/desc      // {optional}
*/

if (isset($_POST['get_notification']) && !empty($_POST['get_notification']) && ($_POST['get_notification'] == 1)) {
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    $sql = "SELECT count(id) as total FROM notifications";
    $db->sql($sql);
    $res = $db->getResult();
    foreach ($res as $row) {
        $total = $row['total'];
    }
    $sql = "SELECT * FROM notifications ORDER BY $sort $order LIMIT $offset, $limit";
    $db->sql($sql);
    $row = $db->getResult();
    foreach ($row as $res) {
        $temp['id'] = $res['id'];
        $temp['title'] = $res['title'];
        $temp['message'] = $res['message'];
        $temp['type'] = $res['type'];
        $temp['type_id'] = $res['type_id'];
        $temp['date_sent'] = $res['date_sent'];
        $temp['image'] = !empty($res['image']) ? DOMAIN_URL . $res['image'] : "";
        $result[] = $temp;
    }

    if (!empty($result)) {
        $response['error'] = false;
        $response['total'] = $total;
        $response['message'] = "Notification fetched successfully.";
        $response['data'] = $result;
    } else {
        $response['error'] = true;
        $response['message'] = "Notification not fetched.";
    }
    print_r(json_encode($response));
    return false;
}

/* 
20.get_orders
    accesskey:90336
    get_orders:1
    order_id:1008625        // {optional}
    pickup:0 / 1            // {optional}
    search:value            // {optional}
    offset:0                // {optional}
    limit:10                // {optional}
    sort:id                 // {optional}
    order:ASC/DESC          // {optional}
    start_date:2020-10-29   // {optional} {YYYY-mm-dd}
    end_date:2020-10-29     // {optional} {YYYY-mm-dd}
    filter_order:received | processed | shipped | delivered | cancelled | returned | awaiting_payment // {optional}
*/
if (isset($_POST['get_orders']) && !empty($_POST['get_orders'])) {

    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    if (isset($_POST['pickup'])) {
        $where = $_POST['pickup'] == 1 ? " WHERE o.local_pickup = 1 " :  " WHERE o.local_pickup = 0 ";
    }

    if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
        $start_date = $db->escapeString($fn->xss_clean($_POST['start_date']));
        $end_date = $db->escapeString($fn->xss_clean($_POST['end_date']));
        $where .= empty($where) ? " WHERE DATE(date_added)>=DATE('" . $start_date . "') AND DATE(date_added)<=DATE('" . $end_date . "')" : " AND DATE(date_added)>=DATE('" . $start_date . "') AND DATE(date_added)<=DATE('" . $end_date . "')";
    }
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if (!empty($where)) {
            $where .= " AND (o.id like '%" . $search . "%' OR o.user_id like '%" . $search . "%' OR o.address like '%" . $search . "%' OR o.`payment_method` like '%" . $search . "%' OR o.`active_status` like '%" . $search . "%' OR o.`mobile` like '%" . $search . "%')";
        } else {
            $where .= " where (o.id like '%" . $search . "%' OR o.user_id like '%" . $search . "%' OR o.address like '%" . $search . "%' OR o.`payment_method` like '%" . $search . "%' OR o.`active_status` like '%" . $search . "%' OR o.`mobile` like '%" . $search . "%')";
        }
    }
    if (isset($_POST['filter_order']) && $_POST['filter_order'] != '') {
        $filter_order = $db->escapeString($fn->xss_clean($_POST['filter_order']));
        $where .= !empty($where) ? " AND `active_status`='" . $filter_order . "'" : " WHERE `active_status`='" . $filter_order . "'";
    }
    if (isset($_POST['order_id']) && !empty($_POST['order_id']) && is_numeric($_POST['order_id'])) {
        $order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
        $where .= !empty($where) ? " AND o.`id` = " . $order_id : " WHERE o.`id` = " . $order_id;
    }

    $sql = "select count(o.id) as total from orders o " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    $total = $res[0]['total'];

    $sql = "select o.*,obt.attachment,count(obt.attachment) as total_attachment ,obt.message as bank_transfer_message,obt.status as bank_transfer_status,(select name from users u where u.id=o.user_id) as user_name,u.name as name,u.country_code as country_code from orders o LEFT JOIN order_bank_transfers obt
    ON obt.order_id=o.id LEFT JOIN users u ON u.id=o.user_id" . $where . " GROUP BY id ORDER BY date_added DESC LIMIT $offset,$limit";
    $db->sql($sql);
    $res = $db->getResult();
    $i = 0;
    $j = 0;

    foreach ($res as $row) {
        if ($row['discount'] > 0) {
            $discounted_amount = $row['total'] * $row['discount'] / 100;
            $final_total = $row['total'] - $discounted_amount;
            $discount_in_rupees = $row['total'] - $final_total;
        } else {
            $discount_in_rupees = 0;
        }
        $res[$i]['discount_rupees'] = "$discount_in_rupees";

        $sql_query = "SELECT id,attachment FROM order_bank_transfers WHERE order_id = " . $row['id'];
        $db->sql($sql_query);
        $res_attac = $db->getResult();

        $myData = array();
        foreach ($res_attac as $item) {
            array_push($myData, ['id' => $item['id'], 'image' => DOMAIN_URL . $item['attachment']]);
        }
        $body1 = json_encode($myData);
        $body = json_decode($body1);

        $final_totals = $res[$i]['total'] + $res[$i]['delivery_charge']  - $res[$i]['discount_rupees'] - $res[$i]['promo_discount'] - $res[$i]['wallet_balance'];
        $final_total =  ceil($final_totals);

        $res[$i]['attachment'] = $body;
        $res[$i]['tax'] = $res[$i]['tax_amount'] . '(' . $res[$i]['tax_percentage'] . '%)';
        $res[$i]['qty'] = (isset($res[$i]['items'][0]['quantity']) && !empty($res[$i]['items'][0]['quantity'])) ? $res[$i]['items'][0]['quantity'] : "0";
        $res[$i]['deliver_by'] = $res[$i]['delivery_boy_id'];
        $res[$i]['user_name'] = !empty($res[$i]['user_name']) ? $res[$i]['user_name'] : "";
        $res[$i]['delivery_boy_id'] = !empty($res[$i]['delivery_boy_id']) ? $res[$i]['delivery_boy_id'] : "";
        $res[$i]['otp'] = !empty($res[$i]['otp']) ? $res[$i]['otp'] : "";
        $res[$i]['order_note'] = !empty($res[$i]['order_note']) ? $res[$i]['order_note'] : "";
        $res[$i]['bank_transfer_message'] = !empty($res[$i]['bank_transfer_message']) ? $res[$i]['bank_transfer_message'] : "";
        $res[$i]['bank_transfer_status'] = !empty($res[$i]['bank_transfer_status']) ? $res[$i]['bank_transfer_status'] : "0";
        $res[$i]['final_total'] = "$final_total";
        $res[$i]['date_added'] = date('d-m-Y h:i:sa', strtotime($res[$i]['date_added']));
        $res[$i]['seller_notes'] = !empty($res[$i]['seller_notes']) ? $res[$i]['seller_notes'] : "";
        $res[$i]['pickup_time'] = !empty($res[$i]['pickup_time']) ? $res[$i]['pickup_time'] : "";

        if ($res[$i]['delivery_boy_id'] != 0 && $res[$i]['delivery_boy_id'] != "") {
            $d_name = $fn->get_data($columns = ['name'], 'id=' . $res[$i]['delivery_boy_id'], 'delivery_boys');
            $res[$i]['deliver_boy_name'] = (!empty($d_name[0]['name']) && $d_name[0]['name'] != null) ? $d_name[0]['name'] : "";
        } else {
            $res[$i]['deliver_boy_name'] = "";
        }
        $res[$i]['status'] = json_decode($res[$i]['status']);
        if (in_array('awaiting_payment', array_column($res[$i]['status'], '0'))) {
            $temp_array = array_column($res[$i]['status'], '0');
            $index = array_search("awaiting_payment", $temp_array);
            unset($res[$i]['status'][$index]);
            $res[$i]['status'] = array_values($res[$i]['status']);
        }
        $status = $res[$i]['status'];

        $sql = "select oi.*,p.id as product_id,v.id as variant_id, pr.rate,pr.review,pr.status as review_status,p.name,p.image,p.manufacturer,p.made_in,p.return_status,p.cancelable_status,p.till_status,v.measurement,(select short_code from unit u where u.id=v.measurement_unit_id) as unit from order_items oi left join product_variant v on oi.product_variant_id=v.id left join products p on p.id=v.product_id left join product_reviews pr on p.id=pr.product_id where order_id=" . $row['id'] . " GROUP BY oi.id";
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
        for ($j = 0; $j < count($res[$i]['items']); $j++) {
            $res[$i]['items'][$j]['status'] = (!empty($res[$i]['items'][$j]['status'])) ? json_decode($res[$i]['items'][$j]['status']) : array();

            if (in_array('awaiting_payment', array_column($res[$i]['items'][$j]['status'], '0'))) {
                $temp_array = array_column($res[$i]['items'][$j]['status'], '0');
                $index = array_search("awaiting_payment", $temp_array);
                unset($res[$i]['items'][$j]['status'][$index]);
                $res[$i]['items'][$j]['status'] = array_values($res[$i]['items'][$j]['status']);
            }

            $res[$i]['items'][$j]['product_image'] = DOMAIN_URL . $res[$i]['items'][$j]['image'];
            $res[$i]['items'][$j]['subtotal'] = $res[$i]['items'][$j]['quantity'] * $res[$i]['items'][$j]['price'];
            $res[$i]['items'][$j]['deliver_by'] = !empty($res[$i]['items'][$j]['deliver_by']) ? $res[$i]['items'][$j]['deliver_by'] : "";
            $res[$i]['items'][$j]['rate'] = !empty($res[$i]['items'][$j]['rate']) ? $res[$i]['items'][$j]['rate'] : "";
            $res[$i]['items'][$j]['review'] = !empty($res[$i]['items'][$j]['review']) ? $res[$i]['items'][$j]['review'] : "";
            $res[$i]['items'][$j]['manufacturer'] = !empty($res[$i]['items'][$j]['manufacturer']) ? $res[$i]['items'][$j]['manufacturer'] : "";
            $res[$i]['items'][$j]['made_in'] = !empty($res[$i]['items'][$j]['made_in']) ? $res[$i]['items'][$j]['made_in'] : "";
            $res[$i]['items'][$j]['return_status'] = !empty($res[$i]['items'][$j]['return_status']) ? $res[$i]['items'][$j]['return_status'] : "";
            $res[$i]['items'][$j]['cancelable_status'] = !empty($res[$i]['items'][$j]['cancelable_status']) ? $res[$i]['items'][$j]['cancelable_status'] : "";
            $res[$i]['items'][$j]['till_status'] = !empty($res[$i]['items'][$j]['till_status']) ? $res[$i]['items'][$j]['till_status'] : "";
            $res[$i]['items'][$j]['review_status'] = (!empty($res[$i]['items'][$j]['review_status']) && ($res[$i]['items'][$j]['review_status'] == 1)) ? $res[$i]['items'][$j]['review_status'] == TRUE : FALSE;
            $sql = "SELECT id from return_requests where product_variant_id = " . $res[$i]['items'][$j]['variant_id'] . " AND user_id = " . $row['user_id'];
            $db->sql($sql);
            $return_request = $db->getResult();
            if (empty($return_request)) {
                $res[$i]['items'][$j]['applied_for_return'] = false;
            } else {
                $res[$i]['items'][$j]['applied_for_return'] = true;
            }
        }
        $i++;
    }
    $orders = $order = array();

    if (!empty($res)) {
        $orders['error'] = false;
        $orders['total'] = $total;
        $orders['data'] = array_values($res);
        print_r(json_encode($orders));
    } else {
        $res['error'] = true;
        $res['message'] = "No orders found!";
        print_r(json_encode($res));
    }
}

/* 
21.get_customers
   accesskey:90336
    get_customers:1
    city_id:119     // {optional}
    limit:10        // {optional}
    offset:0        // {optional}
    sort:id         // {optional}
    order:ASC/DESC  // {optional}
    search:value    // {optional}
*/
if (isset($_POST['get_customers']) && !empty($_POST['get_customers'])) {
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;

    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    if (isset($_POST['city_id']) && !empty($_POST['city_id'])) {
        $filter_user = $db->escapeString($fn->xss_clean($_POST['city_id']));
        $where .= ' where u.city=' . $filter_user;
    }
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if (isset($_POST['city_id']) && !empty($_POST['city_id'])) {
            $where .= " and `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `email` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `city` like '%" . $search . "%' OR `area` like '%" . $search . "%' OR `street` like '%" . $search . "%' OR `status` like '%" . $search . "%' OR `created_at` like '%" . $search . "%'";
        } else {
            $where .= " Where `id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `email` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `city` like '%" . $search . "%' OR `area` like '%" . $search . "%' OR `street` like '%" . $search . "%' OR `status` like '%" . $search . "%' OR `created_at` like '%" . $search . "%'";
        }
    }
    $sql = "SELECT COUNT(id) as total FROM `users` u " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row)
            $total = $row['total'];

        $sql = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u " . $where . " ORDER BY `" . $sort . "` " . $order . " LIMIT " . $offset . ", " . $limit;
        $db->sql($sql);
        $res = $db->getResult();
        $rows = array();
        $tempRow = array();

        foreach ($res as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $path = DOMAIN_URL . 'upload/profile/';
            if (!empty($row['profile'])) {
                $tempRow['profile'] = $path . $row['profile'];
            } else {
                $tempRow['profile'] = $path . "default_user_profile.png";
            }
            $tempRow['email'] = $row['email'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['balance'] = $row['balance'];
            $tempRow['referral_code'] = $row['referral_code'];
            $tempRow['friends_code'] = !empty($row['friends_code']) ? $row['friends_code'] : '';
            $tempRow['city_id'] = $row['city'];
            $tempRow['city'] = !empty($row['city_name']) ? $row['city_name'] : "";
            $tempRow['area_id'] = $row['area'];
            $tempRow['area'] = !empty($row['area_name']) ? $row['area_name'] : "";
            $tempRow['street'] = $row['street'];
            $tempRow['apikey'] = $row['apikey'];
            $tempRow['status'] = $row['status'];
            $tempRow['created_at'] = $row['created_at'];
            $rows[] = $tempRow;
        }
        $response['error'] = false;
        $response['message'] = "Customers fatched successfully.";
        $response['total'] = $total;
        $response['data'] = $rows;
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again leter.";
    }
    print_r(json_encode($response));
}

/* 
22. get_financial_statistics
    accesskey:90336
    get_financial_statistics:1
*/
if (isset($_POST['get_financial_statistics']) && !empty($_POST['get_financial_statistics'])) {
    $config = $fn->get_configurations();
    $total_orders = $total_products = $total_users = $total_sold_out_products = $total_low_stock_count = 0;

    $low_stock_limit = isset($config['low-stock-limit']) && (!empty($config['low-stock-limit'])) ? $config['low-stock-limit'] : 0;
    $sql = "SELECT * FROM settings WHERE variable='currency'";
    $db->sql($sql);
    $res_currency = $db->getResult();

    $total_orders = $fn->rows_count('orders');
    $total_products = $fn->rows_count('products');
    $total_users = $fn->rows_count('users');
    $total_sold_out_products = $fn->sold_out_count();
    $total_low_stock_count = $fn->low_stock_count($low_stock_limit);

    $year = date("Y");
    $curdate = date('Y-m-d');
    $sql = "SELECT SUM(final_total) AS total_sale,DATE(date_added) AS order_date FROM orders WHERE YEAR(date_added) = '$year' AND DATE(date_added)<'$curdate' AND `active_status`='delivered' GROUP BY DATE(date_added) ORDER BY DATE(date_added) DESC  LIMIT 0,7";
    $db->sql($sql);
    $result_order = $db->getResult();
    $total_sales = array_column($result_order, "total_sale");
    if (!empty($total_products) && !empty($total_users)) {

        $response['error'] = false;
        $response['total_orders'] = $total_orders;
        $response['total_products'] = $total_products;
        $response['total_users'] = $total_users;
        $response['total_sold_out_products'] = $total_sold_out_products;
        $response['total_low_stock_count'] = $total_low_stock_count;
        $response['currency'] = $res_currency[0]['value'];
        $response['total_sale'] = (!empty($result_order)) ? strval(array_sum($total_sales)) : "0";
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again leter.";
    }
    print_r(json_encode($response));
}

/* 
23.login
    accesskey:90336
    username:admin
    password:admin123
    fcm_id:YOUR_FCM_ID   // {optional}
    login:1
*/
if (isset($_POST['login']) && !empty($_POST['login'])) {

    if (empty(trim($_POST['username']))) {
        $response['error'] = true;
        $response['message'] = "Username should be filled!";
        print_r(json_encode($response));
        return false;
        exit();
    }
    if (empty($_POST['password'])) {
        $response['error'] = true;
        $response['message'] = "Password should be filled!";
        print_r(json_encode($response));
        return false;
        exit();
    }
    $username = $db->escapeString(trim($fn->xss_clean($_POST['username'])));
    $password = md5($db->escapeString($fn->xss_clean($_POST['password'])));
    $sql = "SELECT * FROM `admin` WHERE username = '" . $username . "' AND password = '" . $password . "'";
    $db->sql($sql);
    $res = $db->getResult();
    $num = $db->numRows($res);
    $rows = $tempRow = $permissions = $permission = array();
    if ($num == 1) {
        $admin_id = $res[0]['id'];

        $fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) ? $db->escapeString($fn->xss_clean($_POST['fcm_id'])) : "";
        if (!empty($fcm_id)) {
            $sql1 = "update admin set `fcm_id` ='$fcm_id' where id = $admin_id";
            $db->sql($sql1);
            $db->sql($sql);
            $res = $db->getResult();
        }
        unset($res[0]['password']);
        $permissions = json_decode($res[0]['permissions'], true);

        foreach ($permissions as $per) {

            if (!array_key_exists('create', $per)) {
                $per['create'] = "0";
            }
            if (!array_key_exists('read', $per)) {
                $per['read'] = "0";
            }
            if (!array_key_exists('update', $per)) {
                $per['update'] = "0";
            }
            if (!array_key_exists('delete', $per)) {
                $per['delete'] = "0";
            }
            $permission[] = $per;
        }

        $asd = array_combine(array_keys($permissions), $permission);
        unset($res[0]['permissions']);
        $response['error'] = false;
        $response['message'] = "Admin login successfully";
        $response['permissions'] = $asd;
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "Invalid username or password!";
    }
    print_r(json_encode($response));
}

/* 
24.update_admin_fcm_id
   accesskey:90336
    id:1
    fcm_id:YOUR_FCM_ID
    update_admin_fcm_id:1
*/
if (isset($_POST['update_admin_fcm_id']) && !empty($_POST['update_admin_fcm_id'])) {
    if (empty($_POST['fcm_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass the fcm_id!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    if (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) {
        $fcm_id = $db->escapeString($fn->xss_clean($_POST['fcm_id']));
        $sql1 = "update admin set `fcm_id` ='$fcm_id' where id = '" . $id . "'";
        if ($db->sql($sql1)) {
            $response['error'] = false;
            $response['message'] = "Admin fcm_id Updeted successfully.";
        } else {
            $response['error'] = true;
            $response['message'] = "Can not update fcm_id of admin.";
        }
        print_r(json_encode($response));
    }
}

/* 
25. get_privacy_and_terms
    accesskey:90336
    get_privacy_and_terms:1
*/
if (isset($_POST['get_privacy_and_terms']) && !empty($_POST['get_privacy_and_terms'])) {
    $sql = "select value from `settings` where variable='manager_app_privacy_policy'";
    $db->sql($sql);
    $res = $db->getResult();
    $sql1 = "select value from `settings` where variable='manager_app_terms_conditions'";
    $db->sql($sql1);
    $res1 = $db->getResult();
    if (!empty($res) && !empty($res1)) {
        $response['error'] = false;
        $response['message'] = "Privacy & Policy fetched!";
        $response['privacy_policy'] = $res[0]['value'];
        $response['terms_conditions'] = $res1[0]['value'];
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong!";
    }
    print_r(json_encode($response));
}

/* 
26.update_order_status
    accesskey:90336
    update_order_status:1
    id:169
    status:cancelled        // {optional}
    delivery_boy_id:20      // {optional}
    seller_notes:test       // {optional}
    pickup_time:2021-10-30 09:41:28        // {optional}
*/
if (isset($_POST['update_order_status']) && isset($_POST['id'])) {
    // if (!verify_token()) {
    //     return false;
    // }
    $id = $db->escapeString($fn->xss_clean($_POST['id']));
    $postStatus = (isset($_POST['status']) && $_POST['status'] != '')  ? $db->escapeString($fn->xss_clean($_POST['status'])) : "";


    $store_pickup = $fn->is_lockup($id);
    if (isset($_POST['pickup_time']) && isset($_POST['seller_notes']) && $_POST['pickup_time'] != '' && $_POST['seller_notes'] != '') {
        $pickup_time = (isset($_POST['pickup_time']) && $_POST['pickup_time'] != '') ? $db->escapeString($fn->xss_clean($_POST['pickup_time'])) : "";
        $seller_notes = (isset($_POST['seller_notes']) && $_POST['seller_notes'] != '') ? $db->escapeString($fn->xss_clean($_POST['seller_notes'])) : "";
    } else {
        $seller_notes = "";
        $pickup_time  = "0000-00-00 00:00:00";
    }
    $sql = "UPDATE orders SET `pickup_time`='" . $pickup_time . "' ,`seller_notes` = '" . $seller_notes . "' WHERE id=" . $id;
    $db->sql($sql);

    $sql = "select o.*,obt.status as attachment_status from orders o LEFT JOIN order_bank_transfers obt ON o.id = obt.order_id where o.id=" . $id;
    $db->sql($sql);
    $res = $db->getResult();

    if ($res[0]['active_status'] == 'awaiting_payment' && ($postStatus == 'returned' || $postStatus == 'delivered' || $postStatus == 'shipped' || $postStatus == 'processed' || $postStatus == 'ready_to_pickup')) {
        $response['error'] = true;
        $response['message'] = "Order can not $postStatus. Because it is on awaiting status.";
        print_r(json_encode($response));
        return false;
    }

    if ($res[0]['payment_method'] == 'bank transfer') {
        $atta_status = $res[0]['attachment_status'] == '0' ? 'pending' : 'rejected ';
        if ($res[0]['attachment_status'] == '0' || $res[0]['attachment_status'] == '2') {
            $response['error'] = true;
            $response['message'] = "Order can not $postStatus. because attachment status is $atta_status";
            print_r(json_encode($response));
            return false;
        }
    }


    if (isset($_POST['delivery_boy_id']) && !empty($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != "") {
        if ($postStatus == 'awaiting_payment') {
            $response['error'] = true;
            $response['message'] = "You can not assign delivery boy when order status is Awaiting Payment.";
            print_r(json_encode($response));
            return false;
        }
        $delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
        $sql = "SELECT delivery_boy_id,status FROM `orders` where id=$id";
        $db->sql($sql);
        $res_delivery_boy_id = $db->getResult();

        if (($res_delivery_boy_id[0]['delivery_boy_id'] == 0)
            || ($res_delivery_boy_id[0]['delivery_boy_id'] != $delivery_boy_id && $res_delivery_boy_id[0]['status'] != 'cancelled')
        ) {
            $sql_get_name = "select name from delivery_boys where id='$delivery_boy_id'";
            $db->sql($sql_get_name);
            $delivery_boy_name = $db->getResult();
            if ($postStatus == 'delivered') {
                $message_delivery_boy = "Hello, Dear " . ucwords($delivery_boy_name[0]['name']) . ", your order has been delivered. order ID : #" . $id . ". Please take a note of it.";
            } else {
                $message_delivery_boy = "Hello, Dear " . ucwords($delivery_boy_name[0]['name']) . ", You have new order to deliver. Here is your order ID : #" . $id . ". Please take a note of it.";
            }
            $fn->send_notification_to_delivery_boy($delivery_boy_id, "Your new order with ID : #$id has been " . ucwords($postStatus), $message_delivery_boy, 'delivery_boys', $id);
            $fn->store_delivery_boy_notification($delivery_boy_id, $id, "Your new order with ID : #$id  has been " . ucwords($postStatus), $message_delivery_boy, 'order_reward');
        }
        $sql = "UPDATE orders SET `delivery_boy_id`='" . $delivery_boy_id . "' WHERE id=" . $id;
        $db->sql($sql);
    }
    $sql = "SELECT COUNT(id) as cancelled FROM `orders` WHERE id=" . $id . " && (active_status LIKE '%cancelled%' OR active_status LIKE '%returned%')";
    $db->sql($sql);
    $res_cancelled = $db->getResult();
    if ($res_cancelled[0]['cancelled'] > 0) {
        $response['error'] = true;
        $response['message'] = 'Could not update order status once cancelled or returned!';
        print_r(json_encode($response));
        return false;
    }

    if ($res[0]['active_status'] != 'delivered' && $postStatus == 'returned') {
        $response['error'] = true;
        $response['message'] = 'Cannot return order unless it is delivered!';
        print_r(json_encode($response));
        return false;
    }
    $sql = "SELECT sub_total FROM order_items WHERE order_id=" . $id;
    $db->sql($sql);
    $res_query = $db->getResult();
    $sql = "SELECT COUNT(id) as total FROM `orders` WHERE user_id=" . $res[0]['user_id'] . " && status LIKE '%delivered%'";
    $db->sql($sql);
    $res_count = $db->getResult();
    $sql = "SELECT * FROM `users` WHERE id=" . $res[0]['user_id'];
    $db->sql($sql);
    $res_user = $db->getResult();
    if (!empty($res)) {
        $status = json_decode($res[0]['status']);
        $user_id =  $res[0]['user_id'];
        foreach ($status as $each) {
            if (in_array($postStatus, $each)) {
                $response['error'] = true;
                if ($store_pickup == 0) {
                    $response['message'] = isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != '' && ($res[0]['delivery_boy_id'] != 0) ? 'Delivery Boy updated, Order already ' . $postStatus : 'Order already ' . $postStatus;
                } else {
                    $response['message'] =  'Pickup data updated , Order already ' . $postStatus;
                }
                print_r(json_encode($response));
                return false;
            }
        }
        if ($postStatus == 'cancelled' || $postStatus == 'returned') {

            $sql = 'SELECT oi.`id` as order_item_id,oi.`product_variant_id`,oi.`quantity`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE `order_id`=' . $id;
            $db->sql($sql);
            $res_oi = $db->getResult();
            if ($cancel_order_from == "") {
                if ($postStatus == 'cancelled') {
                    $cancelation_error = 0;
                    for ($j = 0; $j < count($res_oi); $j++) {
                        $resp = $fn->is_product_cancellable($res_oi[$j]['order_item_id']);
                        if ($resp['till_status_error'] == 1 || $resp['cancellable_status_error'] == 1) {
                            $cancelation_error = 1;
                        }
                    }
                    if ($cancelation_error == 1) {
                        $resp['error'] = true;
                        $resp['message'] = "Found one or more items in order which is either not cancelable or not matching cancelation criteria!";
                        print_r(json_encode($resp));
                        return false;
                    }
                } else {
                    $return_error = 0;
                    for ($j = 0; $j < count($res_oi); $j++) {
                        $resp = $fn->is_product_returnable($res_oi[$j]['order_item_id']);
                        if ($resp['return_status_error'] == 1) {
                            $return_error = 1;
                        }
                    }
                    if ($return_error == 1) {
                        $resp['error'] = true;
                        $resp['message'] = "Found one or more items in order which is not returnable!";
                        print_r(json_encode($resp));
                        return false;
                    }
                }
            }
            for ($i = 0; $i < count($res_oi); $i++) {
                if ($res_oi[$i]['type'] == 'packet') {
                    $sql = "UPDATE product_variant SET stock = stock + " . $res_oi[$i]['quantity'] . " WHERE id='" . $res_oi[$i]['product_variant_id'] . "'";
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
                    if ($res_oi[$i]['measurement_unit_id'] != $res_oi[$i]['stock_unit_id']) {
                        $stock = $fn->convert_to_parent($res_oi[$i]['measurement'], $res_oi[$i]['measurement_unit_id']);
                        $stock = $stock * $res_oi[$i]['quantity'];
                        $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";
                        $db->sql($sql);
                    } else {
                        $stock = $res_oi[$i]['measurement'] * $res_oi[$i]['quantity'];
                        $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";
                        $db->sql($sql);
                    }
                }
            }
            if (strtolower($res[0]['payment_method']) != 'cod') {
                /* update user's wallet */
                $user_id = $res[0]['user_id'];
                $total = $res[0]['total'] + $res[0]['delivery_charge'] + $res[0]['tax_amount'] - $res[0]['promo_discount'];
                $user_wallet_balance = $fn->get_wallet_balance($user_id);
                $new_balance = $user_wallet_balance + $total;
                $fn->update_wallet_balance($new_balance, $user_id);
                /* add wallet transaction */
                $wallet_txn_id = $fn->add_wallet_transaction($id, $user_id, 'credit', $total, 'Balance credited against item cancellation..');
            } else {
                if ($res[0]['wallet_balance'] != 0) {
                    /* update user's wallet */
                    $user_id = $res[0]['user_id'];
                    $total = $res[0]['total'] + $res[0]['delivery_charge'] + $res[0]['tax_amount'] - $res[0]['promo_discount'];
                    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
                    $fn->update_wallet_balance($new_balance, $user_id);
                    /* add wallet transaction */
                    $wallet_txn_id = $fn->add_wallet_transaction($id, $user_id, 'credit', $total, 'Balance credited against item cancellation!');
                }
            }
        }


        if ($postStatus == 'delivered') {
            $sql = "SELECT delivery_boy_id,final_total,total FROM orders WHERE id=" . $id;
            $db->sql($sql);
            $res_boy = $db->getResult();

            if ($res_boy[0]['delivery_boy_id'] != 0) {
                $sql = "SELECT bonus,name FROM delivery_boys WHERE id=" . $res_boy[0]['delivery_boy_id'];
                $db->sql($sql);
                $res_bonus = $db->getResult();

                $reward = $res_boy[0]['total'] / 100 * $res_bonus[0]['bonus'];

                if ($reward > 0) {
                    $sql = "UPDATE delivery_boys SET balance = balance + $reward WHERE id=" . $res_boy[0]['delivery_boy_id'];
                    $db->sql($sql);
                    $comission = $fn->add_delivery_boy_commission($delivery_boy_id, 'credit', $reward, 'Order Delivery Commission.');
                    $sql = "SELECT value FROM `settings` WHERE variable='currency'";
                    $db->sql($sql);
                    $currency = $db->getResult();
                    $message_delivery_boy = "Hello, Dear " . ucwords($res_bonus[0]['name']) . ", Here is the new update on your order for the order ID : #" . $id . ". Your Commission of" . $reward . " is credited. Please take a note of it.";
                    $fn->send_notification_to_delivery_boy($delivery_boy_id, "Your commission " . $reward . " " . $currency[0]['value'] . " has been credited", "$message_delivery_boy", 'delivery_boys', $id);
                    $fn->store_delivery_boy_notification($delivery_boy_id, $id, "Your commission " . $reward . " " . $currency[0]['value'] . " has been credited", $message_delivery_boy, 'order_reward');
                }
            }
            if ($config['is-refer-earn-on'] == 1) {
                if ($res_boy[0]['total'] >= $config['min-refer-earn-order-amount']) {
                    if ($res_count[0]['total'] == 0) {
                        if ($res_user[0]['friends_code'] != '') {
                            if ($config['refer-earn-method'] == 'percentage') {
                                $percentage = $config['refer-earn-bonus'];
                                $bonus_amount = $res_boy[0]['total'] / 100 * $percentage;
                                if ($bonus_amount > $config['max-refer-earn-amount']) {
                                    $bonus_amount = $config['max-refer-earn-amount'];
                                }
                            } else {
                                $bonus_amount = $config['refer-earn-bonus'];
                            }
                            $sql  = "SELECT name,friends_code FROM users WHERE id=" . $res[0]['user_id'];
                            $db->sql($sql);
                            $res_data = $db->getResult();

                            $sql = " select id from `users` where `referral_code` = '" . $res_data[0]['friends_code'] . "'";
                            $db->sql($sql);
                            $friend_user = $db->getResult();

                            if (!empty($friend_user))
                                $fn->add_wallet_transaction($id, $friend_user[0]['id'], 'credit', floor($bonus_amount), 'Refer & Earn Bonus on first order by ' . ucwords($res_data[0]['name']));

                            $sql = "UPDATE users SET balance = balance + floor($bonus_amount) WHERE referral_code='" . $res_data[0]['friends_code'] . "'";
                            $db->sql($sql);
                        }
                    }
                }
            }
        }
        $temp = [];
        foreach ($status as $s) {
            array_push($temp, $s[0]);
        }
        $sql = "SELECT id,active_status FROM order_items WHERE order_id=" . $id;
        $db->sql($sql);
        $result = $db->getResult();
        if ($postStatus == 'cancelled') {
            if (!in_array('cancelled', $temp)) {
                $status[] = array('cancelled', date("d-m-Y h:i:sa"));
                $data = array(
                    'status' => $db->escapeString(json_encode($status)),
                );
            }
            $db->update('orders', $data, 'id=' . $id);
            foreach ($result as $item) {
                if ($item['active_status'] != 'cancelled') {
                    $item_data = array(
                        'status' => $db->escapeString(json_encode($status)),
                        'active_status' => 'cancelled'
                    );
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
        }

        if ($postStatus == 'processed') {
            if (!in_array('processed', $temp)) {
                $status[] = array('processed', date("d-m-Y h:i:sa"));
                $data = array(
                    'status' => $db->escapeString(json_encode($status))
                );
            }
            $db->update('orders', $data, 'id=' . $id);
            foreach ($result as $item) {
                $item_data = array(
                    'status' => $db->escapeString(json_encode($status)),
                    'active_status' => 'processed'
                );
                if ($item['active_status'] != 'cancelled') {
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
        }

        if ($postStatus == 'received') {
            if (!in_array('received', $temp)) {
                $status[] = array('received', date("d-m-Y h:i:sa"));
                $data = array(
                    'status' => $db->escapeString(json_encode($status))
                );
            }
            $db->update('orders', $data, 'id=' . $id);
            foreach ($result as $item) {
                $item_data = array(
                    'status' => $db->escapeString(json_encode($status)),
                    'active_status' => 'received'
                );
                if ($item['active_status'] != 'cancelled') {
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
            /* get order data */
            $user_id1 = $fn->get_data($columns = ['user_id', 'total', 'delivery_charge', 'discount', 'final_total', 'payment_method', 'address', 'otp'], 'id=' . $id, 'orders');

            /* get user data */
            $user_email = $fn->get_data($columns = ['email', 'name'], 'id=' . $user_id1[0]['user_id'], 'users');
            $subject = "Order received successfully";

            /* get order item by order id */
            $order_item = $fn->get_order_item_by_order_id($id);
            $item_ids = array_column($order_item, 'product_variant_id');

            /* get product details by varient id */
            $item_details = $fn->get_product_by_variant_id(json_encode($item_ids));

            for ($i = 0; $i < count($item_details); $i++) {
                $item_data1[] = array(
                    'name' => $item_details[$i]['name'], 'tax_amount' => $order_item[$i]['tax_amount'], 'tax_percentage' => $order_item[$i]['tax_percentage'], 'tax_title' => $item_details[$i]['tax_title'], 'unit' =>  $item_details[$i]['measurement'] . " " . $item_details[$i]['measurement_unit_name'],
                    'qty' => $order_item[$i]['quantity'], 'subtotal' => $order_item[$i]['sub_total']
                );
            }

            $user_wallet_balance = $fn->get_wallet_balance($user_id1[0]['user_id']);
            $user_msg = !empty($res[0]['seller_notes']) ? $res[0]['seller_notes'] : "";
            $user_msg .= "Hello, Dear " . $user_email[0]['name'] . ", We have received your order successfully. Your order summaries are as followed:<br><br>";
            $otp_msg = "Here is your OTP. Please, give it to delivery boy only while getting your order.";

            $order_data = array('total_amount' => $user_id1[0]['total'], 'delivery_charge' => $user_id1[0]['delivery_charge'], 'discount' => $user_id1[0]['discount'], 'wallet_used' => $user_wallet_balance, 'final_total' => $user_id1[0]['final_total'], 'payment_method' => $user_id1[0]['payment_method'], 'address' => $user_id1[0]['address'], 'user_msg' => $user_msg, 'otp_msg' => $otp_msg, 'otp' => $user_id1[0]['otp']);
            send_smtp_mail($user_email[0]['email'], $subject, $item_data1, $order_data);
            $fn->send_order_update_notification($user_id1[0]['user_id'], "Your order has been " . ucwords($postStatus), $user_msg, 'order', $id);
        }
        if ($store_pickup == 0) {
            if ($postStatus == 'shipped') {
                if (!in_array('processed', $temp)) {
                    $status[] = array('processed', date("d-m-Y h:i:sa"));
                    $data = array('status' => $db->escapeString(json_encode($status)));
                }
                if (!in_array('shipped', $temp)) {
                    $status[] = array('shipped', date("d-m-Y h:i:sa"));
                    $data = array('status' => $db->escapeString(json_encode($status)));
                }
                $db->update('orders', $data, 'id=' . $id);
                foreach ($result as $item) {
                    $item_data = array(
                        'status' => $db->escapeString(json_encode($status)),
                        'active_status' => 'shipped'
                    );
                    if ($item['active_status'] != 'cancelled') {
                        $db->update('order_items', $item_data, 'id=' . $item['id']);
                    }
                }
            }
        } else {
            if ($postStatus == 'ready_to_pickup') {
                if (!in_array('processed', $temp)) {
                    $status[] = array('processed', date("d-m-Y h:i:sa"));
                    $data = array('status' => $db->escapeString(json_encode($status)));
                }
                if (!in_array('ready_to_pickup', $temp)) {
                    $status[] = array('ready_to_pickup', date("d-m-Y h:i:sa"));
                    $data = array('status' => $db->escapeString(json_encode($status)));
                }
                $db->update('orders', $data, 'id=' . $id);
                foreach ($result as $item) {
                    $item_data = array(
                        'status' => $db->escapeString(json_encode($status)),
                        'active_status' => 'ready_to_pickup'
                    );
                    if ($item['active_status'] != 'cancelled') {
                        $db->update('order_items', $item_data, 'id=' . $item['id']);
                    }
                }
            }
        }

        if ($postStatus == 'delivered') {
            if (!in_array('processed', $temp)) {
                $status[] = array('processed', date("d-m-Y h:i:sa"));
                $data = array('status' => $db->escapeString(json_encode($status)));
            }
            if ($store_pickup == 0) {
                if (!in_array('shipped', $temp)) {
                    $status[] = array('shipped', date("d-m-Y h:i:sa"));
                    $data = array('status' => $db->escapeString(json_encode($status)));
                }
            } else {
                if (!in_array('ready_to_pickup', $temp)) {
                    $status[] = array('ready_to_pickup', date("d-m-Y h:i:sa"));
                    $data = array('status' => $db->escapeString(json_encode($status)));
                }
            }
            if (!in_array('delivered', $temp)) {
                $status[] = array('delivered', date("d-m-Y h:i:sa"));
                $data = array('status' => $db->escapeString(json_encode($status)));
            }
            $db->update('orders', $data, 'id=' . $id);
            $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'delivered'
            );
            foreach ($result as $item) {

                if ($item['active_status'] != 'cancelled') {
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
        }
        if ($postStatus == 'returned') {
            $status[] = array('returned', date("d-m-Y h:i:sa"));
            $data = array('status' => $db->escapeString(json_encode($status)));
            $db->update('orders', $data, 'id=' . $id);
            $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'returned'
            );
            foreach ($result as $item) {

                if ($item['active_status'] != 'cancelled' && $item['active_status'] == 'delivered') {
                    $db->update('order_items', $item_data, 'id=' . $item['id']);
                }
            }
        }
        $i = sizeof($status);
        $currentStatus = $status[$i - 1][0];
        $final_status = array(
            'active_status' => $currentStatus
        );
        if ($db->update('orders', $final_status, 'id=' . $id)) {
            $response['error'] = false;
            if ($postStatus == 'cancelled') {
                $response['message'] = "Order has been cancelled!";
            } elseif ($postStatus == 'returned') {
                $response['message'] = "Order has been returned!";
            } else {
                $response['message'] = "Order updated successfully.";
            }
            if ($postStatus != 'received') {
                $user_data = $fn->get_data($columns = ['name', 'email', 'mobile', 'country_code'], 'id=' . $user_id, 'users');
                $to = $user_data[0]['email'];
                $mobile = $user_data[0]['mobile'];
                $country_code = $user_data[0]['country_code'];
                $subject = "Your order has been " . ucwords($postStatus);
                $message = "Hello, Dear " . ucwords($user_data[0]['name']) . ", Here is the new update on your order for the order ID : #" . $id . ". Your order has been " . ucwords($postStatus) . ". Please take a note of it.";
                $message .= !empty($res[0]['seller_notes']) ? $res[0]['seller_notes'] : "";
                $message .= "Thank you for using our services!You will receive future updates on your order via Email!";
                $fn->send_order_update_notification($user_id, "Your order has been " . ucwords($postStatus), $message, 'order', $id);
                send_email($to, $subject, $message);
                $message = "Hello, Dear " . ucwords($user_data[0]['name']) . ", Here is the new update on your order for the order ID : #" . $id . ". Your order has been " . ucwords($postStatus) . ". Please take a note of it.";
                $message .= "Thank you for using our services! Contact us for more information";
            }
            $res = $db->getResult();

            print_r(json_encode($response));
        } else {
            $response['error'] = true;
            $response['message'] = isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != '' ? 'Delivery Boy updated, But could not update order status try again!' : 'Could not update order status try again!';
            print_r(json_encode($response));
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Sorry Invalid order ID";
        print_r(json_encode($response));
    }
}

if (isset($_POST['update_bank_transfer_status']) && $_POST['update_bank_transfer_status'] == 1) {
    /*  
    27. update_bank_transfer_status
        accesskey:90336
        update_bank_transfer_status:1
        order_id:123456
        message:test
        status:0 - Pending / 1 - Accepted / 2 - Rejected
    */
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
        $response['error'] = true;
        $response['message'] = "status already Accepted.";
        print_r(json_encode($response));
        return false;
    }

    if (($res[0]['status'] == 0 && $status == 0) || ($res[0]['status'] == 1 && $status == 1) || ($res[0]['status'] == 2 && $status == 2)) {
        $response['error'] = true;
        $response['message'] = "status already $atta_status.";
        print_r(json_encode($response));
        return false;
    }

    if ($res[0]['status'] < $status) {
        if (!empty($message)) {
            $sql_query = "update order_bank_transfers set `message`='" . $message . "',`status`='" . $status . "' where `order_id`=" . $order_id;
        } else {
            $sql_query = "update order_bank_transfers set `status`='" . $status . "' where `order_id`=" . $order_id;
        }

        if ($db->sql($sql_query)) {
            $response['error'] = true;
            $response['message'] = "Bank Transfer Details Updated successfully.";
            print_r(json_encode($response));
            return false;
        } else {
            $response['error'] = true;
            $response['message'] = "Bank Transfer Details Not Updated";
            print_r(json_encode($response));
            return false;
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occurred! Please Try Again.";
        print_r(json_encode($response));
        return false;
    }
}


/* 
28.get_permissions
    accesskey:90336
    id:1
    get_permissions:1
    type: orders/payment/customers/featured/products_order/products/subcategories/categories/home_sliders/faqs/reports/locations/settings/transactions/notifications/return_requests/delivery_boys/promo_codes/new_offers   // {optional}
*/
if (isset($_POST['get_permissions']) && !empty($_POST['get_permissions'])) {

    if (empty(trim($_POST['id']))) {
        $response['error'] = true;
        $response['message'] = "Admin id should be filled!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $type = (isset($_POST['type']) && !empty($_POST['type'])) ? $db->escapeString(trim($fn->xss_clean($_POST['type']))) : "";
    $sql = "SELECT `permissions` FROM `admin` WHERE id = $id ";
    $db->sql($sql);
    $res = $db->getResult();
    $num = $db->numRows($res);
    $rows = $tempRow = $per = $permissions = $permission = array();
    if ($num == 1) {
        $permissions = json_decode($res[0]['permissions'], true);
        if ($type == "products_order" || $type == "orders" || $type == "payment" || $type == "home_sliders" || $type == "categories" || $type == "subcategories" || $type == "products" || $type == "featured" || $type == "customers" || $type == "payment" || $type == "new_offers" || $type == "promo_codes" || $type == "delivery_boys" || $type == "return_requests" || $type == "notifications" || $type == "transactions" || $type == "settings" || $type == "locations" || $type == "reports" || $type == "faqs") {

            $per = $permissions[$type];

            if (!array_key_exists('create', $per)) {
                $per['create'] = "0";
            }
            if (!array_key_exists('read', $per)) {
                $per['read'] = "0";
            }
            if (!array_key_exists('update', $per)) {
                $per['update'] = "0";
            }
            if (!array_key_exists('delete', $per)) {
                $per['delete'] = "0";
            }
            $permission = $per;
            $response['error'] = false;
            $response['message'] = "Permissions fetched successfully";
            $response['data'][$type] = $permission;
        } else if ($type == "") {
            foreach ($permissions as $per) {
                if (!array_key_exists('create', $per)) {
                    $per['create'] = "0";
                }
                if (!array_key_exists('read', $per)) {
                    $per['read'] = "0";
                }
                if (!array_key_exists('update', $per)) {
                    $per['update'] = "0";
                }
                if (!array_key_exists('delete', $per)) {
                    $per['delete'] = "0";
                }
                $permission[] = $per;
            }
            $asd1 = array_combine(array_keys($permissions), $permission);
            $response['error'] = false;
            $response['message'] = "Permissions fetched successfully";
            $response['data'] = $asd1;
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Permissions can not fetched!";
    }
    print_r(json_encode($response));
}


/* 
29.update_order_item_status
    accesskey:90336
    update_order_item_status:1
    order_item_id:7166
    status:cancelled
    order_id:3445
*/
if (isset($_POST['update_order_item_status']) && isset($_POST['order_item_id'])) {
    // if (!verify_token()) {
    //     return false;
    // }
    $order_item_id = $db->escapeString($fn->xss_clean($_POST['order_item_id']));
    $order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
    $postStatus = $db->escapeString($fn->xss_clean($_POST['status']));

    $store_pickup = $fn->is_lockup($order_id);

    $sql = "SELECT COUNT(id) as cancelled FROM `order_items` WHERE id=" . $order_item_id . " && status LIKE '%$postStatus%'";
    $db->sql($sql);
    $res_cancelled = $db->getResult();
    if ($res_cancelled[0]['cancelled'] == 'awaiting_payment' && ($postStatus == 'returned' || $postStatus == 'delivered' || $postStatus == 'shipped' || $postStatus == 'processed' || $postStatus == 'ready_to_pickup')) {
        $response['error'] = true;
        $response['message'] = "Order item can not $postStatus. Because it is on awaiting status.";
        print_r(json_encode($response));
        return false;
    }
    if ($res_cancelled[0]['cancelled'] > 0) {
        $response['error'] = true;
        $response['message'] = 'Could not update order status. Item is already ' . ucwords($postStatus) . '!';
        print_r(json_encode($response));
        return false;
    }

    $sql = "SELECT user_id,status,sub_total FROM order_items WHERE id =" . $order_item_id;
    $db->sql($sql);
    $result = $db->getResult();

    if (!empty($result)) {
        $status = json_decode($result[0]['status']);
        if ($postStatus == 'cancelled') {
            if ($cancel_order_from == "") {
                $response = $fn->is_product_cancellable($order_item_id);
                if ($response["error"] == 1) {
                    print_r(json_encode($response));
                    return false;
                }
            }
            $sql = 'SELECT final_total,total,user_id,payment_method,wallet_balance,delivery_charge,tax_amount,status FROM orders WHERE id=' . $order_id;
            $db->sql($sql);
            $res_order = $db->getResult();
            $sql = 'SELECT oi.*,oi.`product_variant_id`,oi.`quantity`,oi.`discounted_price`,oi.`price`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE oi.`id`=' . $order_item_id;
            $db->sql($sql);
            $res_oi = $db->getResult();
            $price = ($res_oi[0]['discounted_price'] == 0) ? ($res_oi[0]['price'] * $res_oi[0]['quantity']) + $res_oi[0]['tax_amount']  : $res_oi[0]['discounted_price'] * $res_oi[0]['quantity']  + $res_oi[0]['tax_amount'];
            $total = $res_order[0]['total'];
            $final_total = $res_order[0]['final_total'];
            $delivery_charge = $res_order[0]['delivery_charge'];
            if ($total - $price >= 0) {
                $sql_total = "update orders set total=$total-$price where id=" . $order_id;
                $db->sql($sql_total);
            }
            $sql = "select total from orders where id=" . $order_id;
            $db->sql($sql);
            $res_total = $db->getResult();
            $total = $res_total[0]['total'];

            if ($total < $config['min_amount']) {
                if ($delivery_charge == 0) {
                    $dchrg = $config['delivery_charge'];
                    $sql_delivery_chrg = "update orders set delivery_charge=$dchrg where id=" . $order_id;
                    $db->sql($sql_delivery_chrg);
                    $sql_final_total = "update orders set final_total=$final_total-$price+$dchrg where id=" . $order_id;
                } else {
                    $sql_final_total = "update orders set final_total=$final_total-$price where id=" . $order_id;
                }
                $db->sql($sql_final_total);
            } else {
                $sql_final_total = "update orders set final_total=$final_total-$price where id=" . $order_id;
            }
            $db->sql($sql_final_total);
            if ($total <= 0) {
                $sql = "update orders set delivery_charge=0,tax_amount=0,tax_percentage=0,final_total=0 where id=" . $order_id;
                $db->sql($sql);
            }

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
                    $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";
                    $db->sql($sql);
                } else {
                    $stock = $res_oi[0]['measurement'] * $res_oi[0]['quantity'];
                    $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";
                    $db->sql($sql);
                }
                $sql = "select stock from product_variant where product_id=" . $res_oi[0]['product_id'];
                $db->sql($sql);
                $res_stck = $db->getResult();
                if ($res_stck[0]['stock'] > 0) {
                    $sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'" .  " AND id='" . $res_oi[0]['product_variant_id'] . "'";
                    $db->sql($sql);
                }
            }
            $status[] = array($postStatus, date("d-m-Y h:i:sa"));
            $currentStatus = $postStatus;

            $oi_status = $db->escapeString(json_encode($status));
            $sql = "UPDATE order_items SET `status` = '" . $oi_status . "',active_status = '" . $currentStatus . "' WHERE id=" . $order_item_id;
            $db->sql($sql);

            $sql = "SELECT id FROM order_items WHERE order_id=" . $order_id;
            $db->sql($sql);
            $total = $db->numRows();
            $sql = "SELECT id FROM `order_items` WHERE order_id=" . $order_id . " && (`active_status` LIKE '%cancelled%' OR `active_status` LIKE '%returned%' )";
            $db->sql($sql);
            $cancelled = $db->numRows();
            if ($cancelled == $total) {
                if (strtolower($res_order[0]['payment_method']) != 'cod') {
                    /* update user's wallet */
                    $user_id = $res_order[0]['user_id'];
                    $total_amount = $res_order[0]['total'] + $res_order[0]['delivery_charge'] + $res_order[0]['tax_amount'];
                    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = $user_wallet_balance + $total_amount;
                    $fn->update_wallet_balance($new_balance, $user_id);
                    $wallet_txn_id = $fn->add_wallet_transaction($order_id, $user_id, 'credit', $total_amount, 'Balance credited against item cancellation...');
                } else {
                    if ($res_order[0]['wallet_balance'] != 0) {
                        $user_id = $res_order[0]['user_id'];
                        $user_wallet_balance = $fn->get_wallet_balance($user_id);
                        $new_balance = ($user_wallet_balance + $res_order[0]['wallet_balance']);
                        $fn->update_wallet_balance($new_balance, $user_id);
                        $wallet_txn_id = $fn->add_wallet_transaction($order_id, $user_id, 'credit', $res_order[0]['wallet_balance'], 'Balance credited against item cancellation!!');
                    }
                }

                $data_order = array(
                    'status' => $db->escapeString(json_encode($status)),
                    'active_status' => $currentStatus
                );
                $db->update('orders', $data_order, 'id=' . $order_id);
            }

            $response['error'] = false;
            $response['message'] = 'Order item cancelled successfully!';
            $response['subtotal'] = $result[0]['sub_total'];
            print_r(json_encode($response));
            return false;
        }
        if ($postStatus == 'returned') {
            // checking for product is returnable or not
            $response = $fn->is_product_returnable($order_item_id);
            if ($response["error"] == 1) {
                print_r(json_encode($response));
                return false;
            }
            $is_item_delivered = 0;
            foreach ($status as $each_status) {
                if (in_array('delivered', $each_status)) {
                    $is_item_delivered = 1;
                    $config['max-product-return-days'];
                    $now = time(); // or your date as well
                    $status_date = strtotime($each_status[1]);
                    $datediff = $now - $status_date;
                    $no_of_days = round($datediff / (60 * 60 * 24));
                    if ($no_of_days > $config['max-product-return-days']) {
                        $response['error'] = true;
                        $response['message'] = 'Oops! Sorry you cannot return the item now. You have crossed product\'s maximum return period';
                        print_r(json_encode($response));
                        return false;
                    }
                }
            }
            if (!$is_item_delivered) {
                $response['error'] = true;
                $response['message'] = 'Cannot return item unless it is delivered!';
                print_r(json_encode($response));
                return false;
            }
            if ($fn->is_return_request_exists($result[0]['user_id'], $order_item_id)) {
                $response['error'] = true;
                $response['message'] = 'Already applied for return';
                print_r(json_encode($response));
                return false;
            }
            /* store return request */
            $fn->store_return_request($result[0]['user_id'], $order_id, $order_item_id);

            $response['error'] = false;
            $response['message'] = 'Order item returned request received successfully! Please wait for approval.';
            $response['subtotal'] = $result[0]['sub_total'];
            print_r(json_encode($response));
            return false;
        }
    } else {
        $response['error'] = true;
        $response['message'] = 'Order item not found!';
        print_r(json_encode($response));
        return false;
    }
}


/* 
30.delivery_boy_fund_transfers
    accesskey:90336
    delivery_boy_fund_transfers:1
    delivery_boy_id:104     // {optional}
    limit:10                // {optional}
    offset:0                // {optional}
    sort:id                 // {optional}
    order:ASC/DESC          // {optional}
    search:value            // {optional}
*/
if (isset($_POST['delivery_boy_fund_transfers']) && !empty($_POST['delivery_boy_fund_transfers'])) {
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;

    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    if (isset($_POST['delivery_boy_id']) && !empty($_POST['delivery_boy_id'])) {
        $delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
        $where .= " where f.delivery_boy_id = $delivery_boy_id";
    }

    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search = $db->escapeString($fn->xss_clean($_POST['search']));
        if (isset($_POST['delivery_boy_id']) && !empty($_POST['delivery_boy_id'])) {
            $where .= " and f.`id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR f.`date_created` like '%" . $search . "%'";
        } else {
            $where .= " Where f.`id` like '%" . $search . "%' OR `name` like '%" . $search . "%' OR `mobile` like '%" . $search . "%' OR `address` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR f.`date_created` like '%" . $search . "%'";
        }
    }
    $sql = "SELECT COUNT(f.`id`) as total FROM `fund_transfers` f LEFT JOIN `delivery_boys` d ON f.delivery_boy_id=d.id" . $where;
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row)
            $total = $row['total'];

        $sql = "SELECT f.*,d.name,d.mobile,d.address FROM `fund_transfers` f LEFT JOIN `delivery_boys` d ON f.delivery_boy_id=d.id  $where ORDER BY $sort $order LIMIT $offset,$limit";
        $db->sql($sql);
        $res = $db->getResult();
        $rows = array();
        $tempRow = array();

        foreach ($res as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['name'];
            $tempRow['mobile'] = $row['mobile'];
            $tempRow['address'] = $row['address'];
            $tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
            $tempRow['opening_balance'] = $row['opening_balance'];
            $tempRow['closing_balance'] = $row['closing_balance'];
            $tempRow['amount'] = $row['amount'];
            $tempRow['type'] = $row['type'];
            $tempRow['status'] = $row['status'];
            $tempRow['message'] = $row['message'];
            $tempRow['date_created'] = $row['date_created'];
            $rows[] = $tempRow;
        }
        $response['error'] = false;
        $response['message'] = "Fund transfers fatched successfully.";
        $response['total'] = $total;
        $response['data'] = $rows;
    } else {
        $response['error'] = true;
        $response['message'] = "Something went wrong, please try again leter.";
    }
    print_r(json_encode($response));
}

/* 
31.delivery_boy_transfer_fund
    accesskey:90336
    delivery_boy_transfer_fund:1		
    delivery_boy_id:302
    delivery_boy_balance:20
    amount:20
    message: message from admin     // {optional}
*/
if (isset($_POST['delivery_boy_transfer_fund']) && !empty($_POST['delivery_boy_transfer_fund'])) {
    if (empty($_POST['delivery_boy_id']) || $_POST['delivery_boy_balance'] == '' || $_POST['amount'] == '') {
        $response['error'] = true;
        $response['message'] = "some parameters are missing!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
    $sql = "SELECT id FROM `delivery_boys` where id=$id";
    $db->sql($sql);
    $res1 = $db->getResult();
    if (!empty($res1)) {
        $balance = $db->escapeString($fn->xss_clean($_POST['delivery_boy_balance']));
        if ($balance == 0) {
            $response['error'] = true;
            $response['message'] = "Balance must be greater then zero.";
            print_r(json_encode($response));
            return false;
            exit();
        }
        if (!is_numeric(trim($_POST['amount']))) {
            $response['error'] = true;
            $response['message'] = "Amount must be number.";
            print_r(json_encode($response));
            return false;
            exit();
        }
        $amount = $db->escapeString($fn->xss_clean($_POST['amount']));
        if ($amount > $balance) {
            $response['error'] = true;
            $response['message'] = "Amount must be less then or equal to balance.";
            print_r(json_encode($response));
            return false;
            exit();
        }
        $message = (!empty($_POST['message'])) ? $db->escapeString($fn->xss_clean($_POST['message'])) : 'Fund Transferred By Admin';
        $bal = $balance - $amount;
        $sql = "Update delivery_boys set `balance`='" . $bal . "' where `id`=" . $id;
        $db->sql($sql);
        $sql = "INSERT INTO `fund_transfers` (`delivery_boy_id`,`amount`,`opening_balance`,`closing_balance`,`status`,`message`) VALUES ('" . $id . "','" . $amount . "','" . $balance . "','" . $bal . "','SUCCESS','" . $message . "')";
        if ($db->sql($sql)) {
            $response['error'] = false;
            $response['message'] = "Amount transferred successfully.";
        } else {
            $response['error'] = true;
            $response['message'] = "Amount does not transferred, somthing went wrong.";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "Delivery boy does not exist";
    }
    print_r(json_encode($response));
}

/* 
32.get_all_data
    accesskey:90336
    get_all_data:1
*/
if (isset($_POST['get_all_data']) && !empty($_POST['get_all_data'])) {
    //categories
    $sql = "SELECT * FROM category ORDER BY id DESC ";
    $db->sql($sql);
    $res_categories = $db->getResult();

    for ($i = 0; $i < count($res_categories); $i++) {
        $res_categories[$i]['image'] = (!empty($res_categories[$i]['image'])) ? DOMAIN_URL . '' . $res_categories[$i]['image'] : '';
    }
    // slider images
    $sql = 'SELECT * from slider order by id DESC';
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
            }
            if ($row['type'] == 'product') {
                $sql = 'select `name` from products where id = ' . $row['type_id'] . ' order by id desc';
                $db->sql($sql);
                $result1 = $db->getResult();
                $name = (!empty($result1[0]['name'])) ? $result1[0]['name'] : "";
            }

            $temp['type'] = $row['type'];
            $temp['type_id'] = $row['type_id'];
            $temp['name'] = $name;
            $temp['image'] = DOMAIN_URL . $row['image'];
            $slider_images[] = $temp;
        }
    }

    // featured sections
    $sql = 'select * from `sections` order by id desc';
    $db->sql($sql);
    $result = $db->getResult();
    $response = $product_ids = $section = $variations = $featured_sections = array();
    foreach ($result as $row) {
        $product_ids = explode(',', $row['product_ids']);

        $section['id'] = $row['id'];
        $section['title'] = $row['title'];
        $section['short_description'] = $row['short_description'];
        $section['style'] = $row['style'];
        $section['product_ids'] = array_map('trim', $product_ids);
        $product_ids = $section['product_ids'];

        $product_ids = implode(',', $product_ids);

        $sql = 'SELECT * FROM `products` WHERE `status` = 1 AND id IN (' . $product_ids . ')';
        $db->sql($sql);
        $result1 = $db->getResult();
        $product = array();
        $i = 0;
        foreach ($result1 as $row) {
            $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=" . $row['id'] . " ORDER BY serve_for ASC";
            $db->sql($sql);
            $variants = $db->getResult();

            $row['other_images'] = json_decode($row['other_images'], 1);
            $row['other_images'] = (empty($row['other_images'])) ? array() : $row['other_images'];

            for ($j = 0; $j < count($row['other_images']); $j++) {
                $row['other_images'][$j] = DOMAIN_URL . $row['other_images'][$j];
            }
            if ($row['tax_id'] == 0) {
                $row['tax_title'] = "";
                $row['tax_percentage'] = "";
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
            }
            $row['image'] = DOMAIN_URL . $row['image'];
            $product[$i] = $row;
            $product[$i]['variants'] = $variants;
            $i++;
        }
        $section['products'] = $product;
        $featured_sections[] = $section;
        unset($section['products']);
    }
    // offer images
    $sql = 'SELECT * from offers order by id desc';
    $db->sql($sql);
    $res_offer_images = $db->getResult();
    $response = $temp = $offer_images = array();
    foreach ($res_offer_images as $row) {
        $temp['image'] = DOMAIN_URL . $row['image'];
        $offer_images[] = $temp;
    }

    $response['error'] = false;
    $response['message'] = "Data fetched successfully";
    $response['categories'] = $res_categories;
    $response['slider_images'] = $slider_images;
    $response['sections'] = $featured_sections;
    $response['offer_images'] = $offer_images;
    print_r(json_encode($response));
}

/*  
33. delete_other_images
    accesskey:90336
    delete_other_images:1
    product_id:1
    image:1    // {index of other image array}
*/
if (isset($_POST['delete_other_images']) && $_POST['delete_other_images'] == 1) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['product_id']) || $_POST['image'] == "") {
        $response['error'] = true;
        $response['message'] = "All fields should be Passed!";
        print_r(json_encode($response));
        return false;
        exit();
    }
    $pid = $db->escapeString($fn->xss_clean($_POST['product_id']));
    $i = $db->escapeString($fn->xss_clean($_POST['image']));

    $result = $fn->delete_other_images($pid, $i);
    if ($result == 1) {
        $response['error'] = false;
        $response['message'] = "Image deleted successfully";
    } else {
        $response['error'] = true;
        $response['message'] = "Image is not deleted. try agian later";
    }
    print_r(json_encode($response));
    return false;
}

/*  
34. delete_variant
    accesskey:90336
    delete_variant:1
    variant_id:1
*/
if (isset($_POST['delete_variant']) && $_POST['delete_variant'] == 1) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['variant_id'])) {
        $response['error'] = true;
        $response['message'] = "All fields should be Passed!";
        print_r(json_encode($response));
        return false;
        exit();
    }
    $v_id = $db->escapeString($fn->xss_clean($_POST['variant_id']));
    $sql = "SELECT id FROM product_variant WHERE `id`= " . $v_id;
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {
        $product_id = $fn->get_product_id_by_variant_id($v_id);
        $sql = "SELECT count(id) as total FROM product_variant WHERE `product_id`= " . $product_id;
        $db->sql($sql);
        $results = $db->getResult();
        $total = $results[0]['total'];

        $sql = "SELECT images FROM product_variant WHERE id =" . $v_id;
        $db->sql($sql);
        $res = $db->getResult();

        if (isset($res[0]['images']) && !empty($res[0]['images'])) {
            $other_images = $res[0]['images']; /*get images json array*/
            $variant_images = str_replace("'", '"', $other_images);
            $other_images = json_decode($variant_images); /*decode from json to array*/

            foreach ($other_images as $other_image) {
                unlink('../../' . $other_image);
            }
        }
        if ($total == 1) {
            $sql_query = "DELETE FROM cart WHERE product_id = $product_id ";
            $db->sql($sql_query);
            $result = $fn->delete_variant($v_id);

            $sql = "SELECT count(id) as total from product_variant WHERE product_id=" . $product_id;
            $db->sql($sql);
            $total = $db->getResult();

            if ($total[0]['total'] == 0) {
                $sql_query = "SELECT image FROM products WHERE id =" . $product_id;
                $db->sql($sql_query);
                $res = $db->getResult();
                unlink('../../' . $res[0]['image']);

                $sql_query1 = "SELECT size_chart FROM products WHERE id =" . $product_id;
                $db->sql($sql_query1);
                $result1 = $db->getResult();
                unlink('../../' . $res1[0]['size_chart']);

                $sql_query = "SELECT other_images FROM products WHERE id =" . $product_id;
                $db->sql($sql_query);
                $res = $db->getResult();
                if (!empty($res[0]['other_images'])) {
                    $other_images = json_decode($res[0]['other_images']);
                    foreach ($other_images as $other_image) {
                        unlink('../../' . $other_image);
                    }
                }

                $sql_query = "DELETE FROM products WHERE id =" . $product_id;
                $db->sql($sql_query);

                $sql_query = "DELETE FROM favorites WHERE product_id = " . $product_id;
                $db->sql($sql_query);
            }
        } else {
            $result = $fn->delete_variant($v_id);
        }
        $response['error'] = false;
        $response['message'] = "Product variant deleted successfully!";
    } else {
        $response['error'] = true;
        $response['message'] = "Product variant not exist or some error occured!";
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['get_units']) && $_POST['get_units'] == 1) {
    /*  
    35. get_units
        accesskey:90336
        get_units:1
    */

    $sql = "SELECT * FROM unit ";
    $db->sql($sql);
    $res = $db->getResult();

    for ($i = 0; $i < count($res); $i++) {
        $res[$i]['parent_id'] = (!empty($res[$i]['parent_id'])) ? $res[$i]['parent_id'] : "0";
        $res[$i]['conversion'] = (!empty($res[$i]['conversion'])) ? $res[$i]['conversion'] : "0";
    }

    if (!empty($res)) {
        $response['error'] = false;
        $response['message'] = "Units retrieved successfully";
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "No data found!";
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['get_taxes']) && $_POST['get_taxes'] == 1) {
    /*  
    36. get_taxes
        accesskey:90336
        get_taxes:1
    */

    $sql = "SELECT * FROM taxes ";
    $db->sql($sql);
    $res = $db->getResult();

    if (!empty($res)) {
        $response['error'] = false;
        $response['message'] = "Taxes retrieved successfully";
        $response['data'] = $res;
    } else {
        $response['error'] = true;
        $response['message'] = "No data found!";
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['upload_bank_transfers_attachment']) && $_POST['upload_bank_transfers_attachment'] == 1) {
    /*  
    37. upload_bank_transfers_attachment
        accesskey:90336
        upload_bank_transfers_attachment:1
        order_id:1
        image[]:FILE
    */

    if (empty($_POST['order_id']) || empty($_FILES['image'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }
    $error['image'] = '';
    $order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        for ($i = 0; $i < count($_FILES["image"]["name"]); $i++) {
            if ($_FILES["image"]["error"][$i] > 0) {
                $response['error'] = true;
                $response['message'] = "Images not uploaded!";
                print_r(json_encode($response));
                return false;
            } else {
                $result = $fn->validate_other_images($_FILES["image"]["tmp_name"][$i], $_FILES["image"]["type"][$i]);
                if ($result) {
                    $response['error'] = true;
                    $response['message'] = "image type must jpg, jpeg, gif, or png!";
                    print_r(json_encode($response));
                    return false;
                }
            }
        }
    }

    if (isset($_FILES['image']) && ($_FILES['image']['size'][0] > 0)) {
        $file_data = array();
        $target_path = '../upload/attachments/';
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $target_path1 = 'upload/attachments/';
        for ($i = 0; $i < count($_FILES["image"]["name"]); $i++) {
            $filename = $_FILES["image"]["name"][$i];
            $temp = explode('.', $filename);
            $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
            $file_data[] = $target_path1 . '' . $filename;
            if (!move_uploaded_file($_FILES["image"]["tmp_name"][$i], $target_path . '' . $filename)) {
                $response['error'] = true;
                $response['message'] = "Images not uploaded!";
                print_r(json_encode($response));
                return false;
            }
        }
        for ($i = 0; $i < count($file_data); $i++) {
            $data = array(
                'order_id' => $order_id,
                'attachment' => $file_data[$i],
            );
            $db->insert('order_bank_transfers', $data);
        }
        $result = $db->getResult();
    }

    $sql = "select o.*,obt.attachment,count(obt.attachment) as total_attachment ,obt.message as bank_transfer_message,obt.status as bank_transfer_status,(select name from users u where u.id=o.user_id) as user_name from orders o LEFT JOIN order_bank_transfers obt
    ON obt.order_id=o.id where o.id = '" . $order_id . "' ";
    $db->sql($sql);
    $res = $db->getResult();
    $i = 0;
    $j = 0;
    foreach ($res as $row) {
        if ($row['discount'] > 0) {
            $discounted_amount = $row['total'] * $row['discount'] / 100;
            $final_total = $row['total'] - $discounted_amount;
            $discount_in_rupees = $row['total'] - $final_total;
        } else {
            $discount_in_rupees = 0;
        }
        $res[$i]['discount_rupees'] = "$discount_in_rupees";


        $sql_query = "SELECT id,attachment FROM order_bank_transfers WHERE order_id = " . $row['id'];
        $db->sql($sql_query);
        $res_attac = $db->getResult();

        $myData = array();
        foreach ($res_attac as $item) {
            array_push($myData, ['id' => $item['id'], 'image' => DOMAIN_URL . $item['attachment']]);
        }
        $body1 = json_encode($myData);
        $body = json_decode($body1);

        $res[$i]['attachment'] = $body;
        $res[$i]['user_name'] = !empty($res[$i]['user_name']) ? $res[$i]['user_name'] : "";
        $res[$i]['delivery_boy_id'] = !empty($res[$i]['delivery_boy_id']) ? $res[$i]['delivery_boy_id'] : "";
        $res[$i]['otp'] = !empty($res[$i]['otp']) ? $res[$i]['otp'] : "";
        $res[$i]['order_note'] = !empty($res[$i]['order_note']) ? $res[$i]['order_note'] : "";
        $res[$i]['bank_transfer_message'] = !empty($res[$i]['bank_transfer_message']) ? $res[$i]['bank_transfer_message'] : "";
        $res[$i]['bank_transfer_status'] = !empty($res[$i]['bank_transfer_status']) ? $res[$i]['bank_transfer_status'] : "0";

        $final_totals = $res[$i]['total'] + $res[$i]['delivery_charge']  - $res[$i]['discount_rupees'] - $res[$i]['promo_discount'] - $res[$i]['wallet_balance'];

        $final_total =  ceil($final_totals);
        $res[$i]['final_total'] = "$final_total";
        $res[$i]['date_added'] = date('d-m-Y h:i:sa', strtotime($res[$i]['date_added']));
        $res[$i]['status'] = json_decode($res[$i]['status']);
        if (in_array('awaiting_payment', array_column($res[$i]['status'], '0'))) {
            $temp_array = array_column($res[$i]['status'], '0');
            $index = array_search("awaiting_payment", $temp_array);
            unset($res[$i]['status'][$index]);
            $res[$i]['status'] = array_values($res[$i]['status']);
        }
        $status = $res[$i]['status'];
        $item1 = array_map('reset', $status);
        $item2 = array_map('end', $status);
        $res[$i]['status_name'] = $item1;
        $res[$i]['status_time'] = $item2;

        $sql = "select oi.*,p.id as product_id,v.id as variant_id, pr.rate,pr.review,pr.status as review_status,p.name,p.image,p.manufacturer,p.made_in,p.return_status,p.cancelable_status,p.till_status,v.measurement,(select short_code from unit u where u.id=v.measurement_unit_id) as unit from order_items oi left join product_variant v on oi.product_variant_id=v.id left join products p on p.id=v.product_id left join product_reviews pr on p.id=pr.product_id where order_id=" . $row['id'] . " GROUP BY oi.id";
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
        for ($j = 0; $j < count($res[$i]['items']); $j++) {
            $res[$i]['items'][$j]['status'] = (!empty($res[$i]['items'][$j]['status'])) ? json_decode($res[$i]['items'][$j]['status']) : array();

            if (in_array('awaiting_payment', array_column($res[$i]['items'][$j]['status'], '0'))) {
                $temp_array = array_column($res[$i]['items'][$j]['status'], '0');
                $index = array_search("awaiting_payment", $temp_array);
                unset($res[$i]['items'][$j]['status'][$index]);
                $res[$i]['items'][$j]['status'] = array_values($res[$i]['items'][$j]['status']);
            }

            $res[$i]['items'][$j]['image'] = DOMAIN_URL . $res[$i]['items'][$j]['image'];
            $res[$i]['items'][$j]['deliver_by'] = !empty($res[$i]['items'][$j]['deliver_by']) ? $res[$i]['items'][$j]['deliver_by'] : "";
            $res[$i]['items'][$j]['rate'] = !empty($res[$i]['items'][$j]['rate']) ? $res[$i]['items'][$j]['rate'] : "";
            $res[$i]['items'][$j]['review'] = !empty($res[$i]['items'][$j]['review']) ? $res[$i]['items'][$j]['review'] : "";
            $res[$i]['items'][$j]['manufacturer'] = !empty($res[$i]['items'][$j]['manufacturer']) ? $res[$i]['items'][$j]['manufacturer'] : "";
            $res[$i]['items'][$j]['made_in'] = !empty($res[$i]['items'][$j]['made_in']) ? $res[$i]['items'][$j]['made_in'] : "";
            $res[$i]['items'][$j]['return_status'] = !empty($res[$i]['items'][$j]['return_status']) ? $res[$i]['items'][$j]['return_status'] : "";
            $res[$i]['items'][$j]['cancelable_status'] = !empty($res[$i]['items'][$j]['cancelable_status']) ? $res[$i]['items'][$j]['cancelable_status'] : "";
            $res[$i]['items'][$j]['till_status'] = !empty($res[$i]['items'][$j]['till_status']) ? $res[$i]['items'][$j]['till_status'] : "";
            $res[$i]['items'][$j]['review_status'] = (!empty($res[$i]['items'][$j]['review_status']) && ($res[$i]['items'][$j]['review_status'] == 1)) ? $res[$i]['items'][$j]['review_status'] == TRUE : FALSE;
            $sql = "SELECT id from return_requests where product_variant_id = " . $res[$i]['items'][$j]['variant_id'] . " AND user_id = " . $row['user_id'];
            $db->sql($sql);
            $return_request = $db->getResult();
            if (empty($return_request)) {
                $res[$i]['items'][$j]['applied_for_return'] = false;
            } else {
                $res[$i]['items'][$j]['applied_for_return'] = true;
            }
        }
        $i++;
    }

    $response['error'] = false;
    $response['message'] = "Images uploaded successfully!";
    $response['data'] = $res;
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['delete_bank_transfers_attachment']) && $_POST['delete_bank_transfers_attachment'] == 1) {
    /*  
    38. delete_bank_transfers_attachment
        accesskey:90336
        delete_bank_transfers_attachment:1
        order_id:1
        id:2
    */

    if (empty($_POST['order_id']) || empty($_POST['id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }
    $order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
    $id = $db->escapeString($fn->xss_clean($_POST['id']));

    $sql = "SELECT attachment FROM `order_bank_transfers` WHERE id = $id AND order_id = $order_id";
    $db->sql($sql);
    $image = $db->getResult();
    unlink('../' . $image[0]['attachment']);

    $sql1 = "DElETE FROM `order_bank_transfers` WHERE id = $id AND order_id = $order_id";
    $db->sql($sql1);
    $res = $db->getResult();

    $response['error'] = false;
    $response['message'] = "Image deleted successfully!";
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['get_order_invoice']) && $_POST['get_order_invoice'] == 1) {
    /*  
    39. get_order_invoice
        accesskey:90336
        get_order_invoice:1
        order_id:1  OR invoice_id:2
    */

    if (!verify_token()) {
        return false;
    }
    $where = '';

    if (empty($_POST['order_id']) && empty($_POST['invoice_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass order id or invoice id!";
        print_r(json_encode($response));
        return false;
    }

    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;

    $order_id = (isset($_POST['order_id']) && !empty($_POST['order_id']) && is_numeric($_POST['order_id'])) ? $db->escapeString($fn->xss_clean($_POST['order_id'])) : "";
    $invoice_id = (isset($_POST['invoice_id']) && !empty($_POST['invoice_id']) && is_numeric($_POST['invoice_id'])) ? $db->escapeString($fn->xss_clean($_POST['invoice_id'])) : "";


    if (isset($_POST['pickup'])) {
        $where = $_POST['pickup'] == 1 ? " AND o.local_pickup = 1 " :  " WHERE o.local_pickup = 0 ";
    }
    if (!empty($order_id)) {
        $where .= !empty($where) ? " AND o.id = " . $order_id : " WHERE o.id = " . $order_id;
    }
    if (!empty($invoice_id)) {
        $where .= !empty($where) ? " AND i.id = " . $invoice_id : " WHERE i.id = " . $invoice_id;
    }
    $sql = "select count(o.id) as total from orders o LEFT JOIN invoice i ON o.id=i.order_id " . $where;
    $db->sql($sql);
    $res = $db->getResult();
    $total = $res[0]['total'];
    $sql = "select o.*,i.id as invoice_id,obt.attachment,count(obt.attachment) as total_attachment ,obt.message as bank_transfer_message,obt.status as bank_transfer_status,(select name from users u where u.id=o.user_id) as user_name,(select email from users u where u.id=o.user_id) as email from orders o LEFT JOIN order_bank_transfers obt
    ON obt.order_id=o.id LEFT JOIN invoice i ON o.id=i.order_id " . $where . " GROUP BY id ORDER BY date_added DESC LIMIT $offset,$limit";
    $db->sql($sql);
    $res = $db->getResult();
    $i = 0;
    $j = 0;
    foreach ($res as $row) {
        if ($row['discount'] > 0) {
            $discounted_amount = $row['total'] * $row['discount'] / 100;
            $final_total = $row['total'] - $discounted_amount;
            $discount_in_rupees = $row['total'] - $final_total;
        } else {
            $discount_in_rupees = 0;
        }
        $res[$i]['discount_rupees'] = "$discount_in_rupees";


        $sql_query = "SELECT id,attachment FROM order_bank_transfers WHERE order_id = " . $row['id'];
        $db->sql($sql_query);
        $res_attac = $db->getResult();

        $myData = array();
        foreach ($res_attac as $item) {
            array_push($myData, ['id' => $item['id'], 'image' => DOMAIN_URL . $item['attachment']]);
        }
        $body1 = json_encode($myData);
        $body = json_decode($body1);

        $res[$i]['attachment'] = $body;
        $res[$i]['user_name'] = !empty($res[$i]['user_name']) ? $res[$i]['user_name'] : "";
        $res[$i]['seller_notes'] = !empty($res[$i]['seller_notes']) ? $res[$i]['seller_notes'] : "";
        $res[$i]['pickup_time'] = !empty($res[$i]['pickup_time']) ? $res[$i]['pickup_time'] : "";
        $res[$i]['delivery_boy_id'] = !empty($res[$i]['delivery_boy_id']) ? $res[$i]['delivery_boy_id'] : "";
        $res[$i]['otp'] = !empty($res[$i]['otp']) ? $res[$i]['otp'] : "";
        $res[$i]['order_note'] = !empty($res[$i]['order_note']) ? $res[$i]['order_note'] : "";
        $res[$i]['bank_transfer_message'] = !empty($res[$i]['bank_transfer_message']) ? $res[$i]['bank_transfer_message'] : "";
        $res[$i]['bank_transfer_status'] = !empty($res[$i]['bank_transfer_status']) ? $res[$i]['bank_transfer_status'] : "0";
        $res[$i]['invoice_id'] = !empty($res[$i]['invoice_id']) ? $res[$i]['invoice_id'] : "0";

        $final_totals = $res[$i]['total'] + $res[$i]['delivery_charge']  - $res[$i]['discount_rupees'] - $res[$i]['promo_discount'] - $res[$i]['wallet_balance'];

        $final_total =  ceil($final_totals);
        $res[$i]['final_total'] = "$final_total";
        $res[$i]['date_added'] = date('d-m-Y h:i:sa', strtotime($res[$i]['date_added']));
        $res[$i]['status'] = json_decode($res[$i]['status']);
        if (in_array('awaiting_payment', array_column($res[$i]['status'], '0'))) {
            $temp_array = array_column($res[$i]['status'], '0');
            $index = array_search("awaiting_payment", $temp_array);
            unset($res[$i]['status'][$index]);
            $res[$i]['status'] = array_values($res[$i]['status']);
        }
        $status = $res[$i]['status'];
        $item1 = array_map('reset', $status);
        $item2 = array_map('end', $status);
        $res[$i]['status_name'] = !empty($item1) ? $item1 : array();
        $res[$i]['status_time'] = !empty($item2) ? $item2 : array();

        $sql = "select oi.*,p.id as product_id,v.id as variant_id, pr.rate,pr.review,pr.status as review_status,p.name,p.image,p.manufacturer,p.made_in,p.return_status,p.cancelable_status,p.till_status,v.measurement,(select short_code from unit u where u.id=v.measurement_unit_id) as unit from order_items oi left join product_variant v on oi.product_variant_id=v.id left join products p on p.id=v.product_id left join product_reviews pr on p.id=pr.product_id where order_id=" . $row['id'] . " GROUP BY oi.id";
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
        for ($j = 0; $j < count($res[$i]['items']); $j++) {
            $res[$i]['items'][$j]['status'] = (!empty($res[$i]['items'][$j]['status'])) ? json_decode($res[$i]['items'][$j]['status']) : array();

            if (in_array('awaiting_payment', array_column($res[$i]['items'][$j]['status'], '0'))) {
                $temp_array = array_column($res[$i]['items'][$j]['status'], '0');
                $index = array_search("awaiting_payment", $temp_array);
                unset($res[$i]['items'][$j]['status'][$index]);
                $res[$i]['items'][$j]['status'] = array_values($res[$i]['items'][$j]['status']);
            }

            $res[$i]['items'][$j]['image'] = DOMAIN_URL . $res[$i]['items'][$j]['image'];
            $res[$i]['items'][$j]['deliver_by'] = !empty($res[$i]['items'][$j]['deliver_by']) ? $res[$i]['items'][$j]['deliver_by'] : "";
            $res[$i]['items'][$j]['rate'] = !empty($res[$i]['items'][$j]['rate']) ? $res[$i]['items'][$j]['rate'] : "";
            $res[$i]['items'][$j]['review'] = !empty($res[$i]['items'][$j]['review']) ? $res[$i]['items'][$j]['review'] : "";
            $res[$i]['items'][$j]['manufacturer'] = !empty($res[$i]['items'][$j]['manufacturer']) ? $res[$i]['items'][$j]['manufacturer'] : "";
            $res[$i]['items'][$j]['made_in'] = !empty($res[$i]['items'][$j]['made_in']) ? $res[$i]['items'][$j]['made_in'] : "";
            $res[$i]['items'][$j]['return_status'] = !empty($res[$i]['items'][$j]['return_status']) ? $res[$i]['items'][$j]['return_status'] : "";
            $res[$i]['items'][$j]['cancelable_status'] = !empty($res[$i]['items'][$j]['cancelable_status']) ? $res[$i]['items'][$j]['cancelable_status'] : "";
            $res[$i]['items'][$j]['till_status'] = !empty($res[$i]['items'][$j]['till_status']) ? $res[$i]['items'][$j]['till_status'] : "";
            $res[$i]['items'][$j]['review_status'] = (!empty($res[$i]['items'][$j]['review_status']) && ($res[$i]['items'][$j]['review_status'] == 1)) ? $res[$i]['items'][$j]['review_status'] == TRUE : FALSE;
            $sql = "SELECT id from return_requests where product_variant_id = " . $res[$i]['items'][$j]['variant_id'] . " AND user_id = " . $user_id;
            $db->sql($sql);
            $return_request = $db->getResult();
            if (empty($return_request)) {
                $res[$i]['items'][$j]['applied_for_return'] = false;
            } else {
                $res[$i]['items'][$j]['applied_for_return'] = true;
            }
        }
        $i++;
    }
    $orders = $order = array();

    if (!empty($res)) {
        $orders['error'] = false;
        $orders['total'] = $total;
        $orders['data'] = array_values($res);
        print_r(json_encode($orders));
    } else {
        $res['error'] = true;
        $res['message'] = "No orders found!";
        print_r(json_encode($res));
    }
}

/*  
40. delete_variant_images
    accesskey:90336
    delete_variant_images:1
    variant_id:1
    image:1    // {index of image array}
*/
if (isset($_POST['delete_variant_images']) && $_POST['delete_variant_images'] == 1) {
    if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
        $response['error'] = true;
        $response['message'] = "This operation is not allowed in demo panel!.!";
        print_r(json_encode($response));
        return false;
    }
    if (empty($_POST['variant_id']) || $_POST['image'] == "") {
        $response['error'] = true;
        $response['message'] = "All fields should be Passed!";
        print_r(json_encode($response));
        return false;
    }

    $vid = $db->escapeString($fn->xss_clean($_POST['variant_id']));
    $i = $db->escapeString($fn->xss_clean($_POST['image']));

    $result = $fn->delete_variant_images($vid, $i);
    if ($result == 1) {
        $response['error'] = false;
        $response['message'] = "Image deleted successfully";
    } else {
        $response['error'] = true;
        $response['message'] = "Image is not deleted. try agian later";
    }
    print_r(json_encode($response));
    return false;
}
