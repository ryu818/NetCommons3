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
					hmenu.stop(true, false).animate({top: '-32px'}, 50, function(){
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
				hmenu.stop(true, false).animate({top: '-32px'}, 50, function(){
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
		var url = nc_login.attr('href');
		nc_login.click(function(e){$.PagesCommon.showLogin(e, url);return false;});
	});

	// ページ共通
	$.PagesCommon ={
		showLogin: function(e, url) {
			var nc_login_dialog = $('#nc_login_dialog');
			if(nc_login_dialog.get(0)) {
				nc_login_dialog.dialog('open');
				return;
			}
			$.get(url,
				function(res){
					var dialog_el = $('<div id=nc_login_dialog></div>').appendTo($(document.body));
					dialog_el.html(res);

					$(dialog_el).dialog({
						title: __('Login'),
						zIndex: ++$.Common.zIndex
					});
				}
			);
		}
	}
})(jQuery);