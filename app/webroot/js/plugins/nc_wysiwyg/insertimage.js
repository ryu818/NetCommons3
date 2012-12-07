/*
 * NC InsertImage 0.0.0.1
 * @param options hash
 *                  img             : src-borderStyleListまでの指定の代替(imgのhtml or img element(clone)を指定する)  default null
 * 					src             : string 画像のsrcの値を設定。default "",
 * 					alt             : string 画像のalt(title)の値を設定。default "",
 * 					size            : array 画像のサイズの値を配列で設定。default ["auto", "auto"],
 *                  size_select     : hash 画像の大きさ用selectbox 画像アップロード時のみ使用
 *                  unit            : string px or % sizeの単位を設定  default %
 *         			float           : string 画像の回り込みの値を設定。default "",
 * 	            	borderWidth     : string 画像の線の太さの値を設定(1px等)。default "",
 * 	            	borderStyle     : string 画像のスタイルの値を設定。default "solid",
 * 	            	borderColor     : string 画像の色の値を設定。default "#666666",
 * 	            	margin          : array 画像のマージンの値を配列で設定。default [0, 0],		// [縦, 横]
 * 	            	borderWidthList : array 線の太さの値を配列で設定。default ['1px', '2px', '3px', '4px', '5px'],
 * 	            	borderStyleList : array 線のスタイルリストボックスの値を配列で設定。default ['solid','double','dashed','dotted','inset','outset'],
 * 	            	callback        : function 決定時のcallback関数(default null)
 * 	            	cancel_callback : function キャンセル時のcallback関数(default null)
 *                  url             : string   アップロードするaction url
 *                  blank_url       : blank画像へのパスを指定
 */
