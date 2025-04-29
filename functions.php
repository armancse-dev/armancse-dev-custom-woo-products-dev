<?php



function escortwp_child_enqueue_styles() {
    // Load parent theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Load child theme styles
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'), filemtime(get_stylesheet_directory() . '/style.css'));


}
add_action('wp_enqueue_scripts', 'escortwp_child_enqueue_styles');




function enqueue_custom_scripts() {
    wp_enqueue_script('jquery'); // Load jQuery
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function load_dashicons_for_all_users() {
    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'load_dashicons_for_all_users');



function load_fontawesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', array(), '6.4.2');
}
add_action('wp_enqueue_scripts', 'load_fontawesome');


function escortwp_child_theme_setup() {
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'escortwp-child'),
    ));
}
add_action('after_setup_theme', 'escortwp_child_theme_setup');

add_action('woocommerce_account_navigation', function() {
    get_sidebar('right'); // Loads the right sidebar
});
function load_font_awesome_on_my_account() {
    if (is_account_page()) { // Load only on My Account page
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
    }
}
add_action('wp_enqueue_scripts', 'load_font_awesome_on_my_account');





function refresh_premium_profiles() {
    $taxonomy_profile_url = 'escort'; // Replace with actual post type
    
    $taxonomy_location_url = 'escorts'; // Change this to the actual taxonomy slug
    
    // Get country and city from AJAX request
    $country = isset($_GET['country']) ? sanitize_text_field($_GET['country']) : '';
    $city = isset($_GET['city']) ? sanitize_text_field($_GET['city']) : '';
    
    
    

    $meta_query = array(
        array(
            'key' => 'premium',
            'value' => '1',
            'compare' => '=',
            'type' => 'NUMERIC'
        )
    );

    // Add country filter if available
    if (!empty($country)) {
        $meta_query[] = array(
            'key' => 'country',
            'value' => $country,
            'compare' => 'LIKE'
        );
    }

    // Add city filter if available
    if (!empty($city)) {
        $meta_query[] = array(
            'key' => 'city',
            'value' => $city,
            'compare' => 'LIKE'
        );
    }

    $args = array(
        'post_type' => $taxonomy_profile_url,
        'orderby' => 'rand',
        'meta_query' => $meta_query,
        'posts_per_page' => get_option("frontpageshowpremiumcols") * 5,
        'cache_results' => false,
        'no_found_rows' => true,
    );

    $premium_profiles = new WP_Query($args);

    if ($premium_profiles->have_posts()) {
        ob_start();
        while ($premium_profiles->have_posts()) :
            $premium_profiles->the_post();
            include get_template_directory() . '/loop-show-profile.php';
        endwhile;
        wp_reset_postdata();
        echo ob_get_clean();
    } else {
        echo '<p>No premium profiles found.</p>';
    }

    wp_die();
}

add_action('wp_ajax_refresh_premium_profiles', 'refresh_premium_profiles');
add_action('wp_ajax_nopriv_refresh_premium_profiles', 'refresh_premium_profiles');


function escortwp_auto_rotation($query) {
    if (!is_admin() && $query->is_main_query() && (is_tax() || is_category())) {
        $query->set('orderby', 'rand'); // Auto-rotation on taxonomy pages
    }
}
add_action('pre_get_posts', 'escortwp_auto_rotation');





//new code 250

// register button on my account page

function add_register_button_my_account() {
    if (!is_user_logged_in()) {
        echo '<a href="https://iceland-escort.com/registration/" class="button custom-register-button">
            REGISTER FOR A FREE ACCOUNT
        </a>';
    }
}
add_action('woocommerce_before_customer_login_form', 'add_register_button_my_account');


// Tereawallet added on my account

function add_mini_wallet_to_my_account() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $allowed_roles = array('subscriber', 'escort'); // Define allowed roles

        if (array_intersect($allowed_roles, $user->roles)) {
            $user_id = $user->ID;
            $wallet_balance = woo_wallet()->wallet->get_wallet_balance($user_id, false); // Get raw balance without currency

            echo '<a class="woo-wallet-menu-contents custom-wallet" title="Current wallet balance" href="https://iceland-escort.com/my-wallet/">
                <span dir="rtl" class="woo-wallet-icon-wallet"></span>&nbsp;
                <span class="woocommerce-Price-amount amount"><bdi>' . esc_html(number_format($wallet_balance, 2)) . '&nbsp;<span class="woocommerce-Price-currencySymbol">€</span></bdi></span>
            </a>';
        }
    }
}
add_action('woocommerce_account_navigation', 'add_mini_wallet_to_my_account');


