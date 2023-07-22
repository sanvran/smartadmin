<?php
class Database
{

    function create_database($data)
    {
        $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], '');
        if (mysqli_connect_errno())
            return false;
        $mysqli->query("CREATE DATABASE IF NOT EXISTS " . $data['database']);
        $mysqli->close();
        return true;
    }

    function create_tables($data)
    {
        $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], $data['database']);
        if (mysqli_connect_errno())
            return false;
        $query = file_get_contents('assets/sqlcommand.sql');
        $mysqli->multi_query($query);
        $mysqli->close();
        return true;
    }

    function create_admin($data)
    {
        $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], $data['database']);
        if (mysqli_connect_errno())
            return false;

        $password = $data['admin_password'];
        $admin_email = $data['admin_email'];

        $params = [
            'cost' => 12
        ];

        if (empty($password) || strpos($password, "\0") !== FALSE || strlen($password) > 32) {
            return FALSE;
        } else {
            $password = md5($password);
        }

        $set = " `password`='" . $password . "', `username`='" . $data['admin_username'] . "' ";
        if (isset($data['admin_email'])) {
            $set .= ", `email`='" . $data['admin_email'] . "' ";
        }

        $mysqli->query("UPDATE admin SET " . $set . "  WHERE `id`=1 ");
        $mysqli->close();
        return true;
    }

    function create_base_url($data)
    {
        $mysqli = new mysqli($data['hostname'], $data['username'], $data['password'], $data['database']);
        if (mysqli_connect_errno())
            return false;

        $data_json = array(
            'app_url' => $settings['app_name'],
            'company_title' => 'eCart'
        );

        // $data['app_url']

        $data = json_encode($data_json);
        return true;
    }
}
