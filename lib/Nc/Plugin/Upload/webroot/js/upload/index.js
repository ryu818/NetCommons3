/**
 * Upload js
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.webroot.js
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
;(function($) {
	$.Upload = {
/**
 * Wysiwygのオブジェクト保持用
 * @var     array
 */
		setting:{
			img:null,
			wysiwyg:null,
			callback:null
		},

/**
 * ライブラリのカレント高さのいくつ分で次のデータを取得するか
 * @var     integer
 */
		refreshThreshold: 3,

/**
 * Thumbnailの幅、高さの最大値
 * @var     integer
 */
		box: 110,

/**
 * Thumbnailのマージン
 * @var     integer
 */
		space: 5,

/**
 * DialogのTopId
 * @var     string
 */
		dialogId: null,

/**
 * アップロード画面のTopId
 * @var     string
 */
		indexId: null,

/**
 * ライブラリから追加のTopId
 * @var     string
 */
		libraryId: null,
/**
 * URL参照のTopId
 * @var     string
 */
		refUrlId: null,

/**
 * ファイル情報画面のOption値等
 * @var     array
 */
		fileinfoOptions: {},

/**
 * 選択データ
 * @var     object
 */
		data:{},

/**
 * 複数選択を許すかどうか
 * @var     boolean
 */
		multiple:true,

/**
 * 選択データ項目セット処理
 * @param   integer id
 * @param   string key
 * @param   string value
 * @param   string _prefix undefined プレビューを再描画しない。 '' ライブラリーのプレビュー再描画 'preview-' アップロードプレビュー再描画
 * @return  void
 */
		setData: function(id, key, value, _prefix) {
			var previewA;
			if(!$.Upload.data[id]) {
				$.Upload.data[id] = new Object();
			}
			$.Upload.data[id][key] = value;

			if(_prefix != undefined) {
				if(id == 0) {
					previewA = $('#' + this.refUrlId + '-fileinfo-img');
				} else if(_prefix == '') {
					previewA = $('#' + this.libraryId + '-fileinfo-img');
				} else {
					previewA = $('#' + this.indexId + '-fileinfo-img');
				}
				$.Upload._setColorBox(previewA);
			}
		},

/**
 * 選択データセット処理
 * @param   array data
 * @return  void
 */
		setDatas: function(data) {
			var len = data.length, bufData;
			for(var i= 0 ; i < len; i++ ) {
				if(!data[i]['UploadSearch']) {
					bufData = data[i];
				} else {
					bufData = data[i]['UploadSearch'];
				}

				if(!$.Upload.data[bufData['id']]) {
					$.Upload.data[bufData['id']] = bufData;
				}
			}
		},

/**
 * wysiwygオブジェクトセットアップ
 * @param   array setting
 * @return  void
 */
		setup: function(setting) {
			$.extend($.Upload.setting, setting);
		},

/**
 * initialize
 * @param   string id
 * @param   string dialogId
 * @param   array setting
 * @param   integer activeTab
 * @param   array fileinfoOptions
 * @return  void
 */
		init: function(id, dialogId, multiple, setting, activeTab, fileinfoOptions) {
			var tabs = $('#' + id);
			var dialog = $('#' + dialogId);
			$.Upload.dialogId = dialogId;
			$.Upload.multiple = multiple;
			$.Upload.setup(setting);
			$.Upload.fileinfoOptions = fileinfoOptions;
			dialog.dialog('option', 'width', 'auto');
			// テンプレート化
			$.template( "uploadFileinfo", $('#' + dialogId + '-fileinfo-template').html());
			tabs.tabs({
				selected: activeTab,
				beforeActivate: function( event, ui ) {
					var a = ui.newTab.children('a:first');
					if(ui.newPanel.html() != '') {
						var href = a.attr('href');
						// 既に表示中  - Ajaxで再取得しない。
						a.attr('href', '#' + ui.newPanel.attr('id')).attr('data-url', href);

					}
					//a.attr('data-tab-id', ui.newPanel.attr('id'));
					var w = ui.newTab.attr('data-width');
					var position;
					$(window).unbind("resize.uploadResizeLibrary");
					if(w) {
						if(w == 'max') {
							$(window).bind("resize.uploadResizeLibrary", function(e) {
								 if($.Upload.libraryId) {
								 	$.Upload.resizeThumbnail($.Upload.libraryId);
								 }
								 $.Upload.resizeLibrary(e, dialog);
							});

							$.Upload.resizeLibrary(event, dialog);
						} else {
							dialog.dialog('option', 'width', w);
						}
					} else {
						dialog.dialog('option', 'width', 'auto');
					}
					dialog.dialog('option', 'height', 'auto');
				},
				activate: function( event, ui ) {
					var active = tabs.tabs("option","active");
					var disabled = true, $fileInfo, $fileInfoLibrary, $ul, html;
					if(active == 0) {
						disabled = false;
					}

					$('form.upload-files:first', $('#' + dialogId)).fileupload('option', 'disabled', disabled);
				}
			});
		},

