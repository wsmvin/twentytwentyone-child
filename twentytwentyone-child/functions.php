<?php

// Підключио стилі та скрипти

function my_theme_enqueue_styles() {
    wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', array(), '3.6.0', true);
    wp_enqueue_style('twentytwentythree', get_stylesheet_uri(), array(), filemtime(get_stylesheet_directory() . '/style.css'), false);

    wp_enqueue_script('ajax-load', get_stylesheet_directory_uri(). '/assets/ajax-load.js', 'jquery', '', true);
    wp_localize_script( 'ajax-load', 'customData', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
// Функція для перебору і виведення постів
function loop_posts($query) {
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $post_class = implode(' ', get_post_class());
        $post_permalink = esc_url(get_permalink());
        $post_title = get_the_title();
        $post_thumbnail = get_the_post_thumbnail($post_id, 'post-thumbnail');

        echo '<article id="post-' . esc_attr($post_id) . '" class="my-article ' . esc_attr($post_class) . '">';
        echo '<h2 class="entry-title ">' . esc_html($post_title) . '</h2>';
        echo  $post_thumbnail;
        echo '<a href="' . esc_url($post_permalink) . '" class="button">Read More</a>';
        echo '</article><!-- #post-' . esc_attr($post_id) . ' -->';
    }
}
// Додати шорткод [loop type="services" title="Best posts"]
function loop_shortcode($atts) {
    $atts = shortcode_atts(array(
        'type' => 'services',
        'title' => 'Best posts'
    ), $atts, 'loop');

    ob_start();

    $args = array(
        'post_type' => $atts['type'],
        'posts_per_page' => 10
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="loop-wrapper">';
        echo '<h2>' . esc_html($atts['title']) . '</h2>';

        loop_posts($query);

        echo '</div>';

        if ($query->found_posts > 10) {
            echo '<div class="load-more-wrapper">';
            echo '<button id="load-more" data-type="' . esc_attr($atts['type']) . '">Load More</button>';
            echo '</div>';
        }
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('loop', 'loop_shortcode');

// Обробник AJAX-запиту для завантаження наступних 10 постів
function load_more_posts() {
    $type = $_POST['type'];
    $page = $_POST['page'];

    $args = array(
        'post_type' => $type,
        'posts_per_page' => 10,
        'paged' => $page,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        loop_posts($query);
    }

    // Перевіряємо, чи є ще пости для підвантаження
    $next_page = $page + 1;
    $next_query = new WP_Query(array(
        'post_type' => $type,
        'posts_per_page' => 10,
        'paged' => $next_page,
    ));

    if (!$next_query->have_posts()) {
        echo 'no_more_posts'; // Виводимо повідомлення, якщо більше немає постів
    }

    wp_die();
}
add_action('wp_ajax_load_more_posts', 'load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'load_more_posts');

