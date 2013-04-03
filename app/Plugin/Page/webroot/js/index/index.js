/**
 * ページメニュー js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
/**
 * ページメニュー表示時
 * @param   integer is_edit 編集モードかどうか
 * @param   integer active_page_id 現在表示中のページID
 * @param   integer active_tab アクティブなタブのindex 0 or 1
 * @param   integer sel_active_tab 選択中のページItemのあるタブのindex
 * @param   integer copy_page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	$.fn.PageMenu = function(is_edit, active_page_id, active_tab, sel_active_tab, copy_page_id) {
		// Privateメソッド

		// 表示件数
		var chgLimit = function() {
			// 表示件数
			$('#pages-menu-community-limit:visible').chosen({disable_search : true}).change( function(e){
				var limit_url = $(this).val();
				$.get(limit_url, function(res) {
					$('#nc-pages-setting-dialog').replaceWith(res);
				});
			} );
		};

		// ページ削除
		var dletePage = function(url, page_id, all_delete, params) {
			$.post(url, {'data[Page][id]': page_id, 'all_delete': all_delete},function(res){
				var re_html = new RegExp("^<script>", 'i');
				if(!$.trim(res).match(re_html)) {
					// error
					$.Common.showErrorDialog(res, params);
				} else {
					// success
					var li = $('#pages-menu-edit-item-' + page_id);
					var prev = li.prev(), next = li.prev(), parent = li.parents('li');
					var active = (prev.get(0)) ? prev : ((next.get(0)) ? next : parent);
					var pager, curent;
					if(active.get(0)) {
						$('.pages-menu-edit-content', active).click();
					}

					if(li.hasClass('pages-menu-handle-community')) {
						// コミュニティーでページャーがあれば、再表示
						// ページャーの表示がずれていくため
						pager = $('.nc-paginator:first', $('#pages-menu-community'));
						if(pager.get(0)) {
							curent = $('.current:first', pager);
							if(active.get(0) || !curent.prev().get(0)) {
								// current
								pager.next().attr('href', pager.next().attr('href').replace("active_tab=0", "active_tab=1")).click();
							} else {
								// current -1
								curent.prev().children('a').click();
							}
						}
					}

					$(res).appendTo($(document.body));

					li.remove();
				}
			});
		};

		var addEventNestable = function(active_tab_name, root_menu, target, page_id) {
			if(target.hasClass('pages-menu-edit-item')) {
				$(".pages-menu-edit-item" , target).add(target).each(function(){
					var child_target = $(this);
					if(active_tab_name == '#pages-menu-page') {
						$(root_menu.get(0)).nestable('addEvent', [child_target, page_id]);
					} else {
						$(root_menu.get(1)).nestable('addEvent', [child_target, page_id]);
					}
				});
			}
		};

		var slideTarget = function(target, active_tab_name, scroll_target) {
			scroll_target = (scroll_target) ? scroll_target : target;
			target.slideDown(300, function() {
				var position = scroll_target.offset().top+$(active_tab_name).scrollTop() - $(active_tab_name).offset().top;
				// ページ名称にfocus
				var page_name = $('[name=data\\[Page\\]\\[page_name\\]]:visible:first', target);
				if(page_name.get(0)) {
					page_name.select();
					// スクロール
					$(active_tab_name).animate({scrollTop:position}, 400, 'swing');
				} else {
					// スクロール 参加者修正の場合、スクロールにdelayをつけて実行
					$(active_tab_name).delay(500).animate({scrollTop:position}, 400, 'swing');
				}

			});
		};

		var tab = this;
		if(!(is_edit)) {
			var active_li = $('#pages-menu-item-' + active_page_id);
		} else {
			var active_li = $('#pages-menu-edit-item-' + active_page_id);
		}
		var options = {
			active          : active_page_id,
			activeItemClass : 'highlight'
		};
		var active_tab_name = null;

		if(active_tab == 0) {
			active_tab_name = '#pages-menu-page';
		} else {
			active_tab_name = '#pages-menu-community';
		}

		var root_menu = $('#pages-menu-page, #pages-menu-community');
		if(!(is_edit)) {
			options['singleHandleClass'] = 'pages-menu-dd-single';
			options['contentClass'] = 'pages-menu-handle';
		} else {
			options['contentClass'] = 'dd-drag-content';
		}
		$('#pages-menu-tab').tabs({
			active: active_tab,
			activate: function( event, ui ) {
				// その他操作非表示
				$.PageMenu.closeOtherOperation();

				if(ui['newPanel'].attr('id') == 'pages-menu-page') {
					active_tab = 0;
					active_tab_name = '#pages-menu-page';
				} else {
					active_tab = 1;
					active_tab_name = '#pages-menu-community';
				}
				if(is_edit) {
					var is_chief = active_li.attr('data-is-chief');
					var add_community = $('#pages-menu-add-community-btn');
					if(sel_active_tab != active_tab) {
						$('#pages-menu-add-btn').addClass('pages-menu-btn-disable');
					} else if(is_chief) {
						$('#pages-menu-add-btn').removeClass('pages-menu-btn-disable');
					}
					if(add_community.get(0)) {
						if(active_tab == 0) {
							add_community.addClass('pages-menu-btn-disable');
						} else {
							add_community.removeClass('pages-menu-btn-disable');
						}
					}
				}

				if(active_tab == 1) {
					chgLimit();
				}
			}
		});

		chgLimit();

		// スクロール
		setTimeout(function(){
			slideTarget(active_li, active_tab_name);
		}, 500);

		// 言語切替
		var lang_sel =$('#pages-menu-language');
		if(lang_sel.get(0)) {
			lang_sel.chosen({disable_search : true}).change( function(e){
				var lang = $(this).val();
				var url = $(this).attr('data-ajax-url') + '/' + lang + '?is_edit=' + is_edit + '&active_tab=' + active_tab;
				$.get(url, function(res) {
					$('#nc-pages-setting-dialog').replaceWith(res);
				});
			} );
		}

		// ページ移動
		var result_response = '';
		root_menu.nestable(options)
		.on('change', function(e, page_id, drop_page_id, position) {
			if(!page_id || !drop_page_id) {
				return;
			}
			// 表示順変更
			var url = $.Common.urlBlock(0, 'page/menus/chgsequence');
			var data = {'data[Page][id]': page_id, 'data[DropPage][id]': drop_page_id, 'position': position};
			var ret = null;
			var li = $('#pages-menu-edit-item-' + drop_page_id);
			var pos = li.offset(), _buttons = {};
			var default_params = {
				resizable: false,
	            modal: true,
		        position: [pos.left + 30 - $(window).scrollLeft() ,pos.top + 30 - $(window).scrollTop()]
			};
			$.ajax({
				type: "POST",
				url: url,
				data: data,
				async: false,
				success: function(res){
					var re_html = new RegExp("^<script>", 'i');
					var ok = __('Ok') ,cancel = __('Cancel'), body, params;
					if(!$.trim(res).match(re_html)) {
						var re_info_html = new RegExp("^<div class=\"pages-menu-edit-confirm-desc\">", 'i');
						if($.trim(res).match(re_info_html)) {
							// confirm
							_buttons[ok] = function(){
								data['is_confirm'] = 1;
								var list = $('#pages-menu-edit-other-operation');
								var check_url = list.attr('data-ajax-url') + '/' + page_id + '?page_id=' + drop_page_id;
								var timer = $.PageMenu.showProgressbar(check_url, params);
								$.ajax({
									type: "POST",
									url: url,
									data: data,
									// async: false,
									success: function(res){
										result_response = res;
										$.PageMenu.hideProgressbar(timer);
										if($.trim(res).match(re_html)) {
											root_menu.nestable('setStop', [true]);
										} else {
											$.Common.showErrorDialog(res, null, li);
											root_menu.nestable('setStop', [false]);
										}
									}
								});
								$( this ).remove();
							};
							_buttons[cancel] = function(){
								$( this ).remove();
								root_menu.nestable('setStop', [false]);
							};
							params = $.extend({buttons: _buttons}, default_params);
							$('<div></div>').html(res).dialog(params);
						} else {
							// error
							$.Common.showErrorDialog(res, null, li);
							ret = false;
						}
					} else {
						// success
						result_response = res;
						ret = true;
					}
				}
 			});
 			return ret;
		}).on('success', function(e, page_id, drop_page_id, position) {
			var params = new Object();
			params['data-ajax-replace'] = '#pages-menu-edit-item-' + page_id;
			var target = $.Common.ajaxSuccess(null, result_response, params);
			addEventNestable(active_tab_name, root_menu, target, page_id);
			// コンテンツクリックイベント
			$('.pages-menu-edit-content:first', target).click();
		});

		// コミュニティーはすべて閉じた状態で表示
		$(root_menu.get(1)).nestable('collapseAll', ['.highlight:first']);

		// すべて展開
		$(root_menu.get(0)).on('click','.pages-menu-expand-all',function(e) {
			$.PageMenu.closeOtherOperation();
			$(root_menu.get(0)).nestable('expandAll', []);
		});
		$(root_menu.get(1)).on('click','.pages-menu-expand-all',function(e) {
			$.PageMenu.closeOtherOperation();
			$(root_menu.get(1)).nestable('expandAll', []);
		});

		// すべて折りたたむ
		$(root_menu.get(0)).on('click','.pages-menu-collapse-all',function(e) {
			$.PageMenu.closeOtherOperation();
			$(root_menu.get(0)).nestable('collapseAll', []);
		});
		$(root_menu.get(1)).on('click','.pages-menu-collapse-all',function(e) {
			$.PageMenu.closeOtherOperation();
			$(root_menu.get(1)).nestable('collapseAll', []);
		});

		//root_menu.get(1).nestable('expandAll');
		// ページ編集切替
		$('#pages-menu-edit-btn').on('ajax:beforeSend', function(e, url) {
			return url + '&active_tab=' + active_tab;
		});
		if(is_edit) {
			// ページ追加
			$('#pages-menu-add-btn').on('ajax:beforeSend', function(e, url) {
				if($(this).hasClass('pages-menu-add-btn-disable')) {
					// ページ追加不可
					return false;
				}

				var ajax_id, page_edit, replace_ajax_id, active, dd_sequence, position;
				ajax_id = $(this).attr('data-ajax-replace');
				replace_ajax_id = ajax_id.replace(/^#/, '');

				page_edit = $("<li id='"+ replace_ajax_id +"'></li>").hide();
				active_li.before(page_edit);	// class='dd-item dd-drag-item'

				dd_sequence = active_li.attr('data-dd-sequence');
				position = 'bottom';
				if(dd_sequence == 'inner-only') {
					position = 'inner';
				}
				url = url + '/' + active_li.attr('data-id') + '/' +  position;

				return url;
			}).on('ajax:success', function(e, res) {
				var dd_sequence, position, insert_li;
				var target = $.Common.ajaxSuccess(this, res);

				dd_sequence = active_li.attr('data-dd-sequence');
				position = 'bottom';
				if(dd_sequence == 'inner-only') {
					position = 'inner';
					root_menu.nestable('expandItem', [active_li]);
				} else {
					root_menu.nestable('collapseItem', [active_li]);
				}
				insert_li = active_li;
				if(!active_li.get(0)) {
					insert_li = $('ol:first',$(active_tab_name));
				}

				target.hide();
				if(active_tab_name == '#pages-menu-page') {
					$(root_menu.get(0)).nestable('appendList', [insert_li, target, position]);
				} else {
					$(root_menu.get(1)).nestable('appendList', [insert_li, target, position]);
				}

				// スクロール
				slideTarget(target, active_tab_name);
				// コンテンツクリックイベント
				$('.pages-menu-edit-content:first', target).click();
				if(copy_page_id > 0) {
					$('.pages-menu-other-icon:first', target).addClass('pages-menu-edit-highlight-icon');
				}
				$.Event(e).preventDefault();
			});

			// コミュニティー追加
			$('#pages-menu-add-community-btn').on('ajax:beforeSend', function(e, url) {
				if($(this).hasClass('pages-menu-btn-disable')) {
					// ページ追加不可
					return false;
				}
				url = url + '/' + active_li.attr('data-id');
				return url;
			}).on('ajax:success', function(e, res) {
				if (res) {
					location.href = res;
				}
				$.Event(e).preventDefault();
			});

			// ページ削除
			root_menu.on('ajax:beforeSend','.pages-menu-delete-icon',function(e, url) {
				var li = $($(e.target).attr('data-ajax-data')),page_id = li.attr('data-id'), room_id = li.attr('data-room-id');
				var is_top = li.attr('data-is-top');
				var a = $('a.pages-menu-edit-title:first', li), title = $.trim(a.html());
				var ok = __('Ok') ,cancel = __('Cancel');
				var pos = $(e.target).offset(), _buttons = {}, default_params = {
					title: (page_id == room_id) ? __d('pages', 'Delete room') : __d('pages', 'Delete page'),
	            	resizable: false,
	            	// height:180,
	            	modal: true,
		            position: [pos.left-150 - $(window).scrollLeft() ,pos.top-50 - $(window).scrollTop()]
				},params, body;
				_buttons[ok] = function(){
					if($('#pages-menu-all-delete').is(':checked')) {
						var _sub_buttons = {};
						_sub_buttons[ok] = function(){
							dletePage(url, page_id, 1, default_params);
							$(this).remove();
						};
						_sub_buttons[cancel] = function(){
							$(this).remove();
						};
						params = $.extend({buttons: _sub_buttons}, default_params);
						$('<div></div>').html(__d('pages', 'You can\'t be undone completely removed. <br />Are you sure you want to delete?'))
							.appendTo($(document.body)).dialog(params);
					} else {
						dletePage(url, page_id, 0, default_params);
					}
					$(this).remove();
				};
				_buttons[cancel]  = function(){
					$(this).remove();
				};
				params = $.extend({buttons: _buttons}, default_params);
				body = __('Deleting %s. <br />Are you sure to proceed?', title) + '<div><label for="pages-menu-all-delete">'+
							'<input id="pages-menu-all-delete" type="checkbox" name="all_delete" value="1" '+
							((is_top == '1' && page_id == room_id) ? 'disabled="disabled" checked="checked" ' : '') + '/>&nbsp;'+
							__d('pages', 'You completely delete it.')+
							'</label></div>';

				$('<div></div>').html(body).appendTo($(document.body)).dialog(params);
				return false;
			});

			// ページ編集画面表示・参加者修正画面表示・参加者解除
			root_menu.add('#pages-menu-edit-other-operation').on('ajax:beforeSend','[data-page-edit-id]',function(e, url) {
				var link = $(e.target);
				if(!link.hasClass('pages-menu-edit-icon')) {
					// 参加者修正
					if(link.get(0).tagName != 'INPUT') {
						// その他操作内
						var list = $('#pages-menu-edit-other-operation');
						var list_page_id = list.attr('data-id');

						url += '/' + list_page_id;
						link.attr("data-page-edit-id", list_page_id);
						if(link.parent().attr('id') == 'pages-menu-edit-other-operation-unassign-members') {
							// 参加者解除
							link.attr("data-ajax-replace", "#pages-menu-edit-item-" + list_page_id);
						} else {
							link.attr("data-ajax-replace", "#pages-menu-edit-participant-" + list_page_id);
						}
					}
					return url;
				}
			    var page_id = $(this).attr("data-page-edit-id");
			    if($.PageMenu.hideDetail(page_id)) {
					return false;
				}

				url = url + '/' + page_id;
				return url;
			}).on('ajax:success','[data-page-edit-id]',function(e, res) {
				var page_id = $(this).attr("data-page-edit-id");
				if(!$(e.target).hasClass('pages-menu-edit-icon')) {
					// 参加者修正
					$.PageMenu.hideDetail(page_id);
				}
				var target = $.Common.ajaxSuccess(this, res);
				var scroll_target = $('#pages-menu-edit-item-' + page_id);
				$('.pages-menu-edit-content:first', scroll_target).click();

				addEventNestable(active_tab_name, root_menu, target, page_id);


				// スクロール
				slideTarget(target, active_tab_name, scroll_target);
				$.Event(e).preventDefault();		// ajax:success後の$.Common.ajaxの後処理をキャンセル
				$.Event(e).stopPropagation();	// formのajax:successイベントキャンセル
			});

			// ページ編集・参加者修正登録
			root_menu.on('ajax:beforeSend','form.pages-menu-edit-form',function(e, url) {
				var li = $(this).parent();
				if(li.get(0).tagName == 'LI') {
					var focus_input = $(':focus', $(e.target));
					if(focus_input.attr('type') == 'text' && focus_input.attr('name') != "data[Page][page_name]") {
						// ページ名称以外のtextではsubmitさせない。IE8、9ではOKボタンに遷移しているため、動作しない。
						return false;
					}

					var a = $('a.pages-menu-edit-title:first', $(this));
					var input = $('input.pages-menu-edit-title:first', $(this));
					var page_id = li.attr('data-id');
					var detail = $('#pages-menu-edit-detail-' + page_id);
					if((!detail.get(0) || detail.css('display') == 'none') && input.val() == $.trim(a.html()) && !input.hasClass('error-input-message')) {
						// ページ名称変更なし
						input.css('display', 'none');
						a.css('display', '');
						return false;
					}
				}

			}).on('ajax:success','form.pages-menu-edit-form',function(e, res) {
				var target = $.Common.ajaxSuccess(this, res);
				var re_html = new RegExp("^<script>", 'i');
				if($(this).attr('data-name') == 'participant' && !$.trim(res).match(re_html)) {
					$.Common.showErrorDialog(res);
					return;
				}
				var li = $(this).parent();
				if(li.get(0).tagName != 'LI') {
					li = li.parents('li:first');
				}
				var page_id = li.attr('data-id');
				active_li = target;
				addEventNestable(active_tab_name, root_menu, target, page_id);
				$.Event(e).preventDefault();
			});

			// ページ公開・非公開
			root_menu.on('click','.pages-menu-display-flag',function(e) {
				var li = $(this).parents('li:first');
				var parentli = li.parents('li:first');
				if(parentli.get(0)) {
					var parentDisplay = $('[name=data\\[Page\\]\\[display_flag\\]]:first', parentli);
					if(parentDisplay.get(0) && parentDisplay.val() == '0') {
						return false;
					}
				}

				var page_id = li.attr('data-id');
				var setDisplay = null;
				var url = $.Common.urlBlock(0, 'page/menus/display');
				$.Event(e).preventDefault();
				$.Event(e).stopPropagation();

				$('[name=data\\[Page\\]\\[display_flag\\]]', li).each(function(){
					var display = $(this);
					var a = display.prev(),img,alt, pre_alt;

					if(a.get(0).tagName == 'A') {
						img = a.children(':first');
					} else {
						img = a;
					}
					if(setDisplay == null) {
						setDisplay = (display.val() == '1') ? 0 : 1;;
					}
					display.val(setDisplay);
					if(img.get(0)) {
						alt = img.attr('data-alt');
						pre_alt = img.attr('alt');
						img.attr('alt', alt);
						img.parent().attr('title', alt);
						img.attr('data-alt', pre_alt);
						if(setDisplay == 0) {
							img.attr('src', img.attr('src').replace("on.gif", "off.gif"));
						} else {
							img.attr('src', img.attr('src').replace("off.gif", "on.gif"));
						}
					}
				});
				$('[name=data\\[Page\\]\\[display_from_date\\]],[name=data\\[Page\\]\\[display_apply_subpage\\]]', li).each(function(){
					if(setDisplay == 0) {
						$(this).removeAttr("disabled");
					} else {
						$(this).attr("disabled", "disabled");
					}
				});
				$.post(url, {'data[Page][id]': page_id, 'data[Page][display_flag]': setDisplay});
			});

			// コンテンツクリックイベント
			root_menu.on('click','.pages-menu-edit-content',function(e) {
				var content = $(this);
				active_li = content.parents('li:first');

				var button = $('button[data-action]:visible:first', active_li);
				if(button.get(0)) {
					button.click();
				}

				// その他操作非表示
				$.PageMenu.closeOtherOperation();

				// ページ追加disable
				var is_chief = active_li.attr('data-is-chief');
				var dd_sequence = active_li.attr('data-dd-sequence');
				if(dd_sequence != 'inner-only') {
					var parent_li = active_li.parents('li');
					if(parent_li.get(0)) {
						is_chief = active_li.attr('data-is-chief');
					}
				}

				if(!is_chief) {
					$('#pages-menu-add-btn').addClass('pages-menu-btn-disable');
				} else {
					$('#pages-menu-add-btn').removeClass('pages-menu-btn-disable');
				}
				sel_active_tab = active_tab;

				$.Event(e).preventDefault();
				$.Event(e).stopPropagation();
				$('.pages-menu-edit-content', tab).each(function(){
					$(this).removeClass(options.activeItemClass);
					var li = $(this).parents('li:first');
					if(active_li.get(0) == li.get(0)) {
						return;
					}
					li.children('.pages-menu-edit-operation:first').hide();

					var a = $(this).children('a.pages-menu-edit-title:first');
					var input = $(this).children('input.pages-menu-edit-title:first');
					if(input.css('display') != 'none' && $.Common.escapeHTML(input.val()) == $.trim(a.html()) && !input.hasClass('error-input-message')) {
						input.css('display', 'none');
						a.css('display', '');
						//li.children('form:first').submit();
					}
				});
				content.addClass(options.activeItemClass);
				active_li.children('.pages-menu-edit-operation:first').show();
				var a = content.children('a.pages-menu-edit-title:first');
				var input = content.children('input.pages-menu-edit-title:first');
				if(!input.get(0)) {
					return;
				}
				input.css('display', '');
				a.css('display', 'none');
				if($(e.target).get(0).tagName != 'INPUT') {
					input.select();
				}
			});

			if(copy_page_id > 0) {
				$('.pages-menu-other-icon', $('#pages-menu-tab')).addClass('pages-menu-edit-highlight-icon');
			}
		} else {
			// コンテンツクリックイベント
			root_menu.on('click','.pages-menu-handle',function(e) {
				if($(e.target).get(0).tagName == 'A') {
					return;
				}
				var content = $(this);
				var active_li_buf = content.parents('li:first');
				var button = $('button[data-action]:visible:first', active_li_buf);
				if(button.get(0)) {
					button.click();
				}
			});
		}

		// その他操作
		var send_url, postfix_url;
		$('a[data-ajax-type=post][data-ajax=]', '#pages-menu-edit-other-operation').on('ajax:beforeSend', function(e, url) {
			var li = $(e.target).parent();
			if(li.attr('data-name') == 'cancel') {
				return url;
			}
			var list = $('#pages-menu-edit-other-operation');
			var copy_page_id = list.attr('data-copy-page-id'), page_id = list.attr('data-id');
			postfix_url = '/' + copy_page_id + '?page_id=' + page_id;
			send_url = url + postfix_url;
			if(copy_page_id == page_id && li.attr('data-name') == 'move') {
				$.PageMenu.closeOtherOperation();
				return false;
			}
			return send_url;
		}).on('ajax:success',function(e, res) {
			var li = $(e.target).parent();
			if(li.attr('data-name') == 'copy' || li.attr('data-name') == 'cancel') {
				$('#pages-menu-edit-other-operation').after(res);
				return;
			}
			var re_html = new RegExp("^<script>", 'i');
			if(!$.trim(res).match(re_html)) {
				var target = $('#pages-menu-edit-other-operation'), pos = $(target).offset();
				// 確認メッセージ表示
				var ok = __('Ok') ,cancel = __('Cancel');
				var default_params = {
					resizable: false,
		            modal: true,
			        position: [pos.left + 10 - $(window).scrollLeft() ,pos.top + 10 - $(window).scrollTop()]
				}, _buttons = {}, params = new Object();
				_buttons[ok] = function(){
					var shortcut_flag = $('#pages-menu-edit-confirm-shortcut');
					if(shortcut_flag.get(0) && shortcut_flag.is(':checked')) {
						params['shortcut_flag'] = 1;
					}

					params['is_confirm'] = 1;
					$( this ).remove();
					$.PageMenu.operationPage(send_url, postfix_url, params);
				};
				_buttons[cancel] = function(){
					$( this ).remove();
				};
				var dialog_params = $.extend({buttons: _buttons}, default_params);
				$('<div></div>').html(res).dialog(dialog_params);
			} else {
				$('#pages-menu-edit-other-operation').after(res);
			}
			$.PageMenu.closeOtherOperation();

			$.Event(e).preventDefault();
			$.Event(e).stopPropagation();
		});

	};
	$.PageMenu ={
		pageDetailInit : function(id, permalink_prohibition, permalink_prohibition_replace) {
			var form = $('#PagesMenuForm-' + id);
			var input = $('input.pages-menu-edit-title:first', form);
			var permalink = $('input#pages-menu-edit-permalink-' + id);
			var buf_name = input.val();
			var reg = new RegExp(permalink_prohibition , 'ig');

			// ページ名称と固定リンクが同じならば、ページ名称の変更で、固定リンクも変更。
			input.keyup(function(e) {
				var replace_name = buf_name.replace(reg, permalink_prohibition_replace);
				if(permalink.val() == replace_name) {
    				permalink.val($(this).val().replace(reg, permalink_prohibition_replace));
    			}
			});
			input.keydown(function(e) {
				buf_name = $(this).val();
			});
		},
		communityDetailInit : function(id, active_tab) {
			active_tab = (active_tab) ? active_tab : 0;
			var tab = $('#pages-menu-community-tab' + id);
			tab.tabs({
				active: active_tab,
				activate: function( event, ui ) {

				}
			});

			$('input[name="data[Community][participate_flag]"]', tab).change(function(e){
				var target = $(this);
				if(target.val() == '0') {
					$('#pages-menu-community-invite-authority-' + id + '-slider').slider( "disable" );
				} else {
					$('#pages-menu-community-invite-authority-' + id + '-slider').slider( "enable" );
				}
			});
		},

/**
 * コミュニティ写真サンプル変更
 * @param   element target
 * @param   element form
 * @param   string file_name
 * @return  void
 * @access  public
 */
		selectPhoto: function(id, target, file_name) {
			var form = $('#PagesMenuForm-' + id);
			var src = $(target).children(':first').attr("src");

			$('input[name="data[Community][photo]"]:first', form).val(file_name);
			$('input[name="data[Community][upload_id]"]:first', form).val("0");

			$('#pages-menu-community-photo-preview-' + id).css('background-image', "url(" + src + ")");
		},

