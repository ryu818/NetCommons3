/*
 * NC InsertTable 0.0.0.1
 * @param e event object
 * @param options hash
 * 					row				: inserttable用のtableの行数
 * 					col				: inserttable用のtableの列数
 * 					border			: ボーダーの太さ
 * 					cellspacing		: テーブルの行間の長さ
 * 					cellpadding		: セル全体のpadding
 * 					tableStyle		: table用のスタイル
 * 					tdStyle			: td用のスタイル
 *
 */
 ;(function($) {
	$.fn.nc_inserttable = function(e, options) {
		var options = $.extend({
			'callback'	: null,
			'row'			: 5,
			'col'				: 5,
			'border'			: '1',
			'cellspacing'	: '0',
			'cellpadding'	: '0',
			'tableStyle'		: {'border' : '1px solid rgb(0, 0, 0)', 'margin' : '5px', 'width' : '200px', 'border-collapse' : 'collapse'},
			'tdStyle'		: {'border' : '1px solid rgb(0, 0, 0)'}
		}, options);

		var self = this;
		init();

		return;

		function init() {
			var top_el = self;
			top_el.html('<div class="insertTable-title" >' + __d('nc_wysiwyg_inserttable', 'dialog_title') + '</div>'+
				 							'<table class="insertTable-dialog" ></table>'+
				 							'<div class="table-size align-center" > 0x0 </div>');
			var table = $('.insertTable-dialog', top_el);
			for (i = 1; i <= options['row']; i++) {
				var tr = $('<tr id="row-'+ i +'" ></tr>');
				table.append(tr);

				for (y = 1;y <= options['col']; y++) {
					tr.append($('<td id="td-'+ i + '-' + y +'" ></td>')
						.hover(function () {
							onHoverEvent(this);
						})
						.click(function () {
							onClickEvent(this);
						})
					);
				}
			}
		}

		function onHoverEvent(el) {
			var top_el = self;
			var table = $('.insertTable-dialog', top_el)[0];
			var id_arr = el.id.split("-");
			var row = id_arr[1];
			var col = id_arr[2];
			var tdList = table.getElementsByTagName("td");
			for (i = 0; i < tdList.length; i++){
				id_arr = tdList[i].id.split("-");
				var now_row = id_arr[1];
				var now_col = id_arr[2];
				if(now_row <= row && now_col <= col) {
					$(tdList[i]).css({backgroundColor : '#e6e6e6'});
				} else {
					$(tdList[i]).css({backgroundColor : ''});
				}
			}
			$('.table_size', self).html('<div class="table-size align-center" > '+ row + 'x' + col +' </div>');
		}

		function onClickEvent(el) {
			var id_arr = el.id.split("-");
			var row = id_arr[1];
			var col = id_arr[2];

			options['row'] = row;
			options['col'] = col;
			options['tdStyle']['width'] = Math.floor(100/col) +'%';
			var html = createTable();
			if(options.callback)
				options.callback.apply(el, [html]);
		}

		function createTable() {

			html = "";
			html += '<table summary="" cellspacing="'+ options['cellspacing'] +'" cellpadding="'+ options['cellpadding'] +'" style="'+ createStyle(options['tableStyle']) +'" >';
			for (i = 1; i <= options['row']; i++) {
				html += '<tr>';
				for (y = 1; y <= options['col']; y++) {
					html += '<td style="'+ createStyle(options['tdStyle']) +'">&nbsp;</td>';
				}
				html += '</tr>';
			}
			html += '</table><br />';
			return html;
		}

		function createStyle(optStyle) {
			var style = "";

			for (k in optStyle) {
				style += k +": "+ optStyle[k] +"; ";
			}

			return style;
		}
	}
})(jQuery);