;(function($) {
	$.fn.nc_insertimage = function(options) {

		var options = $.extend({
				img             : null,
				src             : "",
				alt             : "",
				size            : ["auto", "auto"],
				size_select     : {
									normal    : [800, 600],
									real_size : [0, 0],
									large     : [1024, 1280],
									medium    : [350, 250],
									small     : [200, 150],
									icon_size : [50, 50]
								  },
				unit            : "%",
        		float           : "",
	            borderWidth     : "",
	            borderStyle     : "",
	            borderColor     : "#666666",
	            margin          : [0, 0],		// [縦, 横]
	            borderWidthList : ['1px', '2px', '3px', '4px', '5px'],
	            borderStyleList : ['solid','double','dashed','dotted','inset','outset'],
	            callback        : null,
	            cancel_callback : null,
	            url             : "",
	            blank_url       : $._nc_base_url + "img/common/blank.gif",wysiwyg : null
	        }, options);

		var self = this, size = [0, 0], pre_size = [0, 0], img = null, img_size = [0, 0];
		var insert_flag = true;

		// 初期値セット - Form作成
		init(options);

		return;

		function init(options) {
			var frm,pre,outer, front, list, border, float, listWidth, listStyle, img_options;
			frm = $('<form action="' + options.url + '"class="nc-wysiwyg-insertimage" enctype="multipart/form-data" method="POST"></form>').appendTo( self );
			if((options.img)) {
				img = $(options.img);
				img_options = getImgCss(img, options);
				options = $.extend(options, img_options);
				img.css({width: "auto", height: "auto"});
				insert_flag = false;
			} else {
				img = createImg(options);
			}

			pre = $('<div class="nc-wysiwyg-insertimage-preview"></div>').appendTo(frm).css({opacity : '0.6'}).css({visibility : 'hidden'});
			if(($.browser.msie && parseInt($.browser.version) < 7)) {
				pre[0].innerHTML = img[0].outerHTML;
				img = $("img", pre);
			} else
				img.appendTo(pre);
			if( insert_flag ) {
				pre.addClass("nc-wysiwyg-insertimage-preview-ins");
				pre_size = [0, 0];	// previewサイズ
				img_size = [0, 0];
			} else {
				pre_size = [pre.width(), pre.height()];	// previewサイズ
				img_size = [img.width(), img.height()];
			}


			outer = $('<div class="nc-wysiwyg-insertimage-outer">'+
						'</div>').css({opacity : '0.4'}).appendTo(frm);
			front = $(_createFrontHtml(options))
						.css({visibility : 'hidden'})
						.appendTo(frm);
			float = $(".nc-wysiwyg-insertimage-float", front);
			float.append(_createFloat("left", $._base_url + "img/plugins/nc_wysiwyg/dialog/float-left.gif", __d('nc_wysiwyg_insertimage', 'left'), (options.float == 'left') ? true : false, img));
			float.append(_createFloat("normal", $._base_url + "img/plugins/nc_wysiwyg/dialog/float-normal.gif", __d('nc_wysiwyg_insertimage', 'normal'), (options.float == '' || options.float == 'none') ? true : false, img));
			float.append(_createFloat("right",$._base_url + "img/plugins/nc_wysiwyg/dialog/float-right.gif", __d('nc_wysiwyg_insertimage', 'right'), (options.float == 'right') ? true : false, img));

			border = $(".nc-wysiwyg-insertimage-border", front);
			list = _initListMenu('border_width', options.borderWidthList, 'border-bottom:', ' solid;');
			listWidth = options.wysiwyg.appendListMain(border, list, "nc-wysiwyg-insertimage-border", _borderWidthEvent, [frm, img, options]);
			options.wysiwyg.chgList(listWidth, options.borderWidth);

			list = _initListMenu('border_style', options.borderStyleList, 'border-bottom:3px ', ';');
			listStyle = options.wysiwyg.appendListMain(border, list, "nc-wysiwyg-insertimage-border", _borderStyleEvent, [frm, img, options]);
			options.wysiwyg.chgList(listStyle, options.borderStyle);

			_addEvent(frm, img, pre, front, outer);

			setTimeout(function() {
				if(img.attr("src") != options.blank_url){
					size = [img.width(), img.height()];		// 画像サイズ
				}else
					size = [0, 0];
				_loadAfter(frm, pre, front, outer, img);
			}, 400);

			return;

			function _createFrontHtml(options) {
				var opt_str = '', value;
				for(var key in options.size_select) {
					value = __d(['nc_wysiwyg_insertimage', 'size_select'], key);
					opt_str += '<option title="' + options.size_select[key][0] + __d('nc_wysiwyg_insertimage', 'product') + options.size_select[key][1] + '" value="' + options.size_select[key][0] + "," + options.size_select[key][1] + '">' + value + '</option>'
				}
				return '<div class="nc-wysiwyg-insertimage-front"><ul class="nc-wysiwyg-insertimage-row">' +
								'<li>' +
									'<div id="nc-wysiwyg-insertimage-select-file">'+
										'<p>' + __d('nc_wysiwyg_insertimage', 'desc_upload') + '</p>' +
										'<input name="files" type="file" />'+
										'&nbsp;<a id="nc-wysiwyg-insertimage-select-file-l" href="#">' + __d('nc_wysiwyg_insertimage', 'direct_link') + '</a>'+
									'</div>' +
									'<div id="nc-wysiwyg-insertimage-direct-link">'+
										'<p>' + __d('nc_wysiwyg_insertimage', 'desc_url') + '</p>' +
										'<dl>' +
											'<dt>' + __d('nc_wysiwyg_insertimage', 'img_url') + '</dt>' +
											'<dd>' +
												'<input class="nc-wysiwyg-insertimage-text" name="url" type="text" value="'+ options.src +'" />'+
												'<br /><a id="nc-wysiwyg-insertimage-direct-link-l" href="#">' + __d('nc_wysiwyg_insertimage', 'select_file') + '</a>'+
											'</dd>' +
										'</dl>' +
									'</div>' +
								'</li>' +
								'<li>' +
									'<dl>' +
										'<dt>' + __d('nc_wysiwyg_insertimage', 'alt') + '</dt>' +
										'<dd>' +
											'<input class="nc-wysiwyg-insertimage-text" name="alt_title" type="text" value="'+ options.alt +'" />' +
										'</dd>' +
									'</dl>' +
								'</li>' +
								'<li>' +
									'<dl>' +
										'<dt>' + __d('nc_wysiwyg_insertimage', 'size_px') + '</dt>' +
										'<dd>' +
											'<div id="nc-wysiwyg-insertimage-upload">' +
												'<select class="nc-wysiwyg-insertimage-size" name="size">'+ opt_str +'</select>' +
											'</div>' +
											'<div id="nc-wysiwyg-insertimage-direct">' +
												'<span id="nc-wysiwyg-insertimage-size-px"><input name="size_w" class="nc-wysiwyg-insertimage-size" type="text" maxlength="4" size="5" value="" />' +
												__d('nc_wysiwyg_insertimage', 'product') +
												'<input name="size_h" class="nc-wysiwyg-insertimage-size" type="text" maxlength="4" size="5" value="" /></span>' +
												'<span id="nc-wysiwyg-insertimage-size-percent"><input name="size_percent" class="nc-wysiwyg-insertimage-size" type="text" maxlength="3" size="5" value="" /></span>' +
												'&nbsp;<select class="nc_wysiwyg_insertimage_unit" name="unit"><option value="px">'+ __d('nc_wysiwyg_insertimage', 'px') +'</option><option value="%">'+ __d('nc_wysiwyg_insertimage', 'percent') +'</option></select>' +
												'<div class="nc-wysiwyg-insertimage-presize"></div>'+
											'</div>' +
										'</dd>' +
									'</dl>' +
								'</li>' +
								((insert_flag) ? '<li class="align-right"><a href="#" id="nc-wysiwyg-insertimage-detail">' + __d('nc_wysiwyg_insertimage', 'detail') + '</a></li>' : '') +
							'</ul><ul' + ((insert_flag) ? ' style="display:none;"' : '') + ' id="nc-wysiwyg-insertimage-detail-ul" class="nc-wysiwyg-insertimage-row">' +
								'<li>' +
									'<dl>' +
										'<dt>' + __d('nc_wysiwyg_insertimage', 'float') + '</dt>' +
										'<dd class="nc-wysiwyg-insertimage-float">' +
										'</dd>' +
									'</dl>' +
								'</li>' +
								'<li>' +
									'<dl>' +
										'<dt>' + __d('nc_wysiwyg_insertimage', 'margin_px') + '</dt>' +
										'<dd>' +
											'<input name="margin_w" class="nc-wysiwyg-insertimage-size" type="text" maxlength="4" size="5" value="' + options.margin[0] + '" />' +
											__d('nc_wysiwyg_insertimage', 'product') +
											'<input name="margin_h" class="nc-wysiwyg-insertimage-size" type="text" maxlength="4" size="5" value="' + options.margin[1] + '" />' +
										'</dd>' +
									'</dl>' +
								'</li>' +
								'<li>' +
									'<dl>' +
										'<dt>' + __d('nc_wysiwyg_insertimage', 'border') + '</dt>' +
										'<dd class="nc-wysiwyg-insertimage-border">' +
										'</dd>' +
									'</dl>' +
								'</li>' +
							'</ul>' +
							'<div class="nc-wysiwyg-insertimage-btn">' +
								'<input name="ok" type="button" class="common-btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'ok')+'" />' +
								'<input name="cancel" type="button" class="common-btn" value="'+__d(['nc_wysiwyg', 'dialog'], 'cancel')+'" />' +
							'</div>' +
							'</div>';
			}

			function _addEvent(frm, img, pre, front, outer) {
				if(insert_flag) {
					// 詳細
					$("#nc-wysiwyg-insertimage-detail").click(function(e){
						if(pre.hasClass("nc_wysiwyg-insertimage-preview-ins"))
							pre.removeClass("nc_wysiwyg-insertimage_preview-ins");
						else
							pre.addClass("nc-wysiwyg-insertimage-preview-ins");
						$('#nc-wysiwyg-insertimage-detail-ul').toggle();
						outer.css({width: front.outerWidth() +'px',  height: front.outerHeight() + 'px'});
						_moveFront(pre, front, outer)
						e.preventDefault();
						return false;
					});
					// file選択
					$("#nc-wysiwyg-insertimage-direct-link").css({display : "none"});
					$("#nc-wysiwyg-insertimage-direct").css({display : "none"});
				} else {
					// URL入力
					$("#nc-wysiwyg-insertimage-select-file").css({display : "none"});
					$("#nc-wysiwyg-insertimage-upload").css({display : "none"});
				}
				$("#nc-wysiwyg-insertimage-direct-link-l").click(function(e){
					return _toggleLink(e);
				});
				$("#nc-wysiwyg-insertimage-select-file-l").click(function(e){
					return _toggleLink(e);
				});

				// URL
				$("[name=url]:first", frm).keyup(function(e){
					img.attr("src", $(this).val());
					img.css("display", "none");
					setTimeout(function() {
						img.css("display", "");
						pre_size = [pre.width(), pre.height()];	// previewサイズ
						img_size = [img.width(), img.height()];
						if(img_size[0] > 0 && img_size[1] > 0) {
							$("[name=size_w]:first", frm).val(img_size[0]);
							$("[name=size_h]:first", frm).val(img_size[1]);
						}
					}, 100);
				});

				// alt
				$("[name=alt_title]:first", frm).keyup(function(e){
					img.attr("alt", $(this).val());
					img.attr("title", $(this).val());
				});

				// 広さ
				$("[name=size_w]:first", frm).keyup(function(e){
					var w = parseInt($(this).val() || 0);
					var h = parseInt($("[name=size_h]:first", frm).val() || 0);
					if(w > 0) {
						img.css({width : w + "px", height : h + "px"});
						$("[name=unit]:first", frm).val(parseInt((w/size[0])*100));
						size = [img.width(), img.height()];		// 画像サイズ
					}
				});

				// 高さ
				$("[name=size_h]:first", frm).keyup(function(e){
					var w = parseInt($("[name=size_w]:first", frm).val() || 0);
					var h = parseInt($(this).val() || 0);
					if(h > 0) {
						img.css({width : w + "px", height : h + "px"});
						size = [img.width(), img.height()];		// 画像サイズ
					}
				});

				// %
				$("[name=size_percent]:first", frm).keyup(function(e){
					var percent = parseInt($(this).val() || 0);
					var size_w, size_h;
					if(percent > 0 ) {
						size_w = parseInt(img_size[0]*(percent/100));
						size_h = parseInt(img_size[1]*(percent/100));
						img.css({
							width  : size_w + "px",
							height : size_h + "px"
						});
						$("[name=size_w]:first", frm).val(size_w);
						$("[name=size_h]:first", frm).val(size_h);
						size = [img.width(), img.height()];		// 画像サイズ
					}
				});

				// 単位
				$("[name=unit]:first", frm).change(function(e){
					var percent;
					var unit = $(this).val();
					if(unit == "%") {
						percent = parseInt(($("[name=size_w]:first", frm).val()/img_size[0])*100);
						if(isNaN(percent)) {
							percent = 100;
						}
						img.css({
							width  : parseInt(img_size[0]*(percent/100)) + "px",
							height : parseInt(img_size[1]*(percent/100)) + "px"
						});
						$("[name=size_percent]:first", frm).val(percent);

						$("#nc-wysiwyg-insertimage-size-percent").css("display", "");
						$("#nc-wysiwyg-insertimage-size-px").css("display", "none");
					} else {
						$("#nc-wysiwyg-insertimage-size-percent").css("display", "none");
						$("#nc-wysiwyg-insertimage-size-px").css("display", "");
					}
				});

				// 余白
				$("[name=margin_w]:first", frm).keyup(function(e){
					var w = parseInt($(this).val() || 0);
					if(w > 0) img.css({marginLeft : w + "px", marginRight : w + "px"});
				});

				$("[name=margin_h]:first", frm).keyup(function(e){
					var h = parseInt($(this).val() || 0);
					if(h > 0) img.css({marginTop : h + "px", marginBottom : h + "px"});
				});

				$("[name=ok]:first", frm).click(function(e){
					if($("#nc-wysiwyg-insertimage-direct-link").css("display") == "none") {
						// アップロード
						// IE６の場合、iframeをappendした段階で画面が崩れるので修正
						if(($.browser.msie && parseInt($.browser.version) < 7))
							front.css({visibility : "hidden"});
						$.Common.sendAttachment(frm,
							{
								id      : self.attr('id'),
								url     : options.url,
								data    : "type=image",
								success : function(ret, obj) {
									img.attr("src", $._base_url + obj[0].path);
									setTimeout(function() {
										img_options = getImgCss(img, options);
										var html = createImg(img_options, true);
										if(($.browser.msie && parseInt($.browser.version) < 7))
											front.css({visibility : "visible"});
										if(options.callback)
											if(!options.callback.call(self, html))
												return false;
									}, 500);
								},
								error : function(ret, obj){
									if(($.browser.msie && parseInt($.browser.version) < 7))
										front.css({visibility : "visible"});
									alert(ret);
								}
							}
						);
					} else {
						img_options = getImgCss(img, options);
						if(img_options.src == "" || img_options.src == undefined ||
							img_options.src == options.blank_url) {
							// URLを指定していない
							alert(__d('nc_wysiwyg_insertimage', 'err_url'));
							$("[name=url]:first", frm).focus();
							return false;
						}
						var html = createImg(img_options, true);
						if(options.callback)
							if(!options.callback.call(self, html))
								return false;
					}
					e.preventDefault();
			        return false;
				});

				$("[name=cancel]:first", frm).click(function(e){
					if(options.cancel_callback)
						if(!options.cancel_callback.call(self))
							return false;
					e.preventDefault();
			        return false;
				});

				function _toggleLink(e) {
					if($("#nc-wysiwyg-insertimage-direct-link").css("display") == "none") {
						$("#nc_wysiwyg-insertimage-direct-link").show();
						$("#nc_wysiwyg-insertimage-direct").show();
						$("#nc_wysiwyg-insertimage-select-file").hide();
						$("#nc_wysiwyg-insertimage-upload").hide();
						$("[name=url]:first", frm).focus();
					} else {
						$("#nc_wysiwyg-insertimage-direct-link").hide();
						$("#nc_wysiwyg-insertimage-direct").hide();
						$("#nc_wysiwyg-insertimage-select-file").show();
						$("#nc_wysiwyg-insertimage-upload").show();
						$("[name=files]:first", frm).focus();
					}
					outer.css({width: front.outerWidth() +'px',  height: front.outerHeight() + 'px'});
					_moveFront(pre, front, outer)
					e.preventDefault();
						return false;
				}
			}

			function _borderWidthEvent(e, k, v, frm, img, options) {
				var s = img.css("borderTopStyle"), c = img.css("borderTopColor");
				if(k == "") {
					img.css({border : '0px none'});
					options.wysiwyg.chgList(listStyle, "");
					return true;
				}
				if(s == "none" || s == "") s = "solid";
				if(c == "transparent" || c == "") c = options.borderColor;
				img.css({border : k + ' ' + s + ' ' + c});
				options.wysiwyg.chgList(listStyle, s);
				return true;
			}

			function _borderStyleEvent(e, k, v, frm, img, options) {
				var w = img.css("borderTopWidth"), c = img.css("borderTopColor");
				if(k == "") {
					img.css({border : '0px none'});
					options.wysiwyg.chgList(listWidth, "");
					return true;
				}
				if(w == "0px" || w == "") w = "3px";
				if(c == "transparent" || c == "") c = options.borderColor;
				img.css({border : w + ' ' + k + ' ' + c});
				options.wysiwyg.chgList(listWidth, w);
				return true;
			}

			function _loadAfter(frm, pre, front, outer) {
				var size_w,size_h;
				if(!insert_flag)
					$(".nc-wysiwyg-insertimage-presize", frm).html('(' + img_size[0] + __d('nc_wysiwyg_insertimage', 'product') + img_size[1] + ')');

				if(options.size[0] != "auto" && options.size[0] != "") {
					img.css({width : options.size[0]});
				}
				if(options.size[1] != "auto" && options.size[1] != "") {
					img.css({height : options.size[1]});
				}
				if(!insert_flag)
					pre.css({visibility : 'visible'});

				size_w = (options.size[0] == "" || options.size[0] == "auto") ? size[0] : options.size[0];
				size_h = (options.size[1] == "" || options.size[1] == "auto") ? size[1] : options.size[1];

				$("[name=size_w]:first", frm).val(size_w);
				$("[name=size_h]:first", frm).val(size_h);
				if(parseInt(size_w/size[0]*100) > 0)
					$("[name=size_percent]:first", frm).val(parseInt(size_w/size[0]*100));
				else
					$("[name=size_percent]:first", frm).val("100");
				if(options.unit == "%" && parseInt((size[1] * size_w/size[0]*100)/100) == size_h) {
					$("#nc-wysiwyg-insertimage-size-percent").css("display", "");
					$("#nc-wysiwyg-insertimage-size-px").css("display", "none");
					$("[name=unit]:first", frm).val("%");
				} else {
					$("#nc-wysiwyg-insertimage-size-percent").css("display", "none");
					$("#nc-wysiwyg-insertimage-size-px").css("display", "");
					$("[name=unit]:first", frm).val("px");
				}

				front.css({visibility : 'visible'});

				if( !insert_flag )
					$("[name=url]:first", frm).focus();
				else
					$("[name=files]:first", frm).focus();

				// リサイズ
				if(!insert_flag) {
					var resize = $('<a class="nc-wysiwyg-insertimage-resize"></a>').appendTo(frm);
					// リサイズmousedownイベント
		            resize.mousedown(function(e) {
		            	var sx = null, sy = null;
		            	var w = pre.width();
		            	var h = pre.height();
		            	var r_w = resize.width();
		            	var max =[Math.max(size[0], w), Math.max(size[1], h)];
		            	front.hide();
		            	outer.hide();
		            	// リサイズmousemoveイベント
		            	var resizeMouseMove = function(e) {
		            		var x_offset = 0, y_offset = 0;

		            		if(sx == null) {
		            			sx = e.pageX, sy = e.pageY;
		            		} else {
		            			x_offset = e.pageX - sx, y_offset = e.pageY - sy;
		            		}
		            		if(parseInt(w || 0) + x_offset < pre_size[0]) x_offset = pre_size[0] - parseInt(w || 0);
		            		if(parseInt(w || 0) + x_offset > max[0]) x_offset = max[0] - parseInt(w || 0);
		            		if(parseInt(h || 0) + y_offset < pre_size[1]) y_offset = pre_size[1] - parseInt(h || 0);
		            		if(parseInt(h || 0) + y_offset > max[1]) y_offset = max[1] - parseInt(h || 0);

							// リサイズ
		            		pre.css({
		       					width      : parseInt(w || 0) + x_offset + 'px',
			                    height     : parseInt(h || 0) + y_offset + 'px'
			                });
		            		e.preventDefault();
			                return false;
		            	};

		            	// リサイズmouseupイベント
		            	var resizeMouseUp = function(e) {
		            		front.show();
		            		outer.show();
		            		_moveFront(pre, front, outer);
		            		$(document).unbind('mousemove', resizeMouseMove);
		            		$(document).unbind('mouseup', resizeMouseUp);
							e.preventDefault();
							return false;
		            	};

		            	$(document).mousemove(resizeMouseMove);
						$(document).mouseup(resizeMouseUp);
						e.preventDefault();
						return false;
		            });
		        }
	            outer.css({width: front.outerWidth() +'px',  height: front.outerHeight() + 'px'});
				_moveFront(pre, front, outer);
	            return;
			}

			function _moveFront(pre, front, outer) {
				var move_size = {left : pre.outerWidth()/2 - front.outerWidth()/2, top : pre.outerHeight()/2 - front.outerHeight()/2};
				front.css(move_size);
				outer.css(move_size);
			}

			function _createFloat(name, src, lang, active, img) {
				var c = "nc-wysiwyg-insertimage-icon";
				return $('<a href="#"><img name="' + name + '"  alt="' + lang + '"  title="' + lang + '" src="' + src + '" class="'+ c + ((active) ? ' active' : '') + '"" /></a>')
					.click(function(e){
						var float = $("img", this);
						$("img" , this.parentNode).removeClass("active");
						float.addClass("active");
						e.preventDefault();
						if(float.attr("name") == "normal")
							img.css({float: ""});
						else
							img.css({float: float.attr("name")});
		            	return false;
					});
			}

			function _initListMenu(title, list, style_pre, style_post) {
				var html = {'' : __d('nc_wysiwyg_insertimage', title)};
				for(var i in list) {
					html[list[i]] = '<div style="' + style_pre + list[i] + style_post +'"></div>';
				}
				return html;
			}
		}

		function createImg(options, html_flag) {
			var html, style_str = '', margin = '';
			style_str += (options.float != "" && options.float != "none") ? ' float:'+options.float + ';' : '';
			if (options.borderWidth == "0" || options.borderStyle == "none") {
				options.borderStyle = "none";
				options.borderWidth = "0px";
			}
			if(options.borderWidth != "" && options.borderWidth != "0")
				style_str += ' border:' + options.borderWidth + ' ' + options.borderStyle + ' ' + options.borderColor + ';';

			if (options.size[0] != "" && options.size[0] != "auto")
				style_str += ' width:' + options.size[0] + "px; ";

			if (options.size[1] != "" && options.size[1] != "auto")
				style_str += ' height:' + options.size[1] + "px; ";

			if (options.margin[0] != "" && options.margin[0] != "auto")
				margin += options.margin[0] + "px ";
			else
				margin += "0";

			if (options.margin[1] != "" && options.margin[1] != "auto")
				margin += " " + options.margin[1] + "px; ";
			else
				margin += " 0;";

			if(margin != "0 0;")
				style_str += ' margin:' + margin;

			if(style_str != '')
				style_str = ' style="'+ $.trim(style_str) +'"'

			html = '<img alt="' + options.alt + '"  title="' + options.alt + '" src="' + ((options.src != "") ? options.src : options.blank_url) + '"'+ style_str + ' />';
			if(!html_flag) return $(html);
			return html;
		}

		function getImgCss(img, options) {
			return {
					src             : img.attr("src"),
					alt             : img.attr("alt"),
					size            : [(img.css("width") == "auto" || img.css("width") == "") ? "auto" : parseInt(img.css("width")),
										(img.css("height") == "auto" || img.css("height") == "") ? "auto" : parseInt(img.css("height"))],
	        		float           : img.css("float"),
		            borderWidth     : img.css("borderTopWidth") ? img.css("borderTopWidth") : options.borderWidth,
		            borderStyle     : img.css("borderTopStyle") ? img.css("borderTopStyle") : options.borderStyle,
		            borderColor     : img.css("borderTopColor") ? img.css("borderTopColor") : options.borderColor,
		            margin          : [(img.css("marginTop") == "auto") ? "0" : parseInt(img.css("marginTop") || 0),
		            					(img.css("marginLeft") == "auto") ? "0" : parseInt(img.css("marginLeft") || 0)]
				};
		}
	}
})(jQuery);