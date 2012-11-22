/**
 * ページスタイル js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$(document).ready(function(){
		var style_values = $('[data-pagestyle-name]', '.nc-pages-setting-content');
		// 画面表示時
		style_values.each(function(){
			$('#'+this.id).val($.Common.getColorCode(this.dataset.pagestyleSelector, this.dataset.pagestyleName));
		});
		// 変更時プレビュー表示
		style_values.change(function(){
			var target = style_values[0].dataset.pagestyleSelector;
			var style = '<style id="page_style_preview" type="text/css">';
			var css = '';
			style_values.each(function(){
				css += this.dataset.pagestyleSelector + '{' + this.dataset.pagestyleName + ':' + this.value + ' !important;}';
			});
			style += css + '</style>';
			if ($('#page_style_preview').size() > 0) {
				$('#page_style_preview').remove();
			}
			$(target).append(style);

			if ($('#page_style_preview_css').size() > 0) {
				$('#page_style_preview_css').remove();
			}
			// CSSをPOSTするためhiddenにセット
			$('<input id="page_style_preview_css" type="hidden" name="data[css]" value="' + css + '" />').appendTo($('[name=pages_style_form]', '.nc-pages-setting-content'));
			// 非表示テキストエリアの情報を更新
			$('#page_setting_textarea').val(css);
		});
	});})(jQuery);