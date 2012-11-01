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
	$(document).ready(function(){
		$(".nc_add_block").chosen().change( function(e){
			var module_id = $(this).val();
			if(module_id != 0) {
				$.PagesBlock.addBlock($(this), module_id);
				$('option:first', $(this)).prop('selected', true);
			}
			$(this).trigger("liszt:updated");
		} );
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
			var block = $('#' + id),block_move = $("#nc_block_move" + id);

			if(block_move.get(0) && !block.hasClass('ui-draggable') && $('#container').get(0)) {
				block_move.click(function(e){
					if (e.target.tagName.toUpperCase() === 'A') {
						return;
					}
					var blockChild = block.children('.nc_frame:first');
					// グルーピング
					if(!blockChild.hasClass('nc_select_group')) {
						blockChild.addClass('nc_select_group');
						t.cancelSelects(block);
						t.groups[id] = block;
					} else {
						blockChild.removeClass('nc_select_group');
						delete t.groups[id];
					}
					t.toggleGroup();
					e.stopPropagation();
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

						var blockChild = block.children('.nc_frame:first');
						blockChild.css("opacity", 0.3);
						var columns = block.parents('[data-columns=top]:first');
						t._setPos(t.columnsPos, columns);

						var childBlocks = new Object();
						if(block.hasClass('nc_group')) {
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
						var div = $('<div></div>').addClass('nc_block_dummy');
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
					},
					drag:function(event, ui){
						//t.searchInsertBlock(t.columnsPos, [event.pageX, event.pageY], 0, 0);
						t.searchInsertBlock(t.columnsPos, [event.pageX, event.pageY], 0, 0);
					},
					revert: function(socketObj) {
						if(t.currentBlocks[0].hasClass('nc_block_dummy')) {
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
						var ret = t.currentBlocks[0].hasClass('nc_block_dummy');
						var show_count_el = null;
						block.children('.nc_frame:first').css("opacity", 1);
						t.currentBlocks[0].removeClass('nc_block_dummy');
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
							//var page_id = block.parents('[data-page]:first').attr('data-page');
							show_count_el = block.parents('[data-show-count]:first');
							params['show_count'] = show_count_el.attr('data-show-count');
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
							$.each(parent_columns.children('.nc_column'), function() {
								if(parent.get(0) == this) {
									return false;
								}
								params['col_num'] += 1;
							});

							var parent_group = parent.parents(".nc_group:first");
							if(parent_group.get(0)) {
								params['parent_id'] = parent_group.attr('data-block');
							}

							$.post($.Common.urlBlock(block_id, 'block/' + t.insertAction),
								params,
								function(res){
									show_count_el.attr('data-show-count', ++params['show_count']);
								}
							);

						});
					}
				});
			}
		},
		//
		//移動挿入先検索
		//
		searchInsertBlock: function(columnsPos, pointer, now_thread_num, now_parent_id) {
			var t = this, insert_columns, insert_column, el_left, el_right, block, position;
			var x = pointer[0],y = pointer[1], insert, direction, force, next_parent_id;

			var ex1, ex2, ey1, ey2, direction, offset, index, parent, buf_insert_column;
			var is_x, is_y, buf_is_x,prev_column;

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

			if(columnsPos['el'].hasClass("nc_group"))
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
	   				 				insert = t.searchInsertBlock(t.blocksPos[now_parent_id][i][j], pointer, now_thread_num + 1, next_parent_id);
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
									break;
								}

								switch (direction) {
									case "left":
										//insert_columnsの左に新列追加
										InsertCell(i, direction, insert_columns);
										break;
									case "right":
										//insert_columnsの右に新列追加
										InsertCell(i, direction, insert_columns);
										break;
									case "top":
										//insert_columnsの上に追加
										InsertBeforeEl(insert);
										break;
									case "bottom":
										//insert_columnsの下に追加
										InsertAfterEl(insert);
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
								}

							} else {
								//insert_columnの下に挿入
								if(block) {
									if(t._cloneStyle(block, 'bottom')) {
										insert = block;
										InsertAfterEl(insert);
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
								;
				        	} else if(direction == "left") {
								// left
				        		InsertCell(i, 'left', insert_columns);
							} else {
								// right
								InsertCell(i, 'right', insert_columns);
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
						insert_column = insert_columns.children(".nc_column:first");
					} else {
						insert_column = insert_columns.children(".nc_column:last");
					}
					if(insert_column && insert_column.get(0) == t.currentBlocks[3].get(0)) {
		        		force = false;
		        		insert = t.currentBlocks[0];
		        	}
				}

	        	if(!t._cloneStyle(insert, direction, force)) {
	        		//キャンセル
	        		;
	        	} else if(direction == "left") {
					//左に新列追加
	        		InsertCell(0, 'left', insert_columns);
				} else {
					//右に新列追加
					InsertCell(-1, 'right', insert_columns);
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

				delMoveEl(column);

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

				delMoveEl(column);
				//DB登録用
				t.insertAction = "insert_row";
			}
			//新規列追加
			function InsertCell(index, direction, insert_columns){
				var div = t.blockDummy;
				var column = div.parent();
				div.css('display', 'none').appendTo($(document.body));	//退避
				delMoveEl(column);
				if(index == -1) {
					// 一番右
					index = insert_columns.children('.nc_column').length - 1;
				}
				var insert_column = insert_columns.children('.nc_column:eq('+index+')');
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
			}
			//移動元要素削除
			function delMoveEl(column) {
				if(!column.children(':first').get(0)) {
					column.remove();
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

			columns_els = columns.children('.nc_column');
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
					if(row.hasClass('nc_group')) {
						//Groupingブロック
						now_columns = $(".nc_columns:first", row);
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
				t.currentBlocks[0].removeClass('nc_block_dummy');
				return true;
			}
			t.blockDummy.css('display', 'none');
			$(t.currentBlocks[0]).before(t.blockDummy);
			t.currentBlocks[0].addClass('nc_block_dummy');
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
					columns = parent_el.parents(".nc_columns:first");
					columns_len = columns.children('.nc_column').length;

					group_parent = columns.parents(".nc_group:first");
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
			if($(target_remove).hasClass("nc_column")) {
				target_remove = null;
			}
			return target_remove;
		},
		/*グルーピング解除完了処理*/
		cancelGroupingComp: function(block) {
			var t = this;
			var parent_column = block.parent();
			var current_columns = $(".nc_columns:first", block);
			var current_column = $(".nc_column:first", current_columns);

			var columnList = Array();
			var count_column = 0;

			var block_els = current_columns.children('.nc_column');	//$(' > .nc_column', current_columns);
			var buf_block = block;
			//current_columns.children('.nc_column').each(function(k, column) {
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
					$(el).children('.nc_frame:first').removeClass('nc_select_group');
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
				$('#nc_block_group').slideDown();
			} else {
				$('#nc_block_group').slideUp();
			}
		},
		/* グルーピング処理 */
		addGrouping: function(e) {
			var t = this, first = true;
			var params = new Object();
			var first_id = null, i = 0, block = null, block_id = null;
			var show_count_el = null;

			e.preventDefault();
			e.stopPropagation();

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

			show_count_el = block.parents('[data-show-count]:first');
			params['show_count'] = show_count_el.attr('data-show-count');
			
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
					var objs = $('div.nc_block', prev);
					//$.proxy(t.initBlock(prev), t);
					//objs.each(function() {
					//	$.proxy(t.initBlock($(this)), t);
					//});
					show_count_el.attr('data-show-count', ++params['show_count']);
					t.groups = new Object();
					t.toggleGroup();
				}
			);
		},
		/* グルーピング解除処理 */
		cancelGrouping: function(e) {
			var t = this, block_id = null;
			var params = new Object(), i = 0;
			var show_count_el = null;

			e.preventDefault();
			e.stopPropagation();

			params['cancel_groups'] = new Array();
			$.each(t.groups, function(k, el) {
				if($(el).hasClass("nc_group")) {
					params['cancel_groups'][i] = $(el).attr('data-block');
				} else {
					$(el).children('.nc_frame:first').removeClass('nc_select_group');
					delete t.groups[k];
				}
				//$(el).children('.nc_frame:first').removeClass('nc_select_group');
				i++;
				if(!block_id) {
					block_id = $(el).attr('data-block');
					show_count_el = $(el).parents('[data-show-count]:first');
					params['show_count'] = show_count_el.attr('data-show-count');
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
							$(this).children('.nc_frame:first').removeClass('nc_select_group');
							if($(this).hasClass("nc_group")) {
								t.cancelGroupingComp($(this));
							}
						});
						show_count_el.attr('data-show-count', ++params['show_count']);
						t.groups = new Object();
						t.toggleGroup();
					}
			);
		},
		delBlockConfirm: function(event, block_id, all_delete_flag, confirm) {
			var t = this;

			var all_delete = (all_delete_flag) ? __d('pages', 'I completely delete it.') : null;

			$.Common.showConfirm(event.target, __d('pages', 'Delete block'),
					confirm, function(){t.delBlock(block_id);},
					null, all_delete);

			event.preventDefault();
			event.stopPropagation();
		},
		addBlock: function( sel_el, module_id ) {
			var t = this, params = new Object(), show_count_el = null;
			var page_el = $(sel_el).parents('[data-add-columns]:first');	// header footer
			if(page_el.get(0)) {
				 page_el = $('#' + page_el.attr('data-add-columns'));
			} else {
				// left center right
				page_el = $(sel_el).parents('[data-page]:first');
			}
			params['show_count'] = page_el.attr('data-show-count');
			params['page_id'] = page_el.attr('data-page');
			params['module_id'] = module_id;

			$.post($.Common.urlBlock(null, 'block/add_block'),
					params,
					function(res){
						var first_column = $(".nc_column:first", page_el);
						var buf_block = first_column.children(":first");
						if(buf_block.get(0)) {
							buf_block.before(res);
						} else {
							first_column.html(res);
						}
						page_el.attr('data-show-count', ++params['show_count']);
					}
			);
		},
		delBlock: function( block_id ) {
			var t = this, all_delete = 0, params = new Object(), show_count_el = null;
			var block = $('#_' + block_id);

			show_count_el = block.parents('[data-show-count]:first');
			if($('#nc_confirm_dialog_mes_flag').is(':checked')) {
				params['all_delete'] = 1;
			} else {
				params['all_delete'] = 0;
			}
			params['show_count'] = show_count_el.attr('data-show-count');

			$.post($.Common.urlBlock(block_id, 'block/del_block'),
					params,
					function(res){
						// 空のグルーピングボックス削除
						var column = block.parent();
						var target_remove = t._removeGroupBlock(block);
						block.remove();
						if(target_remove)
							target_remove.remove();
						if(!column.children(':first').get(0)) {
							column.remove();
						}
						show_count_el.attr('data-show-count', ++params['show_count']);
					}
			);

			$('#nc_mes_dialog').dialog('close');
		}
	}
})(jQuery);