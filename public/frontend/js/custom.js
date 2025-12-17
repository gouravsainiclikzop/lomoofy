$(function() {
    "use strict";

	//Loader	
	$(function preloaderLoad() {
        if($('.preloader').length){
            $('.preloader').delay(200).fadeOut(300);
        }
        $(".preloader_disabler").on('click', function() {
            $("#preloader").hide();
        });
    });
	
	// Script Navigation
	! function(n, e, i, a) {
		n.navigation = function(t, s) {
			var o = {
					responsive: !0,
					mobileBreakpoint:992,
					showDuration: 300,
					hideDuration: 300,
					showDelayDuration: 0,
					hideDelayDuration: 0,
					submenuTrigger: "hover",
					effect: "fade",
					submenuIndicator: !0,
					hideSubWhenGoOut: !0,
					visibleSubmenusOnMobile: !1,
					fixed: !1,
					overlay: !0,
					overlayColor: "rgba(0, 0, 0, 0.5)",
					hidden: !1,
					offCanvasSide: "left",
					onInit: function() {},
					onShowOffCanvas: function() {},
					onHideOffCanvas: function() {}
				},
				u = this,
				r = Number.MAX_VALUE,
				d = 1,
				f = "click.nav touchstart.nav",
				l = "mouseenter.nav",
				c = "mouseleave.nav";
			u.settings = {};
			var t = (n(t), t);
			n(t).find(".nav-menus-wrapper").prepend("<span class='nav-menus-wrapper-close-button'>✕</span>"), n(t).find(".nav-search").length > 0 && n(t).find(".nav-search").find("form").prepend("<span class='nav-search-close-button'>✕</span>"), u.init = function() {
				u.settings = n.extend({}, o, s), "right" == u.settings.offCanvasSide && n(t).find(".nav-menus-wrapper").addClass("nav-menus-wrapper-right"), u.settings.hidden && (n(t).addClass("navigation-hidden"), u.settings.mobileBreakpoint = 99999), v(), u.settings.fixed && n(t).addClass("navigation-fixed"), n(t).find(".nav-toggle").on("click touchstart", function(n) {
					n.stopPropagation(), n.preventDefault(), u.showOffcanvas(), s !== a && u.callback("onShowOffCanvas")
				}), n(t).find(".nav-menus-wrapper-close-button").on("click touchstart", function() {
					u.hideOffcanvas(), s !== a && u.callback("onHideOffCanvas")
				}), n(t).find(".nav-search-button").on("click touchstart", function(n) {
					n.stopPropagation(), n.preventDefault(), u.toggleSearch()
				}), n(t).find(".nav-search-close-button").on("click touchstart", function() {
					u.toggleSearch()
				}), n(t).find(".megamenu-tabs").length > 0 && y(), n(e).resize(function() {
					m(), C()
				}), m(), s !== a && u.callback("onInit")
			};
			var v = function() {
				n(t).find("li").each(function() {
					n(this).children(".nav-dropdown,.megamenu-panel").length > 0 && (n(this).children(".nav-dropdown,.megamenu-panel").addClass("nav-submenu"), u.settings.submenuIndicator && n(this).children("a").append("<span class='submenu-indicator'><span class='submenu-indicator-chevron'></span></span>"))
				})
			};
			u.showSubmenu = function(e, i) {
				g() > u.settings.mobileBreakpoint && n(t).find(".nav-search").find("form").slideUp(), "fade" == i ? n(e).children(".nav-submenu").stop(!0, !0).delay(u.settings.showDelayDuration).fadeIn(u.settings.showDuration) : n(e).children(".nav-submenu").stop(!0, !0).delay(u.settings.showDelayDuration).slideDown(u.settings.showDuration), n(e).addClass("nav-submenu-open")
			}, u.hideSubmenu = function(e, i) {
				"fade" == i ? n(e).find(".nav-submenu").stop(!0, !0).delay(u.settings.hideDelayDuration).fadeOut(u.settings.hideDuration) : n(e).find(".nav-submenu").stop(!0, !0).delay(u.settings.hideDelayDuration).slideUp(u.settings.hideDuration), n(e).removeClass("nav-submenu-open").find(".nav-submenu-open").removeClass("nav-submenu-open")
			};
			var h = function() {
					n("body").addClass("no-scroll"), u.settings.overlay && (n(t).append("<div class='nav-overlay-panel'></div>"), n(t).find(".nav-overlay-panel").css("background-color", u.settings.overlayColor).fadeIn(300).on("click touchstart", function(n) {
						u.hideOffcanvas()
					}))
				},
				p = function() {
					n("body").removeClass("no-scroll"), u.settings.overlay && n(t).find(".nav-overlay-panel").fadeOut(400, function() {
						n(this).remove()
					})
				};
			u.showOffcanvas = function() {
				h(), "left" == u.settings.offCanvasSide ? n(t).find(".nav-menus-wrapper").css("transition-property", "left").addClass("nav-menus-wrapper-open") : n(t).find(".nav-menus-wrapper").css("transition-property", "right").addClass("nav-menus-wrapper-open")
			}, u.hideOffcanvas = function() {
				n(t).find(".nav-menus-wrapper").removeClass("nav-menus-wrapper-open").on("webkitTransitionEnd moztransitionend transitionend oTransitionEnd", function() {
					n(t).find(".nav-menus-wrapper").css("transition-property", "none").off()
				}), p()
			}, u.toggleOffcanvas = function() {
				g() <= u.settings.mobileBreakpoint && (n(t).find(".nav-menus-wrapper").hasClass("nav-menus-wrapper-open") ? (u.hideOffcanvas(), s !== a && u.callback("onHideOffCanvas")) : (u.showOffcanvas(), s !== a && u.callback("onShowOffCanvas")))
			}, u.toggleSearch = function() {
				"none" == n(t).find(".nav-search").find("form").css("display") ? (n(t).find(".nav-search").find("form").slideDown(), n(t).find(".nav-submenu").fadeOut(200)) : n(t).find(".nav-search").find("form").slideUp()
			};
			var m = function() {
					u.settings.responsive ? (g() <= u.settings.mobileBreakpoint && r > u.settings.mobileBreakpoint && (n(t).addClass("navigation-portrait").removeClass("navigation-landscape"), D()), g() > u.settings.mobileBreakpoint && d <= u.settings.mobileBreakpoint && (n(t).addClass("navigation-landscape").removeClass("navigation-portrait"), k(), p(), u.hideOffcanvas()), r = g(), d = g()) : k()
				},
				b = function() {
					n("body").on("click.body touchstart.body", function(e) {
						0 === n(e.target).closest(".navigation").length && (n(t).find(".nav-submenu").fadeOut(), n(t).find(".nav-submenu-open").removeClass("nav-submenu-open"), n(t).find(".nav-search").find("form").slideUp())
					})
				},
				g = function() {
					return e.innerWidth || i.documentElement.clientWidth || i.body.clientWidth
				},
				w = function() {
					n(t).find(".nav-menu").find("li, a").off(f).off(l).off(c)
				},
				C = function() {
					if (g() > u.settings.mobileBreakpoint) {
						var e = n(t).outerWidth(!0);
						n(t).find(".nav-menu").children("li").children(".nav-submenu").each(function() {
							n(this).parent().position().left + n(this).outerWidth() > e ? n(this).css("right", 0) : n(this).css("right", "auto")
						})
					}
				},
				y = function() {
					function e(e) {
						var i = n(e).children(".megamenu-tabs-nav").children("li"),
							a = n(e).children(".megamenu-tabs-pane");
						n(i).on("click.tabs touchstart.tabs", function(e) {
							e.stopPropagation(), e.preventDefault(), n(i).removeClass("active"), n(this).addClass("active"), n(a).hide(0).removeClass("active"), n(a[n(this).index()]).show(0).addClass("active")
						})
					}
					if (n(t).find(".megamenu-tabs").length > 0)
						for (var i = n(t).find(".megamenu-tabs"), a = 0; a < i.length; a++) e(i[a])
				},
				k = function() {
					w(), n(t).find(".nav-submenu").hide(0), navigator.userAgent.match(/Mobi/i) || navigator.maxTouchPoints > 0 || "click" == u.settings.submenuTrigger ? n(t).find(".nav-menu, .nav-dropdown").children("li").children("a").on(f, function(i) {
						if (u.hideSubmenu(n(this).parent("li").siblings("li"), u.settings.effect), n(this).closest(".nav-menu").siblings(".nav-menu").find(".nav-submenu").fadeOut(u.settings.hideDuration), n(this).siblings(".nav-submenu").length > 0) {
							if (i.stopPropagation(), i.preventDefault(), "none" == n(this).siblings(".nav-submenu").css("display")) return u.showSubmenu(n(this).parent("li"), u.settings.effect), C(), !1;
							if (u.hideSubmenu(n(this).parent("li"), u.settings.effect), "_blank" == n(this).attr("target") || "blank" == n(this).attr("target")) e.open(n(this).attr("href"));
							else {
								if ("#" == n(this).attr("href") || "" == n(this).attr("href")) return !1;
								e.location.href = n(this).attr("href")
							}
						}
					}) : n(t).find(".nav-menu").find("li").on(l, function() {
						u.showSubmenu(this, u.settings.effect), C()
					}).on(c, function() {
						u.hideSubmenu(this, u.settings.effect)
					}), u.settings.hideSubWhenGoOut && b()
				},
				D = function() {
					w(), n(t).find(".nav-submenu").hide(0), u.settings.visibleSubmenusOnMobile ? n(t).find(".nav-submenu").show(0) : (n(t).find(".nav-submenu").hide(0), n(t).find(".submenu-indicator").removeClass("submenu-indicator-up"), u.settings.submenuIndicator ? n(t).find(".submenu-indicator").on(f, function(e) {
						return e.stopPropagation(), e.preventDefault(), u.hideSubmenu(n(this).parent("a").parent("li").siblings("li"), "slide"), u.hideSubmenu(n(this).closest(".nav-menu").siblings(".nav-menu").children("li"), "slide"), "none" == n(this).parent("a").siblings(".nav-submenu").css("display") ? (n(this).addClass("submenu-indicator-up"), n(this).parent("a").parent("li").siblings("li").find(".submenu-indicator").removeClass("submenu-indicator-up"), n(this).closest(".nav-menu").siblings(".nav-menu").find(".submenu-indicator").removeClass("submenu-indicator-up"), u.showSubmenu(n(this).parent("a").parent("li"), "slide"), !1) : (n(this).parent("a").parent("li").find(".submenu-indicator").removeClass("submenu-indicator-up"), void u.hideSubmenu(n(this).parent("a").parent("li"), "slide"))
					}) : k())
				};
			u.callback = function(n) {
				s[n] !== a && s[n].call(t)
			}, u.init()
		}, n.fn.navigation = function(e) {
			return this.each(function() {
				if (a === n(this).data("navigation")) {
					var i = new n.navigation(this, e);
					n(this).data("navigation", i)
				}
			})
		}
	}
	(jQuery, window, document), $(document).ready(function() {
		$("#navigation").navigation()
	});
	
	// Product Preview
	$('.sp-wrap').smoothproducts();

	// Range Slider Script
	$(".js-range-slider").ionRangeSlider({
		type: "double",
		min: 0,
		max: 1000,
		from:100,
		to:750,
		grid: true
	});
	
	// Tooltip
	$('[data-toggle="tooltip"]').tooltip();
	
	// Snackbar for Add To Cart Product
	$('.snackbar-addcart').click(function() { 
		Snackbar.show({
			text: 'Your product was added to cart successfully!',
			pos: 'top-right',
			showAction: false,
			actionText: "Dismiss",
			duration: 3000,
			textColor: '#fff',
			backgroundColor:'#151515'
		}); 
	}); 
	
	// Snackbar for wishlist Product
	$('.snackbar-wishlist').click(function(e) { 
		e.preventDefault();
		const $btn = $(this);
		const productId = $btn.data('product-id');
		
		if (!productId) {
			console.error('Product ID not found');
			return;
		}
		
		// Get session ID
		let sessionId = localStorage.getItem('session_id');
		if (!sessionId) {
			sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
			localStorage.setItem('session_id', sessionId);
		}
		
		// Check if already in wishlist (heart icon state)
		const isInWishlist = $btn.find('i').hasClass('fas') || $btn.hasClass('wishlist-active');
		
		if (isInWishlist) {
			// Remove from wishlist
			$.ajax({
				url: '/api/wishlist/product/' + productId,
				method: 'DELETE',
				data: {
					session_id: sessionId
				},
				success: function(response) {
					if (response.success) {
						// Update icon to empty heart
						$btn.find('i').removeClass('fas').addClass('far').css('color', '');
						$btn.removeClass('wishlist-active');
						
						Snackbar.show({
							text: 'Product removed from wishlist',
							pos: 'top-right',
							showAction: false,
							duration: 3000,
							textColor: '#fff',
							backgroundColor: '#151515'
						});
						
						// Update wishlist count
						updateWishlistCount();
					}
				},
				error: function(xhr) {
					// Even if product not found, treat as success (idempotent)
					if (xhr.status === 404 || (xhr.responseJSON && xhr.responseJSON.message && xhr.responseJSON.message.includes('not in wishlist'))) {
						// Update icon to empty heart
						$btn.find('i').removeClass('fas').addClass('far').css('color', '');
						$btn.removeClass('wishlist-active');
						updateWishlistCount();
					} else {
						console.error('Error removing from wishlist:', xhr);
						Snackbar.show({
							text: 'Failed to remove from wishlist',
							pos: 'top-right',
							showAction: false,
							duration: 3000,
							textColor: '#fff',
							backgroundColor: '#dc3545'
						});
					}
				}
			});
		} else {
			// Add to wishlist
			$.ajax({
				url: '/api/wishlist',
				method: 'POST',
				data: {
					product_id: productId,
					session_id: sessionId
				},
				success: function(response) {
					if (response.success) {
						// Update icon to filled red heart
						$btn.find('i').removeClass('far').addClass('fas').css('color', '#dc3545');
						$btn.addClass('wishlist-active');
						
						Snackbar.show({
							text: 'Product added to wishlist successfully!',
							pos: 'top-right',
							showAction: false,
							duration: 3000,
							textColor: '#fff',
							backgroundColor: '#151515'
						});
						
						// Update wishlist count
						updateWishlistCount();
					}
				},
				error: function(xhr) {
					console.error('Error adding to wishlist:', xhr);
					const message = xhr.responseJSON && xhr.responseJSON.message 
						? xhr.responseJSON.message 
						: 'Failed to add to wishlist';
					Snackbar.show({
						text: message,
						pos: 'top-right',
						showAction: false,
						duration: 3000,
						textColor: '#fff',
						backgroundColor: '#dc3545'
					});
				}
			});
		}
	});
	
	// Update wishlist count in header
	function updateWishlistCount() {
		let sessionId = localStorage.getItem('session_id');
		if (!sessionId) {
			sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
			localStorage.setItem('session_id', sessionId);
		}
		
		$.ajax({
			url: '/api/wishlist/count',
			method: 'GET',
			data: { session_id: sessionId },
			success: function(response) {
				if (response.success) {
					$('.dn-counter').text(response.count || '0');
				}
			}
		});
	}
	
	// Initialize wishlist count on page load
	$(document).ready(function() {
		updateWishlistCount();
	});
	
	// Bottom To Top Scroll Script
	$(window).on('scroll', function() {
		var height = $(window).scrollTop();
		if (height > 100) {
			$('#back2Top').fadeIn();
		} else {
			$('#back2Top').fadeOut();
		}
	});
	
	
	// Script For Fix Header on Scroll
	$(window).on('scroll', function() {    
		var scroll = $(window).scrollTop();

		if (scroll >= 50) {
			$(".header").addClass("header-fixed");
		} else {
			$(".header").removeClass("header-fixed");
		}
	});
	
	// Brand-slide
	if ($('.smart-brand').length && !$('.smart-brand').hasClass('slick-initialized')) {
		if ($('.smart-brand').children().length > 0) {
			$('.smart-brand').slick({
			  slidesToShow:6,
			  arrows: false,
			  dots: false,
			  infinite: true,
			  autoplaySpeed: 2000,
			  autoplay:true,
			  responsive: [
				{
				  breakpoint: 1024,
				  settings: {
					arrows: false,
					dots: false,
					slidesToShow:4
				  }
				},
				{
				  breakpoint: 600,
				  settings: {
					arrows: false,
					dots: false,
					slidesToShow:3
				  }
				}
			  ]
			});
		}
	}

	// reviews-slide
	if ($('.reviews-slide').length && !$('.reviews-slide').hasClass('slick-initialized')) {
		if ($('.reviews-slide').children().length > 0) {
			$('.reviews-slide').slick({
			  slidesToShow:1,
			  arrows: true,
			  dots: false,
			  infinite: true,
			  autoplaySpeed: 2000,
			  autoplay:true,
			  responsive: [
				{
				  breakpoint: 1024,
				  settings: {
					arrows: true,
					dots: false,
					slidesToShow:1
				  }
				},
				{
				  breakpoint: 600,
				  settings: {
					arrows: true,
					dots: false,
					slidesToShow:1
				  }
				}
			  ]
			});
		}
	}
	
	// quick_view_slide
	if ($('.quick_view_slide').length && !$('.quick_view_slide').hasClass('slick-initialized')) {
		if ($('.quick_view_slide').children().length > 0) {
			$('.quick_view_slide').slick({
			  slidesToShow:1,
			  arrows: true,
			  dots: true,
			  infinite: true,
			  autoplaySpeed: 2000,
			  autoplay:true,
			  responsive: [
				{
				  breakpoint: 1024,
				  settings: {
					arrows: true,
					dots: true,
					slidesToShow:1
				  }
				},
				{
				  breakpoint: 600,
				  settings: {
					arrows: true,
					dots: true,
					slidesToShow:1
				  }
				}
			  ]
			});
		}
	}
	
	// item Slide
	if ($('.slide_items').length && !$('.slide_items').hasClass('slick-initialized')) {
		// Check if element has children before initializing
		if ($('.slide_items').children().length > 0) {
			$('.slide_items').slick({
			  slidesToShow:4,
			  arrows: true,
			  dots: false,
			  infinite: true,
			  speed: 500,
			  cssEase: 'linear',
			  autoplaySpeed: 2000,
			  autoplay:true,
			  responsive: [
				{
				  breakpoint: 1024,
				  settings: {
					arrows: true,
					dots: false,
					slidesToShow:3
				  }
				},
				{
				  breakpoint: 600,
				  settings: {
					arrows: true,
					dots: false,
					slidesToShow:1
				  }
				}
			  ]
			});
		}
	}
	
	// Home Slider
	if ($('.home-slider').length && !$('.home-slider').hasClass('slick-initialized')) {
		if ($('.home-slider').children().length > 0) {
			$('.home-slider').slick({
			  centerMode:false,
			  slidesToShow:1,
			  arrows: true,
			  dots: true,
			  responsive: [
				{
				  breakpoint: 768,
				  settings: {
					arrows:true,
					slidesToShow:1
				  }
				},
				{
				  breakpoint: 480,
				  settings: {
					arrows: true,
					slidesToShow:1
				  }
				}
			  ]
			});
		}
	}
	
	// fullwidth home slider
	function inlineCSS() {
		$(".home-slider .item").each(function() {
			var attrImageBG = $(this).attr('data-background-image');
			var attrColorBG = $(this).attr('data-background-color');
			if (attrImageBG !== undefined) {
				$(this).css('background-image', 'url(' + attrImageBG + ')');
			}
			if (attrColorBG !== undefined) {
				$(this).css('background', '' + attrColorBG + '');
			}
		});
	}
	inlineCSS();
	
	// Position mega menu centered on viewport
	function positionMegaMenu() {
		$('.mega-menu-item').each(function(index) {
			var $menuItem = $(this);
			var $megaPanel = $menuItem.find('.mega-menu-panel');
			
			if ($megaPanel.length) {
				// Add unique identifier to panel for targeting
				var panelId = 'mega-panel-' + index;
				$megaPanel.attr('data-panel-id', panelId);
				
				var updatePosition = function() {
					var menuItemRect = $menuItem[0].getBoundingClientRect();
					var viewportWidth = $(window).width();
					var viewportCenter = viewportWidth / 2;
					
					// For fixed positioning, use viewport coordinates (no scrollY needed)
					var topPosition = menuItemRect.bottom;
					
					// Center horizontally on viewport, position below menu item
					$megaPanel.css({
						'top': topPosition + 'px',
						'left': '50vw',
						'transform': 'translateX(-50%)',
						'position': 'fixed'
					});
					
					// Wait a bit for panel to be visible and rendered
					setTimeout(function() {
						// Calculate arrow position to point to menu item center
						var menuItemCenter = menuItemRect.left + (menuItemRect.width / 2);
						var panelWidth = $megaPanel.outerWidth() || 900; // Default to min-width
						var panelLeftEdge = viewportCenter - (panelWidth / 2);
						
						// Calculate arrow position relative to panel left edge
						var arrowLeft = menuItemCenter - panelLeftEdge;
						
						// Clamp arrow position to stay within reasonable bounds (27px to panelWidth-27px from edges)
						var minArrowLeft = 27;
						var maxArrowLeft = panelWidth - 27;
						arrowLeft = Math.max(minArrowLeft, Math.min(maxArrowLeft, arrowLeft));
						
						// Set CSS variable on the panel
						$megaPanel[0].style.setProperty('--arrow-left', arrowLeft + 'px');
						
						// Also update via style tag for :before pseudo-element
						var styleId = 'mega-menu-arrow-' + panelId;
						if ($('#' + styleId).length === 0) {
							$('head').append('<style id="' + styleId + '"></style>');
						}
						
						// Use attribute selector for more reliable targeting
						var selector = '.mega-menu-panel[data-panel-id="' + panelId + '"]:before';
						$('#' + styleId).text(selector + ' { left: ' + arrowLeft + 'px !important; transform: translateX(-50%) rotate(45deg) !important; }');
					}, 50);
				};
				
				$menuItem.on('mouseenter', function() {
					updatePosition();
				});
				
				// Store update function for scroll/resize events
				$menuItem.data('updateMegaMenu', updatePosition);
			}
		});
	}
	
	// Initialize on page load
	positionMegaMenu();
	
	// Position regular dropdown arrows to point to menu items
	function positionDropdownArrows() {
		$('.nav-menu > li').each(function(index) {
			var $menuItem = $(this);
			var $dropdown = $menuItem.find('> .nav-dropdown');
			
			// Skip mega menu items (they have their own positioning)
			if ($menuItem.hasClass('mega-menu-item')) {
				return;
			}
			
			if ($dropdown.length) {
				var dropdownId = 'dropdown-' + index;
				$dropdown.attr('data-dropdown-id', dropdownId);
				
				var styleId = 'dropdown-arrow-' + dropdownId;
				if ($('#' + styleId).length === 0) {
					$('head').append('<style id="' + styleId + '"></style>');
				}
				
				var updateArrowPosition = function() {
					var menuItemRect = $menuItem[0].getBoundingClientRect();
					var menuItemCenter = menuItemRect.left + (menuItemRect.width / 2);
					var dropdownLeft = $dropdown.offset().left;
					var dropdownWidth = $dropdown.outerWidth() || 250;
					
					// Calculate arrow position relative to dropdown left edge
					// Arrow should point to center of menu item
					var arrowLeft = menuItemCenter - dropdownLeft;
					
					// Clamp arrow position to stay within reasonable bounds (27px to dropdownWidth-27px)
					var minArrowLeft = 27;
					var maxArrowLeft = dropdownWidth - 27;
					arrowLeft = Math.max(minArrowLeft, Math.min(maxArrowLeft, arrowLeft));
					
					// Update arrow position via dynamic style
					var selector = '.nav-dropdown[data-dropdown-id="' + dropdownId + '"]:before';
					$('#' + styleId).text(selector + ' { left: ' + arrowLeft + 'px !important; }');
				};
				
				$menuItem.on('mouseenter', function() {
					setTimeout(updateArrowPosition, 50);
				});
				
				// Store update function
				$menuItem.data('updateDropdownArrow', updateArrowPosition);
			}
		});
	}
	
	// Initialize dropdown arrows
	positionDropdownArrows();
	
	// Update position on scroll and resize
	var scrollTimeout;
	$(window).on('scroll resize', function() {
		clearTimeout(scrollTimeout);
		scrollTimeout = setTimeout(function() {
			// Update mega menu positions
			$('.mega-menu-item').each(function() {
				var $menuItem = $(this);
				var $megaPanel = $menuItem.find('.mega-menu-panel');
				
				if ($megaPanel.is(':visible')) {
					var updatePosition = $menuItem.data('updateMegaMenu');
					if (updatePosition) {
						updatePosition();
					}
				}
			});
			
			// Update regular dropdown arrow positions
			$('.nav-menu > li').each(function() {
				var $menuItem = $(this);
				var $dropdown = $menuItem.find('> .nav-dropdown');
				
				if (!$menuItem.hasClass('mega-menu-item') && $dropdown.is(':visible')) {
					var updateArrow = $menuItem.data('updateDropdownArrow');
					if (updateArrow) {
						updateArrow();
					}
				}
			});
		}, 10);
	});
	
	// Category Slider for Mobile
	if ($('.category-slider').length && !$('.category-slider').hasClass('slick-initialized')) {
		if ($('.category-slider').children().length > 0) {
			$('.category-slider').slick({
				slidesToShow: 6,
				slidesToScroll: 2,
				arrows: true,
				dots: false,
				infinite: true,
				speed: 300,
				autoplay: false,
				responsive: [
					{
						breakpoint: 768,
						settings: {
							slidesToShow: 6,
							slidesToScroll: 2,
							arrows: true,
							dots: false
						}
					},
					{
						breakpoint: 600,
						settings: {
							slidesToShow: 5,
							slidesToScroll: 2,
							arrows: true,
							dots: false
						}
					},
					{
						breakpoint: 500,
						settings: {
							slidesToShow: 5,
							slidesToScroll: 1,
							arrows: true,
							dots: false
						}
					},
					{
						breakpoint: 400,
						settings: {
							slidesToShow: 5,
							slidesToScroll: 1,
							arrows: true,
							dots: false
						}
					},
					{
						breakpoint: 360,
						settings: {
							slidesToShow: 4,
							slidesToScroll: 1,
							arrows: true,
							dots: false
						}
					}
				]
			});
		}
	}
	
	// Handle wishlist link clicks - append session_id to URL
	$(document).on('click', '.wishlist-link', function(e) {
		e.preventDefault();
		let sessionId = localStorage.getItem('session_id');
		if (!sessionId) {
			sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
			localStorage.setItem('session_id', sessionId);
		}
		
		const wishlistUrl = $(this).attr('href');
		const separator = wishlistUrl.includes('?') ? '&' : '?';
		const newUrl = wishlistUrl + separator + 'session_id=' + sessionId;
		window.location.href = newUrl;
	});
	
});