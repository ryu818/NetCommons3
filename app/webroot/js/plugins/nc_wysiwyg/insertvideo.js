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
			html = $('#nc_wysiwyg_insertvideo_f_video_el').val();
			if (options.callback && inputChecked())
				options.callback(html);
		});

		$(cancel).click(function(){
			$(self).remove();
		});

		// focus：2度目の表示がfocusされないため、timerとする
		setTimeout(function() { $('#nc_wysiwyg_insertvideo_f_video_el')[0].focus(); }, 100);

		return;

		function init() {
			var div,buttons,px,msg;

			if ($._nc.nc_wysiwyg['allow_video'] == 2)
				msg = __d('nc_wysiwyg_insertvideo', 'mod_desc');
			else
				msg = __d('nc_wysiwyg_insertvideo', 'desc');

			$(self).append('<div class="nc_wysiwyg_insertvideo_dialog_title">'+ __d('nc_wysiwyg_insertvideo', 'dialog_title') +'</div>');
			div = $('<div><div id="nc_wysiwyg_insertvideo_video_msg">'+ msg +'</div>'+
					  '<div><textarea id="nc_wysiwyg_insertvideo_f_video_el" cols="20" rows="4"></textarea></div></div>').appendTo(self);

			buttons = $('<div class="btn-bottom"></div>').appendTo(div);

			ok = $('<input name="ok" type="button" class="common_btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'ok')+'" />').appendTo(buttons);
			cancel = $('<input name="cancel" type="button" class="common_btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'cancel')+'" />').appendTo(buttons);

			//各ブラウザ毎にwidthの長さを変える
			px = $('#nc_wysiwyg_insertvideo_f_video_el')[0].clientWidth;
			$('#nc_wysiwyg_insertvideo_video_msg').css({width : px});

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