/**
 * 参加者修正-全選択
 * @param   integer page_id
 * @param   integer def_authority_id
 * @param   element el button element
 * @return  void
 * @access  public
 */
		allChecked: function(page_id, def_authority_id, el) {
			var cell = $(el).parent();
			//var name = $(".pages-menu-auth-listbox-name", cell);
			var input = $("input:hidden:first", cell);
			var select = $("select:first", cell);

			$("input.pages-menu-auth-listbox-name-" + def_authority_id, $("#pages-menu-edit-participant-"+page_id)).each(function() {
				if(!$(this).attr('disabled')) {
					$(this).click().val(input.val());

					if(select.get(0)) {
						var list_name = $(this).parent().next();
						list_name.val(select.val());
					}

				}
			});
		},

/**
 * 参加者修正-権限セレクトボックス
 * @param   integer page_id
 * @param   integer def_authority_id
 * @param   element el button element
 * @return  void
 * @access  public
 */
		chgSelectAuth: function(page_id, el) {
			var authority_id = $(el).val();
			var input = $('input:hidden:first,input:radio:first',$(el).parent());
			input.val(authority_id);
		},

/**
 * 参加者修正- ページ編集画面表示時
 * @param   element top
 * @param   integer page_id
 * @return  boolean detail表示中かどうか
 */
		hideDetail : function(page_id) {
			var ret = false;
			var detail = $('#pages-menu-edit-detail-' + page_id);
			var participant = $('#pages-menu-edit-participant-' + page_id);
			if(participant.css('display') != 'none') {
				// 既に表示中->非表示
				participant.slideUp(300, function() {participant.css('display', 'none').html('');});
			}
			if(!detail.get(0)) {
				return true;
			}
			if(detail.css('display') != 'none') {
				// 既に編集中->非表示
				detail.slideUp(300, function() {detail.css('display', 'none').html('');});
				ret = true;
			}
			return ret;
		},

