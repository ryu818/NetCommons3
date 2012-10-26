/*
 * NC mimeTex 0.0.0.1
 * @param options hash
 * 					url			: string texへの変更のためのphpファイルのアドレス(default : $._base_url + 'mimetex/')
 * 					data		: nc2系のための項目
 * 					callback	: function (default : '')
 *                  text        : string textに渡す初期値
 */
  ;(function($) {
	$.fn.nc_mimetex = function(e, options) {
		var options = $.extend({
			url             : $._base_url + 'nccommon/mimetex/',
			data		 : '',
			callback	: '',
			text         : ''
		}, options);
		var self = this,input,preview,ok,cancel;

		init();

		//クリック時のイベント
		$(preview).click(function(e){
			if (inputChecked()) {
				var texurl = getTexURL(input.val());
				texurl = texurl.replace(/\'/g, "\\'");
				$('.nc_wysiwyg_mimetex_preview').css("background","#ffffff url('" + texurl + "') no-repeat");
			}
		});

		$(ok).click(function(){
			if (inputChecked()) {
				var texurl = getTexURL(input.val());
				var teximg = '<img class="tex" alt="'+ input.val() +'" src="'+ texurl +'" />';
				if(options['callback'])
					options['callback'](teximg);
			}
		});

		$(cancel).click(function(){
			self.remove();
		});

		return;

		function init() {
			var tex,upload;

			if(options.text != '') {
				options.text = decodeURI(options.text);
			}
			$('<div class="nc_wysiwyg_mimetex_dialog_title">'+ __d('nc_wysiwyg_mimetex', 'dialog_title') +'</div>'+
			  '<div class="nc_wysiwyg_mimetex_preview_title">'+ __d('nc_wysiwyg', 'preview') +'</div><div class="nc_wysiwyg_mimetex_preview" />').appendTo(self);

			tex = $('<div class="nc_wysiwyg_mimetex_outer"><div>'+ __d('nc_wysiwyg_mimetex', 'error_mes') +'</div>'+
					'<input class="nc_wysiwyg_mimetex_input" type="text" name="mimetex" value="'+options.text+'" /></div>').insertAfter('.nc_wysiwyg_mimetex_dialog_title');
			upload	= $('<div class="nc_wysiwyg_mimetex_btn"></div>').appendTo(self);

			//ボタンの追加
			preview		= $('<input type="button" value="'+ __d('nc_wysiwyg', 'preview') +'" name="preview" style="margin-left: 5px;" />').appendTo(tex);
			ok			= $('<input name="ok" type="button" class="common_btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'ok')+'" />').appendTo(upload);
			cancel		= $('<input name="cancel" type="button" class="common_btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'cancel')+'" />').appendTo(upload);

			input = $('.nc_wysiwyg_mimetex_input', tex);

			// focus：2度目の表示がfocusされないため、timerとする
			setTimeout(function() { input.focus(); }, 100);
			if(options.text != '') {
				$(preview).click();
			}
		}

		//入力チェックする内容は増えても良いように関数化
		function inputChecked() {
			if (!input.val()) {
				alert(__d('nc_wysiwyg_mimetex', 'error_mes'));
			} else {
				return true;
			}
			return false;
		}

		//指定した文字列をTex変換するためのURLに変換
		function getTexURL(uri) {
			var data = options.data == '' ? options.data : options.data + "&";
			return options.url + "?"+ data +"c=" + encodeURI(uri);
		}

		//文字列をURLエンコードする
		function encodeURI(uri) {
			uri = uri.replace(/\'/g, "\\'");
			uri = encodeURIComponent(uri).replace(/%C2%A5/g,"%5C").replace(/%/g, "%_");
			return uri;
		}
		//文字列をURLデコードする
		function decodeURI(uri) {
			uri = uri.replace(/%5C/g,"%C2%A5").replace(/%_/g, "%");
			uri = decodeURIComponent(uri);
			return uri;
		}
	}
})(jQuery);