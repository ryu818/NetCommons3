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
	$.fn.Announcement = function(id) {
		var t = this, content;
		var block_id = $('#' + id).attr('data-block');
		var url = $.Common.urlBlock(block_id, 'announcement/edits');
		content = $('#'+id+'-content');
		content.dblclick(function(event) {
			// TODO: $.Common.ajaxに修正予定
			$.pjax.click(event, t, {'url': url});
		}).hover(function() {
			content.stop(false, true).effect("highlight", {}, 2000);
		}, function(){});
	}
})(jQuery);