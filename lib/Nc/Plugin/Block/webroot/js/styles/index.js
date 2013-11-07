/**
 * ブロックスタイル js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.BlockStyles = function(block_id, active_tab, act_category) {
		$('form#BlockIndexForm'+block_id+',form#BlockIndexFormTheme'+block_id).on('ajax:success', function(e, res) {
			var re_html = new RegExp("^<script>", 'i');
			if($.trim(res).match(re_html)) {
				$.Common.reloadBlock(null, '_' + block_id, {'_nc_include_css' : 1});
				if($('[name=is_apply]', $(this)).val() == 0) {
					// 決定
					$('#nc-block-styles-dialog'+block_id).after(res).remove();
					return false;
				}

			}
		});
		// 適用ボタン等でタブ等が一瞬くずれて表示されるためjsからshow
		$('#nc-block-styles' + block_id).show();
		$(this).tabs({
			active: active_tab,
			show: function(event, ui) {
				var accordion_el = $('#nc-block-styles-theme' + block_id);
				if(ui.index == 1 && !accordion_el.hasClass('ui-accordion')) {
					accordion_el.show().accordion({
						//'fillSpace': true,
						//'event': 'mouseover'
						'active' : act_category
					});
				}
			}
		});
		$('#nc-block-styles-display-to-date-' + block_id).datetimepicker();
		$('#nc-block-styles-display-from-date-' + block_id).datetimepicker();

	};

	$.BlockStyles = {
		/* 最小の広さ、高さ変更*/
		chgSize: function(select, input) {
			if($(select).val() > 0) {
				$(input).show();
			} else {
				$(input).hide();
			}
		},
		chgDisplayFlag: function(select, input) {
			if($(select).val() == 0) {
				$(input).removeAttr("disabled");
			} else {
				$(input).attr('disabled','disabled');
			}
		},

		clickSubmit: function(submit, input) {
			if($(submit).attr('name') == 'apply') {
				$(input).val(1);
			} else {
				$(input).val(0);
			}
		},
		/* テーマクリック */
		clickTheme: function(e, form, theme_name, input) {
			if($(input).val() == theme_name) {
				return false;
			}
			$(input).val(theme_name);
			$(form).submit();
			$.Event(e).preventDefault();
		}
	}
})(jQuery);