/*
 * NC Smiley 0.0.0.1
 * @param e event object
 * @param options hash
 * 					type		: string 各typeに特化した処理を使用する場合に指定する。(default 'dialog')
 * 									wysiwyg	: wysiwygに特化した処理をする場合に指定する。callbackを使用したい場合にも使用する。
 * 									dialog		: 通常に使いたい場合はこれを指定する。デフォルト
 * 					blank		: hash ブランクとして使用するpathとtitleを設定する。(default {'path'  : 'titleicon/blank.gif','title' : ''})
 * 					css			: string ダイアログで読み込むCSSファイルのパス(default 'css/plugins/smiley.css')
 * 					callback	: function 呼び出し元で設定したFunctionを使用
 *
 */
;(function($) {
	$.fn.nc_smiley = function(options) {
		var options = $.extend({
			'type'		: 'dialog',
			'blank'		: {'titleicon/blank.gif' : ''},
			'css'		: 'css/plugins/smiley.css',
			'callback'	: null

		}, options);

		var self = this, sm = __d('nc_wysiwyg_smiley'), ul = $('<ul class="nc-wysiwyg-smiley"></ul>'), dialog = null;

		init(options);

		for (k in sm) {
			$('<li></li>').append(
				$('<a href="javascript:;" ><img src="' + $._base_url +'img/plugins/nc_wysiwyg/'+ k +'" title="'+ sm[k] +'" alt="'+ sm[k] +'" /></a>')
				.click(function(e) {
					var el = e.target, html = el.nodeName.toLowerCase() == 'a' ? el.innerHTML : el.parentNode.innerHTML;
					if(options.callback)
						options.callback.call(self, html);
					else {
						$(self).find('img').replaceWith(html);
						if(dialog)
							dialog.hide();
					}
					e.preventDefault();
					return false;
		            })
			).appendTo(ul);
		}
		return;

		function init(options) {
			if(options.css)
				$.Common.loadLink(options.css);

			if (options.blank)
				sm = $.extend(options.blank, sm);

			if (options.type == 'dialog') {
				dialog = self.nc_toggledialog({el : ul});
			} else {
				self.append(ul);
			}
		}
	}
})(jQuery);