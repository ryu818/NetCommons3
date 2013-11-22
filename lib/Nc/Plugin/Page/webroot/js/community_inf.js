/**
 * コミュニティー情報 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.PageCommunityInf = {
/**
 * コミュニティー参加、退会操作コールバック
 * @param   event e
 * @param   string res
 * @return  void
 */
		communityOperationCallback: function(e, res) {
			var reHtml = new RegExp("^<script>", 'i');
			if($.trim(res).match(reHtml)) {
				$('#pages-menu-community-inf').html(res).dialog("destroy");
				return false;
			}
			return true;
		}
	};
})(jQuery);