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
	$(function(){
		// ヘッダーメニュー
		var hover = false ;
		var hmenu = $("#nc-hmenu");
		var nc_hmenu_arrow = $("#nc-hmenu-arrow");

		if(!hmenu.get(0))
			return;
		if($._mode == 0 && $._display_header_menu == 1) {
			$(document).mousemove(function(e){
				if(e.pageY-$(window).scrollTop() <= 40) {
					hover = true;
					setTimeout(function(){
						if(hover == true) {
							hmenu.stop(true, false).animate({top:'0'}, 50, function() {
								nc_hmenu_arrow.addClass('nc-arrow-up');
							});
						}
					}, 500);
				} else {
					hover = false;
					hmenu.stop(true, false).animate({top: '-40px'}, 50, function(){
						nc_hmenu_arrow.removeClass('nc-arrow-up');
					});
				}
			});
		}
		nc_hmenu_arrow.click(function(e){
			if(nc_hmenu_arrow.hasClass('nc-arrow-up')) {
				hover = false;
				hmenu.stop(true, false).animate({top: '-40px'}, 50, function(){
					nc_hmenu_arrow.removeClass('nc-arrow-up');
				});
			} else {
				hover = true;
				hmenu.stop(true, false).animate({top:'0'}, 50, function() {
					nc_hmenu_arrow.addClass('nc-arrow-up');
				});
			}
			return false;
		});

		// ログイン
		var nc_login = $('#nc-login');
		nc_login.click(function(e){
			var url = nc_login.attr('href');
			$.PagesCommon.showLogin(e, url);
		});
		var nc_pages_setting = $('#nc-pages-setting');
		nc_pages_setting.click(function(e){
			var url = nc_pages_setting.attr('href');
			$.PagesCommon.showPageSetting(e, url);
		});
	});

	// ページ共通
	$.PagesCommon ={
		showLogin: function(e, url) {
			e.preventDefault();
			$.Common.showDialog('nc_login_dialog', {'url' : url}, {'title' : __('Login')});
		},
		showPageSetting: function(e, url) {
			var id = 'nc-pages-setting-dialog-outer', w, h;
			var a = $(e.target);
			var close_url = a.attr('data-page-setting-url');
			var show_url = a.attr('href');

			e.preventDefault();
			var dialog_outer_el = $('#' + id);
			var dialog_el = dialog_outer_el.children(':first');
			var arrow_outer = $('#nc-pages-setting-arrow-outer');
			var arrow = arrow_outer.children(':first');

			if(arrow.get(0) && !arrow.hasClass('nc-arrow-left')) {
				arrow_outer.click();
			} else if(dialog_outer_el.get(0)) {
				w = dialog_el.outerWidth();
				$('.nc-pages-setting-arrow', dialog_el).addClass('nc-arrow-right').removeClass('nc-arrow-left');
				dialog_el.animate({'left': '-' + w + 'px'}, 500, function() {
					dialog_outer_el.remove();
				});

				// 閉じる(Sessionクリア)
				$.get(url, function(res){
					a.attr('data-page-setting-url', show_url);
					a.attr('href', close_url);
				});
			} else {
				// 表示
				$.get(url,function(res) {
					dialog_outer_el = $('<div id="' + id + '" style="visibility:hidden;"></div>').appendTo($(document.body));
					dialog_outer_el.html(res);
					dialog_el = dialog_outer_el.children(':first');
					w = dialog_el.outerWidth();
					dialog_el.css({'left' :  '-' + w + 'px'});
					dialog_outer_el.css('visibility','visible');
					dialog_el.animate({'left': 0}, 500);

					a.attr('data-page-setting-url', show_url);
					a.attr('href', close_url);
				});
			}
		}
	}
})(jQuery);