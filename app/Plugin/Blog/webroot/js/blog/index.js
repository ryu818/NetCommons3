/**
 * ブログ表示方法変更 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.Blog = function(id) {
		// 表示件数
		var chgLimit = function() {
			// 表示件数
			$('select.blog-widget-selectbox:visible', $('#' + id)).chosen({disable_search : true}).change( function(e){
				var limit_url = $(this).val();
				location.href = limit_url;
			} );
		};
		chgLimit();

		// コンテンツdblclick処理
		$('div.blog-entry-content-highlight', $(this)).dblclick(function(event) {
			$($(this).attr('data-edit-id')).click();
		}).hover(function() {
			$(this).stop(false, true).effect("highlight", {}, 2000);
		}, function(){});
	};
})(jQuery);