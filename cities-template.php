<?php
/* Template Name: Cities Table */

get_header(); ?>

<div class="cities-table-container">
    <h1>Cities and Temperatures</h1>
    <input type="text" id="city-search" placeholder="Search for cities...">
    <div id="before-table">
        <?php do_action('before_cities_table'); ?>
    </div>
    <table id="cities-table">
        <thead>
            <tr>
                <th>Country</th>
                <th>City</th>
                <th>Temperature (°C)</th>
            </tr>
        </thead>
        <tbody>
            <!-- Rows will be dynamically populated -->
        </tbody>
    </table>
    <div id="after-table">
        <?php do_action('after_cities_table'); ?>
    </div>
</div>

<?php get_footer(); ?>
