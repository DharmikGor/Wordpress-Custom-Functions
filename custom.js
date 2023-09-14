/********************************** 
 Load More Insight Buttons JS Start
***********************************/

$(document).on("click", ".work.load-more-button", function () {
	var page = parseInt($(".page_number").val());
	var taxnomy_slug = $(".taxnomy_slug").val();
	currentPage = page + 1;
	var loadMoreButtonValue = $(".load_more_button_value").val();

	$(".work-no-found").remove();
	$(".page_number").remove();
	$(".load_more_button_value").remove();
	$(".load-more-button").remove();
	$(".work-list-loader").show();

	$.ajax({
		type: "POST",
		url: localize_data.admin_ajax_url,
		data: {
			action: "works_pagination",
			taxnomy_slug: taxnomy_slug,
			page: currentPage,
		},
		success: function (response) {
			response = JSON.parse(response);
			$(".page_number").val(currentPage);
			$(".work-list-loader").remove();
			$(".show_work").append(response.workHtml);
		},
	});
});

/*************************** 
 Guides Paginations JS Start
****************************/

var currentPage = 1;

jQuery(document).on("click", ".product-pagination li", function (e) {
	if (jQuery(this).hasClass("active")) {
		return true;
	}
	let selectedPage = parseInt(jQuery(this).find("a").attr("data-page"));
	currentPage = selectedPage;
	triggerProductSearch();
});

jQuery(document).on("change", ".product_category_filter, .product_type_filter, .sort_order_filter", function (e) {
	currentPage = 1;
	triggerProductSearch();
});

jQuery(document).on("click", ".resetFilter", function () {
	currentPage = 1;
	jQuery(".product_category_filter, .product_type_filter, .sort_order_filter").val("");
	triggerProductSearch();
});

function triggerProductSearch($productCategoryFilter = null, $productTypesFilter = null) {
	jQuery(".no-found").remove();
	let currentPageId = jQuery("input[name=currentPageID]").val();
	var productCategoryFilter = jQuery(".product_category_filter").val();
	var productTypeFilter = jQuery(".product_type_filter").val();
	var sortOrderFilter = jQuery(".sort_order_filter").val();

	$("html, body").animate(
		{
			scrollTop: jQuery("#product-top-bar").offset().top - 300,
		},
		100
	);

	jQuery.ajax({
		type: "POST",
		url: localize_data.ajaxurl,
		data: {
			action: "product_pagination",
			currentPageId: currentPageId,
			page: currentPage,
			productCategoryFilter: productCategoryFilter,
			productTypeFilter: productTypeFilter,
			sortOrderFilter: sortOrderFilter,
		},
		beforeSend: function () {
			jQuery(".ajax-product-card, .product-pagination").remove();
			jQuery(".ajax-loader").fadeIn(500);
		},
		success: function (response) {
			setTimeout(function () {
				response = JSON.parse(response);
				jQuery("#show_product").html(response.productHtml);
			}, 500);
		},
	});
}
