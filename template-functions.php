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


/* Products Rendering Functions */

function renderProductHtml($page = 1, $currentPageID = null, $productCategoryFilter = null, $productTypeFilter = null, $sortOrderFilter = 'a-z')
{

	$itemsPerPage = (get_field('items_per_page', $currentPageID)) ? get_field('items_per_page', $currentPageID) : 6;
	$productCatalogTitle = get_field('product_catalog_title', $currentPageID);

	$args = [
		'post_type'        => 'product',
		'posts_per_page'   => $itemsPerPage,
		'post_status'      => 'publish',
		'paged'            => $page,
	];

	if ($sortOrderFilter == 'desc' || $sortOrderFilter == 'asc') {
		$args['orderby'] = 'date';
		$args['order'] = $sortOrderFilter;
	} elseif ($sortOrderFilter == 'z-a') {
		$args['orderby'] = 'title';
		$args['order'] = 'DESC';
	} else {
		$args['orderby'] = 'title';
		$args['order'] = 'ASC';
	}

	$taxQuery = [];
	if (!empty($productCategoryFilter)) {
		$taxQuery[] = [
			'taxonomy' => 'product_category',
			'field'    => 'slug',
			'terms'    => $productCategoryFilter,
			'operator' => 'AND'
		];
	}

	if (!empty($productTypeFilter)) {
		$taxQuery[] = [
			'taxonomy' => 'product_type',
			'field'    => 'slug',
			'terms'    => $productTypeFilter,
			'operator' => 'AND'
		];
	}

	if ($taxQuery && count($taxQuery) > 0) {
		$args['tax_query'] = $taxQuery;
	}

	$productQuery = new WP_Query($args);

	$endNumberOfProduct = $itemsPerPage * $page;
	$startNumberOfProduct = $endNumberOfProduct - $itemsPerPage + 1;
	$totalProducts = $productQuery->found_posts;
	$endNumberOfProduct   = ($totalProducts < $endNumberOfProduct) ? $totalProducts : $endNumberOfProduct;

	ob_start(); ?>

	<input type="hidden" name="currentPageID" value="<?php echo $currentPageID; ?>">
	<div class="col-12">
		<?php if ($productCatalogTitle) : ?>
			<div class="text-center">
				<h2><?php echo $productCatalogTitle; ?></h2>
			</div>
		<?php endif; ?>
		<div class="product-top-bar" id="product-top-bar">
			<div class="text">
				<h4>All Products</h4>
				<p>Showing <span><?php echo $startNumberOfProduct; ?> to <?php echo $endNumberOfProduct; ?></span> of <?php echo $totalProducts; ?></p>
			</div>
			<div class="sort">
				<?php $productCategoryArray = get_terms(array(
					'taxonomy' => 'product_category',
					'hide_empty' => false
				));
				if ($productCategoryArray && is_array($productCategoryArray) && count($productCategoryArray) > 0) : ?>
					<select class="product_category_filter">
						<option value="" <?php echo $productCategoryFilter == '' ? 'selected' : ''; ?>>Select Product Category</option>
						<?php foreach ($productCategoryArray as $productCategory) : ?>
							<option value="<?php echo $productCategory->slug; ?>" <?php echo $productCategoryFilter == $productCategory->slug ? 'selected' : ''; ?>><?php echo $productCategory->name; ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
				<?php $productTypesArray = $productCategoryArray = get_terms(array(
					'taxonomy' => 'product_type',
					'hide_empty' => false
				));
				if ($productTypesArray && is_array($productTypesArray) && count($productTypesArray) > 0) : ?>
					<select class="product_type_filter">
						<option value="" <?php echo $productTypeFilter == '' ? 'selected' : ''; ?>>Select Product Type</option>
						<?php foreach ($productTypesArray as $productType) : ?>
							<option value="<?php echo $productType->slug; ?>" <?php echo $productTypeFilter == $productType->slug ? 'selected' : ''; ?>><?php echo $productType->name; ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
				<div>
					<span>Sort by</span>
					<select class="sort_order_filter">
						<option value="a-z" <?php echo $sortOrderFilter == 'a-z' ? 'selected' : ''; ?>>A to Z</option>
						<option value="z-a" <?php echo $sortOrderFilter == 'z-a' ? 'selected' : ''; ?>>Z to A</option>
						<option value="asc" <?php echo $sortOrderFilter == 'asc' ? 'selected' : ''; ?>>Oldest</option>
						<option value="desc" <?php echo $sortOrderFilter == 'desc' ? 'selected' : ''; ?>>Latest</option>
					</select>
				</div>
				<?php $buttonDisabledArribute = ($productCategoryFilter || $productTypeFilter || $sortOrderFilter != 'a-z') ? '' : 'disabled'; ?>
				<button class='resetFilter' <?php echo $buttonDisabledArribute; ?>>Reset All</button>
			</div>
		</div>
	</div>
	<?php if ($productQuery->have_posts()) :
		while ($productQuery->have_posts()) {
			$productQuery->the_post();
			set_query_var('productID', get_the_ID());
			get_template_part('parts/common/product-card');
		}
		echo '<div class="ajax-loader" style="display: none; position:relative; text-align: center; margin: 10rem 0;"><div class="loader">Loading...</div></div>';
		if ($productQuery->max_num_pages > 1) : ?>

			<div class="col-12">
				<ul class="pagination-list product-pagination">
					<?php if (($page - 1) >= 1) {
						echo '<li class="left-arrow"><a href="javascript:void(0)" data-page="' . $page - 1 . '"><img src="' . get_template_directory_uri() . '/assets/images/arrow-yellow.svg" alt="arrow-yellow"></a></li>';
					} ?>
					<?php for ($i = 1; $i <= $productQuery->max_num_pages; $i++) {
						$activeClass = $i == $page ? 'active' : '';
						echo '<li class="page ' . $activeClass . '"><a href="javascript:void(0)" data-page="' . $i . '">' . $i . '</a></li>';
					} ?>
					<?php if (($page + 1) <= $productQuery->max_num_pages) {
						echo '<li class="right-arrow"><a href="javascript:void(0)" data-page="' . $page + 1 . '"><img src="' . get_template_directory_uri() . '/assets/images/arrow-yellow.svg" alt="arrow-yellow"></a></li>';
					} ?>
				</ul>
			</div>
<?php
		endif;
		wp_reset_postdata();
	else :
		echo '<h3 class="no-found" style=" text-align:center; padding: 82.5px 0;">No Product Found.</h3>';
		echo '<div class="ajax-loader" style="display: none; position:relative; text-align: center; margin: 10rem 0;"><div class="loader">Loading...</div></div>';
	endif;
	$data = ob_get_clean();
	return $data;
}

/**
 * Guides Pagination
 */
function product_pagination()
{
	$page = (!empty($_POST["page"])) ? $_POST["page"] : 1;
	$currentPageId = (!empty($_POST["currentPageId"])) ? $_POST["currentPageId"] : null;
	$productCategoryFilter = (!empty($_POST["productCategoryFilter"])) ? $_POST["productCategoryFilter"] : '';
	$productTypeFilter = (!empty($_POST["productTypeFilter"])) ? $_POST["productTypeFilter"] : '';
	$sortOrderFilter = (!empty($_POST["sortOrderFilter"])) ? $_POST["sortOrderFilter"] : 'a-z';
	$data = renderProductHtml($page, $currentPageId, $productCategoryFilter, $productTypeFilter, $sortOrderFilter);
	echo json_encode(array('productHtml' => $data));
	die();
}

add_action('wp_ajax_nopriv_product_pagination', 'product_pagination');
add_action('wp_ajax_product_pagination', 'product_pagination');

?>