function add_custom_js_to_my_account_page() {
    // Check if we are on the "My Account" page (you can adjust the page ID or URL check if needed)
    if (is_account_page()) {
        ?>
        <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Select the custom wallet element and the sidebar-right container
            var customWallet = document.querySelector('.woo-wallet-menu-contents.custom-wallet');
            var sidebarRight = document.querySelector('.sidebar-right');

            // Check if both elements exist
            if (customWallet && sidebarRight) {

                // Create a media query for both mobile and desktop (no size restriction)
                var mediaQuery = window.matchMedia('(max-width: 768px), (min-width: 769px)');

                // Function to move the custom wallet to the top
                function moveWallet(e) {
                    if (e.matches) {
                        // Move the custom wallet to the top of the sidebar-right
                        sidebarRight.insertBefore(customWallet, sidebarRight.firstChild);
                    } else {
                        // Reset the custom wallet position if not on the targeted screen size
                        sidebarRight.appendChild(customWallet);
                    }
                }

                // Run the function once on load
                moveWallet(mediaQuery);

                // Listen for changes in screen size
                mediaQuery.addListener(moveWallet);
            }
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'add_custom_js_to_my_account_page');


// code for 250

function add_missing_payment_plans() {
    $payment_plans = get_option('payment_plans', []);

    // These are your correct product IDs and plan settings
    $new_plans = [
        'premium_1day'   => ['price' => 30,  'duration' => 1,  'woo_product_id' => 188],
        'premium_3days'  => ['price' => 70,  'duration' => 3,  'woo_product_id' => 1791],
        'premium_1week'  => ['price' => 150, 'duration' => 7,  'woo_product_id' => 1839],
        'premium_1month' => ['price' => 400, 'duration' => 30, 'woo_product_id' => 1911],

        'featured_1day'   => ['price' => 50,  'duration' => 1,  'woo_product_id' => 1842],
        'featured_3days'  => ['price' => 130, 'duration' => 3,  'woo_product_id' => 1843],
        'featured_1week'  => ['price' => 250, 'duration' => 7,  'woo_product_id' => 1845], // ← Make sure this is correct
        'featured_1month' => ['price' => 700, 'duration' => 30, 'woo_product_id' => 1912]
    ];

    // Overwrite all values to ensure consistency
    foreach ($new_plans as $key => $plan) {
        $payment_plans[$key] = $plan;
    }

    update_option('payment_plans', $payment_plans);
}

// 🚀 Trigger this once on admin init to force update
// add_action('admin_init', 'add_missing_payment_plans');


//24-04-25 feature and premium value update

// Reusable function to extend featured/premium expiry based on product
function escort_extend_expiry_based_on_product($profile_id, $duration_days, $type = 'premium') {
    if (!$profile_id || !in_array($type, ['premium', 'featured'])) return;

    $meta_key = $type . '_expire';
    $current_expire = (int) get_post_meta($profile_id, $meta_key, true);
    $now = time();

    // If current expire is in the future, extend from that date; otherwise from now
    $base_time = ($current_expire && $current_expire > $now) ? $current_expire : $now;
    $new_expire = strtotime("+{$duration_days} days", $base_time);

    update_post_meta($profile_id, $type, 1); // Ensure it's active
    update_post_meta($profile_id, $meta_key, $new_expire);
}

// Main WooCommerce order hook
add_action('woocommerce_order_status_completed', 'escort_handle_order_expiry_update');
function escort_handle_order_expiry_update($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    $user_id = $order->get_user_id();
    if (!$user_id) return;

    // Get escort profile ID for user (assuming one-to-one)
    $escort_profiles = get_posts([
        'post_type' => 'escort',
        'post_status' => 'publish',
        'numberposts' => 1,
        'author' => $user_id,
    ]);

    if (empty($escort_profiles)) return;
    $profile_id = $escort_profiles[0]->ID;

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if (!$product) continue;

        $product_id = $product->get_id();

        // Match based on known durations
        $duration_map = [
            // Premium
            188 => ['days' => 1, 'type' => 'premium'],
            1791 => ['days' => 3, 'type' => 'premium'],
            1839 => ['days' => 7, 'type' => 'premium'],
            1911 => ['days' => 30, 'type' => 'premium'],

            // Featured
            1842 => ['days' => 1, 'type' => 'featured'],
            1843 => ['days' => 3, 'type' => 'featured'],
            1845 => ['days' => 7, 'type' => 'featured'],
            1912 => ['days' => 30, 'type' => 'featured'],
        ];

        if (isset($duration_map[$product_id])) {
            $info = $duration_map[$product_id];
            escort_extend_expiry_based_on_product($profile_id, $info['days'], $info['type']);
        }
    }
}



