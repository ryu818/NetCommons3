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
	$.fn.PageMenu = function(is_edit, active_page_id, active_tab, sel_active_tab) {
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

		var chgSequenceSuccess = function(res, page_id) {
			var detail = $('#pages-menu-edit-detail-' + page_id);
			if(detail.css('display') != 'none') {
				// 表示順を変更したときに、詳細部分を閉じる->親と子の（固定リンクが変更されるかもしれないため）
				detail.slideUp(300, function() {detail.html('');});
			}
			$(res).appendTo($(document.body));
		};

		var slideTarget = function(target, active_tab_name, scroll_target) {
			scroll_target = (scroll_target) ? scroll_target : target;
			target.slideDown(300, function() {
				var position = scroll_target.offset().top+$(active_tab_name).scrollTop() - $(active_tab_name).offset().top;
				// ページ名称にfocus
				var page_name = $('[name=data\\[Page\\]\\[page_name\\]]:visible:first', target);
				if(page_name.get(0)) {
					page_name.select();
				}
				// スクロール
				$(active_tab_name).animate({scrollTop:position}, 400, 'swing');
			});
		}

		var tab = this;

		var active_li = $('#pages-menu-edit-item-' + active_page_id);
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
				if(ui['newPanel'].attr('id') == 'pages-menu-page') {
					active_tab = 0;
					active_tab_name = '#pages-menu-page';
				} else {
					active_tab = 1;
					active_tab_name = '#pages-menu-community';
				}
				if(is_edit) {
					var is_chief = active_li.data('is-chief');
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
				var url = $(this).data('ajax-url') + '/' + lang + '?is_edit=' + is_edit + '&active_tab=' + active_tab;
				$.get(url, function(res) {
					$('#nc-pages-setting-dialog').replaceWith(res);
				});
			} );
		}

		// ページ移動
		root_menu.nestable(options)
		.on('change', function(e, page_id, drop_page_id, position) {
			if(!page_id || !drop_page_id) {
				return;
			}
			// 表示順変更
			var url = $.Common.urlBlock(0, 'page/menu/chgsequence');
			var data = {'data[Page][id]': page_id, 'data[DropPage][id]': drop_page_id, 'position': position};
			var ret = null;
			var li = $('#pages-menu-edit-item-' + drop_page_id);
			var pos = li.offset(), _buttons = {};
			var default_params = {
				resizable: false,
	            modal: true,
		        position: [pos.left + 30 - $(window).scrollLeft() ,pos.top + 30 - $(window).scrollTop()]
			}
			$.ajax({
				type: "POST",
				url: url,
				data: data,
				async: false,
				success: function(res){
					var re_html = new RegExp("^<script>", 'i');
					var ok = __('Ok') ,cancel = __('Cancel'), body, params;
					if(!$.trim(res).match(re_html)) {
						var re_info_html = new RegExp("^<div>", 'i');
						if($.trim(res).match(re_info_html)) {
							// confirm
							_buttons[ok] = function(){
								data['is_confirm'] = 1;
								$.ajax({
									type: "POST",
									url: url,
									data: data,
									// async: false,
									success: function(res){
										if($.trim(res).match(re_html)) {
											chgSequenceSuccess(res, page_id);
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
						chgSequenceSuccess(res, page_id);
						ret = true;
					}
				}
 			});
 			return ret;
		});

		// コミュニティーはすべて閉じた状態で表示
		$(root_menu.get(1)).nestable('collapseAll', ['.highlight:first']);

		// すべて展開
		$(root_menu.get(0)).on('click','.pages-menu-expand-all',function(e) {
			$(root_menu.get(0)).nestable('expandAll', []);
		});
		$(root_menu.get(1)).on('click','.pages-menu-expand-all',function(e) {
			$(root_menu.get(1)).nestable('expandAll', []);
		});

		// すべて折りたたむ
		$(root_menu.get(0)).on('click','.pages-menu-collapse-all',function(e) {
			$(root_menu.get(0)).nestable('collapseAll', []);
		});
		$(root_menu.get(1)).on('click','.pages-menu-collapse-all',function(e) {
			$(root_menu.get(1)).nestable('collapseAll', []);
		});

		//root_menu.get(1).nestable('expandAll');
		// ページ編集切替
		$('#pages-menu-edit-btn').on('ajax:before', function(e, url) {
			return url + '&active_tab=' + active_tab;
		});
		if(is_edit) {
			// ページ追加
			$('#pages-menu-add-btn').on('ajax:before', function(e, url) {
				if($(this).hasClass('pages-menu-add-btn-disable')) {
					// ページ追加不可
					return false;
				}

				var ajax_id, page_edit, replace_ajax_id, active, dd_sequence, position;
				ajax_id = $(this).data('ajax-replace');
				replace_ajax_id = ajax_id.replace(/^#/, '');

				page_edit = $("<li id='"+ replace_ajax_id +"'></li>").hide().before(active_li);	// class='dd-item dd-drag-item'

				dd_sequence = active_li.data('dd-sequence');
				position = 'bottom';
				if(dd_sequence == 'inner-only') {
					position = 'inner';
				}
				url = url + '/' + active_li.data('id') + '/' +  position;

				return url;
			}).on('ajax:success', function(e, target) {
				dd_sequence = active_li.data('dd-sequence');
				position = 'bottom';
				if(dd_sequence == 'inner-only') {
					position = 'inner';
					root_menu.nestable('expandItem', [active_li]);
				} else {
					root_menu.nestable('collapseItem', [active_li]);
				}
				var insert_li = active_li;
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
			});

			// コミュニティー追加
			$('#pages-menu-add-community-btn').on('ajax:before', function(e, url) {
				if($(this).hasClass('pages-menu-btn-disable')) {
					// ページ追加不可
					return false;
				}
				url = url + '/' + active_li.data('id');
				return url;
			}).on('ajax:beforeSuccess', function(e, res) {
				if (res) {
					location.href = res;
				}
				e.preventDefault();
			});

			// ページ削除
			root_menu.on('ajax:before','.pages-menu-delete-icon',function(e, url) {
				var li = $($(e.target).data('ajax-data')),page_id = li.data('id'), room_id = li.data('room-id');
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
						$('<div></div>').html(__d('pages', 'I can\'t be undone completely removed. <br />Are you sure you want to delete?'))
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
							((page_id == room_id) ? 'disabled="disabled" checked="checked" ' : '') + '/>&nbsp;'+
							__d('pages', 'I completely delete it.')+
							'</label></div>';

				$('<div></div>').html(body).appendTo($(document.body)).dialog(params);
				return false;
			});

			// ページ詳細編集画面表示
			root_menu.on('ajax:before','[data-page-edit-id]',function(e, url) {
			    var page_id = $(this).data('page-edit-id');
				var detail = $('#pages-menu-edit-detail-' + page_id);
				if(detail.css('display') != 'none') {
					// 既に編集中->非表示
					detail.slideUp(300, function() {detail.html('');});
					return false;
				}

				url = url + '/' + page_id;
				return url;
			}).on('ajax:success','[data-page-edit-id]',function(e, target) {
				var page_id = $(this).data('page-edit-id');
				var scroll_target = $('#pages-menu-edit-item-' + page_id);
				// スクロール
				slideTarget(target, active_tab_name, scroll_target);
			});

			// ページ編集
			root_menu.on('ajax:before','form',function(e, url) {
				var focus_input = $(':focus', $(e.target));
				if(focus_input.attr('type') == 'text' && focus_input.attr('name') != "data[Page][page_name]") {
					// ページ名称以外のtextではsubmitさせない。IE8、9ではOKボタンに遷移しているため、動作しない。
					return false;
				}
				var li = $(this).parent();
				var a = $('a.pages-menu-edit-title:first', $(this));
				var input = $('input.pages-menu-edit-title:first', $(this));
				var page_id = li.data('id');
				var detail = $('#pages-menu-edit-detail-' + page_id);
				if(detail.css('display') == 'none' && input.val() == $.trim(a.html()) && !input.hasClass('error-input-message')) {
					// ページ名称変更なし
					input.css('display', 'none');
					a.css('display', '');
					return false;
				}
			}).on('ajax:success','form',function(e, target) {
				var li = $(this).parent();
				var page_id = li.data('id');
				if(active_tab_name == '#pages-menu-page') {
					$(root_menu.get(0)).nestable('addEvent', [target, page_id]);
				} else {
					$(root_menu.get(1)).nestable('addEvent', [target, page_id]);
				}
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

				var page_id = li.data('id');
				var setDisplay = null;
				var url = $.Common.urlBlock(0, 'page/menu/display');
				e.preventDefault();
				e.stopPropagation();

				$('[name=data\\[Page\\]\\[display_flag\\]]', li).each(function(){
					var display = $(this);
					var a = display.prev(), img = a.children(':first');
					if(setDisplay == null) {
						setDisplay = (display.val() == '1') ? 0 : 1;;
					}
					display.val(setDisplay);
					if(img.get(0)) {
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

				// ページ追加disable
				var is_chief = active_li.data('is-chief');
				var dd_sequence = active_li.data('dd-sequence');
				if(dd_sequence != 'inner-only') {
					var parent_li = active_li.parents('li');
					if(parent_li.get(0)) {
						is_chief = active_li.data('is-chief');
					}
				}

				if(!is_chief) {
					$('#pages-menu-add-btn').addClass('pages-menu-btn-disable');
				} else {
					$('#pages-menu-add-btn').removeClass('pages-menu-btn-disable');
				}
				sel_active_tab = active_tab;

				e.preventDefault();
				e.stopPropagation();
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
		} else {
			// コンテンツクリックイベント
			root_menu.on('click','.pages-menu-handle',function(e) {
				var content = $(this);
				var active_li_buf = content.parents('li:first');
				var button = $('button[data-action]:visible:first', active_li_buf);
				if(button.get(0)) {
					button.click();
				}
			});
		}
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

			$('input[name="data[Community][publication_range_flag]"]', tab).change(function(e){
				var target = $(this);
				if(target.val() == '0') {
					$('input[name="data[Community][publication_authority]"]', tab).attr('disabled', 'disabled');
				} else {
					$('input[name="data[Community][publication_authority]"]', tab).removeAttr('disabled');
				}
			});
			$('input[name="data[Community][participate_flag]"]', tab).change(function(e){
				var target = $(this);
				if(target.val() == '0') {
					$('#pages-menu-community-invite-authority-' + id).slider( "disable" );
				} else {
					$('#pages-menu-community-invite-authority-' + id).slider( "enable" );
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
		}
	};
})(jQuery);