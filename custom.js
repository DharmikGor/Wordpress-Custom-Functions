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

$(document).on("click", ".guides-pagination", function (e) {
    if ($(this).hasClass('active')) {
        return true;
    }
    let selectedPage = parseInt($(this).find('a').attr('data-page'))
    currentPage = selectedPage
    triggerGuidesSearch();
});

function triggerGuidesSearch() {

    $(".guides-no-found").remove();
    $(".remove-guide-list").remove();
    $(".guides-pagination-group").remove();
    $(".guides-list-loader").show();
    $('html, body').animate({
        scrollTop: $("#show_guides").offset().top - 300
    }, 100);

    $.ajax({
        type: "POST",
        url: localize_data.admin_ajax_url,
        data: {
            action: "guides_pagination",
            page: currentPage
        },
        success: function(response){
            response = JSON.parse(response);
            $(".guides-list-loader").hide();
            $('#show_guides').html(response.guidesHtml);
        }
    })
}