/**
 * ページ共通 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       webroot.js.main
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$(document).ready(function(){
		// ヘッダーメニュー
		var hover = false ;
		var hmenu = $("#nc_hmenu");
		var nc_hmenu_arrow = $("#nc_hmenu_arrow");

		if(!hmenu.get(0))
			return;
		if($._mode == 0 && $._display_header_menu == 1) {
			$(document).mousemove(function(e){
				if(e.pageY-$(window).scrollTop() <= 40) {
					hover = true;
					setTimeout(function(){
						if(hover == true) {
							hmenu.stop(true, false).animate({top:'0'}, 50, function() {
								nc_hmenu_arrow.addClass('nc_arrow_up');
							});
						}
					}, 500);
				} else {
					hover = false;
					hmenu.stop(true, false).animate({top: '-40px'}, 50, function(){
						nc_hmenu_arrow.removeClass('nc_arrow_up');
					});
				}
			});
		}
		if($._mode != 0) {
			// setting modeならばdefault表示
			hmenu.css('top', '0');
			nc_hmenu_arrow.addClass('nc_arrow_up');
		}
		nc_hmenu_arrow.click(function(e){
			if(nc_hmenu_arrow.hasClass('nc_arrow_up')) {
				hover = false;
				hmenu.stop(true, false).animate({top: '-40px'}, 50, function(){
					nc_hmenu_arrow.removeClass('nc_arrow_up');
				});
			} else {
				hover = true;
				hmenu.stop(true, false).animate({top:'0'}, 50, function() {
					nc_hmenu_arrow.addClass('nc_arrow_up');
				});
			}
			return false;
		});

		// ログイン
		var nc_login = $('#nc_login');
		nc_login.click(function(e){
			var url = nc_login.attr('href');
			$.PagesCommon.showLogin(e, url);
		});
		var nc_pages_setting = $('#nc_pages_setting');
		nc_pages_setting.click(function(e){
			var url = nc_pages_setting.attr('href');
			$.PagesCommon.showPageMenu(e, url);
		});
	});

	// ページ共通
	$.PagesCommon ={
		showLogin: function(e, url) {
			e.preventDefault();
			$.Common.showDialog('nc_login_dialog', {'url' : url}, {'title' : __('Login')});
		},
		showPageMenu: function(e, url) {
			var id = 'nc_pages_setting_dialog_outer', w, h;
			e.preventDefault();
			var dialog_outer_el = $('#' + id);
			var dialog_el = dialog_outer_el.children(':first');
			if(dialog_outer_el.get(0)) {
				w = dialog_el.outerWidth();
				$('.nc_pages_setting_arrow', dialog_el).addClass('nc_arrow_right').removeClass('nc_arrow_left');
				dialog_el.animate({'left': '-' + w + 'px'}, 500, function() {
					dialog_outer_el.remove();
				});
			} else {
				$.get(url,function(res) {
					dialog_outer_el = $('<div id="' + id + '" style="visibility:hidden;"></div>').appendTo($(document.body));
					dialog_outer_el.html(res);
					dialog_el = dialog_outer_el.children(':first');
					w = dialog_el.outerWidth();
					dialog_el.css({'left' :  '-' + w + 'px'});
					dialog_outer_el.css('visibility','visible');
					dialog_el.animate({'left': 0}, 500);
				});
			}
		}
	}
})(jQuery);