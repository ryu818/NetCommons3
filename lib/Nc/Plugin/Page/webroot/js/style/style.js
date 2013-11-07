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
 * @param   boolean isPost
 * @return  void
 * @since   v 3.0.0.0
 */
		initFont: function(id, isPost) {
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
				var reg = new RegExp("^data\\[PageStyle\\]\\[style\\]", 'i'), input, el, value, containerFontSize;
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
					if(bufArr[0] == 'body' || bufArr[0] == '#parent-container') {
						el = $(bufArr[0]+':first');
					} else {
						el = $(bufArr[0]+':first', $('#pages-menu-style-visible-hide'));
					}
					value = el.css(bufArr[1]);
					if(bufArr[1] == 'font-size') {
						if(bufArr[0] == '#parent-container') {
							value = Math.round(value.replace(/px$/g, ''));
							value = parseInt(value);
							input.children().each(function(){
								if(value == parseInt($(this).html())) {
									value = $(this).val();
								}
							});
						} else {
							containerFontSize = $('#parent-container:first').css('font-size');
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
						containerFontSize = $('#parent-container:first').css('font-size');
						value = Math.round((value.replace(/px$/g, '')/containerFontSize.replace(/px$/g, ''))*100);
						if(value%10 != 0) {
							// ie
							value = Math.round(value/10) + '0';
						}
						value += '%';
					} else if(bufArr[1] == 'color' || bufArr[1] == 'border-top-color') {
						value = $.Common.getColorCode(el, bufArr[1]);
					}
					if(!isPost || input.attr('type') != 'text') {
						input.val(value);
					}

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
			this._initScope(id);

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
			var backgroundOriginal = $('#pages-menu-background-original');
			var backgroundHref = $('a:first', $('#pages-menu-background-original-outer'));
			var backgroundOriginalLink = $('a', $('.pages-menu-background-original-type:first', backgroundOriginal));

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
			$.PageStyle.highlightPattern('patterns', true);
			$.PageStyle.highlightPattern('images', true);
			$(".pages-menu-backgrounds", accordion).each(function(){
				var ul = $(this);
				if(ul.parents('div:first').attr('id') == 'pages-menu-background-patterns') {
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
						$.PageStyle.highlightPattern('patterns');
						$.PageStyle.highlightPattern('images');
					});
					return false;
				}
			});

			// オリジナル背景 - サムネイルクリック
			backgroundHref.click(function(e){
				$.PageStyle.clickBackgroundFile(this);
				$.Event(e).preventDefault();
			});
			$('input[name="data[PageStyle][original_background_attachment]"]', backgroundOriginal).change(function(e){
				$.PageStyle.clickBackgroundFile(backgroundHref);
			});
			$('#pages-menu-background-original-position').change(function(e){
				$.PageStyle.clickBackgroundFile(backgroundHref);
			});
			// オリジナル背景 - repeat
			backgroundOriginalLink.click(function(e){
				var self = this;
				backgroundOriginalLink.each(function(){
					if(this != self) {
						$(this).parent().removeClass('pages-menu-background-highlight');
					}
				});
				$(this).parent().addClass('pages-menu-background-highlight');
				$('#pages-menu-background-original-repeat-hidden').val($(this).attr('data-background-repeat'));
				$.PageStyle.clickBackgroundFile(backgroundHref);
				$.Event(e).preventDefault();
			});
		},