/**
 * ダイアログクローズ処理
 * @param   object event
 * @return  void
 */
		closeDialog: function(e) {
			$('#' + this.dialogId).dialog('close');
			if(e) {
				$.Event(e).preventDefault();
			}
		},

/**
 * アップロード画面初期処理
 * @param   string id
 * @return  void
 */
		initUploadTab: function(id) {
			var form = $('#Form'+id);
			this.indexId = id;
			form.fileupload({
				dataType: 'html',
				success: function(res){
					var ul = $('#upload-preview-list'+id), afterLi;
					if(res.match(/error-message/)) {
						form.append(res);
					} else if(ul.get(0)) {
						afterLi = $('li:last', ul);
						$('li', $(res)).each(function () {
							afterLi.after(this);
							if($.Upload.multiple) {
								$(this).addClass('upload-selected');
							}
						});
					} else {
						form.after(res);
						ul = $('#upload-preview-list'+id);
						ul.children().each(function (key, value) {
							if(key ==0) {
								$('a:first', $(value)).click();
							} else if($.Upload.multiple) {
								$(value).addClass('upload-selected');
							}
						});
					}
					$.Upload.resizeThumbnail(id, 'preview');
				}
			});

			setTimeout(function(){$(':input:visible:first',form).focus();}, 100);
		},

/**
 * ライブラリから追加初期処理
 * @param   string id
 * @return  void
 */
		initLibraryTab: function(id) {
			var libraryUl = $('#upload-library-list' + id);
			var form = $('#Form'+id);
			this.resizeThumbnail(id);
			this.libraryId = id;

			$('#upload-library-file-type'+id+' a').on('click', function(e) {
				var fileType = $(this).data('file-type');

				if($(this).hasClass('upload-current')) {
					$.Event(e).preventDefault();
					return false;
				}
				$(this).parent().children().each(function(){
					if(fileType == $(this).data('file-type')) {
						$(this).addClass('upload-current')
					} else {
						$(this).removeClass('upload-current')
					}
				});
				$('[name=data\\[UploadSearch\\]\\[file_type\\]]:first', $(this).parent()).val(fileType);
				$('#Form' + id).submit();
				return false;
			});

			// テンプレート化
			$.template( "uploadItem", $('#' + id + '-item-template').html());
			$.template( "uploadMore", $('#' + id + '-more-template').html());

			$('#' + id + '-delete-all').on('ajax:beforeSend',function(e, url, data) {
				url += '/';
				$('#' + id + '-selection').children().each(function(){
					url += $(this).attr('data-upload-id')+ ',';
				});
				data['_Token'] = {'key':$('input[name="data[_Token][key]"]:first', $('#Form' + id)).val()};
				return {'url' : url,'data' : data};
			});
			$('#Form' + id).on('ajax:beforeSend',function(e, url, data) {
				return {'options' : {'dataType' : 'json'}};
			}).on('ajax:success',function(e, res) {
				var li, uploadId;
				$('input[name="data[_Token][key]"]:first', $(this)).val(res['token']);
				$('input[name="data[UploadSearch][page]"]:first', $(this)).val(1);	// 検索のため1ページ目に戻す
				if(res['search_results']) {
					if(res['page'] == 1) {
						// 再検索
						libraryUl.html('');
					}
					$.each(res['search_results'], function(){
						li = $.tmpl( "uploadItem", this );
						uploadId = li.children('a:first').attr('data-upload-id');
						if($.Upload.data[uploadId] && $.Upload.data[uploadId]['_selected']) {
							li.children('a:first').click();
						}
						libraryUl.append(li);
					});
					$.Upload.setDatas(res['search_results']);
				}
				if(res['has_more']) {
					var more = {'id': id, 'page': res['page']+1};
					libraryUl.append($.tmpl( "uploadMore", more ));
				}
				$.Event(e).preventDefault();
				return false;
			});

			// Scroll
			libraryUl.bind("scroll.uploadScrollLibrary", function(e) {
				$.Upload.scrollLibrary(e, id, this);
			});
			if(libraryUl.get(0).clientHeight == libraryUl.get(0).scrollHeight) {
				// Scrollバーがでていないのに、Moreが存在する。=> 再取得
				var hasMore = this.hasMore(id);
				if(hasMore && hasMore.get(0)) {
					hasMore.click().parent().remove();
				}
			}
			setTimeout(function(){$(':input:visible:first',form).focus();}, 100);
		},

