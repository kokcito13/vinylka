function InitCommonFeatures() {
    $('.tov__item:nth-child(4n)').addClass('tov__item_fourth');

    var form = $('.b-input-i');
    $('.b-label', form).bind({
        'click': function () {
            $(this).hide().prev('.b-input, .b-ta').focus();
        }
    });
    $('.ico_type_cover-close').click(function () {
        $(this).parents('.b-popup-i').hide();
    });
    $('.b-input, .b-ta', form).bind({
        'focus': function () {
            $(this).next('label').hide();
        },
        'blur': function () {
            if ($(this).val() == '')
                $(this).next('label').show();
        }
    });
}

commonController = function ()
{
    var args, $form;

    this.init = function ()
    {
        $form = $('#order_form');
        $('a.b-submit__cansel').click(function(){
            $('.b-popup-i').fadeOut();
            return false;
        });
        $('div#error_block').hide();
    }

    this.goToPage = function (page)
    {
        var loader = $('<img/>',{src:'/static/default/img/ajax-loader.gif', width:'66px', height:'66px', style:'margin-left: 45%; margin-top: 25%'});
        var box = $('div.box');
        var types = 0;

        box.css('height', box.height());
        box.html(loader);

        $('input[name=types]:checked').each(function(index){
            if (index == 0) {
                types = $(this).val();
            } else {
                types += '-'+$(this).val();
            }
        });

        $.get(
            '/more_stickers?page='+page+'&types='+types,
            function (data) {
                if (data.html) {
                    data.html = '<div style="display:none;" id="box_vs_items" class="col-md-12"><div class="row">'+data.html+'</div></div>';
                    box.html(data.html);
                    box.css('height', 'auto');
                    $('#box_vs_items').fadeIn();
                    $("html, body").animate({ scrollTop: 0 }, "slow");
                }
            }, "json"
        )
            .done(function () {
//                console.log("second success");
            })
            .fail(function () {
//                console.log("error");
            })
            .always(function () {
//                console.log("finished");
            });

        return false;
    }


    this.showPopup = function(id)
    {
        $('.b-popup-i').fadeIn();
        $('#last_name').hide();

        return false;
    }

    this.submitOrderForm = function ()
    {
        var errorBlock = $('div#error_block');
        errorBlock.find('div.b-input-i').html('');
        errorBlock.fadeOut();
        args = $form.serialize();

        $.post(
            $form.attr('action'),
            args,
            function (data) {
                if (data.success) {
                    $('#myModal').modal('hide');

                    $('#myModalSenks').modal('show');
                    setTimeout(function(){
                        $('#myModalSenks').modal('hide');
                    }, 10000)
                } else if (data.error) {
                    var html_error = '';

                    $.each(data.error, function(k,v){
                        console.log(k,v);
                        html_error += '<p><b>'+k+':</b> '+v+'</p>';
                    });

                    errorBlock.find('div.b-input-i').html(html_error);
                    errorBlock.fadeIn();
                }
            },
            "json"
        )
            .fail(function () {
            })
            .always(function () {
            });

        return false;
    }
}

var common = new commonController();
$(function () {
    common.init();
})