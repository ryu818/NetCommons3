/**
 * 会員管理 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.User = function(id, list) {
		var table = $("#user-list");
		var tabs = $(this);
		tabs.tabs({
			beforeActivate: function( event, ui ) {
				var a = ui.newTab.children('a:first');
				if(ui.newPanel.html() != '') {
					var href = a.attr('href');
					// 既に表示中 - Ajaxで再取得しない。
					a.attr('href', '#' + ui.newPanel.attr('id')).attr('data-url', href);

				}
				a.attr('data-tab-id', ui.newPanel.attr('id'));
				var w = ui.newTab.attr('data-width');
				if(w) {
					$('#' + id).dialog('option', 'width', w);
				} else {
					$('#' + id).dialog('option', 'width', 'auto');
				}
			},
			activate: function( event, ui ) {
				var active = tabs.tabs("option","active");
				if(active > 0) {
					// 一覧以外
					var text = $('input:text:first', ui.newPanel);
					if(text.get(0)) {
						text.focus();
					}
				}
			}
		});

		var colModel = [
			{display: list[0], name : 'User.handle', width: 120, height: 26, sortable : true},
			{display: list[1], name : 'User.username', width: 110, height: 26, sortable : true},
			{display: list[2], name : 'Authority.hierarchy', width: 85, height: 26, sortable : true},
			{display: list[3], name : 'User.is_active', width: 55, height: 26, sortable : true},
			{display: list[4], name : 'User.created', width: 105, height: 26, sortable : true},
			{display: list[5], name : 'User.last_login', width: 105, height: 26, sortable : true}
		];

		if(list[6]) {
			colModel[6] = {display: list[6], name : 'admin', width: 300, height: 26, sortable : false};
			colModel[7] = {display: list[7], name : 'allChecked', width: 80, height: 26, sortable : false, align: 'center'};
		}

		var params = {
			width: 'auto',
			height: 'auto',
			showToggleBtn: false,
			url: table.attr('data-url'),
			setParams : function() {
				// 絞り込み検索用
				var fields = $("input:hidden", $("#user-init-tab-list")).serializeArray();
				return fields;
			},
			//procmsg: '読み込み中です..',
			//pagestat: '全 {total} 件のうち、{from} - {to} 件目を表示中',
			method: 'POST',
			dataType: 'json',
			colModel : colModel,
			sortname: "Authority.hierarchy",
			sortorder: "desc",
			usepager: true,
			//useRp: true,
			rp: 15,
			resizable: false/*,
			singleSelect: true*/
		};
		table.flexigrid(params);
	};
	$.User = {
		editIds : {},
		// 編集画面初期処理
		editInit : function(id, editUserId, editTitle, addUrl) {
			var tabs = $('#user-init-tab');
			var userEdit = $('.user-edit:first', $('#' + id))
			var text = $(':text:first', userEdit);
			var active = tabs.tabs("option","active");
			var file = $('#UserAvatarFile' + id);
			if(text.get(0)) {
				text.focus();
			}

			if(editTitle != '') {
				// 会員登録->会員編集へ
				var li = $('li:eq('+active+')', tabs.children('ul:first'));
				var a = $('a:first', li);
				tabs.tabs('add', addUrl, a.html(), active + 1);
				a.html(editTitle);
				a.after($.User.getCloseBtn(editUserId));
				$.User.editIds[editUserId] = li.get(0);
			}

			if(file.get(0)) {
				var userAvatarDiv = $('.user-avatar-outer:first', $('#' + id));
				var userAvatarImage = userAvatarDiv.find('img:first');
				$.User.initObjectScaler(userAvatarImage);

				userAvatarDiv.children(':first').click(function(){
					$.User.selectAvatar(id);
				});
				file.fileupload({
					dropZone:userAvatarDiv,
					formData: function (form) {
						return {
							0:{'name':'data[_Token][key]','value':$('input[name="data[_Token][key]"]:first', $('#Form' + id)).val()}
						};
					},
					dataType: 'html',
					success: function(res){
						var uploadId;
						var userAvatarImage;
						if (res) {
							userAvatarDiv.html(res);
							userAvatarDiv.children('div:first').click(function(){
								$.User.selectAvatar(id);
							});
	
							userAvatarImage = userAvatarDiv.find('img:first');
							$('#UserAvatar' + id).val(userAvatarImage.attr('data-avatar'));
	
							$.User.initObjectScaler(userAvatarImage);
						}
					}
				});
			}

			var ul = $('#user-init-tab-ul');
			var w = $('.user-init-tab-add:first', ul).attr('data-width');

			tabs.parents('.ui-dialog:first').css('width', w + 'px');
		},
/**
 * 会員管理のリンク識別子は、ハンドルと同等のものがデフォルトはいるようにする
 * @param   object event
 * @return  void
 */
		chgHandle: function(id, input) {
			var handleInput = $(input);
			var top = $('#' + id);
			var form = $('form:first', top);
			var permalinkInput = $('input[name="data[User][permalink]"]:first', form);
			if(handleInput.get(0) && permalinkInput.get(0)) {
				if(permalinkInput.val() == handleInput.prev().html()) {
					var handleValue = handleInput.val();
					permalinkInput.val(handleValue);
					handleInput.prev().html(handleValue);
				}
			}
		},

		// 項目設定初期処理
		displaySettingInit : function(isInit) {
			isInit = (isInit == undefined) ? true : isInit;
			var topOuter = $('#user-display-setting');
			var top = $('.user-display-setting:first', topOuter);
			var outer = $('div.user-display-setting-col', top);

			var sortable = $('div.user-display-setting-area-outer', top);
			var preCell = null, form;

			top.sortable( {
				//items: top.children(),
				placeholder: "ui-state-highlight",
				handle: '> .user-display-setting-area-top-title',
				cursor: 'move',
				distance: 2,
				zIndex: $.Common.zIndex++
				//start: function(e,ui) {
					//ui.helper.find('div.blog-style-widget-area-content:first').hide();
				//},
				//stop: function(e, ui) {

				//}
			} );

			// 表示順変更
			outer.sortable( {
				items: sortable,
				placeholder: "ui-state-highlight",
				handle: '> .user-display-setting-area-title',
				cursor: 'move',
				distance: 2,
				//containment: top.parent().parent(),
				connectWith: "div.user-display-setting-col",
				zIndex: $.Common.zIndex++,
				start: function(e,ui) {
					//ui.helper.find('div.blog-style-widget-area-content:first').hide();
				},
				change: function(e, ui) {
					preCell = ui.item.parent();
				},
				stop: function(e, ui) {
					var rows = preCell.children();
					if(!rows.get(0)) {
						var parent = preCell.parent();
						preCell.remove();
						var lists = parent.children();
						if(!lists.get(0)) {
							parent.parents('.user-display-setting-list:first').remove();
						} else {
							$('.user-display-setting-right-btn:first', parent.parents('.user-display-setting-list:first')).css('visibility', 'visible');
						}
					}
				}
			} );

			if(isInit) {
				form = $('form:first', topOuter);
				form.on('ajax:beforeSend',function(e, url) {
					// 表示順変更
					var lists =top.children(), data = new Array();
					var listNum = 0, i = 0, ret = new Object();
					//data['Item'] = new Array();

					lists.each(function(kList, vList){
						var colNum = 0;
						var cols = $(".user-display-setting-area-list-outer", $(vList)).children(':first').children();

						cols.each(function(kCol, vCol){
							var rows = $('.user-display-setting-area-outer', vCol);
							if(rows.get(0) && listNum <= kList) {
								listNum++;
							}
							if(rows.get(0) && colNum <= kCol) {
								colNum++;
							}
							rows.each(function(kRow, vRow){
								var itemId = $(vRow).attr('data-item-id');
								data[i] = new Object();
								data[i]['Item'] = new Object();
								data[i]['Item']['id'] = itemId;
								data[i]['Item']['list_num'] = listNum;
								data[i]['Item']['col_num'] = colNum;
								data[i]['Item']['row_num'] = kRow + 1;
								i++;
							});
						});

					});
					ret['data'] = data;
					return ret;
				}).on('ajax:success',function(res) {
					// 会員追加・会員編集タブ初期化
					var ul = $('#user-init-tab-ul');
					var tabs = ul.parent();
					var active = tabs.tabs("option","active");

					$('a.ui-tabs-anchor', ul).each(function(k, v){
						v = $(v);
						var dataUrl = v.attr('data-url');
						if(k > 0 && k < active) {

							$('#' +v.attr('data-tab-id')).html('');
							if(dataUrl) {
								//$(v.attr('href')).html('');
								v.attr('href', dataUrl);
							}
						}
					});
				});
			}
		},
		addList : function(a) {
			$(a).parents('.user-display-setting-list:first').after($('#user-display-setting-dummy').html());
			$.User.displaySettingInit(false);
		},
		addCol : function(a, maxCol) {
			var parent = $(a).parent().prev();
			var cols = $('.user-display-setting-col', parent);
			var colLen = cols.length;
			if(colLen == maxCol) {
				// 列の数はmaxColまで
				return;
			} else if(colLen == maxCol - 1) {
				$(a).css('visibility', 'hidden');
			}

			var w = Math.floor(100/(colLen + 1));

			cols.each(function(){
				$(this).css('width', w + '%');
			});

			$(cols.get(cols.length - 1)).addClass('user-display-setting-right-line').
				after($('.user-display-setting-col:first', $('#user-display-setting-dummy')).parent().html());
			$(cols.get(cols.length - 1)).next().css('width', w + '%');

			$.User.displaySettingInit(false);
		},
		// 会員編集
		memberEdit : function(e, a) {
			var ul = $('#user-init-tab-ul');
			var tabs = ul.parent(), active;
			var userId = $(a).attr('data-user-id'),count = 0,active_li, w,title;

			$.Event(e).preventDefault();
			if($.User.editIds[userId]) {
				// 既に表示中
				ul.children().each(function(){
					if($.User.editIds[userId] == this) {
						tabs.tabs( "option", "active", count );
					}
					count++;
				});
			} else {
				tabs.tabs('option', 'tabTemplate', "<li><a href='#{href}'>#{label}</a> "+$.User.getCloseBtn(userId)+"</li>");
				title = $(a).attr('data-tab-title') ? $(a).attr('data-tab-title') : $(a).attr('title')
				tabs.tabs('add', $(a).attr('href'), title, 1);
				active_li = ul.children(':first').next();
				$.User.editIds[userId] = active_li.get(0);

				tabs.tabs( "option", "active", 1 );
			}
		},
		memberQuit : function(userId, a, active) {
			var ul = $('#user-init-tab-ul');
			var tabs = ul.parent();
			if(a && !active) {
				var remove_li = $(a).parent(), count = 0;

				ul.children().each(function(){
					if(this == remove_li.get(0)) {
						active = count;
						return false;
					}
					count++;
				});
			}

			if(!active) {
				active = tabs.tabs("option","active");
			}

			delete $.User.editIds[userId];

			tabs.tabs('remove', active);
		},
		selectGroupInit : function(id) {
			var top = $('#' + id);
			var form = $('form:first', top);
			top.parents('.ui-dialog:first').css('width', '640px');
			form.on('ajax:beforeSend',function(e, url) {
				// selectの値がPOSTで送信されなかったため手動でセット。
				var ret = {'data': {'PageUserLink' : new Object()}};
				var select = $('#EnrollPageUserLinkPageId' + id);
				select.children().each(function(){
					//if(!$(this).prop('disabled')) {
						ret['data']['PageUserLink'][$(this).val()]= {'room_id' : $(this).val()};
					//}
				});
				return ret;
			});
		},

		getCloseBtn: function(userId) {
			return "<a href='#' onclick='$.User.memberQuit(" + userId + ", this); return false;' class='user-edit-close ui-icon ui-icon-close'>Remove Tab</a>";
		},
/**
 * 参加ルーム選択オプションClick
 * @param   element option
 * @return  void
 * @since   v 3.0.0.0
 */
		clickGroupOption: function(option) {
			option = $(option);
			var select = option.parent();
			var parent_id = option.attr('data-parent-id');
			if(option.prop('disabled')) {
				return;
			}
			var selects = null;
			if(!select.hasClass('user-selectlist-enroll')) {
				// 全ルーム一覧
				if(!option.prop('selected')) {
					selects = select.children('[data-parent-id='+option.val()+']').prop('selected', false);
				} else {
					// 子グループ　親ルームも選択
					if(parent_id) {
						selects = select.children('[value='+parent_id+']:first').prop('selected', true);
					}
				}

			} else {
				// 参加させるルーム
				if(!option.prop('selected')) {
					if(parent_id) {
						selects = select.children('[value='+parent_id+']:first').prop('selected', false);
					}
				} else {
					// 親ルーム　子グループも選択
					selects = select.children('[data-parent-id='+option.val()+']').prop('selected', true);
				}
			}
			if(selects) {
				selects.each(function(){
					$.User.clickGroupOption(this);
				});
			}
		},

/**
 * 権限設定初期処理
 * @param   string id
 * @return  void
 * @since   v 3.0.0.0
 */
		selectAuthInit : function(id) {
			var top = $('#' + id);
			var form = $('form:first', top);
			top.parents('.ui-dialog:first').css('width', '750px');
			//form.on('ajax:success',function(res) {
			//	$.User.successSelectAuth(res);
			//});
		},

/**
 * 権限設定-全選択
 * @param   integer user_id
 * @param   integer defAuthorityId
 * @param   element el button element
 * @return  void
 * @since   v 3.0.0.0
 */
		allChecked: function(user_id, defAuthorityId, el) {
			var cell = $(el).parent();
			var input = $("input:hidden:first", cell);
			var select = $("select:first", cell);

			$("input.user-auth-listbox-name-" + defAuthorityId, $("#user-room-list-" + user_id)).each(function() {
				if(!$(this).attr('disabled')) {
					$(this).prop('checked', true).val(input.val());
					//$(this).click().val(input.val());

					if(select.get(0)) {
						var list_name = $(this).parent().next();
						list_name.val(select.val());
					}

				}
			});
		},
/**
 * 権限設定-権限セレクトボックス
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
 * 権限設定決定後処理
 * @param   integer userId
 * @param   string errorMes
 * @return  void
 * @since   v 3.0.0.0
 */
		successSelectAuth: function(userId, errorMes) {
			var tabs = $('#user-init-tab');
			var tabList = $('#user-init-tab-list');
			setTimeout(function(){	// ヘッダーメッセージを表示するため
				if(errorMes != "") {
					// 警告メッセージを会員一覧に表示
					var description = $(".top-description:first", tabList);
					if(description.prev().get(0) && description.prev().hasClass('error-message')) {
						description.prev().remove();
					}
					description.before(errorMes);
				}
				active = tabs.tabs("option","active");
				tabs.tabs( "option", "active", 0 );
				$.User.memberQuit(userId, null, active);
			}, 100);
		},
/**
 * 画像サイズ調整初期化処理
 * @param   element el
 * @return  void
 * @since   v 3.0.0.0
 */
		initObjectScaler: function(el) {
			el.cjObjectScaler({
				method: 'fit',
			}, function() {
				el.css('visibility', '');
			});
		},
/**
 * ファイル選択表示処理
 * @param   string id
 * @return  void
 * @since   v 3.0.0.0
 */
		selectAvatar: function(id) {
			$('#UserAvatarFile' + id).click();
		},
/**
 * アバター削除処理
 * (画面上のみの削除。DB上の削除処理は決定ボタン押下時に行う。)
 * @param   element el
 * @return  void
 * @since   v 3.0.0.0
 */
		deleteAvatar: function(id) {
			var userAvatarImg = $('.user-avatar img', $('#' + id));
			imgSrc = userAvatarImg.attr('src');
			userAvatarImg.attr('src', imgSrc.replace(/nc-downloads\/.*$/, 'user/img/avatar.gif'));
			$.User.initObjectScaler(userAvatarImg);
			$('#UserAvatar' + id).val('');
		}
	}
})(jQuery);