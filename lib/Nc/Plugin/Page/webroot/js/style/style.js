/**
 * ページスタイル js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.PageStyle = {
/**
 * フォント設定初期処理
 * @param   string id
 * @return  void
 * @since   v 3.0.0.0
 */
		initFont: function(id) {
			var tabs = $('#pages-menu-style-init-tab');
			var scope = $('#pages-menu-style-scope');
			var lang = $('#pages-menu-style-lang');
			var accordion = $('#pages-menu-style-font-accordion');
			var _settingBorderStyleOption = function(option) {
				return '<div title="'+option.text+'" style="border-bottom:3px '+option.text+'; height:12px;"></div>';
			};
			var form = $('#Form' + id);
			var data = {}, containerFontSize;
			var _settingFormData = function(obj, initFlag) {
				var reg = new RegExp("^data\\[PageStyle\\]\\[style\\]", 'i'), input, el, value, containerFontSize, difference;
				if(!obj['name'].match(reg)) {
					return true;
				}
				
				var bufName = obj['name'].replace(reg, '').replace(/\[/g, '');
				var bufArr = bufName.split(']'), bufValue;
				if(!data[bufArr[0]]) {
					data[bufArr[0]] = {};
				}
				if(initFlag) {
					input = $('[name="'+obj['name']+'"]', form);
					if(bufArr[0] == 'body' || bufArr[0] == '#container') {
						el = $(bufArr[0]+':first');
					} else {
						el = $(bufArr[0]+':first', $('#pages-menu-style-visible-hide'));
					}
					value = el.css(bufArr[1]);
					if(bufArr[1] == 'font-size') {
						if(bufArr[0] == '#container') {
							value = Math.round(value.replace(/px$/g, ''));
							value = parseInt(value);
							input.children().each(function(){
								if(value == parseInt($(this).html())) {
									value = $(this).val();
								}
							});
						} else {
							containerFontSize = $('#container:first').css('font-size');
							value = Math.round((value.replace(/px$/g, '')/containerFontSize.replace(/px$/g, ''))*100);
							if(value%8 != 0) {
								// ie
								if(value%8 > 4) {
									value = value+8-value%8;
								} else {
									value = value-8+value%8;
								}
							}
							value += '%'; 
							input.val(value);
						}
					} else if(bufArr[1] == 'font-family') {
						bufValue = value.split(',');
						value = $.trim(bufValue[0]).replace(/'$/g, '').replace(/^'/g, '');
					} else if(bufArr[1] == 'line-height') {
						containerFontSize = $('#container:first').css('font-size');
						value = Math.round((value.replace(/px$/g, '')/containerFontSize.replace(/px$/g, ''))*100);
						if(value%10 != 0) {
							// ie
							value = Math.round(value/10) + '0';
						}
						value += '%';
					} else if(bufArr[1] == 'color' || bufArr[1] == 'border-top-color') {
						value = $.Common.getColorCode(el, bufArr[1]);
					}
					input.val(value);
					obj['value'] = value;
				}
				data[bufArr[0]][bufArr[1]] = obj['value'];
			};
			
			$(form.serializeArray()).each(function(){
				_settingFormData(this, true);
			});
			$.template( "pagestyleTemp", $('#pages-style-css-template').html());

			tabs.tabs({
				beforeActivate: function( event, ui ) {
					var a = ui.newTab.children('a:first');
					if(ui.newPanel.html() != '') {
						var href = a.attr('href');
						// 既に表示中 - Ajaxで再取得しない。
						a.attr('href', '#' + ui.newPanel.attr('id')).attr('data-url', href);
					}
				}
			});
			scope.select2({
				minimumResultsForSearch:-1
			});
			lang.select2({
				minimumResultsForSearch:-1
			});
			
			accordion.accordion({
				'active' : 0
			});
			
			$(':input', accordion).each(function(){
				var input = $(this);
				if(input.hasClass('pages-menu-style-colorpicker')) {
					input.colpick();
				} else if(input.hasClass('pages-menu-style-border-style')) {
					input.select2({
						minimumResultsForSearch:-1,
						formatResult: _settingBorderStyleOption,
						formatSelection: _settingBorderStyleOption
					});
				}
				
				input.change(function(e){
					var obj = {'name': $(this).attr('name'), 'value': $(this).val()};
					_settingFormData(obj);
					// CSS 適用
					var css = $.tmpl('pagestyleTemp', {'id':'pages-style-font-css','data':data});
					$('#pages-style-font-css').remove();
					$("head").append( css );
				});
			});
		},

/**
 * 確認ダイアログメッセージ変更
 * @param   string id
 * @param   string submit|reset
 * @return  void
 * @since   v 3.0.0.0
 */
		setConfirm: function(id, type) {
			var form = $('#Form' + id);
			var submit = form.attr('data-confirm-submit');
			var typeHidden = $('#' + id + '-type');
			if(!submit) {
				submit = form.attr('data-ajax-confirm');
			}
			var reset = form.attr('data-confirm-reset');
			if(type == 'submit') {
				form.attr('data-ajax-confirm', submit);
				form.removeAttr('data-ajax-callback');
			} else {
				form.attr('data-confirm-submit', submit);
				form.attr('data-ajax-confirm', reset);
				form.attr('data-ajax-callback', 'location.reload(true);');
			}
			typeHidden.val(type);
		}
	};
})(jQuery);