/**
 * 表示位置変更初期処理
 * @param   string id
 * @param   boolean isPost
 * @return  void
 * @since   v 3.0.0.0
 */
		initDisplayPosition: function(id, isPost) {
			this._initScope(id);

			var form = $('#Form' + id);
			var data = {};
			var _settingFormData = function(obj, initFlag) {
				var reg = new RegExp("^data\\[PageStyle\\]\\[style\\]", 'i'), input, el, value;
				input = $('[name="'+obj['name']+'"]', form);
				if(!obj['name'].match(reg)) {
					if(initFlag) {
						el = $('#container');
						switch(obj['name']) {
							case 'data[PageStyle][align]':
								var inputLeft = $('[name="data[PageStyle][style][#container][margin-left]"]', form);
								var inputRight = $('[name="data[PageStyle][style][#container][margin-right]"]', form);
								input.change(function(e){
									if($(this).val() == 'center') {
										inputLeft.attr('disabled', true);
										inputRight.attr('disabled', true);
									} else {
										inputLeft.attr('disabled', false);
										inputRight.attr('disabled', false);
									}
									if($(this).val() == 'center') {
										el.css({'margin-left' : 'auto', 'margin-right' : 'auto', 'float': 'none'});
									} else if($(this).val() == 'right') {
										el.css({'margin-left' : '', 'margin-right' : '', 'float': 'right'});

									} else {
										el.css({'margin-left' : '', 'margin-right' : '', 'float': 'none'});
									}
								});
								if($(input).val() == 'center') {
									inputLeft.attr('disabled', true);
									inputRight.attr('disabled', true);
								}
								break;
							case 'data[PageStyle][width]':
								var inputSubWidth = $('[name="data[PageStyle][width-custom]"]', form);
								input.change(function(e){
									if($(this).val() != 'by hand') {
										inputSubWidth.attr('disabled', true);
										el.css({'width' : $(this).val()});
									} else {
										inputSubWidth.attr('disabled', false);
										el.css({'width' : inputSubWidth.val() + 'px'});
									}
								});
								if($(input).val() != 'by hand') {
									inputSubWidth.attr('disabled', true);
								}
								break;
							case 'data[PageStyle][height]':
								var inputSubHeight = $('[name="data[PageStyle][height-custom]"]', form);
								input.change(function(e){
									if($(this).val() != 'by hand') {
										inputSubHeight.attr('disabled', true);
										el.css({'height' : $(this).val()});
									} else {
										inputSubHeight.attr('disabled', false);
										el.css({'height' : inputSubHeight.val() + 'px'});
									}
								});
								if($(input).val() != 'by hand') {
									inputSubHeight.attr('disabled', true);
								}
								break;
							case 'data[PageStyle][width-custom]':
								input.change(function(e){
									el.css({'width' : $(this).val() + 'px'});
								});
								break;
							case 'data[PageStyle][height-custom]':
								input.change(function(e){
									el.css({'height' : $(this).val() + 'px'});
								});
								break;
							}
					}
					return true;
				}

				var bufName = obj['name'].replace(reg, '').replace(/\[/g, '');
				var bufArr = bufName.split(']');
				if(!data[bufArr[0]]) {
					data[bufArr[0]] = {};
				}
				if(initFlag) {
					el = $(bufArr[0]+':first');
					value = el.css(bufArr[1]);
					switch(bufArr[1]) {
						case 'margin-top':
						case 'margin-right':
						case 'margin-bottom':
						case 'margin-left':
							value = parseInt(value);
							if(!isPost || input.attr('type') != 'text') {
								input.val(value);
							}
							break;
					}
					input.change(function(e){
						var obj = {'name': $(this).attr('name'), 'value': $(this).val() + 'px'};
						_settingFormData(obj);
						// CSS 適用
						var css = $.tmpl('pagestyleTemp', {'id':'pages-style-display-position-css','data':data});
						$('#pages-style-display-position-css').remove();
						$("head").append( css );
					});
				}
				if(obj['value'] != '0') {
					data[bufArr[0]][bufArr[1]] = obj['value'];
				} else {
					delete data[bufArr[0]][bufArr[1]];
				}

			};

			$(form.serializeArray()).each(function(){
				_settingFormData(this, true);
			});
		},

/**
 * CSS編集初期処理
 * @param   string id
 * @return  void
 * @since   v 3.0.0.0
 */
		initEditCss: function(id) {
			var input = $('#pages-menu-edit-css-textarea');
			this._initScope(id);

			input.change(function(e){
				// CSS 適用
				var css = $.tmpl('pagestyleTemp', {'id':'pages-style-edit-css','content':$(this).val()});
				$('#pages-style-edit-css').remove();
				$("head").append( css );
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
				'body, .pages-menu-background, .pages-menu-background-color-link, .pages-menu-background-original-inner':{'background-color' : color}
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
 * @param   string type patterns or images
 * @return  void
 */
		clickBackground: function(a, type) {
			var key = 'body', data;
			if(type == 'patterns') {
				key = '#parent-container'
				if($(a).attr('href') == '#') {
					data = {'#parent-container':{'background-image' : 'none'}};
				} else {
					data = {'#parent-container':{'background-image' : 'url(' + $(a).attr('href') + ')'}};
				}
			} else {
				if($(a).attr('href') == '#') {
					data = {'body':{'background-image' : 'none'}};
				} else {
					data = {'body':{'background-image' : 'url(' + $(a).attr('href') + ')'}};
				}
			}
			data[key]['background-position'] = 'left top';
			data[key]['background-attachment'] = 'scroll';
			data[key]['background-repeat'] = 'repeat';

			var css = $.tmpl('pagestyleTemp', {'id':'pages-style-background-' + type + '-css','data':data});
			$('#pages-style-background-' + type + '-css').remove();
			$("head").append( css );
			$.PageStyle.highlightPattern(type);
		},

/**
 * 背景パターンハイライト処理
 * @param   string type patterns or images
 * @param   boolean initializeかどうか
 * @return  void
 */
		highlightPattern: function(type, init) {
			var addHighlight = false;
			var removeHighlight = (init) ? true : false;
			if(type == 'patterns') {
				var backgroundImage = $('#parent-container').css('background-image');
				var list = '#pages-menu-background-patterns';
				var backgroundImageHidden = $('#pages-menu-background-patterns-hidden');

				var items = $('a.pages-menu-background', $(list));
			} else {
				var backgroundImage = $('body').css('background-image');
				var list = '#pages-menu-background-images';
				var backgroundImageHidden = $('#pages-menu-background-images-hidden');

				var items = $('a.pages-menu-background', $(list));
				items.push($('a:first', $('#pages-menu-background-original-outer')).get(0));
			}


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

			items.each(function(){
				var href, highlight;
				href = $(this).attr('href');
				highlight = $(this).parent();

				if(!addHighlight && href == backgroundImage) {
					addHighlight = true;
					highlight.addClass('pages-menu-background-highlight');
				} else if(!removeHighlight && highlight.hasClass('pages-menu-background-highlight')) {
					removeHighlight = true;
					highlight.removeClass('pages-menu-background-highlight');
				}

				if(addHighlight && removeHighlight) {
					return false;
				}
			});
		},

/**
 * カラー選択クリック
 * @param   element a
 * @param   string type patterns or images
 * @return  void
 */
		clickBackgroundSub: function(a, type) {
			var id = $(a).attr('data-background-id');
			var groupId = $(a).attr('data-background-group-id');
			var parentA = $("#pages-menu-background-group-" + groupId);
			parentA.attr('data-background-id', id)
				.attr('href', $(a).attr('href')).css('background-image', 'url(' + $(a).attr('href') + ')');
			////$('#pages-menu-select-color-' + groupId).dialog('close');
			$.PageStyle.clickBackground(parentA, type);
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

/**
 * オリジナル背景ファイル選択時
 * @param   string  fileName
 * @param   string  url
 * @param   string  libraryUrl
 * @return  void
 */
		selectBackgroundFile: function(fileName, url, libraryUrl) {
			var background = $('a:first', $('#pages-menu-background-original-outer'));
			var div = $('.nc-thumbnail-centered:first', background);
			var img = $('img', div);


			if(!img.get(0)) {
				img = $('<img>');
			}
			background.attr('href', url);
			img.attr('src', libraryUrl);
			div.append(img);

			$('#pages-menu-background-original-image-hidden').val(fileName);
			$.PageStyle.clickBackgroundFile(background);
		},

/**
 * オリジナル背景サムネイルクリック
 * @param   element a
 * @return  void
 */
		clickBackgroundFile: function(a) {
			var top = $('#pages-menu-background-original');
			var url = $(a).attr('href'), css, data = {}, key = 'body';
			var repeat = $('#pages-menu-background-original-repeat-hidden').val();
			if(url == '#') {
				return;
			}
			data = {'body':{'background-image' : 'url(' + url + ')'}};

			data[key]['background-position'] = $('#pages-menu-background-original-position').val();
			data[key]['background-attachment'] = $('input[name="data[PageStyle][original_background_attachment]"]:checked', top).val();


			if(repeat == 'full') {

				data[key]['background-size'] = 'cover';
				data[key]['background-repeat'] = 'no-repeat';
			} else {
				data[key]['background-repeat'] = repeat;
			}


			css = $.tmpl('pagestyleTemp', {'id':'pages-style-background-image-css','data':data});
			$('#pages-style-background-image-css').remove();
			$("head").append( css );
			$.PageStyle.highlightPattern('image');
		}
	};
})(jQuery);