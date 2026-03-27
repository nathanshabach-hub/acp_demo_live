$(document).ready(function () {
    $(document).off('click.ajaxPaging', '.ajshort a').on('click.ajaxPaging', '.ajshort a', function () {
        var thisHref = $(this).attr('href'); 
        thisHref = decodeURIComponent(thisHref); 
        if (!thisHref || thisHref === '#' || thisHref === 'javascript:void(0)') {
            return false;
        }
        $('#loaderID').show();
        $('#listID').load(thisHref, function () {
            $(this).fadeTo(200, 1);
            $('#loaderID').hide();
        });
        return false;
    });
});


$(document).off('click.ajaxPagingSearch', '.admin_ajax_search').on('click.ajaxPagingSearch', '.admin_ajax_search', function () {
    var thisHref = $(location).attr('href');
    thisHref = decodeURIComponent(thisHref);
    $('#loaderID').show();
    $.ajax({
        type: 'POST',
        url: thisHref,
        cache: false,
        data: $('#adminSearch').serialize(),
        success: function (result) {
            $("#listID").html(result);
            $('#loaderID').hide();
        },
        error: function () {
            $('#loaderID').hide();
        }
    });
    return false;
});



function ajaxSearch() {
    var thisHref = $(location).attr('href');
    thisHref = decodeURIComponent(thisHref);
    $('#loaderID').show();
    $.ajax({
        type: 'GET',
        url: thisHref,
        cache: false,
        data: $('#adminSearch').serialize(),
        success: function (result) {
            $("#listID").html(result);
            $('#loaderID').hide();
        },
        error: function () {
            $('#loaderID').hide();
        }
    });
    return false;
}
function actionFromAjax() {
    var thisHref = $(location).attr('href');
    $('#loaderID').show();
    $.ajax({
        type: 'POST',
        url: thisHref,
        cache: false,
        data: $('#actionFrom').serialize(),
        success: function (result) {
            $("#listID").html(result);
            $('#loaderID').hide();
        },
        error: function () {
            $('#loaderID').hide();
        }
    });
    return false;
}

function ajaxActionFunction() {
    if (isAnySelect()) {
        actionFromAjax();
    }
    return false;
}

$(document).ready(function () {
    $(document).off('click.ajaxPagingToggle', '.right_acdc a').on('click.ajaxPagingToggle', '.right_acdc a', function (e) {
        e.preventDefault();
        var $link = $(this);
        var $wrap = $link.closest('.right_acdc');
        var clickId = $wrap.attr('id');
        var clickTitle = $link.attr('title') || 'change status';
        var thisHref = $link.attr('href');
        if (!thisHref || thisHref === '#' || thisHref === 'javascript:void(0)') {
            return false;
        }
        if (!confirm('Are you sure you want to ' + clickTitle + ' ?')) {
            return false;
        }

        $('#loder' + clickId).show();
        $.ajax({
            type: 'GET',
            url: thisHref,
            cache: false,
            success: function (result) {
                $("#" + clickId).html(result);
            },
            error: function () {
                $('#loder' + clickId).hide();
            },
            complete: function () {
                $('#loder' + clickId).hide();
            }
        });
                return false;
        
    });
});
