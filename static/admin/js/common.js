var TYPE_STATUS = 1,
    TYPE_DELETE = 2,
    TYPE_MOVE = 3,
    TYPE_MOVE_UP = 1,
    TYPE_MOVE_DOWN = 2,
    TYPE_SORT = 4,
    TYPE_REFRESH = 5,
    TYPE_GET_CITIES = 6,
    image = '/static/admin/images/show_',
    status_class_prefix = 'page_status_',
    url = '',
    empty = '';

var NO_BASE_UPLOAD = false;

function ShowPopUp(text) {
    $('#wrapper').append(text);
    $('.PopUpHeader a').bind('click', function() {
        if ($(this).attr('id') != 'saveButton') {
        	ClosePopUp();
        }
    });
}

function ClosePopUp() {
    $('.PopUpBg').addClass('hide');
}

var sendStatusData = function(url, data) {
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        success: function(result) {
        	if (data.type == TYPE_REFRESH) {
        		alert("Данные обновлены!");
        	} else if ( data.type == TYPE_DELETE ){
                    $.each($('tr'),function(){
                        if( $(this).attr('rel') == data.id ){
                            $(this).remove()
                        }
                    });
                            
                }
            try {
                photoData = $.parseJSON(result);
            } catch (e) {
                ShowPopUp(result);
            }
        }
    });
};

var changeStatusImg = function(id) {
    var imageClass = '.' + status_class_prefix + id;
    var data = {
        now: parseInt($(imageClass).parent()[0].className, 10),
        change: 0,
        hint: ''
    };
    switch (data.now) {
	    case 0:
	        data.change = 1;
	        data.hint = 'Скрыть';
	    break;
	    case 1:
	        data.change = 0;
	        data.hint = 'Показать';
	    break;
	    case 3:
	        return;
    }
    $(imageClass).attr('src', image + data.change + '.png').parent().attr('title', data.hint)[0].className = data.change;
    return data.change;
};

var changeStatus = function(id, type) {
    switch (type) {
    case TYPE_STATUS:
        var newStatus = changeStatusImg(id);
        sendStatusData(url, {
            type: TYPE_STATUS,
            status: newStatus,
            id: id
        });
        break;
    case TYPE_DELETE:
        if (confirm('Вы действительно хотите удалить безвозвратно?')) {
            $('.id_' + id).remove();
            $('tr[rel='+id+']').remove();
            $('.' + id).remove();
            sendStatusData(url, {
                type: TYPE_DELETE,
                id: id
            });
        }
       break;
    case TYPE_REFRESH:
        sendStatusData(url, {
            type: TYPE_REFRESH,
            id: id
        });
    	break;
    }
};

$(function() {
    url = $('#statusUrl').val();
    if (url === undefined) {
        url = '';
    }
    $('#uploadInput').change(function() {
        if (!NO_BASE_UPLOAD) {
            $(this).upload($('#uploadUrl').val() + '/' + $('#idPhoto').val(), function(res) {
                try {
                    data = $.parseJSON(res);
                    $('#previewImage').removeClass('hide').attr('href', decodeURIComponent((data.path + '').replace(/&amp;/g, '&')) + '&t=' + new Date().getTime());
                    $('#idPhoto').val(data.idPhoto);
                    $('#clickToUpload').text('Заменить');
                } catch (e) {
                    ShowPopUp(res);
                }
            }, 'html');
        }
    });
    $('#uploadInput2').change(function() {
        if (!NO_BASE_UPLOAD) {
            $(this).upload($('#uploadUrl').val() + '/' + $('#idPhoto2').val(), function(res) {
                try {
                    data = $.parseJSON(res);
                    $('#previewImage2').removeClass('hide').attr('href', decodeURIComponent((data.path + '').replace(/&amp;/g, '&')) + '&t=' + new Date().getTime());
                    $('#idPhoto2').val(data.idPhoto);
                    $('#clickToUpload2').text('Заменить');
                } catch (e) {
                    ShowPopUp(res);
                }
            }, 'html');
        }
    });
    $('#uploadInput3').change(function() {
        if (!NO_BASE_UPLOAD) {
            $(this).upload($('#uploadUrl').val() + '/' + $('#idPhoto3').val(), function(res) {
                try {
                    data = $.parseJSON(res);
                    $('#previewImage3').removeClass('hide').attr('href', decodeURIComponent((data.path + '').replace(/&amp;/g, '&')) + '&t=' + new Date().getTime());
                    $('#idPhoto3').val(data.idPhoto);
                    $('#clickToUpload3').text('Заменить');
                } catch (e) {
                    ShowPopUp(res);
                }
            }, 'html');
        }
    });
	$("#tabs").tabs();
	$('.multiselect').multiselect();
});

function statusMenu() {
    if ($('#main_menu').hasClass('hide')) {
        $('.closeMenu').children().text('<< Скрыть меню');
        $('#main_menu').toggle('slow', function() {
            $('#main_menu').removeClass('hide');
        });
        //status = 1;
    } else {
        $('.closeMenu').children().text('Показать меню >>');
        $('#main_menu').toggle('slow', function() {
            $('#main_menu').addClass('hide');
        });
        //status = 0;
    }
/*$.cookie("menu", status, {
          expires: 7,
          path: "/"
    });*/
}

