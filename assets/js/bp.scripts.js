function bp_nav() {

	var nav_init_height
	var body_offset_y
	var is_megamenu = $('body[class*="mega-menu"]').length

	function manage_anchor() {

		var local_offset
		var anchor_offset
		var final_offset

		if($('.site-header').length) {
			if($(window).width() > 768) {
				local_offset = $('.site-header').outerHeight()
			} else {
				local_offset = $('.site-header').outerHeight()
			}
		} else {
			local_offset = 0
		}

		// The function actually applying the offset
		function go_to_anchor() {

			var anchor = location.hash

			if(anchor.length !== 0 && $(anchor).length) {

				anchor_offset = $(location.hash).offset().top
				final_offset = anchor_offset - local_offset
				window.scrollTo(window.scrollX, final_offset)

			}

		}

		$('.main-navigation .main-nav-wrap').localScroll({
			onBefore: function() {
				this.offset = {
					top: -$('.header-nav-wrap').outerHeight(),
				}
			}
		})

		window.setTimeout(go_to_anchor, 250)

	}

	manage_anchor()

	if(is_megamenu) return;

	nav_init_height = $('.main-navigation .main-nav-wrap').outerHeight()

	function openclose_nav() {

		var height_timer
		var toggle = $('.menu-toggle')
		var menu_wrap = $('.main-navigation')
		var menu = $('.main-navigation .main-nav-wrap')
		var all = $('.main-navigation .main-nav-wrap *')
		var li = $('.main-navigation .main-nav > ul > li')
		var main_nav = $('.main-navigation .main-nav')
		var background = $('.menu-background')
		var li_height
		var toggle_offset
		var main_nav_margin

		$('body').toggleClass('menu-open')
		menu_wrap.toggleClass('toggled')

		var viewport_height = viewport.h
		var menu_h = menu.outerHeight()

		if($('body').hasClass('menu-open')) {
			body_offset_y = $(window).scrollTop()
			menu_max_height = viewport_height + 'px'
			$('html').addClass('lock-body')
			$('body').css('margin-top', -body_offset_y)
			li_height = $('a', li).outerHeight()
			li.css('height', li_height + 'px')
			if($('> div', menu).length == 1 || $('> div', menu).length == 2) {
				toggle_offset = toggle.offset()
				main_nav_margin = parseInt($('.header-nav-wrap').height())
				main_nav.css('margin-top', main_nav_margin + 'px')
				main_nav.css({height: 'calc(100% - ' + main_nav_margin + 'px)'})
			}
			if($('.main-nav.widgetized').length) {
	      new SimpleBar($('.main-nav.widgetized')[0])
		  }
			height_timer_init()
		} else {
			menu_max_height = nav_init_height
			$('html').removeClass('lock-body')
			$('body').css('margin-top', '')
			$(window).scrollTop(body_offset_y)
			height_timer_unset()
		}

		toggle.toggleClass('a-disappear')
		menu.css('height', menu_max_height).toggleClass('a-translate-xo a-appear a-scale-1')

		all.removeClass('clicked hidden')

		setTimeout(function(){
			toggle.toggleClass('clicked')
			toggle.toggleClass('a-disappear')
		}, 500)

		function height_timer_init() {
			height_timer = setInterval(update_height, 250)
			// console.log('setIh')
		}

		function height_timer_unset() {
			window.clearInterval(height_timer)
			// console.log('unsetIh')
		}

		function update_height() {
			var new_height = window.innerHeight
			if(new_height != viewport_height) {
				viewport_height = new_height
				menu_max_height = viewport_height + 'px'
				menu.css('height', menu_max_height)
			}
			// console.log('update_height')
		}
	}

	$( ".menu-toggle" ).click(function() {
		openclose_nav()
	})

	$('.bp-search-item').click(function() {
		$('.navigation-search').removeClass('nav-search-active')
		$('.search-item').removeClass('active close-search')
	})

	$('.main-navigation .main-nav-wrap a').click(function(e){
		var $this = $(this)
		var $t_li = $this.closest('li')
		var $all_li = $this.closest('.main-nav').find('> ul > li')
		var submenu = $t_li.find('.sub-menu')

		/*
		if($t_li.hasClass('search-item')) {
		 $('.navigation-search').toggleClass('nav-search-active')
			$t_li.toggleClass('close-search')
		} */

		if(viewport.w < 768 && !submenu.length) {
			if($this.parent().hasClass('search-item')) return;
			openclose_nav()
		} else
		if (viewport.w < 768 && submenu.length) {
			e.preventDefault()
			$('.menu-item-has-children').removeClass('clicked')
			$all_li.addClass('hidden')
			$t_li.removeClass('hidden').addClass('clicked')
		}
	})

	/* TBR
	$('.mobile-bar-items .search-item').click(function(e){
		$('.navigation-search').toggleClass('nav-search-active')
		$(this).toggleClass('close-search')
	}) */

	$('.sub-menu').click(function() {
		var viewport_w = viewport.w
		if (viewport.w < 768) {
			$('.menu-item-has-children').removeClass('clicked')
			$('.main-nav-wrap li').removeClass('clicked hidden')
		}
	})

}

