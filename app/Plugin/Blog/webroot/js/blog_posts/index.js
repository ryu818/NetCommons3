/**
 * ブログ記事投稿・編集 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.BlogPosts = function(id, is_error) {
		var wysiwyg = $('#HtmlareaContent'+id).nc_wysiwyg({
			autoRegistForm : $('#Form' + id)
		});
		var date = $('#BlogPostPostDate' + id);
		var area_outer = $('#blog-post-widget-area' + id);
		var items = $('.blog-style-widget-area-title', area_outer).disableSelection();
		var sel_tags = $('#blog-posts-tags-select' + id);

		$('#Form' + id).submit(function(e){
			$.BlogPosts.addTags(e, id);
		});

		date.datetimepicker();
		if(is_error == 0) {
			$('#BlogPostTitle' + id).select();
		}

		// 編集画面表示・非表示
		items.click(function(e){
			$(this).parents('.blog-style-widget-area:first').children('.blog-style-widget-area-content:first').slideToggle();
			$.Event(e).preventDefault();
		});

		sel_tags.chosen();

		$.BlogPosts.initCategory(id);
	};

	$.BlogPosts = {
		// カテゴリーinit処理
		initCategory : function(id) {
			var sel_categories, parent_select_category, add_category_outer, add_category;
			sel_categories = $('#blog-posts-categories-select' + id);
			parent_select_category = $('#blog-posts-categories-parent-select' + id);
			add_category_outer = $('#blog-posts-add-category-outer' + id);
			add_category = $('.blog-posts-add-text', add_category_outer);

			sel_categories.chosen();
			if(parent_select_category.get(0)) {
				parent_select_category.chosen({allow_single_deselect: true});
			}
			add_category_outer.prev().click(function(e){
				$(this).next().slideToggle();
				add_category.focus();
				$.Event(e).preventDefault();
			});
		},
		// カテゴリー追加
		addCategory : function(e, id) {
			if(e.keyCode != 13 && e.type != 'click') {
				return;
			}
			var add_category_outer = $('#blog-posts-add-category-outer' + id);
			var add_category = $('.blog-posts-add-text', add_category_outer);
			var parent_select_category = $('#blog-posts-categories-parent-select' + id);
			var url = add_category.attr('data-ajax-url');
			var sel_categories = $('#blog-posts-categories-select' + id);
			var params = new Object();
			params[add_category.attr('name')] = add_category.val();
			params[parent_select_category.attr('name')] = parent_select_category.val();
			params[sel_categories.attr('name')] = sel_categories.trigger("liszt:updated").val();

			$.Event(e).preventDefault();

			$.post(url,
				params,
				function(res){
					$('#blog-post-widget-area-content' + id).html(res);
					$.BlogPosts.initCategory(id);
				}
			);
		},

		// タグ追加
		addTags : function(e, id) {
			if(e.keyCode != 13 && e.type != 'click' && e.type != 'submit') {
				return;
			}
			if(e.type != 'submit') {
				$.Event(e).preventDefault();
			}
			var tag_names = $('#blog-post-tag-names' + id);
			var tags_select = $('#blog-posts-tags-select' + id);
			var tag_names_arr = tag_names.val().split(",");
			$.each(tag_names_arr, function(key, value) {
				var value = $.trim(value);
				if(value != "") {
					// TODO: valueに「"」や「'」等の特殊文字があっても問題なく動作するかテストすること。
					var child = tags_select.children('[value=' + value + ']:first');
					if(child.get(0)) {
						// 既に存在する
						child.attr('selected', 'selected');
					} else {
						tags_select.append($('<option value="' + value + '" selected="selected">' + value + '</option>'));
					}

				}
			});
			tags_select.trigger("liszt:updated");
			tag_names.val('');
		}
	}
})(jQuery);