var $ = jQuery.noConflict();
$( document ).ready(function() {
    
var droppedItems = [];

$("#leftDiv").height($(window).height() - 20);
//var dropSpace = $(window).width() - $("#leftDiv").width();
//$("#dropZone").width(dropSpace - 70);
$("#rightDiv").height($("#leftDiv").height());

$(".item").draggable({
    appendTo: "body",
    cursor: "move",
    helper: 'clone',
    revert: "invalid"
});

$("#rightDiv").droppable({
    tolerance: "intersect",
    accept: ".item",
    activeClass: "ui-state-default",
    hoverClass: "ui-state-hover",
    drop: function(event, ui) {
        $("#rightDiv").append($(ui.draggable));
        id = ui.draggable.attr('item_id')
        droppedItems.push(id);
        console.log(droppedItems);
        //$('#droppedItems').html(JSON.stringify(droppedItems));
    }
});


$("#rightDiv .item").each(function(){
    id = $(this).attr('item_id');
    droppedItems.push(id);
    console.log(droppedItems);
})

$("#form_clear").on("click", function(e) {
    e.preventDefault();

    $("#rightDiv").empty();
    droppedItems.pop();

})

$("#form_save").on("click", function(e) {
    e.preventDefault();

    //alert(droppedItems);

    //$('#response').hide();
    $.ajax({

        type: 'POST',
        data: JSON,
        url: myAjax.ajax_url,
        data: { action: 'form_save', droppedItems:JSON.stringify(droppedItems) },
        success: function(response) {
            console.log(response);
            $('#response').html(response);
            //$('#response').delay(4000).empty();

        }

    });

    return false;




});




});








