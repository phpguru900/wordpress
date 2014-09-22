<?php
/**
 * Plugin Name: UltraCart
 * Plugin URI: http://probacto.com
 * Description: This is a wordpress integration to UltraCart web service.
 * Version: 1.0
 * Author: Probacto
 * Author URI: http://probacto.com
 * License: GPL2
 */
define('ULTRACART_PLUGIN_URL', plugin_dir_url(__FILE__));

/* Runs when plugin is activated */
register_activation_hook(__FILE__, 'my_plugin_install');

/* Runs on plugin deactivation */
register_deactivation_hook(__FILE__, 'my_plugin_remove');

function my_plugin_install() {

    global $wpdb;

    $the_page_title = 'Checkout';
    $the_page_name = 'Checkout';
    //$the_page_title2 = 'Shopping Cart';
    // $the_page_name2 = 'Shopping Cart';
    // the menu entry...
    delete_option("my_plugin_page_title");
    add_option("my_plugin_page_title", $the_page_title, '', 'yes');
    //delete_option("my_plugin_page_title2");
    // add_option("my_plugin_page_title2", $the_page_title2, '', 'yes');
    // the slug...
    delete_option("my_plugin_page_name");
    add_option("my_plugin_page_name", $the_page_name, '', 'yes');
    //delete_option("my_plugin_page_name2");
    //add_option("my_plugin_page_name2", $the_page_name2, '', 'yes');
    // the id...
    delete_option("my_plugin_page_id");
    add_option("my_plugin_page_id", '0', '', 'yes');
    //delete_option("my_plugin_page_id2");
    //add_option("my_plugin_page_id2", '0', '', 'yes');

    $the_page = get_page_by_title($the_page_title);
    //$the_page2 = get_page_by_title( $the_page_title2 );

    if (!$the_page) {

        // Create post object
        $_p = array();
        $_p['post_title'] = $the_page_title;
        $_p['post_status'] = 'publish';
        $_p['post_type'] = 'page';
        $_p['comment_status'] = 'closed';
        $_p['ping_status'] = 'closed';

        // Insert the post into the database
        $the_page_id = wp_insert_post($_p);
        if ($the_page_id) {
            update_post_meta($the_page_id, '_wp_page_template', 'checkout-template.php');
        }
    } else {
        // the plugin may have been previously active and the page may just be trashed...

        $the_page_id = $the_page->ID;

        //make sure the page is not trashed...
        $the_page->post_status = 'publish';
        $the_page_id = wp_update_post($the_page);
    }

    delete_option('my_plugin_page_id');
    add_option('my_plugin_page_id', $the_page_id);
}

function my_plugin_remove() {

    global $wpdb;

    $the_page_title = get_option("my_plugin_page_title");
    $the_page_name = get_option("my_plugin_page_name");
    //$the_page_title2 = get_option( "my_plugin_page_title2" );
    //$the_page_name2 = get_option( "my_plugin_page_name2" );
    //  the id of our page...
    $the_page_id = get_option('my_plugin_page_id');
    if ($the_page_id) {

        wp_delete_post($the_page_id); // this will trash, not delete
    }

    delete_option("my_plugin_page_title");
    delete_option("my_plugin_page_name");
    delete_option("my_plugin_page_id");
}

require_once "includes/products_cpt_metabox.php";

