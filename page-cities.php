<?php
/* Template Name: Cities Table */
get_header();
?>

<div id="cities-search">
    <input type="text" id="search" placeholder="Search cities...">
    <button id="search-button">Search</button>
</div>

<div id="cities-table">
    <table>
        <thead>
            <tr>
                <th>Country</th>
                <th>City</th>
                <th>Temperature</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $results = $wpdb->get_results("
                SELECT p.ID, p.post_title, t.name AS country
                FROM $wpdb->posts p
                LEFT JOIN $wpdb->term_relationships tr ON (p.ID = tr.object_id)
                LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'countries')
                LEFT JOIN $wpdb->terms t ON (tt.term_id = t.term_id)
                WHERE p.post_type = 'cities' AND p.post_status = 'publish'
            ");

            foreach ($results as $city) {
                $latitude = get_post_meta($city->ID, '_latitude', true);
                $longitude = get_post_meta($city->ID, '_longitude', true);

                // Fetch temperature using OpenWeatherMap API
                $api_key = 'a0c0b7d43d3daa7f4b8468da74423195';
                $response = wp_remote_get("https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&units=metric&appid={$api_key}");
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $temperature = $data['main']['temp'] ?? 'N/A';

                echo "<tr>
                        <td>{$city->country}</td>
                        <td>{$city->post_title}</td>
                        <td>{$temperature}°C</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php get_footer(); ?>
