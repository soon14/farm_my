$(function () {
 
    $('.file__icon').click(function () {
        $('input[name=imgurl]').trigger('click');
    })

    $('input[name=imgurl]').change(function () {
       
        var  fr= new FileReader()
        fr.onload=function () {
            $('[name=imgurl_img]').attr("src",fr.result)
        }
        fr.readAsDataURL(this.files[0])
    })


    $('.files_address').click(function () {
        $('input[name=img_address]').trigger('click');
    })

    $('input[name=img_address]').change(function () {

        var  fr= new FileReader()
        fr.onload=function () {
            $('[name=address_img]').attr("src",fr.result)
        }
        fr.readAsDataURL(this.files[0])
    })

    
    $('.submit-btn').click(function () {
        var formData= new FormData($('form').get(0));
        $.ajax({
            url:"",
            type:"post",
            data:formData,
            processData:false,
            contentType:false,
            dataType:"json",
            success:function (data) {
                if (data.status!=true){  //失败
                    set_poput_code(data.info,false)
                    return false;
                }
                    set_poput_code(data.info,true)
            }
        })
    })
})
