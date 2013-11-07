/*
 * NC TableBoder 0.0.0.1
 *
 *                      wysiwyg         : nc_wysiwyg object
 *                      active_tab      : Active Tab Name (table or row or col or cell)
 *                      table_el        : 選択table element
 *						pos_title       : 位置のタイトル
 *                      sel_els         : 選択エレメント(table or tr or td)															 },
 *                      borderWidthList : array 線の太さの値を配列で設定。default ['1px', '2px', '3px', '4px', '5px']
 * 	            	　　borderStyleList : array 線のスタイルリストボックスの値を配列で設定。default ['solid','double','dashed','dotted','inset','outset']
 * 	                    borderColor     : string 線の色のデフォルト値を設定。default "#666666"
 */
 ;(function($) {
	$.fn.nc_tableborder = function(options) {
		var options = $.extend({
			wysiwyg         : null,
			active_tab      : null,
			table_el        : null,
			pos_title       : '',
			sel_els         : null,
			borderWidthList : ['1px', '2px', '3px', '4px', '5px'],
			borderStyleList : ['solid','double','dashed','dotted','inset','outset'],
			borderColor     : "#000000"
		}, options);

		// trならば、tdにsel_elsを変換
		if(options.sel_els[0].nodeName.toLowerCase() == "tr") {
			var sel_els = [];
			$(options.sel_els).each(function(k, v){
				for (var i = 0; i < v.childNodes.length; i++) {
					if(v.childNodes[i].nodeName.toLowerCase() == "td") {
						sel_els.push(v.childNodes[i]);
					}
				}
			});
			options.sel_els = sel_els;
		}

		var self = this, property = {}, active_tab = options.active_tab;
		var listWidth, listStyle;
		var c = $.Common.getColorCode(options.sel_els[0],"borderTopColor");
		property = {
			width			: ($(options.sel_els[0]).attr("data-nc-wysiwyg-border-top")) ? "" : $(options.sel_els[0]).css('borderTopWidth'),
			style			: ($(options.sel_els[0]).attr("data-nc-wysiwyg-border-top")) ? "" : $(options.sel_els[0]).css('borderTopStyle'),
			color			: (c == "transparent" || $(options.sel_els[0]).attr("data-nc-wysiwyg-border-top")) ? options.borderColor : c,
			border          : {Top: true, Right: true, Bottom: true, Left: true}
		};
		init();

		showPreview();

		setTimeout(function() { $("#nc-wysiwyg-tableboder-icon-none")[0].focus(); }, 100);


		return;

		function init() {
			var table, div, list, borderstyle, buttons;

			var borderWidth = property.width;
			var borderStyle = property.style;
			var borderColor = property.color;

			div = $('<div style="clear:left;"></div>').appendTo( self );
			table = $('<ul class="nc-wysiwyg-tableboder"></ul>').appendTo( div );
			$(table).append('<li class="nc-wysiwyg-tableborder-title"><dl><dt>'+ __d('nc_wysiwyg_tableborder', active_tab) +'</dt>' +
				((active_tab != 'table') ? '<dd>'+ __d('nc_wysiwyg_tableborder', 'separator') + options.pos_title +'</dd>' : '') + '</dl></li>');
			_createBoderPreview();

			borderwidth = $('<li class="nc-wysiwyg-tableborder-content float-left"></li>').appendTo(table);
			list = _initListMenu('border_width', options.borderWidthList, 'border-bottom:', ' solid;');
			listWidth = options.wysiwyg.appendListMain(borderwidth, list, "nc-wysiwyg-tableborder-border",_borderWidthEvent,[]);
			options.wysiwyg.chgList(listWidth, borderWidth);

			borderstyle = $('<li class="nc-wysiwyg-tableborder-content float-left"></li>').appendTo(table);
			list = _initListMenu('border_style', options.borderStyleList, 'border-bottom:3px ', ';');
			listStyle = options.wysiwyg.appendListMain(borderstyle, list, "nc-wysiwyg-tableborder-border",_borderStyleEvent,[]);
			options.wysiwyg.chgList(listStyle, borderStyle);

			$(table).append('<li id="nc-wysiwyg-tableboder-borderColor" class="nc-wysiwyg-tableborder-content float-left"><dl>'+
										'<dt>'+ __d('nc_wysiwyg_tableborder', 'color') + __d('nc_wysiwyg_tableborder', 'separator') + '</dt>'+
										'<dd>'+
											'<input type="text" name="bordercolor" class="nc-wysiwyg-tableborder-color-input" value="'+ borderColor +'" maxlength="7"  />'+
											'<a href="javascript:;" class="nc-wysiwyg-tableborder-color">'+
												'<img style="background-color:' + (borderColor ? borderColor : options.borderColor) + ';"' + ' src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/sel_color.gif" title="' + "" + '" alt="' + "" + '" />'+
											'</a>'+
										'</dd>'+
							'</dl></li>');
			$("#nc-wysiwyg-tableboder-icon-none").click(function(e){
				property.border = {Top: false, Right: false, Bottom: false, Left: false};
				setPreview("none");
				e.preventDefault();
			    return false;
			});

			$("#nc-wysiwyg-tableboder-icon-outer").click(function(e){
				property.border = {Top: true, Right: true, Bottom: true, Left: true};
				_chgList();
				setPreview();
				e.preventDefault();
			    return false;
			});

			$("#nc-wysiwyg-tableboder-icon-top").click(function(e){
				property.border['Top'] = (property.border['Top']) ? false : true;
				if(property.border['Top'])
					_chgList();
				setPreview("Top");
				e.preventDefault();
			    return false;
			});

			$("#nc-wysiwyg-tableboder-icon-bottom").click(function(e){
				property.border['Bottom'] = (property.border['Bottom']) ? false : true;
				if(property.border['Bottom'])
					_chgList();
				setPreview("Bottom");
				e.preventDefault();
			    return false;
			});

			$("#nc-wysiwyg-tableboder-icon-left").click(function(e){
				property.border['Left'] = (property.border['Left']) ? false : true;
				if(property.border['Left'])
					_chgList();
				setPreview("Left");
				e.preventDefault();
			    return false;
			});

			$("#nc-wysiwyg-tableboder-icon-right").click(function(e){
				property.border['Right'] = (property.border['Right']) ? false : true;
				if(property.border['Right'])
					_chgList();
				setPreview("Right");
				e.preventDefault();
			    return false;
			});

			// borderカラークリック
			$(".nc-wysiwyg-tableborder-color", div).click(function(e){
				var self = this;
				var c = $.Common.getColorCode($("img", self)[0], 'backgroundColor');
				if(c == "transparent")
					c = options.color;
				var callback = function() {
					var opts = {
						colorcode : c,
						callback  : function(v) {
							$("img", self).css({'backgroundColor': v});
							$(self.previousSibling).val(v);
							property.color = v;
							//setPreview();
							options.wysiwyg.removeDialog("nc-wysiwyg-tableborder-color");
						},
						cancel_callback  : function(v) {
							options.wysiwyg.removeDialog("nc-wysiwyg-tableborder-color");
						}
					};
					$("#nc-wysiwyg-tableborder-color").nc_colorpicker(opts);
				};
				var toggle_options = {
					id        : "nc-wysiwyg-tableborder-color",
					js        : [$._base_url+'js/plugins/jquery.colorpicker.js'],
					jsname    : ['$.fn.nc_colorpicker'],
					pos_base  : $(self.previousSibling),
					style     : {left:"left", top:"outbottom"},
					callback  : callback
				};
				options.wysiwyg.toggleDialog(self, toggle_options);
				e.preventDefault();
			    return false;
			});

			$(".nc-wysiwyg-tableborder-color-input", div).keyup(function(e){
				var c = $(this).val();
				if(c.match(/^#[0-9a-f]{6}/i)) {
					$("img", this.nextSibling).css({'backgroundColor': c});
					property.color = c;
					//setPreview();
				}
			}).focus(function(e){
				options.wysiwyg.removeDialog("nc-wysiwyg-tableborder-color");
			});

			//ok cancel button
			buttons = $('<div class="nc-wysiwyg-tableborder-btn"></div>').appendTo( self );
			buttons.append($('<input name="ok" type="button" class="common-btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'ok')+'" />')
				.click(function(e){
					_registBorder();
					options.wysiwyg.removeDialog("nc-wysiwyg-tableborder");
					e.preventDefault();
					return false;
				}));

			buttons.append($('<input name="cancel" type="button" class="common-btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'cancel')+'" />')
				.click(function(e){
					options.wysiwyg.removeDialog("nc-wysiwyg-tableborder");
					e.preventDefault();
			        return false;
				}));

			function _borderWidthEvent(e, v){
				property.width = v;
				_chgList("width");
				return true;
			}
			function _borderStyleEvent(e, v){
				property.style = v;
				_chgList("style");
				//setPreview();
				return true;
			}
			function _createBoderPreview(){

					$(table).append('<li class="nc-wysiwyg-tableborder-content float-left"><ul class="nc-wysiwyg-tableborder-align">'+
										'<li><ul class="nc-wysiwyg-tableboder-icon-l">'+
											'<li><a href="javascript:;" id="nc-wysiwyg-tableboder-icon-none"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-border-none.gif" title="'+ __d('nc_wysiwyg_tableborder', 'none') +'" alt="'+ __d('nc_wysiwyg_tableborder', 'none') +'" /></a></li>'+
											'<li><a href="javascript:;" id="nc-wysiwyg-tableboder-icon-outer"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-border-outer.gif" title="'+ __d('nc_wysiwyg_tableborder', 'outer') +'" alt="'+ __d('nc_wysiwyg_tableborder', 'outer') +'" /></a></li>'+
										'</ul></li>'+
										'<li class="nc-wysiwyg-tableboder-align-clear"><ul class="nc-wysiwyg-tableboder-icon">'+
											'<li class="nc-wysiwyg-tableborder-preview">' +
												'<div id="nc-wysiwyg-tableborder-preview">'+
												'</div>' +
											'</li>'+
											'<li><a href="javascript:;" id="nc-wysiwyg-tableboder-icon-top"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-border-top.gif" title="'+ __d('nc_wysiwyg_tableborder', 'top') +'" alt="'+ __d('nc_wysiwyg_tableborder', 'top') +'" /></a></li>'+
											'<li><a href="javascript:;" id="nc-wysiwyg-tableboder-icon-bottom"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-border-bottom.gif" title="'+ __d('nc_wysiwyg_tableborder', 'bottom') +'" alt="'+ __d('nc_wysiwyg_tableborder', 'bottom') +'" /></a></li>'+
										'</ul></li>'+
										'<li class="nc-wysiwyg-tableboder-align-clear"><ul class="nc-wysiwyg-tableboder-icon-bottom">'+
											'<li><a href="javascript:;" id="nc-wysiwyg-tableboder-icon-left"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-border-left.gif" title="'+ __d('nc_wysiwyg_tableborder', 'left') +'" alt="'+ __d('nc_wysiwyg_tableborder', 'left') +'" /></a></li>'+
											'<li><a href="javascript:;" id="nc-wysiwyg-tableboder-icon-right"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-border-right.gif" title="'+ __d('nc_wysiwyg_tableborder', 'right') +'" alt="'+ __d('nc_wysiwyg_tableborder', 'right') +'" /></a></li>'+
										'</ul></li>'+
									'</ul></li>');
			}
			function _initListMenu(title, list, style_pre, style_post) {
				var html = {'' : __d('nc_wysiwyg_insertimage', title)};
				for(var i in list) {
					html[list[i]] = '<div style="' + style_pre + list[i] + style_post +';"></div>';
				}
				return html;
			}

			function _chgList(type) {
				var width = options.wysiwyg.getList(listWidth);
				var style = options.wysiwyg.getList(listStyle);
				if(width == "" && type != "width" && (type == undefined || style != "")) {
					options.wysiwyg.chgList(listWidth, "1px", ((type == undefined) ? true : false));
					property.width = "1px";
				}
				if(style == "" && type != "style" && (type == undefined || width != "")) {
					options.wysiwyg.chgList(listStyle, "solid", ((type == undefined) ? true : false));
					property.style = "solid";
				}
			}

			/* 登録処理 */
			function _registBorder() {
				switch (active_tab) {
					case "table":
						_regist(options.sel_els[0]);
						break;
					case "row":
						$(options.sel_els).each(function(k, v){
							/*var buf_border = {
								Top : property.border['Top'],
								Right : property.border['Right'],
								Bottom : property.border['Bottom'],
								Left : property.border['Left']
							};
							if(v.cellIndex == 0) {
								buf_border['Right'] = false;
							} else if(v.cellIndex == v.parentNode.cells.length - 1) {

								buf_border['Left'] = false;
							} else {
								buf_border['Right'] = false;
								buf_border['Left'] = false;
							}*/

							_regist(v);
						});
						break;
					case "col":
						$(options.sel_els).each(function(k, v){
							/*var buf_border = {
								Top : property.border['Top'],
								Right : property.border['Right'],
								Bottom : property.border['Bottom'],
								Left : property.border['Left']
							};
							if(v.parentNode.rowIndex == 0) {
								buf_border['Bottom'] = false;
							} else if(v.parentNode.rowIndex == options.table_el.rows.length - 1) {
								buf_border['Top'] = false;
							} else {
								buf_border['Top'] = false;
								buf_border['Bottom'] = false;
							}*/
							_regist(v);
						});
						break;
					case "cell":
						$(options.sel_els).each(function(k, v){
							_regist(v);
						});
						break;
				}
				// 再描画
				$(options.table_el).hide();
				setTimeout(function() { $(options.table_el).show(); }, 100);

				function _regist(el) {
					var preview = $("#nc-wysiwyg-tableborder-preview");
					$.each(['Top','Right','Bottom','Left'], function(k, v) {
						$(el).css("border"+v, preview.css("border"+ v));
						if(property.border[v] == false) {
							$(el).css("border"+v, "1px dotted #666666");
							$(el).attr("data-nc-wysiwyg-border-" + v.toLowerCase(), "1");
						} else {
							$(el).removeAttr("data-nc-wysiwyg-border-" + v.toLowerCase());
						}
					});
				}
			}
		}

		function setPreview(type) {
			var width,style,color;
			var preview = $("#nc-wysiwyg-tableborder-preview");
			$.each(['Top','Right','Bottom','Left'], function(k, v) {
				if(type == undefined || type == v || type == "none") {
					width = preview.css("border" + v + "Width");
					style = preview.css("border" + v + "Style");
					color = $.Common.getColorCode(preview[0],"border" + v + "Color");
					if(type != "none" && property.width != "" && property.style != "" && (property.border[v] ||
						(width != property.width || style != property.style || color != property.color))) {
						property.border[v] = true;
						preview.css("border" + v , property.width + " " + property.style + " " + property.color);
					} else {
						property.border[v] = false;
						preview.css("border" + v , "2px dotted #666666");
					}
				}
			});
		}

		function showPreview() {
			// preview
			var edge_tds = _getEdgeTds(options.sel_els);
			switch (active_tab) {
				case "table":
					_setTransparentAttrPreviewLine(options.sel_els[0]);
					_tracePreviewLine($(options.sel_els[0]).css("border"));
					break;
				case "row":
				case "col":
					_setTransparentAttrPreviewLine(edge_tds[0], "Top");
					_setTransparentAttrPreviewLine(edge_tds[1], "Right");
					_setTransparentAttrPreviewLine(edge_tds[2], "Bottom");
					_setTransparentAttrPreviewLine(edge_tds[0], "Left");
					_tracePreviewLine({
						borderTop    : $(edge_tds[0]).css("borderTop"),
						borderRight  : $(edge_tds[1]).css("borderRight"),
						borderBottom : $(edge_tds[2]).css("borderBottom"),
						borderLeft   : $(edge_tds[0]).css("borderLeft")
					});
					break;
				case "cell":
					_setTransparentAttrPreviewLine(edge_tds[0]);
					_tracePreviewLine({
						borderTop    : $(edge_tds[0]).css("borderTopWidth") + " " + $(edge_tds[0]).css("borderTopStyle") + " " + $(edge_tds[0]).css("borderTopColor"),
						borderRight  : $(edge_tds[0]).css("borderRightWidth") + " " + $(edge_tds[0]).css("borderRightStyle") + " " + $(edge_tds[0]).css("borderRightColor"),
						borderBottom : $(edge_tds[0]).css("borderBottomWidth") + " " + $(edge_tds[0]).css("borderBottomStyle") + " " + $(edge_tds[0]).css("borderBottomColor"),
						borderLeft   : $(edge_tds[0]).css("borderLeftWidth") + " " + $(edge_tds[0]).css("borderLeftStyle") + " " + $(edge_tds[0]).css("borderLeftColor")
					});
					break;
			}

			return;

			function _tracePreviewLine(border) {
				var preview = $("#nc-wysiwyg-tableborder-preview");
				if (typeof(border) == 'string') {
					preview.css("border", border);
				} else {
					preview.css(border);
				}
				$.each(['Top','Right','Bottom','Left'], function(k, v) {
					if(property.border[v] == false) {
						preview.css("border"+ v + "Width", "2px");
					}
				});
				//preview.css("borderWidth", "3px");
			}

			// tableボーダー0の場合、tableが点線に変わっているため、ダイアログも合わせる
			function _setTransparentAttrPreviewLine(el, value) {
				var preview = $("#nc-wysiwyg-tableborder-preview");
				$.each(['Top','Right','Bottom','Left'], function(k, v) {
					if($(el).attr("data-nc-wysiwyg-border-" + v.toLowerCase()) && (value == undefined || value == v)) {
						property.border[v] = false;
					}
				});
			}

			// 中心部から最も遠いtdを4点選出
			function _getEdgeTds(sel_els) {
				var ret = [sel_els[0], sel_els[0], sel_els[0], sel_els[0]];
				if(sel_els[0].nodeName.toLowerCase() != "table") {
					$(sel_els).each(function(k, v) {
						if(ret[0] == null || ret[0].parentNode.rowIndex > v.parentNode.rowIndex &&
							ret[0].cellIndex > v.cellIndex) {
							ret[0] = v;
						}
						if(ret[1] == null || ret[1].parentNode.rowIndex > v.parentNode.rowIndex &&
							ret[1].cellIndex < v.cellIndex) {
							ret[1] = v;
						}
						if(ret[2] == null || ret[2].parentNode.rowIndex < v.parentNode.rowIndex &&
							ret[2].cellIndex > v.cellIndex) {
							ret[2] = v;
						}
						if(ret[3] == null || ret[3].parentNode.rowIndex < v.parentNode.rowIndex &&
							ret[3].cellIndex < v.cellIndex) {
							ret[3] = v;
						}
					});
				}
				return ret;
			}
		}
	}
})(jQuery);