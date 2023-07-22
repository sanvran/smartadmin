<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
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
-------------------------------------------
APIs for eCart
-------------------------------------------
1. get_all_products
2. get_all_products_name
3. add_products_review
4. get_product_reviews
5. delete_product_review
6. delete_review_images
-------------------------------------------
-------------------------------------------
*/

if (!verify_token()) {
    return false;
}

if (!isset($_POST['accesskey'])  || trim($_POST['accesskey']) != $access_key) {
    $response['error'] = true;
    $response['message'] = "No Accsess key found!";
    print_r(json_encode($response));
    return false;
}


if (isset($_POST['get_all_products']) && $_POST['get_all_products'] == 1) {
    /* 
    1.get_all_products
        accesskey:90336
        get_all_products:1
        product_id:219      // {optional}
        user_id:1782        // {optional}
        slug:green-apple-1  // {optional}
        category_id:29      // {optional}
        subcategory_id:63   // {optional}
        limit:5             // {optional}
        offset:1            // {optional}
        sort:id             // {optional}
        order:asc/desc      // {optional}
    */

    $product_id = (isset($_POST['product_id']) && !empty($_POST['product_id'])) ? $db->escapeString($fn->xss_clean($_POST['product_id'])) : "";
    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

    $slug = (isset($_POST['slug']) && !empty($_POST['slug'])) ? $db->escapeString($fn->xss_clean($_POST['slug'])) : "";

    $category_id = (isset($_POST['category_id']) && !empty($_POST['category_id'])) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : "";
    $subcategory_id = (isset($_POST['subcategory_id']) && !empty($_POST['subcategory_id'])) ? $db->escapeString($fn->xss_clean($_POST['subcategory_id'])) : "";

    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;

    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : "p.row_order + 0";
    $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $db->escapeString($fn->xss_clean($_POST['order'])) : "ASC";

    $where = "";

    $product = $fn->get_products($user_id, $product_id, $slug, $category_id, $subcategory_id, $where, $limit, $offset, $sort, $order);

    print_r(json_encode($product));
    return false;
}

if (isset($_POST['get_all_products_name']) && $_POST['get_all_products_name'] == 1) {
    /*
    2.get_all_products_name
        accesskey:90336
		get_all_products_name:1
    */
    $sql = "SELECT name FROM `products` where status = 1";
    $db->sql($sql);
    $res = $db->getResult();
    $rows = $tempRow = $blog_array = $blog_array1 = array();
    foreach ($res as $row) {
        $tempRow['name'] = $row['name'];
        $rows[] = $tempRow;
    }
    $names = array_column($rows, 'name');

    $pr_names = implode(",", $names);
    $pr_name = explode(",", $pr_names);

    $response['error'] = false;
    $response['data'] = $pr_name;
    print_r(json_encode($response));
}

