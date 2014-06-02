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
    var idCat = 0;
    var self = this;

    this.init = function ()
    {
        $form = $('#order_form');
        $('a.b-submit__cansel').click(function(){
            $('.b-popup-i').fadeOut();
            return false;
        });
        $('div.error_block').hide();
        $('#last_name_ind').hide();
    }

    this.getByStatus = function(ele)
    {
        $('#btns_status a').removeClass('btn-success');
        $(ele).addClass('btn-success');

        return self.goToPage(1);
    }

    this.goToPage = function (page)
    {
        var loader = $('<img/>',{src:'/static/default/img/ajax-loader.gif', width:'66px', height:'66px', style:'margin-left: 45%; margin-top: 25%'});
        var status = $('#btns_status').find('a.btn-success').first().data('status');
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
            '/more_stickers/'+idCat+'?page='+page+'&types='+types+'&status='+status,
            function (data) {
                if (data.html) {
                    data.html = '<div style="display:none;" id="box_vs_items" class="col-md-12"><div class="row">'+data.html+'</div></div>';

                }
                box.html(data.html);
                box.css('height', 'auto');
                $("html, body").animate({ scrollTop: 0 }, "slow");
            }, "json"
        )
            .done(function () {
                $('#box_vs_items').fadeIn();
            })
            .fail(function () {
            })
            .always(function () {
            });

        return false;
    }


    this.submitOrderForm = function ()
    {
        var errorBlock = $('div.error_block');
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

    this.submitIndividualOrder = function(formEl)
    {
        var errorBlock = $('div.error_block');
        errorBlock.find('div.b-input-i').html('');
        errorBlock.fadeOut();
        args = $(formEl).serialize();

        $.post(
            $(formEl).data('action'),
            args,
            function (data) {
                if (data.success) {
                    $('#myModalIndividual').modal('hide');

                    $('#myModalSenks').modal('show');
                    setTimeout(function(){
                        $('#myModalSenks').modal('hide');
                    }, 10000);
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

    this.setIdCat = function(id)
    {
        idCat = id;
    }
}

var common = new commonController();
$(function () {
    common.init();
})