function content_margin() {

	var timer
	var margin

	function work() {
		if(($('.flex-vertical-align').length && !$('.nav-is-fixed').length)
		|| ($('.nav-is-fixed').length && $('.no-content-margin').length)
		|| (!$('.nav-is-fixed').length)
		|| ($('.header-on-left').length && viewport.w > 768)) {
			$('#page').css('margin-top', '0px')
			return
		}
		margin = parseInt($('.header-nav-wrap').outerHeight())
		$('#page').css('margin-top', margin + 'px')
	}

	function update_check() {
		ui_update && setTimeout(work, 150)
	}

	function init() {
		setTimeout(work, 150)
		timer = setInterval(update_check, 250)
	}

	$('body').imagesLoaded(function() {
		init()
	})
}

function content_height() {

	// content_height()
	// makes .site-content height equal to available viewport height (minus header & footer) if it's smaller
	// in other words, total page height will be always full height

	var timer
	var content_h
	var footer_h
	var header_h
	var header_w
	var site_h
	var eval_h


	function is_sidebar() {
		if($('#right-sidebar').length || $('#left-sidebar').length) return true
		return false
	}

	function sidebar_check() {

		if(!is_sidebar()) return false

		$('.site-content').addClass('flex-column')
		site_h = $('#page').outerHeight(true, true)

		if(site_h < content_h) {
			console.log('sidebar_check', site_h, viewport.h)
			return false
		}

		return true

	}

	function reset() {
		console.log('reset')
		$('.site-content').css('height', '')
		$('.site-content').removeClass('flex-vertical-align flex-column')
	}

	function work_same_width() {

		header_h = $('.header-nav-wrap').outerHeight(true,true)+1 // add +1 as failsafe when header height has decimal
		footer_h = $('.site-footer').outerHeight(true,true)
		site_h = ($('body').hasClass('nav-is-fixed')) ? header_h + $('body').outerHeight(true, true) : $('body').outerHeight(true, true)

		if(site_h <= viewport.h) {
			content_h = viewport.h - header_h - footer_h

			/**
			console.log('site_h', site_h)
			console.log('header_h ', header_h)
			console.log('footer_h ', footer_h)
			console.log('viewport_height ', viewport.h)
			console.log('content_h ', content_h)
			**/

			$('.site-content').addClass('flex-vertical-align')

			!sidebar_check() && $('.site-content').css('height', content_h + 'px')

		} else if(site_h > viewport.h) {

			return

		} else {
			/**
			console.log('site_h', site_h)
			console.log('header_h ', header_h)
			console.log('footer_h ', footer_h)
			console.log('viewport_height ', viewport.h)
			console.log('content_h ', content_h)
			**/

			reset()

		}

		// console.log('work_same_width')

	}

	function work_diff_width() {

		site_h = $('.site-content').outerHeight(true, true)
		footer_h = $('.site-footer').outerHeight(true,true)
		eval_h = site_h + footer_h
		content_h = viewport.h - footer_h

		if(eval_h <= viewport.h) {

			$('.site-content').addClass('flex-vertical-align')

			!sidebar_check() && $('.site-content').css('height', content_h + 'px')

		} else {

			reset()

		}

		// console.log('work_diff_width')

	}

	function double_check() {

		var primary_h

		primary_h = $('#primary').outerHeight(true, true)

		if(primary_h > content_h && content_h !== 0) {
			// console.log('double_check reset')
			content_h = 0
			reset()
		}

	}

	function work() {

		header_w = parseInt($('.header-nav-wrap').width())

		/**
		console.log('site_h', site_h)
		console.log('viewport_height ', viewport.h)
		**/
		if(header_w == viewport.w) {

			work_same_width()

		} else {

			work_diff_width()

		}

		double_check()

		// console.log("content_height work()", header_w, viewport.w)

	}

	function update() {
		work()
	}

	function init() {

		if($('.wvhg_action').length) return

		$('body').imagesLoaded( function() {

			if($('.sow-slider-images').length) {

				$('.sow-slider-images').on('cycle-initialized', function( event, opts ) {
					work()
				})

				timer = setInterval(update, 500)

			} else {
				work()
				timer = setInterval(update, 500)
			}
		})

	}

	init()

}


$(window).on('load', function() {
	$(".bp-loader").addClass('a-disappear')
	setTimeout(function() {
		$('.bp-loader').remove()
	}, 1500)
})

$(document).ready(function() {
	if(!$(".bp-loader").hasClass('a-disappear')) {
		$(".bp-loader").addClass('a-disappear')
		setTimeout(function() {
			$('.bp-loader').remove()
		}, 1500);
	}
	bp_nav()
	content_margin()
	content_height()
})
