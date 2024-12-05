<?php

/**
 * Storefront automatically loads the core CSS even if using a child theme as it is more efficient
 * than @importing it in the child theme style.css file.
 *
 * Uncomment the line below if you'd like to disable the Storefront Core CSS.
 *
 * If you don't plan to dequeue the Storefront Core CSS you can remove the subsequent line and as well
 * as the sf_child_theme_dequeue_style() function declaration.
 */
//add_action( 'wp_enqueue_scripts', 'sf_child_theme_dequeue_style', 999 );

/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}

/**
 * Note: DO NOT! alter or remove the code above this text and only add your custom PHP functions below this text.
 */

/*Custom post type for cities */
function create_custom_post_type_cities() {
    register_post_type('cities', [
        'label' => 'Cities',
        'public' => true,
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-location',
        'rewrite' => ['slug' => 'cities'],
    ]);
}
add_action('init', 'create_custom_post_type_cities');

/* custom taxonomy country*/
function create_custom_taxonomy_countries() {
    register_taxonomy('countries', 'cities', [
        'label' => 'Countries',
        'hierarchical' => true,
        'rewrite' => ['slug' => 'countries'],
    ]);
}
add_action('init', 'create_custom_taxonomy_countries');

/* longtitude latitude custom field */
function add_city_meta_box() {
    add_meta_box(
        'city_meta_box',
        'City Coordinates',
        'render_city_meta_box',
        'cities',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_city_meta_box');

function render_city_meta_box($post) {
    $latitude = get_post_meta($post->ID, '_latitude', true);
    $longitude = get_post_meta($post->ID, '_longitude', true);
    ?>
    <label for="latitude">Latitude:</label>
    <input type="text" name="latitude" value="<?php echo esc_attr($latitude); ?>" />
    <br>
    <label for="longitude">Longitude:</label>
    <input type="text" name="longitude" value="<?php echo esc_attr($longitude); ?>" />
    <?php
}

function save_city_meta_box_data($post_id) {
    if (array_key_exists('latitude', $_POST)) {
        update_post_meta($post_id, '_latitude', sanitize_text_field($_POST['latitude']));
    }
    if (array_key_exists('longitude', $_POST)) {
        update_post_meta($post_id, '_longitude', sanitize_text_field($_POST['longitude']));
    }
}
add_action('save_post', 'save_city_meta_box_data');

/* search AJAX*/
function enqueue_cities_scripts() {
    wp_enqueue_script('cities-ajax', get_stylesheet_directory_uri() . '/js/cities-ajax.js', ['jquery'], null, true);
    wp_localize_script('cities-ajax', 'citiesAjax', ['ajax_url' => admin_url('admin-ajax.php')]);
}
add_action('wp_enqueue_scripts', 'enqueue_cities_scripts');

/* AJAX Handler*/
function ajax_search_cities() {
    global $wpdb;
    $search = sanitize_text_field($_POST['search']);
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, t.name AS country
        FROM $wpdb->posts p
        LEFT JOIN $wpdb->term_relationships tr ON (p.ID = tr.object_id)
        LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'countries')
        LEFT JOIN $wpdb->terms t ON (tt.term_id = t.term_id)
        WHERE p.post_type = 'cities' AND p.post_status = 'publish' AND p.post_title LIKE %s
    ", '%' . $wpdb->esc_like($search) . '%'));

    foreach ($results as $city) {
        $city->temperature = 'N/A'; // Replace with API logic if needed
    }

    wp_send_json($results);
}
add_action('wp_ajax_search_cities', 'ajax_search_cities');
add_action('wp_ajax_nopriv_search_cities', 'ajax_search_cities');
