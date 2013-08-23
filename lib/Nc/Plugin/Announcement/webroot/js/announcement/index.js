/**
 * Announcement js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.Announcement = function(id, url, title) {
		var content;
		if(url) {
			content = $('#'+id+'-content');
			content.dblclick(function(e) {
				content.attr('data-pjax', '#' + id);
				content.attr('data-ajax-url', url);
				$.Common.ajax(e, content);
			}).hover(function(e) {
				content.stop(false, true).effect("highlight", {}, 2000);
			}, function(){}).attr('title', title);
		}
	}
})(jQuery);