function SubmitPageFrom() { //Submit Form
    $('#PageForm').submit();
}
$(document).ready(function() {
    $('.PopUpHeader a').bind('click', function() {
        if ($(this).attr('id') != 'saveButton') {
        	ClosePopUp();
        }
    });
    $('#langPanel a').bind('click', function() {
        Content.ChangeLang($(this));
    });
    $('input[type=file]').filestyle({
        image: "",
        imageheight: 24,
        imagewidth: 170,
        width: 185
    });
    $('#SavePage').click(function() {
        $('#MainPageForm').submit();
    });
    $('#SumbitSeriesForm').click(function() {
        $('#SeriesForm').submit();
    });
    $('.moveUpTr').bind('click', function() {
        moveUpTR(this);
    });
    $('.moveDownTr').bind('click', function() {
        moveDownTR(this);
    });
    $('.moveUp').bind('click', function() {
        moveUp(this);
    });
    $('.moveDown').bind('click', function() {
        moveDown(this);
    });

});
//ul 
//####PAGE TREE MOVEING


function moveUp(obj) { //starts moveUp function
    last = false;
    first = false;
    if ($(obj).parent().hasClass('last')) {
        $(obj).parent().removeClass('last');
        last = true;
    }
    if ($(obj).parent().prev().hasClass('first')) {
        $(obj).parent().prev().removeClass('first');
        first = true;
    }
    preId = $(obj).parent().prev().attr('id');
    preText = $(obj).parent().prev().html();
    mainMove(obj, preId, preText, TYPE_MOVE_UP, last, first);
}

function moveDown(obj) { //starts moveDown function
    last = false;
    first = false;
    if ($(obj).parent().next().hasClass('last')) {
        $(obj).parent().next().removeClass('last');
        last = true;
    }
    if ($(obj).parent().hasClass('first')) {
        $(obj).parent().removeClass('first');
        first = true;
    }
    preId = $(obj).parent().next().attr('id');
    preText = $(obj).parent().next().html();
    mainMove(obj, preId, preText, TYPE_MOVE_DOWN, last, first);
}
//end ul
//Table


function moveUpTR(obj) { //starts moveUp function
    preId = $(obj).parent().parent().prev().attr('id');
    preText = $(obj).parent().parent().prev().html();
    mainMoveTr(obj, preId, preText, TYPE_MOVE_UP);
}

function moveDownTR(obj) { //starts moveDown function
    preId = $(obj).parent().parent().next().attr('id');
    preText = $(obj).parent().parent().next().html();
    mainMoveTr(obj, preId, preText, TYPE_MOVE_DOWN);
}

function mainMoveTr(obj, preId, preText, typeMove) {
    currentId = $(obj).parent().parent().attr('id');
    $(obj).parent().parent().attr('id', preId);
    curentText = $(obj).parent().parent().html();
    switch (typeMove) {
    case TYPE_MOVE_UP:
        $(obj).parent().parent().prev().replaceWith('<tr id="' + currentId + '">' + curentText + '</tr>');
        break;
    case TYPE_MOVE_DOWN:
        $(obj).parent().parent().next().replaceWith('<tr id="' + currentId + '">' + curentText + '</tr>');
        break;
    }
    $(obj).parent().parent().html(preText);
    ReDecorateZebra();
    ReBuildArrow();
    $.ajax({
        type: "POST",
        url: $('#moveUrl').val(),
        data: {
            'curentId': currentId,
            'withId': preId,
            'typeMove': typeMove,
            type: TYPE_MOVE
        },
        success: function(result) {

        }
    });
}
//endtable;

function mainMove(obj, preId, preText, typeMove, last, first) {
    currentId = $(obj).parent().attr('id');
    addClasses = '';
    $(obj).parent().attr('id', preId);
    curentText = $(obj).parent().html();
    level = $(obj).parent()[0].className.match(/.*level_([0-9]).*/); //geting level num
    addClasses += (last && typeMove == TYPE_MOVE_DOWN) ? 'last' : '';
    addClasses += (first && typeMove == TYPE_MOVE_UP) ? 'first' : '';
    replaceText = '<li class="level_' + level[1] + ' ' + addClasses + '" id="' + currentId + '">' + curentText + '</li>'; //what clicked
    switch (typeMove) {
    case TYPE_MOVE_UP:
        {
            $(obj).parent().prev().replaceWith(replaceText);
            if (last) $(obj).parent().addClass('last');
        }
        break;
    case TYPE_MOVE_DOWN:
        {
            $(obj).parent().next().replaceWith(replaceText);
            if (first) $(obj).parent().addClass('first');
        }
        break;
    }
    $(obj).parent().html(preText)[last]; //Magic
    ReDecorateZebra();
    ReBuildArrow();
    $.ajax({
        type: "POST",
        url: $('#moveUrl').val(),
        data: {
            'curentId': currentId,
            'withId': preId,
            type: TYPE_MOVE,
            'typeMove': typeMove
        },
        success: function(result) {

        }
    });
}

