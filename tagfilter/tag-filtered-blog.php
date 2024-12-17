<?php
/**
 * Plugin Name: Tag Filtered Blog
 * Description: A plugin to display blog posts in a 3x3 grid with tag filtering.
 * Version: 1.1
 * Author: Osio_Efren
 */

// Enqueue styles and scripts
function tag_filtered_blog_enqueue_scripts() {
    wp_enqueue_style('tag-filtered-blog-style', plugins_url('css/styles.css', __FILE__));
    wp_enqueue_script('tag-filtered-blog-script', plugins_url('js/scripts.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'tag_filtered_blog_enqueue_scripts');

// Create settings page
function tag_filtered_blog_create_settings_page() {
    add_options_page(
        'Tag Filtered Blog Settings',
        'Tag Filtered Blog',
        'manage_options',
        'tag-filtered-blog',
        'tag_filtered_blog_settings_page_html'
    );
}
add_action('admin_menu', 'tag_filtered_blog_create_settings_page');

function tag_filtered_blog_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error('tag_filtered_blog_messages', 'tag_filtered_blog_message', __('Settings Saved', 'tag_filtered_blog'), 'updated');
    }

    settings_errors('tag_filtered_blog_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('tag_filtered_blog');
            do_settings_sections('tag_filtered_blog');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function tag_filtered_blog_settings_init() {
    register_setting('tag_filtered_blog', 'tag_filtered_blog_options');

    add_settings_section(
        'tag_filtered_blog_section',
        __('Select Tags to Display', 'tag_filtered_blog'),
        'tag_filtered_blog_section_cb',
        'tag_filtered_blog'
    );

    add_settings_field(
        'tag_filtered_blog_tags',
        __('Tags', 'tag_filtered_blog'),
        'tag_filtered_blog_tags_cb',
        'tag_filtered_blog',
        'tag_filtered_blog_section'
    );
}
add_action('admin_init', 'tag_filtered_blog_settings_init');

function tag_filtered_blog_section_cb($args) {
    echo '<p>' . __('Select the tags you want to display in the filter:', 'tag_filtered_blog') . '</p>';
}

function tag_filtered_blog_tags_cb($args) {
    $options = get_option('tag_filtered_blog_options');
    $selected_tags = isset($options['tags']) ? $options['tags'] : array();
    $tags = get_tags();
    foreach ($tags as $tag) {
        echo '<input type="checkbox" name="tag_filtered_blog_options[tags][]" value="' . esc_attr($tag->term_id) . '"' . (in_array($tag->term_id, $selected_tags) ? ' checked' : '') . '> ' . esc_html($tag->name) . '<br>';
    }
}

function tag_filtered_blog_shortcode() {
    $options = get_option('tag_filtered_blog_options');
    $selected_tags = isset($options['tags']) ? $options['tags'] : array();

    $output = '<div class="filter-container">';
    $output .= '<div class="filter-buttons">';
    $output .= '<button onclick="filterPosts(\'all\', event)" class="filter-button">All</button>';

    if (!empty($selected_tags)) {
        foreach ($selected_tags as $tag_id) {
            $tag = get_tag($tag_id);
            if ($tag) {
                $output .= '<button onclick="filterPosts(\'' . $tag->slug . '\', event)" class="filter-button">' . $tag->name . '</button>';
            }
        }
    }

    $output .= '</div>';
    $output .= '</div>';

    $output .= '<div id="blogPosts" class="grid-container">';

    $query_args = array(
        'posts_per_page' => 99,
        'tag__in' => $selected_tags, 
    );

    $query = new WP_Query($query_args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $output .= '<div class="post" data-tags="' . esc_attr(implode(' ', wp_get_post_tags(get_the_ID(), array('fields' => 'slugs')))) . '">';
            $output .= '<h2 style="min-height: 75px;">' . get_the_title() . '</h2>';

            if (has_post_thumbnail()) {
                $output .= '<div class="post-thumbnail">' . get_the_post_thumbnail(get_the_ID(), 'medium') . '</div>';
            }

            $output .= '<div class="post-tags">';
            $output .= get_the_tag_list('<ul class="tag-list"><li class="tag-item">', '</li><span class="semi-colon">;</span><li class="tag-item">', '</li></ul>');

            $output .= '</div>';

            $output .= '<div class="post-author">';
            $output .= 'By ' . get_the_author();
            $output .= '</div>';

            $output .= '<div class="post-excerpt">' . get_the_excerpt() . '</div>';

            $output .= '<a href="' . get_permalink() . '" class="read-more-button">Read More</a>';
            $output .= '</div>';
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>No posts found</p>';
    }

    $output .= '</div>';

    return $output;
}

add_shortcode('tag_filtered_blog', 'tag_filtered_blog_shortcode');
?>
