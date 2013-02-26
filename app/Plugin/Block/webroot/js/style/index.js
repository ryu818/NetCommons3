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
	$.fn.BlockStyle = function(active_tab, block_id) {
		$('form#PageIndexForm'+block_id).on('ajax:success', function(e, res) {
			var re_html = new RegExp("^<script>", 'i');
			if($.trim(res).match(re_html)) {
				$.Common.reloadBlock(null, '_' + block_id);
				if($('[name=is_apply]', $(this)).val() == 0) {
					// 決定
					$('#nc-block-style-dialog'+block_id).after(res).remove();
					return false;
				}

			}
		});

		$(this).tabs({
			active: active_tab
		});
		$('#nc-block-style-display-to-date-' + block_id).datetimepicker();
		$('#nc-block-style-display-from-date-' + block_id).datetimepicker();
	};

	$.BlockStyle = {
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
		}
	}
})(jQuery);