function ultracart_enqueue_scripts() {
    wp_enqueue_script('accounting', ULTRACART_PLUGIN_URL . 'accounting.min.js', false);
    wp_enqueue_script('cart_rest', ULTRACART_PLUGIN_URL . 'cart_rest_0.2.js', false);

    wp_enqueue_script('easyResponsiveTabs', ULTRACART_PLUGIN_URL . 'js/easyResponsiveTabs/easyResponsiveTabs.js', array('jquery'));
    ////////////
    wp_register_script(
            'ultracart', ULTRACART_PLUGIN_URL . 'ultracart.js', array('jquery')
    );
    wp_localize_script('ultracart', 'ultra_cart_vars', array('plugin_path' => ULTRACART_PLUGIN_URL,
        'site_url' => site_url(),
        'merchant_id' => get_option('merchant_id'),
        'proxy' => get_option('proxy'),
        'proxy_path' => get_option('proxy_path'))
    );
    wp_localize_script('ultracart', 'ultra_cart_theme', array(
        'cart_seperate_page' => get_option('cart_seperate_page'),
        'cart_visibility' => get_option('cart_visibility'),
        'cart_position' => get_option('cart_position'),
        'cart_loading_visible' => get_option('cart_loading_visible'),
        'cart_flying_effect' => get_option('cart_flying_effect'),
        'cart_coupon' => get_option('cart_coupon'),
            )
    );
    wp_localize_script('ultracart', 'ultra_cart_pages', array(
        'products_page' => get_page_link(get_option('ultracart_products_page')),
        'the_cart_page' => get_page_link(get_option('ultracart_cart_page')),
        'the_checkout_page' => get_page_link(get_option('ultracart_checkout_page')),
            )
    );
    $uc_payments = get_option('uc_payments');
    $uc_amazon = get_option('uc_amazon');
    wp_localize_script('ultracart', 'uc_payments', array(
        'paypal' => ($uc_payments['paypal']) ? $uc_payments['paypal'] : 0,
        'amazon' => ($uc_payments['amazon']) ? $uc_payments['amazon'] : 0,
        'amazon_merchant_id' => $uc_amazon['merchant_id'],
        'amazon_sandbox' => ($uc_amazon['sandbox']) ? $uc_amazon['sandbox'] : 0,
            )
    );
    $uc_subscription = get_option('uc_subscription');
    $uc_uc = $uc_subscription['uc'];
    $uc_sendy = $uc_subscription['sendy'];
    wp_localize_script('ultracart', 'ultra_subscription', array(
        'uc_sendy' => $uc_sendy['subscription'],
        'uc_uc' => $uc_uc['subscription'],
            )
    );

    //Jquery UI
    global $wp_scripts;
    $ui = $wp_scripts->query('jquery-ui-core');
    $protocol = is_ssl() ? 'https' : 'http';
    $ui_url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
    wp_enqueue_style('jquery-ui-smoothness', $ui_url, false, null);
    wp_enqueue_script('jquery-ui-autocomplete');
    //

    wp_enqueue_script('ultracart');
    ////////////
    wp_enqueue_style('cart_rest', ULTRACART_PLUGIN_URL . 'ultracart.css', false);
    wp_enqueue_style('cart_rest_responsive', ULTRACART_PLUGIN_URL . 'uc_responsive.css', false);
    wp_enqueue_script('bootstrap_js', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js', false);
    wp_enqueue_style('bootstrap_css', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css', false);
    wp_enqueue_style('bootstrap_theme_css', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css', false);
}

add_action('wp_enqueue_scripts', 'ultracart_enqueue_scripts');

function pages_override($content) {
    if (is_page('shopping-cart')) {
        $content .= file_get_contents(dirname(__FILE__) . '/cart-template.php');
    }
    return $content;
}

function load_checkout($template) {
    if (is_page('products')) {
        $template = dirname(__FILE__) . '/ultracart-products-page.php';
    }
    if (is_page('checkout')) {
        $template = dirname(__FILE__) . '/checkout-template.php';
    }
    if (is_page('shopping-cart')) {
        $template = dirname(__FILE__) . '/ultracart-page-template.php';
    }

    return $template;
}

function uc_load_single_checkout($single_template) {
    global $post;
    if ($post->post_type == 'product' || $post->post_type == 'post') {
        $single_template = dirname(__FILE__) . '/ultracart-product-page.php';
    }
    return $single_template;
}

add_filter('the_content', 'pages_override');
add_filter('template_include', 'load_checkout');
add_filter('single_template', 'uc_load_single_checkout');

//create custom plugin settings menu
add_action('admin_menu', 'ultracart_create_menu');

function ultracart_create_menu() {

    //create new top-level menu
    add_menu_page('Ultracart Plugin Settings', 'Ultracart', 'administrator', 'ultracart_settings_page', 'ultracart_settings_page', '');
    add_submenu_page('ultracart_settings_page', 'Ultracart Personalization', 'Personalization', 'manage_options', 'ultracart_personalization_page', 'ultracart_personalization_page', '');
    add_submenu_page('ultracart_settings_page', 'Ultracart Pages', 'Pages', 'manage_options', 'ultracart_pages_settings', 'ultracart_pages_settings', '');
    add_submenu_page('ultracart_settings_page', 'Subscription', 'Subscription', 'manage_options', 'ultracart_subscription_settings', 'ultracart_subscription_settings', '');

    add_submenu_page('ultracart_settings_page', 'Payments', 'Payments', 'manage_options', 'ultracart_payment_settings', 'ultracart_payment_settings', '');
    add_submenu_page('ultracart_settings_page', 'URL Settings', 'URL Settings', 'manage_options', 'ultracart_url_settings', 'ultracart_url_settings', '');

    //call register settings function
    add_action('admin_init', 'register_mysettings');
}

function register_mysettings() {
    //register our settings
    register_setting('ultracart_settings_group', 'merchant_id');
    register_setting('ultracart_settings_group', 'proxy');
    register_setting('ultracart_settings_group', 'proxy_path');

    register_setting('ultracart_personalization_group', 'cart_seperate_page');
    register_setting('ultracart_personalization_group', 'cart_visibility');
    register_setting('ultracart_personalization_group', 'cart_position');
    register_setting('ultracart_personalization_group', 'cart_loading_visible');
    register_setting('ultracart_personalization_group', 'cart_flying_effect');
    register_setting('ultracart_personalization_group', 'cart_coupon');

    register_setting('ultracart_pages_group', 'ultracart_cart_page');
    register_setting('ultracart_pages_group', 'ultracart_checkout_page');
    register_setting('ultracart_pages_group', 'ultracart_products_page');

    register_setting('ultracart_subscription_group', 'uc_subscription');

    register_setting('ultracart_payments_settings_group', 'uc_payments');
    register_setting('ultracart_payments_settings_group', 'uc_amazon');

    //register_setting( 'ultracart_siteurl_settings_group', 'siteurl' );
    //register_setting( 'ultracart_siteurl_settings_group', 'home' );
    //register_setting( 'ultracart_aweber_settings_group', 'proxy_path' );
}

function ultracart_settings_page() {
    ?>
    <div class="wrap">
        <h2>Ultracart Settings</h2>

        <form method="post" action="options.php">
    <?php settings_fields('ultracart_settings_group'); ?>
    <?php do_settings_sections('ultracart_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Merchant ID</th>
                    <td><input type="text" name="merchant_id" value="<?php echo get_option('merchant_id'); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Use Proxy</th>
                    <td><input type="radio" name="proxy" value="true" <?php if (get_option(proxy) == 'true') {
        echo 'checked="checked"';
    } ?>/> True 
                        <input type="radio" name="proxy" value="false" <?php if (get_option(proxy) == 'false') {
        echo 'checked="checked"';
    } ?> /> False </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Proxy Path</th>
                    <td><input type="text" name="proxy_path" value="<?php echo get_option('proxy_path'); ?>" /></td>
                </tr>
            </table>

    <?php submit_button(); ?>

        </form>
    </div>
        <?php } ?>
        <?php

        function i_print($array) {
            echo '<pre>';
            print_r($array);
            echo '</pre>';
        }

        function ultracart_personalization_page() {
            include 'includes/ultracart_personalization.php';
        }

        function ultracart_pages_settings() {
            include 'includes/ultracart_pages_settings.php';
        }

        function ultracart_subscription_settings() {
            include 'includes/ultracart_subscription.php';
        }

        function ultracart_payment_settings() {
            include 'includes/ultracart_payment.php';
        }

        function ultracart_url_settings() {
            include 'includes/ultracart-url-config.php';
        }

////AJAX
        add_action('wp_head', 'pluginname_ajaxurl');

        function pluginname_ajaxurl() {
            ?>
    <script type="text/javascript">
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    </script>
    <?php
}

function i_sendy_subscriber() {
    if (isset($_POST['action'])) {
        $uc_subscription = get_option('uc_subscription');
        $uc_sendy = $uc_subscription['sendy'];

        $sendy_url = $uc_sendy['url']; //'http://email.calltocontact.com/';
        $list = $uc_sendy['list']; //'NH3IZ4vDW0fCapPIh1CGNg';

        $name = $_POST['i_name'];
        $email = $_POST['i_email'];
        if (trim($name) == '') {
            echo 'Name required';
            exit;
        }
        if (trim($email) == '') {
            echo 'Email required';
            exit;
        }
        //subscribe
        $postdata = http_build_query(
                array(
                    'name' => $name,
                    'email' => $email,
                    'list' => $list,
                    'boolean' => 'true'
                )
        );
        $opts = array('http' => array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata));
        $context = stream_context_create($opts);
        $result = file_get_contents($sendy_url . 'subscribe', false, $context);
        //--------------------------------------------------//
        echo $result;
    } exit;
}

add_action('wp_ajax_i_sendy_subscriber', 'i_sendy_subscriber');
add_action('wp_ajax_nopriv_i_sendy_subscriber', 'i_sendy_subscriber');
?>