/**
 * AnnouncementEdit js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.AnnouncementEdit = function(id) {
		var t = this;
		var block_id = $('#' + id).attr('data-block');

		var wysiwyg = $('#'+id+'_wysiwyg').nc_wysiwyg({
			'focus'    : true,
			'image'    : $.Common.urlBlock(id, 'announcement/image'),
			'file'     : $.Common.urlBlock(id, 'announcement/file'),
		});
		var form = $('#form' + id);

		form.submit(function(e) {
			$.pjax.submit(e, t);
		});
		$('input[name="cancel"]', form).click(function(e) {
			$.pjax.click(e, t, {'url': $.Common.urlBlock(id, 'announcement')});
		});
	}
})(jQuery);