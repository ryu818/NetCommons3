/**
 * announcement js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.Announcement = function(url) {
		var t = this;
		t.id = t.attr('id');
		t.block_id = t.attr('data-block');

		if(url) {
			var content = $('#'+t.id+'_content');
			content.dblclick(function(event) {
				$.pjax.click(event, t, {'url': url});//'/' + $._block_type +'/' + t.block_id + '/announcement/edit/#' + t.id});
			}).hover(function() {
				content.stop(false, true).show("highlight", {}, 2000);
			}, function(){});
		}
	}
})(jQuery);