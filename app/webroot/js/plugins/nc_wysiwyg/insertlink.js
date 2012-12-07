/*
 * NC InsertLink 0.0.0.1
 * @param options hash
 *					url        : string   デフォルトのURLテキストに表示する値(default 'http://')
 *					title      : string   デフォルトのtitleテキストに表示する値(default '')
 *					target     : string   デフォルトのtargetテキストに表示する値(default '')
 *										  指定なし ：'' or 新規ウィドウ : '_blank' or その他 : '_other'
 *					callback   : function リンク挿入時のcallback関数(default null)
 *										  callback内：args hash
 *                                                            @param url
 *                                                            @param title
 *                                                            @param target
 *                                                            @return boolean
 *                  cancel_callback : function　キャンセルボタン押下時のcallback関数(default null)
 *                                                            @return boolean
 */
;(function($) {
	$.fn.nc_insertlink = function(options) {
		var options = $.extend({
        		url             : 'http://',
	            title           : '',
	            target          : '',				// '' or '_blank' or '_other'
	            callback        : null,
	            cancel_callback : null,
	            html            :
	            	'<div class="nc-wysiwyg-insertlink-title">'+ __d('nc_wysiwyg_insertlink', 'dialog_title') +'</div>' +
				  		'<ul class="nc_wysiwyg_insertlink">'+
				 			'<li><dl><dt><label for="nc-wysiwyg-insertlink-url">'+ __d('nc_wysiwyg_insertlink', 'url') +'<span class="require">*</span></label></dt><dd><input id="nc-wysiwyg-insertlink-url" name="insertlink_url" class="nc-wysiwyg-insertlink-input" type="text" /></dd></dl></li>' +
				  			'<li><dl><dt><label for="nc-wysiwyg-insertlink-title">'+ __d('nc_wysiwyg_insertlink', 'title') +'</label></dt><dd><input id="nc-wysiwyg-insertlink-title" name="url_title" class="nc-wysiwyg-insertlink-input" type="text" /></dd></dl></li>' +
				  			'<li><dl><dt><label for="nc-wysiwyg-insertlink-target">'+ __d('nc_wysiwyg_insertlink', 'target') +'</label></dt><dd><select id="nc-wysiwyg-insertlink-target" class="nc-wysiwyg-insertlink-select" name="url_target"><option value="">'+ __d('nc_wysiwyg_insertlink', 'target_none') +'</option><option value="_blank">'+ __d('nc_wysiwyg_insertlink', 'target_blank') +'</option><option value="_other">'+ __d('nc_wysiwyg_insertlink', 'target_other') +'</option></select>&nbsp;<input id="nc-wysiwyg-insertlink-other" name="other" size="10" style="visibility: hidden;" type="text" /></dd></dl></li>' +
				  		'</ul><div class="nc-wysiwyg-insertlink-btn"><input id="nc-wysiwyg-insertlink-ok" class="common-btn" name="ok" type="button" value="' + __d('nc_wysiwyg_insertlink', 'ok') + '" />' +
				  	'&nbsp;<input id="nc-wysiwyg-insertlink-cancel" name="cancel" class="common-btn" type="button" value="' + __d(['nc_wysiwyg', 'dialog'], 'cancel') + '" /></div>'
	        }, options);

		var self = this, u, t, ta, o;

		// 初期値セット - Form作成
		init(options);

		// イベント
		$("#nc-wysiwyg-insertlink-target").change(function(e){
			return changeTarget();
		});
		$("#nc-wysiwyg-insertlink-ok").click(function(e){
			// OK
			return clickLink(e);
		});
		$("#nc-wysiwyg-insertlink-cancel").click(function(e){
			// cancel
			return clickCancel(e);
		});

		// focus：2度目の表示がfocusされないため、timerとする
		setTimeout(function() { $("#nc_wysiwyg_insertlink_url").focus(); }, 100);

		return;

		function init(options) {
			// create form
			self.append(options.html);

			// el取得
			u = $("#nc-wysiwyg-insertlink-url");
			t = $("#nc-wysiwyg-insertlink-title");
			ta = $("#nc-wysiwyg-insertlink-target");
			o = $("#nc-wysiwyg-insertlink-other");

			// 初期値セット
			u.val(options.url);
			t.val(options.title);
			if(options.target != '_blank' && options.target != '') {
				ta.val('_other');
				o.css({visibility : 'visible'});
				o.val(options.target);
			} else
				ta.val(options.target);
		}

		function clickLink(e) {
			var arg = {};
			if($(u).val() == '' || $(u).val() == 'http://') {
				// エラー(未入力)
				alert(__d('nc_wysiwyg_insertlink', 'err_url'));
				u.focus();
				return false;
			}

			arg['href'] = $(u).val();
			if($(t).val() != '') arg['title'] = $(t).val();
			var ta_val = $("#nc-wysiwyg-insertlink-target").val();
			if(ta_val != '')
				arg['target'] = (ta_val != '_other') ? ta_val : $("#nc-wysiwyg-insertlink-other").val();

			if(options.callback)
				if(!options.callback.apply(self, [arg]))
					return false;
			//$(self).remove();
			e.preventDefault();
	        return false;
		}

		function clickCancel(e) {
			if(options.cancel_callback)
				if(!options.cancel_callback.apply(self))
					return false;
			//$(self).remove();
			e.preventDefault();
	        return false;
		}

		function changeTarget() {
			// ターゲット（その他の場合、テキスト表示）
			$(o).css({visibility : $("#nc-wysiwyg-insertlink-target").val() == '_other' ? 'visible' : 'hidden'});
			if($("#nc-wysiwyg-insertlink-target").val() == '_other') o.focus();
		}
	}
})(jQuery);