/**
 * ページ - セッティングモードON js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       webroot.js.main
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$(function(){
	//$(document).ready(function(){
		$(".nc-add-block").select2({
			placeholderOption: 'first',
			width: 'element'
		}).change( function(e){
			var module_id = $(this).val();
			if(module_id > 0) {
				$.PagesBlock.addBlock($(this), module_id);
				$(this).select2("val", "");
			}
		} );
		$.PagesBlock.showOperationBlock($(".nc-copy-block"));
	});
	$(document).click(function(event){
		var t = $.PagesBlock, target = $(event.target);
		if(target.get(0).tagName && target.get(0).tagName.toLowerCase() == "html") return;

		if(!target.hasClass("block_move")) {
			t.cancelSelects();
		}

		t.toggleGroup();
	});

	$.PagesBlock ={
		blockDummy : null,
		currentBlocks : new Array(),
		columnsPos : new Object(),
		columnPos : new Object(),
		blocksPos : new Object(),
		insert : null,
		insertAction : null,

		groups : new Object(),

		initBlock: function(id) {
			var t = this;
			var block = $('#' + id),block_move = $("#nc-block-move" + id);
			var block_move_desc= null;

			// ブロックコピー
			$('#nc-block-header-copy' + id).on('ajax:success', function(e, res) {
				var re_html = new RegExp("<div class=\"nc-copy-block-outer\"", 'i');
				$(this).parents('.nc-drop-down:first').hide();
				if(!$.trim(res).match(re_html)) {
					// error
					$.Common.showErrorDialog(res, null, e.target);
					return;
				}
				var add_block_ids = ['nc-add-block-headercolumn','nc-add-block-leftcolumn','nc-add-block-centercolumn',
									'nc-add-block-rightcolumn','nc-add-block-footercolumn'];
				$(add_block_ids).each(function() {
					var add_btn = $('#'+ this);
					if(add_btn.get(0)) {
						var res_target = $(res);
						var prev = add_btn.prev();
						if(prev.get(0) && prev.hasClass('nc-copy-block-outer')) {
							prev.remove();
						}
						add_btn.hide().before(res_target);

						$.PagesBlock.showOperationBlock(res_target.children('select:first'));
					}
				});
			});

			// ブロックスタイル

			$('#nc-block-styles-link' + id +',#nc-block-display-styles-link' + id+',#nc-block-contents-list-link' + id).on('ajax:beforeSend', function(e, url) {
				var a = $(e.target), dialog_id, is_content_list = false;
				if($(this).attr('id') == 'nc-block-styles-link' + id) {
					dialog_id = a.attr('data-block-styles-dialog-id');
				} else if($(this).attr('id') == 'nc-block-display-styles-link' + id) {
					dialog_id = a.attr('data-block-display-styles-dialog-id');
				} else {
					dialog_id = a.attr('data-block-contents-list-dialog-id');
					is_content_list = true;
				}
				var style_dialog = $('#' + dialog_id);
				if(style_dialog.get(0)) {
					// 既に表示中
					if(is_content_list) {
						style_dialog.remove();
					} else {
						style_dialog.dialog('open');
						$(this).parents('.nc-drop-down:first').hide();
						return false;
					}
				}
				return url;
			}).on('ajax:success', function(e, res) {
				var title ='',w,h;
				if($(this).attr('id') == 'nc-block-styles-link' + id) {
					block_style =true;
				}
				var a = $(e.target), pos = a.offset(), style_dialog, dialog_id;
				if($(this).attr('id') == 'nc-block-styles-link' + id) {
					dialog_id = a.attr('data-block-styles-dialog-id');
					title = 'Block style';
				} else if($(this).attr('id') == 'nc-block-display-styles-link' + id) {
					dialog_id = a.attr('data-block-display-styles-dialog-id');
					title = 'Display style';
				} else {
					dialog_id = a.attr('data-block-contents-list-dialog-id');
					title = 'Content list'
				}
				var params = {
					title: __d('block', title),
					position: [e.pageX - $(window).scrollLeft(), e.pageY - $(window).scrollTop()],
					show: 'blind',
					hide: 'blind'
				};
				if(title == 'Block style') {
					params['width'] = 400;
					params['resizable'] = false;
				}
				style_dialog = $('<div id="' + dialog_id + '" class="nc-block-styles-dialog"></div>').html(res);
				w = style_dialog.children(':first').attr('data-width');
				h = style_dialog.children(':first').attr('data-height');
				if(parseInt(w) > 0) {
					params['width'] = parseInt(w);
					//style_dialog.dialog('option','width',parseInt(w));
				}
				if(parseInt(h) > 0) {
					params['height'] = parseInt(h);
					//style_dialog.dialog('option','height',parseInt(h));
				}
				style_dialog.dialog(params);
				$(this).parents('.nc-drop-down:first').hide();
			});

			if(block_move.get(0) && $('#parent-container').get(0)) {
				block_move.unbind('click').click(function(e){
					if (e.target.tagName.toUpperCase() === 'A') {
						return;
					}
					var blockChild = block.children('.nc-frame:first');
					// グルーピング
					if(!blockChild.hasClass('nc-select-group')) {
						blockChild.addClass('nc-select-group');
						t.cancelSelects(block);
						t.groups[id] = block;
					} else {
						blockChild.removeClass('nc-select-group');
						delete t.groups[id];
					}
					t.toggleGroup();
					//$.Event(e).preventDefault();
					$.Event(e).stopPropagation();
				});
				block.draggable({
					//opacity  : 0.8,    //ドラッグ時の不透明度
					helper : 'clone',
					distance:5,
					handle : block_move,
					scroll : true,
					revert : true,
					zIndex:++$.Common.blockZIndex,
					start:function(event, ui){
						t.cancelSelects();
						t.currentBlocks = new Array();
						t.columnsPos = new Object();
						t.columnPos = new Object();
						t.blocksPos = new Object();

						var blockChild = block.children('.nc-frame:first');
						blockChild.css("opacity", 0.3);
						var columns = block.parents('[data-columns=top]:first');
						t._setPos(t.columnsPos, columns);

						var childBlocks = new Object();
						if(block.hasClass('nc-group')) {
							//childBlocks[block.attr('id')] = true;
							var els = $('[data-block]', block);
							for (var i = 0,len = els.length; i < len; i++) {
								childBlocks[$(els[i]).attr('id')] = true;
							}
						}
						t.setSearchBlock(columns, 0, 0, childBlocks);

						// 移動元ブロックと、移動元の上下のブロックを保持
						t.currentBlocks[0] = block;
						t.currentBlocks[1] = block.prev();
						t.currentBlocks[2] = block.next();
						if(t.currentBlocks[2].get(0) && !t.currentBlocks[2].attr('id')) {
							// clone element
							t.currentBlocks[2] = t.currentBlocks[2].next();
						}
						// 列に１つしかブロックがない場合、列も保持
						if(!t.currentBlocks[1].get(0) && !t.currentBlocks[2].get(0)) {
							t.currentBlocks[3] = block.parent();
						}
						var div = $('<div></div>').addClass('nc-block-dummy');
						block.before(div);
						//block[0].parentNode.insertBefore(div[0], block[0]);
						t.blockDummy = div;

						t._cloneStyle();
						// border
						div.css({
							width: blockChild.outerWidth()  + "px",
							height: blockChild.outerHeight() + "px",
							marginTop:block.css("marginTop"),
							marginLeft:block.css("marginLeft"),
							marginBottom:block.css("marginBottom"),
							marginRight:block.css("marginRight")
						});
						ui.helper.css({
							width: blockChild.outerWidth()  + "px",
							height: blockChild.outerHeight() + "px"
						});

						block_move_desc = $('<div></div>').addClass('nc-block-move-desc');
						$(ui.helper).append(block_move_desc);
					},
					drag:function(event, ui){
						//t.searchInsertBlock(t.columnsPos, [event.pageX, event.pageY], 0, 0);
						t.searchInsertBlock(t.columnsPos, [event.pageX, event.pageY], 0, 0, block_move_desc);
					},
					revert: function(socketObj) {
						block_move_desc.remove();
						if(t.currentBlocks[0].hasClass('nc-block-dummy')) {
					        // revert
							var column = block.parent();
							t.blockDummy.remove();
							t.blockDummy = null;
							return true;
					    } else {
					        // don't revert.
					        return false;
					    }
					},
					stop:function(event, ui){
						var block = $(this);
						var ret = t.currentBlocks[0].hasClass('nc-block-dummy');
						block.children('.nc-frame:first').css("opacity", 1);
						t.currentBlocks[0].removeClass('nc-block-dummy');
						if(ret) {
							return;
						}

						var offset = block.offset();
						var top_offset = parseInt(offset.top, 10);
						var left_offset = parseInt(offset.left, 10);
						var params = new Object();

/* TODO:他カラムへの移動
						if(t.insertAction == "insert_column") {
							// 他カラムへの移動
							var input_page_id = $('#' + t.insert.attr('id') + '_main_page_id');
							var dummy_td = t.block_dummy.parent();
							var insert_tr = $(".tr_columns:first", t.insert);
							var insert_td = insert_tr.children(":first");
							var div_content = insert_td.children(":first");
							insert_page_id = input_page_id.val();
							current_input_page_id.val(insert_page_id);	// input hiddenにSet
							insert_show_count = t.getShowCount(block.attr("id"), insert_page_id);
							if(div_content.get(0)) {
								div_content[0].parentNode.insertBefore(t.block_dummy[0], div_content[0]);
							} else {
								insert_td.append(t.block_dummy);
							}
							if($.trim(dummy_td.html()) == '' && dummy_td.parent().get(0).cells.length > 1) {
								dummy_td.remove();
							}

							t.insertAction = "insert_row";
						}
*/

						var dur = Math.sqrt((Math.abs(top_offset^2)+Math.abs(left_offset^2))*0.015)*100;
						block.css("position", "absolute").css("zIndex", $.Common.blockZIndex);
						block.animate(t.blockDummy.offset(), dur, function() {
							block.css("position", "").css("zIndex", '');
							$(ui.helper).remove();
							//block.css("left", "").css("top", "");
							//var parent = block.parent();
							// 空のグルーピングボックス削除
							var target_remove = t._removeGroupBlock(block);

							var column = block.parent();
							// t.blockDummy.before(block); //scriptがなくなる
							t.blockDummy[0].parentNode.insertBefore(block[0], t.blockDummy[0]);

							if(!column.children(':first').get(0)) {
								column.remove();
							}

							t.blockDummy.remove();
							t.blockDummy = null;
							//$(ui.helper).remove();
							if(target_remove) {
								var parent = target_remove.parent();
								target_remove.remove();
								if(!parent.children(':first').get(0)) {
									parent.remove();
								}
							}

							// リクエスト処理
							var block_id = block.attr('data-block');
							var page_id = block.attr('data-page');
							params['page_id'] = page_id;
							params['show_count'] = $._nc.show_count[page_id];
							params['col_num'] = 1;
							params['row_num'] = 1;
							params['parent_id'] = 0;

							var parent = block.parent();
							var parent_columns = parent.parent();
							if(t.insertAction == "insert_row") {
								$.each(parent.children('[data-block]'), function() {
									if(block.get(0) == this) {
										return false;
									}
									params['row_num'] += 1;
								});
							}
							$.each(parent_columns.children('.nc-column'), function() {
								if(parent.get(0) == this) {
									return false;
								}
								params['col_num'] += 1;
							});

							var parent_group = parent.parents(".nc-group:first");
							if(parent_group.get(0)) {
								params['parent_id'] = parent_group.attr('data-block');
							}

							$.post($.Common.urlBlock(block_id, 'block/' + t.insertAction),
								params,
								function(res){
									$._nc.show_count[page_id]++;
								}
							);

						});
					}
				});
			}

			// リサイズ処理
			var block_frame = block.children('.nc-frame:first');
			var show_size = $('#nc-block-show-size');
			block_frame.resizable({
				zIndex:1,
				resize : function(e, ui) {
					show_size.css("top", e.pageY  + "px")
						.css("left", e.pageX  + "px")
						.html(ui['size']['width'] + __d('pages', 'x') + ui['size']['height'])
						.css("zIndex", $.Common.blockZIndex + 1)
						.removeClass("display-none");
				},
				stop : function(e, ui) {
					show_size.addClass("display-none");
					var params = new Object();
					params['is_resize'] = 1;
					if(ui['size']['width'] != ui['originalSize']['width']) {
						params['min_width_size'] = ui['size']['width'];
					}
					if(ui['size']['height'] != ui['originalSize']['height']) {
						params['min_height_size'] = ui['size']['height'];
					}
					if(!params['min_width_size'] && !params['min_height_size']) {
						// 変更なし
						return;
					}
					$.post($('#nc-block-styles-link' + id).attr('href'),
						params
					);
				}
			});
			block_frame.children('.ui-resizable-se:last-child').addClass('nc-block-mode');
		},
		//
		//移動挿入先検索
		//
		searchInsertBlock: function(columnsPos, pointer, now_thread_num, now_parent_id, block_move_desc) {
			var t = this, insert_columns, insert_column, el_left, el_right, block, position;
			var x = pointer[0],y = pointer[1], insert, direction, force, next_parent_id;

			var ex1, ex2, ey1, ey2, direction, offset, index, parent, buf_insert_column;
			var is_x, is_y, buf_is_x,prev_column, cell_index;

/*TODO:他カラムへの移動
			var column = false , re_cut_move = new RegExp("_move$", "i");

			if(now_thread_num == 0) {
				$(t.column_ids).each(function(k, v) {
					move_column = $('#'+v + '_main_move');
					if(move_column.get(0)) {
						move_column.removeClass('nc_move_space_hover');
						if($.Common.within(move_column, x, y)) {
							// 他カラムへ移動
							column = $('#' + v.replace(re_cut_move,""));
							return false;
						}
					}
				});
				if(column !== false) {
					InsertOtherColumn(column);
					move_column.addClass('nc_move_space_hover');
					return;
				}
			}
*/

			if(columnsPos['el'].hasClass("nc-group"))
				insert_columns = columnsPos["group_el"];
			else
				insert_columns = columnsPos["el"];

			if(t.columnPos[now_parent_id] &&
					x >= columnsPos['left'] &&
		            x <  columnsPos['right']) {
				// x軸がtopカラム内部
				//insert = null;
				for (var i = 0,col_len = t.columnPos[now_parent_id].length; i < col_len; i++) {
					//column
					//最も近いcolumnエレメントを取得しておく
					el_left = t.columnPos[now_parent_id][i]["left"];
					el_right = t.columnPos[now_parent_id][i]["right"];
					//within_x
					if(x >= el_left &&
	       				 x <=  el_right) {
						//移動列取得
						//insert = null;
						buf_is_x = false;
						insert_column = t.columnPos[now_parent_id][i]["el"];
						for (var j = 0,row_count = t.blocksPos[now_parent_id][i].length; j < row_count; j++) {
							block = t.blocksPos[now_parent_id][i][j]['el'];
							if(t.blocksPos[now_parent_id][i][j]['top'] > y && position == null) {
								//y座標が越えた最初のエレメント取得
								position = t.blocksPos[now_parent_id][i][j]['el'];
							}
							is_x = false;
							is_y = false;
							if(x >= t.blocksPos[now_parent_id][i][j]['left'] &&
		   				 			x <=  t.blocksPos[now_parent_id][i][j]['right']) {
								is_x = true;
								buf_is_x = true;
							}
							if(y >= t.blocksPos[now_parent_id][i][j]['top'] &&
			   				 	y <=  t.blocksPos[now_parent_id][i][j]['bottom']) {
								is_y = true;
							}
							if(is_x && is_y) {
								//ブロックx, y座標内部にマウスがある

								insert = t.blocksPos[now_parent_id][i][j]['el'];
								if(t.currentBlocks[0].get(0) != insert.get(0) &&
										t.blocksPos[now_parent_id][i][j]['group_flag']) {
	   				 				next_parent_id = insert.attr("id");
	   				 				insert = t.searchInsertBlock(t.blocksPos[now_parent_id][i][j], pointer, now_thread_num + 1, next_parent_id, block_move_desc);
									if(insert) {
										break;
									}
	   				 			}

								ex1 = t.blocksPos[now_parent_id][i][j]['left'];
								ex2 = t.blocksPos[now_parent_id][i][j]['right'];
								ey1= t.blocksPos[now_parent_id][i][j]['top'];
								ey2 = t.blocksPos[now_parent_id][i][j]['bottom'];

								direction = null;
								offset = Math.ceil((ex2 - ex1)/4);	//左右(ex2 - ex1)/4 pxまで許容範囲(1/4)
								if(x > ex2 - offset) {
									direction = "right";
								} else if(x < ex1 + offset) {
									direction = "left";
								}else if(y > ey1 + (ey2 - ey1)/2) {
									direction = "bottom";
								} else {
									direction = "top";
								}
								if(!t._cloneStyle(insert, direction)) {
									showMoveDesc(block_move_desc, "");
									break;
								}

								switch (direction) {
									case "left":
										//insert_columnsの左に新列追加
										cell_index = InsertCell(i, direction, insert_columns);
										showMoveDesc(block_move_desc, direction, now_thread_num, cell_index);
										break;
									case "right":
										//insert_columnsの右に新列追加
										cell_index = InsertCell(i, direction, insert_columns);
										showMoveDesc(block_move_desc, direction, now_thread_num, cell_index);
										break;
									case "top":
										//insert_columnsの上に追加
										InsertBeforeEl(insert);
										showMoveDesc(block_move_desc, direction, now_thread_num, insert);
										break;
									case "bottom":
										//insert_columnsの下に追加
										InsertAfterEl(insert);
										showMoveDesc(block_move_desc, direction, now_thread_num, insert);
										break;
								}
								break;
							}
						}
						if(buf_is_x && (insert == undefined || insert == null)) {
							//既存列のブロックとブロックの間、また、ブロック上あるいは下にある
							//その位置に挿入
							if(position != null){
								if(t._cloneStyle(position, 'top')) {
									insert = position;
									InsertBeforeEl(insert);
									showMoveDesc(block_move_desc, 'top', now_thread_num, insert);
								} else {
									showMoveDesc(block_move_desc, "");
								}

							} else {
								//insert_columnの下に挿入
								if(block) {
									if(t._cloneStyle(block, 'bottom')) {
										insert = block;
										InsertAfterEl(insert);
										showMoveDesc(block_move_desc, 'bottom', now_thread_num, insert);
									} else {
										showMoveDesc(block_move_desc, "");
									}
								}
							}
						}

						// buf_is_y &&
						if(insert == undefined || insert == null) {
							// 現在のカラムの左右
							force = true;
							if(x < el_left + ((el_right - el_left) / 2)) {
								direction = "left";
							} else {
								direction = "right";
							}
							if(t.currentBlocks[3]) {
								if(direction == "left") {
									if(t.columnPos[now_parent_id][i - 1]) {
										buf_insert_column = t.columnPos[now_parent_id][i - 1]["el"];
									} else {
										buf_insert_column = t.columnPos[now_parent_id][i]["el"];
									}
								} else {
									if(t.columnPos[now_parent_id][i + 1]) {
										buf_insert_column = t.columnPos[now_parent_id][i + 1]["el"];
									} else {
										buf_insert_column = t.columnPos[now_parent_id][i]["el"];
									}
								}
								if((buf_insert_column && buf_insert_column.get(0) == t.currentBlocks[3].get(0)) ||
									t.columnPos[now_parent_id][i]["el"].get(0) == t.currentBlocks[3].get(0)) {
					        		force = false;
					        	}
							}
							insert = t.currentBlocks[0];
							if (!t._cloneStyle(insert, direction, force)) {
								showMoveDesc(block_move_desc, "");
				        	} else if(direction == "left") {
								// left
				        		cell_index = InsertCell(i, 'left', insert_columns);
				        		showMoveDesc(block_move_desc, 'left', now_thread_num, cell_index);
							} else {
								// right
								cell_index = InsertCell(i, 'right', insert_columns);
								showMoveDesc(block_move_desc, 'right', now_thread_num, cell_index);
							}
						}
						break;
					}
				}
			}

			if(insert == null) {
				force = true;
				//if(x < columnsPos['left'] + (columnsPos['right'] - columnsPos['left']) / 2) {
				if(x < columnsPos['left']) {
					direction = "left";
				} else {
					direction = "right";
				}
				if(t.currentBlocks[3]) {
					if(direction == "left") {
						insert_column = insert_columns.children(".nc-column:first");
					} else {
						insert_column = insert_columns.children(".nc-column:last");
					}
					if(insert_column && insert_column.get(0) == t.currentBlocks[3].get(0)) {
		        		force = false;
		        		insert = t.currentBlocks[0];
		        	}
				}

	        	if(!t._cloneStyle(insert, direction, force)) {
	        		//キャンセル
	        		showMoveDesc(block_move_desc, "");
	        	} else if(direction == "left") {
					//左に新列追加
	        		cell_index = InsertCell(0, 'left', insert_columns);
	        		showMoveDesc(block_move_desc, 'left', now_thread_num, cell_index);
				} else {
					//右に新列追加
					cell_index = InsertCell(-1, 'right', insert_columns);
					showMoveDesc(block_move_desc, 'right', now_thread_num, cell_index);
				}
			}
			return t.insert;

/*TODO:他カラムへの移動
			//別カラム移動
			function InsertOtherColumn(column){
				t.insert = column;
				t.insertAction = "insert_columns";
			}
*/
			//新規行追加
			function InsertBeforeEl(obj){
				var div = t.blockDummy;
				var column = div.parent();
				obj.before(div);
				//obj[0].parentNode.insertBefore(div[0], obj[0]);
				t.insert = obj;
				//t.insert_column = insert_column;

				deleteMoveEl(column);

				//DB登録用
				t.insertAction = "insert_row";
			}
			function InsertAfterEl(obj){
				var div = t.blockDummy;
				var column = div.parent();
				obj.after(div);
				//obj[0].parentNode.insertBefore(div[0], obj[0].nextSibling);
				t.insert = obj;
				//t.insert_column = column;

				deleteMoveEl(column);
				//DB登録用
				t.insertAction = "insert_row";
			}
			//新規列追加
			function InsertCell(index, direction, insert_columns){
				var div = t.blockDummy;
				var column = div.parent();
				div.css('display', 'none').appendTo($(document.body));	//退避
				deleteMoveEl(column);
				if(index == -1) {
					// 一番右
					index = insert_columns.children('.nc-column').length - 1;
				}
				var insert_column = insert_columns.children('.nc-column:eq('+index+')');
				var new_column = $('<div></div>').addClass(insert_column.attr('class'));

				if(direction == 'left') {
					insert_column.before(new_column);
				} else {
					insert_column.after(new_column);
				}
				div.css('display', '').appendTo(new_column);
				t.insert = div;

				//DB登録用
				t.insertAction = "insert_cell";
				return index;
			}
			//移動元要素削除
			function deleteMoveEl(column) {
				if(!column.children(':first').get(0)) {
					column.remove();
				}
			}
			// 移動用説明文表示
			function showMoveDesc(block_move_desc, direction, thread_num, block) {
				var b_id, title, value = '';
				if(direction == '') {
					block_move_desc.css('display', 'none');
				} else {
					if(direction == 'left' || direction == 'right') {
						block++;
						if(direction == 'right') {
							block = ++block;
						}
						value = __d('pages', 'Add a new column:[%s]', block);
					} else {
						b_id = block.attr('id');
						title = $('#nc-block-header-page-name' + b_id).html();
						if(direction == 'top') {
							value = __d('pages', 'Move to the top of the [%s]', title);
						} else {
							value = __d('pages', 'Move to the bottom of the [%s]', title);
						}
					}
					if(thread_num != 0) {
						value += '<br />' + __d('pages', 'Thread:[%s]', thread_num);
					}
					block_move_desc.html(value).css('display', 'block');
				}
			}
		},
		//
		//移動挿入先取得
		//
		setSearchBlock: function(columns, now_thread_num, now_parent_id, childBlocks) {
			// 初期化
			var t = this, columns_els, child_els, count, column, row, now_columns, next_parent_id;
			t.columnPos[now_parent_id] = Array();
			t.blocksPos[now_parent_id] = Array();

			columns_els = columns.children('.nc-column');
			for (var i = 0, col_len = columns_els.length; i < col_len; i++) {
				column = $(columns_els[i]);
				t.columnPos[now_parent_id][i] = new Object();
				t._setPos(t.columnPos[now_parent_id][i], column);
				t.blocksPos[now_parent_id][i] = Array();
				child_els = column.children();
				count = 0;
				for (var j = 0,row_len = child_els.length; j < row_len; j++) {
					row = $(child_els[j]);
					next_parent_id = row.attr("id");
					if(!next_parent_id) {
						// clone
						continue;
					}
					t.blocksPos[now_parent_id][i][count] = new Object();
					t.blocksPos[now_parent_id][i][count]['group_flag'] = false;
					if(row.hasClass('nc-group')) {
						//Groupingブロック
						now_columns = $(".nc-columns:first", row);
						t.blocksPos[now_parent_id][i][count]['group_flag'] = true;
						t.blocksPos[now_parent_id][i][count]['group_el'] = now_columns;
						if(now_columns) t.setSearchBlock(now_columns, now_thread_num + 1,next_parent_id, childBlocks);
					}
					if(!childBlocks[row.attr("id")]) {
						t._setPos(t.blocksPos[now_parent_id][i][count], row);
					}
					count++;
				}
			}
		},
		_cloneStyle: function(insert, direction, force) {
			var t = this;
			var parent = t.blockDummy.parent();
			if(direction == 'top' || direction == 'bottom') {
				var next_el = (direction == 'bottom') ? t.currentBlocks[1] : t.currentBlocks[2];
				if((next_el.get(0) && next_el.attr('id') == insert.attr('id')) || t.currentBlocks[0].attr('id') == insert.attr('id')) {
					// 自分自身の上か下ならば、Cancel
					// または、一つ下のブロックのTop、一つ上のブロックのBottom
					insert = null;
				}
			} else if(t.currentBlocks[3] && insert) {
				// 自分自身の左右列
				// 左(右)に移動で、その列の左(右)に唯一の移動元ブロックがあるならば、Cancel
				if((t.currentBlocks[0].attr('id') == insert.attr('id')) ||
						(direction == 'left' && insert.parent().prev().get(0) == t.currentBlocks[3].get(0)) ||
						(direction == 'right' && insert.parent().next().get(0) == t.currentBlocks[3].get(0))) {
					insert = null;
				}
			}

			if((insert && insert.attr('id')) || force) {
				t.blockDummy.css('display', '');
				t.currentBlocks[0].removeClass('nc-block-dummy');
				return true;
			}
			t.blockDummy.css('display', 'none');
			$(t.currentBlocks[0]).before(t.blockDummy);
			t.currentBlocks[0].addClass('nc-block-dummy');
			t.insert = t.currentBlocks[0];
			if(!parent.children(':first').get(0)) {
				parent.remove();
			}
			return false;
		},

		_setPos: function(key, obj, offset) {
			var offset = (offset == undefined) ? obj.offset() : offset;
			key['el'] = obj;
			key['top'] = offset.top;
			key['right'] = offset.left + obj.outerWidth();
			key['bottom'] = offset.top + obj.outerHeight();
			key['left'] = offset.left;
		},

		_removeGroupBlock: function(chk) {
			var target_remove;
			var parent_el, child_els, columns, columns_len , group_parent, remove;
			while(1) {
				parent_el = chk.parent();
				child_els = parent_el.children();
				if(child_els.length == 1) {
					columns = parent_el.parents(".nc-columns:first");
					columns_len = columns.children('.nc-column').length;

					group_parent = columns.parents(".nc-group:first");
					target_remove = parent_el;	//chk.parent();
					if(group_parent.get(0) && columns_len == 1) {
						remove = group_parent;
						chk = group_parent;
					} else break;
				} else break;
			}
			////if(remove && columns_len == 1) {
			if(remove) {
				target_remove = remove;
			}
			if($(target_remove).hasClass("nc-column")) {
				target_remove = null;
			}
			return target_remove;
		},
		/*グルーピング解除完了処理*/
		cancelGroupingComp: function(block) {
			var t = this;
			var parent_column = block.parent();
			var current_columns = $(".nc-columns:first", block);
			var current_column = $(".nc-column:first", current_columns);

			var columnList = Array();
			var count_column = 0;

			var block_els = current_columns.children('.nc-column');	//$(' > .nc-column', current_columns);
			var buf_block = block;
			//current_columns.children('.nc-column').each(function(k, column) {
			block_els.each(function(k, column) {
				if(column == current_column.get(0)) {
					//既存列追加処理
					var divList = Array();
					var count = 0;
					$(column).children().each(function() {
						if($(this).attr('[data-block]')) {
							divList[count] = this;
							count++;
						}
						buf_block.after($(this));
						buf_block = $(this);
					});
				} else {
					//新列追加処理
					columnList[count_column] = column;
					count_column++;
				}
			});
			for (var i = columnList.length - 1; i >= 0; i--) {
				//新列追加処理
				parent_column.after($(columnList[i]));
			}
			block.remove();
		},
		/* グループ化の選択を解除する */
		cancelSelects: function(block) {
			var t = this;
			if(block) {
				var current = block.parents('[data-columns]:first');
			}
			$.each(t.groups, function(k, el) {
				if (!block || $(el).parents('[data-columns]:first')[0] != current[0]) {
					$(el).children('.nc-frame:first').removeClass('nc-select-group');
					delete t.groups[$(el).attr('id')];
				}
			});
		},
		/* グループ化、グループ化解除のリンク表示 */
		toggleGroup: function() {
			var t = this, group = false;
			for (var key in t.groups) {
				var group = true;
				break;
			}
			if(group == true) {
				$('#nc-block-group').slideDown();
			} else {
				$('#nc-block-group').slideUp();
			}
		},
		/* グルーピング処理 */
		addGrouping: function(e) {
			var t = this, first = true;
			var params = new Object();
			var first_id = null, i = 0, block = null, block_id = null, page_id = null;

			$.Event(e).preventDefault();
			$.Event(e).stopPropagation();

			if (!$.Common.confirm(__d('pages', 'groupConfirm'))) return false;

			params['groups'] = new Array();
			$.each(this.groups, function(k, el) {
				params['groups'][i] = $(el).attr('data-block');
				if(!first_id)
					first_id = k;
				i++;
			});

			block = $('#' + first_id);
			block_id = block.attr('data-block');
			page_id = block.attr('data-page');

			params['page_id'] = page_id;
			params['show_count'] = $._nc.show_count[page_id];

			$.post($.Common.urlBlock(block_id, 'block/add_group'),
				params,
				function(res){
					var prev = null;
					$.each(t.groups, function(k, el) {
						if(first) {
							$(el).before(res);
							prev = $(el).prev();
						}
						var column = block.parent();
						$(el).remove();
						if(!column.children(':first').get(0)) {
							column.remove();
						}
						first = false;
					});
					var objs = $('div.nc-block', prev);
					//$.proxy(t.initBlock(prev), t);
					//objs.each(function() {
					//	$.proxy(t.initBlock($(this)), t);
					//});
					$._nc.show_count[page_id]++;
					t.groups = new Object();
					t.toggleGroup();
				}
			);
		},
		/* グルーピング解除処理 */
		cancelGrouping: function(e) {
			var t = this, block_id = null, page_id = null;
			var params = new Object(), i = 0;

			$.Event(e).preventDefault();
			$.Event(e).stopPropagation();

			params['cancel_groups'] = new Array();
			$.each(t.groups, function(k, el) {
				if($(el).hasClass("nc-group")) {
					params['cancel_groups'][i] = $(el).attr('data-block');
				} else {
					$(el).children('.nc-frame:first').removeClass('nc-select-group');
					delete t.groups[k];
				}
				//$(el).children('.nc-frame:first').removeClass('nc-select-group');
				i++;
				if(!block_id) {
					block_id = $(el).attr('data-block');
					page_id = $(el).attr('data-page');
					params['page_id'] = page_id;
					params['show_count'] = $._nc.show_count[page_id];
				}
			});
			t.toggleGroup();

			if(params['cancel_groups'].length == 0) {
				return;
			}

			if (!$.Common.confirm(__d('pages', 'groupCancelConfirm'))) {
				return false;
			}
			$.post($.Common.urlBlock(block_id, 'block/cancel_group'),
					params,
					function(res){
						$.each(t.groups, function() {
							$(this).children('.nc-frame:first').removeClass('nc-select-group');
							if($(this).hasClass("nc-group")) {
								t.cancelGroupingComp($(this));
							}
						});
						$._nc.show_count[page_id]++;
						t.groups = new Object();
						t.toggleGroup();
					}
			);
		},
		deleteBlockConfirm: function(event, a, block_id, all_delete_flag, confirm) {
			var t = this;

			var all_delete = (all_delete_flag) ? __d('pages', 'You completely delete it.') : null;
			var pos = $(a).offset();	// IE8でエラーになったため、event.targetで取得しない。
			var ok = __('Ok') ,cancel = __('Cancel');

			if(all_delete) {
				confirm += '<div><label for="nc-confirm-dialog-mes-flag">'+
							'<input id="nc-confirm-dialog-mes-flag" type="checkbox" name="confirm_dialog_mes_flag" value="1" />&nbsp;'+
							all_delete+
							'</label></div>';
			}
			var _buttons = {};
			_buttons[ok] = function(){
				t.deleteBlock(block_id);
				//$(this).dialog('close');
				$(this).remove();
			};
			_buttons[cancel]  = function(){
				//$(this).dialog('close');
				$(this).remove();
			};
			$('<div></div>').html(confirm).appendTo($(document.body)).dialog({
				title: __d('pages', 'Delete block'),
				resizable: false,
				height:180,
				modal: true,
				buttons: _buttons,
				zIndex: ++$.Common.zIndex,
				position: [pos.left+20 - $(window).scrollLeft() ,pos.top+20 - $(window).scrollTop()]
    		});

			$.Event(event).preventDefault();
			$.Event(event).stopPropagation();
		},
		addBlock: function( sel_el, module_id ) {
			var t = this, params = new Object();
			var page_el = $(sel_el).parents('[data-add-columns]:first');	// header footer
			if(page_el.get(0)) {
				 page_el = $('#' + page_el.attr('data-add-columns'));
			} else {
				// left center right
				page_el = $(sel_el).parents('[data-page]:first');
			}
			params['page_id'] = page_el.attr('data-page');
			params['show_count'] = $._nc.show_count[params['page_id']];
			params['module_id'] = module_id;

			$.post($.Common.urlBlock(null, 'block/add_block'),
				params,
				function(res){
					var first_column = $(".nc-column:first", page_el);
					var buf_block = first_column.children(":first");
					if(buf_block.get(0)) {
						buf_block.before(res);
					} else {
						first_column.html(res);
					}
					$._nc.show_count[params['page_id']]++;
				}
			);
		},
		deleteBlock: function( block_id ) {
			var t = this, all_delete = 0, params = new Object(), show_count_el = null;
			var block = $('#_' + block_id);
			var page_id = block.attr('data-page');

			if($('#nc-confirm-dialog-mes-flag').is(':checked')) {
				params['all_delete'] = 1;
			} else {
				params['all_delete'] = 0;
			}
			params['page_id'] = page_id;
			params['show_count'] = $._nc.show_count[page_id];

			$.post($.Common.urlBlock(block_id, 'block/del_block'),
					params,
					function(res){
						// 空のグルーピングボックス削除
						var column = block.parent();
						var target_remove = t._removeGroupBlock(block);
						block.remove();
						if(target_remove)
							target_remove.remove();
						if(!column.children('div:first').hasClass('nc-block')
								&& (column.next().hasClass('nc-column') || column.prev().hasClass('nc-column'))) {
							column.remove();
						}
						$._nc.show_count[page_id]++;
					}
			);

			$('#nc-mes-dialog').dialog('close');
		},
		showOperationBlock: function(select) {
			if(!select.get(0)) {
				return;
			}
			select.select2({
				minimumResultsForSearch:-1,
				width: 'element'
			}).change( function(e){
				var url = $(this).val();
				var params = new Object();
				var page_el = $(this).parents('[data-add-columns]:first');	// header footer
				if(page_el.get(0)) {
					page_el = $('#' + page_el.attr('data-add-columns'));
				} else {
					// left center right
					page_el = $(this).parents('[data-page]:first');
				}
				params['page_id'] = page_el.attr('data-page');
				params['show_count'] = $._nc.show_count[params['page_id']];

				$.PagesBlock.operationBlock($(this), url, params);
			} );
		},

		// ブロック操作
		operationBlock: function(target, url, params) {
			var pos = $(target).prev().offset();
			$.post(url,
				params,
				function(res){
					if($.trim(res) == 'true') {
						location.reload(true);
					} else {
						// 確認メッセージ表示
						var ok = __('Ok') ,cancel = __('Cancel');
						var default_params = {
							resizable: false,
				            modal: true,
					        position: [pos.left + 10 - $(window).scrollLeft() ,pos.top + 10 - $(window).scrollTop()]
						}, _buttons = {};
						_buttons[ok] = function(){
							var shortcut_type = $('#nc-block-confirm-shortcut');
							if(shortcut_type.get(0) && shortcut_type.is(':checked')) {
								params['shortcut_type'] = 1;
							}

							params['is_confirm'] = 1;
							$.PagesBlock.operationBlock(target, url, params);
							$( this ).remove();
						};
						_buttons[cancel] = function(){
							$(target).val('');
							$( this ).remove();
						};
						var dialog_params = $.extend({buttons: _buttons}, default_params);
						$('<div></div>').html(res).dialog(dialog_params);
					}
				}
			);
		},

		// ヘッダー表示・非表示切替
		toggleBlockHeader: function(e, a) {
			var target = $(a).parent();
			var show_target = (target.hasClass('nc-block-move')) ? target.next() : target.prev();
			target.slideUp();
			show_target.slideDown();
			$.Event(e).preventDefault();
			$.Event(e).stopPropagation();
		},
		toggleOperation: function(e, id) {
			//var pos = $(e.target).position();
			$('#nc-block-header-operation' + id).toggle().css({
				'zIndex':$.Common.blockZIndex++,
				'right': '-55px',
				'top' :  '30px'
			});
			$.Event(e).preventDefault();
		}
	}
})(jQuery);