function ReDecorateZebra() {
    i = 1;
    if ($('.tree')[0] !== undefined) {
        $('.tree li').each(function() {
            $(this).removeClass('grey').removeClass('light');
            i % 2 == 0 ? $(this).addClass('light') : $(this).addClass('grey');
            i++;
        });
    }

    if ($('.zebra')[0] !== undefined) {
        $('.zebra tbody tr').each(function() {
            $(this).removeClass('grey').removeClass('light');
            i % 2 == 0 ? $(this).addClass('light') : $(this).addClass('grey');
            i++;
        });
    }
}

function ReBuildArrow() {
    if ($('.tree')[0] !== undefined) {
        i = 1;
        classStr = 'level_';
        $('.tree .moveUp').replaceWith('<a href="javascript:void(0);" class="clearMoveUp"><img src="/static/admin/images/nobg.png" alt="" width="9" height="8" /></a>');
        $('.tree .moveDown').replaceWith('<a href="javascript:void(0);" class="clearMoveDown"><img src="/static/admin/images/nobg.png" alt="" width="9" height="8" /></a>');
        //make clear
        while ($('.level_' + i)[0] !== undefined) {
            j = 1;
            liCount = $('.level_' + i).length;
            $('.level_' + i).each(function() { //set new arrow
                if (j != liCount && !$(this).hasClass('last')) {
                    $(this).children('.clearMoveDown').replaceWith('<a href="javascript:void(0);" class="moveDown"><img class="" src="/static/admin/images/arrow_blue_down.gif" alt="" width="10" height="15" /></a>');
                }
                if (j != 1 && !$(this).hasClass('first')) {
                    $(this).children('.clearMoveUp').replaceWith('<a href="javascript:void(0);" class="moveUp"><img class="" src="/static/admin/images/arrow_blue_up.gif" alt="" width="10" height="15" /></a>');
                }
                j++;
            });
            i++;
        }
        $('.moveUp').bind('click', function() {
            moveUp(this);
        });
        $('.moveDown').bind('click', function() {
            moveDown(this);
        });
    }

    if ($('.zebra')[0] !== undefined) {
        i = 1;
        current = $('#page_current').val();
        first = $('#page_first').val();
        last = $('#page_last').val();
        currentItemCount = $('#page_currentItemCount').val();
        $('.moveUpTr').replaceWith('<a href="javascript:void(0);" class="clearMoveUp"><img src="/static/admin/images/nobg.png" alt="" width="9" height="8" /></a>');
        $('.moveDownTr').replaceWith('<a href="javascript:void(0);" class="clearMoveDown"><img src="/static/admin/images/nobg.png" alt="" width="9" height="8" /></a>');
        $('.zebra tbody tr').each(function() {
            if ((current != first) || (i != 1)) {
                $(this).children('td').children('.clearMoveUp').replaceWith('<a href="javascript:void(0);" class="moveUpTr"><img src="/static/admin/images/arrow_blue_up.gif" alt="" width="10" height="15"></a>');
            }
            if ((current != last) || (i != currentItemCount)) {
                $(this).children('td').children('.clearMoveDown').replaceWith('<a href="javascript:void(0);" class="moveDownTr"><img src="/static/admin/images/arrow_blue_down.gif" alt="" width="10" height="15"></a>');
            }
            i++;
        });
        $('.moveUpTr').bind('click', function(){
            moveUpTR(this);
        });
        $('.moveDownTr').bind('click', function(){
            moveDownTR(this);
        });
    }
};


function ReDecorateZebraTable() {
    i = 1;
    $('.zebra tbody tr').each(function() {
        $(this).removeClass('grey');
        $(this).removeClass('light');
        i % 2 == 0 ? $(this).addClass('light') : $(this).addClass('grey');
        i++;
    });
}

function toggleEditor(id) {
    if (!tinyMCE.get(id)) tinyMCE.execCommand('mceAddControl', false, id);
    else tinyMCE.execCommand('mceRemoveControl', false, id);
}

Content = {
    'ChangeLang': function(AObject) {
        prefix = '#fields_';
        $('#langPanel a').removeClass('current');
        AObject.addClass('current');
        $('div.slideBox').animate({
            left: -1000 * (AObject.addClass('current').parent('li')[0].className - 1)
        }, 900);
    }
};

var	process = [],
	 processI = 0,
	 processInterVal = null,
	 processStart = function() {
	    if (process.length == 0) {
	        $('#process').show();
	        processInterVal = (setInterval(function() {
	            var str = 'Processing';
	            for (var i = 0; i < processI; i++)
	            str += '.';
	            $('#process').html(str);
	            processI++;
	            if (processI > 4) processI = 0;
	        }, 300));
	    }
	    process.push(1);
	},
	processStop = function() {
	    if (process.length != 0) {
	        process.pop(1);
	        if (process.length == 0) {
	            clearInterval(processInterVal);
	            $('#process').hide();
	        }
	    }
	};
	
var inArray = function(e, array) {
	var l = array.length;
	for(var i = 0; i < l; i++) {
		if (parseInt(array[i], 10) === parseInt(e,10)) {
			return true;
		}
	}
	return false;
} 