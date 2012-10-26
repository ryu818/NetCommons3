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
		// topid,controller_action名称からurl取得
		urlBlock : function(id, controller_action) {
			var block_id = $('#' + id).attr('data-block');
			return $._page_url + $._block_type + '/' + block_id + '/' + controller_action + '/#' + id;
		},
		// javascript動的ロード
	    load : function(src, check, next, timeout) {
			check = new Function('return !!(' + check + ')');
			var script = document.createElement('script');
				script.src = src;
			document.body.appendChild(script);
			this.wait(check, next, timeout);
		},

		// 動的ロードの待機
		wait: function  (check, next, timeout) {
			timeout = (typeof timeout == "undefined") ? 10000 : timeout;
			if (!check()) {
				setTimeout(function() {
					if(timeout != undefined) {
						timeout = timeout - 100;
						if(timeout < 0) return;
					}
					if (!check()) setTimeout(arguments.callee, 100);
					else next();
				}, 100);
	 		} else
	 			next();
	 	},

	 	/**
	 	 * スタイルシートを追加する
	 	 * @param   css_name		CSSファイル名称
	 	 * @param   media			media名(MediaDescタイプ)
	 	 **/
 		loadLink: function (href, media){
 			var nLink = null;
 			for(var i=0; (nLink = document.getElementsByTagName("LINK")[i]); i++) {
 				if(nLink.href == href) {
 					//既に追加済
 					return true;
 				}
 			}
 			return this._loadLink(href, media);
 		},
 		_loadLink: function (href, media){
 			if(typeof document.createStyleSheet != 'undefined') {
 				document.createStyleSheet(href);
 				var oLinks = document.getElementsByTagName('LINK');
 				var nLink = oLinks[oLinks.length-1];
 			} else if(document.styleSheets){
 	  			var nLink=document.createElement('LINK');
 				nLink.rel="stylesheet";
 				nLink.type="text/css";
 				nLink.media= (media ? media : "screen");
 				nLink.href=href;
 				var oHEAD=document.getElementsByTagName('HEAD').item(0);
 				oHEAD.appendChild(nLink);
 			}
 		},

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
		escapeHTML: function(str) {
			return String(str).replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;").replace(/ /g, "&nbsp;");
		},

		unescapeHTML: function(str) {
			return String(str).replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&quot;/g, '"').replace(/&apos;/g, "'").replace(/&#039;/g, "'").replace(/&nbsp;/g, " ").replace(/&amp;/g,'&');
		},
		/* 正規表現のエスケープ */
		quote: function (str){
		    return str.replace(/\W/g, function($0){
		        return '\\' + $0;
		    });
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
			html += '<div class="btn-bottom">'+
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
				modal: true,
				zIndex: ++this.zIndex,
				position: [pos.left+20 - $(window).scrollLeft() ,pos.top+20 - $(window).scrollTop()],
			});
		},
		/* 色取得一般メソッド */
		// RBG値から HSL値を取得
		getHSL : function(r, g, b)
		{
			var h,s,l,v,m;
			var r = r/255;
			var g = g/255;
			var b = b/255;
			v = Math.max(r, g), v = Math.max(v, b);
			m = Math.min(r, g), m = Math.min(m, b);
			l = (m+v)/2;
			if (v == m) var sl_s = 0, sl_l = Math.round(l*255),sl_h=0;
			else
			{
				if (l <= 0.5) s = (v-m)/(v+m);
				else s = (v-m)/(2-v-m);
				if (r == v) h = (g-b)/(v-m);
				if (g == v) h = 2+(b-r)/(v-m);
				if (b == v) h = 4+(r-g)/(v-m);
				h = h*60; if (h<0) h += 360;
				var sl_h = Math.round(h/360*255);
				var sl_s = Math.round(s*255);
				var sl_l = Math.round(l*255);
			}
			return { h : sl_h, s : sl_s , l : sl_l };
		},
		getRBG : function(h, s, l)
		{
			var r, g, b, v, m, se, mid1, mid2;
			h = h/255, s = s/255, l = l/255;
			if (l <= 0.5) v = l*(1+s);
			else v = l+s-l*s;
			if (v <= 0) var sl_r = 0, sl_g = 0, sl_b = 0;
			else
			{
				var m = 2*l-v,h=h*6, se = Math.floor(h);
				var mid1 = m+v*(v-m)/v*(h-se);
				var mid2 = v-v*(v-m)/v*(h-se);
				switch (se)
				{
					case 0 : r = v;    g = mid1; b = m;    break;
					case 1 : r = mid2; g = v;    b = m;    break;
					case 2 : r = m;    g = v;    b = mid1; break;
					case 3 : r = m;    g = mid2; b = v;    break;
					case 4 : r = mid1; g = m;    b = v;    break;
					case 5 : r = v;    g = m;    b = mid2; break;
				}
				var sl_r = Math.round(r*255);
				var sl_g = Math.round(g*255);
				var sl_b = Math.round(b*255);
			}
			return { r : sl_r, g : sl_g , b : sl_b };
		},
		getRGBtoHex : function(color) {
			if(color.r ) return color;
			if(color == "transparent" || color.match("^rgba")) return "transparent";
			if(color.match("^rgb")) {
				color = color.replace("rgb(","");
				color = color.replace(")","");
				color_arr = color.split(",");
				return { r : parseInt(color_arr[0]), g : parseInt(color_arr[1]) , b : parseInt(color_arr[2]) };
			}
			if ( color.indexOf('#') == 0 )
				color = color.substring(1);
			var red   = color.substring(0,2);
			var green = color.substring(2,4);
			var blue  = color.substring(4,6);
			return { r : parseInt(red,16), g : parseInt(green,16) , b : parseInt(blue,16) };
		},
		getHex : function(r, g, b)
		{
			var co = "#";
			if (r < 16) co = co+"0"; co = co+r.toString(16);
			if (g < 16) co = co+"0"; co = co+g.toString(16);
			if (b < 16) co = co+"0"; co = co+b.toString(16);
			return co;
		},
		getColorCode: function(el , p_name) {
			if(p_name == "borderColor" || p_name == "border-color") {
				p_name = "borderTopColor";
			}
			if(p_name == "borderTopColor" || p_name == "borderRightColor" ||
				p_name == "borderBottomColor" || p_name == "borderLeftColor") {
				var width = $(el).css(p_name.replace("Color","")+"Width");
				if(width == "" || width == "0px" || width == "0") {
					return "transparent";
				}
			}
			var rgb = el.style[p_name];	//$(el).css(p_name);
			if(rgb == undefined || rgb == null) {
				return "transparent";
			} else if (rgb.match("^rgb") && rgb != "transparent" && rgb.substr(0, 1) != "#") {
				rgb = rgb.substr(4, rgb.length - 5);
				var rgbArr = rgb.split(",");
				rgb = $.Common.getHex(parseInt(rgbArr[0]),parseInt(rgbArr[1]),parseInt(rgbArr[2]));
			} else if(rgb.substr(0, 1) != "#"){
				//windowtext等
				if(p_name == "backgroundColor") {
					return "transparent";
				}
				return "";
			}
			return rgb;
		},

		colorCheck: function(event) {
			if(((event.ctrlKey && !event.altKey) || event.keyCode == 229 || event.keyCode == 46 || event.keyCode == 8 ||
				(event.keyCode >= 37 && event.keyCode <= 40) || event.keyCode == 9 || event.keyCode == 13 ||
				(event.keyCode >= 96 && event.keyCode <= 105) ||
				(event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 65 && event.keyCode <= 70)))
				return true;
			return false;
		},

		numberCheck: function(event) {
			if(((event.ctrlKey && !event.altKey) || event.keyCode == 229 || event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 13 ||
				(event.keyCode >= 96 && event.keyCode <= 105) ||
				(event.keyCode >= 37 && event.keyCode <= 40) || (!event.shiftKey && event.keyCode >= 48 && event.keyCode <= 57)))
				return true;
			return false;
		},

		numberConvert: function(event) {
			if(event.keyCode == 13 || event.type == "blur") {
				var event_el = $(event.target);
				var num_value = event_el.val();
				var en_num = "0123456789.,-+";
				var em_num = "０１２３４５６７８９．，－＋";
				var str = "";
				for (var i=0; i< num_value.length; i++) {
					var c = num_value.charAt(i);
					var n = em_num.indexOf(c,0);
					var m = en_num.indexOf(c,0);
					if (n >= 0) {c = en_num.charAt(n);str += c;
					} else if (m >= 0) str += c;
				}
				if(num_value != str) event_el.val(str);
				return true;
			}
			return false;
		}
	};
	/* グローバル関数 */
	__ = function(name) {
		if(name == undefined) {
			return $._lang['common'];
		}
		return ($._lang['common'][name]) ? $._lang['common'][name] : name;
	};
	__d = function(key, name) {
		if(typeof key != 'string') {
			var buf_key = null;
			$.each(key, function() {
				if(buf_key == null) {
					buf_key = $._lang[this];
				} else {
					buf_key = buf_key[this];
				}
			});
			if(name == undefined) {
				return buf_key;
			}
			return (buf_key[name]) ? buf_key[name] : name;
		}
		if(name == undefined) {
			return $._lang[key];
		}
		return ($._lang[key][name]) ? $._lang[key][name] : name;
	};
})(jQuery);