/**
 * Urlから参照初期処理
 * @param   string id
 * @return  void
 */
		initRefUrlTab: function(id) {
			var $img = $.Upload.setting.img ? $($.Upload.setting.img) : null;
			var float, fileInfo, options, imgObject, rateWidth, rateHeight;
			var uploadId = 0;	// uploadId=0固定
			$.template( "uploadRefUrl", $('#' + id + '-ref-url-template').html());
			id = id + '-ref_url';
			var data = {
				'_top_id': id,
				'id': uploadId,
				'file_type': 'image',
				'unit': '%'
			};

			this.refUrlId = id;

			if (!$img) {
				// 新規
				$.Upload.injectFileInfo(id, uploadId, data);
				return;
			}

			// 編集
			float = $img.css("float");
			if(float != 'left' && float != 'right') {
				float = '';
				if($img.hasClass('block-center')) {
					float = 'center';
				}
			}

			$.extend(data, {
				'file_name': $img.attr('src'),
				'file_type': 'image',
				'url': $img.attr('src'),
				'alt': $img.attr('alt'),
				'width': $img.width(),
				'height': $img.height(),
				'float': float,
				'margin_top_bottom': ($img.css("marginTop") == "auto") ? "0" : parseInt($img.css("marginTop") || 0),
				'margin_left_right': ($img.css("marginLeft") == "auto") ? "0" : parseInt($img.css("marginLeft") || 0),
				'border_width': $img.css("borderTopWidth") ? parseInt($img.css("borderTopWidth") || 0) : 0,
				'border_style': $img.css("borderTopStyle") ? $img.css("borderTopStyle") : 'solid'
			});

			img = $('<img>').attr('src', $img.attr('src')).css('visibility','hidden');
			img.bind('load', function(){
				var width = $(this).width();
				var height = $(this).height();
				$(this).remove();

				if(width > 0) {
					data['resize_width'] = data['width'];
					data['resize_height'] = data['height'];
					data['width'] = width;
					data['height'] = height;
				}

				rateWidth = Math.round((data['resize_width']/data['width'])*100 || 0);//parseInt((data['resize_width']/data['width'])*100 || 0);
				rateHeight = Math.round((data['resize_height']/data['height'])*100 || 0);//parseInt((data['resize_height']/data['height'])*100 || 0);
				if(rateWidth != 0 || rateHeight != 0) {
					// 100%以外
					if(rateWidth > 100 || rateHeight > 100 || rateWidth != rateHeight) {
						data['unit'] = 'px';
					} else {
						data['percent_size'] = rateWidth;
					}
				}

				$.Upload.injectFileInfo(id, uploadId, data);
			});
			img.bind('error', function(){
				$.Upload.injectFileInfo(id, uploadId, data);
			});
			$('#'+id).append(img);
		},

/**
 * ファイル情報をフォームに埋め込む
 * @param   integer id
 * @param   integer uploadId
 * @param   object data
 * @return  void
 */
		injectFileInfo: function(id, uploadId, data) {
			$.Upload.data[uploadId] = $.extend($.Upload.data[uploadId], data);	// uploadId=0固定
			options = $.extend({}, $.Upload.fileinfoOptions);
			data = $.extend(options, $.Upload.data[uploadId]);

			fileInfo = $.tmpl('uploadRefUrl', data);

			$('.upload-ref-url:first', $('#' +id + '-outer')).html(fileInfo);
			$.Upload.initFileInfo(id);
			setTimeout(function(){$(':input:visible:first',$('#' +id + '-outer')).focus();}, 100);
		},

/**
 * ライブラリのスクロールバーが下の位置にきたら、画像ライブラリを追記読み込み。
 * @param   object event
 * @param   integer id
 * @param   element list
 * @return  void
 */
		scrollLibrary: function(e, id, list) {
			var hasMore = this.hasMore(id);
			if ( hasMore && list.scrollHeight < list.scrollTop + ( list.clientHeight * this.refreshThreshold ) ) {
				hasMore.click().parent().remove();
			}
		},

/**
 * ライブラリにまだデータがあるかどうかのチェック
 * @param   integer id
 * @return  void
 */
		hasMore: function(id) {
			var hasMore = $('#upload-library-has-more' + id), a;
			if(!hasMore.get(0)) {
				return false;
			}
			a = hasMore.children('a:first');
			return a;
		},

/**
 * 検索ユーザータイプセレクトボックスチェンジイベント
 * @param   element select
 * @param   string id
 * @param   integer all 検索ユーザータイプの「すべて」の定数
 * @return  void
 */
		changeSearchUserType: function(select, id, all) {
			$textOptions = $('#upload-text-options' + id);
			$pluginAll = $('#upload-plugin-all' + id);
			$createdAll = $('#upload-created-all' + id);

			if ($(select).val() == all) {
				// すべて
				$textOptions.show();
				$pluginAll.show();
				$pluginAll.prev().hide();
				$pluginAll.next().hide();
				$createdAll.show();
				$createdAll.prev().hide();
				$createdAll.next().hide();
			} else if ($(select).val() < all) {
				// 自分自身
				$textOptions.hide();
				$pluginAll.prev().show();
				$pluginAll.hide();
				$pluginAll.next().hide();
				$createdAll.prev().show();
				$createdAll.hide();
				$createdAll.next().hide();
			} else {
				// 退会ユーザー
				$textOptions.hide();
				$pluginAll.prev().hide();
				$pluginAll.hide();
				$pluginAll.next().show();
				$createdAll.prev().hide();
				$createdAll.hide();
				$createdAll.next().show();
			}
		},

