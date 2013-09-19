/*
 * NC InsertVideo 0.0.0.1
 * @param options hash
 * 					callback	: fanction (default : null)
 */
;(function($) {
	$.fn.nc_insertvideo = function(options) {
		var options = $.extend({
	            callback        : null
	        }, options);
		var self = this,html,ok,cancel;

		init();

		$(ok).click(function(){
			html = $('#nc-wysiwyg-insertvideo-f-video-el').val();
			if (options.callback && inputChecked())
				options.callback(html);
		});

		$(cancel).click(function(){
			$(self).remove();
		});

		// focus：2度目の表示がfocusされないため、timerとする
		setTimeout(function() { $('#nc-wysiwyg-insertvideo-f-video-el')[0].focus(); }, 100);

		return;

		function init() {
			var div,buttons,px,msg;

			if ($._nc.nc_wysiwyg['allow_video'] == 2)
				msg = __d('nc_wysiwyg_insertvideo', 'mod_desc');
			else
				msg = __d('nc_wysiwyg_insertvideo', 'desc');

			$(self).append('<div class="nc-wysiwyg-insertvideo-dialog-title">'+ __d('nc_wysiwyg_insertvideo', 'dialog_title') +'</div>');
			div = $('<div><div id="nc-wysiwyg-insertvideo-video-msg">'+ msg +'</div>'+
					  '<div><textarea id="nc-wysiwyg-insertvideo-f-video-el" cols="20" rows="4"></textarea></div></div>').appendTo(self);

			buttons = $('<div class="btn-bottom"></div>').appendTo(div);

			ok = $('<input name="ok" type="button" class="common-btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'ok')+'" />').appendTo(buttons);
			cancel = $('<input name="cancel" type="button" class="common-btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'cancel')+'" />').appendTo(buttons);

			//各ブラウザ毎にwidthの長さを変える
			px = $('#nc-wysiwyg-insertvideo-f-video-el')[0].clientWidth;
			$('#nc-wysiwyg-insertvideo-video-msg').css({width : px});

		}

		function inputChecked() {
			if (!html.match(/^\s*<(iframe|object|embed).*>(.|\s)*<\/(iframe|object|embed)>\s*$/i)) {
				alert(__d('nc_wysiwyg_insertvideo', 'error_mes'));
				return false;
			} else {
				return true;
			}
		}
	}
})(jQuery);