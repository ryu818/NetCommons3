/**
 * 共通 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       webroot.js.main
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.Common ={
		zIndex : 2000,
		blockZIndex : 1000,
		alert: function(str) {
			str = this._massage(str);
			if(str == "") return;
			alert(str);
		},
		confirm: function(str) {
			str = this._massage(str);
			if(str == "") return;
			return confirm(str);
		},
		_massage: function(str) {
			if(typeof str != 'string') return "";
			var re_html = new RegExp("^[\s\r\n]*<!DOCTYPE html", 'i');
			if(str.match(re_html)) {
				document.write(str);
				return '';
			} else {
				str = str.replace(/&lt;/ig,"<");
				str = str.replace(/&gt;/ig,">");
				str = str.replace(/\\n/ig,"\n");
				str = str.replace(/(<br(?:.|\s|\/)*?>)/ig,"\n");
				return str;
			}
		},
		showConfirm: function(target, title, confirm, callback, cancel_callback, input_label) {
			var pos = $(target).offset();
			var dialog_el = $('#nc_mes_dialog');
			if(dialog_el.get(0)) {
				dialog_el.remove();
			}

			var html = '<div id="nc_mes_dialog" style="display:none;">'+
					'<div class="nc_confirm_dialog_mes">'+confirm+'</div>';
			if(input_label) {
				html += '<label for="nc_confirm_dialog_mes_flag">'+
							'<input id="nc_confirm_dialog_mes_flag" type="checkbox" name="confirm_dialog_mes_flag" value="1" />&nbsp;'+
							input_label+
							'</label>';
			}
			html += '<div class="btn-bottom" style="left:55px; bottom:10px;position:absolute;">'+
						'<input type="button" class="common_btn" name="ok" value="'+__('Ok')+'">&nbsp;&nbsp;&nbsp;'+
						'<input type="button" class="common_btn" name="cancel" value="'+__('Cancel')+'">'+
					'</div>'+
				'</div>';
			$(html).appendTo($(document.body));
			dialog_el = $('#nc_mes_dialog');

			$('input[name="ok"]', dialog_el).click(function(event){
				if(callback) {
					callback();
				}
				dialog_el.dialog('close');
			});
			$('input[name="cancel"]', dialog_el).click(function(event){
				if(cancel_callback) {
					cancel_callback();
				}
				dialog_el.dialog('close');
			});

			dialog_el.dialog({
				title: title,
				resizable: false,
				width: 320,
				height:(input_label) ? 165 : 140,
				modal: true,
				zIndex: ++this.zIndex,
				position: [pos.left+20 - $(window).scrollLeft() ,pos.top+20 - $(window).scrollTop()],
			});
		}
	};
	/* グローバル関数 */
	__ = function(name) {
		return ($._lang['common'][name]) ? $._lang['common'][name] : name;
	};
	__d = function(key, name) {
		return ($._lang[key][name]) ? $._lang[key][name] : name;
	};
})(jQuery);