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
		resizeWindow(dialog_el);
		
		$('#nc-pages-setting-arrow-outer').click(function(event){
			var arrow_outer = $(this);
			var arrow = arrow_outer.children(':first');
			var arrow_w = parseInt(arrow_outer.outerWidth());
			var w = parseInt(dialog_el.outerWidth()) - arrow_w;

			if(arrow.hasClass('nc_arrow_left')) {
				dialog_el.stop(true, false).animate({left: '-' + w + 'px'}, 500, function(){
					arrow.addClass('nc_arrow_right');
					arrow.removeClass('nc_arrow_left');
				});
			} else {
				dialog_el.stop(true, false).animate({left:'0'}, 500, function() {
					arrow.addClass('nc_arrow_left');
					arrow.removeClass('nc_arrow_right');
				});
			}
			return false;
		});
	}

/**
 * ウィンドウサイズ　ボックスリサイズ処理
 * @private
 */
	function resizeWindow(dialog_el) {
		var content, content_h = 0, offset= -37;
		var h = $(window).height();
		content = $('.nc-pages-setting-content', dialog_el);
		var marginTop = isNaN(parseInt(content.css('marginTop'))) ? 0 : parseInt(content.css('marginTop'));
		var marginBottom = isNaN(parseInt(content.css('marginBottom'))) ? 0 : parseInt(content.css('marginBottom'));
		var paddingTop = isNaN(parseInt(content.css('paddingTop'))) ? 0 : parseInt(content.css('paddingTop'));
		var paddingBottom = isNaN(parseInt(content.css('paddingBottom'))) ? 0 : parseInt(content.css('paddingBottom'));
		var dialog_top = 40;

		dialog_el.children().css('height' , h + 'px');

		$('.nc-pages-setting-arrow', dialog_el).css('top', h/2 - 10 + 'px');

		var els = $('[data-pages-header]', dialog_el);
		for (var i = 0,len = els.length; i < len; i++) {
			content_h += parseInt($(els[i]).outerHeight());
		}

		content.css('height', h + offset - parseInt(content_h)  - dialog_top - marginTop - marginBottom - paddingTop - paddingBottom);

	}
/**
 * ウィンドウサイズ　ボックスリサイズ処理
 * window.event
 */
	$(window).resize(function() {
		resizeWindow($('#nc-pages-setting-dialog'));
	});
})(jQuery);