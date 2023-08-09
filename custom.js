/********************************** 
 Load More Insight Buttons JS Start
***********************************/

$(document).on('click', '.load-more-insight-button', function (event) {
    event.preventDefault();
    var page = parseInt($('.page_number').val());
    currentPage = page+1;
    triggerInsightPostDisplay();
});

function triggerInsightPostDisplay() {
    var postPerPage = $('.post_per_page').val();
    var loadMoreButtonValue = $('.load_more_button_value').val();
    var page = parseInt($('.page_number').val());
    var insightTypeTermID = $('.insight_type:checkbox:checked').map(function() {
        return this.value;
    }).get();

    $(".page_number").remove();
    $(".load_more_button_value").remove();
    $(".insights-no-found").remove();

    $(".insight-list-loader").show();
    $(".load-more-button").hide();

    if(insightTypeTermID.length == 0 ){
        $('.clear-box').hide();
    }else{
        $('.clear-box').show();
    }

    $.ajax({
        type: "POST",
        url: localize_data.admin_ajax_url,
        data: {
            action: "insight_pagination",
            postPerPage: postPerPage,
            loadMoreButton: loadMoreButtonValue,
            page: currentPage,
            insightType : insightTypeTermID,
        },
        success: function(response){
            response = JSON.parse(response);
            $('.grid').show();
            $('.show_insights').append(response.insightHtml);
            $('.page_number').val(currentPage);
            $(".insight-list-loader").hide();
            showHideInsightLoadMoreButton()
            if($('.grid').length > 0){
                $('.grid').masonry('reloadItems');
                $('.grid').masonry( 'layout' );
            }
        }
    })
}

showHideInsightLoadMoreButton();
function showHideInsightLoadMoreButton(){
    if('show' == $('.load_more_button_value').val()){
        $(".load-more-button").show();
    }else{
        $(".load-more-button").hide();
    }
}



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