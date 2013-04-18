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
		if(active_tab == undefined) {
			active_tab = getActivetab(id);
		}

		$('#blog-edits-tab' + id).tabs({
			active: active_tab,
			show: function(event, ui) {
				if(ui.index != 0) {
					location.hash = $(ui.tab).attr('href');
					$('html,body').animate({ scrollTop: $(window).scrollTop() - 75 }, 'swing');
				}
				active_tab = ui.index;
			}
		});
		$(window).bind("hashchange", function(e){
			// hash値が切り替わったらタブの表示を切り替える
			var active = getActivetab(id);
			if(active !== false) {
				$('#blog-edits-tab' + id).tabs( "option", "active", active);
			}
		});
		$(this).show();

		function getActivetab(id) {
			var hash = location.hash;
			var active = false;
			if(hash == '#' + id) {
				active = 0;
			} else if(hash == '#blog-edits-tab-comment' + id) {
				active = 1;
			} else if(hash == '#blog-edits-tab-trackback' + id) {
				active = 2;
			} else if(hash == '#blog-edits-tab-approval' + id) {
				active = 3;
			}
			return active;
		}

	};
})(jQuery);