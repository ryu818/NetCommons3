/**
 * 個人情報管理 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.Policy = function(id) {
		var tabs = $(this);
		tabs.tabs({
			beforeActivate: function( event, ui ) {
				var a = ui.newTab.children('a:first');
				if(ui.newPanel.html() != '') {
					var href = a.attr('href');
					// 既に表示中 - Ajaxで再取得しない。
					a.attr('href', '#' + ui.newPanel.attr('id')).attr('data-url', href);

				}
			}
		});
	};
	$.Policy = {

/**
 * 確認ダイアログメッセージ変更
 * @param   string id
 * @param   string submit|reset
 * @return  void
 * @since   v 3.0.0.0
 */
		setConfirm: function(id, type) {
			var form = $('#Form' + id);
			var submit = form.attr('data-confirm-submit');
			var typeHidden = $('#policy-type-' + id);
			if(!submit) {
				submit = form.attr('data-ajax-confirm');
			}
			var reset = form.attr('data-confirm-reset');
			if(type == 'submit') {
				form.attr('data-ajax-confirm', submit);
			} else {
				form.attr('data-confirm-submit', submit);
				form.attr('data-ajax-confirm', reset);
			}
			typeHidden.val(type);
		}
	};
})(jQuery);