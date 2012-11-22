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
			// ページ編集
			$(tab).on('ajax:before','[data-page-edit-id]',function(e, url) {
			    var page_id = $(this).data('page-edit-id');
				var detail = $('#pages-menu-edit-detail-' + page_id);
				if(detail.css('display') != 'none') {
					// 既に編集中->非表示
					detail.slideUp(300);
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

			// コンテンツクリックイベント
			$(tab).on('click','.pages-menu-edit-content',function(e) {
				var content = $(this);
				e.preventDefault();
				e.stopPropagation();
				$('.pages-menu-edit-content', tab).each(function(){
					$(this).removeClass(options.activeItemClass);
					$(this).parents('li:first').children('.pages-menu-edit-operation:first').hide();

					var a = $(this).children('a.pages-menu-edit-title:first');
					var input = $(this).children('input.pages-menu-edit-title:first');
					if(input.css('display') != 'none' && input.val() == $.trim(a.html())) {
						input.css('display', 'none');
						a.css('display', '');
					}
				});
				active_li = content.parents('li:first');
				content.addClass(options.activeItemClass);
				active_li.children('.pages-menu-edit-operation:first').show();
				var a = content.children('a.pages-menu-edit-title:first');
				var input = content.children('input.pages-menu-edit-title:first');
				input.css('display', '');
				a.css('display', 'none');
				if($(e.target).get(0).tagName != 'INPUT') {
					input.select();
				}
			});
		}
	}
})(jQuery);