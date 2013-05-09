/**
 * 承認 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.Approve = function(id) {
		$(this).on('ajax:success', function(e, res) {
			var re_html = new RegExp("^<script>", 'i');
			if($.trim(res).match(re_html)) {
				$.Common.reloadBlock(null, id);
				$('[name=close]:first', $(this)).click();
			}
		});
	};
})(jQuery);