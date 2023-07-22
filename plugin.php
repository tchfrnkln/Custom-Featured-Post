<?php

/**
 * Plugin Name: Custom Featured Posts
 * Description: Allows site administrators to select a specific category of posts to be featured on the homepage.
 * Version: 1.0
 * Author: Franklin
 */

// Activation hook
register_activation_hook(__FILE__, 'custom_featured_posts_activate');


function custom_featured_posts_settings_page() {
    add_options_page(
        'Custom Featured Posts Settings',
        'Custom Featured Posts',
        'manage_options',
        'custom_featured_posts',
        'custom_featured_posts_render_settings_page'
    );
}

function custom_featured_posts_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings if form is submitted
    if (isset($_POST['custom_featured_posts_save_settings'])) {
        check_admin_referer('custom_featured_posts_settings_nonce');

        $selected_category = sanitize_text_field($_POST['custom_featured_posts_category']);
        update_option('custom_featured_posts_category', $selected_category);

        echo '<div class="updated"><p>' . __('Settings saved.', 'custom-featured-posts') . '</p></div>';
    }

    // Display the settings form
    $selected_category = get_option('custom_featured_posts_category', 0);
    $categories = get_categories();

    echo '<div class="wrap">';
    echo '<h1>' . __('Custom Featured Posts Settings', 'custom-featured-posts') . '</h1>';
    echo '<form method="post">';
    wp_nonce_field('custom_featured_posts_settings_nonce');
    echo '<label for="custom_featured_posts_category">' . __('Select Featured Category:', 'custom-featured-posts') . '</label>';
    echo '<select id="custom_featured_posts_category" name="custom_featured_posts_category">';
    foreach ($categories as $category) {
        echo '<option value="' . esc_attr($category->term_id) . '" ' . selected($category->term_id, $selected_category, false) . '>' . esc_html($category->name) . '</option>';
    }
    echo '</select>';
    echo '<br><br>';
    echo '<input type="submit" name="custom_featured_posts_save_settings" class="button-primary" value="' . __('Save Settings', 'custom-featured-posts') . '">';
    echo '</form>';
    echo '</div>';
}

add_action('admin_menu', 'custom_featured_posts_settings_page');

function custom_featured_posts_get_featured_posts($number_of_posts)
{
    $category_id = get_option('custom_featured_posts_category', 0);
    $args = array(
        'posts_per_page' => $number_of_posts,
        'cat' => $category_id,
    );
    $featured_posts = get_posts($args);
    return $featured_posts;
}

function custom_featured_posts_display_featured_posts()
{
    $number_of_posts = get_option('custom_featured_posts_number_of_posts', 3); // Default to 3 if not set in the settings
    $featured_posts = custom_featured_posts_get_featured_posts($number_of_posts);

    if (empty($featured_posts)) {
        return; // No featured posts found
    }

    echo '<div class="custom-featured-posts">';
    foreach ($featured_posts as $post) {
        setup_postdata($post);
        echo '<div class="custom-featured-post">';
        if (has_post_thumbnail($post)) {
            echo '<div class="custom-featured-thumbnail">' . get_the_post_thumbnail($post, 'medium') . '</div>';
        }
        echo '<h3 class="custom-featured-title">' . get_the_title($post) . '</h3>';
        echo '<div class="custom-featured-excerpt">' . get_the_excerpt($post) . '</div>';
        echo '</div>';
    }
    echo '</div>';
}

add_action('homepage', 'custom_featured_posts_display_featured_posts');


function custom_featured_posts_enqueue_styles()
{
    wp_enqueue_style('custom-featured-posts-styles', plugin_dir_url(__FILE__) . 'css/custom_featured_posts.css');
}

// Hook the CSS enqueue function to WordPress' wp_enqueue_scripts action
add_action('wp_enqueue_scripts', 'custom_featured_posts_enqueue_styles');

