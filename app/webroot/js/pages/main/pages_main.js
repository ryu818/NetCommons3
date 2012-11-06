/**
 * ページ - メタ情報、テーマ、スタイル設定 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       webroot.js.main
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$(document).ready(function(){
		// ページスタイル
		var nc_pages_style_link = $('#nc_pages_style_link');
		var url = nc_pages_style_link.attr('href');
		nc_pages_style_link.click(function(e){$.PagesMain.showPageStyleLink(e, url);return false});
	});
	$.PagesMain ={
		showPageStyleLink: function(e, url) {
			var nc_page_style_dialog = $('#nc_page_style_dialog');
			if (nc_page_style_dialog.get(0)) {
				nc_page_style_dialog.dialog('open');
				return;
			}
			$.get(url,
				function(res) {
					var dialog_el = $('<div id=nc_page_style_dialog></div>').appendTo($(document.body));
					dialog_el.html(res);
					$(dialog_el).dialog({
						title: __d('pages', 'Page setting'),
						zIndex: ++$.Common.zIndex,
						modal: true
					});
				}
			);
		}
	}
})(jQuery);