/*
 * NC toggledialog 0.0.0.1
 * @author      Ryuji Masukawa
 *
 * ポップアップダイアログ表示・非表示
 * (@param self          : object)	: $()として呼ぶ場合、使用しない
 * @param options
 *          id        : dialog id名称      id default this.dialog_id (nc_toggledialog)
 *          el        : string             ダイアログの中身(el or content)
 *          content   : object element     ダイアログの中身(el or content)
 *          css       : array css src
 *          js        : array javascript src
 *          jsname    : array javascript valiable name js　jsの指定と対応して指定すること（jsを指定しているならば、jsnameは必須）
 *          pos_base  : 位置指定の基準element default　　document.body
 *          style     : hash トップエレメントのスタイルをhashで指定 {left : 100, top : 100} 等
 *                      width,height : max    pos_baseの広さに設定
 *                      left : left       : pos_baseの内のleft
 *                             right      : pos_baseの内のright
 *                             outleft    : pos_baseの外側のleft
 *                             outright   : pos_baseの外側のright
 *                             center     : pos_baseの中央
 *                      top  : top        : pos_baseの内のtop
 *                             bottom     : pos_baseの内のbottom
 *                　           outtop     : pos_baseの外側のtop
 *							   outbottom  : pos_baseの外側のbottom
 *                             center     : pos_baseの中央
 *          effect    : basic or slide or fade (default slide)
 *          callback  : array functions
 *          show_flag : boolean trueの場合、nc_toggledialogを呼んだ場合、常に表示する(default false)
 */