/**
 * ライブラリから追加 - ウィンドウリサイズ処理時にダイアログリサイズ処理
 * @param   object event
 * @param   element dialog
 * @return  void
 */
		resizeLibrary: function(e, dialog) {
			//var position = dialog.dialog('option', 'position');
			dialog.dialog('option', 'width', $(window).width() - 30);
			dialog.dialog('option', 'height', $(window).height() - 60);
			dialog.dialog('option', 'position', ['center', 'center']);
		},

/**
 * Thumbnailリサイズ処理
 * @param   string id
 * @param   string type library or preview (default:library)
 * @return  void
 */
		resizeThumbnail: function(id, type) {
			type = (type == undefined) ? 'library' : type;
			var $css = $('#upload-' + type + '-list' + id + '-css');
			var $ul = $('#upload-' + type + '-list' + id);
			var offset = 150;

			if(type == 'library') {
				$('#' + this.dialogId).dialog('option', 'position', ['center', 'center']);
				setTimeout(function(){
					var positonUl = $ul.position();
					var height = $(window).height() - positonUl['top'] - offset;
					if(height < 150) {
						height = 120;
					}
					$ul.css('height', height + 'px');	// ulの高さ設定
				}, 100);
			}

			if ($css.length)
				$css.remove();

			template = function(options) {
				return '<style type="text/css" id="'+options.id+'-css">'+
					'#'+options.id+' {'+
						'padding: '+options.space+'px;'+
					'}'+
					'#'+options.id+' .upload-attachment {'+
						'width: '+options.box+'px;'+
						'margin: '+options.space+'px;'+
					'}'+
					'#'+options.id+' .upload-preview,'+
					'#'+options.id+' .upload-preview .upload-thumbnail {'+
						'width: '+options.box+'px;'+
						'height: '+options.box+'px;'+
					'}'+
					'#'+options.id+' .upload-portrait .upload-thumbnail img {'+
						'max-width: '+options.box+'px;'+
						'height: auto;'+
					'}'+
					'#'+options.id+' .upload-landscape .upload-thumbnail img {'+
						'width: auto;'+
						'max-height: '+options.box+'px;'+
					'}'+
					'</style>';
			}

			$("head").append( template({
				id:		'upload-' + type + '-list' + id ,
				box:	this._getBox(id, type),
				space:	$.Upload.space
			}) );
		},

/**
 * Thumbnail ボックスサイズ取得
 * @param   string id
 * @param   string type
 * @return  integer
 */
		_getBox: function(id, type) {
			var box = $.Upload.box,
				space, width, columns, scrollOffset = 20;

			space  = $.Upload.space * 2;
			width   = $('#upload-' + type + '-list' + id).width() - space - scrollOffset;
			columns = Math.ceil( width / ( box + space ) );
			box = Math.floor( ( width - ( columns * space ) ) / columns );
			return box;
		},

/**
 * サムネイルItemクリック処理
 * @param   object event
 * @param   object element a
 * @param   string type undefined|'preview' preview:アップロード画面
 * @return  boolean false
 */
		clickItem: function(e, a, type) {
			var uploadId = $(a).attr('data-upload-id');
			var $li = $(a).parent(), $ul, isSelected = false;
			if($li.get(0) && $li.get(0).tagName != 'LI') {
				// Selection
				var $a = $('#' + $.Upload.libraryId + '-item-' + $(a).attr('data-upload-id')).children('a:first');
				if($a.get(0)) {
					$a.click();
					$.Event(e).preventDefault();
					return false;
				} else {
					$li = $();
					var div = $(a).children('.upload-thumbnail:first');
					if(div.hasClass('upload-selected-current')) {
						isSelected = true;
					}
				}
			}

			if(type == 'preview') {
				$ul = $('#upload-preview-list' + $.Upload.indexId);
			} else {
				$ul = $('#upload-library-list' + $.Upload.libraryId);
			}

			if($li.hasClass('upload-selected-current') || isSelected) {
				this._deleteSelection(uploadId, $li, type);
			} else  {
				if(type == 'preview') {
					$('li.upload-attachment', $ul).each(function(){
						var $refLi = $(this);
						if($li.get(0) != $refLi.get(0)) {
							if($refLi.hasClass('upload-selected-current')) {
								if($.Upload.multiple) {
									$.Upload._addSelection($refLi.children('a:first').attr('data-upload-id'), $refLi, type);
								} else {
									$.Upload._deleteSelection($refLi.children('a:first').attr('data-upload-id'), $refLi, type);
								}
							}
						}
					});
				} else {
					$('#' + $.Upload.libraryId + '-selection').children().each(function(){
						var $refA = $(this);
						var refUploadId = $refA.attr('data-upload-id');
						if(uploadId != refUploadId) {
							$refLi = $('#' + $.Upload.libraryId + '-item-' + refUploadId);
							if($.Upload.data[refUploadId]['_selected'] == 'upload-selected-current') {
								if($.Upload.multiple) {
									$.Upload._addSelection(refUploadId, $refLi, type);
								} else {
									$.Upload._deleteSelection(refUploadId, $refLi, type);
								}
							}
						}
					});
				}
				this._addSelection(uploadId, $li, type, 'upload-selected-current');
			}

			$.Event(e).preventDefault();
			return false;
		},

