/*
 * NC Table Menu 0.0.0.1
 * @param nc_wysiwyg object             : nc_wysiwyg object
 * @param options    hash
 *                      table_pos       : hash   　nc_wysiwygのgetSelectTablePosメソッドの返り値（詳しくはgetSelectTablePosメソッド参照）
 *                      html            : string   テンプレート文字列(html)
 */
;(function($) {
	$.fn.nc_tablemenu = function(wysiwyg, options) {
		var options = $.extend({
				table_pos      : null,
				html           :
	            	'<ul class="nc-wysiwyg-tablemenu">' +
            			'<li>' +
            				'<ul>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-property" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'table_property') +
		            				'</a>' +
		            			'</li>' +
		            		'</ul>' +
            			'</li>' +
            			'<li>' +
            				'<ul>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-cell-merge" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'cell_merge') +
		            				'</a>' +
		            			'</li>' +
		            		'</ul>' +
            			'</li>' +
            			'<li>' +
            				'<ul>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-row-partition" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'row_partition') +
		            				'</a>' +
		            			'</li>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-col-partition" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'col_partition') +
		            				'</a>' +
		            			'</li>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-cell-partition" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'cell_partition') +
		            				'</a>' +
		            			'</li>' +
		            		'</ul>' +
            			'</li>' +
            			'<li>' +
            				'<ul>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-row-before-insert" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'row_before_insert') +
		            				'</a>' +
		            			'</li>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-row-after-insert" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'row_after_insert') +
		            				'</a>' +
		            			'</li>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-row-delete" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'row_delete') +
		            				'</a>' +
		            			'</li>' +
		            		'</ul>' +
            			'</li>' +
            			'<li>' +
            				'<ul>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-col-before-insert" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'col_before_insert') +
		            				'</a>' +
		            			'</li>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-col-after-insert" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'col_after_insert') +
		            				'</a>' +
		            			'</li>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-col-delete" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'col_delete') +
		            				'</a>' +
		            			'</li>' +
		            		'</ul>' +
            			'</li>' +
            			'<li>' +
            				'<ul>' +
		            			'<li>' +
		            				'<a class="nc-wysiwyg-tablemenu-delete" href="#">' +
		            					__d('nc_wysiwyg_tablemenu', 'table_delete') +
		            				'</a>' +
		            			'</li>' +
		            		'</ul>' +
            			'</li>' +
            		'</ul>'
	        }, options);

		var self = this;
		var merge_dialog_flag = false;
		self.html(options.html);
		addEvent(wysiwyg, options);
		return;

		function addEvent(wysiwyg, options) {
			var td, table_pos = options.table_pos, menuClassName, sel;
			var colspan_flag = false, rowspan_flag = false;
			for(var i = 0; i < table_pos.cell_els.length; i++) {
				td = table_pos.cell_els[i];
				if(td.colSpan > 1)
					colspan_flag = true;
				if(td.rowSpan > 1)
					rowspan_flag = true;
			}
			$("a", self).each(function(k, v) {
				menuClassName = $(v).attr("class").replace(" active", "");
				switch(menuClassName) {
					case "nc-wysiwyg-tablemenu-property":
						/* 表プロパティ */
						$(v).addClass("active").click(function() {
							var callback = function() {
												var opts = {
													table_pos : table_pos,
													wysiwyg : wysiwyg
												}
												$("#nc-wysiwyg-tableproperty").nc_tableproperty(opts);
							};
							var options = {
								id  : "nc-wysiwyg-tableproperty",
								css : [$._base_url+'css/plugins/nc_wysiwyg/tableproperty.css'],
								js : [$._base_url+'js/plugins/nc_wysiwyg/tableproperty.js'],
								jsname : ['$.fn.nc_tableproperty'],
								callback : callback
							};
							wysiwyg.toggleDialog(self, options);
						});
						break
					case "nc-wysiwyg-tablemenu-cell-merge":
						/* セルのマージ */
						$(v).addClass("active").click(function(){
							var td, callback, options;
							if(table_pos.cell_els.length > 1) {
								_merge(table_pos);
							} else {
								// マージ用ダイアログ表示
								td = table_pos.cell_els[0];
								callback = function() {
													var opts = {
														td       : td,
														callback : function(row, col) {
															// マージ
															var buf_table_pos = table_pos;
															var tr = td.parentNode, buf_row = 0, buf_col = 0, rowIndex = tr.rowIndex, buf_tr;
															var td_cnt, span_num, span_key_arr = [], span_num_arr = [], buf_td_cnt;
															buf_table_pos['cell_els'] = [td];
															buf_table_pos['ranges'] = null;
															if((row <= 0 || col <= 0) && (row == 1 && col == 1) ) {
																return;
															}
															row--;
															col--;
															while(row != buf_row) {
																buf_row++;
																if(tr.cells[td.cellIndex + buf_row]) {
																	buf_table_pos['cell_els'].push(tr.cells[td.cellIndex + buf_row]);
																}
															}
															td_cnt = 0;
															////span_num = 0;
															for(var k = 0; k < tr.childNodes.length; k++) {
																td_cnt += tr.childNodes[k].colSpan;
																if( td != tr.childNodes[k] && tr.childNodes[k].rowSpan > 1 ) {
																	//span_num+=tr.childNodes[k].colSpan;
																	span_key_arr.push(tr.childNodes[k].rowSpan - 1);
																	span_num_arr.push(tr.childNodes[k].colSpan);
																}
																if(td == tr.childNodes[k])
																	break;
															}

															while(col != buf_col) {
																buf_col++;
																if(buf_table_pos.table_el.rows[rowIndex + buf_col]) {
																	// 該当行
																	buf_tr = buf_table_pos.table_el.rows[rowIndex + buf_col];
																	span_num = 0;
																	for (var j=0; j< span_key_arr.length; j++) {
																		if(span_key_arr[j] != 0) {
																			span_key_arr[j]--;
																			span_num += span_num_arr[j];
																		}
																	}

																	buf_td_cnt = span_num;
																	for(var k = 0; k < buf_tr.childNodes.length; k++) {
																		buf_td_cnt += buf_tr.childNodes[k].colSpan;
																		if(buf_td_cnt == td_cnt) {
																			// マージ対象
																			//buf_table_pos['cell_els'].push(buf_tr.childNodes[k]);
																			//row追加
																			buf_row = row;
																			for(var j = buf_tr.childNodes[k].cellIndex; j < buf_tr.childNodes.length; j++) {
																				if(buf_row >= 0) {
																					buf_table_pos['cell_els'].push(buf_tr.childNodes[j]);
																				}
																				buf_row--;
																			}
																			break;
																		}
																		if( buf_tr.childNodes[k].rowSpan > 1 ) {
																			span_key_arr.push(buf_tr.childNodes[k].rowSpan - 1);
																			span_num_arr.push(buf_tr.childNodes[k].colSpan);
																		}
																	}
																}
															}
															_merge(buf_table_pos);
															wysiwyg.removeDialog("nc-wysiwyg-tablemerge");
															wysiwyg.focus(true);
															wysiwyg.rangeSelect(td);
															wysiwyg.checkTargets();
															return true;
														},
														cancel_callback : function() {
															wysiwyg.removeDialog("nc-wysiwyg-tablemerge");
															wysiwyg.focus(true);
															wysiwyg.rangeSelect(td);
															wysiwyg.checkTargets();
															return true;
														}
													};
													$("#nc-wysiwyg-tablemerge").nc_tablemerge(opts);
								}
								options = {
									id  : "nc-wysiwyg-tablemerge",
									css : [$._base_url+'css/plugins/nc_wysiwyg/tablemerge.css'],
									js : [$._base_url+'js/plugins/nc_wysiwyg/tablemerge.js'],
									jsname : ['$.fn.nc_tablemerge'],
									callback : callback
								};
								merge_dialog_flag = true;
								wysiwyg.toggleDialog(self, options);
							}

						});
						break;
					case "nc-wysiwyg-tablemenu-row-partition":
						if(rowspan_flag)
							$(v).addClass("active").click(function(){
								_rowPartition();
							});
						break;
					case "nc-wysiwyg-tablemenu-col-partition":
						if(colspan_flag)
							$(v).addClass("active").click(function(){
								_colPartition();
							});
						break;
					case "nc-wysiwyg-tablemenu-cell-partition":
						if(colspan_flag || rowspan_flag)
							$(v).addClass("active").click(function(){
								// 行分割＋列分割
								_rowPartition();
								_colPartition();
							});
						break;
					case "nc-wysiwyg-tablemenu-row-before-insert":
					case "nc-wysiwyg-tablemenu-row-after-insert":
						// 選択行の前後に挿入
						$(v).addClass("active").click(function(e){
							var rows = table_pos.table_el.rows, clone_el;
							var rowIndex = table_pos.cell_els[0].parentNode.rowIndex;
							clone_el = table_pos.cell_els[0].parentNode.cloneNode(true);
							for (var i=0; i<rows.length; i++) {
								for (var j=0; j<rows[i].cells.length; j++) {
									if($(rows[i].cells[j]).attr("rowSpan") > 1 &&
										i + $(rows[i].cells[j]).attr("rowSpan") - 1  >= rowIndex) {
										if(rows[i].rowIndex != rowIndex || $(e.target).hasClass("nc-wysiwyg-tablemenu-row-after-insert")) {
											$(rows[i].cells[j]).attr("rowSpan", $(rows[i].cells[j]).attr("rowSpan") + 1);
										}
										if(rows[i].rowIndex == rowIndex && $(e.target).hasClass("nc-wysiwyg-tablemenu-row-after-insert")) {
											$(clone_el.cells[j]).remove();
										}
									}
								}
							}

							$("td", clone_el).each(function(k, v){
								$(v).html("&nbsp;");
								if($(v).css('height').match(/%$/))
									$(v).css({height:''});
								v.removeAttribute("rowSpan", 0);
							});

							if($(e.target).hasClass("nc-wysiwyg-tablemenu-row-before-insert"))
								$(table_pos.cell_els[0].parentNode).before(clone_el);
							else
								$(table_pos.cell_els[0].parentNode).after(clone_el);

							sel = wysiwyg.getSelection();
							if (sel && !$.browser.msie) {
								sel.removeAllRanges();
								if(table_pos.ranges != null)
									sel.addRange(table_pos.ranges[0]);
							}
						});
						break;
					case "nc-wysiwyg-tablemenu-row-delete":
						$(v).addClass("active").click(function(){
							var tr;
							for(var i = 0; i < table_pos.cell_els.length; i++) {
								if(table_pos.cell_els[i].parentNode) {
									$(table_pos.cell_els[i].parentNode).remove();
								}
							}
							tr = $("tr", table_pos.table_el);
							if(!tr || !tr[0])
								$(table_pos.table_el).remove();
						});
						break;
					case "nc-wysiwyg-tablemenu-col-before-insert":
					case "nc-wysiwyg-tablemenu-col-after-insert":
						// 選択列の前後に挿入
						$(v).addClass("active").click(function(e){
							var current_colspan = 1,insert_index, otd, c_td, attr;
							var rows = table_pos.table_el.rows, colspan, tr = table_pos.cell_els[0].parentNode;
							// 何列目に挿入するか検索
							for (var i=0; i<tr.cells.length; i++) {
								if(tr.cells[i] == table_pos.cell_els[0])
									break;
								current_colspan += $(tr.cells[i]).attr("colSpan");
							}
							for (var i=0; i<rows.length; i++) {
								colspan = 0;
								for (var j=0; j<rows[i].cells.length; j++) {
									colspan += $(rows[i].cells[j]).attr("colSpan");
									if(colspan >= current_colspan || j == rows[i].cells.length - 1) {
										// 挿入
										//style = $(rows[i].cells[j]).attr("style");
										if(colspan < current_colspan && j == rows[i].cells.length - 1)
											insert_index = rows[i].cells[j].cellIndex + 1;
										else if($(e.target).hasClass("nc-wysiwyg-tablemenu-col-before-insert"))
											insert_index = rows[i].cells[j].cellIndex;
										else
											insert_index = rows[i].cells[j].cellIndex + 1;
										attrs = rows[i].cells[j].attributes;
										c_td = rows[i].cells[j];
										otd = rows[i].insertCell(insert_index);
										$(otd).html("&nbsp;");
										for (var k = 0; k < attrs.length; ++k) {
											a = attrs.item(k);
											if (!a.specified) {
												continue;
											}
											name = a.nodeName.toLowerCase();
											value = _getNodeValue(c_td, name, a);
											if(name == "colspan" || name == "rowspan")
												continue;
											$(otd).attr(name,value);
										}
										if($(otd).css('width').match(/%$/))
											$(otd).css({width:''});
										break;
									}
								}
							}
							sel = wysiwyg.getSelection();
							if (sel && !$.browser.msie) {
								sel.removeAllRanges();
								if(table_pos.ranges != null)
									sel.addRange(table_pos.ranges[0]);
							}
						});
						break;
					case "nc-wysiwyg-tablemenu-col-delete":
						$(v).addClass("active").click(function(e){
							var rows = table_pos.table_el.rows, buf_colspan, buf_i = null;
							var current_colspan = 1, tr = table_pos.cell_els[0].parentNode;

							// 何列目を削除するか検索
							for (var i=0; i<tr.cells.length; i++) {
								if(tr.cells[i] == table_pos.cell_els[0])
									break;
								current_colspan += $(tr.cells[i]).attr("colSpan");
							}
							for (var i=0; i<rows.length; i++) {
								colspan = 0;
								if(buf_i) {
									i = buf_i;
									if(!rows[i])
										break;
								}
								for (var j=0; j<rows[i].cells.length; j++) {
									colspan += $(rows[i].cells[j]).attr("colSpan");
									if(colspan >= current_colspan) {
										if($(rows[i].cells[j]).attr("colSpan") == 1) {
											if($(rows[i].cells[j]).attr("rowSpan") >= 2) {
												buf_i = i + $(rows[i].cells[j]).attr("rowSpan");
											}
											$(rows[i].cells[j]).remove();
											if(!rows[i].cells[0]) {
												$(rows[i]).remove();
												i--;
											}
										} else {
											buf_colspan = $(rows[i].cells[j]).attr("colSpan") - 1;
											if(buf_colspan == 1)
												rows[i].cells[j].removeAttribute("colSpan", 0);
											else
												$(rows[i].cells[j]).attr("colSpan", buf_colspan);

										}
										break;
									}
								}
							}
							// 空のtable削除
							rows = table_pos.table_el.rows;
							if(!rows[0])
								$(table_pos.table_el).remove();
						});
						break;
					case "nc-wysiwyg-tablemenu-delete":
						$(v).addClass("active").click(function(){
							$(table_pos.table_el).remove();
						});
						break;
				}
				$(v).click(function(e){
					if($(e.target).hasClass("active")) {
						wysiwyg.removeDialog(wysiwyg.dialog_id);
						if(!merge_dialog_flag) {
							wysiwyg.addUndo();
							wysiwyg.focus(true);
							wysiwyg.checkTargets();
						}
					}
					e.preventDefault();
					return false;
				});

				function _rowPartition() {
					var rowspan, tr, clone_el, buf_push_clone = [];
					for(var i = 0; i < table_pos.cell_els.length; i++) {
						rowspan = $(table_pos.cell_els[i]).attr("rowSpan");
						if(rowspan > 1) {
							table_pos.cell_els[i].removeAttribute("rowSpan", 0);
							tr = table_pos.cell_els[i].parentNode;
							for(var j = 0; j < rowspan - 1; j++) {
								tr = tr.nextSibling;
								if(tr) {
									if($(table_pos.cell_els[i]).css('height').match(/%$/)) {
										$(table_pos.cell_els[i]).css("height", (parseInt($(table_pos.cell_els[i]).css('height'))/rowspan) + "%");
									} else if(table_pos.cell_els[i].style.height.match(/px$/)) {
										$(table_pos.cell_els[i]).css("height", (parseInt($(table_pos.cell_els[i]).css('height'))/rowspan) + "px");
									}
									clone_el = table_pos.cell_els[i].cloneNode(false);
									clone_el.innerHTML ='&nbsp;';
									if(tr.cells[table_pos.cell_els[i].cellIndex])
										$(tr.cells[table_pos.cell_els[i].cellIndex]).before(clone_el);
									else
										$(tr).append(clone_el);
									buf_push_clone.push(clone_el);
								}
							}
							// 選択セル追加
							for(var j = 0; j < buf_push_clone.length; j++) {
								table_pos.cell_els.push(buf_push_clone[j]);
							}
						}
					}
				}

				function _colPartition() {
					var colspan, clone_el;
					for(var i = 0; i < table_pos.cell_els.length; i++) {
						colspan = $(table_pos.cell_els[i]).attr("colSpan");
						if(colspan > 1) {
							table_pos.cell_els[i].removeAttribute("colSpan", 0);
							if($(table_pos.cell_els[i]).css('width').match(/%$/)) {
								$(table_pos.cell_els[i]).css("width", (parseInt($(table_pos.cell_els[i]).css('width'))/colspan) + "%");
							} else if(table_pos.cell_els[i].style.width.match(/px$/)) {
								$(table_pos.cell_els[i]).css("width", (parseInt($(table_pos.cell_els[i]).css('width'))/colspan) + "px");
							}
							for(var j = 0; j < colspan - 1; j++) {
								clone_el = table_pos.cell_els[i].cloneNode(false);
								clone_el.innerHTML ='&nbsp;';
								$(table_pos.cell_els[i]).after(clone_el);
							}
						}
					}
				}

				function _merge(table_pos) {
					var td, buf_td, buf_cell_els = [], buf_ranges = [], ranges = [], sel;

					_mergeRow();
					_mergeCol();
					_mergeRow();	// 再マージ

					// マージ後、row_spanを再調整
					_reRowControl(table_pos);

					if(table_pos.sel_name == "table" && table_pos.table_el.rows.length == 1
						 && table_pos.table_el.rows[0].cells.length == 1) {
						// removeAttrは、IEでエラーとなる
						table_pos.table_el.rows[0].cells[0].removeAttribute("colSpan", 0);
						table_pos.table_el.rows[0].cells[0].removeAttribute("rowSpan", 0);
					}

					sel = wysiwyg.getSelection();
					if (sel && !$.browser.msie) {
						sel.removeAllRanges();
						for(var i = 0; i < ranges.length; i++)
							sel.addRange(ranges[i]);
					}

					function _mergeRow() {
						var cells_cnt = _getColCount(table_pos.cell_els);
						var buf_td_cnt, merge_lists = [], buf_merge_lists = [];
						for(var i = 0; i < table_pos.cell_els.length; i++) {
							var td = table_pos.cell_els[i];
							if(buf_td &&  td.parentNode && buf_td.parentNode &&
								td.parentNode.rowIndex == buf_td.parentNode.rowIndex &&
								 buf_td_cnt + 1 == cells_cnt[i] - (td.colSpan - 1) &&
								 td.rowSpan == buf_td.rowSpan) {
								for(var j = 0; j < merge_lists.length; j++) {
									if(merge_lists[j] == buf_td) {
										buf_td = buf_merge_lists[j];
										break;
									}
								}
								buf_merge_lists.push(buf_td);
								merge_lists.push(td);
								//_mergeCell("col", buf_td, td);
							}
							//else {
								var buf_td = td;
								var buf_td_cnt = cells_cnt[i];
								buf_cell_els.push(td);
								if(table_pos.ranges != null)
									buf_ranges.push(table_pos.ranges[i]);
							//}
						}
						for(var i = 0; i < buf_merge_lists.length; i++) {
							_mergeCell("col", buf_merge_lists[i], merge_lists[i]);
						}
					}

					function _mergeCol() {
						var cells_cnt = _getColCount(buf_cell_els);
						for(var i = 0; i < buf_cell_els.length; i++) {
							td = buf_cell_els[i];
							for(var j = 0; j < buf_cell_els.length; j++) {
								buf_td = buf_cell_els[j];
								if(td != buf_td && td.parentNode && buf_td.parentNode &&
										td.parentNode.rowIndex + td.rowSpan == buf_td.parentNode.rowIndex &&
									buf_td.colSpan == td.colSpan) {
									// 列の位置が同じかどうか
									if(cells_cnt[i] == cells_cnt[j]) {
										// マージ対象
										_mergeCell("row", td, buf_td);
									} else if(buf_ranges[j]) {
										ranges.push(buf_ranges[j]);
									}
								} else if(buf_ranges[j]) {
									ranges.push(buf_ranges[j]);
								}
							}
						}
					}

					function _mergeCell(type, td, merge_td) {
						var tr = merge_td.parentNode;
						var td_html = td.innerHTML.replace(/(<br[ ]+\/>[\s\n]*)*$/, '').replace(/&nbsp;/, '');
						var buf_merge_td_html = merge_td.innerHTML.replace(/(<br[ ]+\/>[\s\n]*)*$/, '').replace(/&nbsp;/, '');
						if(type == "col") {
							if($(merge_td).css('width').match(/%$/) && $(td).css('width').match(/%$/))
								$(td).css("width", (parseInt($(merge_td).css('width')) + parseInt($(merge_td).css('width'))) + "%");
							else if(merge_td.style.width.match(/px$/) && td.style.width.match(/px$/))
								$(td).css("width", (parseInt($(merge_td).css('width')) + parseInt($(merge_td).css('width'))) + "px");

							td.colSpan = merge_td.colSpan + td.colSpan;
						} else {
							if($(merge_td).css('height').match(/%$/) && $(td).css('height').match(/%$/))
								$(td).css("height", (parseInt($(merge_td).css('height')) + parseInt($(merge_td).css('height'))) + "%");
							else if(merge_td.style.height.match(/px$/) && td.style.height.match(/px$/))
								$(td).css("height", (parseInt($(merge_td).css('height')) + parseInt($(merge_td).css('height'))) + "px");
							td.rowSpan = merge_td.rowSpan + td.rowSpan;
						}

						if(buf_merge_td_html != '') {
							if(td_html != '')
								td.innerHTML = td.innerHTML + buf_merge_td_html;
							else
								td.innerHTML = merge_td.innerHTML;
						}
						$(merge_td).remove();
						if(type == "row" && td.parentNode.cells.length == 1) {
							td.removeAttribute("rowSpan", 0);
						}
						// trが空ならば削除
						if(tr.innerHTML == '')
							$(tr).remove();
					}

					// 同tr内ですべてのrowspanが同じかどうかチェックし
					// 同じならばrowspanを削除
					// 同tr内ですべてのrowspanが2以上ならば、その数に応じて減算
					function _rowSpanChk(tr, rowSpan) {
						var eq_flag = true, minRowSpan, buf_tr, bufRowSpan;
						for (var i=0; i < tr.cells.length; i++) {
							if(tr.cells[i].rowSpan != rowSpan)
								eq_flag = false;
							if(!minRowSpan || minRowSpan > tr.cells[i].rowSpan)
								minRowSpan = tr.cells[i].rowSpan;
						}
						if(eq_flag) {
							for (var i=0; i < tr.cells.length; i++) {
								tr.cells[i].removeAttribute("rowSpan", 0);
							}
						} else if(minRowSpan > 1) {
							for (var i=0; i < tr.cells.length; i++) {
								tr.cells[i].rowSpan = tr.cells[i].rowSpan - (minRowSpan - 1);
							}
						}
						bufRowSpan = 1;
						while(minRowSpan > 1) {
							// rowを遡り、rowSpanが現Rowに至るものをrow--
							minRowSpan--;
							bufRowSpan++;
							if(tr.rowIndex - minRowSpan >= 0 && table_pos.table_el.rows[tr.rowIndex - minRowSpan]) {
								buf_tr = table_pos.table_el.rows[tr.rowIndex - minRowSpan];
								for (var i=0; i < buf_tr.cells.length; i++) {
									if(buf_tr.cells[i].rowSpan >= bufRowSpan) {
										if( buf_tr.cells[i].rowSpan == 2 )
											buf_tr.cells[i].removeAttribute("rowSpan", 0);
										else
											buf_tr.cells[i].rowSpan = buf_tr.cells[i].rowSpan - 1;
									}
								}
							}
						}
					}

					// マージ後、row_spanを再調整
					function _reRowControl(table_pos) {
						/*var tr = $("tr", table_pos.table_el);
						tr.each(function(k, v){
							_rowSpanChk(v, v.cells[0].rowSpan);
						});*/
						var td = $("td", table_pos.table_el);
						td.each(function(k, v){
							var tr = v.parentNode, rowIndex= tr.rowIndex, rowSpan = v.rowSpan;
							while(rowSpan > 1) {
								if(!table_pos.table_el.rows[rowIndex + (rowSpan - 1)]) {
									rowSpan--;
									if( rowSpan == 1 )
										v.removeAttribute("rowSpan", 0);
									else
										v.rowSpan = rowSpan;

									$("td", tr).each(function(sub_k, sub_v){
										if(v != sub_v && sub_v.rowSpan > 1) {
											if( sub_v.rowSpan == 2 )
												sub_v.removeAttribute("rowSpan", 0);
											else
												sub_v.rowSpan = sub_v.rowSpan - 1;
										}
									});
									_rowSpanChk(tr, rowSpan);
								} else {
									_rowSpanChk(tr, rowSpan);
									break;
								}
							}
						});
					}

			        // 何列目かを求める
			        function _getColCount(cell_els) {
			        	var ret_cell_cnt = {};
			        	var span_key_arr = [], span_num_arr = [], span_cnt_arr = [], span_tr_arr = [], rowIndex = 0;
						var buf_tr = options.table_pos.table_el.rows[0];
						while(typeof buf_tr != "undefined") {
							var buf_td_cnt = 0;
							var use_td_arr = {};
							for(var i = 0; i < buf_tr.childNodes.length; i++) {
								buf_td_cnt += buf_tr.childNodes[i].colSpan;
								for (var k=0; k< span_key_arr.length; k++) {
									if(!use_td_arr[k] && span_key_arr[k] != 0 && buf_td_cnt == span_cnt_arr[k]
										&& span_tr_arr[k] != buf_tr) {
										//span_key_arr[k]--;
										use_td_arr[k] = true;
										buf_td_cnt += span_num_arr[k];
									}
								}
								for(var j = 0; j < cell_els.length; j++) {
									if(buf_tr.childNodes[i] == cell_els[j]) {
										ret_cell_cnt[j] = buf_td_cnt;
										break;
									}
								}

								if( buf_tr.childNodes[i].rowSpan > 1 ) {
									span_key_arr.push(buf_tr.childNodes[i].rowSpan );
									span_num_arr.push(buf_tr.childNodes[i].colSpan);
									span_cnt_arr.push(buf_td_cnt);
									span_tr_arr.push(buf_tr);
								}
							}
							rowIndex++;
							buf_tr = options.table_pos.table_el.rows[rowIndex];
							for (var k=0; k< span_key_arr.length; k++) {
								if(span_key_arr[k] != 0) {
									span_key_arr[k]--;
									//use_td_arr[k] = true;
									//buf_td_cnt += span_num_arr[k];
								}
							}
						}
						return ret_cell_cnt;
			        }
				}

				function _getNodeValue(node, name, a) {
		        	if (name != "style") {
						// ブラウザによっては、height等の属性は、本来入力していないものを自動的に指定されてしまう可能性があるため、
						// a.nodeValueを用いる
						if (typeof node[a.nodeName] != "undefined" && name != "height"  && name != "width"  && name != "href" && name != "src" && !/^on/.test(name)) {
							value = node[a.nodeName];
						} else {
							value = a.nodeValue;
						}
					} else {
						value = node.style.cssText;
					}
					return value;
		        }
			});
		}
	}
})(jQuery);