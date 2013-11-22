/**
 * コミュニティー招待 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.PageInviteCommunity = {
/**
 * コミュニティー招待初期処理
 * @param   void
 * @return  void
 */
		inviteCommunityInit : function() {
			var inviteCommunity = $('.pages-menu-invite-community:first', $('#pages-menu-invite-community'));
			var timer = setInterval(function() {
				// CSSが読み込み完了するまで待機
				if(inviteCommunity.outerWidth() > 100) {
					inviteCommunity.select2({
						minimumResultsForSearch:-1,
						width: 'element'
					});
					clearInterval(timer);
				}
			},100);

		},

/**
 * コミュニティー招待 会員選択画面表示時
 * @param   string id 検索結果mainId
 * @param   string selectMembersId select memberセレクトボックス
 * @return  void
 */
		selectMemberSearchInit : function(id, selectMembersId) {
			var searchResults = $(id);
			var selectMenbers = $(selectMembersId);
			var options = $(":checked", selectMenbers);
			var handles = new Array();
			options.each(function(){
				handles.push($(this).val());
			});
			$('ul.pages-menu-invite-community-search-list:first', searchResults).children().each(function(){
				if($.inArray($(this).attr('data-handle'), handles) != -1) {
					$('.pages-menu-invite-community-search-participating:first', $(this)).show();
				}
			});
		},

/**
 * コミュニティー招待 会員選択時
 * @param   string id select memberセレクトボックス
 * @param   event e
 * @param   element el
 * @param   string handle
 * @return  void
 */
		selectMemberSearch : function(id, e, el, handle) {
			var selectMenbers = $(id);
			selectMenbers.append($('<option>').html(handle).val(handle).attr('selected' , true)).select2();
			$('.pages-menu-invite-community-search-participating:first', $(el)).show();
			$.Event(e).preventDefault();
		}
	};
})(jQuery);