/**
 * ライブラリから追加 ファイル選択
 * @param   string uploadId
 * @param   element $li
 * @param    string type undefined|'preview'
 * @param   string  className
 * @return  void
 */
		_addSelection: function(uploadId, $li, type, className) {
			var $newA, fileInfo, data, options, $selection;
			var $fileInfo = (type == 'preview') ? $('#' + $.Upload.indexId + '-library-fileinfo') : $('#' + $.Upload.libraryId + '-library-fileinfo');
			className = (className == undefined) ? 'upload-selected' : className;
			$li.addClass(className);
			if(className == 'upload-selected-current') {
				$li.removeClass('upload-selected');
				// ファイル詳細表示
				options = $.extend({}, $.Upload.fileinfoOptions);	// extendが参照渡しのため
				data = $.Upload.data[uploadId];
				data = $.extend(options, data);
				data['_top_id'] = (type == 'preview') ? $.Upload.indexId : $.Upload.libraryId;
				if(type == 'preview') {
					data['_prefix'] = 'preview-';
					data['is_edit'] = false;
				}

				fileInfo = $.tmpl( "uploadFileinfo", data);

				$fileInfo.html(fileInfo);

				$.Upload.initFileInfo(null, type);

			} else if(className == 'upload-selected') {
				$li.removeClass('upload-selected-current');
			}
			$.Upload.data[uploadId]['_selected'] = className;

			if((type != 'preview')) {
				$selection = $('#' + $.Upload.libraryId + '-selection');
				$newA = $('#' + $.Upload.libraryId + '-selection-' + uploadId);
				if(!$newA.get(0)) {
					$newA = $li.children(':first').clone().hide();
					$newA.attr('id', $.Upload.libraryId + '-selection-' + uploadId).appendTo($selection).slideDown('fast');
				}

				if(className == "upload-selected-current") {
					$newA.children(':first').addClass('upload-selected-current');
				} else {
					$newA.children(':first').removeClass('upload-selected-current');
				}
			}
		},

/**
 * ライブラリから追加 ファイル選択解除
 * @param   integer $uploadId
 * @param   element $li
 * @param   string type
 * @return  void
 */
		_deleteSelection: function(uploadId, $li, type) {
			var $fileInfo = (type == 'preview') ? $('#' + $.Upload.indexId + '-library-fileinfo') : $('#' + $.Upload.libraryId + '-library-fileinfo');
			$li.removeClass('upload-selected-current').removeClass('upload-selected');
			delete $.Upload.data[uploadId]['_selected'];
			if((type != 'preview')) {
				$('#' + $.Upload.libraryId + '-selection-' + uploadId).slideUp("fast", function(){$(this).remove()});
			}
			$fileInfo.html('');
		},

/**
 * ファイル詳細Init処理
 * @param   integer id
 * @return  void
 */
		initFileInfo: function(id, type) {
			var top, previewA ;
			if(id == undefined || id == null) {
				id = (type == 'preview') ? $.Upload.indexId : $.Upload.libraryId;
				top = $('#' + id + '-library-fileinfo');
			} else {
				top = $('#' + id + '-outer');
			}
			previewA = $('#' + id + '-fileinfo-img');
			$("[name=data\\[UploadDetail\\]\\[border_width\\]]:first", top).select2({
				minimumResultsForSearch:-1,
				formatResult: $.Upload._settingBorderWidthOption,
				formatSelection: $.Upload._settingBorderWidthOption
			});
			$("[name=data\\[UploadDetail\\]\\[border_style\\]]:first", top).select2({
				minimumResultsForSearch:-1,
				formatResult: $.Upload._settingBorderStyleOption,
				formatSelection: $.Upload._settingBorderStyleOption
			});
			if(previewA.attr('data-file-type') == 'image') {
				$.Upload._setColorBox(previewA);
			}

			$(".upload-delete:first", top).on('ajax:beforeSend',function(e, url, data) {
				data['_Token'] = {'key':$('input[name="data[_Token][key]"]:first', $('#Form' + id)).val()};
				return {'data' : data};
			});
		},


