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

function trim_content($excerpt, $maxCharacter = '50', $htmlTag = '')
{
    if ($htmlTag) {
        echo '<' . $htmlTag . '>';
    }
    if ($excerpt) {
        echo (strlen(strip_tags($excerpt)) > $maxCharacter) ? substr(strip_tags($excerpt), 0, $maxCharacter) . "..." : substr(strip_tags($excerpt), 0, $maxCharacter);
    }
    if ($htmlTag) {
        echo '</' . $htmlTag . '>';
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