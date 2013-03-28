/**
 * ブログ編集 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.BlogEdits = function(id, active_tab) {
		var hash = location.hash;
		if(typeof active_tab == "undedined") {
			if(hash.match(/^#blog-edits-tab-comment/)) {
				active_tab = 1;
			} else if(hash.match(/^#blog-edits-tab-trackback/)) {
				active_tab = 2;
			} else if(hash.match(/^#blog-edits-tab-approval/)) {
				active_tab = 3;
			}
		}

		//var form = $('#Form' + id);
		//var action = form.attr('action');

		$('#blog-edits-tab' + id).tabs({
			active: active_tab,
			show: function(event, ui) {
				location.hash = $(ui.tab).attr('href');
				$(window).scrollTop($(window).scrollTop() - 75);	// タブの上にScroll移動
			}
		});
		$(this).show();

	};
})(jQuery);