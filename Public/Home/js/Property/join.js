
$(function () {


    //用户信息的请求
    $("#case").find('tr').find('td').click(function () {
        var alen = $(this).children('span').eq(0).text();
        var xnb=$(this).attr('xnb');
        getadds( xnb,alen);

    })



    //获取虚拟币地址
    function getadds(xnb,alen) {
        $.ajax({
            url:"/Home`Property`case_join",
            data:{case:xnb},
            type:"post",
            success:function (data) {

                $('#buy_xnb').html(parseFloat(data.property)>0?data.property: '0.00');  //可用虚拟币
                $('#no_xnb').html(parseFloat(data.property_d)>0?data.property_d: '0.00');   //冻结虚拟币
                $('#dizhi').text(alen);    //虚拟币名字

                $('.dizhi').each(function () {
                    $(this).text(alen);
                });    //虚拟币名字

                $('#address').text(data.adds);    //钱包地址
                $('.qrcodeCanvas').attr('src',data.img_address);    //钱包地址
                $('.sendxnb').attr('xnb',xnb);
                $("#in_curr_form").show();
                // $("#packet_url").hide();
                $('[name=address]').text(data.address)

            }
        })
    }

    $("#in_curr_submit").click(function () {
        $.ajax({
            url: "/Home`Property`xnbrollin",
            type: "post",
            data:{number:$('[name=number]').val(),xnb:$('.sendxnb').attr('xnb'),user_address:$('[name=user_address]').val()},
            success:function (data) {
                if (data.status!=true){   //请求失败
                    set_poput_code(data.info,false);
                    return false
                }

                // //成功后展示用户地址，和二维码   弃用，请求成功后直接刷新页面
                // $('#address').text(data.info);
                //
                // $(".qrcodeCanvas").empty().qrcode({
                //     render : "canvas",    //设置渲染方式，有table和canvas，使用canvas方式渲染性能相对来说比较好
                //     text : utf16to8(data.info),    //扫描二维码后显示的内容,可以直接填一个网址，扫描二维码后自动跳向该链接
                //     width : "100",               //二维码的宽度
                //     height : "100",              //二维码的高度
                //     background : "#ffffff",       //二维码的后景色
                //     foreground : "#000000",        //二维码的前景色
                //     src: '/Public/jquery.qrcode/54.png'             //二维码中间的图片
                // });
                //
                // //虚拟币冻结金额增加
                // var i=parseFloat($('#no_xnb').text())+parseFloat($('[name=number]').val());
                // $('#no_xnb').text(i);
                //
                // //页面跳转到地址
                // $("#in_curr_form").hide();
                // $("#packet_url").show();
                //
                // //由于页面没有刷新，清空input框
                // $('[name=number]').val("");
                // $('[name=password]').val("")
                set_poput_code(data.info,true);
                return false

            }

        })
    })
    
    $('.status').change(function () {
        location.href='/Home`Property`join_currency_status`'+$(this).val()+'`tag`3.html';
    })



})