/**
 * その他操作Click
 * @param   Event e
 * @return  void
 */
		clkOtherOperation : function(e) {
			var a = $(e.target);
			var pos = a.offset();
			var list = $('#pages-menu-edit-other-operation');
			var offset_top = - 20,offset_left = - 5;
			var li = $(a).parents('li:first'), page_id = li.attr('data-id'), room_id = li.attr('data-room-id'),list_page_id = list.attr('data-id');
			var copy_page_id = list.attr('data-copy-page-id');
			var copy_is_top = list.attr('data-copy-is-top');
			var copy_space_type = list.attr('data-copy-space-type');
			var top = pos.top + offset_top - $(window).scrollTop();
			var top_dialog_top = $("#nc-pages-setting-dialog").position().top;
			var is_top = parseInt(li.attr('data-is-top'));
			var is_chief = parseInt(li.attr('data-is-chief'));
			var is_parent_chief = parseInt(li.attr('data-is-parent-chief'));
			var is_sel_modules = parseInt(a.attr('data-is-sel-modules'));
			var is_sel_members = parseInt(a.attr('data-is-sel-members'));
			var is_contents = parseInt(a.attr('data-is-sel-contents'));
			var space_type = li.attr('data-space-type');
			var modules = $('#pages-menu-edit-other-operation-modules');
			var members = $('#pages-menu-edit-other-operation-members');
			var add_members = $('#pages-menu-edit-other-operation-add-members');
			var unassign_members = $('#pages-menu-edit-other-operation-unassign-members');
			var contents = $('#pages-menu-edit-other-operation-contents');
			var copy = $('li[data-operation=copy]',list);
			var copy_after = $('li[data-operation=copy-after]',list);
			var copy_cancel = $('#pages-menu-edit-other-operation-copy-cancel');

			// モジュール利用許可表示切替
			//if(room_id != page_id || (is_top && (li.hasClass("pages-menu-handle-private") || li.hasClass("pages-menu-handle-myportal")))) {
			//	modules.hide();
			//} else {
			//	modules.show();
			//}

			// 参加者設定 - 修正
			//if(!$('#pages-menu-edit-participant-'+page_id).get(0)) {
			//	members.hide();
			//	add_members.hide();
			//} else {
				if(page_id != room_id) {
					members.hide();
					add_members.show();
				} else {
					members.show();
					add_members.hide();
				}
			//}

			// 参加者割り当て解除
			if(page_id == room_id && is_top == 0) {
				unassign_members.show();
			} else {
				unassign_members.hide();
			}

			if(!copy_page_id) {
				// コピー表示
				copy.show();
				copy_after.hide();
				copy_cancel.hide();
			} else {
				// 移動、ショートカット作成、ペースト表示
				copy.hide();
				copy_after.show();
				copy_cancel.show();
			}

			if(is_top && space_type != 4) {	// 固定値
				$('a:first', copy).addClass('disable-lbl');
			} else if(is_chief) {
				$('a:first', copy).removeClass('disable-lbl');
			} else {
				$('a:first', copy).addClass('disable-lbl');
			}
			if(copy_is_top != is_top) {
				$('a:first', copy_after).addClass('disable-lbl');
			} else if((!is_top || (space_type == 4 && space_type == copy_space_type)) && is_parent_chief) {	// 固定値
				$('a:first', copy_after).removeClass('disable-lbl');
			} else {
				$('a:first', copy_after).addClass('disable-lbl');
			}
			if(is_sel_members) {
				$('a:first', unassign_members).removeClass('disable-lbl');
				$('a:first', members).removeClass('disable-lbl');
				$('a:first', add_members).removeClass('disable-lbl');
			} else {
				$('a:first', unassign_members).addClass('disable-lbl');
				$('a:first', members).addClass('disable-lbl');
				$('a:first', add_members).addClass('disable-lbl');
			}
			if(is_sel_modules) {
				$('a:first', modules).removeClass('disable-lbl');
			} else {
				$('a:first', modules).addClass('disable-lbl');
			}
			if(is_contents) {
				$('a:first', contents).removeClass('disable-lbl');
			} else {
				$('a:first', contents).addClass('disable-lbl');
			}

			a.tooltip().tooltip('close');
			if(page_id != list_page_id) {
				list.hide();
			}
			if(top  + list.outerHeight() + top_dialog_top > $(window).height()) {
				// ウィンドウ幅をこえている
				top -= top  + list.outerHeight() + top_dialog_top - $(window).height();
			}
			var params = {
				'top': top,
				'left': pos.left + offset_left - $(window).scrollLeft(),
				'z-index': $.Common.zIndex++,
			}

			list.attr('data-id', page_id).css(params).toggle();
			$.Event(e).preventDefault();
			$.Event(e).stopPropagation();
		},

