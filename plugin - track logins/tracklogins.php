<?php

/**
 * @package Track_Logins
 * @version 1.0
 */
/*
  Plugin Name: Track Logins
  Plugin URI: http://wordpress.org/extend/plugins/track-logins
  Description: This is a wordpress plugin which tracks invalid logins. Let's say a user tries to login and the password is wrong, that counts as an invalid login.  Next time the user tries to login it takes the number of invalid logins and runs a routine to increase the amount of time it takes for the login screen to show either it was invalid or log them in.
  Author: Benjamin Lewis
  Version: 1.0
  Author URI: http://wordpress.org/
 */

// function to create the DB / Options / Defaults					
function createtable_tracklogin() {

    global $wpdb;
    $tracklogin = $wpdb->prefix . 'track_login';

    // create the ECPT metabox database table
    $sql = "CREATE TABLE " . $tracklogin . " (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,		
		`ip` varchar(50) NOT NULL,
                `lognum` mediumint(9) NOT NULL,		
		UNIQUE KEY id (id)
		);";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// run the install scripts upon plugin activation
register_activation_hook(__FILE__, 'createtable_tracklogin');

// function to create the DB / Options / Defaults					
function droptable_tracklogin() {

    global $wpdb;
    $tracklogin = $wpdb->prefix . 'track_login';

    // drop the database table track_login    
    $wpdb->query("DROP TABLE {$tracklogin}");
}

// run the uninstall scripts upon plugin deactivation/uninstall
//register_deactivation_hook(__FILE__, 'droptable_tracklogin');
register_uninstall_hook(__FILE__, 'droptable_tracklogin');

function getip() {
    if (isset($_SERVER)) {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    return $realip;
}

function login_success() {

    global $wpdb;
    $tracklogin = $wpdb->prefix . 'track_login';

    $ip_addr = getip();

    $sql = "SELECT count(*) as cip FROM {$tracklogin} WHERE `ip`='{$ip_addr}'";
    $count = $wpdb->get_results($sql);
    if (!($count[0]->cip)) {
        //insert row
        $insertsql = "INSERT INTO {$tracklogin} (`ip`, `lognum`) VALUES ('{$ip_addr}', '0')";
        $wpdb->query($insertsql);
    } else {
        //update row
        $updatesql = "UPDATE {$tracklogin} SET `lognum`='0' WHERE `ip`='{$ip_addr}'";
        $wpdb->query($updatesql);
    }
}

// hook success login
add_action('wp_login', 'login_success');

function login_fail() {

    global $wpdb;
    $tracklogin = $wpdb->prefix . 'track_login';

    $ip_addr = getip();

    $sql = "SELECT count(*) as cip, `lognum` FROM {$tracklogin} WHERE `ip`='{$ip_addr}'";
    $count = $wpdb->get_results($sql);
    if (!($count[0]->cip)) {
        //insert row
        $lognum = 1;
        $insertsql = "INSERT INTO {$tracklogin} (`ip`, `lognum`) VALUES ('{$ip_addr}', '{$lognum}')";
        $wpdb->query($insertsql);
    } else {
        //update row
        $lognum = $count[0]->lognum + 1;
        $updatesql = "UPDATE {$tracklogin} SET `lognum`='{$lognum}' WHERE `ip`='{$ip_addr}'";
        $wpdb->query($updatesql);
    }
    sleep(pow(2, $lognum));
}

// hook failed login
add_action('wp_login_failed', 'login_fail');


if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Tracklogin_List_Table extends WP_List_Table {

    function get_columns() {
        $columns = array(
//            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'ip' => 'IP Address',
            'lognum' => 'Number of login failures'
        );
        return $columns;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'id' => array('id', false),
            'ip' => array('ip', false),
            'lognum' => array('lognum', false)
        );
        return $sortable_columns;
    }

//    function get_bulk_actions() {
//        $actions = array(
//            'delete' => 'Delete'
//        );
//        return $actions;
//    }
//
//    function column_cb($item) {
//        return sprintf(
//                        '<input type="checkbox" name="book[]" value="%s" />', $item['id']
//        );
//    }

    function prepare_items() {

        global $wpdb;
        $tracklogin = $wpdb->prefix . 'track_login';
        // If no sort, default to id
        $orderby = (!empty($_GET['orderby']) ) ? $_GET['orderby'] : 'id';
        // If no order, default to asc
        $order = (!empty($_GET['order']) ) ? $_GET['order'] : 'asc';

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $curpage = ($current_page - 1) * $per_page;

        $allsql = "SELECT count(*) as cip FROM {$tracklogin}";
        $all = $wpdb->get_results($allsql);
        $total_items = $all[0]->cip;

        $sql = "SELECT * FROM {$tracklogin} ORDER BY {$orderby} {$order} LIMIT {$curpage},{$per_page}";

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $result = $wpdb->get_results($sql, ARRAY_A);

        $this->set_pagination_args(array(
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page //WE have to determine how many items to show on a page
        ));

        $this->items = $result;
    }

    function column_default($item, $column_name) {
        switch ($column_name) {
            case 'id':
            case 'ip':
            case 'lognum':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }
}

function track_logins_add_menu_items() {
    add_menu_page('Track Logins List Table', 'Track Logins', 'activate_plugins', 'track_logins', 'track_logins_page');
}

add_action('admin_menu', 'track_logins_add_menu_items');

function track_logins_page() {
    $trackloginListTable = new Tracklogin_List_Table();
    echo '<div class="wrap"><h2>Track Logins List Table</h2>';
    $trackloginListTable->prepare_items();
    $trackloginListTable->display();
    echo '</div>';
}

?>