/**
 * URL Input変更時処理
 * @param   element input
 * @return  void
 */
		changeRefUrl: function(input) {
			var value = $(input).val();
			var a = $('#' + this.refUrlId + '-fileinfo-img');

			img = $('<img>').attr('src', value).css('visibility','hidden');
			img.bind('load', function(){
				var width = $(this).width();
				var height = $(this).height();
				$(this).remove();

				if(width > 0) {
					$.Upload.data[0]['real_url'] = value;
					$.Upload.data[0]['percent_size'] = 100;
					$.Upload.data[0]['width'] = $.Upload.data[0]['resize_width'] = width;
					$.Upload.data[0]['height'] = $.Upload.data[0]['resize_height'] = height;
					$('#' + this.refUrlId + '-fileinfo-percent-size').val(100);
					$('#' + this.refUrlId + '-fileinfo-px-size-width').val(width);
					$('#' + this.refUrlId + '-fileinfo-px-size-height').val(height);
					$.Upload._setColorBox(a);
				} else {
					value = '';
				}
				a.attr('href', value);
				a.children('img:first').attr('src', value);
			});
			$(input).after(img);
		},

/**
 * 単位セレクトボックス変更イベント
 * @param   element select
 * @return  void
 */
		changeUnit: function(select) {
			var li = $(select).parents('li:first');
			if($(select).val() == 'px') {
				li.removeClass('upload-fileinfo-percent-size');
				li.addClass('upload-fileinfo-px-size');
			} else {
				li.addClass('upload-fileinfo-percent-size');
				li.removeClass('upload-fileinfo-px-size');
			}
		},

/**
 * ライブラリから追加　クリアボタン処理
 * @param   void
 * @return  void
 */
		clearSelection: function() {
			var selection = $('#'+$.Upload.libraryId+'-selection'), uploadId;
			selection.children().each(function(){
				uploadId = $(this).attr('data-upload-id');

				$.Upload._deleteSelection(uploadId,$('#' + $.Upload.libraryId + '-item-' + $(this).attr('data-upload-id')));
			});
		},

/**
 * ファイル編集成功処理
 * @param   integer uploadId
 * @param   boolean isRefUrl
 * @param   string  formId
 * @param   string  parentId
 * @return  void
 */
		successEdit: function(uploadId, isRefUrl, formId, parentId) {
			var form = $(formId);
			var extension, file_name, alt, description;
			var parentFileName, parentAlt, parentDescription, li;
			var parentPreviewFileName, parentPreviewAlt, parentPreviewDescription;

			parentFileName = $('#upload-fineinfo-' + parentId + '-file-name');
			parentAlt = $('#upload-fineinfo-' + parentId + '-alt');
			parentDescription = $('#upload-fineinfo-' + parentId + '-description');
			parentPreviewFileName = $('#upload-fineinfo-preview-' + parentId + '-file-name');
			parentPreviewAlt = $('#upload-fineinfo-preview-' + parentId + '-alt');
			parentPreviewDescription = $('#upload-fineinfo-preview-' + parentId + '-description');

			file_name = $('[name=data\\[Upload\\]\\[basename\\]]:first', form).val();
			extension = $('[name=data\\[Upload\\]\\[extension\\]]:first', form).val();
			if(extension != '') {
				file_name += '.' + extension;
			}
			alt = $('[name=data\\[Upload\\]\\[alt\\]]:first', form);
			if(alt.get(0)) {
				alt = alt.val();
			}

			description = $('[name=data\\[Upload\\]\\[description\\]]:first', form).val();
			if(parentFileName.get(0)) {
				parentFileName.html($.Common.escapeHTML(file_name));
				if(parentAlt.get(0)) parentAlt.val(alt);
				parentDescription.val(description);
			}
			if(parentPreviewFileName.get(0)) {
				parentPreviewFileName.html($.Common.escapeHTML(file_name));
				if(parentPreviewAlt.get(0)) parentPreviewAlt.val(alt);
				parentPreviewDescription.val(description);
			}

			li = parentDescription.parents('li:first');
			if(description) {
				li.removeClass('display-none');
			} else {
				li.addClass('display-none');
			}
			this.setData(uploadId, 'file_name', file_name);
			this.setData(uploadId, 'alt', alt);
			this.setData(uploadId, 'description', description);
			if(isRefUrl) {
				this.setData(0, 'file_name', file_name);
				this.setData(0, 'alt', alt);
				this.setData(0, 'description', description);
			}

			$('[name=cancel]:first', form).click();

		},

/**
 * BorderWidthセレクトタブ生成処理
 * @param   object option
 * @return  string html
 */
		_settingBorderWidthOption: function(option) {
			if (option.id == '0') {return option.text;}
			return '<div title="'+option.text+'" style="border-bottom:'+option.text+' solid; height:12px;"></div>';
		},

/**
 * BorderStrleセレクトタブ生成処理
 * @param   object option
 * @return  string html
 */
		_settingBorderStyleOption: function(option) {
			//if (!option.id) {return option.text;}
			return '<div title="'+option.text+'" style="border-bottom:3px '+option.text+'; height:12px;"></div>';
		},