;(function($) {
	$.nc_toggledialog = function(self, options) {
		return nc_toggleDialog(self, options);
	}

	$.fn.nc_toggledialog = function(options) {
		var ret = [], cnt = 0;
		this.each(function() {
			var self = this;
			ret[cnt] = nc_toggleDialog(self, options);
			cnt++;
		});
		return (ret[1]) ? ret : ret[0];
	};

	function nc_toggleDialog(o, options)
    {
    	if(this instanceof nc_toggleDialog) {
    		return this.init(o, options);
    	} else {
    		return new nc_toggleDialog(o, options);
    	}
        //return this instanceof nc_toggleDialog
        //    ? this.init(o, options)
        //    : new nc_toggleDialog(o, options);
    }

    $.extend(nc_toggleDialog.prototype,
    {
		dialogs    : {},
		object     : null,
    	options    : {},
    	className  : 'nc_toggledialog',

    	init : function(o, options)
        {
        	var t = this;
        	t.dialogs = {};
        	t.object = o;
        	t.options = t.initOpt(options);
        	if(o.nodeType && o.nodeType == 1) {
        		if(t.options.show_flag == true)
        			t.show(o);
        		else
	        		t.toggle(o);
	        }

        	return this;
        },


        initOpt : function(options)
        {
        	var t = this;
        	var css = [], js = [], jsname = [], callback = [];

        	if ( options ) {
	        	if ( options.css ) {
		            css = options.css;
		            delete options.css;
		        }
		        var js = [];
		        if ( options.js ) {
		            js = options.js;
		            delete options.js;
		        }
		        var jsname = [];
		        if ( options.jsname ) {
		            jsname = options.jsname;
		            delete options.jsname;
		        }
	        }

	       	var options = $.extend({
	       		id        : 'nc_toggledialog',
	       		className : '',
	            el        : null,
	            content   : '',
	            css       : [],
				js        : [],
				jsname    : [],
				pos_base  : $(document.body),
				style     : null,
				effect    : 'slide',
				callback  : null,
				show_flag : false
	        }, options);

	        options.css = options.css.concat(css);
	        options.js = options.js.concat(js);
	        options.jsname = options.jsname.concat(jsname);

	        return options;
        },

        // 表示中のダイアログ非表示
	    // self以外を削除する
        removes : function(self)
        {
        	var t = this, rm_dialogs = [];
        	for (var i in t.dialogs) {
        		if(!self || !self[0] || self[0] != t.dialogs[i][0])
        			rm_dialogs.push(i);
        	}
        	// IEでダイアログからダイアログを表示する際に2つ目のダイアログが再表示できなくなるため修正
        	// （2つ目のダイアログが削除する前に1つ目が削除されてしまうため）
        	for (var i = rm_dialogs.length - 1; i >= 0; --i) {
        		t.remove(rm_dialogs[i]);
        	}
        },

        remove : function(id, hide_flag)
        {
        	var t = this, id = id || t.options.id, check, timeout = 5000;
        	var dialog = $("#"+id), options = t.options;

        	if(!dialog || !dialog[0] || !dialog[0].parentNode || typeof dialog[0].parentNode.tagName == "undefined")
        		return false;

			dialog.hide();

        	if(hide_flag) {
        		check = function() {return !!(dialog.css("display") == "none");};
        		$.Common.wait(check, _remove, timeout);
			} else
				_remove();

        	function _remove() {
        		if(!dialog)
	        		return false;
	        	dialog.remove();
	        	delete t.dialogs[id];
        	};
        },

        show : function(event_el, options)
        {
        	var t = this;
        	if(options) t.options = t.initOpt(options);
        	var id = t.options.id;
        	var check, timeout = 5000;
        	var dialog = t.dialogs[id], options = t.options;

			if($("#" + id)[0]) $("#" + id).remove();
	        if(!dialog || !dialog[0] || !dialog[0].parentNode || typeof dialog[0].parentNode.tagName == "undefined")
	        	dialog = t._create(event_el);

			// loadが終わるまで待機
			check = function() {return !!(t.dialogs[id]);}; //new Function('return !!(t.dialogs[options.id])');
			$.Common.wait(check, _show);

		 	function _show() {
		 		switch (options.effect) {
       				case "basic":
       				case "visible":
       					break;
       				case "slide":
       					dialog.hide();
       					dialog.slideDown(300);
       					break;
       				case "fade":
       					dialog.hide();
       					dialog.fadeIn();
       					break;
       			}
       			dialog.css({zIndex : $.Common.zIndex++});
       			dialog.css({visibility : 'visible'});
		 	}
        },

        hide : function(id)
        {
        	var t = this, id = id || t.options.id;
        	var dialog = $("#"+id), options = t.options;
        	if(!dialog || !dialog[0] || !dialog[0].parentNode || typeof dialog[0].parentNode.tagName == "undefined")
        		return false;

        	switch (options.effect) {
   				case "basic":
   				case "visible":
   					dialog.css({display : 'none'});
   					break;
   				case "slide":
   					dialog.slideUp();
   					break;
   				case "fade":
   					dialog.fadeOut();
   					break;
   			}
   			t.remove(id, true);
        },

		toggle : function(event_el, options)
        {
        	var t = this;
        	if(options) t.options = t.initOpt(options);
        	var id = t.options.id;

        	var el = (t.options.id) ? $("#"+t.options.id) : $(event_el).next();

        	if(!el[0] || (id && id != el.attr("id")) || el.css('display') == "none") {
        		t.show(event_el);
        	} else {
        		t.hide(id);
        	}
        },

        _create : function(event_el)
        {
        	var t = this, id = t.options.id;
        	var dialog , options = t.options;
        	dialog = $('<div>' + options.content + '</div>')
       			.attr({"id" : options.id}).css({visibility : 'hidden'});
       		if(options.className != '')
       			dialog.addClass(options.className);
       		dialog.addClass(t.className);
       		if((event_el[0] && event_el[0].tagName.toLowerCase() == "body") ||
       			(event_el.tagName && event_el.tagName.toLowerCase() == "body")){
       			$(event_el).append(dialog);
       		}else {
       			$(event_el).after(dialog);
       		}
       		if (options.css && options.css.length > 0) {
        		for ( var i in options.css ) {
        			$.Common.loadLink(options.css[i]);
        		}
			}
			if(options.el) {
	        	dialog.append(options.el);
        	}
			if(options.js && options.js.length > 0) {
				for ( var i in options.js ) {
					$.Common.load(options.js[i], options.jsname[i], function() {
	        			if (i+1 == options.js.length) {
			            	_finProcess();
						}
	        		});
	        	}
	        	return dialog;
        	}

        	_finProcess();

        	return dialog;

        	function _finProcess() {
        		if(options.callback){
			    	options.callback.apply(t.object);
			    }
            	if (options.style)
            		_setCss(options.style);

            	// 値を保持
            	t.dialogs[id] = dialog;

            	return true;

            	function _setCss(style) {
            		var pos, buf;
            		var pos_base = $(options.pos_base);
            		if(options.pos_base[0].nodeName.toLowerCase() != 'body') {
            			pos = pos_base.position();
            		} else {
            			pos = {left : 0, top: 0};
            		}
            		if(style.width) {
            			switch (style.width) {
            				case "max":
            					style.width = pos_base.width() - parseInt(dialog.css("paddingLeft")) - parseInt(dialog.css("paddingRight"));
            					break;
            			}
            			dialog.css({width: style.width});
            			delete style.width;
            		}
            		if(style.height) {
            			switch (style.height) {
            				case "max":
            					style.height = pos_base.height() - parseInt(dialog.css("paddingTop")) - parseInt(dialog.css("paddingBottom"));
            					break;
            			}
            			dialog.css({height: style.height});
            			delete style.height;
            		}
            		if(style.top) {
            			switch (style.top) {
            				case "top":
            					style.top = pos.top;
            					break;
            				case "bottom":
            					style.top = pos.top + pos_base.outerHeight() - dialog.outerHeight();
            					break;
            				case "outtop":
            					style.top = pos.top - dialog.outerHeight();
            					break;
            				case "outbottom":
            					style.top = pos.top + pos_base.outerHeight();
            					break;
            				case "center":
            					buf = pos.top + pos_base.outerHeight()/2 - dialog.outerHeight()/2;
            					style.top = (buf < 0) ? 0 : buf;
            					break;
            			}
            		}
            		if(style.left) {
            			switch (style.left) {
            				case "left":
            					style.left = pos.left;
            					break;
            				case "right":
            					style.left = pos.left + pos_base.outerWidth() - dialog.outerWidth();
            					break;
            				case "outleft":
            					style.left = pos.left - dialog.outerWidth();
            					break;
            				case "outright":
            					style.left = pos.left + pos_base.outerWidth();
            					break;
            				case "center":
            					buf = pos.left + pos_base.outerWidth()/2 - dialog.outerWidth()/2;
            					style.left = (buf < 0) ? 0 : buf;
            			}
            		}
            		dialog.css(style);
            	};
        	};
        }
    });
})(jQuery);