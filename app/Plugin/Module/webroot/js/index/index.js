/**
 * モジュール管理 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.Module = function(id, active_tab) {
		$.Module.id = id;
		$(this).tabs({
			active: active_tab
		});

		$('#module-list-tab').tabs({
			// active: active_tab
		});

		var params = {
			width: '580',
			height: 'auto',
			showToggleBtn: false,
			resizable: false,
			singleSelect: true
		};
		$("#module-list-not-install").flexigrid(params);
		$("#module-list-system").flexigrid(params);
		$("#module-list-install").flexigrid(params);
	};

	$.Module = {
		id : 0,
		clickSubmit: function(e, dir_name, type, confirm, confirm_sub) {
			var form_str = '#form' + $.Module.id;
			var type_input = $(form_str + ' [name=type]');
			var dir_name_input = $(form_str + ' [name=dir_name]');

			e.preventDefault();

			if(confirm) {
				var ok = __('Ok') ,cancel = __('Cancel');
				var default_params = {
					resizable: false,
		            modal: true,
		            position: [e.pageX - $(window).scrollLeft(), e.pageY - $(window).scrollTop()]
				}, _buttons = {}, params = new Object();
				_buttons[ok] = function(){
					$( this ).remove();
					$.Module.clickSubmit(e, dir_name, type, confirm_sub);
				};
				_buttons[cancel] = function(){
					$( this ).remove();
				};
				var dialog_params = $.extend({buttons: _buttons}, default_params);
				$('<div></div>').html(confirm).dialog(dialog_params);
				return;
			}
			dir_name_input.val(dir_name);
			type_input.val(type);
			$(form_str).submit();
		}
	}
})(jQuery);