/**
 * 画像プレビュー表示処理(colorbox)
 * @param   integer uploadId
 * @param   element a
 * @return  void
 */
		_setColorBox: function(previewA) {
			var getSize = function(uploadId, isOuter) {
				var width = 0, height = 0, upload;
				if(uploadId != undefined && uploadId != '') {
					upload = $.Upload.data[uploadId];
					if (upload['unit'] != 'px') {
						width = parseInt(upload['width']*(upload['percent_size']/100) || upload['width']);
						height = parseInt(upload['height']*(upload['percent_size']/100) || upload['height']);
					} else {
						width = parseInt(upload['resize_width'] || upload['width']);
						height = parseInt(upload['resize_height'] || upload['height']);
					}
				}
				if(isOuter && upload['border_width'] >= 1) {
					width += parseInt(upload['border_width'])*4;
					height += parseInt(upload['border_width'])*4;
				}
				return [width, height];
			};

			var ret = getSize(previewA.attr('data-upload-id'), true);
			previewA.colorbox({
				photo: true,
				innerWidth: ret[0],
				innerHeight:ret[1],
				scalePhotos: false,
				onOpen: function() {
					if($(this).attr('href') == '') {
						$(this).colorbox.close()
					}
				},
				onComplete: function() {
					// 直接画像サイズをID指定で変更
					var img, uploadId = $(this).attr('data-upload-id');
					var ret = getSize(uploadId);
					var upload = $.Upload.data[uploadId];
					img = $('.cboxPhoto:first', $('#colorbox'));
					img.css({'width':ret[0], 'height':ret[1]});

					if(upload['border_width'] >= 1) {
						if(!upload['border_style']) {
							upload['border_style'] = 'solid';
						}
						img.css({'border-width':upload['border_width']+'px', 'border-style':upload['border_style']});
					}
				}
			});
		},

/**
 * 投稿に挿入（イメージ挿入ボタン押下処理）
 * @param   string action どのアクション(タブ)で挿入されたか。'index':アップロード 'library':ライブラリー,'ref_url':URL参照
 * @return  void
 */
		addUploadForImage: function(action) {
			var img = $(), uploadId = 0, upload, src, isInsert = false;
			if (action == 'index') {
				img = $('#upload-preview-list' + $.Upload.indexId).find('img');
			} else if(action == 'library') {
				img = $('#'+$.Upload.libraryId+'-selection').find('img');
			} else {
				src = $('#' + this.refUrlId + '-fileinfo-img').children('img:first').attr('src');
				if(src != '') {
					img = $('<img>');
				}
			}
			img.each(function () {
				if(action == 'index' || action == 'library') {
					var a = $(this).parents('a:first');
					uploadId = a.attr('data-upload-id');
					if(action == 'index' && (!a.parent().hasClass('upload-selected') && !a.parent().hasClass('upload-selected-current'))) {
						return;
					}
				}
				if($.Upload.setting.callback) {
					var resLibrary = $.Upload.getLibraryUrl(uploadId, $.Upload.data[uploadId]['real_url']);
					$.Upload.setting.callback( resLibrary[0], $.Upload.data[uploadId]['real_url'], resLibrary[1]);
				} else {
					upload = $.extend({}, $.Upload.data[uploadId]);
					$.Upload.insertImage($.extend(upload, {
						url : $.Upload.data[uploadId]['real_url']
					}));
				}
				isInsert = true;
			});
			if(isInsert == false) {
				$.Common.alert(__d('upload', 'Please select files to add.'));
				return false;
			}
			if($.Upload.setting.wysiwyg) {
				$.Upload.setting.wysiwyg.addUndo();
			}
			$.Upload.closeDialog();
			if (action == 'ref_url') {
				delete img;
			}
		},

/**
 * 投稿に挿入（ファイル挿入ボタン押下処理）
 * @param   string action どのアクション(タブ)で挿入されたか。'index':アップロード 'library':ライブラリー,'ref_url':URL参照
 * @return  void
 */
		addUploadForFile: function(action) {
			var id = $.Upload.setting.id, isInsert = false;
			var file = null;
			if (action == 'index') {
				file = $('#upload-preview-list' + $.Upload.indexId).find('a');
			} else if(action == 'library') {
				file = $('#'+$.Upload.libraryId+'-selection').children();
			}
			file.each(function (key, value) {
				var a = $(value);
				var uploadId = a.attr('data-upload-id');
				var html = '<a>'+$.Upload.data[uploadId]['file_name']+'</a>';
				if(action == 'index' && (!a.parent().hasClass('upload-selected') && !a.parent().hasClass('upload-selected-current'))) {
					return;
				}
				if($.Upload.setting.callback) {
					var resLibrary = $.Upload.getLibraryUrl(uploadId, $.Upload.data[uploadId]['real_url']);
					$.Upload.setting.callback( resLibrary[0], $.Upload.data[uploadId]['real_url'], resLibrary[1]);
				} else {
					if(key > 0) {
						// 改行コード挿入
						$.Upload.insertFile('<br />');
					}
					$.Upload.insertFile(html, {
						target:'_blank',
						href: $.Upload.data[uploadId]['real_url']
					});
				}
				isInsert = true;
			});
			if(isInsert == false) {
				$.Common.alert(__d('upload', 'Please select files to add.'));
				return false;
			}
			if($.Upload.setting.wysiwyg) {
				$.Upload.setting.wysiwyg.addUndo();
			}
			$.Upload.closeDialog();
		},