/**
 * その他操作 Copy Click
 * @param   Event e
 * @return  void
 */
		clkCopy : function(e) {
			var list = $('#pages-menu-edit-other-operation');
			var list_page_id = list.attr('data-id');
			var li = $('#pages-menu-edit-item-' + list_page_id);
			var copy_page_id = li.attr('data-id');
			var copy_space_type = li.attr('data-space-type');
			var copy_is_top = li.attr('data-is-top');
			var a = $('a.pages-menu-edit-title:first', li);
			if($(e.target).hasClass('disable-lbl')) {
				$.Event(e).preventDefault();
				return;
			}
			//list.attr('data-id', copy_page_id);
			list.attr('data-copy-page-id', copy_page_id);
			list.attr('data-copy-is-top', copy_is_top);
			list.attr('data-copy-space-type', copy_space_type);
			this.closeOtherOperation();

			$('.pages-menu-other-icon', $('#pages-menu-tab')).addClass('pages-menu-edit-highlight-icon');

			$('#pages-menu-edit-other-operation-title').html('['+$(e.target).html() + ']' + a.html());

			//$.Event(e).preventDefault();
		},
/**
 * その他操作 Cancel Click
 * @param   Event e
 * @return  void
 */
		clkCancel : function(e) {
			var list = $('#pages-menu-edit-other-operation');
			list.removeAttr('data-copy-page-id');
			this.closeOtherOperation();
			$('.pages-menu-other-icon', $('#pages-menu-tab')).removeClass('pages-menu-edit-highlight-icon');

			//e.preventDefault();
		},

