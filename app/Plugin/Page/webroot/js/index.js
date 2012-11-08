/**
 * ページ設定 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.Page = function() {
		var dialog_el = $(this);
		var content = $('#nc_pages_setting_content');
		resizeWindow(dialog_el);
	}

/**
 * ウィンドウサイズ　ボックスリサイズ処理
 * @private
 */
	function resizeWindow(dialog_el) {
		var content, content_h = 0, offset= -37;
		var h = $(window).height();
		dialog_el.children().css('height', h + 'px');
		$('.nc_pages_setting_arrow', dialog_el).css('top', h/2 - 10 + 'px');
		
		content = $('#nc_pages_setting_content');
		
		$.each(content.parent().children(), function() {
			if($(this).get(0) != content.get(0)) {
				content_h += parseInt($(this).outerHeight());
			}
		});
		content.css('height', h + offset - content_h - parseInt(dialog_el.css('top'))  - parseInt(content.css('marginTop')) - parseInt(content.css('marginBottom')) - parseInt(content.css('paddingTop')) - parseInt(content.css('paddingBottom')));
	}
/**
 * ウィンドウサイズ　ボックスリサイズ処理
 * window.event
 */
	$(window).resize(function() {
		resizeWindow($('#nc_pages_setting_dialog'));
	});
})(jQuery);