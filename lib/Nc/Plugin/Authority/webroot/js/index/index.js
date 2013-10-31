/**
 * 権限管理 js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.Authority = {
		init: function() {
			var params = {
				width: '640',
				height: 'auto',
				showToggleBtn: false,
				resizable: false,
				singleSelect: true
			};
			$("#authority-list-grid").flexigrid(params);
		},
		initEdit: function(id) {
			// Privateメソッド
			var changeBaseAuthId = function() {
				desc.children().removeClass('authority-highlight');
				$(descId + '-' + select.val()).addClass('authority-highlight');
			};
			var descId = '#authority-edit-desc' + id;
			var desc = $(descId);
			var text = $('input:text:first', $('#authority-list'));
			var select = $('select:first', $('#authority-list'));
			text.focus();
			changeBaseAuthId();
			select.change(function () {
				changeBaseAuthId();
			});
		},
		initSetLevel: function(id, hierarchy, disabled) {
			hierarchy = (typeof hierarchy == 'undefined' || hierarchy == null) ? 50 : hierarchy%100;
			if(hierarchy == 0) {
				hierarchy = 100;
			}
			$("#authority-slider"+id).slider({
				range: "min",
				value: hierarchy,
				min: 1,
				max: 100,
				disabled: disabled,
				slide: function( event, ui ) {
					$("#authority-level"+id).val(ui.value);
				}
			});
			$("#authority-level"+id).val($("#authority-slider"+id).slider("value"));
		},
		initDetail: function(id, myportalMembers) {
			var top = $('#' + id);
			var form = $('form:first', top);
			var myportalUseFlag = $('input[name="data[Authority][myportal_use_flag]"]', form);
			myportalUseFlag.change(function () {
				var myportalDetail = $('#authority-select-authority' + id);
				if($(this).val() == myportalMembers) {
					myportalDetail.show();
				} else {
					myportalDetail.hide();
				}
			});
		},
		initDetail2: function(id) {
			var top = $('#' + id);
			var form = $('form:first', top);
			var myportalUseFlag = $('input[name="data[Authority][myportal_use_flag]"]', form);
			var privateUseFlag = $('input[name="data[Authority][private_use_flag]"]', form);
			console.log(myportalUseFlag);
			console.log(privateUseFlag);
			console.log(myportalUseFlag.val());
			console.log(privateUseFlag.val());
			form.on('ajax:beforeSend',function(e, url) {
				if(!url.match(/\/usable_module\//) || myportalUseFlag.val() > 0 || privateUseFlag.val() > 0) {
					return true;
				}
				return $(this).attr("data-confirm-url");
			});
		},
		initUsableModule: function(id) {
			var top = $('#' + id);
			var form = $('form:first', top);
			$('#authority-usable-module-tab'+id).tabs();

			form.on('ajax:beforeSend',function(e, url) {
				// selectの値がPOSTで送信されなかったため手動でセット。
				var ret = {'data': {'MyportalModuleLink' : new Object(), 'PrivateModuleLink' : new Object()}};
				var select = $('#ModuleLinkModuleIdMyportal' + id);
				select.children().each(function(){
					ret['data']['MyportalModuleLink'][$(this).val()]= {'module_id' : $(this).val()};
				});
				var select = $('#ModuleLinkModuleIdPrivate' + id);
				select.children().each(function(){
					ret['data']['PrivateModuleLink'][$(this).val()]= {'module_id' : $(this).val()};
				});
				return ret;
			});
		}
	}
})(jQuery);