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
		// data-pjax属性の値をtargetとしてhrefタグのURLを用いてAjaxでデータを取得し、targetを置換する。
		//
		// data-ajax属性の値をtargetとしてhrefタグのURLを用いてAjaxでデータを取得する。
		// data-ajax-replace属性ならば、targetと入れ替える。
		//
		// data-ajax-url:URL default：href属性から取得
		// data-ajax-type: post or get
		// data-ajax-effect: 遷移時effect default：fold
		// data-ajax-confirm: メッセージをValueに設定すると確認ダイアログ表示
		// data-ajax-dialog-id: ダイアログとして表示する場合、指定dialogTopのid属性
		// data-ajax-dialog-class: ダイアログとして表示した場合のダイアログクラス名
		// data-ajax-dialog-options: ダイアログとして表示した場合のダイアログオプション jquery dialogのoptionsをhash配列を文字列に変換したもの
		//		"position" : "mouse"指定があればマウス位置にダイアログ表示
		// カスタムイベント ajax:
		// ajax:beforeSend - Ajaxリクエスト前に呼ばれる。falseを返せば処理を中断する。
		// ajax:beforeSendのみ、return値(string or array)でURL及びdataの内容を上書き可。
		// ajax:success - Ajaxリクエスト直後に呼ばれる。falseを返せば処理を中断する。
		//        @param Event ajax:success event object
		//        @param res ajax response
		//        @param Event parent event object
		// 例：$('form:first', this).on('ajax:beforeSuccess', function(e, res) {
		//	       e.preventDefault();
		//     });
		//
		ajax : function(e, a, confirm) {
			var data_pjax, top, url, data, input_type, type, ret;
			var target_pjax = a.attr("data-pjax");
			var confirm = (typeof confirm == "undefined") ? a.attr("data-ajax-confirm") : confirm;
			if(a.hasClass('disable-lbl')) {
				e.preventDefault();
				return false;
			}
			if(confirm){
				var ok = __('Ok') ,cancel = __('Cancel');
				var default_params = {
					resizable: false,
		            modal: true
				}, _buttons = {}, params = new Object();
				_buttons[ok] = function(){
					$( this ).remove();
					$.Common.ajax(e, a, false);
				};
				_buttons[cancel] = function(){
					$( this ).remove();
				};
				var dialog_params = $.extend({buttons: _buttons}, default_params);
				$('<div></div>').html(confirm).dialog(dialog_params);
				e.preventDefault();
				return;
			}

			if(target_pjax) {
				// pjax
				// TODO:pjaxにはeffect等が対応していない。
				top = $(target_pjax);
				if(top.get(0)) {
					$.pjax.submit(e, top);
				}
			} else {
				if(a.get(0).tagName.toLowerCase() == 'form') {
					input_type = 'POST';
					url = a.attr('action');
					data = a.serializeArray();
				} else {
					input_type = 'GET';
					url = a.attr('href');
					data = {};
				}

				ret = $.Common.fire('ajax:beforeSend', [url, data], a, e);
				if (!ret) {
					e.preventDefault();
					return false
				}
				if(ret !== true) {
					if(typeof ret == "string") {
						url = ret;
					} else if(ret['url']) {
						url = ret['url'];
						data = ret['data'] ? ret['data'] : data;
					} else {
						url = ret[0];
						data = ret[0];
					}
				}
				type = a.attr("data-ajax-type");
				url = a.attr("data-ajax-url") ? a.attr("data-ajax-url") : url;
				$.ajax({
					type: (type == 'GET' || type == 'get' || type == 'POST' || type == 'post') ? type : input_type,
					url: url,
					data: data,
					success: function(res, status, xhr){
						if (!$.Common.fire('ajax:success', [res, e, status, xhr], a,e)) {
							return false
						}
						$.Common.ajaxSuccess(a, res, null, e);
					}
	 			});
			}
			e.preventDefault();
		},

		ajaxSuccess : function(a, res, params, e) {
			var target,replace_target,effect;
			var buf_a, res_target, res_other_target, buf_res_target, effect_cnt = 0, effect_index = -1;
			var dialog_id, dialog_title, dialog_options, dialog;
			a = $(a);
			if (params) {
				var target = params['data-ajax'];
				replace_target = params['data-ajax-replace'];
				effect = params['data-ajax-effect'];
				dialog_id = params['data-ajax-dialog-id'];
				dialog_class = params['data-ajax-dialog-class'];
				dialog_options = params['data-ajax-dialog-options'];
			} else if(a.get(0)) {
				target = a.attr("data-ajax");
				replace_target = a.attr("data-ajax-replace");
				effect = a.attr("data-ajax-effect") ? a.attr("data-ajax-effect") : null;
				dialog_id = a.attr("data-ajax-dialog-id");
				dialog_class = a.attr("data-ajax-dialog-class");
				dialog_options = a.attr("data-ajax-dialog-options");
			} else {
				return false;
			}

			if(typeof dialog_options == 'string') {
				dialog_options = $.parseJSON(dialog_options);
				if(dialog_options['position'] && dialog_options['position'] == 'mouse') {
					dialog_options['position'] = [e.pageX - $(window).scrollLeft(), e.pageY - $(window).scrollTop()];
				}

			}

			if(dialog_options) {
				if(effect) {
					dialog_options['show'] = effect;
					dialog_options['hide'] = effect;
				}
				if(typeof dialog_options['resizable'] == 'undefined') {
					dialog_options['resizable'] = false;
				}
				dialog_options = $.extend({}, {zIndex: ++$.Common.zIndex}, dialog_options);
			}

			if (dialog_id) {
				dialog = $('#' + dialog_id);
				if(!dialog.get(0)) {
					target = (target) ? target : $(document.body);
					dialog = $('<div id='+ dialog_id +' style="display:none;"></div>').appendTo(target);
				}
				dialog.html(res);
				dialog.dialog(dialog_options);
				if(dialog_class) {
					dialog.parent().addClass(dialog_class);
				}
			} else if(target) {
				res_target = $(target);
				if(effect && $(target).css('display') != 'none') {
					$(target).hide(effect, function(){
						$(target).html(res);
						$(target).show(effect);
					});
				} else {
					$(target).html(res);
				}
			} else if(replace_target) {
				res_target = $();
				res_other_target = $();
				buf_res_target = $(res);
				buf_res_target.each(function(){
					if (this.nodeType == 1 && $(this).is(replace_target)) {
						res_target.push(this);
					} else {
						res_other_target.push(this);
					}
				});
				if(!res_target.get(0)) {
					res_target = res_other_target;
					res_other_target = $();
				}
				if(effect && res_target.get(0)) {
					$(replace_target).hide(effect, function(){
						$(res_target).css('display', 'none');
						$(replace_target).replaceWith(res_target);
						$(replace_target).after(res_other_target);
						$(res_target).show(effect);
					});
				} else {
					$(replace_target).replaceWith(res_target);
					$(replace_target).after(res_other_target);
				}
			}
			return res_target;
		},

		fire : function(type, args, content, parent_event) {
			var event = $.Event(type);	// , { relatedTarget: target }
			// mouse系変数マージ
			if(parent_event && parent_event.pageX) {
				event.pageX = parent_event.pageX;
				event.pageY = parent_event.pageY;
				event.clientX = parent_event.clientX;
				event.clientY = parent_event.clientY;
				event.screenX = parent_event.screenX;
				event.screenY = parent_event.screenY;
				event.offsetX = parent_event.offsetX;
				event.offsetY = parent_event.offsetY;
			}

			content.trigger(event, args);
			//return !event.isDefaultPrevented();
			if(typeof event.result == "undefined") {
				return !event.isDefaultPrevented();
			}
			return event.result;
		},

		// ブロックリロード処理
		reloadBlock : function(e, id, data, pjax) {
			pjax = (typeof pjax == 'undefined') ? false : pjax
			var block = (typeof id == 'string') ? $('#' + id) : $(id),re, params = new Object();
			var url = block.attr('data-url');
			if($._block_type == 'blocks') {
				re = new RegExp("/active-blocks/", 'i');
				url = url.replace(re, "/" + $._block_type + "/");
			} else {
				re = new RegExp("/blocks/", 'i');
				url = url.replace(re, "/" + $._block_type + "/");
			}
			if(pjax && $.support.pjax) {
				params['url'] = url;
				if(data) {
					params['data'] = data;
				}
				$.pjax.click(e, block, params);
			} else {
				$.ajax({
					type: 'GET',
					url: url,
					data: data,
					success: function(res, status, xhr){
						block.replaceWith(res);
						//$.Common.ajaxSuccess(a, res);
					}
	 			});
			}
		},

		// block_id,controller_action名称からurl取得
		urlBlock : function(block_id, controller_action) {
			if(!block_id) {
				return $._page_url + $._block_type + '/' + controller_action;
			}
			var id = '_' + block_id;
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

 		flash: function(str, pause) {
 			var mes = $('#flashMessage');
 			if(mes.get(0)) {
 				mes.remove();
 			}
 			mes = $(str).prependTo($("body"));
 			pause = (typeof pause == "undefined") ? 2000 : pause;
 			mes.delay(pause).animate({top: -1 * mes.outerHeight()}, 500, function() {
				mes.remove();
			});

 		},

 		// エラーダイアログ表示
 		showErrorDialog : function(error_str, params, target) {
			var ok = __('Ok');
			var body = '<div class="error-message">' + error_str + '</div>', _buttons = {}, pos;

			_buttons[ok] = function(){
				$( this ).remove();
			};
			var default_params = {
				resizable: false,
	            modal: true,
		        //position:,
		        buttons: _buttons
			}
			if(target) {
				pos = $(target).offset();
				default_params['position'] = [pos.left + 5 - $(window).scrollLeft() ,pos.top + 5 - $(window).scrollTop()];
			}
			params = $.extend({}, default_params);
			$('<div></div>').html(body).dialog(params);
		},

		// ・$this->Form-inputでselector指定した場合のアラート表示をform中のエレメントが変更されたら削除する
		// ・エラーがおこった最初のエレメントにフォーカスを移動する。
		// TODO:WYSIWYGには対応していない。
		closeAlert : function(input, alert) {
			var t = this, i =0,text;
			$.each( $(input), function() {
				var child = $(this), form, focus;
				if (child.is(':button,:submit,:reset,:image') || child.css('display') == 'none') {
					return;
				}
				if(i == 0) {
					form = child.parents('form:first');
					if(form.get(0)) {
						if (child.is(':hidden')) {
							setTimeout(function(){
								focus = $(':focus', form);
								if(!focus.get(0)) {
									if (child.is(':text,:password,textarea')) {
										child.select();
									} else {
										child.focus();
									}
								}
							}, 500);
						} else {
							focus = $(':focus', form);
							if(!focus.get(0)) {
								if (child.is(':text,:password,textarea')) {
									child.select();
								} else {
									child.focus();
								}
							}
						}
					}
				}
				child.addClass('error-input-message');
				if (child.is(':text,:password,textarea')) {
					child.bind("keydown focus", function(e){
						text = child.val();
					});
					child.bind("keyup change", function(e){
						var child = $(this);
						if(child.val() != text) {
							alert.remove();
							child.removeClass('error-input-message');
						}
					});
				} else if(child.is(':input')) {
					child.click(function(e){
						alert.remove();
					});
				}
				i++;
			});

		},
		alert: function(str) {
			str = this._message(str);
			if(str == "") return;
			alert(str);
		},
		confirm: function(str) {
			str = this._message(str);
			if(str == "") return;
			return confirm(str);
		},
		_message: function(str) {
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
		// jquery selector escape処理
		escapeSelector: function(str) {
			return str.replace(/([#;&,\.\+\*\~':"\!\^$\[\]\(\)=>\|])/g, "\\$1");
		},
		/* 正規表現のエスケープ */
		quote: function (str){
		    return str.replace(/\W/g, function($0){
		        return '\\' + $0;
		    });
		},
		within: function($element, x, y) {
			var offset = $element.offset();

			return (y >= offset.top &&
					y <  offset.top + $element.outerHeight() &&
					x >= offset.left &&
					x <  offset.left + $element.outerWidth());
		},
		/* ダイアログ表示用 */
		showDialog: function(id, ajax_options, dialog_options) {
			var dialog = $('#' + id);
			var ajax_defaults = {
				success : function(res) {
					var dialog_el = $('<div id='+ id +'></div>').appendTo($(document.body));
					dialog_el.html(res);
					dialog_el.dialog(dialog_options);
				}
			}, dialog_defaults = {
				zIndex: ++$.Common.zIndex
			};
			ajax_options = $.extend({}, ajax_defaults, ajax_options),
				dialog_options = $.extend({}, dialog_defaults, dialog_options);

			if(dialog.get(0)) {
				dialog.dialog('open');
				return;
			}
			$.ajax(ajax_options);
		},
		/* 権限[主坦　モデレータ　一般]スライダー */
		sliderAuthority: function(id, disable) {console.log(id);
			var _hierarchy = function(authority_id) {
				var  h = 0;
				switch (authority_id)
				{
					case 2 : h = '301'; break;
					case 3 : h = '201'; break;
					case 4 : h = '101'; break;
				}
				return h;
			}
			var _authority_id = function(h) {
				var authority_id = 2;
				switch (h)
				{
					case '301' : authority_id = 2; break;
					case '201' : authority_id = 3; break;
					case '101' : authority_id = 4; break;
				}
				console.log(authority_id);
				return authority_id;
			}
			var input = $('#' + id);
			if(!input.get(0)) return false;
			var slider = input.next();

			if(!input.get(0) || input.get(0).tagName.toLowerCase() != 'input') {
				return false;
			}
			console.log($(input).val());
			slider.slider({
				'min'    : 2,
				'max'    : 4,
				'value'  : _authority_id($(input).val()),
				'animate': 'fast',
				'range'  : 'min',
				'change': function( event, ui ) {
					var authority_id = $(this).slider( "option", "value" );
					$(input).val(_hierarchy(authority_id));
				}
			});
			if(disable) {
				slider.slider( "disable" );
			}
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
			var rgb = $(el).css(p_name);
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
		},
		// %sのみ変換
		sprintf: function() {
			var str = arguments[0];
			if(str == undefined || str == null) {
				return str;
			}
			for (i = 1; i < arguments.length; i++) {
				str = str.replace(/%s/, arguments[i]);
			}
			return str;
		}
	};
	/* グローバル関数 */
	__ = function(name) {
		var r = [], ret;
		if(name == undefined) {
			return $._lang['common'];
		}
		ret = ($._lang['common'][name]) ? $._lang['common'][name] : name;
		if(arguments.length >= 2) {
			r.push.apply(r, arguments);
			//r.shift();
			r[0] = ret;
			ret = $.Common.sprintf.apply(this, r);
		}
		return ret;
	};
	__d = function(key, name) {
		var r = [], ret;
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
			ret = (buf_key[name]) ? buf_key[name] : name;
		}
		if(!ret) {
			if(name == undefined) {
				return $._lang[key];
			}
			ret = ($._lang[key][name]) ? $._lang[key][name] : name;
		}
		if(arguments.length > 2) {
			r.push.apply(r, arguments);
			r.shift();
			r[0] = ret;
			ret = $.Common.sprintf.apply(this, r);
		}
		return ret;
	};
})(jQuery);