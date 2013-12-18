/*
 * NC ColorPicker 0.0.0.1 (farbtastic.jsの親クラス)
 * @param options hash
 *                  colorcode       : string   Color Code (default #000000)
 *                  callback        : function 決定時のcallback関数(default null)
 *                  cancel_callback : function キャンセルボタン押下時のcallback関数(default null)
 *					html            : string   テンプレート文字列(html)
 *					js              : string   ColorPickerで必要なjavascript(farbtastic.js)のパス
 *                  jsname          : string   javascript valiable name js　jsファイルが読み込めたかどうかを判定する変数
 *					css             : string   ColorPickerで必要なcss(farbtastic.css)のパス
 */
;(function($) {
	$.fn.nc_colorpicker = function(options) {
		var options = $.extend({
				colorcode      : "#000000",
				callback       : null,
				cancel_callback: null,
        		html           :
	            	'<input type="text" id="colorpicker-color" name="color" value="COLOR_CODE" />' +
						'<div id="colorpicker"></div>' +
						'<div style="width: 218px;" class="align-center"><input class="nc-common-btn" id="nc-colorpicker-ok" type="button" value="' + __d(['nc_wysiwyg', 'dialog'], 'ok') + '" />' +
					'&nbsp;<input id="nc-colorpicker-cancel" class="nc-common-btn" type="button" value="' + __d(['nc_wysiwyg', 'dialog'], 'cancel') + '" /></div>',
				js             : $._base_url+'js/plugins/farbtastic.js',
				jsname         : '$.farbtastic',
        		css            : $._base_url+'css/plugins/farbtastic.css'
	        }, options);

		var self = this;

		$.Common.loadLink(options.css);
		$.Common.load(options.js, options.jsname, function() {
			$(self).html(options.html.replace(/COLOR_CODE/, options.colorcode));

			$("#colorpicker").farbtastic("#colorpicker-color");
			// イベント
			$("#nc-colorpicker-ok").click(function(e){
				if(options.callback)
					if(!options.callback.apply(self, [$("#colorpicker-color").val()]))
						return false;
				e.preventDefault();
		        return false;
			});
			$("#nc-colorpicker-cancel").click(function(e){
				if(options.cancel_callback)
					if(!options.cancel_callback.apply(self, [$("#colorpicker-color").val()]))
						return false;
				e.preventDefault();
		        return false;
			});
		});
	}
})(jQuery);