if (isset($_POST['add_products_review']) && $_POST['add_products_review'] == 1) {
    /*
    3.add_products_review
        accesskey:90336
        add_products_review:1
        product_id:219      
        user_id:23        
        rate:value
        review:string
        images[]:FILE   // {optional}
    */

    if (empty($_POST['product_id']) || empty($_POST['user_id']) || empty($_POST['rate']) || empty($_POST['review'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
        exit();
    }

    $product_id = $db->escapeString(trim($fn->xss_clean($_POST['product_id'])));
    $user_id = $db->escapeString(trim($fn->xss_clean($_POST['user_id'])));
    $rate = $db->escapeString(trim($fn->xss_clean($_POST['rate'])));
    $review = $db->escapeString(trim($fn->xss_clean($_POST['review'])));
    $product_variant_id = $fn->get_variant_id_by_product_id($product_id);
    $message = false;

    $sql = "SELECT * FROM `order_items` WHERE user_id = $user_id and active_status ='delivered'";
    $db->sql($sql);
    $res = $db->getResult();
    if (empty($res)) {
        $response['error'] = true;
        $response['message'] = "You can not review this product!";
        print_r(json_encode($response));
        return false;
    }

    $sql = "SELECT id FROM users WHERE id=" . $user_id;
    $db->sql($sql);
    $res = $db->getResult();
    if (empty($res)) {
        $response['error'] = true;
        $response['message'] = "User id Does not exists!";
        print_r(json_encode($response));
        return false;
    }

    $sql = "SELECT * FROM product_reviews WHERE product_id=" . $product_id . " AND user_id=" . $user_id;
    $db->sql($sql);
    $res = $db->getResult();
    $count = $db->numRows($res);

    if (!empty($_FILES['images'])) {
        if ($_FILES["images"]["error"] == 0) {
            for ($i = 0; $i < count($_FILES["images"]["name"]); $i++) {
                if ($_FILES["images"]["error"][$i] > 0) {
                    $response['error'] = true;
                    $response['message'] = "Images not uploaded!";
                    print_r(json_encode($response));
                    return false;
                } else {
                    $result = $fn->validate_other_images($_FILES["images"]["tmp_name"][$i], $_FILES["images"]["type"][$i]);
                    if ($result) {
                        $response['error'] = true;
                        $response['message'] = "image type must jpg, jpeg, gif, or png!";
                        print_r(json_encode($response));
                        return false;
                    }
                }
            }
        }

        $other_images = '';
        if (isset($_FILES['images']) && ($_FILES['images']['size'][0] > 0)) {
            $file_data = array();
            $target_path = '../upload/reviews/';
            if (!is_dir($target_path)) {
                mkdir($target_path, 0777, true);
            }
            $target_path1 = 'upload/reviews/';
            for ($i = 0; $i < count($_FILES["images"]["name"]); $i++) {
                $filename = $_FILES["images"]["name"][$i];
                $temp = explode('.', $filename);
                $filename = microtime(true) . '-' . rand(100, 999) . '.' . end($temp);
                $file_data[] = $target_path1 . '' . $filename;
                if (!move_uploaded_file($_FILES["images"]["tmp_name"][$i], $target_path . '' . $filename)) {
                    $response['error'] = true;
                    $response['message'] = "Images not uploaded!";
                    print_r(json_encode($response));
                    return false;
                }
            }
            $other_images = json_encode($file_data);
        }
    }
    if ($count > 0) {
        if (!empty($other_images)) {
            $sql1 = "UPDATE product_reviews SET rate= $rate ,review= '$review',images = '$other_images' WHERE product_id=" . $product_id  . " AND user_id=" . $user_id;
        } else {
            $sql1 = "UPDATE product_reviews SET rate= $rate ,review= '$review' WHERE product_id=" . $product_id  . " AND user_id=" . $user_id;
        }
        $db->sql($sql1);
        $res = $db->getResult();
        $message = true;
    } else {
        if (!empty($other_images)) {
            $sql = "INSERT INTO product_reviews (product_id,user_id,rate,review,images) VALUES('$product_id','$user_id','$rate','$review','$other_images')";
        } else {
            $sql = "INSERT INTO product_reviews (product_id,user_id,rate,review) VALUES('$product_id','$user_id','$rate','$review')";
        }
        $db->sql($sql);
        $res = $db->getResult();
    }

    $sql = "select AVG(rate) as average, ";
    for ($i = 0; $i < 5; $i++) {
        $n = $i + 1;
        $sql .= " ( SELECT COUNT(review) as r$n from product_reviews where rate = $n and product_id = $product_id ) r$n,";
    }
    $sql = rtrim($sql, ",");
    $sql .= " from product_reviews where product_id = $product_id GROUP BY r5 ";
    $db->sql($sql);
    $r5 = $db->getResult();

    $sql = "UPDATE `products` p
        INNER JOIN ( SELECT product_id, COUNT(id) as total_ratings, AVG(rate) as average FROM product_reviews WHERE product_id = $product_id ) pr ON p.id = pr.product_id
        SET p.ratings = pr.average, p.number_of_ratings = pr.total_ratings
    WHERE p.id = $product_id ";
    $res = $db->getResult();
    $product = array();

    if ($db->sql($sql)) {
        $sql1 = "select pr.*,u.name as username,u.profile as user_profile,u.id as user_id,pr.date_added from product_reviews pr join users u on u.id= pr.user_id where pr.product_id = $product_id and pr.user_id=$user_id ";
        $db->sql($sql1);
        $data = $db->getResult();
        $data[0]['images'] = json_decode($data[0]['images'], 1);
        $data[0]['images'] = (empty($data[0]['images'])) ? array() : $data[0]['images'];

        for ($j = 0; $j < count($data[0]['images']); $j++) {
            $data[0]['images'][$j] = DOMAIN_URL . $data[0]['images'][$j];
        }

        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['user_profile'] = (!empty($data[$i]['user_profile'])) ? DOMAIN_URL . 'upload/profile/' . $data[$i]['user_profile'] : '';
        }

        $response['error'] = false;
        $response['message'] = $message == true ? 'Review updated Successfully!' : 'Review Added Successfully!';
        $response['data'] = $data;
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
}

if (isset($_POST['get_product_reviews']) && $_POST['get_product_reviews'] == 1) {
    /*
    4.get_product_reviews
        accesskey:90336
        get_product_reviews:1
        product_id:220      // {optional}
        slug:product-slug   // {optional}
        user_id:29          // {optional}
        limit:5             // {optional}
        offset:1            // {optional}
        sort:id             // {optional}
        order:asc/desc      // {optional}
    */

    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit'])) ? $db->escapeString($fn->xss_clean($_POST['limit'])) : 10;
    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset'])) ? $db->escapeString($fn->xss_clean($_POST['offset'])) : 0;

    $sort = (isset($_POST['sort']) && !empty($_POST['sort'])) ? $db->escapeString($fn->xss_clean($_POST['sort'])) : "p.row_order + 0";
    $order = (isset($_POST['order']) && !empty($_POST['order'])) ? $db->escapeString($fn->xss_clean($_POST['order'])) : "ASC";

    $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

    $where = "";
    if (isset($_POST['product_id']) && !empty($_POST['product_id']) && is_numeric($_POST['product_id'])) {
        $product_id = $db->escapeString($fn->xss_clean($_POST['product_id']));
        $where .=  " AND p.`id` = $product_id";
    }
    if (isset($_POST['user_id']) && !empty($_POST['user_id']) && is_numeric($_POST['user_id'])) {
        $where .=  " AND oi.`user_id` = $user_id";
    }
    if (isset($_POST['slug']) && !empty($_POST['slug'])) {
        $slug = $db->escapeString($fn->xss_clean($_POST['slug']));
        $where .= " AND p.`slug`= '$slug' ";
    }

    $sql = "SELECT p.id as product_id,p.name AS product_name,p.slug,u.id as user_id,u.name as username,u.profile as user_profile,oi.product_variant_id,oi.active_status,p.ratings,p.number_of_ratings,pr.id,pr.rate,pr.review,pr.date_added,pr.images FROM `order_items` oi JOIN product_variant pv ON pv.id = oi.product_variant_id JOIN products p ON p.id = pv.product_id LEFT JOIN product_reviews pr ON pr.product_id = p.id LEFT JOIN users u ON u.id = pr.user_id WHERE active_status = 'delivered' $where GROUP BY pr.user_id";
    $db->sql($sql);
    $total1 = $db->getResult();
    $total = count($total1);

    $sql = "SELECT p.id AS product_id,p.name AS product_name,p.slug,u.id as user_id,u.name as username,u.profile as user_profile,oi.product_variant_id,oi.active_status,p.ratings,p.number_of_ratings,pr.id,pr.rate,pr.review,pr.images,pr.date_added FROM `order_items` oi JOIN product_variant pv ON pv.id = oi.product_variant_id JOIN products p ON p.id = pv.product_id LEFT JOIN product_reviews pr ON pr.product_id = p.id LEFT JOIN users u ON u.id = pr.user_id WHERE oi.active_status = 'delivered' "  . $where . " GROUP BY pr.user_id ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
    $db->sql($sql);
    $res = $db->getResult();
    $product = array();
    $i = 0;
    foreach ($res as $row) {
        $row['user_profile'] = (!empty($row['user_profile'])) ? DOMAIN_URL . 'upload/profile/' . $row['user_profile'] : '';
        $row['review_eligible'] = 1;
        $row1['review_eligible'] = $user_id == ""  ? 1 : 1;

        $sql_query = "SELECT * FROM product_reviews WHERE product_id = " . $row['product_id'] . " AND user_id = " . $row['user_id'] . "";
        $db->sql($sql_query);
        $res1 = $db->getResult();
        if (!empty($res1)) {
            $row['images'] = json_decode($row['images'], 1);
            $row['images'] = (empty($row['images'])) ? array() : $row['images'];
            for ($j = 0; $j < count($row['images']); $j++) {
                $row['images'][$j] = DOMAIN_URL . $row['images'][$j];
            }
            $row['rate'] = (!empty($row['rate'])) ? $row['rate'] : '0';
            $row['user_id'] = (!empty($row['user_id'])) ? $row['user_id'] : '';
            $row['username'] = (!empty($row['username'])) ? $row['username'] : '';
            $row['review'] = (!empty($row['review'])) ? $row['review'] : '';
            $row['id'] = (!empty($row['id'])) ? $row['id'] : '';
            $row['date_added'] = (!empty($row['date_added'])) ? $row['date_added'] : '';
        } else {
            $row['id'] = "";
            $row['rate'] = "0";
            $row['review'] = "";
            $row['date_added'] = "";
            $row['images'] = array();
        }
        $product[$i] = $row;
        $i++;
    }

    if (!empty($res)) {
        $response['error'] = ($res[0]['number_of_ratings'] > 0) ?false : true;
        $response['message'] = ($res[0]['number_of_ratings'] > 0) ? "Products review retrieved successfully" : "No reviews found";
        $response['number_of_reviews'] = $total;
        $response['avg_ratings'] = $res[0]['ratings'];
        $response['number_of_ratings'] = $res[0]['number_of_ratings'];
        $response['review_eligible'] = $row1['review_eligible'];
        $response['product_review'] = ($res[0]['number_of_ratings'] > 0) ? $product : [];
    } else {
        $response['error'] = true;
        $response['message'] = "No products available";
        $response['review_eligible'] = "0";
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['delete_product_review']) && $_POST['delete_product_review'] == 1) {
    /*
    5.delete_product_review
        accesskey:90336
        delete_product_review:1
        product_id:220     
        user_id:29          
    */
    if (empty($_POST['product_id'] && $_POST['user_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    $product_id = $db->escapeString($fn->xss_clean($_POST['product_id']));
    $user_id = $db->escapeString($fn->xss_clean($_POST['user_id']));

    $sql_query = "SELECT images FROM product_reviews WHERE product_id = $product_id AND user_id = $user_id";
    $db->sql($sql_query);
    $res = $db->getResult();

    if (!empty($res[0]['images'])) {
        $other_images = json_decode($res[0]['images']);
        foreach ($other_images as $other_image) {
            unlink('../' . $other_image);
        }
    }

    $sql = "SELECT * FROM product_reviews WHERE product_id=" . $product_id . " AND user_id=" . $user_id;
    $db->sql($sql);
    $res = $db->getResult();
    $count = $db->numRows($res);

    if ($count >= 0) {
        $sql1 = "DELETE FROM `product_reviews` WHERE product_id = $product_id AND user_id = $user_id";
        $db->sql($sql1);
        $results = $db->getResult();
    }

    $sql = "select AVG(rate) as average, ";
    for ($i = 0; $i < 5; $i++) {
        $n = $i + 1;
        $sql .= " ( SELECT COUNT(review) as r$n from product_reviews where rate = $n and product_id = $product_id ) r$n,";
    }
    $sql = rtrim($sql, ",");
    $sql .= " from product_reviews where product_id = $product_id GROUP BY r5 ";
    $db->sql($sql);
    $r5 = $db->getResult();

    $r = "SELECT product_id, COUNT(id) as total_ratings, AVG(rate) as average FROM product_reviews WHERE product_id = $product_id";
    $db->sql($r);
    $res = $db->getResult();
    if ($res[0]['total_ratings'] != 0) {
        $sql1 = "UPDATE `products` p
        INNER JOIN ( SELECT product_id, COUNT(id) as total_ratings, AVG(rate) as average FROM product_reviews WHERE product_id = $product_id ) pr ON p.id = pr.product_id
        SET p.ratings = pr.average, p.number_of_ratings = pr.total_ratings WHERE p.id = $product_id ";
    } else {
        $sql1 = "UPDATE `products` p SET p.ratings = 0.0, p.number_of_ratings = 0 WHERE p.id = $product_id ";
    }
    if ($db->sql($sql1)) {
        $response['error'] = false;
        $response['message'] = "Product Review Deleted Successfully!";
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
    return false;
}

if (isset($_POST['delete_review_images']) && $_POST['delete_review_images'] == 1) {
    /*
    6.delete_review_images
        accesskey:90336
        delete_review_images:1
        product_id:220     
        user_id:29          
    */

    if (empty($_POST['product_id'] && $_POST['user_id'])) {
        $response['error'] = true;
        $response['message'] = "Please pass all fields!";
        print_r(json_encode($response));
        return false;
    }

    $product_id = $db->escapeString($fn->xss_clean($_POST['product_id']));
    $user_id = $db->escapeString($fn->xss_clean($_POST['user_id']));

    $sql_query = "SELECT images FROM product_reviews WHERE product_id = $product_id AND user_id = $user_id";
    $db->sql($sql_query);
    $res = $db->getResult();
    if (!empty($res[0]['images'])) {
        $images = json_decode($res[0]['images']);
        foreach ($images as $image) {
            unlink('../' . $image);
        }
    }
    $sql = "UPDATE product_reviews SET images = '' WHERE product_id = $product_id AND user_id = $user_id";
    $db->sql($sql);
    $result = $db->getResult();
    if (empty($result)) {
        $response['error'] = false;
        $response['message'] = "Product review images deleted successfully!";
    } else {
        $response['error'] = true;
        $response['message'] = "Some Error Occrred! please try again.";
    }
    print_r(json_encode($response));
}
