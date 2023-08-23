<?php

/**********************************************************************************
 Set Default Featured Image For Post Type Post If Featured Image Not Available
***********************************************************************************/

function set_default_featured_image()
{
    global $post;
    if ($post) {
        $post_type = get_post_type($post->ID);
        if ($post_type === 'guide') {
            $already_has_thumb = has_post_thumbnail($post->ID);
            if (!$already_has_thumb) {
                set_post_thumbnail($post->ID, '226'); // Find attachment media id by right clicking the image in the Media library and selecting inspect element. Look for the data-id number. This number is then added to the post id.
            }
        }
    }
}
add_action('the_post', 'set_default_featured_image');
add_action('save_post', 'set_default_featured_image');
add_action('draft_to_publish', 'set_default_featured_image');
add_action('new_to_publish', 'set_default_featured_image');
add_action('pending_to_publish', 'set_default_featured_image');
add_action('future_to_publish', 'set_default_featured_image');



/*************************************
 Trim Content By Character Count
**************************************/

function trim_content($excerpt, $maxCharacter = '50', $htmlTag = '', $print = true)
{
    if ($print == true) {
        if ($htmlTag) {
            echo '<' . $htmlTag . '>';
        }
        if ($excerpt) {
            echo (strlen(strip_tags($excerpt)) > $maxCharacter) ? substr(strip_tags($excerpt), 0, $maxCharacter) . "..." : substr(strip_tags($excerpt), 0, $maxCharacter);
        }
        if ($htmlTag) {
            echo '</' . $htmlTag . '>';
        }
    } else {
        $content = '';
        if ($htmlTag) {
            $content .= '<' . $htmlTag . '>';
        }
        if ($excerpt) {
            $content .= (strlen(strip_tags($excerpt)) > $maxCharacter) ? substr(strip_tags($excerpt), 0, $maxCharacter) . "..." : substr(strip_tags($excerpt), 0, $maxCharacter);
        }
        if ($htmlTag) {
            $content .= '</' . $htmlTag . '>';
        }
        return $content;
    }
}



/****************************
 Add SVG upload support
*****************************/

function add_svg_to_upload_mimes($uploadMimes)
{
    $uploadMimes['svg']  = 'image/svg+xml';
    $uploadMimes['svgz'] = 'image/svg+xml';

    return $uploadMimes;
}
add_filter('upload_mimes', 'add_svg_to_upload_mimes');



/******************************************
 Pre Print Pre Function For Debugging
*******************************************/

function pre_print_pre($data, $exit = false)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    if ($exit) {
        exit();
    }
}



/***************************************
 Contact Form 7 Auto p Tag Disabled
*****************************************/

add_filter('wpcf7_autop_or_not', '__return_false');



/********************************
 Custom Pagination With Mid Size
*********************************/

$totalPages = intval($publicationsQuery->max_num_pages);
$links      = [];
if ($paged >= 1) {
    $links[] = $paged;
}
if ($paged >= 2) {
    $links[] = $paged - 1;
}
if ($paged >= 3) {
    $links[] = $paged - 2;
}
if (($paged + 2) <= $totalPages) {
    if ($paged < 3) {
        $links[] = $paged + 2;
    }
    $links[] = $paged + 1;
}
sort($links);

if ($paged > 1) {
    echo '<li class="prev"><a href="' . get_pagenum_link($paged - 1) . '"><i class="fa fas fa-chevron-left" aria-hidden="true"></i><span class="sr-only">Go to Previous Page</span></a></li>';
}
foreach ($links as $link) {
    $activeClass = ($paged == $link) ? 'active' : '';
    echo '<li class="' . $activeClass . '"><a href="' . get_pagenum_link($link) . '" data-page="' . $link . '">' . $link . '</a></li>';
}
if (!in_array($totalPages, $links)) {
    if (!in_array($totalPages - 1, $links)) {
        echo '<li class="ellipses-wrapper"><span class="ellipses">…</span></li>' . "\n";
    }
    $activeClass = $paged == $totalPages ? ' active' : '';
    echo '<li class="' . $activeClass . '"><a href="' . get_pagenum_link($totalPages) . '" data-page="' . $totalPages . '">' . $totalPages . '</a></li>';
}
if ($paged < $publicationsQuery->max_num_pages) {
    echo '<li class="next"><a href="' . get_pagenum_link($paged + 1) . '"><i class="fa fas fa-chevron-right" aria-hidden="true"></i><span class="sr-only">Go to Next Page</span></a></li>';
}



/****************************
 Custom Structure BreadCrumbs
*****************************/

