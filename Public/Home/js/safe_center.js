$(function () {
    /*left list*/
    slide("#safe_list", 10, 0, 180, .8);
    var $list=$("#safe_list"),$li=$list.children("li");
    $("#center_title").click(function () {
        $list.toggle();
    });

    /*页面传参*/
    var url=window.location.href,index =url.indexOf("tag");
    if(index != -1){
        var tat= url.split("tag%60")[1].split(".")[0];
        $($li).removeClass("selected").addClass("similar");
        $($li[tat]).removeClass("similar").addClass("selected")
    }

});

function leftShow(target) {
    $(target).parent().find(".selected").removeClass("selected");
    $(target).addClass("selected");
}
