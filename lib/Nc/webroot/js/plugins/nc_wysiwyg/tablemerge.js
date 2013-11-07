/*
 * NC TableMerge 0.0.0.1
 * @param options    hash
 *                      td              : td element
 * 	          　　　  	callback        : function 決定時のcallback関数(default null)
 * 	            	　　cancel_callback : function キャンセル時のcallback関数(default null)
 */
 ;(function($) {
	$.fn.nc_tablemerge = function(options) {
		var options = $.extend({
			td              : null,
			callback        : null,
	        cancel_callback : null
		}, options);
		var self = this;

		init();

		// focus：2度目の表示がfocusされないため、timerとする
		setTimeout(function() { $("#nc-wysiwyg-tablemerge-col").focus(); }, 100);

		return;

		function init() {
			var merge, buttons;
			self.append('<div class="nc-wysiwyg-tablemerge-title">'+ __d('nc_wysiwyg_tablemerge', 'cell') +'&nbsp;'+ __d('nc_wysiwyg_tablemerge', 'separator') +'&nbsp;'+
				(options.td.parentNode.rowIndex + 1) + __d('nc_wysiwyg_tablemerge', 'cell_sep') + (options.td.cellIndex + 1) + '</div>');
			self.append('<div class="nc-wysiwyg-tablemerge-title align-center">' + __d('nc_wysiwyg_tablemerge', 'merge') + '</div>');
			merge = $('<ul class="nc-wysiwyg-tablemerge"></ul>').appendTo( self );
			merge.append('<li><dl><dt>'+ __d('nc_wysiwyg_tablemerge', 'col') +'</dt><dd>'+ __d('nc_wysiwyg_tablemerge', 'separator') +'<input id="nc-wysiwyg-tablemerge-col" class="align-right" type="text" name="col" value="1" /></dd></dl></li>');
			merge.append('<li><dl><dt>'+ __d('nc_wysiwyg_tablemerge', 'row') +'</dt><dd>'+ __d('nc_wysiwyg_tablemerge', 'separator') +'<input id="nc-wysiwyg-tablemerge-row" class="align-right" type="text" name="row" value="1" /></dd></dl></li>');

			//ok cancel button
			buttons = $('<div class="nc-wysiwyg-tablemerge-btn"></div>').appendTo( self );
			buttons.append($('<input name="ok" type="button" class="common-btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'ok')+'" />')
				.click(function(e){
					var col = parseInt($("#nc-wysiwyg-tablemerge-col").val());
					var row = parseInt($("#nc-wysiwyg-tablemerge-row").val());
					col = (col > 0) ? col : 0;
					row = (row > 0) ? row : 0;
					if(options.callback)
						if(!options.callback.call(self, col, row))
							return false;
					e.preventDefault();
			        return false;
				}));

			buttons.append($('<input name="cancel" type="button" class="common-btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'cancel')+'" />')
				.click(function(e){
					if(options.cancel_callback)
						if(!options.cancel_callback.apply(self))
							return false;
					e.preventDefault();
			        return false;
				}));
		}
	}
})(jQuery);