/**
 * ページメニュー js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
/**
 * ページメニュー表示時
 * @param   integer isEdit 編集モードかどうか
 * @param   integer activePageId 現在表示中のページID
 * @param   integer activeTab アクティブなタブのindex 0 or 1
 * @param   integer selActiveTab 選択中のページItemのあるタブのindex
 * @param   integer isPost Postリクエストかどうか。
 * @param   integer copyPageId
 * @param   integer participantPageId 参加者修正画面を表示するpage_id
 * @return  void
 * @since   v 3.0.0.0
 */
	$.fn.PageMenu = function(isEdit, activePageId, activeTab, selActiveTab, isPost, copyPageId, participantPageId) {
		// Privateメソッド

		// 表示件数
		var chgLimit = function() {
			$('#pages-menu-community-limit:visible').select2({
				minimumResultsForSearch:-1,
				width: 'element'
			}).change(function(e){
				var limitUrl = $(this).val();
				$.get(limitUrl, function(res) {
					$('#nc-pages-setting-dialog').replaceWith(res);
				});
			});
		};

		// ページ削除
		var dletePage = function(url, pageId, allDelete, params) {
			$.post(url, {'data[Page][id]': pageId, 'all_delete': allDelete, 'token': $('#pages-menu-token').val()},function(res){
				var re_html = new RegExp("^<script>", 'i');
				if(!$.trim(res).match(re_html)) {
					// error
					$.Common.showErrorDialog(res, params);
				} else {
					// success
					var li = $('#pages-menu-edit-item-' + pageId);
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

		var addEventNestable = function(activeTabName, rootMenu, target, pageId) {
			if(target.hasClass('pages-menu-edit-item')) {
				$(".pages-menu-edit-item" , target).add(target).each(function(){
					var child_target = $(this);
					if(activeTabName == '#pages-menu-page') {
						$(rootMenu.get(0)).nestable('addEvent', [child_target, pageId]);
					} else {
						$(rootMenu.get(1)).nestable('addEvent', [child_target, pageId]);
					}
				});
			}
		};

		var tab = this;
		if(!(isEdit)) {
			var activeLi = $('#pages-menu-item-' + activePageId);
		} else {
			var activeLi = $('#pages-menu-edit-item-' + activePageId);
		}
		var options = {
			active          : activePageId,
			activeItemClass : 'nc-highlight'
		};
		var activeTabName = null;

		if(activeTab == 0) {
			activeTabName = '#pages-menu-page';
		} else {
			activeTabName = '#pages-menu-community';
		}

		var rootMenu = $('#pages-menu-page, #pages-menu-community');
		if(!(isEdit)) {
			options['singleHandleClass'] = 'pages-menu-dd-single';
			options['contentClass'] = 'pages-menu-handle';
		} else {
			options['contentClass'] = 'dd-drag-content';
		}
		$('#pages-menu-tab').tabs({
			active: activeTab,
			activate: function( event, ui ) {
				// その他操作非表示
				$.PageMenu.closeOtherOperation();

				if(ui['newPanel'].attr('id') == 'pages-menu-page') {
					activeTab = 0;
					activeTabName = '#pages-menu-page';
				} else {
					activeTab = 1;
					activeTabName = '#pages-menu-community';
				}
				if(isEdit) {
					var isChief = activeLi.attr('data-is-chief');
					var add_community = $('#pages-menu-add-community-btn');
					if(selActiveTab != activeTab) {
						$('#pages-menu-add-btn').addClass('pages-menu-btn-disable');
					} else if(isChief) {
						$('#pages-menu-add-btn').removeClass('pages-menu-btn-disable');
					}
					if(add_community.get(0)) {
						if(activeTab == 0) {
							add_community.addClass('pages-menu-btn-disable');
						} else {
							add_community.removeClass('pages-menu-btn-disable');
						}
					}
				}

				if(activeTab == 1) {
					chgLimit();
				}
			}
		});

		chgLimit();

		// スクロール
		setTimeout(function(){
			$.PageMenu.slideTarget(activeLi, activeTabName);
			// コミュニティー検索focus
			var searchText = $('#nc-pages-setting-community-search-text');
			console.log(isPost);
			if(activeTab == 1 && searchText.get(0) && isPost) {
				searchText.select();
			}
		}, 500);

		// 言語切替
		var lang_sel =$('#pages-menu-language');
		if(lang_sel.get(0)) {
			lang_sel.select2({
				minimumResultsForSearch:-1,
				width: 'element'
			}).change( function(e){
				var lang = $(this).val();
				var url = lang + '?is_edit=' + isEdit + '&active_tab=' + activeTab;
				$.get(url, function(res) {
					$('#nc-pages-setting-dialog').replaceWith(res);
				});
			});
		}

		// ページ移動
		var result_response = '';
		rootMenu.nestable(options)
		.on('change', function(e, pageId, dropPageId, position) {
			if(!pageId || !dropPageId) {
				return;
			}
			// 表示順変更
			var url = $.Common.urlBlock(0, 'page/menus/chgsequence');
			var data = {'data[Page][id]': pageId, 'data[DropPage][id]': dropPageId, 'position': position, 'token': $('#pages-menu-token').val()};
			var ret = null;
			var li = $('#pages-menu-edit-item-' + dropPageId);
			var pos = li.offset(), _buttons = {};
			var defaultParams = {
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
								var checkUrl = list.attr('data-ajax-url') + '/' + pageId + '?page_id=' + dropPageId;
								var timer = $.PageMenu.showProgressbar(checkUrl, params);
								$.ajax({
									type: "POST",
									url: url,
									data: data,
									// async: false,
									success: function(res){
										result_response = res;
										$.PageMenu.hideProgressbar(timer);
										if($.trim(res).match(re_html)) {
											rootMenu.nestable('setStop', [true]);
										} else {
											$.Common.showErrorDialog(res, null, li);
											rootMenu.nestable('setStop', [false]);
										}
									}
								});
								$( this ).remove();
							};
							_buttons[cancel] = function(){
								$( this ).remove();
								rootMenu.nestable('setStop', [false]);
							};
							params = $.extend({buttons: _buttons}, defaultParams);
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
		}).on('success', function(e, pageId, dropPageId, position) {
			var params = new Object();
			params['data-ajax'] = '#pages-menu-edit-item-' + pageId;
			var target = $.Common.ajaxSuccess(e, null, result_response, params);
			addEventNestable(activeTabName, rootMenu, target, pageId);
			// コンテンツクリックイベント
			$('.pages-menu-edit-content:first', target).click();
		});

		// コミュニティーはすべて閉じた状態で表示
		$(rootMenu.get(1)).nestable('collapseAll', ['.nc-highlight:first']);

		// すべて展開
		$(rootMenu.get(0)).on('click','.pages-menu-expand-all',function(e) {
			$.PageMenu.closeOtherOperation();
			$(rootMenu.get(0)).nestable('expandAll', []);
		});
		$(rootMenu.get(1)).on('click','.pages-menu-expand-all',function(e) {
			$.PageMenu.closeOtherOperation();
			$(rootMenu.get(1)).nestable('expandAll', []);
		});

		// すべて折りたたむ
		$(rootMenu.get(0)).on('click','.pages-menu-collapse-all',function(e) {
			$.PageMenu.closeOtherOperation();
			$(rootMenu.get(0)).nestable('collapseAll', []);
		});
		$(rootMenu.get(1)).on('click','.pages-menu-collapse-all',function(e) {
			$.PageMenu.closeOtherOperation();
			$(rootMenu.get(1)).nestable('collapseAll', []);
		});

		//rootMenu.get(1).nestable('expandAll');
		// ページ編集切替
		$('#pages-menu-edit-btn').on('ajax:beforeSend', function(e, url) {
			return url + '&active_tab=' + activeTab;
		});
		if(isEdit) {
			// ページ追加
			$('#pages-menu-add-btn').on('ajax:beforeSend', function(e, url) {
				if($(this).hasClass('pages-menu-add-btn-disable')) {
					// ページ追加不可
					return false;
				}

				var ajax_id, pageEdit, replaceAjaxId, active, ddSequence, position, url, data = {};
				ajax_id = $(this).attr('data-ajax');
				replaceAjaxId = ajax_id.replace(/^#/, '');

				pageEdit = $("<li id='"+ replaceAjaxId +"'></li>").hide();
				activeLi.before(pageEdit);	// class='dd-item dd-drag-item'

				ddSequence = activeLi.attr('data-dd-sequence');
				position = 'bottom';
				if(ddSequence == 'inner-only') {
					position = 'inner';
				}
				url = url + '/' + activeLi.attr('data-id') + '/' +  position;
				data['token'] = $('#pages-menu-token').val();

				return {url : url, data: data};
			}).on('ajax:success', function(e, res) {
				var ddSequence, position, insert_li;
				var target = $.Common.ajaxSuccess(e, this, res);

				ddSequence = activeLi.attr('data-dd-sequence');
				position = 'bottom';
				if(ddSequence == 'inner-only') {
					position = 'inner';
					rootMenu.nestable('expandItem', [activeLi]);
				} else {
					rootMenu.nestable('collapseItem', [activeLi]);
				}
				insert_li = activeLi;
				if(!activeLi.get(0)) {
					insert_li = $('ol:first',$(activeTabName));
				}

				target.hide();
				if(activeTabName == '#pages-menu-page') {
					$(rootMenu.get(0)).nestable('appendList', [insert_li, target, position]);
				} else {
					$(rootMenu.get(1)).nestable('appendList', [insert_li, target, position]);
				}

				// スクロール
				$.PageMenu.slideTarget(target, activeTabName);
				// コンテンツクリックイベント
				$('.pages-menu-edit-content:first', target).click();
				if(copyPageId > 0) {
					$('.pages-menu-other-icon:first', target).addClass('pages-menu-edit-highlight-icon');
				}
				$.Event(e).preventDefault();
			});

			// コミュニティー追加
			$('#pages-menu-add-community-btn').on('ajax:beforeSend', function(e, url) {
				var url, data = {};
				if($(this).hasClass('pages-menu-btn-disable')) {
					// ページ追加不可
					return false;
				}
				url = url + '/' + activeLi.attr('data-id');
				data['token'] = $('#pages-menu-token').val();
				return {url : url, data: data};
			}).on('ajax:success', function(e, res) {
				if (res) {
					location.href = res;
				}
				$.Event(e).preventDefault();
			});

			// ページ削除
			rootMenu.on('ajax:beforeSend','.pages-menu-delete-icon',function(e, url) {
				var li = $($(e.target).attr('data-ajax-data')),pageId = li.attr('data-id'), roomId = li.attr('data-room-id');
				var isTop = li.attr('data-is-top');
				var a = $('a.pages-menu-edit-title:first', li), title = $.trim(a.html());
				var ok = __('Ok') ,cancel = __('Cancel');
				var pos = $(e.target).offset(), _buttons = {}, defaultParams = {
					title: (pageId == roomId) ? __d('pages', 'Delete room') : __d('pages', 'Delete page'),
	            	resizable: false,
	            	// height:180,
	            	modal: true,
		            position: [pos.left-150 - $(window).scrollLeft() ,pos.top-50 - $(window).scrollTop()]
				},params, body;
				_buttons[ok] = function(){
					if($('#pages-menu-all-delete').is(':checked')) {
						var _sub_buttons = {};
						_sub_buttons[ok] = function(){
							dletePage(url, pageId, 1, defaultParams);
							$(this).remove();
						};
						_sub_buttons[cancel] = function(){
							$(this).remove();
						};
						params = $.extend({buttons: _sub_buttons}, defaultParams);
						$('<div></div>').html(__d('pages', 'You can\'t be undone completely removed. <br />Are you sure you want to delete?'))
							.appendTo($(document.body)).dialog(params);
					} else {
						dletePage(url, pageId, 0, defaultParams);
					}
					$(this).remove();
				};
				_buttons[cancel]  = function(){
					$(this).remove();
				};
				params = $.extend({buttons: _buttons}, defaultParams);
				body = __('Deleting %s. <br />Are you sure to proceed?', title) + '<div><label for="pages-menu-all-delete">'+
							'<input id="pages-menu-all-delete" type="checkbox" name="all_delete" value="1" '+
							((isTop == '1' && pageId == roomId) ? 'disabled="disabled" checked="checked" ' : '') + '/>&nbsp;'+
							__d('pages', 'You completely delete it.')+
							'</label></div>';

				$('<div></div>').html(body).appendTo($(document.body)).dialog(params);
				return false;
			});

			// ページ編集画面表示・参加者修正画面表示・参加者解除
			rootMenu.add('#pages-menu-edit-other-operation').on('ajax:beforeSend','[data-page-edit-id]',function(e, url) {
				var link = $(e.target);
				if(!link.hasClass('pages-menu-edit-icon')) {
					// 参加者修正
					if(link.get(0).tagName != 'INPUT') {
						// その他操作内
						var list = $('#pages-menu-edit-other-operation');
						var listPageId = list.attr('data-id');

						url += '/' + listPageId;
						link.attr("data-page-edit-id", listPageId);
						if(link.parent().attr('id') == 'pages-menu-edit-other-operation-unassign-members') {
							// 参加者解除
							link.attr("data-ajax", "#pages-menu-edit-item-" + listPageId);
						} else {
							link.attr("data-ajax", "#pages-menu-edit-participant-" + listPageId);
						}
					}
					return url;
				}
			    var pageId = $(this).attr("data-page-edit-id");
			    if($.PageMenu.hideDetail(pageId)) {
					return false;
				}

				url = url + '/' + pageId;
				return url;
			}).on('ajax:success','[data-page-edit-id]',function(e, res) {
				var pageId = $(this).attr("data-page-edit-id");
				if(!$(e.target).hasClass('pages-menu-edit-icon')) {
					// 参加者修正
					$.PageMenu.hideDetail(pageId);
				}
				var target = $.Common.ajaxSuccess(e, this, res);
				var scrollTarget = $('#pages-menu-edit-item-' + pageId);
				$('.pages-menu-edit-content:first', scrollTarget).click();

				addEventNestable(activeTabName, rootMenu, target, pageId);


				// スクロール
				$.PageMenu.slideTarget(target, activeTabName, scrollTarget);
				$.Event(e).preventDefault();		// ajax:success後の$.Common.ajaxの後処理をキャンセル
				$.Event(e).stopPropagation();	// formのajax:successイベントキャンセル
			});

			// ページ編集・参加者修正登録
			rootMenu.on('ajax:beforeSend','form.pages-menu-edit-form',function(e, url) {
				var li = $(this).parent();
				if(li.get(0).tagName == 'LI') {
					var focusInput = $(':focus', $(e.target));
					if(focusInput.attr('type') == 'text' && focusInput.attr('name') != "data[Page][page_name]") {
						// ページ名称以外のtextではsubmitさせない。IE8、9ではOKボタンに遷移しているため、動作しない。
						return false;
					}

					var a = $('a.pages-menu-edit-title:first', $(this));
					var input = $('input.pages-menu-edit-title:first', $(this));
					var pageId = li.attr('data-id');
					var detail = $('#pages-menu-edit-detail-' + pageId);
					if((!detail.get(0) || detail.css('display') == 'none') && input.val() == $.trim(a.html()) && !input.hasClass('nc-error-input-message')) {
						// ページ名称変更なし
						input.css('display', 'none');
						a.css('display', '');
						return false;
					}
				}

			}).on('ajax:success','form.pages-menu-edit-form',function(e, res) {
				var re_html = new RegExp("^<script>", 'i');
				var li = $(this).parent();
				if(li.get(0).tagName != 'LI') {
					li = li.parents('li:first');
				}
				var pageId = li.attr('data-id');
				var reReloadHtml = new RegExp("^<div id=\"pages-menu-edit-participant-"+pageId+"\"", 'i');
				res = $.trim(res);
				if($(this).attr('data-name') == 'participant' && res != '' && !res.match(re_html)) {
					if(res.match(reReloadHtml)) {
						// reload
						$("#pages-menu-edit-participant-" + pageId).replaceWith(res);
					} else {
						$.Common.showErrorDialog(res);
					}
					return false;
				}
				var target = $.Common.ajaxSuccess(e, this, res);

				activeLi = target;
				addEventNestable(activeTabName, rootMenu, target, pageId);
				$.Event(e).preventDefault();
			});

			// ページ公開・非公開
			rootMenu.on('click','.pages-menu-display-flag',function(e) {
				var li = $(this).parents('li:first');
				var parentli = li.parents('li:first');
				if(parentli.get(0)) {
					var parentDisplay = $('[name=data\\[Page\\]\\[display_flag\\]]:first', parentli);
					if(parentDisplay.get(0) && parentDisplay.val() == '0') {
						return false;
					}
				}

				var pageId = li.attr('data-id');
				var setDisplay = null;
				var url = $.Common.urlBlock(0, 'page/menus/display');
				$.Event(e).preventDefault();
				$.Event(e).stopPropagation();

				$('[name=data\\[Page\\]\\[display_flag\\]]', li).each(function(){
					var display = $(this);
					var a = display.prev(),img,alt, preAlt;

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
						preAlt = img.attr('alt');
						img.attr('alt', alt);
						img.parent().attr('title', alt);
						img.attr('data-alt', preAlt);
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
				$.post(url, {'data[Page][id]': pageId, 'data[Page][display_flag]': setDisplay, 'token': $('#pages-menu-token').val()});
			});

			// コンテンツクリックイベント
			rootMenu.on('click','.pages-menu-edit-content',function(e) {
				var content = $(this);
				activeLi = content.parents('li:first');

				var button = $('button[data-action]:visible:first', activeLi);
				if(button.get(0)) {
					button.click();
				}

				// その他操作非表示
				$.PageMenu.closeOtherOperation();

				// ページ追加disable
				var isChief = activeLi.attr('data-is-chief');
				var ddSequence = activeLi.attr('data-dd-sequence');
				if(ddSequence != 'inner-only') {
					var parent_li = activeLi.parents('li');
					if(parent_li.get(0)) {
						isChief = activeLi.attr('data-is-chief');
					}
				}

				if(!isChief) {
					$('#pages-menu-add-btn').addClass('pages-menu-btn-disable');
				} else {
					$('#pages-menu-add-btn').removeClass('pages-menu-btn-disable');
				}
				selActiveTab = activeTab;

				$.Event(e).preventDefault();
				$.Event(e).stopPropagation();
				$('.pages-menu-edit-content', tab).each(function(){
					$(this).removeClass(options.activeItemClass);
					var li = $(this).parents('li:first');
					if(activeLi.get(0) == li.get(0)) {
						return;
					}
					li.children('.pages-menu-edit-operation:first').hide();

					var a = $(this).children('a.pages-menu-edit-title:first');
					var input = $(this).children('input.pages-menu-edit-title:first');
					if(input.css('display') != 'none' && $.Common.escapeHTML(input.val()) == $.trim(a.html()) && !input.hasClass('nc-error-input-message')) {
						input.css('display', 'none');
						a.css('display', '');
						//li.children('form:first').submit();
					}
				});
				content.addClass(options.activeItemClass);
				activeLi.children('.pages-menu-edit-operation:first').show();
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

			if(copyPageId > 0) {
				$('.pages-menu-other-icon', $('#pages-menu-tab')).addClass('pages-menu-edit-highlight-icon');
			}
		} else {
			// コンテンツクリックイベント
			rootMenu.on('click','.pages-menu-handle',function(e) {
				if($(e.target).get(0).tagName == 'A') {
					return;
				}
				var content = $(this);
				var activeLiBuf = content.parents('li:first');
				var button = $('button[data-action]:visible:first', activeLiBuf);
				if(button.get(0)) {
					button.click();
				}
			});
		}

		// その他操作
		var sendUrl, postfixUrl;
		$('a[data-ajax-type=post][data-ajax=]', '#pages-menu-edit-other-operation').on('ajax:beforeSend', function(e, url) {
			var li = $(e.target).parent();
			if(li.attr('data-name') == 'cancel') {
				return url;
			}
			var list = $('#pages-menu-edit-other-operation');
			var copyPageId = list.attr('data-copy-page-id'), pageId = list.attr('data-id');
			postfixUrl = '/' + copyPageId + '?page_id=' + pageId;
			sendUrl = url + postfixUrl;
			if(copyPageId == pageId && li.attr('data-name') == 'move') {
				$.PageMenu.closeOtherOperation();
				return false;
			}
			return sendUrl;
		}).on('ajax:success',function(e, res) {
			var li = $(e.target).parent();
			if(li.attr('data-name') == 'copy' || li.attr('data-name') == 'cancel') {
				$('#pages-menu-edit-other-operation').after(res);
				return;
			}
			var re_html = new RegExp("^<script>", 'i');
			if(!$.trim(res).match(re_html)) {
				re_html = new RegExp("^<div class=\"pages-menu-edit-confirm-desc\">", 'i');
				if(!$.trim(res).match(re_html)) {
					var list = $('#pages-menu-edit-other-operation');
					var pageId = list.attr('data-id');
					$.Common.showErrorDialog(res, null, $("#pages-menu-edit-item-" + pageId));
				} else {
					var target = $('#pages-menu-edit-other-operation'), pos = $(target).offset();
					// 確認メッセージ表示
					var ok = __('Ok') ,cancel = __('Cancel');
					var defaultParams = {
						resizable: false,
			            modal: true,
				        position: [pos.left + 10 - $(window).scrollLeft() ,pos.top + 10 - $(window).scrollTop()]
					}, _buttons = {}, params = new Object();
					_buttons[ok] = function(){
						var shortcut_type = $('#pages-menu-edit-confirm-shortcut');
						if(shortcut_type.get(0) && shortcut_type.is(':checked')) {
							params['shortcut_type'] = 1;
						}

						params['is_confirm'] = 1;
						$( this ).remove();
						$.PageMenu.operationPage(sendUrl, postfixUrl, params);
					};
					_buttons[cancel] = function(){
						$( this ).remove();
					};
					var dialogParams = $.extend({buttons: _buttons}, defaultParams);
					$('<div></div>').html(res).dialog(dialogParams);
				}
			} else {
				$('#pages-menu-edit-other-operation').after(res);
			}
			$.PageMenu.closeOtherOperation();

			$.Event(e).preventDefault();
			$.Event(e).stopPropagation();
		});

		if(participantPageId) {
			// 編集->参加者修正ボタンによるSubmitの場合、参加者修正画面に遷移。
			$.PageMenu.showEntryMembers(participantPageId);
		}
	};
	$.PageMenu ={
		pageDetailInit : function(id, permalink_prohibition, permalink_prohibition_replace) {
			var form = $('#PagesMenuForm-' + id);
			var input = $('input.pages-menu-edit-title:first', form);
			var permalink = $('input#pages-menu-edit-permalink-' + id);
			var bufName = input.val();
			var reg = new RegExp(permalink_prohibition , 'ig');

			// ページ名称と固定リンクが同じならば、ページ名称の変更で、固定リンクも変更。
			var changepermalink = function(el) {
				var replaceName = bufName.replace(reg, permalink_prohibition_replace);
				if(permalink.val() == replaceName) {
					permalink.val($(el).val().replace(reg, permalink_prohibition_replace));
				}
			}
			input.change(function(e) {
				changepermalink(this);
			});
			input.keyup(function(e) {
				changepermalink(this);
			});
			input.keydown(function(e) {
				bufName = $(this).val();
			});
		},

/**
 * コミュニティー初期処理
 * @param   integer id pageId
 * @param   integer activeTab ａｃｔｉｖｅなタブをセット:default 0
 * @return  void
 */
		communityDetailInit : function(id, activeTab) {
			activeTab = (activeTab) ? activeTab : 0;
			var tab = $('#pages-menu-community-tab' + id);
			var form = tab.parents('form:first');
			tab.tabs({
				active: activeTab,
				activate: function( event, ui ) {

				}
			});

			$('input[name="data[Community][participate_flag]"]', tab).change(function(e){
				var target = $(this);
				var participateForce = $('#pages-menu-community-participate-force-outer-' + id);
				if(target.val() == '0') {
					$('#pages-menu-community-invite-authority-' + id + '-slider').slider( "disable" );
					participateForce.slideDown("slow", function() {participateForce.effect("highlight");});
				} else {
					$('#pages-menu-community-invite-authority-' + id + '-slider').slider( "enable" );
					participateForce.slideUp();
				}

			});

//			$('input[name="data[Community][publication_range_flag]"]', tab).change(function(e){
//				var target = $(this);
//				var participateForce = $('#pages-menu-community-participate-force-outer-' + id);
//				if(target.val() == '0') {
//					participateForce.slideUp();
//				} else {
//					participateForce.slideDown("slow", function() {participateForce.effect("highlight");});
//				}
//			});

			$('#pages-menu-community-detail-setting-' + id).click(function(e){
				var target = $('#pages-menu-community-detail-' + id);
				if(!target.is(':visible')) {
					$.PageMenu.slideTarget($('#pages-menu-community-detail-' + id), $('#pages-menu-community'));
				} else {
					target.slideUp(300);
				}
				$.Event(e).preventDefault();
				$.Event(e).stopPropagation();
			});

			$('textarea[name="data[Revision][content]"]', tab).nc_wysiwyg({
				autoRegistForm : form,
				plugin : 'upload',
				image : true,
				file : true
			});
		},

/**
 * ページ行Initial
 * @param   integer pageId
 * @param   string  preUrl
 * @param   string  url
 * @param   boolean isParticipant 参加者修正へ遷移するかどうか
 * @return  void
 * @since   v 3.0.0.0
 */
		itemInit: function(pageId, preUrl, url, isParticipant) {
			var reUrl = new RegExp("^"+ $.Common.quote(preUrl) , 'i');
			var replaceUrl = location.href.replace(reUrl,'');
			var isParticipant = '';
			if(replaceUrl != location.href && (replaceUrl == '' || replaceUrl.substr(0, 1) == '/' || replaceUrl.substr(0, 1) == '?')) {
				// コミュニティーを追加直後に固定リンクを変更した場合、再描画。
				if(isParticipant) {
					isParticipant = '&participant_page_id=' + pageId;
				}
				location.href = url + '?is_edit=1' + isParticipant;
			} else if(isParticipant) {
				// 編集->参加者修正ボタンによるSubmitの場合、参加者修正画面に遷移。
				$.PageMenu.showEntryMembers(pageId);
			}
		},

/**
 * 参加者修正画面遷移
 * @param   integer pageId
 * @return  void
 * @since   v 3.0.0.0
 */
		showEntryMembers: function(pageId) {
			var content = $('.pages-menu-edit-content:first', $('#pages-menu-edit-item-' + pageId));
			var list = $('#pages-menu-edit-other-operation');
			list.attr('data-id', pageId);
			if(!content.hasClass('nc-highlight')) {
				content.click();
				var timer = setInterval(function(){
					if(content.hasClass('nc-highlight')) {
						$('a:first', $('#pages-menu-edit-other-operation-add-members')).click();
						clearInterval(timer);
					}
				} , 200);
			} else {
				$('a:first', $('#pages-menu-edit-other-operation-add-members')).click();
			}
		},
/**
 * 参加者修正-全選択
 * @param   integer pageId
 * @param   integer defAuthorityId
 * @param   element el button element
 * @return  void
 * @since   v 3.0.0.0
 */
		allChecked: function(pageId, defAuthorityId, el) {
			var cell = $(el).parent();
			//var name = $(".pages-menu-auth-listbox-name", cell);
			var input = $("input:hidden:first", cell);
			var select = $("select:first", cell);

			$("input.pages-menu-auth-listbox-name-" + defAuthorityId, $("#pages-menu-edit-participant-"+pageId)).each(function() {
				if(!$(this).attr('disabled')) {
					$(this).click().val(input.val());

					if(select.get(0)) {
						var listName = $(this).parent().next();
						if(!listName.attr('disabled')) {
							listName.val(select.val());
						}
					}

				}
			});
		},

/**
 * 参加者修正-権限セレクトボックス
 * @param   element el
 * @return  void
 * @since   v 3.0.0.0
 */
		chgSelectAuth: function(el) {
			var authorityId = $(el).val();
			var input = $('input:hidden:first,input:radio:first',$(el).parent());
			input.val(authorityId);
		},

/**
 * 参加者修正- ページ編集画面表示時
 * @param   element top
 * @param   integer pageId
 * @return  boolean detail表示中かどうか
 */
		hideDetail : function(pageId) {
			var ret = false;
			var detail = $('#pages-menu-edit-detail-' + pageId);
			var participant = $('#pages-menu-edit-participant-' + pageId);
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
			var offsetTop = - 20,offset_left = - 5;
			var li = $(a).parents('li:first'), pageId = li.attr('data-id'), roomId = li.attr('data-room-id'),listPageId = list.attr('data-id');
			var copyPageId = list.attr('data-copy-page-id');
			var copyIsTop = list.attr('data-copy-is-top');
			var copySpaceType = list.attr('data-copy-space-type');
			var top = pos.top + offsetTop - $(window).scrollTop();
			var topDialogTop = $("#nc-pages-setting-dialog").position().top;
			var isTop = parseInt(li.attr('data-is-top'));
			var isChief = parseInt(li.attr('data-is-chief'));
			var isParentChief = parseInt(li.attr('data-is-parent-chief'));
			var isSelModules = parseInt(a.attr('data-is-sel-modules'));
			var isSelMembers = parseInt(a.attr('data-is-sel-members'));
			var isContents = parseInt(a.attr('data-is-sel-contents'));
			var spaceType = li.attr('data-space-type');
			var modules = $('#pages-menu-edit-other-operation-modules');
			var members = $('#pages-menu-edit-other-operation-members');
			var addMembers = $('#pages-menu-edit-other-operation-add-members');
			var unassignMembers = $('#pages-menu-edit-other-operation-unassign-members');
			var contents = $('#pages-menu-edit-other-operation-contents');
			var copy = $('li[data-operation=copy]',list);
			var copyAfter = $('li[data-operation=copy-after]',list);
			var copyCancel = $('#pages-menu-edit-other-operation-copy-cancel');

			// モジュール利用許可表示切替
			//if(roomId != pageId || (isTop && (li.hasClass("pages-menu-handle-private") || li.hasClass("pages-menu-handle-myportal")))) {
			//	modules.hide();
			//} else {
			//	modules.show();
			//}

			// 参加者設定 - 修正
			//if(!$('#pages-menu-edit-participant-'+pageId).get(0)) {
			//	members.hide();
			//	addMembers.hide();
			//} else {
				if(pageId != roomId) {
					members.hide();
					addMembers.show();
				} else {
					members.show();
					addMembers.hide();
				}
			//}

			// 参加者割り当て解除
			if(pageId == roomId && isTop == 0) {
				unassignMembers.show();
			} else {
				unassignMembers.hide();
			}

			if(!copyPageId) {
				// コピー表示
				copy.show();
				copyAfter.hide();
				copyCancel.hide();
			} else {
				// 移動、ショートカット作成、ペースト表示
				copy.hide();
				copyAfter.show();
				copyCancel.show();
			}

			if(isTop && spaceType != 4) {	// 固定値
				$('a:first', copy).addClass('nc-disable-lbl');
			} else if(isChief) {
				$('a:first', copy).removeClass('nc-disable-lbl');
			} else {
				$('a:first', copy).addClass('nc-disable-lbl');
			}
			if(copyIsTop != isTop) {
				$('a:first', copyAfter).addClass('nc-disable-lbl');
			} else if((!isTop || (spaceType == 4 && spaceType == copySpaceType)) && isParentChief) {	// 固定値
				$('a:first', copyAfter).removeClass('nc-disable-lbl');
			} else {
				$('a:first', copyAfter).addClass('nc-disable-lbl');
			}
			if(isSelMembers) {
				$('a:first', unassignMembers).removeClass('nc-disable-lbl');
				$('a:first', members).removeClass('nc-disable-lbl');
				$('a:first', addMembers).removeClass('nc-disable-lbl');
			} else {
				$('a:first', unassignMembers).addClass('nc-disable-lbl');
				$('a:first', members).addClass('nc-disable-lbl');
				$('a:first', addMembers).addClass('nc-disable-lbl');
			}
			if(isSelModules) {
				$('a:first', modules).removeClass('nc-disable-lbl');
			} else {
				$('a:first', modules).addClass('nc-disable-lbl');
			}
			if(isContents) {
				$('a:first', contents).removeClass('nc-disable-lbl');
			} else {
				$('a:first', contents).addClass('nc-disable-lbl');
			}

			a.tooltip().tooltip('close');
			if(pageId != listPageId) {
				list.hide();
			}
			if(top  + list.outerHeight() + topDialogTop > $(window).height()) {
				// ウィンドウ幅をこえている
				top -= top  + list.outerHeight() + topDialogTop - $(window).height();
			}
			var params = {
				'top': top,
				'left': pos.left + offset_left - $(window).scrollLeft(),
				'z-index': $.Common.zIndex++,
			}

			list.attr('data-id', pageId).css(params).toggle();
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
			var listPageId = list.attr('data-id');
			var li = $('#pages-menu-edit-item-' + listPageId);
			var copyPageId = li.attr('data-id');
			var copySpaceType = li.attr('data-space-type');
			var copyIsTop = li.attr('data-is-top');
			var a = $('a.pages-menu-edit-title:first', li);
			if($(e.target).hasClass('nc-disable-lbl')) {
				$.Event(e).preventDefault();
				return;
			}
			//list.attr('data-id', copyPageId);
			list.attr('data-copy-page-id', copyPageId);
			list.attr('data-copy-is-top', copyIsTop);
			list.attr('data-copy-space-type', copySpaceType);
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
 * @param   checkUrl
 * @param   params
 * @return  timer
 */
		showProgressbar: function(checkUrl, params) {
			var progressbarOuter, progressbarTitle, progressbar, timer;
			var timer, percent = 0;

			var overlay = $( "<div id='pages-menu-progressbar-overlay'>" ).addClass( "ui-widget-overlay" );
			overlay.appendTo( document.body ).css({
				width: $(document.body).width(),
				height: $(document.body).height(),
				zIndex: $.Common.zIndex++
			});

			progressbarOuter = $('<div id="pages-menu-progressbar-outer" style="display:none;"><div id="pages-menu-progressbar-title"></div><div id="pages-menu-progressbar"></div></div>').css('z-index', $.Common.zIndex++).appendTo($(document.body));
			progressbarOuter.position({
				my: "center",
				at: "center",
				of: window
			});
			progressbarTitle = $('#pages-menu-progressbar-title');
			progressbar = $('#pages-menu-progressbar');

			progressbar.progressbar();

            function chgProgressbar(url, progressbar, progressbarOuter, progressbarTitle) {
            	var percent = 0;
            	if(!$("#pages-menu-progressbar-title").get(0)) {
            		return;
            	}
            	$.ajax({
					type: "POST",
					dataType: 'json',
					url: checkUrl,
					async: false,
					success: function(res){
						if(res['current']) {
							progressbarOuter.show();
							progressbarTitle.html(res['current'] + '/' + res['total']  + ' : ' + res['title']).show();
							percent = parseInt(res['percent']);
						}
					}
	 			});
	 			progressbar.progressbar("option", "value", percent);
	 			return percent;
            };
            // 最初は500ms、その後は2s毎にチェック
            setTimeout(function(){
            	percent = chgProgressbar(checkUrl, progressbar, progressbarOuter, progressbarTitle);
            }, 500);

            timer = setInterval(function(){
            	percent = chgProgressbar(checkUrl, progressbar, progressbarOuter, progressbarTitle);
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
			var progressbarOuter = $('#pages-menu-progressbar-outer');
			clearInterval(timer);
			overlay.remove();
			progressbarOuter.remove();
		},

/**
 * ページ操作
 * @param   url
 * @param   postfixUrl
 * @param   params
 * @return  void
 */
		operationPage: function(url, postfixUrl, params) {
			var list = $('#pages-menu-edit-other-operation');
			var pageId = list.attr('data-id');
			var checkUrl = list.attr('data-ajax-url');

			var timer = $.PageMenu.showProgressbar(checkUrl + postfixUrl, params);

			$.post(url,
				params,
				function(res){
					var re_html = new RegExp("^<script>", 'i');
					//clearInterval(timer);
					//overlay.remove();
					//progressbarOuter.remove();
					$.PageMenu.hideProgressbar(timer);

					if(!$.trim(res).match(re_html)) {
						// error
						$.Common.showErrorDialog(res, null, $("#pages-menu-edit-item-" + pageId));
					} else {
						$('#pages-menu-edit-other-operation').after(res);
					}
				}
			);
		},

/**
 * ページメニューリロード
 * @param   integer pageId
 * @return  void
 */
		reload: function(pageId, isEdit) {
			var tab = $('#pages-menu-tab'), isEdit = (isEdit) ? isEdit : 1;
			$.get(tab.attr('data-ajax-url') + '?is_edit='+isEdit+'&page_id=' + pageId, function(res) {
				$('#nc-pages-setting-dialog').replaceWith(res);
			});
		},

/**
 * コミュニティ写真サンプル変更、コミュニティーカスタムの画像のファイル選択コールバック
 * @param   integer pageId
 * @param   boolean isUpload アップロードされた画像かどうか。
 * @param   string  fileName
 * @param   string  url
 * @param   string  libraryUrl
 * @return  void
 */
		selectCommunityFile: function(pageId, isUpload, fileName, url, libraryUrl) {
			var img = $('#pages-menu-community-photo-' + pageId);

			var reFileName = new RegExp("^([0-9]+)\.(.+)$", 'i');
			if(isUpload && fileName.match(reFileName)) {
				fileName = RegExp.$1 + '_library.' + RegExp.$2;
			}

			libraryUrl = (typeof libraryUrl == "undefined") ? url : libraryUrl;

			img.attr('src', libraryUrl);
			$('#pages-menu-community-photo-hidden-' + pageId).val(fileName);
			$('#pages-menu-community-is-upload-hidden-' + pageId).val(isUpload);
		},

/**
 * 画面スライド
 * @param   element target
 * @param   element activeTabName
 * @param   element  scrollTarget
 * @return  void
 */
		slideTarget: function(target, activeTabName, scrollTarget) {
			scrollTarget = (scrollTarget) ? scrollTarget : target;
			target.slideDown(300, function() {
				var position = scrollTarget.offset().top+$(activeTabName).scrollTop() - $(activeTabName).offset().top;
				// ページ名称にfocus
				var page_name = $('[name=data\\[Page\\]\\[page_name\\]]:visible:first', target);
				if(page_name.get(0)) {
					page_name.select();
					// スクロール
					$(activeTabName).animate({scrollTop:position}, 400, 'swing');
				} else {
					// スクロール 参加者修正の場合、スクロールにdelayをつけて実行
					$(activeTabName).delay(500).animate({scrollTop:position}, 400, 'swing');
				}

			});
		}
	};
})(jQuery);