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
	$.fn.PageMenu = function(is_edit, active_page_id) {
		var tab = this;

		var active_li;
		var options = {
			active          : active_page_id,
			activeItemClass : 'highlight'
		};

		var root_menu = $('#pages-menu-page');
		if(!(is_edit)) {
			options['singleHandleClass'] = 'pages-menu-dd-single';
			options['contentClass'] = 'pages-menu-handle';
		} else {
			options['contentClass'] = 'dd-drag-content';
		}
		$('#pages-menu-tab').tabs();

		// ページ移動
		root_menu.nestable(options)
		.on('change', function(e, page_id, move_page_id, position) {
			// 表示順変更
			return true;
		});
		if(is_edit) {
			active_li = $('#pages-menu-edit-item-' + active_page_id);

			// ページ追加
			$('#pages-menu-add-btn').on('ajax:before', function(e, url) {
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
				target.hide();
				root_menu.nestable('appendList', [active_li, target, position]);
				target.slideDown(300, function() {
					var position = target.offset().top+$('#pages-menu-page').scrollTop() - $('#pages-menu-page').offset().top;
					// ページ名称にfocus
					$('[name=data\\[Page\\]\\[page_name\\]]:first', target).select();
					// スクロール
					$('#pages-menu-page').animate({scrollTop:position}, 400, 'swing');
				});
				// コンテンツクリックイベント
				$('.pages-menu-edit-content:first', target).click();
			});

			// ページ削除
			$(tab).on('ajax:before','.pages-menu-delete-icon',function(e, url) {
				var li = $($(e.target).data('ajax-data')),page_id = li.data('id');
				var a = $('a.pages-menu-edit-title:first', li), title = $.trim(a.html());
				var ok = __('Ok') ,cancel = __('Cancel');
				var pos = $(e.target).offset(), _buttons = {}, default_params = {
					title: __d('pages', 'Delete page'),
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
						$('<div class="pages-menu-all-delete-confirm"></div>').html(__d('pages', 'I can\'t be undone completely removed. <br />Are you sure you want to delete?'))
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
							'<input id="pages-menu-all-delete" type="checkbox" name="all_delete" value="1" />&nbsp;'+
							__d('pages', 'I completely delete it.')+
							'</label></div>';

				$('<div></div>').html(body).appendTo($(document.body)).dialog(params);
				return false;
			});

			// ページ詳細編集画面表示
			$(tab).on('ajax:before','[data-page-edit-id]',function(e, url) {
			    var page_id = $(this).data('page-edit-id');
				var detail = $('#pages-menu-edit-detail-' + page_id);
				if(detail.css('display') != 'none') {
					// 既に編集中->非表示
					detail.slideUp(300, function() {detail.html('');});
					return false;
				} else if(detail.children().length > 0) {
					detail.slideDown(300);
					return false;
				}

				url = url + '/' + page_id;
				return url;
			}).on('ajax:success','[data-page-edit-id]',function(e, target) {
				var page_id = $(this).data('page-edit-id');
				target.slideDown(300, function() {
					target = $('#pages-menu-edit-item-' + page_id);
					var position = target.offset().top+$('#pages-menu-page').scrollTop() - $('#pages-menu-page').offset().top;
					// ページ名称にfocus
					$('[name=data\\[Page\\]\\[page_name\\]]:first', target).select();
					// スクロール
					$('#pages-menu-page').animate({scrollTop:position}, 400, 'swing');
				});
			});

			// ページ編集
			$(tab).on('ajax:before','form',function(e, url) {
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
				root_menu.nestable('addEvent', [target, page_id]);
			});

			// ページ公開・非公開
			$(tab).on('click','.pages-menu-display-flag',function(e) {
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
			$(tab).on('click','.pages-menu-edit-content',function(e) {
				var content = $(this);
				active_li = content.parents('li:first');

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
		};
		// ページ削除
		var dletePage = function(url, page_id, all_delete, default_params) {
			$.post(url, {'data[Page][id]': page_id, 'all_delete': all_delete},function(res){
				var re_html = new RegExp("^<script>", 'i');
				if(!$.trim(res).match(re_html)) {
					// error
					var ok = __('Ok');
					var body = '<div class="error-message">' + res + '</div>';
					var _buttons = {}, params;
					_buttons[ok] = function(){
						$( this ).remove();
					};
					params = $.extend({buttons: _buttons}, default_params);
					$('<div></div>').html(body).dialog(params);
				} else {
					// success
					var li = $('#pages-menu-edit-item-' + page_id);
					var prev = li.prev(), next = li.prev(), parent = li.parents('li');
					var active = (prev.get(0)) ? prev : ((next.get(0)) ? next : parent);
					if(active.get(0)) {
						$('.pages-menu-edit-content', active).click();
					}
					$(res).appendTo($(document.body));

					li.remove();
				}
			});
		};
	}
})(jQuery);