/**
 * ブログ表示方法変更 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.BlogStyles = function(id) {
		var top_el = $(this);
		var sortable = $('div.blog-style-widget-area-content-parent', top_el);
		var area_outer = $('.blog-style-widget-area-outer', top_el);
		var items = $('.blog-style-widget-area-title', area_outer).disableSelection();
		var state = false;

		// 表示順変更
		sortable.sortable( {
			items: area_outer,
			placeholder: "ui-state-highlight",
			handle: '> .blog-style-widget-item',
			cursor: 'move',
			distance: 2,
			containment: $(this),
			connectWith: "div",
			zIndex: $.Common.blockZIndex++,
			start: function(e,ui) {
				ui.helper.find('div.blog-style-widget-area-content:first').hide();
			},
			stop: function(e, ui) {
				var params = {
					'widget_type' : ui.item.attr('data-widget-type'),
					'col_num' : ui.item.parents('.blog-style-widget-area:first').attr('data-col-num'),
					'row_num' : ui.item.parent().children('div').index(ui.item) + 1
				};
				state = true;
				$.post(top_el.attr('data-ajax-url'),
					params,
					function(res){
						// メインブロックリロード
						$.Common.reloadBlock(e, id);
					}
				);
			}
		} );

		// 編集画面表示・非表示
		items.click(function(e){
			if(state == false) {
				$(this).parents('.blog-style-widget-area-outer:first').children('.blog-style-widget-area-content:first').slideToggle();
			}
			state = false;
			$.Event(e).preventDefault();
		});
	};

	$.BlogStyles = {
		// 公開・非公開変更
		display : function(e, id, a, url) {
			var item = $(a).parents('.blog-style-widget-area-outer:first');
			var img = $(a).children(':first'),alt, pre_alt,display;
			alt = img.attr('data-alt');
			pre_alt = img.attr('alt');
			img.attr('alt', alt);
			img.parent().attr('title', alt);
			img.attr('data-alt', pre_alt);

			if(img.attr('src').match(/on\.gif$/)) {
				img.attr('src', img.attr('src').replace("on.gif", "off.gif"));
				display = 0;
			} else {
				img.attr('src', img.attr('src').replace("off.gif", "on.gif"));
				display = 1;
			}

			var params = {
				'widget_type' : item.attr('data-widget-type'),
				'display_flag' : display
			};

			$.post(url,
				params,
				function(res){
					// メインブロックリロード
					$.Common.reloadBlock(e, id);
				}
			);

			$.Event(e).preventDefault();
			$.Event(e).stopPropagation();
		},

		// ウジェット編集画面 - キャンセルクリック
		clickWidgetChancel : function(e, input) {
			var item = $(input).parents('.blog-style-widget-area-outer:first');
			$('.blog-style-widget-area-title:first', item).click();
			$.Event(e).preventDefault();
		}
	}
})(jQuery);