/**
 * リビジョン js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.fn.Revision = function(id) {
		var table = $(this);
		var radios = $('input:radio', table);
		/* ラジオボタン操作 */
		radios.click(function(e) {
 			radios.each(function(){
 				var row = $(this).parents('tr:first');
 				var cell = $(this).parents('th:first');
 				var rowIndex = row[0].rowIndex;
 				var orherRadio = null;
 				var isOld = false, isDisplay = true;

 				if($(this).is('[name=revision_id]')) {
					isOld = true;
				}
 				if(isOld) {
 					orherRadio = $('input:radio:first', cell.next());
 				} else {
 					orherRadio = $('input:radio:first', cell.prev());
 				}

 				if(!orherRadio.is(':checked') && !$(this).is(':checked')) {
					if(rowIndex == 1) {
						if(isOld) {
							isDisplay = false;
						}
					} else {
						if(!isOld) {
							isDisplay = false;
						}
					}
 				}
 				if(isDisplay) {
 					$(this).removeClass('display-none');
 				} else {
 					$(this).addClass('display-none');
 				}
 			});
		});

	};

	$.Revision = {
		compare : function(e, id) {
			var form = $('#Form' + id),action = form.attr('action');
			var datas = form.serializeArray();
			var params = '';
			$.each(datas, function(i, field){
				if(field.name != 'current_revision_id' && field.name != 'revision_id') {
					return;
				}
				if(params == '') {
					params += '?';
				} else {
					params += '&';
				}
				params += field.name + '=' + field.value;
			});
			var url = action + params + '#' + id;
			location.href = url;
			e.preventDefault();
		}
	}
})(jQuery);