<?php 

/*******************************************************
Posts Render, Pagination, Loader, and WP_Query with AJAX 
********************************************************/

/* For The Load More Button */
function renderWorksHtml($taxonomySlug = null, $page = 1)
{

	$itemsPerPage = (get_field('display_work_per_page', 'options')) ? get_field('display_work_per_page', 'options') : 5;
	$page         = ($page) ? $page : get_query_var('paged');

	$args = [
		'post_type'      => 'work',
		'posts_per_page' => $itemsPerPage,
		'post_status'    => 'publish',
		'paged'          => $page,
	];

	$taxQuery = [];
	if (!empty($taxonomySlug)) {
		$taxQuery[] = [
			'taxonomy' => 'work_agency',
			'field'    => 'slug',
			'terms'    => $taxonomySlug,
			'operator' => 'AND'
		];
	}

	if ($taxQuery && count($taxQuery) > 0) {
		$args['tax_query'] = $taxQuery;
	}

	$worksQuery = new WP_Query($args);
	ob_start();

	$showLoadMoreButton = true;
	if ($worksQuery->max_num_pages == $page || $worksQuery->max_num_pages === 0) {
		$showLoadMoreButton = false;
	}

	if ($worksQuery->have_posts()) {
		echo '<input type="hidden" class = "page_number" value ="' . $page . '">';
		echo '<input type="hidden" class = "taxnomy_slug" value ="' . $taxonomySlug . '">';
		if ($worksQuery->posts && is_array($worksQuery->posts) && count($worksQuery->posts) > 0):
			foreach ($worksQuery->posts as $posts):
                set_query_var('work', $posts->ID);
                get_template_part('parts/common/work-list-card');
			endforeach;
		endif;
		if ($worksQuery->max_num_pages > 1) {
			?>
			<div class="work-list-loader" style="display:none; text-align:center; ">
				<img src="<?php echo get_template_directory_uri() . '/assets/images/Infinity-loder.svg'; ?>" alt="">
			</div>
			<?php if ($showLoadMoreButton) { ?>
				<div class="work load-more-button text-center mt-5">
					<a href="javascript:void(0)" class="btn-load">Load More</a>
				</div>
			<?php }
		}
        
		wp_reset_postdata();
	} else {
		echo '<h3 class="work-no-found" style=" text-align:center; padding: 150px 0;">No Work Found.</h3>';
	}
	$data = ob_get_clean();
	return $data;
}

/**
 * Work Post Pagination
 */
function works_pagination()
{
	$page         = (isset($_POST["page"])) ? $_POST["page"] : 1;
	$taxnomy_slug = (isset($_POST["taxnomy_slug"])) ? $_POST["taxnomy_slug"] : '';
	$data         = renderWorksHtml($taxnomy_slug, $page);
	echo json_encode(array('workHtml' => $data));
	die();
}

add_action('wp_ajax_nopriv_works_pagination', 'works_pagination');
add_action('wp_ajax_works_pagination', 'works_pagination');


/* For The Paginations */

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

?>