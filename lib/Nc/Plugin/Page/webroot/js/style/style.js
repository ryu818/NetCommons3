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
 * ライブラリのカレント高さのいくつ分で次のデータを取得するか
 * @var     integer
 */
		refreshThreshold: 3,
					
/**
 * フォント設定初期処理
 * @param   string id
 * @return  void
 * @since   v 3.0.0.0
 */
		initFont: function(id) {
			var tabs = $('#pages-menu-style-init-tab');
			var scope = $('#' + id + '-scope');
			var lang = $('#' + id + '-lang');
			var accordion = $('#' + id + '-accordion');
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
 * レイアウト変更初期処理
 * @param   string id
 * @param   string  (headercolumn:0|1)_(leftcolumn:0|1)_(rightcolumn:0|1)_(footercolumn:0|1)
 * @return  void
 * @since   v 3.0.0.0
 */
		initLayout: function(id, layouts) {
			this._initScope(id);
			this.highlightLayout(true, layouts);
			
		},

/**
 * 背景初期処理
 * @param   string id
 * @return  void
 * @since   v 3.0.0.0
 */
		initBackground: function(id) {
			var accordion = $('#' + id + '-accordion');
			var form = $('#Form' + id);
			var colors = $('#pages-menu-backgrounds-colors');
			var categories = $('#pages-menu-backgrounds-categories');
			var picker = $('#' + id + '-picker');
			var patterns, images, colorCode = $.Common.getColorCode($('body'), 'background-color');
			
			this._initScope(id);
			
			accordion.accordion({
				'active' : 0
			});
			
			colors.select2({
				minimumResultsForSearch:-1
			}).on("change", function() {
				$.PageStyle.setConfirm(id, 'search');
				$('input[name=\"data[Background][patterns_page]\"]:first', form).val(1);
				$('input[name=\"data[Background][images_page]\"]:first', form).val(1);
				form.submit();
			});
			categories.select2({
				minimumResultsForSearch:-1
			}).on("change", function() {
				$.PageStyle.setConfirm(id, 'search');
				$('input[name=\"data[Background][patterns_page]\"]:first', form).val(1);
				$('input[name=\"data[Background][images_page]\"]:first', form).val(1);
				form.submit();
			});

			picker.colpick({
				layout: 'full', 
				flat:true, 
				submit:0, 
				color: colorCode,
				onChange: function(hsb,hex,rgb,fromSetColor){
					$.PageStyle.setBackgroundColor('#' + hex);
				}
			});
			$.PageStyle.setBackgroundColor(colorCode);
			$.PageStyle.highlightPattern(true);
			$(".pages-menu-backgrounds", accordion).each(function(){
				var ul = $(this);
				if(!patterns) {
					patterns = ul;
				} else {
					images = ul;
				}
				ul.bind("scroll.uploadScrollLibrary", function(e) {
					$.PageStyle.scrollBackground(e, ul);
				});
				if(ul.get(0).clientHeight > 0 && ul.get(0).clientHeight == ul.get(0).scrollHeight) {
					// Scrollバーがでていないのに、Moreが存在する。=> 再取得
					var hasMore = $(".pages-menu-hasmore:first", ul);
					if(hasMore && hasMore.get(0)) {
						hasMore.children(0).click();
						hasMore.remove();
					}
				}
			});
			// 検索
			form.on('ajax:success', function(e, res) {
				var reHtml = new RegExp("^<ul", 'i');
				if($.trim(res).match(reHtml)) {
					$(res).each(function(){
						var ul = $(this);
						var type = $('input[name=\"type\"]:first', form).val();
						if(ul.get(0).tagName != 'UL') {
							return true;
						}
						if(type == 'search') {
							if(ul.hasClass('pages-menu-patterns-search')) {
								patterns.html('');
							} else {
								images.html('');
							}
						} 
						
						if(ul.hasClass('pages-menu-patterns-search')) {
							ul.children().appendTo(patterns);
						} else {
							ul.children().appendTo(images);
						}
						$.PageStyle.highlightPattern();
					});
					return false;
				}
			});
		},
		
/**
 * 適用範囲セレクトボックス初期処理
 * @param   string id
 * @return  void
 * @since   v 3.0.0.0
 */
		_initScope: function(id) {
			var scope = $('#' + id + '-scope');
			var lang = $('#' + id + '-lang');
			scope.select2({
				minimumResultsForSearch:-1
			});
			lang.select2({
				minimumResultsForSearch:-1
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
			var redirectHidden = $('#' + id + '-redirect');
			var redirect = 0;
			if(!submit) {
				submit = form.attr('data-ajax-confirm');
			}
			var reset = form.attr('data-confirm-reset');
			
			if(type == 'submitLayout') {
				form.attr('data-ajax-confirm', submit);
				form.removeAttr('data-ajax-callback');
				redirect = 1;
				form.attr('data-ajax-callback', 'location.reload(true);');
				type = 'submit';
			} else 
				if(type == 'submit') {
				form.attr('data-ajax-confirm', submit);
				form.removeAttr('data-ajax-callback');
			} else if(type == 'reset') {
				form.attr('data-confirm-submit', submit);
				form.attr('data-ajax-confirm', reset);
				redirect = 1;
				form.attr('data-ajax-callback', 'location.reload(true);');
			} else {
				form.attr('data-confirm-submit', submit);
				form.attr('data-ajax-confirm', '');
			}
			redirectHidden.val(redirect);
			typeHidden.val(type);
		},
		
/**
 * ライブラリのスクロールバーが下の位置にきたら、画像ライブラリを追記読み込み。
 * @param   element a
 * @return  void
 */
		scrollBackground: function(e, list) {
			var hasMore = $(".pages-menu-hasmore:first", list);
			if ( hasMore.get(0) && list.get(0).scrollHeight < list.get(0).scrollTop + ( list.get(0).clientHeight * $.PageStyle.refreshThreshold ) ) {
				hasMore.children(0).click();
				hasMore.remove();
			}
		},

/**
 * Backgroundカラー変更
 * @param   string  color
 * @return  void
 */
		setBackgroundColor: function(color) {
			var data = {
				'body, .pages-menu-background, .pages-menu-background-color-link':{'background-color' : color}
			};
			var backgroundColorHidden = $('#pages-menu-background-color-hidden');
			
			var css = $.tmpl('pagestyleTemp', {'id':'pages-style-background-color-css','data':data});
			$('#pages-style-background-color-css').remove();
			$("head").append( css );
			backgroundColorHidden.val(color);
		},
		
/**
 * Backgroundサムネイル画像クリック
 * @param   element a
 * @return  void
 */
		clickBackground: function(a) {
			var data = {'body':{'background-image' : 'url(' + $(a).attr('href') + ')'}};
			data['body']['background-position'] = 'left top';
			data['body']['background-repeat'] = 'repeat';
			
			var css = $.tmpl('pagestyleTemp', {'id':'pages-style-background-css','data':data});
			$('#pages-style-background-css').remove();
			$("head").append( css );
			$.PageStyle.highlightPattern();
		},
		
/**
 * 背景パターンハイライト処理
 * @param   boolean initializeかどうか
 * @return  void
 */
		highlightPattern: function(init) {
			var backgroundImage = $('body').css('background-image');
			var addHighlight = false;
			var removeHighlight = (init) ? true : false;
			var list = ['#pages-menu-background-patterns', '#pages-menu-background-images'];
			var backgroundImageHidden = $('#pages-menu-background-image-hidden');
			if(backgroundImage == 'none') {
				backgroundImage = '#';
			} else {
				if(backgroundImage.match(/url\(['"]{1}(.*)['"]{1}\)/i)) {
					backgroundImage = RegExp.$1;
				}
			}
			if(backgroundImage == '#') {
				backgroundImageHidden.val('none');
			} else {
				backgroundImageHidden.val(backgroundImage);
			}
			
			for (var i=0, numArity=list.length; i<numArity; i++) {
				$('a.pages-menu-background', $(list[i])).each(function(){
					if(!addHighlight && $(this).attr('href') == backgroundImage) {
						addHighlight = true;
						$(this).parent().addClass('pages-menu-background-highlight');
					} else if(!removeHighlight && $(this).parent().hasClass('pages-menu-background-highlight')) {
						removeHighlight = true;
						$(this).parent().removeClass('pages-menu-background-highlight');
					}

					if(addHighlight && removeHighlight) {
						return false;
					}
				});
				if(addHighlight && removeHighlight) {
					break;
				}
			}
		},

/**
 * カラー選択クリック
 * @param   element a
 * @return  void
 */
		clickBackgroundSub: function(a) {
			var id = $(a).attr('data-background-id');
			var groupId = $(a).attr('data-background-group-id');
			var parentA = $("#pages-menu-background-group-" + groupId);
			parentA.attr('data-background-id', id)
				.attr('href', $(a).attr('href')).css('background-image', 'url(' + $(a).attr('href') + ')');
			////$('#pages-menu-select-color-' + groupId).dialog('close');
			$.PageStyle.clickBackground(parentA);
		},
		
/**
 * レイアウトハイライト処理
 * @param   boolean initializeかどうか
 * @param   string  (headercolumn:0|1)_(leftcolumn:0|1)_(rightcolumn:0|1)_(footercolumn:0|1)
 * @return  void
 */
		highlightLayout: function(init, layouts) {
			var addHighlight = false;
			var removeHighlight = (init) ? true : false;
			var layoutHidden = $('#pages-menu-layout-hidden');
			var layoutArr = layouts.split("_");
			
			layoutHidden.val(layouts);
			
			$('a.pages-menu-layout', $('#pages-menu-layout-list')).each(function(){
				if(!addHighlight && $(this).attr('data-layout') == layouts) {
					addHighlight = true;
					$(this).parent().addClass('pages-menu-background-highlight');
				} else if(!removeHighlight && $(this).parent().hasClass('pages-menu-background-highlight')) {
					removeHighlight = true;
					$(this).parent().removeClass('pages-menu-background-highlight');
				}

				if(addHighlight && removeHighlight) {
					return false;
				}
			});
		},
	};
})(jQuery);