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
			var oldCheckRow = $('input[name=revision_id]:checked:first', '#nc-revisions'+id).parents('tr:first');
			var oldCheckRowIndex = oldCheckRow[0].rowIndex;
			var newCheckRow = $('input[name=current_revision_id]:checked:first', '#nc-revisions'+id).parents('tr:first');
			var newCheckRowIndex = newCheckRow[0].rowIndex;

			radios.each(function(){
				var row = $(this).parents('tr:first');
				var rowIndex = row[0].rowIndex;
				var isOld = false, isDisplay = true;

				if($(this).is('[name=revision_id]')) {
					isOld = true;
				}
				if (isOld && newCheckRowIndex > rowIndex
						|| !isOld && oldCheckRowIndex < rowIndex) {
					isDisplay = false;
				}

				if(isDisplay) {
					$(this).show();
				} else {
					$(this).hide();
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
			var ajaxParams = {
				'data-pjax' : '#' + id,
				'data-ajax-type' : 'get',
				'data-ajax-url' : url
			};
			$.Common.ajax(e, form, ajaxParams);
			e.preventDefault();
		}
	}
})(jQuery);