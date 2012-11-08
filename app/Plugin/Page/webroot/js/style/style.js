/**
 * ページスタイル js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.PageStyle = function() {
		var tag = 'body';
		var color = $.getHexCode(tag, 'color');
		var bgcolor = $.getHexCode(tag, 'background-color');
		$('#page_setting_color').val(color);
		$('#page_setting_bgcolor').val(bgcolor);

		$('#pages_style_button').click(function(e){
			$('body').css('color', color);
			$('body').css('background-color', bgcolor);
			// location.reload();
		});

	}
	$.getHexCode = function(tag, style) {
		var style_val = $(tag).css(style);
		var code = $.Common.getRGBtoHex(style_val);
		var hex_code = $.Common.getHex(code['r'], code['g'], code['b']);
		return hex_code;
	}
})(jQuery);