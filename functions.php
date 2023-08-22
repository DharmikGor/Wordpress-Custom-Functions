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


/********************************************************
 Posts Render, Pagination, Loader, and WP_Query with AJAX
*********************************************************/

function renderInsightHtml($postPerPage = 8, $page = 1, $postsType = '', $loadMoreButton = 'show')
{

    $page = ($page) ? $page : get_query_var('paged');

    $insightArgs = [
        'post_type'      => 'insight',
        'posts_per_page' => $postPerPage,
        'post_status'    => 'publish',
        'paged'          => $page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];

    $taxQuery = [];

    if (!empty($postsType)) {
        $taxQuery[] = [
            'taxonomy' => 'insight_type',
            'field'    => 'term_id',
            'terms'    => $postsType,
            'operator' => 'AND'
        ];
    }

    if ($taxQuery && count($taxQuery) > 0) {
        $insightArgs['tax_query'] = $taxQuery;
    }
    $insightQuery = new WP_Query($insightArgs);

    ob_start();

    if ($insightQuery->max_num_pages == $page || $insightQuery->max_num_pages == 0) {
        $loadMoreButton = 'hide';
    }

    if ($insightQuery->have_posts()) {
        echo '<input type="hidden" class = "post_per_page" value ="' . $postPerPage . '">';
        echo '<input type="hidden" class = "page_number" value ="' . $page . '">';
        echo '<input type="hidden" class = "load_more_button_value" value ="' . $loadMoreButton . '">';
        while ($insightQuery->have_posts()) {
            $insightQuery->the_post();
            set_query_var('insight_post_id', get_the_ID());
            get_template_part('parts/common/insight-list-card');
        }
        wp_reset_postdata();
    } else {
        echo '<h3 class="insights-no-found text-white" style=" text-align:center; margin: 82.5px 0;">No Insights Found.</h3>';
    }
    $data = ob_get_clean();
    return $data;
}

/**
 * Insight Post Pagination
 */
function insight_pagination()
{
    $page           = (isset($_POST["page"])) ? $_POST["page"] : 1;
    $loadMoreButton = 'show';
    $postPerPage    = isset($_POST['postPerPage']) ? $_POST['postPerPage'] : 8;
    $postsType      = isset($_POST['insightType']) ? $_POST['insightType'] : '';
    $data           = renderInsightHtml($postPerPage, $page, $postsType, $loadMoreButton);
    echo json_encode(array('insightHtml' => $data));
    die();
}

add_action('wp_ajax_nopriv_insight_pagination', 'insight_pagination');
add_action('wp_ajax_insight_pagination', 'insight_pagination');


function renderGuidesHtml($page = 1)
{

    $ResourcePageID   = get_page_by_title('Resources')->ID;
    $guiesPostPerPage = (get_field('guides_post_per_page', $ResourcePageID)) ? get_field('guides_post_per_page', $ResourcePageID) : 6;

    $GuidesArgs = [
        'post_type'        => 'guide',
        'posts_per_page'   => $guiesPostPerPage,
        'post_status'      => 'publish',
        'orderby'          => 'date',
        'order'            => 'DESC',
        'paged'            => $page,
        'suppress_filters' => true
    ];

    $GuidesQuery = new WP_Query($GuidesArgs);

    ob_start();

    if ($GuidesQuery->have_posts()) {
        echo '<div class="custom-grid-layout">';
        while ($GuidesQuery->have_posts()) {
            $GuidesQuery->the_post(); ?>
            <div class="remove-guide-list">
                <div class="guides__box">
                    <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), '247x289'); ?>" alt="guide-featured-image">
                    <a href="<?php echo get_the_permalink(get_the_ID()) ?>">
                        <?php if (strlen(get_the_title(get_the_ID())) > 65) {
                            echo '<h3>' . substr(get_the_title(get_the_ID()), 0, 65) . '...</h3>';
                        } else {
                            echo '<h3>' . substr(get_the_title(get_the_ID()), 0, 65) . '</h3>';
                        } ?>
                    </a>
                    <a href="<?php echo get_the_permalink(get_the_ID()) ?>" class="btn">Download</a>
                </div>
            </div>
            <?php
        }
        echo '</div>';
        echo '<div class="guides-list-loader" style="display:none; text-align: center;"><img src="' . get_template_directory_uri() . '/assets/images/Infinity-loder.svg" alt=""></div>';
        if ($GuidesQuery->max_num_pages > 1) { ?>
            <ul class="guides-pagination-group">
                <?php for ($i = 1; $i <= $GuidesQuery->max_num_pages; $i++) {
                    $activeClass = $i == $page ? 'active' : '';
                    echo '<li class="guides-pagination ' . $activeClass . '"><a href="javascript:void(0)" data-page="' . $i . '">' . $i . '</a></li>';
                } ?>
            </ul>
            <?php
        }
        wp_reset_postdata();
    } else {
        echo '<h3 class="guides-no-found" style=" text-align:center; padding: 82.5px 0;">No Guides Found.</h3>';
        echo '<div class="guides-list-loader" style="display:none; text-align: center;"><img src="' . get_template_directory_uri() . '/assets/images/Infinity-loder.svg" alt=""></div>';
    }
    $data = ob_get_clean();
    return $data;
}

/**
 * Guides Pagination
 */
function guides_pagination()
{
    $page = (isset($_POST["page"])) ? $_POST["page"] : 1;
    $data = renderGuidesHtml($page);
    echo json_encode(array('guidesHtml' => $data));
    die();
}

add_action('wp_ajax_nopriv_guides_pagination', 'guides_pagination');
add_action('wp_ajax_guides_pagination', 'guides_pagination');