/**
 * システム管理 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.System = function(id) {
		var tabs = $(this);
		var general = $('#system-init-tab-general');
		setTimeout(function(){	 // ダイアログ位置が上部になるため初期処理を遅延
			tabs.tabs({
				beforeActivate: function( event, ui ) {
					var a = ui.newTab.children('a:first');
					if(ui.newPanel.html() != '') {
						var href = a.attr('href');
						// 既に表示中 - Ajaxで再取得しない。
						a.attr('href', '#' + ui.newPanel.attr('id')).attr('data-url', href);

					}
					a.attr('data-tab-id', ui.newPanel.attr('id'));
					var w = ui.newTab.attr('data-width');
					var topW = $('#system-init-tab').attr('data-width');
					if(w) {
						$('#' + id).dialog('option', 'width', w);
					} else if(topW) {
						$('#' + id).dialog('option', 'width', topW);
					} else {
						$('#' + id).dialog('option', 'width', 'auto');
					}
				},
				activate: function( event, ui ) {
					var text = $('form > input:text:first', ui.newPanel);
					$.System.focus(ui.newPanel);
				}
			});
			general.prev().show();
			$.System.focus(general);
		}, 100);
	};
	$.System = {
/**
 * 先頭のtextにフォーカス
 * @param   element panel
 * @return  void
 */
		focus: function(panel) {
			var text = $('form input:text:first', panel);
			if(text.get(0)) {
				text.focus();
			}
		},
/**
 * コミュニティー一覧取得
 * @param   integer id
 * @param   string  url
 * @param   element el
 * @return  void
 */
		displayCommunityList: function(id, el) {
			if($(el).val() != '-3') {
				$('#' + id).hide();
			} else {
				$('#' + id).show();
			}
		}
	}
})(jQuery);