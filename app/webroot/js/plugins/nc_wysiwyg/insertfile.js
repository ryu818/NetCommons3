/*
 * NC InsertFile 0.0.0.1
 * @param options hash
 *                  url             : string   アップロードするaction url
 *                  callback        : function 画像挿入時のcallback関数(default null)
 *                                          img html
 *                  cancel_callback : function キャンセル時のcallback関数(default null)
 */
;(function($) {
	$.fn.nc_insertfile = function(options) {

		var options = $.extend({
				url             : "",
				callback        : null,
				cancel_callback : null
	        }, options);

		var self = this;

		// 初期値セット - Form作成
		init(options);

		return;

		function init(options) {
			var frm, upload, files;

			frm = $('<form action="' + options.url + '"class="nc_wysiwyg_insertfile" enctype = "multipart/form-data" method="POST"></form>').appendTo( self );
			frm.append('<p>' + __d('nc_wysiwyg_insertfile', 'desc_file') + '</p>');
			upload = $('<input class="nc_wysiwyg_insertfile_inputfile" name="files" type="file" />').appendTo( frm );
			row = $('<ul class="nc_wysiwyg_insertfile_row">' +
						'<li>' +
							'<dl>' +
								'<dt>' + __d('nc_wysiwyg_insertfile', 'title') + '</dt>' +
								'<dd>' +
									'<input type="text" value="" name="alt_title" class="nc_wysiwyg_insertfile_title" />' +
								'</dd>' +
							'</dl>' +
						'</li>' +
					'</ul>').appendTo( frm );


			$('<div class="nc_wysiwyg_insertfile_btn">' +
								'<input name="ok" type="button" class="common_btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'ok')+'" />' +
								'<input name="cancel" type="button" class="common_btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'cancel')+'" />' +
							'</div>').appendTo( frm );
			_addEvent(frm);
			return;

			function _addEvent(frm) {
				$("[name=ok]:first", frm).click(function(e){
					$.Common.sendAttachment(frm,
						{
							id      : self.attr('id'),
							url     : options.url,
							data    : "type=file",
							success : function(ret, obj) {
								var title = ($("[name=alt_title]:first", frm).val() != '') ? $("[name=alt_title]:first", frm).val() : obj[0].name;
								var html = '<a href="' + $._base_url + obj[0].path + '" title="'+ title + '" target="_blank">'+ title + '</a>';
								if(options.callback)
									if(!options.callback.call(self, html))
										return false;
							},
							error : function(ret, obj){
								alert(ret);
							}
						}
					);
					e.preventDefault();
		            return false;
				});

				$("[name=cancel]:first", frm).click(function(e){
					if(options.cancel_callback)
						if(!options.cancel_callback.call(self))
							return false;
					e.preventDefault();
			        return false;
				});
			}
		}
	}
})(jQuery);