/*
	Prologue by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
*/

(function($) {

    $('li.dropdown').click(function() {
        $(this).find('ul').first().toggle(500);
    });

    $("#personalMessage").on('keyup', function(){
        $charsR = 150 - ($("#personalMessage input").val()).length;
        $(".numberCount").text($charsR);
    });

    $("#newMessage").on('keyup', function(){
        $charsR = 256 - ($("#newMessage input").val()).length;
        $(".numberCount").text($charsR);
    });

    $("#community").find(".replies").hide();

    $(".msgAnswers").click(function(){
        $(this).parent().parent().find('.replies').first().toggle('fold', 500);
	});

    $(".btn-options").click(function(){
        $(this).parent().find('.btn-options-sm').first().toggle('blind', 500);
    });

    $(".btn-replyMSG").click(function(){
        $(".newReply").toggle('fold', 500);
    });

    $(".btn-delete, .btn-delete-sm").click(function(){
        idMessage = $(this).attr('name');

        swal({
            title: "¿Estás seguro?",
            text: "Este mensaje se borrará completamente",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Borrar",
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        },
        function(){
            $.ajax({
                method: 'POST',
                url: './deleteMessage/'+idMessage
            })
                .done(function( status ){
                    swal(status);
                    setTimeout(function(){
                        location.reload();
                    }, 2000);
                });
        });
    });

    $(".btn-delete-inMessage, .btn-delete-inMessage-sm").click(function(){
        idMessage = $(this).attr('name');

        swal({
                title: "¿Estás seguro?",
                text: "Este mensaje se borrará completamente",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Borrar",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            },
            function(){
                $.ajax({
                    method: 'POST',
                    url: '../deleteMessage/'+idMessage
                })
                    .done(function( status ){
                        swal(status);
                        setTimeout(function(){
                            location.reload();
                        }, 2000);
                    });
            });
    });

    $("#tabs").click(function(){
        $(this).tab('show');
    });

    $("#buttonSearch").click(function(){
        search = $("#search").val();
        window.location.href = '/searchCommunity?s='+search;
    });

	skel.breakpoints({
		wide: '(min-width: 961px) and (max-width: 1880px)',
		normal: '(min-width: 961px) and (max-width: 1620px)',
		narrow: '(min-width: 961px) and (max-width: 1320px)',
		narrower: '(max-width: 960px)',
		mobile: '(max-width: 736px)'
	});

	$(function() {

		var	$window = $(window),
			$body = $('body');

		// Disable animations/transitions until the page has loaded.
			$body.addClass('is-loading');

			$window.on('load', function() {
				$body.removeClass('is-loading');
			});

		// CSS polyfills (IE<9).
			if (skel.vars.IEVersion < 9)
				$(':last-child').addClass('last-child');

		// Fix: Placeholder polyfill.
			$('form').placeholder();

		// Prioritize "important" elements on mobile.
			skel.on('+mobile -mobile', function() {
				$.prioritize(
					'.important\\28 mobile\\29',
					skel.breakpoint('mobile').active
				);
			});

		// Scrolly links.
			$('.scrolly').scrolly();

		// Nav.
			var $nav_a = $('#nav a');

			// Scrolly-fy links.
				$nav_a
					.scrolly()
					.on('click', function(e) {

						var t = $(this),
							href = t.attr('href');

						if (href[0] != '#')
							return;

						e.preventDefault();

						// Clear active and lock scrollzer until scrolling has stopped
							$nav_a
								.removeClass('active')
								.addClass('scrollzer-locked');

						// Set this link to active
							t.addClass('active');

					});

			// Initialize scrollzer.
				var ids = [];

				$nav_a.each(function() {

					var href = $(this).attr('href');

					if (href[0] != '#')
						return;

					ids.push(href.substring(1));

				});

				$.scrollzer(ids, { pad: 200, lastHack: true });

		// Header (narrower + mobile).

			// Toggle.
				$(
					'<div id="headerToggle">' +
						'<a href="#header" class="toggle"></a>' +
					'</div>'
				)
					.appendTo($body);

			// Header.
				$('#header')
					.panel({
						delay: 500,
						hideOnClick: true,
						hideOnSwipe: true,
						resetScroll: true,
						resetForms: true,
						side: 'left',
						target: $body,
						visibleClass: 'header-visible'
					});

			// Fix: Remove transitions on WP<10 (poor/buggy performance).
				if (skel.vars.os == 'wp' && skel.vars.osVersion < 10)
					$('#headerToggle, #header, #main')
						.css('transition', 'none');

	});

})(jQuery);