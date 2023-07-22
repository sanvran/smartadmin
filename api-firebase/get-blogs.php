<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('../includes/crud.php');
$db = new Database();
$db->connect();
include_once('../includes/variables.php');
include_once('verify-token.php');
include_once('../includes/custom-functions.php');
$fn = new custom_functions;

/* 
-------------------------------------------
APIs for eCart
-------------------------------------------
1. get_blogs
2. get_blog_categories
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


if (isset($_POST['get_blogs']) && !empty($_POST['get_blogs']) && $_POST['get_blogs'] == 1) {
    /*  
    1.get_blogs.php
        accesskey:90336      
        get_blogs:1
        slug:value              // {optional}
        category_slug:value     // {optional}
        category_id:9           // {optional}
        blog_id:1               // {optional} 
        offset:0                // {optional}
        limit:10                // {optional}
        sort:id                 // {optional}
        order:DESC / ASC        // {optional}
        search:search_value     // {optional}
    */
    $where = "";
    if (isset($_POST['blog_id']) && !empty($_POST['blog_id'])) {
        $id = $db->escapeString(trim($_POST['blog_id']));
        $where .= " AND b.id = $id";
    }
    if (isset($_POST['slug']) && !empty($_POST['slug'])) {
        $slug = $db->escapeString(trim($_POST['slug']));
        $where .= " AND b.slug = '$slug' ";
    }


    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($_POST['offset'])) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($_POST['limit'])) : 10;

    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($_POST['sort'])) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($_POST['order'])) : 'DESC';

    if (isset($_POST['search'])) {
        $search = $db->escapeString($_POST['search']);
        $where .= " AND (`title` like '%" . $search . "%' OR `description` like '%" . $search . "%' )";
    }

    if (isset($_POST['category_slug']) && !empty($_POST['category_slug'])) {
        $category_slug = $db->escapeString(trim($_POST['category_slug']));
        $where .= " AND bc.slug = '$category_slug' ";
    }

    if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
        $where .= " AND b.category_id = $category_id";
    }

    $sql1 = "SELECT count(b.id) as total FROM `blogs` b join blog_categories bc on bc.id=b.category_id where b.status=1 " . $where;
    $db->sql($sql1);
    $res1 = $db->getResult();
    $total = $res1[0]['total'];
    $sql = "SELECT b.*,bc.slug as category_slug,bc.name as category_name FROM `blogs` b  LEFT JOIN blog_categories bc on bc.id=b.category_id where b.status=1 " . $where . " ORDER BY `$sort` $order LIMIT $offset,$limit";
    $db->sql($sql);
    $res = $db->getResult();
    if (!empty($res)) {
        foreach ($res as $row) {
            $tempRow['id'] = $row['id'];
            $tempRow['category_id'] = $row['category_id'];
            $tempRow['category_slug'] = $row['category_slug'];
            $tempRow['category_name'] = $row['category_name'];
            $tempRow['title'] = $row['title'];
            $tempRow['slug'] = $row['slug'];
            $tempRow['description'] = $row['description'];
            $tempRow['image'] = (!empty($row['image'])) ? DOMAIN_URL . $row['image'] : '';
            $tempRow['status'] = $row['status'];
            $tempRow['date_created'] = $row['date_created'];
            $rows[] = $tempRow;
        }
        $response['error'] = false;
        $response['message'] = 'Blogs Retrived Successfully!';
        $response['total'] = $total;
        $response['data'] = $rows;
    } else {
        $response['error'] = true;
        $response['message'] = 'Data not Found!';
    }
    print_r(json_encode($response));
}

if (isset($_POST['get_blog_categories']) && !empty($_POST['get_blog_categories']) && $_POST['get_blog_categories'] == 1) {
    /* 
    2.get_blog_categories
        accesskey:90336
        get_blog_categories:1
        category_id:2       // {optional}
        slug:value          // {optional}
        limit:10            // {optional}
        offset:0            // {optional}
        sort:id             // {optional}
        order:ASC/DESC      // {optional}
        search:search_value // {optional}
    */
    $where = '';
    $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';

    $where = "";
    if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
        $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
        $where .= " AND id = $category_id";
    }
    if (isset($_POST['slug']) && !empty($_POST['slug'])) {
        $slug = $db->escapeString($fn->xss_clean($_POST['slug']));
        $where .= " AND slug = '$slug' ";
    }

    if (isset($_POST['search'])) {
        $search = $db->escapeString($_POST['search']);
        $where .= " AND (`name` like '%" . $search . "%' OR `slug` like '%" . $search . "%' )";
    }

    $sql = "SELECT count(id) as total FROM blog_categories where status = 1 " . $where;
    $db->sql($sql);
    $total = $db->getResult();

    $sql_query = "SELECT * FROM blog_categories where status = 1 "  . $where . " ORDER BY `$sort` $order LIMIT $offset,$limit";
    $db->sql($sql_query);
    $res = $db->getResult();
    if (!empty($res)) {

        for ($i = 0; $i < count($res); $i++) {
            $res[$i]['image'] = (!empty($res[$i]['image'])) ? DOMAIN_URL  . $res[$i]['image'] : '';
        }

        $response['error'] = false;
        $response['message'] = "Categories retrieved successfully";
        $response['total'] = $total[0]['total'];
        $response['data'] = $res;
    } else {
        $response['error'] = "true";
        $response['message'] = "No data found!";
    }
    print_r(json_encode($response));
    return false;
}