/**
 * その他操作非表示
 * @param   void
 * @return  void
 */
		closeOtherOperation : function() {
			$('#pages-menu-edit-other-operation').hide();
		},

/**
 * プログレスバー表示
 * @param   check_url
 * @param   params
 * @return  timer
 */
		showProgressbar: function(check_url, params) {
			var progressbar_outer, progressbar_title, progressbar, timer;
			var timer, percent = 0;

			var overlay = $( "<div id='pages-menu-progressbar-overlay'>" ).addClass( "ui-widget-overlay" );
			overlay.appendTo( document.body ).css({
				width: $(document.body).width(),
				height: $(document.body).height(),
				zIndex: $.Common.zIndex++
			});

			progressbar_outer = $('<div id="pages-menu-progressbar-outer" style="display:none;"><div id="pages-menu-progressbar-title"></div><div id="pages-menu-progressbar"></div></div>').css('z-index', $.Common.zIndex++).appendTo($(document.body));
			progressbar_outer.position({
				my: "center",
				at: "center",
				of: window
			});
			progressbar_title = $('#pages-menu-progressbar-title');
			progressbar = $('#pages-menu-progressbar');

			progressbar.progressbar();

            function chgProgressbar(url, progressbar, progressbar_outer, progressbar_title) {
            	var percent = 0;
            	if(!$("#pages-menu-progressbar-title").get(0)) {
            		return;
            	}
            	$.ajax({
					type: "POST",
					dataType: 'json',
					url: check_url,
					async: false,
					success: function(res){
						if(res['current']) {
							progressbar_outer.show();
							progressbar_title.html(res['current'] + '/' + res['total']  + ' : ' + res['title']).show();
							percent = parseInt(res['percent']);
						}
					}
	 			});
	 			progressbar.progressbar("option", "value", percent);
	 			return percent;
            };
            // 最初は500ms、その後は2s毎にチェック
            setTimeout(function(){
            	percent = chgProgressbar(check_url, progressbar, progressbar_outer, progressbar_title);
            }, 500);

            timer = setInterval(function(){
            	percent = chgProgressbar(check_url, progressbar, progressbar_outer, progressbar_title);
            	if(percent >= 100) {
					clearInterval(timer);
					return;
                }
            }, 2000);

            return timer;
		},

