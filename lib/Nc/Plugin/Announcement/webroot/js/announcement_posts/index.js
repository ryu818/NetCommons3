/**
 * お知らせ記事投稿・編集 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.AnnouncementPosts = function(id) {
		var wysiwyg = $('#RevisionContent'+id).nc_wysiwyg({
			autoRegistForm : $('#Form' + id),
			focus : true,
			image : true,
			file : true
		});
		var pre_change_flag = $('#AnnouncementPreChangeFlag' + id);
		var date = $('#AnnouncementPreChangeDate' + id);
		var area_outer = $('#announcement-posts-widget-area' + id);
		var items = $('.nc-widget-area-title:first', area_outer).disableSelection();

		pre_change_flag.click(function(){
			if(pre_change_flag.is(':checked')) {
				date.parent().slideDown();
			} else {
				date.parent().slideUp();
			}
		});
		date.datetimepicker();

		// 編集画面表示・非表示
		items.click(function(e){
			$(this).parents('.nc-widget-area:first').children('.nc-widget-area-content:first').slideToggle();
			$.Event(e).preventDefault();
		});
	};
})(jQuery);