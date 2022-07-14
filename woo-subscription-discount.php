<?php

/**
 * Plugin Name: Woo Subscription Discount
 * Plugin URI: https://github.com/mahmudhaisan/woo-solutions
 * Description: Woo Subscription Discount 
 * Author: Mahmud haisan                                     
 * Author URI: https://github.com/mahmudhaisan
 * Developer: Mahmud Haisan
 * Developer URI: https://github.com/mahmudhaisan
 * Text Domain: woosolutions493
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */




if (!defined('ABSPATH')) {
    die('are you cheating');
}

define("PLUGINS_PATHS_ASSETS", plugin_dir_url(__FILE__) . 'assets/');
define("PLUGINS_PATHS", plugin_dir_url(__FILE__) . '');

function woo_subscription_enqueue_files() {

    wp_enqueue_style('woo-sub-discount-style', PLUGINS_PATHS_ASSETS . 'css/style.css');
    wp_enqueue_script('woo-sub-discount-radial-progress-bar', PLUGINS_PATHS_ASSETS . 'js/radial-progress-bar.js', array('jquery'));
    wp_enqueue_script('woo-sub-discount-jquery', PLUGINS_PATHS_ASSETS . 'js/script.js', array('jquery'));
}


add_action('wp_enqueue_scripts', 'woo_subscription_enqueue_files');


function woo_show_frontend() { ?>

    <?php }

add_shortcode('woo-discount', 'woo_show_frontend');



//discounted users function
function total_orders_by_user() {
    global $wpdb;
    global $current_users_total_order;

    //orders stats
    $order_stats_sql = 'SELECT * FROM `wp_wc_order_stats`';
    $orders_stats =  $wpdb->get_results($order_stats_sql);
    $current_registered_user_id = get_current_user_id();

    // customer id array
    $customer_id_list = [];

    foreach ($orders_stats as $order_stat) {
        $order_id_list = $order_stat->order_id;
        $customer_id_list[] = $order_stat->customer_id;
        // echo $order_id_list . ' ';
    }

    foreach ($customer_id_list as $single_customer_id) {
        // echo $single_customer_id;
    }

    $duplicate_items_count = array_count_values($customer_id_list);

    //orders stats
    $users_stats_sql = "SELECT `customer_id` FROM `wp_wc_customer_lookup` WHERE `user_id` = '$current_registered_user_id'";
    $users_stats_query =  $wpdb->get_results($users_stats_sql);
    $user_customer_id = $users_stats_query[0]->customer_id; // return user's customer id
    $current_users_total_order = $duplicate_items_count[$user_customer_id];
    return $current_users_total_order;
}

add_shortcode('my_shortcode', 'total_orders_by_user');






add_filter('woocommerce_calculated_total', 'custom_calculated_total', 10, 2);


function custom_calculated_total($total, $cart) {
    //getting total orders
    $total_orders_by_user =  total_orders_by_user();
    //checking if total orders of current user is returning a int if it divides by 10
    $discount_interval_eligibility_check = $total_orders_by_user / 10;

    if ($discount_interval_eligibility_check == 0) {
        $discount_interval_eligibility_check = 1.5;
    } else {
        $discount_interval_eligibility_check = $discount_interval_eligibility_check;
    }

    if (current_user_can('subscriber')) {

        if (is_int($discount_interval_eligibility_check)) {
            $user_order_total_ammount = 0;
        }
        if (is_float($discount_interval_eligibility_check)) {
            $user_order_total_ammount = $total;
        }
    } else {
        $user_order_total_ammount = $total;
    }


    return $user_order_total_ammount;
}









/*
 * Step 1. Add Link (Tab) to My Account menu
 */
add_filter('woocommerce_account_menu_items', 'woo493_add_links_account_page', 40);
function woo493_add_links_account_page($menu_links) {
    if (current_user_can('subscriber')) {

        $menu_links = array_slice($menu_links, 0, 3, true)
            + array('subscriber-discount' => 'Subscriber')
            + array_slice($menu_links, 3, NULL, true);
    }
    return $menu_links;
}


/*
 * Step 2. Register Permalink Endpoint
 */
add_action('init', 'woo493_endpoints');
function woo493_endpoints() {

    add_rewrite_endpoint('subscriber-discount', EP_PAGES);
}



/*
 * Step 3. Content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
 */
add_action('woocommerce_account_subscriber-discount_endpoint', 'woo493_endpoint_contents');
function woo493_endpoint_contents() {
    global $woocommerce;
    // global $total_orders_percentage;
    if (current_user_can('subscriber')) {

        $total_orders_percentage_actual =  total_orders_by_user() * 10;

        $total_orders_percentage = substr($total_orders_percentage_actual, -2);

        $total_orders_percentage_str = strval($total_orders_percentage);


        if ($total_orders_percentage_str == '00') {
            $total_orders_percentage = 100;
        }

        if ($total_orders_percentage_actual == 0) {
            $total_orders_percentage = 0;
        }


        // var_dump($total_orders_percentage_str);


        // echo $total_orders_percentage;
        // print_r($total_orders_percentage);

        if ($total_orders_percentage == 0) {
            $total_orders_percentage = 0;
        } else {

            if ($total_orders_percentage % 100 == 0) {
                $total_orders_percentage = 100;
            }
        }

        if ($total_orders_percentage == 0) {
            $item_number_show = 0;
        } else {

            $item_number_show = substr(total_orders_by_user(), -1);
        }


        if ($total_orders_percentage == 100) {
            $item_number_show = $total_orders_percentage  / 10;
        }

        // print_r($item_number_show);

        // $cart_subtotal = $woocommerce->cart->get_cart_total();
        // echo $cart_subtotal;
        // echo $total_orders_percentage_int_check;
        // echo $total_orders_percentage_int_check;
        // echo plugin_dir_url(__FILE__);



    ?>


        <div class="card-circle">
            <div class="percent">
                <svg>
                    <circle cx="105" cy="105" r="100"></circle>
                    <circle cx="105" cy="105" r="100" style="--percent:<?php echo  $total_orders_percentage ?>"></circle>
                </svg>
                <div class="number">
                    <h3><?php echo  $item_number_show; ?><span> Item</span></h3>
                </div>
            </div>
            <div class="title">
            </div>
        </div>
<?php
    }
}