function get_breadcrumb()
{
    global $post;
    $html            = '';
    $breadcrumbItems = [
        [
            'url'   => home_url(),
            'title' => 'Home',
        ]
    ];

    if (is_category()) {
        $postCategory = get_the_category($post->ID);
        foreach ($postCategory as $category) {
            $breadcrumbItems[] = [
                'url'   => get_permalink($category->term_id),
                'title' => html_entity_decode($category->name)
            ];
        }
    } elseif (is_page() && get_post_ancestors($post->ID)) {
        foreach (array_reverse(get_post_ancestors($post->ID)) as $ancestor) {
            $breadcrumbItems[] = [
                'url'   => get_permalink($ancestor),
                'title' => html_entity_decode(get_the_title($ancestor))
            ];
        }
    }

    if (!is_front_page() && is_home()) {
        $breadcrumbItems[] = [
            'url'   => false,
            'title' => html_entity_decode(get_the_title(get_option('page_for_posts')))
        ];
    } else {
        $breadcrumbItems[] = [
            'url'   => false,
            'title' => html_entity_decode(get_the_title())
        ];
    }


    if ($breadcrumbItems && is_array($breadcrumbItems) && count($breadcrumbItems) > 0):
        $html    = '<ol class="breadcrumb">';
        $counter = 1;
        foreach ($breadcrumbItems as $item):
            $activeClass = ($counter == count($breadcrumbItems)) ? ' active' : '';
            $html .= '<li class="breadcrumb-item' . $activeClass . '">';
            if ($item['url']) {
                $html .= '<a href="' . $item['url'] . '">' . $item['title'] . '</a>';
            } else {
                $html .= $item['title'];
            }
            $html .= '</li>';
            $counter++;
        endforeach;
        $html .= '</ol>';
    endif;

    return $html;
}



/****************************
 Dynamic Reading Time
*****************************/

function get_reading_time($post_id)
{
    $words_per_min = 200; // estimate of how many words someone can read per minute
    $content       = get_post_field('post_content', $post_id, 'display');
    $word_count    = str_word_count($content);

    $readingTime = intval(ceil($word_count / $words_per_min));

    return sprintf(
        '%s min read',
        $readingTime,
    );
}



/************************************
 Set Post View Count For Trending Posts 
*************************************/

function setPostViews($postID)
{
    $countKey = 'post_views_count';
    $count    = get_post_meta($postID, $countKey, true);
    if ($count == '') {
        $count = 0;
        delete_post_meta($postID, $countKey);
        add_post_meta($postID, $countKey, '0');
    } else {
        $count++;
        update_post_meta($postID, $countKey, $count);
    }
}



/************************************
 Get Youtube Video Thumbnail From Url 
*************************************/

function get_video_thumbnail($src)
{
    $url_pieces = explode('/', $src);
    if ($url_pieces[2] == 'dai.ly') {
        $id        = $url_pieces[3];
        $hash      = json_decode(file_get_contents('//api.dailymotion.com/video/' . $id . '?fields=thumbnail_large_url'), TRUE);
        $thumbnail = $hash['thumbnail_large_url'];
    } else if ($url_pieces[2] == 'www.dailymotion.com') {
        $id        = $url_pieces[4];
        $hash      = json_decode(file_get_contents('//api.dailymotion.com/video/' . $id . '?fields=thumbnail_large_url'), TRUE);
        $thumbnail = $hash['thumbnail_large_url'];
    } else if ($url_pieces[2] == 'vimeo.com') { // If Vimeo
        $id        = $url_pieces[3];
        $hash      = unserialize(file_get_contents('//vimeo.com/api/v2/video/' . $id . '.php'));
        $thumbnail = $hash[0]['thumbnail_large'];
    } elseif ($url_pieces[2] == 'youtu.be') { // If Youtube
        $extract_id = explode('?', $url_pieces[3]);
        $id         = $extract_id[0];
        $thumbnail  = '//img.youtube.com/vi/' . $id . '/mqdefault.jpg';
    } else if ($url_pieces[2] == 'player.vimeo.com') { // If Vimeo
        $id        = $url_pieces[4];
        $hash      = unserialize(file_get_contents('//vimeo.com/api/v2/video/' . $id . '.php'));
        $thumbnail = $hash[0]['thumbnail_large'];
    } elseif ($url_pieces[2] == 'www.youtube.com' && $url_pieces[3] == 'embed') { // If Youtube
        $extract_id = explode('=', $url_pieces[4]);
        $id         = $extract_id[0];
        $thumbnail  = '//img.youtube.com/vi/' . $id . '/mqdefault.jpg';
    } elseif ($url_pieces[2] == 'www.youtube.com') { // If Youtube
        $extract_id = explode('=', $url_pieces[3]);
        $id         = $extract_id[1];
        $thumbnail  = '//img.youtube.com/vi/' . $id . '/mqdefault.jpg';
    } else {
        $thumbnail = $src;
    }
    return $thumbnail;
}


/*************************************************************************************
 Add a custom link to the end of a specific menu that uses the wp_nav_menu() function
 *************************************************************************************/

function add_custom_menu_link($items, $args)
{
    if ($args->theme_location == 'header-menu') {
        $items = $items . '<li class="nav-item"><input type="text" class="form-control" placeholder="Search"></li>';
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'add_custom_menu_link', 10, 2);