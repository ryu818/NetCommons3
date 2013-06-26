/**
 * コンテンツ一覧 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.Content = function(id, active_url, active_id) {
		var form = $(this);

		setTimeout(function(){$("#nc-content-sel-module" + id).chosen().change(function(e){
			$(this).attr('data-ajax-url', $(this).attr('data-ajax-url')+ '/module_id:' + $(this).val());
			$(this).attr('data-ajax', form.attr('data-ajax'));
			$.Common.ajax(e, this);
		});}, 100);

		if(active_url) {
			$('#' + id).attr('data-ajax-url', active_url);
			$.Common.reloadBlock(null, id);
		} else if(active_id && $('#' + active_id).get(0)) {
			$.Common.reloadBlock(null, active_id);
		}
	};

	$.Content = {
		switchingContent: function(radio) {
			if(!$(radio).parent().hasClass("highlight")) {
				$(radio.form).submit();
			}
		},

		referenceContent: function(e, a, content_title, block_url) {
			var padding = 10;
			var w = 700;
			var h = 500;
			var iframeStr = '<iframe class="nc-content-reference" src="' + $(a).attr("href") + '" />';
			if(block_url) {
				iframeStr = '<div class="nc-content-reference-outer"><div class="nc-content-reference-title nc-panel-color"><a href="' + block_url + '" onclick="$(\'.ui-dialog\').remove();">' + __d('block', 'To the placement page') + '</a></div>' + iframeStr + '</div>';
			}
			var element = $(iframeStr);
        	element.dialog({
				title: content_title,
				width: w,
				height: h,
				modal: true,
				resizable: true,
				show:'fold',
				hide:'fold'
        	});
			if(block_url) {
				element.width(w).height(h);
        		$('iframe:first', element).width(w - padding).height(h - padding - 30);
			} else {
				element.width(w - padding).height(h - padding);
			}
        	$.Event(e).preventDefault();
		}
	}
})(jQuery);