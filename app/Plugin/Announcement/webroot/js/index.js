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
	$.fn.Announcement = function(id, is_chief) {
		var t = this;
		var url = $.Common.urlBlock(id, 'announcement/edit');
		if(is_chief) {
			var content = $('#'+id+'_content');
			content.dblclick(function(event) {
				$.pjax.click(event, t, {'url': url});
			}).hover(function() {
				content.stop(false, true).show("highlight", {}, 2000);
			}, function(){});
		}
	}
})(jQuery);