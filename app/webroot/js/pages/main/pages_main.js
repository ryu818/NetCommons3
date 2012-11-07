/**
 * ページ - メタ情報、テーマ、レウアウト、スタイル設定 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       webroot.js.main
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$(document).ready(function(){
		var page_setting = $('#page_setting');
		var url = page_setting.attr('href');		page_setting.click(function(e){$.PagesMain.showPageSetting(e, url);return false;});	});

	$.PagesMain ={
		showPageSetting: function(e, url) {
			e.preventDefault();
			$.Common.showDialog('page_setting_dialog', {'url' : url}, {'title' : __d('pages', 'Page setting')});		}
	}
})(jQuery);