/**
 * ファイル挿入処理（画像）
 * @param   array  params
 * @return  void
 */
		insertImage: function(params) {
			params = params || {};
			params = $.extend({
				'unit': '%'
			}, params);
			if(!params['border_width'] || params['border_width'] == '') {
				delete params['border_style'];
			} else if(!params['border_style']) {
				params['border_style'] = 'solid';
			}
			var wysiwyg = $.Upload.setting.wysiwyg;
			wysiwyg.focus(true);
			if($.browser.msie) {
				wysiwyg.moveToBookmark(wysiwyg.bookmark);
			}

			var img = $.Upload.setting.img ? $.Upload.setting.img : null;
			if (img) {
				if ($.browser.isSafari) {
					this.rangeSelect(img);
				}
				img.src = params.url;
			} else {
				img = wysiwyg.applyInlineStyle('img', {src : params.url}, true);
			}
			img = $(img);

			var cssObj = {};
			for (var field in params) {
				var value = params[field], width, height;
				if(value == '' && (field != 'title' && field != 'alt')) {
					continue;
				}
				switch (field) {
					////case 'title':
					case 'alt':
						img.attr(field, value);
						break;
					case 'unit':
						if (value == '%') {
							//if (!params['percent_size'] || params['percent_size'] == '100') {
							//	break;
							//}
							width = parseInt(params['width']*(params['percent_size']/100) || params['width']) + 'px';
							height = parseInt(params['height']*(params['percent_size']/100) || params['height']) + 'px';

						} else {
							width = parseInt(params['resize_width'] || params['width']) + 'px';
							height = parseInt(params['resize_height'] || params['height']) + 'px';
						}
						cssObj['width'] = width;
						cssObj['height'] = height;
					case 'float':
						if (value == 'left' || value == 'right') {
							cssObj['float'] = value;
							img.removeClass('block-center');
						} else if (value == 'center') {
							img.addClass('block-center');
							cssObj['float'] = '';
						} else {
							cssObj['float'] = '';
							img.removeClass('block-center');
						}
						break;
					case 'border_width':
						cssObj['border-width'] = value;
						break;
					case 'border_style':
						cssObj['border-style'] = value;
						break;
				}
			}
			var margin = (params.margin_top_bottom ? params.margin_top_bottom : '0') + 'px';
			margin += ' ' + (params.margin_left_right ? params.margin_left_right : '0') + 'px';
			cssObj['margin'] = margin;
			cssObj['border-style'] = (cssObj['border-width'] && cssObj['border-style'] == 'none') ? 'solid' : cssObj['border-style'];

			$(img).css(cssObj);
		},

/**
 * ファイル挿入処理
 * @param   string  html
 * @param   array  params
 * @return  void
 */
		insertFile: function(html, params) {
			params = params || {};
			var wysiwyg = $.Upload.setting.wysiwyg;
			wysiwyg.focus(true);
			if($.browser.msie)
				wysiwyg.moveToBookmark(wysiwyg.bookmark);
			wysiwyg.applyInlineStyle(html, params, true);
		},

/**
 * ファイル削除処理
 * @param   string  html
 * @param   string  uploadId undefinedならばすべて削除
 * @param   string  topId
 * @return  void
 */
		deleteSuccess: function(html, uploadId) {
			var form = $('#Form' + this.libraryId);
			if($.trim(html).match(/^\{.*\}$/)) {
				ret = $.parseJSON(html);
				$('input[name="data[_Token][key]"]:first', form).val(ret['token']);
			} else {
				// 確認ダイアログ
				var params = {
					width: 'auto',
					minWidth: 600,
					modal: true
				};
				$('<div id="' + this.libraryId + '-confirm-dialog"></div>').html(html).dialog(params);
				return;
			}
			if(typeof uploadId == 'undefined' || uploadId == '') {
				$.Upload.clearSelection();
			} else {

				$('#' + this.libraryId + '-selection').children().each(function(){
					if(uploadId == $(this).attr('data-upload-id')) {
						$.Upload._deleteSelection(uploadId,$('#' + $.Upload.libraryId + '-item-' + uploadId));
					}
				});
			}
			// 再検索
			form.submit();
		},


/**
 * ファイル名、ライブラリーURL取得
 * @param   integer uploadId
 * @param   string  realUrl
 * @return  array(string filename ,string  libraryUrl)
 */
		getLibraryUrl: function(id, realUrl) {
			var urlSplit, libraryUrl = realUrl;
			if(!id) {
				return realUrl;
			}
			var reUrl = new RegExp("(.+/)"+id+"\.(.+)$", 'i');
			if(realUrl.match(reUrl)) {
				libraryUrl = RegExp.$1 + id + '_library.' + RegExp.$2;
			}
			return [id + '.' + RegExp.$2 , libraryUrl];
		}
	}
})(jQuery);