//automatic reset functions

add_action('wp_loaded', 'check_and_reset_expired_escort_fields');

function check_and_reset_expired_escort_fields() {
    $args = [
        'post_type'      => 'escort',
        'post_status'    => 'publish',
        'meta_query'     => [
            'relation' => 'OR',
            ['key' => 'featured_expire', 'compare' => 'EXISTS'],
            ['key' => 'premium_expire', 'compare' => 'EXISTS'],
        ],
        'posts_per_page' => -1,
    ];

    $escorts = get_posts($args);
    $now = current_time('timestamp');

    foreach ($escorts as $escort) {
        $escort_id = $escort->ID;

        $featured_expire = (int) get_post_meta($escort_id, 'featured_expire', true);
        $premium_expire = (int) get_post_meta($escort_id, 'premium_expire', true);

        if ($featured_expire && $featured_expire < $now) {
            update_post_meta($escort_id, 'featured', 0);
        }

        if ($premium_expire && $premium_expire < $now) {
            update_post_meta($escort_id, 'premium', 0);
        }
    }
}

add_action('escort_expire_check_daily', 'check_and_reset_expired_escort_fields');

function escort_schedule_expiry_check() {
    if (!wp_next_scheduled('escort_expire_check_daily')) {
        wp_schedule_event(time(), 'daily', 'escort_expire_check_daily');
    }
}
add_action('wp', 'escort_schedule_expiry_check');




//Checking for Duplicate Phone Numbers for blacklist
function check_duplicate_phone() {
    if (!isset($_POST['phone'])) {
        wp_send_json_error('No phone number provided.');
    }

    global $wpdb;
    $phone = sanitize_text_field($_POST['phone']);

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'bcphone' AND meta_value = %s",
        $phone
    ));

    if ($exists > 0) {
        echo 'exists';
    } else {
        echo 'not_exists';
    }
    wp_die();
}
add_action('wp_ajax_check_duplicate_phone', 'check_duplicate_phone');
add_action('wp_ajax_nopriv_check_duplicate_phone', 'check_duplicate_phone');



//user verified
function show_fluent_form_for_verified_users($atts) {
    $atts = shortcode_atts(['id' => ''], $atts);
    $form_id = intval($atts['id']);
    $current_user_id = get_current_user_id();

    if (!$current_user_id) {
        return '<p>You must be Verified in to view this form.</p>';
    }

    $args = [
        'post_type'   => 'escort',
        'author'      => $current_user_id,
        'meta_query'  => [
            [
                'key'     => 'verified',
                'value'   => '1',
                'compare' => '='
            ]
        ]
    ];

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>You must be verified to access this form.</p>';
    }

    return do_shortcode("[fluentform id='{$form_id}']");
}
add_shortcode('verified_fluent_form', 'show_fluent_form_for_verified_users');


// review system
// ========================
// Enable comments on escort CPT
function enable_comments_on_escort_cpt() {
    add_post_type_support('escort', 'comments');
}
add_action('init', 'enable_comments_on_escort_cpt');

// Keep comments open on escort posts
function keep_comments_open_for_escort($open, $post_id) {
    $post = get_post($post_id);
    return ($post && $post->post_type === 'escort') ? true : $open;
}
add_filter('comments_open', 'keep_comments_open_for_escort', 10, 2);

// Save escort rating
function save_escort_review_rating($comment_id) {
    if (
        isset($_POST['escort_rating_nonce_field']) &&
        wp_verify_nonce($_POST['escort_rating_nonce_field'], 'escort_rating_nonce') &&
        isset($_POST['rating']) &&
        in_array($_POST['rating'], ['1', '2', '3', '4', '5'])
    ) {
        add_comment_meta($comment_id, 'escort_rating', intval($_POST['rating']));
    }
}
add_action('comment_post', 'save_escort_review_rating');














?>