/**
 * プログレスバー非表示
 * @param   timer
 * @return  void
 */
		hideProgressbar: function(timer) {
			var overlay = $('#pages-menu-progressbar-overlay');
			var progressbar_outer = $('#pages-menu-progressbar-outer');
			clearInterval(timer);
			overlay.remove();
			progressbar_outer.remove();
		},

/**
 * ページ操作
 * @param   url
 * @param   postfix_url
 * @param   params
 * @return  void
 */
		operationPage: function(url, postfix_url, params) {
			var list = $('#pages-menu-edit-other-operation');
			var page_id = list.attr('data-id');
			var check_url = list.attr('data-ajax-url');

			var timer = $.PageMenu.showProgressbar(check_url + postfix_url, params);

			$.post(url,
				params,
				function(res){
					var re_html = new RegExp("^<script>", 'i');
					//clearInterval(timer);
					//overlay.remove();
					//progressbar_outer.remove();
					$.PageMenu.hideProgressbar(timer);

					if(!$.trim(res).match(re_html)) {
						// error
						$.Common.showErrorDialog(res, null, $("#pages-menu-edit-item-" + page_id));
					} else {
						$('#pages-menu-edit-other-operation').after(res);
					}
				}
			);
		},

/**
 * ページメニューリロード
 * @param   integer page_id
 * @return  void
 */
		reload: function(page_id, is_edit) {
			var tab = $('#pages-menu-tab'), is_edit = (is_edit) ? is_edit : 1;
			$.get(tab.attr('data-ajax-url') + '?is_edit='+is_edit+'&page_id=' + page_id, function(res) {
				$('#nc-pages-setting-dialog').replaceWith(res);
			});
		}
	};
})(jQuery);