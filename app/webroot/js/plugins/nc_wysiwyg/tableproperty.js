/*
 * NC TableProperty 0.0.0.1
 * @param options    hash
 *                      wysiwyg         : nc_wysiwyg object
 *                      table_pos       : hash   　nc_wysiwygのgetSelectTablePosメソッドの返り値（詳しくはgetSelectTablePosメソッド参照）
 *                      color           : string カラーダイアログのデフォルト値
 */
 ;(function($) {
	$.fn.nc_tableproperty = function(options) {
		var options = $.extend({
			table_pos	: null,
			wysiwyg     : null,
			color       : "#ff0000"
		}, options);
		var self = this;
		var active_tab = options['table_pos']['sel_name'];

		var property = getPropertyValue(options.table_pos);

		init();

		return;

		function getPropertyValue(table_pos) {
			var property = {}, pos;
			property['table'] = _setProperty("table", table_pos['table_el']);
			pos = getRowPos();
			property['row'] = _setProperty("row", pos[1][0], pos);
			pos = getColPos();
			property['col'] = _setProperty("col", pos[1][0], pos);
			pos = getCellPos();
			property['cell'] = _setProperty("cell", pos[1][0], pos);

			return property;

			function _setProperty(type, base_el, pos) {
				var property;
				var c = $.Common.getColorCode(base_el, 'color');
				var bc = $.Common.getColorCode(base_el, 'backgroundColor');
				switch (type) {
					case "table":
						property = {
							sel_els         : [base_el],
							textAlign       : _getTextAlign(base_el.style.textAlign),
							verticalAlign   : _getVerticalAlign(base_el.style.verticalAlign),
							width           : parseInt(base_el.style.width) || "",
							widthUnit       : $(base_el).css('width').match(/%$/) ? '%' : 'px',
							height          : parseInt(base_el.style.height) || "",
							heightUnit      : $(base_el).css('height').match(/%$/) ? '%' : 'px',
							marginWidth     : parseInt($(base_el).css('marginLeft')) || 0,
							marginHeight    : parseInt($(base_el).css('marginTop')) || 0,
							cellPadding     : $(base_el).attr('cellPadding') || "",
							cellSpacing     : $(base_el).attr('cellSpacing') || "",
							borderCollapse  : $(base_el).css('borderCollapse'),
							backgroundColor : (bc == "transparent") ? "" : bc,
							color           : (c == "transparent") ? "" : c,
							whiteSpace      : $(base_el).css('whiteSpace') || "",
							uniformlySized  : getUniformlySized(base_el),
							summary         : $(base_el).attr('summary') || ''
						};
						break;
					case "row":
					case "col":
					case "cell":
						property = {
							pos             : pos[0],
							sel_els         : pos[1],
							textAlign       : _getTextAlign(pos[1][0].style.textAlign),
							verticalAlign   : _getVerticalAlign(pos[1][0].style.verticalAlign),
							width           : (pos[1][0].style.width) ? parseInt(pos[1][0].style.width) : "",
							widthUnit       : (pos[1][0].style.width).match(/%$/) ? '%' : 'px',
							height          : (pos[1][0].style.height) ? parseInt(pos[1][0].style.height) : "",
							heightUnit      : (pos[1][0].style.height).match(/%$/) ? '%' : 'px',
							cellPadding     : (pos[1][0].style.paddingTop) ? parseInt(pos[1][0].style.paddingTop) : "",
							backgroundColor : (bc == "transparent") ? "" : bc,
							color           : (c == "transparent") ? "" : c,
							whiteSpace      : $(pos[1][0]).css('whiteSpace') || ""
						};
						break;
				}
				return property;
			}

			function _getTextAlign(value) {
				var ret = "";
				switch (value) {
					case "left":
					case "center":
					case "right":
						ret = value;
						break;
				}
				return ret;
			};

			function _getVerticalAlign(value) {
				var ret = "";
				switch (value) {
					case "top":
					case "middle":
					case "bottom":
						ret = value;
						break;
				}
				return ret;
			};
		}

		// セル幅が均一かどうか
		function getUniformlySized(table) {
			var w=null, ret = null;
			$("td", table).each(function(k, v){
				if(ret == false)
					return ret;
				var w_buf = $(v).css('width');
				if(w_buf.match(/%$/)) {
					if($(v).attr("colspan") > 1)
						w_buf = parseInt(parseInt(w_buf)/$(v).attr("colspan"));
					else
						w_buf = parseInt(w_buf);
					if(w == null)
						w = parseInt(w_buf);
					if(w == w_buf) {
						ret = true;
					} else {
						ret = false;
					}
				}
			});
			if(ret == null)
				ret = false;
			return ret;
		}

		// セル幅を均一にセット
		function setUniformlySized(table) {
			var n = $("tr", table)[0], i = 3, td, cnt = 0, percent = 100;
			// 列数を求める
			for (var i = 0; i < n.childNodes.length; i++) {
				td = n.childNodes[i];
				if($(td).attr("colspan") > 1)
					cnt += $(td).attr("colspan");
				else
					cnt++;
			}
			percent = Math.floor(100/cnt);
			$("td", table).each(function(k, v){
				$(v).css({width : (percent*parseInt($(v).attr("colspan"))) + "%"});
			});
			return true;
		}

		// 選択行の位置取得
		// rowSpanは考慮せず、単純に選択行を取得する
		// @return array[title string, array rows]
		function getRowPos() {
			var rows = {}, ret_rows = [], ret_str = '', apret_str = null, preRowIndex = 0,row_flag=false;
			$(options.table_pos.cell_els).each(function(k, v){
				var row = v.parentNode, rowIndex = row.rowIndex;
				if(!rows[rowIndex]) {
					rows[rowIndex] = row;
					ret_rows.push(row);
					if(ret_str == '')
						ret_str += (rowIndex + 1);
					else if(preRowIndex + 1 == rowIndex) {
						if(apret_str != null && row_flag) {
							ret_str += apret_str;
							apret_str = null;
							row_flag = false;
						}
						apret_str = __d(['nc_wysiwyg_tableproperty', 'panel'], 'col_sep') + (rowIndex + 1);
					} else {
						if(apret_str != null) {
							ret_str += apret_str;
							apret_str = null;
						}
						row_flag = true;
						apret_str = __d(['nc_wysiwyg_tableproperty', 'panel'], 'row_sep')  + "&nbsp;" + (rowIndex + 1);
					}
					preRowIndex = rowIndex;
				}
			});

			if(apret_str != null)
				ret_str += apret_str;
			return [ret_str, ret_rows];
		}

		// 選択列の位置取得
		// colSpanは考慮せず、単純に選択列を取得する
		// @return array[title string, array cells]
		function getColPos() {
			var cells = {}, ret_cells = [], ret_str = '', apret_str = null, preCellIndex = 0, buf_cells = {};
			// cellIndex順にソート
			var cell_els = [];
			$(options.table_pos.cell_els).each(function(k, v){
				var row = v.parentNode, rowIndex = row.rowIndex;
				var cell = v, cellIndex = cell.cellIndex;
				if(!cell_els[cellIndex]) {
					cell_els[cellIndex] = {};
				}

				if(!cell_els[cellIndex][rowIndex]) {
					cell_els[cellIndex][rowIndex] = cell;
				}
			});
			cells = cell_els;

			$.each(cell_els, function(k, v){
				if(v) {
					$.each(v, function(sub_k, sub_v){
						var cell = sub_v, cellIndex = cell.cellIndex;
						ret_cells.push(cell);
						// 選択列内のその他のセルを取得
						$("tr", options.table_pos['table_el']).each(function(other_tr_k, other_tr_v){
							var other_tr_v_index = 0;
							$("td", other_tr_v).each(function(other_k, other_v){
								var other_cellIndex = other_v.cellIndex + other_tr_v_index;
								var other_row = other_v.parentNode, other_rowIndex = other_row.rowIndex;
								if($(other_v).attr("colspan") > 1) {
									other_tr_v_index += $(other_v).attr("colspan") - 1;
								}
								if(cellIndex == other_cellIndex) {
									// 列が等しい
									if(!cells[other_cellIndex]) {
										cells[other_cellIndex] = {};
									}
									if(!cells[other_cellIndex][other_rowIndex]) {
										cells[other_cellIndex][other_rowIndex] = other_v;
										ret_cells.push(other_v);
									}
								}
							});
						});

						if(!buf_cells[cellIndex]) {
							if(ret_str == '')
								ret_str += (cellIndex + 1);
							else if(preCellIndex + 1 == cellIndex)
								apret_str = __d(['nc_wysiwyg_tableproperty', 'panel'], 'col_sep') + (cellIndex + 1);
							else {
								if(apret_str != null) {
									ret_str += apret_str;
									apret_str = null;
								}
								apret_str = __d(['nc_wysiwyg_tableproperty', 'panel'], 'row_sep')  + "&nbsp;" + (cellIndex + 1);
							}
							preCellIndex = cellIndex;
							buf_cells[cellIndex] = cell;
						}
					});
				}
			});

			if(apret_str != null)
				ret_str += apret_str;

			return [ret_str, ret_cells];
		}

		// 選択セルの位置取得
		// @return array[title string, array cells]
		function getCellPos() {
			var cells = {}, ret_cells = options.table_pos.cell_els, ret_str = '';

			$(options.table_pos.cell_els).each(function(k, v){
				var row = v.parentNode, rowIndex = row.rowIndex;
				var cell = v, cellIndex = cell.cellIndex;

				if(!cells[rowIndex]) {
					cells[rowIndex] = {};
				}

				if(!cells[rowIndex][cellIndex]) {
					cells[rowIndex][cellIndex] = cell;
					if(ret_str != '')
						ret_str += __d(['nc_wysiwyg_tableproperty', 'panel'], 'row_sep') + "&nbsp;";
					ret_str += (rowIndex + 1) + __d(['nc_wysiwyg_tableproperty', 'panel'], 'cell_sep') + (cellIndex + 1);
				}
			});

			return [ret_str, ret_cells];
		}

		function init() {
			var div, tabs, table, col, row, cell,buttons;
			var select_tab = options['table_pos']['sel_name'];


			tabs = $('<ul class="nc_wysiwyg_tableproperty"></ul>').appendTo( self );

			div = $('<div class="clearfix"></div>').appendTo( self );
			
			_appendTab(tabs, 'table', __d(['nc_wysiwyg_tableproperty', 'tab_name'], 'table'), (select_tab == 'table') ? true : false);
			div.append(_createContent('table'));

			_appendTab(tabs, 'row', __d(['nc_wysiwyg_tableproperty', 'tab_name'], 'row'), (select_tab == 'row') ? true : false);
			div.append(_createContent('row', __d(['nc_wysiwyg_tableproperty', 'tab_name'], 'row')));

			_appendTab(tabs, 'col', __d(['nc_wysiwyg_tableproperty', 'tab_name'], 'col'), (select_tab == 'col') ? true : false);
			div.append(_createContent('col', __d(['nc_wysiwyg_tableproperty', 'tab_name'], 'col')));

			_appendTab(tabs, 'cell', __d(['nc_wysiwyg_tableproperty', 'tab_name'], 'cell'), (select_tab == 'cell') ? true : false);
			div.append(_createContent('cell', __d(['nc_wysiwyg_tableproperty', 'tab_name'], 'cell')));

			$('ul#tableproperty_panel_'+select_tab).show();

			// 線を重ねる
			$("#nc_wysiwyg_tableproperty_table_borderCollapse").click(function(e){
				if(this.checked)
					$("#nc_wysiwyg_tableproperty_table_cellSpacing")[0].disabled = true;
				else
					$("#nc_wysiwyg_tableproperty_table_cellSpacing")[0].disabled = false;
			});

			// 背景選択
			$(".nc_wysiwyg_tableproperty_sel_color", div).click(function(e){
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
							options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty_color");
						},
						cancel_callback  : function(v) {
							options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty_color");
						}
					};
					$("#nc_wysiwyg_tableproperty_color").nc_colorpicker(opts);
				};
				var toggle_options = {
					id        : "nc_wysiwyg_tableproperty_color",
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

			$(".nc_wysiwyg_tableproperty_color_input", div).keyup(function(e){
				var c = $(this).val();
				if(c.match(/^#[0-9a-f]{6}/i)) {
					$("img", this.nextSibling).css({'backgroundColor': c});
				}
			}).focus(function(e){
				options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty_color");
			});

			//ok cancel button
			buttons = $('<div class="btn-bottom"></div>').appendTo( self );
			$(buttons).append($('<input name="ok" type="button" class="common_btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'ok')+'" />')
				.click(function(e){
					// 決定
					// Activeのタブのみ更新。その他、タブまで更新してしまうと
					// 本来、更新したくないものまで更新されてしまうため
					var css = null, attr = null;
					var w_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_width"), w = w_el.val();
					var w_unit_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_width_unit"), w_unit = w_unit_el.val();
					var h_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_height"), h = h_el.val();
					var h_unit_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_height_unit"), h_unit = h_unit_el.val();
					var bgc_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_backgroundColor"), bgc = bgc_el.val();
					var c_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_color"), c = c_el.val();
					var ws_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_whiteSpace");

					var m_w_el, m_w, m_h_el, m_h, bc_el, cp_el, cp, cs_el, cs, summary_el, summary;
					var cell_p_el,cell_p;
					if(!c.match(/^#[0-9a-f]{6}/i))
						c = '';
					if(!bgc.match(/^#[0-9a-f]{6}/i))
						bgc = '';

					switch (active_tab) {
						case "table":
							m_w_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_margin_width"), m_w = m_w_el.val();
							m_h_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_margin_height"), m_h = m_h_el.val();
							bc_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_borderCollapse");
							cp_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_cellPadding"), cp = cp_el.val();
							cs_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_cellSpacing"), cs = cs_el.val();
							summary_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_summary"), summary = options.wysiwyg.htmlEncode(summary_el.val());
							css = {
								width           : parseInt(w) ? parseInt(w) + w_unit : "",
								height          : parseInt(h) ? parseInt(h) + h_unit : "",
								margin          : (m_w != '' || m_h != '') ? (parseInt(m_h) + 'px ' + parseInt(m_w) + 'px') : '',
								borderCollapse  : (bc_el[0].checked) ? 'collapse' : 'separate',
								backgroundColor : bgc,
								color           : c,
								whiteSpace      : (ws_el[0].checked) ? 'nowrap' : ''
							};
							attr = {
								cellPadding     : cp,
								cellSpacing     : cs,
								summary         : summary
							};
							// セル幅を均一に
							if($("#nc_wysiwyg_tableproperty_" + "cell_equality")[0].checked)
								setUniformlySized(property[active_tab].sel_els[0]);
							else if(getUniformlySized(property[active_tab].sel_els[0])) {
								$("td", property[active_tab].sel_els[0]).each(function(k, v){
									$(v).css({width : ''});
								});
							}
							break;
						case "row":
						case "col":
						case "cell":
							cell_p_el = $("#nc_wysiwyg_tableproperty_" + active_tab+ "_padding"), cell_p = cell_p_el.val();
							css = {
								width           : parseInt(w) ? parseInt(w) + w_unit : "",
								height          : parseInt(h) ? parseInt(h) + h_unit : "",
								padding         : (cell_p != '') ? (parseInt(cell_p) + 'px ' + parseInt(cell_p) + 'px') : '',
								backgroundColor : bgc,
								color           : c,
								whiteSpace      : (ws_el[0].checked) ? 'nowrap' : ''
							};
					}
					// 配置
					$(".nc_wysiwyg_tableproperty_active", $("#tableproperty_panel_" + active_tab)).each(function(k, v) {
						if($(v).hasClass("nc_wysiwyg_tableproperty_" + active_tab + "_left")) {
							css['textAlign'] = "left";
						} else if($(v).hasClass("nc_wysiwyg_tableproperty_" + active_tab + "_center")) {
							css['textAlign'] = "center";
						} else if($(v).hasClass("nc_wysiwyg_tableproperty_" + active_tab + "_right")) {
							css['textAlign'] = "right";
						} else if($(v).hasClass("nc_wysiwyg_tableproperty_" + active_tab + "_top")) {
							css['verticalAlign'] = "top";
						} else if($(v).hasClass("nc_wysiwyg_tableproperty_" + active_tab + "_middle")) {
							css['verticalAlign'] = "middle";
						} else if($(v).hasClass("nc_wysiwyg_tableproperty_" + active_tab + "_bottom")) {
							css['verticalAlign'] = "bottom";
						}
					});
					if(!css['textAlign'])
						css['textAlign'] = '';

					if(!css['verticalAlign']) {
						css['verticalAlign'] = '';
					}
					if(active_tab == "table") {
						$("td", options.table_pos['table_el']).each(function(k, v){
							$(v).css({verticalAlign : css['verticalAlign']});
						});
					}
					$.each(property[active_tab].sel_els, function(k, v) {
						if(css != null)
							$(v).css(css);
						if(attr != null)
							$(v).attr(attr);
						if(active_tab == "row") {
							$("td", v).each(function(td_k, td_v){
								$(td_v).css({padding : css['padding']});
							});
						}
					});
					options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty");
					e.preventDefault();
			        return false;
				}));

			$(buttons).append($('<input name="cancel" type="button" class="common_btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'cancel')+'" />')
				.click(function(e){
					options.wysiwyg.removeDialog("nc_wysiwyg_tableproperty");
					e.preventDefault();
			        return false;
				}));

			return;

			function _appendTab(tabs, name, title, active) {
				var tab = $('<li><a class="nc_wysiwyg_tableproperty_tab" href="javascript:;" ><span>'+ title +'</span></a></li>').appendTo(tabs);

				if (active == true) {
					var active_a = $("a", tab);
					active_a.css({backgroundColor : "#ffffff"});
					// focus移動
					setTimeout(function() { active_a.focus(); }, 100);
				}
				var atag = $("a.nc_wysiwyg_tableproperty_tab",tab);
				atag.click(function(){
					$("a.nc_wysiwyg_tableproperty_tab", tabs).css({backgroundColor : ""});
					$(this).css({backgroundColor : "#ffffff"});
					// set active tab
					active_tab = name;
					$('ul.tableproperty_panel').hide();
					$('ul#tableproperty_panel_'+ name).toggle();
				});
				return ;
			}

			function _createContent(type, title) {
				var content,pos;
				

				content = $('<ul id="tableproperty_panel_'+ type +'" class="tableproperty_panel"></ul>').hide();
				if (type == 'table') {
					_appendTextAlign(type, content, property.table.textAlign, property.table.verticalAlign);
					$(content).append('<li><dl><dt style="float: left;">'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'width') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_table_width" name="table_width" class="tableproperty_text align-right" value="'+ property.table.width +'" /><select id="nc_wysiwyg_tableproperty_table_width_unit" name="table_width_unit"><option value="px" '+ ((property.table.widthUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((property.table.widthUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'height') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_table_height" name="table_height" class="tableproperty_text align-right" value="'+ property.table.height +'" /><select id="nc_wysiwyg_tableproperty_table_height_unit" name="table_height_unit"><option value="px" '+ ((property.table.heightUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((property.table.heightUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'margin_width') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_table_margin_width" name="table_margin_width" class="tableproperty_text align-right" value="'+ property.table.marginWidth +'" />px</dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'margin_height') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_table_margin_height" name="table_margin_height" class="tableproperty_text align-right" value="'+ property.table.marginHeight +'" />px</dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'cellpadding') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_table_cellPadding" name="table_cellPadding" class="tableproperty_text align-right" value="'+ property.table.cellPadding +'"/>px</dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'cellspacing') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input id="nc_wysiwyg_tableproperty_table_cellSpacing" type="text" id="nc_wysiwyg_tableproperty_table_cellSpacing" name="table_cellSpacing" class="tableproperty_text align-right" value="'+ property.table.cellSpacing +'"'+ ((property.table.borderCollapse == 'collapse') ? ' disabled="disabled"' : '') +' />px</dd></dl></li>');
					$(content).append('<li><label for="nc_wysiwyg_tableproperty_table_borderCollapse"><input type="checkbox" id="nc_wysiwyg_tableproperty_table_borderCollapse" name="table_borderCollapse" '+ ((property.table.borderCollapse == 'collapse') ? 'checked="checked"' : '') +' />'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'border_pile') +'</label></li>');
					$(content).append('<li class="nc_wysiwyg_tableproperty_linebreak clear"></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'bgcolor') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_table_backgroundColor" name="table_backgroundColor" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ property.table.backgroundColor +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((property.table.backgroundColor != '') ? ' style="background-color:' + property.table.backgroundColor + '"' : '') + ' src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/sel_color.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" /></a></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'fontcolor') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_table_color" name="table_color" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ property.table.color +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((property.table.color != '') ? ' style="background-color:' + property.table.color + '"' : '') + ' src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/sel_color.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" /></a></dd></dl></li>');
					$(content).append('<li><label for="nc_wysiwyg_tableproperty_table_whiteSpace"><input type="checkbox" id="nc_wysiwyg_tableproperty_table_whiteSpace" name="table_whiteSpace" '+ ((property.table.whiteSpace == 'nowrap') ? 'checked="checked"' : '') +' />'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'nowrap') +'</label></li>');
					$(content).append('<li><label for="nc_wysiwyg_tableproperty_cell_equality"><input type="checkbox" id="nc_wysiwyg_tableproperty_cell_equality" name="cell_equality" '+ ((property.table.uniformlySized == true) ? 'checked="checked"' : '') +' />'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'cell_equality') +'</label></li>');
					_appendBorderBtn();
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'summary') +'</dt><span class="nc_wysiwyg_tableproperty_summary_sep">'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'</span><dd><textarea id="nc_wysiwyg_tableproperty_table_summary" name="table_summary" style="width:250px;height:50px;" >'+ property.table.summary +'</textarea></dd></dl></li>');
				} else if (type == 'row') {
					$(content).append('<li class="nc_wysiwyg_tableproperty_title"><dl><dt>' + title + '</dt><dd>' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') + property.row.pos +'</dd></dl></li>');
					_appendTextAlign(type, content, property.row.textAlign, property.row.verticalAlign);
					$(content).append('<li><dl><dt style="float: left;">'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'width') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_row_width" name="row_width" class="tableproperty_text align-right" value="'+ property.row.width +'" /><select id="nc_wysiwyg_tableproperty_row_width_unit" name="row_width_unit"><option value="px" '+ ((property.row.widthUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((property.row.widthUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'height') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_row_height" name="row_height" class="tableproperty_text align-right" value="'+ property.row.height +'" /><select id="nc_wysiwyg_tableproperty_row_height_unit" name="row_height_unit"><option value="px" '+ ((property.row.heightUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((property.row.heightUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'cellpadding') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_row_padding" name="row_padding" class="tableproperty_text align-right" value="'+ property.row.cellPadding +'"/>px</dd></dl></li>');
					$(content).append('<li class="nc_wysiwyg_tableproperty_linebreak clear" style="float:none;"></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'bgcolor') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_row_backgroundColor" name="row_backgroundColor" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ property.row.backgroundColor +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((property.row.backgroundColor != '') ? ' style="background-color:' + property.row.backgroundColor + '"' : '') + ' src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/sel_color.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" /></a></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'fontcolor') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_row_color" name="row_color" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ property.row.color +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((property.row.color != '') ? ' style="background-color:' + property.row.color + '"' : '') + ' src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/sel_color.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" /></a></dd></dl></li>');
					$(content).append('<li><label for="nc_wysiwyg_tableproperty_row_whiteSpace"><input type="checkbox" id="nc_wysiwyg_tableproperty_row_whiteSpace" name="row_whiteSpace" '+ ((property.row.whiteSpace == 'nowrap') ? 'checked="checked"' : '') +' />'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'nowrap') +'</label></li>');
					_appendBorderBtn();
				} else if (type == 'col') {
					$(content).append('<li class="nc_wysiwyg_tableproperty_title"><dl><dt>' + title + '</dt><dd>' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') + property.col.pos +'</dd></dl></li>');
					_appendTextAlign(type, content, property.col.textAlign, property.col.verticalAlign);
					$(content).append('<li><dl><dt style="float: left;">'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'width') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_col_width" name="col_width" class="tableproperty_text align-right" value="'+ property.col.width +'" /><select id="nc_wysiwyg_tableproperty_col_width_unit" name="col_width_unit"><option value="px" '+ ((property.col.widthUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((property.col.widthUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'height') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_col_height" name="col_height" class="tableproperty_text align-right" value="'+ property.col.height +'" /><select id="nc_wysiwyg_tableproperty_col_height_unit" name="col_height_unit"><option value="px" '+ ((property.col.heightUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((property.col.heightUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'cellpadding') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_col_padding" name="col_padding" class="tableproperty_text align-right" value="'+ property.col.cellPadding +'"/>px</dd></dl></li>');
					$(content).append('<li class="nc_wysiwyg_tableproperty_linebreak clear"></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'bgcolor') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_col_backgroundColor" name="col_backgroundColor" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ property.col.backgroundColor +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((property.col.backgroundColor != '') ? ' style="background-color:' + property.col.backgroundColor + '"' : '') + ' src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/sel_color.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" /></a></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'fontcolor') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_col_color" name="col_color" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ property.col.color +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((property.col.color != '') ? ' style="background-color:' + property.col.color + '"' : '') + ' src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/sel_color.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" /></a></dd></dl></li>');
					$(content).append('<li><label for="nc_wysiwyg_tableproperty_col_whiteSpace"><input type="checkbox" id="nc_wysiwyg_tableproperty_col_whiteSpace" name="col_whiteSpace" '+ ((property.col.whiteSpace == 'nowrap') ? 'checked="checked"' : '') +' />'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'nowrap') +'</label></li>');
					_appendBorderBtn();
				} else if (type == 'cell') {
					$(content).append('<li class="nc_wysiwyg_tableproperty_title"><dl><dt>' + title + '</dt><dd>' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') + property.cell.pos +'</dd></dl></li>');
					_appendTextAlign(type, content, property.cell.textAlign, property.cell.verticalAlign);
					$(content).append('<li><dl><dt style="float: left;">'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'width') +'</dt><dd class="nc_wysiwyg_tableproperty_title_v">'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_cell_width" name="cell_width" class="tableproperty_text align-right" value="'+ property.cell.width +'" /><select id="nc_wysiwyg_tableproperty_cell_width_unit" name="cell_width_unit"><option value="px" '+ ((property.cell.widthUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((property.cell.widthUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'height') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_cell_height" name="cell_height" class="tableproperty_text align-right" value="'+ property.cell.height +'" /><select id="nc_wysiwyg_tableproperty_cell_height_unit" name="cell_height_unit"><option value="px" '+ ((property.cell.heightUnit == 'px') ? 'selected="selected"' : '') +' >px</option><option value="%" '+ ((property.cell.heightUnit == '%') ? 'selected="selected"' : '') +' >%</option></select></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'cellpadding') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_cell_padding" name="cell_padding" class="tableproperty_text align-right" value="'+ property.cell.cellPadding +'"/>px</dd></dl></li>');
					$(content).append('<li class="nc_wysiwyg_tableproperty_linebreak clear"></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'bgcolor') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_cell_backgroundColor" name="cell_backgroundColor" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ property.cell.backgroundColor +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((property.cell.backgroundColor != '') ? ' style="background-color:' + property.cell.backgroundColor + '"' : '') + ' src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/sel_color.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" /></a></dd></dl></li>');
					$(content).append('<li><dl><dt>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'fontcolor') +'</dt><dd>'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'separator') +'<input type="text" id="nc_wysiwyg_tableproperty_cell_color" name="cell_color" class="nc_wysiwyg_tableproperty_color_input tableproperty_text" value="'+ property.cell.color +'" maxlength="7" /><a href="javascript:;" class="nc_wysiwyg_tableproperty_sel_color"><img' + ((property.cell.color != '') ? ' style="background-color:' + property.cell.color + '"' : '') + ' src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/sel_color.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'sel_color') + '" /></a></dd></dl></li>');
					$(content).append('<li><label for="nc_wysiwyg_tableproperty_cell_whiteSpace"><input type="checkbox" id="nc_wysiwyg_tableproperty_cell_whiteSpace" name="cell_whiteSpace" '+ ((property.cell.whiteSpace == 'nowrap') ? 'checked="checked"' : '') +' />'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'nowrap') +'</label></li>');
					_appendBorderBtn();
				}
				return content;

				function _appendBorderBtn() {
					var wysiwyg = options['wysiwyg'];
					$(content).append($('<li class="nc_wysiwyg_tableproperty_border"></li>')
							.append($('<input type="button" value="'+ __d(['nc_wysiwyg_tableproperty', 'panel'], 'border') +'" />')
								.click(function() {
									var callback = function() {
														var opts = {
															wysiwyg    : wysiwyg,
															active_tab : active_tab,
															table_el   : options.table_pos['table_el'],
															pos_title  : property[active_tab]['pos'],
															sel_els    : property[active_tab]['sel_els']
														}
														$("#nc_wysiwyg_tableborder").nc_tableborder(opts);
									}
									var toggle_opts = {
										id  : "nc_wysiwyg_tableborder",
										css : [$._base_url+'css/plugins/nc_wysiwyg/tableborder.css'],
										js : [$._base_url+'js/plugins/nc_wysiwyg/tableborder.js'],
										jsname : ['$.fn.nc_tableborder'],
										effect : 'basic',
										callback : callback
									};
									wysiwyg.toggleDialog(self, toggle_opts);
								})
							)
						);
				}

				function _appendTextAlign(type, content, textAlign, verticalAlign){
					var li = $('<li class="nc_wysiwyg_tableproperty_position"><fieldset><legend>'+__d(['nc_wysiwyg_tableproperty', 'panel'], 'text_align')+'</legend>' +
										'<ul class="tableproperty_align">'+
											'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_left' + ((textAlign == "left") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-left.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'text_align_left') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'text_align_left') + '" /></a></li>'+
											'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_center' + ((textAlign == "center") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-center.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'text_align_center') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'text_align_center') + '" /></a></li>'+
											'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_right' + ((textAlign == "right") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-right.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'text_align_right') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'text_align_right') + '" /></a></li>'+
										'</ul></fieldset>' +
										'<fieldset><legend>'+__d(['nc_wysiwyg_tableproperty', 'panel'], 'vertical_align')+'</legend>' +
										'<ul class="tableproperty_align">' +
											'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_top' + ((verticalAlign == "top") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-top.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'vertical_align_top') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'vertical_align_top') + '" /></a></li>'+
											'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_middle' + ((verticalAlign == "middle") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-middle.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'vertical_align_middle') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'vertical_align_middle') + '" /></a></li>'+
											'<li><a href="javascript:;" class="nc_wysiwyg_tableproperty_' + type + '_bottom' + ((verticalAlign == "bottom") ? ' nc_wysiwyg_tableproperty_active' : '') + '"><img src="'+ $._base_url +'img/plugins/nc_wysiwyg/dialog/table-bottom.gif" title="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'vertical_align_bottom') + '" alt="' + __d(['nc_wysiwyg_tableproperty', 'panel'], 'vertical_align_bottom') + '" /></a></li>'+
									'</ul></fieldset></li>');
					$(content).append(li);

					// Add Event
					$("a", li).each(function(k, v){
						$(v).click(function(e){
							var a = e.target;
							if(a.nodeName.toLowerCase() != 'a')
								a = a.parentNode;

							if($(a).hasClass("nc_wysiwyg_tableproperty_active"))
								$(a).removeClass("nc_wysiwyg_tableproperty_active");
							else {
								var u_align = a.parentNode.parentNode;
								$(".nc_wysiwyg_tableproperty_active", u_align).each(function(r_k, r_a){
									$(r_a).removeClass("nc_wysiwyg_tableproperty_active");
								});
								$(a).addClass("nc_wysiwyg_tableproperty_active");
							}
							return false;
						});
					});

				}
			}

		}
	}
})(jQuery);