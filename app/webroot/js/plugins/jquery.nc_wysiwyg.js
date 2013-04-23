/*
 * NC WYSIWYG 0.0.0.1
 * @author      Ryuji Masukawa
 *
 * NC WYSIWYG was made based on the jwysiwyg0.5.
 * http://code.google.com/p/jwysiwyg/
 *
 * tinymce3(minorVersion 2.7) is used as reference.
 * http://tinymce.moxiecode.com/
 *
 */
;(function($) {
	$.fn.nc_wysiwyg = function(options) {

        /**
         * もし、カスタムコントローラをセットする場合、
         * 以下のように動作する。
         *
         * ・同じライン、同じグループ（キー）に同じボタンがあった場合は、マージする。
         * ・そうでない場合は、追加
         * ・同じライン上にあるグループは、グループのキーでソートした順番で表示する。
         *
         */
        var controls = [], css = [], js = [];
        if ( options ) {
        	if (options.controls) {
        		controls = options.controls;
            	delete options.controls;
        	}
        	if ( options.css ) {
	            css = options.css;
	            delete options.css;
	        }
	        if ( options.js ) {
	            js = options.js;
	            delete options.js;
	        }
        }

        var options = $.extend({
            html : '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>INITIAL_TITLE</title>INITIAL_HEADER</head><body>INITIAL_CONTENT</body></html>',
            title: 'wysiwyg editor',
			css  : [],                // css src
			js   : [],				  // javascript src
			style: null,

            debug        : false,

            autoSave     : true,
            rmUnwantedBr : false,

            cssInc       : true,	  		// 親のCSSをincludeするかどうか
            parseHtml    : true,      		// 登録時、htmlモードに移る時にタグを整形（タブ、改行挿入等）するかどうか
            tabStr       : '    ',    		// parseHtmlがtrueの場合のタブのスペースの数
            lineBreak    : '\n',      		// parseHtmlがtrueの場合の改行コード

            forecolor    : '#ff0000', 		// fontカラーのデフォルト色
            hilitecolor  : '#ff0000',		// backgroundカラーのデフォルト色

            undo_level   : 100,      		// undo redoできる履歴を保持する数

            controls     : [],

			autoRegistForm    : false,		// 自動登録を行う場合、登録のForm Elementをセット
			autoRegistTime    : 30000,		// 30秒

            formatMes    : true,             // 空のelement等のメッセージを画面上に表示するかどうか
            format_time  : 3000,				// 3秒
            focus        : false,
            image        : null,
			file         : null
        }, options);
        options.css = options.css.concat(css);
        options.js = options.js.concat(js);

		setOptControls(controls);

		var ret = [], cnt = 0;
		this.each(function() {
			ret[cnt] = Wysiwyg(this, options);
			cnt++;
		});
		return (ret[1]) ? ret : ret[0];

		/**
		 * カスタムコントローラセット
		 */
		function setOptControls(controls) {
			options.controls = $.extend(true, options.controls, Wysiwyg.TOOLBAR);
			for ( var line in controls )
	        {
				if ( options.controls[line] )
				{
					for ( var group in controls[line] )
					{
						if(options.controls[line][group]["group"] == controls[line][group]["group"]) {
							for ( var value in controls[line][group]["value"] ) {
								if ( value in options.controls[line][group]["value"] ) {
					                $.extend(options.controls[line][group]["value"][value], controls[line][group]["value"][value]);
					            } else
					                options.controls[line][group]["value"][value] = controls[line][group]["value"][value];
							}
						} else
							options.controls[line].push(controls[line][group]);
					}
				} else
					options.controls[line] = controls[line];

				// sort
				options.controls[line].sort(function(a, b){return (a.group > b.group) ? 1 : -1 ;});

	        }
		};
	};

	function Wysiwyg(el, options)
    {
        return this instanceof Wysiwyg
            ? this.init(el, options)
            : new Wysiwyg(el, options);
    }

	$.extend(Wysiwyg, {
    	/**
         * グループ - ボタン
         *
         * 説明(value):
         *          key  className,commandが指定されていなければ、ボタンのクラス名称
         *            commandが指定されていなければ、execCommandの第一引数（コマンド名）
         *         	  visible     : boolean  ボタンを表示するかどうか
         *			  tags        : array    選択範囲が、tagsで書かれてある内部であれば、ボタンをactiveに変更
         *			  css         : hash     選択範囲が、cssで書かれてあるスタイルであれば、ボタンをactiveに変更
         *			  active_class: array    選択範囲が、classで書かれてあるclassNameであれば、ボタンをactiveに変更
         *			  command     : string   execCommandの第一引数（コマンド名）
         *						             classNameが指定されていなければ、ボタンのクラス名称
         *    		  arguments   : array    execCommandの第三引数
         *			  className   : string   ボタンアイコン(a)のクラス名称
         *			  exec        : function 押下した時の動作を独自で設定（指定しない場合、execCommand)
         *            list        : hash     ボタンをリストボックスで表示（keyの値がexec指定にした場合の第三引数、valueはリストの表示文字列）
         *            extend_body : boolean  falseの場合、tag,cssが一致した場合、それ以上親のelementまで遡らない（default true）
         *                                   例えば、現要素がfont-family指定があり、一致していたら、その親要素で違うfont-family指定がしてあっても
         *                                   現要素のfont-family指定を優先する。
         *			  liClassName : string   ボタン(li)のクラス名称
         *            collapsedDis: boolean  trueの場合、選択範囲が折りたたまれている場合、ボタンを有効化しない(default false)
         *            eventtags   : array    イベントを実行するタグの一覧（event参照）
		 *            event       : hash     WYSIWYG内のeventtagsに記述されたタグにおけるイベントによるcallbackを指定する
		 *                                   記述方法：　{dblclick :function(e){
		 *								                      alert(e.type);
		 *								                 }}
		 *                                   等、現状、dblclick,contextmenuのみ指定可能
		 *            components  : hash     TOOLBAR共通メソッド定義用　呼び出し方：this.components.key_name.appley,this.components.key_name.call等
		 *                                   hash_key==this.components.key_nameで一意の値を設定する
		 *            title       : string   アイコンのtitleタグの内容 default __d(['nc_wysiwyg','icons'], key名)
         */
        TOOLBAR : [
			// 1行目
			[
				{
					group :   "001",
					value : {
						fontname      : { visible : true, css : {fontFamily : ''}, list : __d('nc_wysiwyg', 'fontname'), extend_body : false,
										  exec : function(value) {
		            					      var n = this.applyInlineStyle('span', {style : {fontFamily : value}});
		            					      if(n) this.rangeSelect(n);
		            					      else this.chgList(this.panel_btns['fontname'], '');
		            					  }
		            					},
		            	fontsize      : { visible : true, css : {fontSize : ''}, list : __d('nc_wysiwyg', 'fontsize'), extend_body : false,
		            					  exec : function(value) {
		            					      var n = this.applyInlineStyle('span', {style : {fontSize : value}});
		            					      if(n) this.rangeSelect(n);
		            					      else this.chgList(this.panel_btns['fontsize'], '');
		            					  }
		            					},
		            	formatblock   : { visible : true, tags : ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'address', 'pre', 'p'], list : __d('nc_wysiwyg', 'formatblock'), extend_body : false,
		            					  exec : function(value) {
		            					      var n = this.applyInlineStyle(value);
		            					      if(n) this.rangeSelect(n);
		            					      else this.chgList(this.panel_btns['fontname'], '');
		            					  }
		            					}
					}
				},
				{
					group :   "002",
					value : {
						bold          : { visible : true, tags : ['b', 'strong'], css : { fontWeight : 'bold' } },
		            	italic        : { visible : true, tags : ['i', 'em'], css : { fontStyle : 'italic' } },
		            	underline     : { visible : true, tags : ['u'], css : { textDecoration : 'underline' } },
		            	strikeThrough : { visible : true, tags : ['s', 'strike'], css : { textDecoration : 'line-through' } }
					}
				},
				{
					group :   "003",
					value : {
						subscript   : { visible : true, tags : ['sub'],
										exec : function(e) {
											var sel_n = null;
											this.editorDoc.execCommand("subscript", false, []);
		            					    var r = this.getRange();
											if(r.endContainer && r.endContainer.parentNode) {
												sel_n = r.endContainer.parentNode;
												if(sel_n.nodeName.toLowerCase() == 'sup' || sel_n.nodeName.toLowerCase() == 'sub')
													this.rangeSelect(sel_n, 1);
											} else {
												sel_n = this.getSelectNode().parentNode;
											}

		            					    if(sel_n && sel_n.childNodes[0] && sel_n.childNodes[0].nodeName.toLowerCase() == 'sup')
												sel_n = sel_n.childNodes[0];
											if(sel_n && sel_n.nodeName.toLowerCase() == 'sup' &&
										   		sel_n.childNodes.length == 1) {
										   		$(sel_n).after(sel_n.innerHTML);
										   		this.rangeSelect(sel_n.parentNode, 1);
										   		$(sel_n).remove();
										    }
										    this.checkTargets();
		            					}
		            				  },
		            	superscript : { visible : true, tags : ['sup'],
		            					exec : function(e) {
											var sel_n = null;
											this.editorDoc.execCommand("superscript", false, []);
		            					    var r = this.getRange();
		            					    if(r.endContainer && r.endContainer.parentNode) {
												sel_n = r.endContainer.parentNode;
												if(sel_n.nodeName.toLowerCase() == 'sup' || sel_n.nodeName.toLowerCase() == 'sub')
													this.rangeSelect(sel_n, 1);
											} else {
												sel_n = this.getSelectNode().parentNode;
											}
											if(sel_n && sel_n.childNodes[0] && sel_n.childNodes[0].nodeName.toLowerCase() == 'sub')
												sel_n = sel_n.childNodes[0];
											if(sel_n && sel_n.nodeName.toLowerCase() == 'sub' &&
										   		sel_n.childNodes.length == 1) {
										   		$(sel_n).after(sel_n.innerHTML);
										   		this.rangeSelect(sel_n.parentNode, 1);
										   		$(sel_n).remove();
										    }
										    this.checkTargets();
		            					}
		            				  }
					}
				},
				{
					group :   "004",
					value : {
						forecolor   : { visible : true,
										exec : function(e) {
											var c = $("a.forecolor", this.panel).css('backgroundColor');
											var n = this.applyInlineStyle('span', {style : {color : c}});
											if(n) {
												this.rangeSelect(n, 1);
											}
										}
									  },
						forecolor_arrow : { visible : true, liClassName : 'nc-wysiwyg-arrow',
										exec : function(e) {
											var self = this, event_el = e.target;
											var c = $.Common.getColorCode($("a.forecolor", this.panel)[0], 'backgroundColor');
											var callback = function() {self.components.colorpickerCallback.call(self, 'forecolor', c);};
											var options = {
												id        : self.id + "-forecolor",
												js        : [$._base_url+'js/plugins/jquery.colorpicker.js'],
												jsname    : ['$.fn.nc_colorpicker'],
												callback  : callback
											};
											this.toggleDialog(e, options);
										},
										components : {
											colorpickerCallback : function (name, c) {
												var self = this;
												var opts = {
													colorcode : c,
													callback  : function(v) {
														$("a." + name, self.panel).css({'backgroundColor': v});
														self.removeDialog(self.id + "-" + name);
														if(name == 'hilitecolor') {
															var n = self.applyInlineStyle('span', {style : {backgroundColor : v}});
														} else {
															var n = self.applyInlineStyle('span', {style : {color : v}});
														}
														if(n) {
															self.rangeSelect(n, 1);
														}
													},
													cancel_callback  : function(v) {
														self.removeDialog(self.id + "-" + name);
													}
												};
												$("#" + self.id + "-" + name).nc_colorpicker(opts);

											}
										}
									  },
		            	hilitecolor : { visible : true,
										exec : function(e) {
											var c = $("a.hilitecolor", this.panel).css('backgroundColor');
											var n = this.applyInlineStyle('span', {style : {backgroundColor : c}});
											if(n) {
												this.rangeSelect(n, 1);
											}
										}
									  },
		            	hilitecolor_rarrow : { visible : true, liClassName : 'nc-wysiwyg-rarrow',
										 exec : function(e) {
											var self = this, event_el = e.target;
											var c = $.Common.getColorCode($("a.hilitecolor", this.panel)[0], 'backgroundColor');
											var callback = function() {self.components.colorpickerCallback.call(self, 'hilitecolor', c);};
											var options = {
												id        : self.id + "-hilitecolor",
												js        : [$._base_url+'js/plugins/jquery.colorpicker.js'],
												jsname    : ['$.fn.nc_colorpicker'],
												callback  : callback
											};
											this.toggleDialog(e, options);
										  }
									    }
					}
				},
				{
					group :   "005",
					value : {
						removeFormat : {
			                visible : true,
			                exec    : function()
			                {
			                	var self = this;
			                	if($.browser.msie) {
			                		var spans, font, loop_flag = true;
			                		var f = self.currentNode ? self.currentNode : self.getSelectNode();
			                    	// 選択NodeTopをselect
		                    		var span = null, p = f.parentNode;
		                    		while(1) {
		                    			if(p.nodeName.toLowerCase() == 'span' && p.innerHTML == f.outerHTML) {
		                    				f = p;
		                    				p = p.parentNode;
		                    			} else {
		                    				break;
		                    			}
		                    		}

		                    		// fontタグへ置換
		                    		if(f.nodeName.toLowerCase() == 'body') {
		                    			var bm = self.getBookmark();
		                    			if(!bm || bm.length == 0) {
		                    				return;
		                    			}
		                    			f = self.applyInlineStyle('font');
		                    		} else if(f.style.color != '' || f.style.backgroundColor != '' || f.style.fonSize != '' || f.style.fontFamily != '') {
		                    			font = $("<font></font>", this.editorDoc);
				                    	f = self.replace(font[0], f, true);
		                    		}
		                    		while(loop_flag) {
			                    		loop_flag = false;
			                    		spans = $("span", f);
			                    		for (var i = 0; i < spans.length; ++i) {
			                    			if(spans[i].style.color != '' || spans[i].style.backgroundColor != '' || spans[i].style.fonSize != '' || spans[i].style.fontFamily != '') {
				                    			font = $("<font></font>",this.editorDoc);
						                    	self.replace(font[0], spans[i], true);
						                    	loop_flag = true;
						                    	break;
				                    		}
			                    		}
		                    		}
		                    		self.rangeSelect(f);
			                    } else if($.browser.safari) {
			                    	var f = self.getSelectNode();
			                    	var formatTags = " font span b script strong em i u ins s strike sub sup ";
			                    	if(formatTags.indexOf(" " + f.tagName.toLowerCase() + " ") != -1) {
			                    		var buf_f = f;
			                    		do {
			                    			if(!buf_f || buf_f.nodeName.toLowerCase() == "body")
			                    				break;
			                    			if(buf_f.childNodes.length == 1)
			                    				f = buf_f;
			                    		} while ( buf_f = buf_f.parentNode );
			                    		if(f.nextSibling && f.nextSibling.nodeName.toLowerCase() == 'br') {
				                    		var r = self.getRange();
											r.setStartBefore(f);
											r.setEndAfter(f.nextSibling);
											self.setRange(r);
										}
			                    	}
			                    }
			                    this.editorDoc.execCommand('removeFormat', false, []);
			                    if($.browser.safari && f.nodeName.toLowerCase() != "body") {
			                    	// Class Apple-style-spanを検索し、削除
			                    	var remove_el_arr = [];
			                    	var buf_f = f;
			                    	if(!f.parentNode) {
			                    		f = this.editorDoc.body;
			                    	} else {
				                    	do {
			                    			if(!buf_f || buf_f.nodeName.toLowerCase() == "body")
			                    				break;
			                    			if(buf_f.childNodes.length == 1)
			                    				f = buf_f;
			                    		} while ( buf_f = buf_f.parentNode );
			                    	}
			                    	$.each(self.select('span,font', f), function(k, el) {
										if ($(el).hasClass("Apple-style-span")) {
											remove_el_arr.push(el);
										}
									});
									for (i = remove_el_arr.length - 1; i >= 0; i--) {
										var rm_el = remove_el_arr[i];
										$(rm_el).after(rm_el.innerHTML);
										$(rm_el).remove();
									}
			                    }
			                    this.checkTargets();
			                    //this.editorDoc.execCommand('unlink', false, []);
			                }
			            }
					}
				}
			],
			// 2行目
			[
				{
					group :   "001",
					value : {
						undo : { visible : true, exec : function(){ this.undo();} },
		            	redo : { visible : true, exec : function(){ this.redo();} }
					}
				},
				{
					group :   "002",
					value : {
						justifyLeft   : { visible : true, css : { textAlign : 'left' },
										  exec : function(e) {
			            				  	this.components.execTextAlign.call(this, "justifyLeft", "left");
		            					  }
		            				    },
			            justifyCenter : { visible : true, tags : ['center'], css : { textAlign : 'center' },
			            				  exec : function(e) {
			            				  	this.components.execTextAlign.call(this, "justifyCenter", "center");
		            					  },
		            					  components : {
											execTextAlign : function (name, type) {
												if($.browser.msie || $.browser.opera){
				            				  		var n = this.currentNode ? this.currentNode : this.getSelectNode();
				            				  		if(n && n.nodeName.toLowerCase() == 'img') {
				            				  			n = this.applyInlineStyle('<div style="text-align:'+type+'">' + n.outerHTML + '</div>', null, true);
				            				  		} else if(n && n.nodeName.toLowerCase() != 'div') {
					            				  		n = this.applyInlineStyle('div', {style : {textAlign : type}});
													} else {
														$(n).css({textAlign : type});
													}
				            				  	} else {
				            				  		var sel_n = null;
													this.editorDoc.execCommand(name, false, []);
				            					    var r = this.getRange();
				            					    if(r.endContainer && r.endContainer.parentNode) {
														sel_n = r.endContainer.parentNode;
														if(sel_n.nodeName.toLowerCase() != 'div')
															sel_n = r.startContainer.parentNode;
														if(sel_n.nodeName.toLowerCase() == 'div') {
															$(sel_n).removeAttr("align");
															$(sel_n).css("textAlign", type);
														}
													}
													this.checkTargets();
				            				  	}
											}
										  }
		            				   },
			            justifyRight  : { visible : true, css : { textAlign : 'right' },
										  exec : function(e) {
			            				  	this.components.execTextAlign.call(this, "justifyRight", "right");
		            					  }
		            				    }
					}
				},
				{
					group :   "003",
					value : {
						insertOrderedList    : { visible : true, tags : ['ol'],
														exec : function(e) {
															if(!$.browser.msie)
				            				  					this.editorDoc.execCommand("insertOrderedList", false, []);
				            				  				else {
				            				  					var n = this.applyInlineStyle('div');
				            				  					this.rangeSelect(n);
				            				  					this.editorDoc.execCommand("insertOrderedList", false, []);
				            				  					if(n && n.parentNode) {
												   					$(n).before(n.innerHTML);
																	$(n).remove();
																}
				            				  				}
				            				  				this.checkTargets();
			            					  			}
			            					   		},
			            insertUnorderedList  : { visible : true, tags : ['ul'],
														exec : function(e) {
															if(!$.browser.msie)
				            				  					this.editorDoc.execCommand("insertUnorderedList", false, []);
				            				  				else {
				            				  					var n = this.applyInlineStyle('div');
				            				  					this.rangeSelect(n);
				            				  					this.editorDoc.execCommand("insertUnorderedList", false, []);
				            				  					if(n && n.parentNode) {
												   					$(n).before(n.innerHTML);
																	$(n).remove();
																}
				            				  				}
				            				  				this.checkTargets();
			            					  			}
			            					   		}
					}
				},
				{
					group :   "004",
					value : {
						outdent    : { visible : true,
									   exec : function() {
									   		var n = this.currentNode ? this.currentNode : this.getSelectNode();
									   		//if(n && n.nodeName.toLowerCase() == 'li') {
									   		//	this.editorDoc.execCommand('outdent', false, []);
		            				        //} else
		            				        if(n && n.nodeName.toLowerCase() == 'div') {
									   			var marginLeft = parseInt($(n).css("marginLeft"));
									   			if(marginLeft > 20) {
									   				$(n).css({marginLeft : (marginLeft - 20) + "px"});
									   			} else {
									   				$(n).css({marginLeft : ''});
									   				if(n.style.length == 0 || $(n).attr("style") == '') {
									   					$(n).before(n.innerHTML);
														$(n).remove();
														this.checkTargets();
													}
												}

											}
		            				   }
		            				 },
						indent     : { visible : true,
						               exec : function() {
									   		var n = this.currentNode ? this.currentNode : this.getSelectNode();
									   		var r = this.getRange();
									   		//if(n && n.nodeName.toLowerCase() == 'li') {
									   		//	this.editorDoc.execCommand('indent', false, []);
		            				        //} else
		            				        if(n && n.nodeName.toLowerCase() != 'div') {
									   			if(!$.browser.opera && r.startContainer && r.endContainer &&
									   				r.startContainer == r.endContainer) {

									   				var br = r.startContainer.nextSibling;
									   				if(!br) {
									   					br = this.editorDoc.createTextNode("");
									   					r.insertNode(br);
									   					br = br.nextSibling;
									   					r.setStartBefore(br);
									   				} else {
									   					r.setStartBefore(r.startContainer);
									   				}
									   				r.setEndAfter(br);
									   				this.setRange(r);
									   			}
									   			n = this.applyInlineStyle('div', {style : {marginLeft : "20px"}});
									   			if(n) {
													this.rangeSelect(n, 1);
												}
											} else {
												var m = (parseInt($(n).css("marginLeft"))) ? parseInt($(n).css("marginLeft")) : 0;
												$(n).css({marginLeft : (m + 20) + "px"});
											}
		            				   }
		            				 },
						blockquote : { visible : true, tags : ['blockquote'],
									   exec : function() {
									   		var n = this.getSelectBlockNode();
									   		if(n && n.nodeName.toLowerCase() != 'blockquote') {
									   			n = this.applyInlineStyle("blockquote", {"class" : "quote"});
												if(n) this.rangeSelect(n);
											} else {
												$(n).before(n.innerHTML);
												$(n).remove();
												this.checkTargets();
											}
		            				   }
		            				 }
					}
				},
				{
					group :   "005",
					value : {
						inserttable  : { visible : true,exec : function(e) {
							var self = this;
							var callback = function() {
												var opts = {
													callback : function(html){
														self.focus(true);
														if($.browser.msie)
															self.moveToBookmark(self.bookmark);
														var table = self.applyInlineStyle(html, null, true);
														self.removeDialog(self.id + "-inserttable");
														if(!table.nextSibling || table.nextSibling.nodeName.toLowerCase() != "br") {
															$(table).after("<br />");
														}
														self.rangeSelect(table);
														self.checkTargets();
														self.addUndo();
													}
												};
												$("#" + self.id + "-inserttable").nc_inserttable(e, opts);
											};
							var options = {
								id  : self.id + "-inserttable",
								css : [$._base_url+'css/plugins/nc_wysiwyg/inserttable.css'+'?'+$._v],
								js : [$._base_url+'js/plugins/nc_wysiwyg/inserttable.js'+'?'+$._v],
								jsname : ['$.fn.nc_inserttable'],
								callback : callback
							};
							this.toggleDialog(e, options);
						}},
						inserttable_rarrow : { visible : true, liClassName : 'nc-wysiwyg-rarrow',
							eventtags : ['table', 'thead', 'tbody', 'tfoot','tr', 'th', 'td'],
							event : {contextmenu :function(e, n){
								var pos = this.editor.position(), sc_pos = ($.browser.msie) ? {top : 0, left: 0} : this.getScrollDoc();
								var options = {
									style    : {left: e.pageX + pos['left'] - sc_pos['left'], top : e.pageY + pos['top'] - sc_pos['top']}
								};
								this.components.showTableMenu.call(this, $("a.inserttable-rarrow", this.panel), options);
								e.preventDefault();
								return false;
							}},
							tags : ['table', 'thead', 'tbody', 'tfoot','tr', 'th', 'td'],
							exec : function(e) {
								this.components.showTableMenu.call(this, e);
							},
							components : {
								showTableMenu : function (o, options) {
									var self = this, selPos;
									var selPos = self.getSelectTablePos();
									options = $.extend({
										id  : self.id + "-inserttable-rarrow",
										css : [$._base_url+'css/plugins/nc_wysiwyg/tablemenu.css'+'?'+$._v],
										js : [$._base_url+'js/plugins/nc_wysiwyg/tablemenu.js'+'?'+$._v],
										jsname : ['$.fn.nc_tablemenu'],
										className : "nc-wysiwyg-tablemenu",
										callback : function() {
											var opt = {table_pos : selPos};
											$("#" + self.id + "-inserttable-rarrow").nc_tablemenu(self, opt);
										}
									}, options);
									this.toggleDialog(o, options);
								}
							}
						}
					}
				},
				{
					group :   "006",
					value : {
						insertHorizontalRule : { visible : true, tags : ['hr'],
													exec : function(e) {
		            					      			var n = this.applyInlineStyle('hr', {style : {width : "100%", height : "2px"}}, true);
		            					      			if(n) this.rangeSelect(n);
		            					  			}
		            					  	   }
					}
				},
				{
					group :   "007",
					value : {
						insertsmiley : { visible : true,exec : function(e) {
											var self = this;
											var callback = function(){
																var opts = {
																	'type' : 'wysiwyg',
																	'blank': null,
																	'callback' : function(html){
																		self.focus(true);
																		if($.browser.msie)
																			self.moveToBookmark(self.bookmark);
																		self.applyInlineStyle(html, null, true);
																		self.removeDialog(self.id + "-smiley");
																		self.addUndo();
																	}
																};
																$("#" + self.id + "-smiley").nc_smiley(opts);
															};
											var options = {
												id  : self.id + "-smiley",
												css : [$._base_url+'css/plugins/nc_wysiwyg/smiley.css'+'?'+$._v],
												js  : [$._base_url+'js/plugins/nc_wysiwyg/smiley.js'+'?'+$._v],
												jsname  : ['$.fn.nc_smiley'],
												callback : callback
											};
											this.toggleDialog(e, options);
										}
									}
					}
				},
				{
					group :   "008",
					value : {
						inserttex : {
										visible : true,
										active_class : ['tex'],
										eventtags : ['img'],
										event : {dblclick :function(e, n){
											var self = this;
											var src = $(n).attr("src");
											var re = new RegExp(/.*\/nccommon\/mimetex\/\?c=(.*)/i);
											if(src.match(re)) {
												self.components.showInserTex.call(self, e, n);
												return true;
											}
											return false;
										}},
					  					components : {
											showInserTex : function (e, n) {
										  		var self = this;
										  		var text = '';
												n = (n == undefined) ? $(e.target) : $(n);
												if(n.get(0) && n.get(0).nodeName.toLowerCase() == 'img') {
													var src = $(n).attr("src");
													var re = new RegExp(/.*\/nccommon\/mimetex\/\?c=(.*)/i);
													if(src.match(re)) {
														text = RegExp.$1;
													}
												}
												//var e_n = $(e.target);
												var callback = function(){
																	var self = this;
																	var opts = {
																		callback : function(html){
																			self.focus(true);
																			if($.browser.msie)
																				self.moveToBookmark(self.bookmark);
																			var tex_el = self.applyInlineStyle(html, null, true);
																			self.removeDialog(self.id + "-inserttex");
																			if($.browser.safari && n && n.get(0) && n.get(0).nodeName.toLowerCase() == 'img') {
																				$(tex_el).insertBefore(n);
																				n.remove();
																			}
																			self.addUndo();
																		},
																		text : text
																	};
																	$("#" + self.id + "-inserttex").nc_mimetex(e, opts);
																};
												var options = {
													id  : self.id + "-inserttex",
													css : [$._base_url+'css/plugins/nc_wysiwyg/mimetex.css'+'?'+$._v],
													js  : [$._base_url+'js/plugins/nc_wysiwyg/mimetex.js'+'?'+$._v],
													jsname  : ['$.fn.nc_mimetex'],
													callback : callback
												};
												self.toggleDialog($(self.panel_btns['inserttex']).children(":first"), options);
											}
					  					},
									  	exec : function(e) {
										  	var self = this;
										    var n = self.currentNode ? self.currentNode : self.getSelectNode();
										    self.components.showInserTex.call(self, e, n);
										}
									}
					}
				},
				{
					group :   "009",
					value : {
						createlink : { visible : true,
									   tags : ['a'],
									   exec : function(e) {
									   		var self = this;
									   		var options = {
									   			id       : self.id + "-createlink",
												css      : [$._base_url+'css/plugins/nc_wysiwyg/insertlink.css'+'?'+$._v],
												js       : [$._base_url+'js/plugins/nc_wysiwyg/insertlink.js'+'?'+$._v],
												jsname    : ['$.fn.nc_insertlink'],
												callback : function(){
													var n = this.currentNode ? this.currentNode : this.getSelectNode();
													var opts = {
										        		callback : function(args) {
										        			var a, bm, v;
										        			// リンク挿入
										        			self.removeDialog(self.id + "-createlink");
										        			self.focus(true);
										        			bm = self.bookmark;
											        		if(n.nodeName.toLowerCase() != "img" && (!bm || ($.browser.msie && bm.length == 0) || (!$.browser.msie && bm.start == bm.end))) {
									        					var v = (args.title) ? args.title : args.href;
									        					if($.browser.msie)
											        				self.moveToBookmark(bm);
																var a = self.applyInlineStyle('<a>' + v + '</a>', args, true);
										        			} else if(n && n.nodeName.toLowerCase() != 'a') {
											        			if(!$.browser.mozilla) {
																	if(n.nodeName.toLowerCase() == "img") {
																		if(!$.browser.opera)
																			self.rangeSelect(n);
																		var a = self.applyInlineStyle('a', args, true);
																		$(a).append(n);
																	} else {
																		self.moveToBookmark(self.bookmark);
																		var a = self.applyInlineStyle('a', args);
																	}
																} else
											        				var a = self.applyInlineStyle('a', args);
											        		} else {
											        			// 更新
											        			$(n).attr(args);
											        			if(!args['title'])
											        				$(n).removeAttr('title');
											        			if(!args['target'])
											        				$(n).removeAttr('target');
											        			var a = n;
										        			}
										        			self.rangeSelect(a);
										        			self.addUndo();

										        			return true;
										        		},
										        		cancel_callback : function() {
										        			// キャンセル
										        			self.removeDialog(self.id + "-createlink");
										        			//self.focus(true);
										        			//self.moveToBookmark(bm);
										        			return true;
										        		}
											        };
											        if(n && n.nodeName.toLowerCase() == 'img' &&
											        	n.parentNode && n.parentNode.nodeName.toLowerCase() == 'a') {
											        	n = n.parentNode;
											        }
													if(n && n.nodeName.toLowerCase() == 'a') {
														opts = $.extend({
															url    : $(n).attr("href"),
															title  : $(n).attr("title"),
															target : $(n).attr("target")
														}, opts);
													}
													$("#" + self.id + "-createlink").nc_insertlink(opts);
												}
											}

											this.toggleDialog(e, options);
									   }
									 },
						unlink     : { visible : true,
									   tags : ['a'],
									   exec : function(e) {
									   		var n = this.currentNode ? this.currentNode : this.getSelectNode();
									   		if(n && n.nodeName.toLowerCase() == 'a')
									   			this.rangeSelect(n);
									   		this.editorDoc.execCommand('unlink', false, []);
									   }
			                         }
					}
				},
				{
					group :   "010",
					value : {
						savezip : { visible : true }
					}
				},
				{
					group :   "011",
					value : {
						insertvideo : { visible : true,exec : function(e) {
											var self = this;
											var callback = function(){
																var opts = {
																	callback : function(html){
																		self.focus(true);
																		if($.browser.msie)
																			self.moveToBookmark(self.bookmark);
																		var spn = self.applyInlineStyle('span', null, true);
																		spn.innerHTML = html;
																		spn.parentNode.insertBefore(spn.childNodes[0], spn);
																		$(spn).remove();
																		self.checkTargets();
																		self.removeDialog(self.id + "-insertvideo");
																		self.addUndo();
																	}
																};
																$("#" + self.id + "-insertvideo").nc_insertvideo(opts);
															};
											var options = {
												id  : self.id + "-insertvideo",
												css : [$._base_url+'css/plugins/nc_wysiwyg/insertvideo.css'+'?'+$._v],
												js  : [$._base_url+'js/plugins/nc_wysiwyg/insertvideo.js'+'?'+$._v],
												jsname  : ['$.fn.nc_insertvideo'],
												callback : callback
											};
											this.toggleDialog(e, options);
										}
						},
						insertimage : { visible : true,
										tags : ['img'],
										eventtags : ['img'],
										event : {dblclick :function(e, n){
											var self = this;
											var src = $(n).attr("src");
											var re = new RegExp(/.*\/nccommon\/mimetex\/\?c=(.*)/i);
											if(!src.match(re)) {
												self.components.insertimageDetail.call(self, e, n);
												return true;
											}
											return false;
										}},
										exec : function(e) {
											var self = this;
											var e_n = $(e.target);
											var n = this.currentNode ? this.currentNode : this.getSelectNode();
											if(n && n.nodeName.toLowerCase() == 'img') {
												self.components.insertimageDetail.call(self, e, n);
												return;
											}
											var options = {
												id       : self.id + "-insertimage",
												css      : [$._base_url+'css/plugins/nc_wysiwyg/insertimage.css'+'?'+$._v],
												js       : [$._base_url+'js/plugins/nc_wysiwyg/insertimage.js'+'?'+$._v],
												jsname   : ['$.fn.nc_insertimage'],
												style    : {left: "right", top : "top"},
												pos_base : self.editor,
												callback : function(e){
													var opts = {
														url      : self.options.image,
														callback : function(html) {
															self.focus(true);
															if($.browser.msie)
																self.moveToBookmark(self.bookmark);
															var img_el = self.applyInlineStyle(html, null, true);
															self.removeDialog(self.id + "-insertimage");
															if($.browser.safari && e_n && e_n.get(0).nodeName.toLowerCase() == 'img') {
																$(img_el).insertBefore(e_n);
																e_n.remove();
															}
															self.addUndo();
														},
														cancel_callback : function() {
										        			// キャンセル
										        			self.removeDialog(self.id + "-insertimage");
										        			return true;
										        		},
										        		wysiwyg : self
													};
													$("#" + self.id + "-insertimage").nc_insertimage(opts);
												}
											};
											//this.toggleDialog(document.body, options);
											self.toggleDialog($(self.panel_btns['insertimage']).children(":first"), options);
										},
										components : {
											insertimageDetail : function (e, img) {
												var self = this,pos, sc_pos;
												var ins_dialog = $("#" + self.id + "-insertimage");
												//img = (img == undefined) ? $(e.target) : img;
												// 詳細表示
												var options = {
													id       : self.id + "-insertimage",
													css      : [$._base_url+'css/plugins/nc_wysiwyg/insertimage.css'+'?'+$._v],
													js       : [$._base_url+'js/plugins/nc_wysiwyg/insertimage.js'+'?'+$._v],
													jsname   : ['$.fn.nc_insertimage'],
													effect   : "fade",
													callback : function(){
														var opts = {
															url      : self.options.image,
															img             : (typeof img == 'string' || typeof img == 'undefined') ? img : $(img).clone(),
															callback : function(html) {
											        			self.focus(true);
																if($.browser.msie)
																	self.moveToBookmark(self.bookmark);
																////var img = self.currentNode ? self.currentNode : self.getSelectNode();
																////if ($.browser.safari && img.tagName.toLowerCase() == 'img') {
																////	self.rangeSelect(img);
																////}
																var img_el = self.applyInlineStyle(html, null, true);
																self.closeDialogs();
																if($.browser.safari) {// && img && img.nodeName.toLowerCase() == 'img'
																	$(img_el).insertBefore($(img));
																	$(img).remove();
																}
																self.addUndo();
											        			return true;
											        		},
											        		cancel_callback : function() {
											        			// キャンセル
											        			self.removeDialog(self.id + "-insertimage");
											        			return true;
											        		},
											        		wysiwyg : self
												        };
												        $("#" + self.id + "-insertimage").nc_insertimage(opts);
													},
													cancel_callback : function() {
									        			// キャンセル
									        			self.removeDialog(self.id + "-insertimage");
									        			return true;
									        		}
												};
												if(ins_dialog.get(0)) {
													// TODO:ここにははいってこない？
													options.style = {left: "outleft", top : "top"};
													options.pos_base = ins_dialog;
													self.showDialog(ins_dialog, options);
												} else {
													options.style = {left: "right", top : "top"};
													options.pos_base = self.editor;
													//self.toggleDialog(document.body, options);
													self.toggleDialog($(self.panel_btns['insertimage']).children(":first"), options);
												}
											}
										}
						},
						insertfile : { visible : true,
									   exec : function(e) {
											var self = this;
											var options = {
												id       : self.id + "-insertfile",
												css      : [$._base_url+'css/plugins/nc_wysiwyg/insertfile.css'+'?'+$._v],
												js       : [$._base_url+'js/plugins/nc_wysiwyg/insertfile.js'+'?'+$._v],
												jsname   : ['$.fn.nc_insertfile'],
												style    : {left: "right", top : "top"},
												pos_base : self.editor,
												callback : function(e){
													var opts = {
														url      : self.options.file,
														callback : function(html) {
															self.focus(true);
															if($.browser.msie)
																self.moveToBookmark(self.bookmark);
															var a = self.applyInlineStyle(html, null, true);
															self.rangeSelect(a);
															self.removeDialog(self.id + "-insertfile");
															self.addUndo();
														},
														cancel_callback : function() {
										        			// キャンセル
										        			self.removeDialog(self.id + "-insertfile");
										        			return true;
										        		}
													};
													$("#" + self.id + "-insertfile").nc_insertfile(opts);
												}
											}
											//this.toggleDialog(document.body, options);
											self.toggleDialog($(self.panel_btns['insertfile']).children(":first"), options);
										}
						}
					}
				}
			]
		]
    });


	$.extend(Wysiwyg.prototype,
    {
    	id              : null,
		editor          : null,
        options         : {},
		panel           : null,
		panel_btns      : {},
		statusbar       : null,
		resize          : null,
		start_w         : null,
		start_h         : null,
		original        : null,

		initialContent  : null,
		editorDoc       : null,
		currentNode     : null,
		is_mac          : null,
		dialog_id       : 'nc-wysiwyg-dialog',
		bookmark        : null,
		_pendingStyles  : null,
		_keyhandler     : null,
		_checkNode      : null,

		nc_undoManager  : null,
		dialogs         : null,

		components      : {},
		events          : [],
		eventstags      : [],

		autoregist      : null,
		edit_mode       : 'edit',

        init : function(el, options)
        {
			var self = this, qel = $(el);

			this.is_mac = navigator.userAgent.indexOf('Mac') != -1;

            this.options = options || {};

            this.id = 'nc-wysiwyg' + $(el).attr('id');
            var newX = qel.width() || el.clientWidth;
            var newY = qel.height() || el.clientHeight;
            if($(el).parent().hasClass('nc-wysiwyg-outer')) {
            	// 初期処理終了済
            	return;
            }

            if ( el.nodeName.toLowerCase() == 'textarea' ) {
				this.original = el;
				$(el).addClass('nc-wysiwyg');

                if ( newX == 0 && el.cols )
                    newX = ( el.cols * 8 ) + 21;

                if ( newY == 0 && el.rows )
                    newY = ( el.rows * 16 ) + 16;

				var editor = this.editor = $('<iframe></iframe>').css({
                    height    : ( newY  ).toString() + 'px',
                    width     : ( newX  ).toString() + 'px'
                }).attr('class', 'nc-wysiwyg').load(function(){
					if(!self.editorDoc) {
						self.initialContent = $(self.original).val();
						self.editorDoc = self.getWin().document;
						self.chgDesignMode();
						self.initFrame();
						self.chgModeInit("edit");
						// undo redo
						self.nc_undoManager = nc_undoManager(self);

						var js = $._base_url+'js/plugins/jquery.nc_toggledialog.js';
						var jsname = '$.nc_toggledialog';
						$.Common.load(js, jsname, function() {
							self.dialogs = $.nc_toggledialog(self);
						});
						self.addUndo(true);
						if(self.options.focus) {
							self.focus(false);
						}
					}
                });

            }

            this.start_w = newX;
            this.start_h = newY;

            var panel = this.panel = $('<div></div>').addClass('nc-wysiwyg-panels');

			var statusbar = this.statusbar = $('<ul></ul>').addClass('statusbar');

			/**
			 * toolbar作成
             */
            this.appendControls();
            this.el = $('<div id="' + this.id + '"></div>').addClass('nc-wysiwyg-outer')
              .append(panel)
              .append( $('<div><!-- --></div>').css({ clear : 'both' }) )
              .append(editor);
			//this.chgEdit();
			$(el).hide().before(this.el);
			this.el.css({
                width : ( newX > 0 ) ? ( newX + parseInt(this.el.css("padding-left") || 0)).toString() + 'px' : '100%'
            });
			// $(el).addClass("editor_hidden").css({visibility : 'hidden'}).before(this.el);
			this.el.append(el)
              .append(statusbar)
              .append( $('<div><!-- --></div>').css({ clear : 'both' }) );

			/**
			 * statusbar作成
             */
            this.appendStatusbar();

/*
			既にinitFrameにて初期化しているため、必要がないと思われる
            if ( this.initialContent.length == 0 )
                this.setContent('');
*/

            if ( this.options.autoSave )
                $('form').submit(function() { self.saveContent(); });

/*
			リセットは、関係ないformのリセットボタンの可能性もあるため、コメント
            $('form').bind('reset', function()
            {
                self.setContent( self.initialContent );
                self.saveContent();
            });
*/
        },
		/* iframe initialize */
		initFrame : function(content) {

			var self = this, n, r, sn, en , br;
			if(content != undefined)
				this.options.content = content;
			if(this.options.content == undefined)
				this.options.content = this.initialContent;

			this._initFrame(this.editorDoc, this.options);

/*
IEで、iframe中のbodyのborderがつくから削除していると
思われるが、再現しないためコメント
            if ( $.browser.msie )
            {
                //Remove the horrible border it has on IE.
                setTimeout(function() { $(self.editorDoc.body).css('border', 'none'); }, 0);
            }
*/

			/*$(this.original).focus(function()
            {
                $(self.editorDoc.body).focus();
            });*/

			$(this.editorDoc).mouseup(function( e )
            {
            	self.bookmark = self.getBookmark();	// IEはbookmarkを保持しないため
            	self.currentNode = self.getSelectNode();
                self.checkTargets(e.target);
                self.addUndo();
                self.closeDialogs();
            });
/*
			$(this.editorDoc).mouseup(function( e )
            {
                self.checkTargets(e.target);
                self.closeDialogs();
            });
*/
            $(this.editorDoc).bind("contextmenu",function(e){
				// コンテキストメニュー
				_addEvents(e, "contextmenu");
			});


            $(this.editorDoc).keydown(function( e )
            {
            	if($.browser.safari && (e.keyCode == 46 || e.keyCode == 8)) {
            		// １行選択してdelete(backspace)ボタン、
            		// または、1行にわたるNodeを選択してdelete(backspace)
            		// ボタンを押すと、そのelementが削除されないため対処
					var data = null, p, pre_p, cur_flag, r = self.getRange(), f = self.getSelectNode();
					switch (r.startContainer.nodeType) {
					    case 3:
					    case 4:
					    case 8:
					    	data = r.startContainer.data;
					    	break;
					}
					p = f;
					cur_flag = true;
					do {
						if(cur_flag == true) {
							cur_flag = false;
						 	if(!(p.childNodes.length == 1 && f.innerHTML == data))
						 		break;
						 } else {
						 	if(p.nodeName.toLowerCase() == 'body' || p.childNodes.length != 1) {
						 		p = pre_p;
						 		break;
						 	}
						 }
						 pre_p = p;
					} while ( p = p.parentNode );

                  	if(data != null && f.innerHTML == data && p.nextSibling.nodeName.toLowerCase() == 'br') {
                  		r.setEndAfter(p.nextSibling);
						self.setRange(r);
                  	}
                  	// 削除後、さらに入力すると削除前のスタイルが残ってしまうため対処
                  	setTimeout(function() {self.collapse();}, 100);
				}
            	if(e.ctrlKey && e.keyCode == 90) {
            		// undo
            		self.undo();
            		e.preventDefault();
    				return false;
            	}
            	if(e.ctrlKey && e.keyCode == 89) {
            		// redo
            		self.redo();
            		e.preventDefault();
    				return false;
            	}
            	if($.browser.msie && e.keyCode == 8) {
            		// brのみの状態でBackspaceを押すとpタグに変換されてしまうため対処
            		setTimeout(function() {
            			if(self.editorDoc.body.innerHTML.toLowerCase() == "<p>&nbsp;</p>") {
            				self.editorDoc.body.innerHTML = "<br />";
            				self.rangeSelect(self.editorDoc.body.childNodes[0]);
            				self.collapse(true);
            				self.checkTargets();
            			}
            		}, 100);
            	}
            });

            $(this.editorDoc).keypress(function( e )
            {
            	n = self.getSelectNode();
            	if( e.keyCode == 13 && e.shiftKey == false && self.detachBlockQuote(n)) {
            		e.preventDefault();
					return false;
            	} else if ( !$.browser.mozilla && e.keyCode == 13 ) {
            		if(n.nodeName.toLowerCase() != 'li') {
            			if ( $.browser.msie ) {
            				var rng = self.getRange();
		                    rng.pasteHTML('<br />');
		                    rng.collapse(false);
		                    rng.select();
		                } else if($.browser.safari) {
		                	self.editorDoc.execCommand("InsertLineBreak", false, []);
		                	var bm = self.getBookmark();
		                	self.getWin().scrollTo(bm.scrollX, bm.scrollY+16);
		                } else {
		                	r = self.getRange();
		                	sn = r.startContainer;
							r = self.getRange();
							if(r.startContainer && r.startContainer.nextSibling)
								n = r.startContainer.previousSibling;
	            			self.editorDoc.execCommand('inserthtml', false, '<br><br id="nc-wysiwygbr">');
							r = self.getRange();
	            			en = r.startContainer;
	            			br = $('#nc-wysiwygbr', self.editorDoc);
	            			if(sn != en || $.browser.opera) {
		            			r.setStartBefore(br[0]);
								r.setEndBefore(br[0]);
								self.setRange(r);
							}
							if(sn != en && !$.browser.opera) {
								br.removeAttr("id");
								self.rangeSelect(br[0]);
		                    } else {
		                    	br.removeAttr("id");
		                    	var buf_br = br.prev();
	                    		br.remove();
	                    		br = buf_br;
		                    }
		                    r.setStartAfter(br[0]);
							r.setEndAfter(br[0]);
							self.setRange(r);
							var bm = self.getBookmark();
							self.getWin().scrollTo(bm.scrollX, bm.scrollY+16);
	           			}
	           			e.preventDefault();
	           			return false;
            		}
            	}
            });

            $(this.editorDoc).keyup(function( e )
            {
            	var k = e.keyCode;
				self.bookmark = self.getBookmark();	// IEはbookmarkを保持しないため
				self.currentNode = self.getSelectNode();
                if ((k >= 33 && k <= 36) || (k >= 37 && k <= 40) || k == 13 || k == 45 || k == 46 || k == 8  ||
                		(e.ctrlKey && (k == 86 || k == 88)) || k.ctrlKey || (this.is_mac && (k == 91 || k == 93))) {
            		// enter、上下左右、baskspace, Delキー,カット＆ペーストならば、checkTargetsを呼び出す
            		self.checkTargets(self.currentNode);
	            	self.addUndo();
            	}
            	if ( self.options.autoSave )
            		self.saveContent();
            });

            $(this.editorDoc).dblclick(function( e )
            {
            	if(self.getMode() == 'edit')
            		_addEvents(e, "dblclick");
            	e.preventDefault();
    			return false;
            });

			// auto regist
            if( this.options.autoRegistForm ) {
            	this.autoregist = this.content();
            	var unique_id = $.Common.uniqueId();
            	this.el.attr('data-wysiwyg-unique-id', unique_id);
            	setTimeout(function() {
            		var buf_el = $('#' + self.id);
            		if(unique_id == buf_el.attr('data-wysiwyg-unique-id')) {
            			self.regist(null, null, true);
						if (self.options.autoRegistForm && self.el && self.el[0] && self.el[0].nodeName) {
							setTimeout(arguments.callee, self.options.autoRegistTime);
						}
            		}
				}, this.options.autoRegistTime);
            }

            return;

            function _addEvents(e, type) {
            	var n = e.target;
				var node_name = n.nodeName.toLowerCase(), chk = false;
            	for(var i in self.events) {
            		if(self.events[i][type]) {
            			// イベントが一致
            			for(var j in self.eventstags[i]) {
            				if(node_name == self.eventstags[i][j]) {
            					chk = true;
            					break;
            				}
            			}
            			// タグ一致
            			var ret = false;
            			if(chk == true)
	            			ret = self.events[i][type].call(self, e, n);
            			if(ret)
            				break;
            		}
            	}
            }
		},

		_initFrame : function(doc, options) {
			var self = this, headstr='', vq, rel, type, media;
			var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
			var br = '<br />';

			if (options.css)
                for ( var i in options.css )
	                headstr += '<link rel="stylesheet" type="text/css" media="screen"  charset="utf-8" href="' + options.css[i] + '" />';

            if (options.js)
            	for ( var i in options.js )
	                headstr += '<script type="text/javascript" charset="utf-8" src="' + options.js[i] + '"></script>';

			if (options.cssInc) {
				// 親のCSSをinclude
				$("link").each(function(k, v){
					vq = $(v);
					rel = type = media = '';
					if(vq.attr("rel")) {
						rel = 'rel="' + vq.attr("rel") + '" ';
					}
					if(vq.attr("type")) {
						type = 'type="' + vq.attr("type") + '" ';
					}
					if(vq.attr("media")) {
						media = 'media="' + vq.attr("media") + '" ';
					}
					headstr += '<link ' + rel + type + media + 'href="' + vq.attr("href") + '" />';
				});
			}
			headstr += '<style>html,body {height : 100% !important; background-image : none; padding:0px;margin:0px; background-color:#ffffff;}';
			if($.browser.msie || $.browser.opera) {
				//options.htmlの値を変更したことにより、tableのフォントサイズが一サイズ大きくなったため
				headstr += 'td {font-size:80%;}';
			}
			headstr += '</style>';
			if(options.content != undefined && $.trim(options.content).match(re))
				br = '';						// 最後にbrがある場合は、追加しない
			doc.open();
            doc.write(
                options.html
					.replace(/INITIAL_TITLE/, options.title)
                    .replace(/INITIAL_CONTENT/, (options.content != undefined) ? options.content + br : '<br />')
                    .replace(/INITIAL_HEADER/, headstr)
            );
            doc.close();

            if (options.style)
            {
                setTimeout(function()
                {
                    $(doc).find('body').css(options.style);
                }, 100);
            }
            this._setStyleWithCSS();
		},

		getWin : function(iframe) {
			return (typeof iframe == "undefined") ? this.editor[0].contentWindow : iframe.contentWindow;
		},

        getContent : function(rmUnwantedBr)
        {
			var t = this;
			if(t.getMode() == 'html')
				t.setContent($(t.original).val());

            var html = $.trim($(t.editorDoc).find('body').html());
            if (rmUnwantedBr || t.options.rmUnwantedBr ) {
				var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
				html = $.trim(html).replace(re, '');
			}
			return html;
        },

        setContent : function(newContent, body)
        {
        	body = (body == undefined) ? this.editorDoc.body : body;
        	//SCRIPTタグをinnerHTMLした場合、IEでは削除されるため
			if($.browser.msie || $.browser.safari) {
				body.innerHTML = "<br />" + newContent;
				body.removeChild(body.firstChild);
			} else {
				body.innerHTML = newContent;
			}
        },

        saveContent : function(parse_flag)
        {
            if ( this.original )
            {
                var content = (parse_flag && this.options.parseHtml) ? this.parseContent() : this.getContent();

                $(this.original).val(content);
                return content;
            }
        },

		// getTextAreaと同等の処理 nc2系では、このmethodの別名とする
		content : function(root)
		{
			return this.parseContent(true, true, root);
		},

		clear : function()
        {
            this.setContent('<br />');
            this.saveContent();
            this.addUndo();
        },

        appendMenu : function( panel, cmd, args, className, fn )
        {
            var self = this;
            var args = args || [];
            var li = $('<li></li>').addClass('l').append(
                $('<a href="javascript:;"><!-- --></a>').addClass("l").addClass(className || cmd)
                .click(function(e) {
                	var li = $(e.target).parent("li");
					if(self.getMode() == 'edit' && li.css("opacity") >= 1) {
						self.closeDialogs($(this).next());
						args.push(e);
						self.focus(true);
						if ( fn ) fn.apply(self, args); else self.editorDoc.execCommand(cmd, false, args);
						if ( self.options.autoSave ) self.saveContent();
						self.addUndo();
					}
					self.focus(false, function(){
													if($.browser.msie)
														self.moveToBookmark(self.bookmark);
												});
					e.preventDefault();
					return false;
	            })
            ).appendTo( panel );
            self.panel_btns[className] = li;

            var id = this.id + '-btn-' + className;
            li.attr('id', id);

            if(cmd == 'forecolor') {
				// forecolorのデフォルト色
				$("a", li).css({'backgroundColor': self.options.forecolor});
			} else if(cmd == 'hilitecolor') {
				// hilitecolorのデフォルト色
				$("a", li).css({'backgroundColor': self.options.hilitecolor});
			}

            return li;
        },

        appendList : function( panel, cmd, args, className, fn, list )
        {
        	var self = this, callback, btn_callback;
        	var li = $('<li></li>').addClass('l').appendTo( panel );
        	for(var i in list) {
        		if(cmd == 'formatblock' && i != '') {
					list[i] = '<' + i + '>' + list[i] + '</' + i + '>';
				} else if(cmd == 'fontname' && i != '') {
					list[i] = '<div style="font-family:' + i.replace(/\\/, "\\\\") + '">' + list[i] + '</div>';
				} else if(cmd == 'fontsize' && i != '') {
					list[i] = '<div style="font-size:' + i + '">' + list[i] + '</div>';
				}
			}
			self.panel_btns[className] = li;

			callback = function(e, key, value) {
				if(key == "") {
					if(self.getMode() != 'edit') return false;
            		self.closeDialogs($(this).next());
            		return true;
				}
        		var li = $(e.target).parents("li.l");
				if(self.getMode() == 'edit' && li.css("opacity") >= 1) {
					var args = args || [key] || [];
					if ( fn ) fn.apply(self, args); else self.editorDoc.execCommand(cmd, false, args);
					if ( self.options.autoSave ) self.saveContent();
					self.addUndo();
				}
				self.focus(false, function(){
												if($.browser.msie)
													self.moveToBookmark(self.bookmark);
											});
        	};

        	return self.appendListMain(li, list, className || cmd, callback, args);
        },

        appendControls : function()
        {
        	var t = this, panel = $('<ul></ul>').addClass('nc-wysiwyg-panel').appendTo( this.panel );

			for ( var line in this.options.controls ) {
				// 改行
				var br_flag = false;
				if(li && line != 0) br_flag = true;

				for ( var group in this.options.controls[line] ) {
					var group_li = null;
					var first = false;
					for ( var name in this.options.controls[line][group]["value"] ) {
						var control = this.options.controls[line][group]["value"][name];

						// netcommons用グローバル定義によるボタン非表示
						if(name == "insertimage" && $._nc.nc_wysiwyg['allow_attachment'] == 0 ||
							name == "insertfile" && $._nc.nc_wysiwyg['allow_attachment'] <= 1 ||
							name == "insertvideo" && $._nc.nc_wysiwyg['allow_video'] == 0)
							control.visible = false;

						if((name == "insertimage" && !this.options.image) || (name == "insertfile" && !this.options.file)) {
							control.visible = false;
						}
						if ( control.visible ) {
							// ボタン背景
							if(group_li) {
								if(!first) group_li.addClass("lbtn");
								else group_li.addClass("cbtn");
								first = true;
							}

							if(br_flag) {
								//li.css({ clear : 'both' });
								var panel = $('<ul></ul>').addClass('nc-wysiwyg-panel').appendTo( this.panel );
								br_flag = false;
							}

							if ( control.list ) {
								// リスト表示
								var li = t.appendList(
									panel,
			                        control.command || name,
			                        control.arguments || [],
			                        control.className || control.command || name || 'empty',
			                        control.exec,
			                        control.list
			                    ).addClass("list");
			                    li.click(function(e){
			                    	if($(this.parentNode.parentNode).css("opacity") < 1) {
			                    		$(this).next().hide();
			                    		e.preventDefault();
						            	return false;
			                    	}
			                    });
							} else {
								var li = t.appendMenu(
									panel,
			                        control.command || name,
			                        control.arguments || [],
			                        control.className || control.command || name || 'empty',
			                        control.exec
			                    );
			                    if(control.liClassName)
									li.addClass(control.liClassName);
								if(control.title)
									li.children().attr("title", control.title);
								else {
									li.children().attr("title", __d(['nc_wysiwyg', 'icons'], name));
								}
								group_li = li;
							}
							if ( control.event ) {
								t.events.push(control.event);
								t.eventstags.push(control.eventtags);
							}

							if( control.components ) {
								$.extend(t.components, control.components);
							}

							if ( control.collapsedDis ) {
								li.addClass("collapsedDis").css({opacity : '0.4'});
							}
							if ((li.hasClass("nc-wysiwyg-arrow") || li.hasClass("nc-wysiwyg-rarrow")) && ( control.tags || control.css )) {
								li.css({opacity : '0.4'});
							}
						}
					}
					if(group_li) {
						if(!first) group_li.addClass("btn");
						else group_li.addClass("rbtn");
					}

				}
			}
        },

		appendModeMenu : function( lang_key, active_flag, className )
        {
            var self = this;
			className = className || lang_key;
            var li = $('<li></li>').append(
                $('<a>' + __d('nc_wysiwyg', lang_key) + '</a>')
            );
			if(active_flag) li.addClass("nc-wysiwyg-active");
			li.mousedown(function() {
				var pre_mode = self.getMode();
				var li = $(this);
				var objs = $('li', li.parent());
				objs.each(function() {
					var obj = $(this);
					(obj[0] == li[0]) ? obj.addClass("nc-wysiwyg-active") : obj.removeClass("nc-wysiwyg-active");
				});
				switch (className) {
					case "edit":
						self.chgEdit(pre_mode);
						break;
					case "html":
						self.chgHtml(pre_mode);
						break;
					case "preview":
						self.chgPreview(pre_mode);
						break;
				}
            }).appendTo( this.statusbar )
			.addClass(className);
        },

		appendStatusbar : function()
        {
        	var modemenu = ["edit", "html", "preview"];
			for ( var i in modemenu )
				this.appendModeMenu(modemenu[i], (modemenu[i] == "edit") ? true : false);

			// pathメニュー
			$('<li>'+__d('nc_wysiwyg', 'path')+'&nbsp;:&nbsp;</li>').addClass('path').appendTo( this.statusbar ).attr('id', 'path-'+ this.id);

			// resizeメニュー
			this.appendResize("resize");
		},

		appendResize : function(className) {
			var self = this, img_w = ($.browser.msie) ? 17 : 22;
			var s_w = 0;
			$("li", self.statusbar).each(function(i, s_li) {
				s_w += parseInt($(s_li).outerWidth() || 0);
			});
			var resize = $('<a></a>');
			var li = $('<div></div>').append( resize ).addClass(className)
			.appendTo( this.el );
            //.css('left', parseInt(this.editor.outerWidth() || 0) - s_w - img_w  + 'px')
            //.appendTo( this.statusbar );

            // リサイズmousedownイベント
            resize.mousedown(function() {
            	var sx = null, sy = null;
            	var m_w = self.el.width();
            	var m_h = self.el.height();
            	var w = self.editor.width() || $(self.original).width();
            	var h = self.editor.height() || $(self.original).height();
            	var r_w = resize.width();

            	self.editor.blur();
            	self.editor.hide();
	            $(self.original).hide();

	        	self.resize = $('<div></div>')
	        				.css({
	        					width    : w,
			                    height   : h
			                }).attr('class', 'resizebox')
			                .insertBefore( self.editor );

            	// リサイズmousemoveイベント
            	var resizeMouseMove = function(event) {
            		var x_offset = 0, y_offset = 0;
            		if(sx == null) {
            			sx = event.pageX, sy = event.pageY;
            		} else {
            			x_offset = event.pageX - sx, y_offset = event.pageY - sy;
            		}
            		if(parseInt(w || 0) + x_offset < self.start_w) x_offset = self.start_w - parseInt(w || 0);
            		if(parseInt(h || 0) + y_offset < self.start_h) y_offset = self.start_h - parseInt(h || 0);

					// リサイズ
            		self.resize.css({
       					width      : parseInt(w || 0) + x_offset + 'px',
	                    height     : parseInt(h || 0) + y_offset + 'px'
	                });
	                self.el.css({
       					width      : parseInt(m_w || 0) + x_offset + 'px',
	                    height     : parseInt(m_h || 0) + y_offset + 'px'
	                });
	                resize.css({
       					width      : parseInt(r_w || 0) + x_offset + 'px'
	                });
            	};

            	// リサイズmouseupイベント
            	var resizeMouseUp = function(event) {
            		var mode = self.getMode();
            		var w = self.resize.width();
            		var h = self.resize.height();

            		// リサイズ
            		self.editor.css({
       					width      : self.resize.width(),
	                    height     : self.resize.height()
	                });
	                $(self.original).css({
       					width      : self.resize.width(),
	                    height     : self.resize.height()
	                });
	                self.resize.remove();

            		$(document).unbind('mousemove', resizeMouseMove);
            		$(document).unbind('mouseup', resizeMouseUp);

            		(mode != "html") ? self.editor.show() : $(self.original).show();
            		self._setStyleWithCSS();
		            event.preventDefault();
            	};

            	$(document).mousemove(resizeMouseMove);
				$(document).mouseup(resizeMouseUp);

            });
		},

		chgEdit : function(pre_mode) {
			if(pre_mode == "edit")
				return;
			var self =this;
			var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
            $("ul", this.panel).css({opacity : '1.0'});
            this.closeDialogs();
            if(pre_mode == "html")
				this.setContent($(this.original).val());
			// blankを取り除いたものを表示
			var content = this.parseContent(true);
			$(this.original).val(content);
			if(!($.browser.msie || $.browser.opera) && !content.match(re)) {
				// 最後にbrが無い場合、付与
				this.setContent(content + '<br />');
			} else
				this.setContent(content);
			this.editor.show();
			$('#path-'+ this.id).css({visibility : 'visible'});
            $(this.original).hide();
            this.chgDesignMode('on');

        	// フォーカスの移動
        	this.focus(false, this.checkTargets);

        	// Operaでモードを変更した場合、iframeのイベントがリセットされるようなので修正(Ver9.64)
            if($.browser.msie || $.browser.opera)
	            this.initFrame(content);

			setTimeout(function() {self.chgModeInit("edit");}, 100);
		},

		chgHtml : function(pre_mode) {
			if(pre_mode == "html")
				return;
			var self =this;
			$("ul", this.panel).css({opacity : '0.4'});
			this.closeDialogs();
			this.chgModeInit("html");
			this.saveContent(true);
			$(this.original).show().css({visibility : 'visible'});
			$('#path-'+ this.id).css({visibility : 'hidden'});
            this.editor.hide();

            // フォーカスの移動
            this.focus();
		},

		chgPreview : function(pre_mode) {
			if(pre_mode == "preview")
				return;
			var self =this;
			if(pre_mode == "html")
				this.setContent($(this.original).val());
			// blankを取り除いたものを表示
			var content = this.parseContent(true);
			$(this.original).val(content);
			this.setContent(content);
			$("ul", this.panel).css({opacity : '0.4'});
			this.closeDialogs();
			//this.saveContent();
			this.editor.show();
            $(this.original).hide();
            $('#path-'+ this.id).css({visibility : 'hidden'});
            this.chgDesignMode('off');

			// IEでdesignModeをoffにした場合、iframeが再読み込みされるため
            if($.browser.msie)
	            this.initFrame(content);
	        setTimeout(function() {self.chgModeInit("preview");}, 100);
		},

		/**
		 * モード変更init
		 * tableの枠線が0、かつ、編集モードならば、点線を表示する。
		 */
		chgModeInit : function(mode, root) {
			var t = this, border;
			root = root || this.editorDoc;
			mode = mode || self.getMode();
			if(!root)
				return;
			switch (mode) {
				case "edit":
					$("table,td", root).each(function(i, el) {
						$.each(['Top','Right','Bottom','Left'], function(k, v) {
							if($(el).css("border" + v + "Width") == "0px" || $(el).css("border" + v + "Style") == "none") {
								$(el).attr("data-nc-wysiwyg-border-" + v.toLowerCase(), "1");
								$(el).css("border" + v + "Width", "1px");
								$(el).css("border" + v + "Style", "dotted");
								$(el).css("border" + v + "Color", "#666666");
							}
						});
					});
					break;
				default :
					$("table,td", root).each(function(i, el) {
						$.each(['Top','Right','Bottom','Left'], function(k, v) {
							if($(el).attr("data-nc-wysiwyg-border-" + v.toLowerCase())) {
								$(el).css("border" + v + "Width", "0px");
								$(el).css("border" + v + "Style", "none");
								$(el).css("border" + v + "Color", "");
								if(mode == "html")
									$(el).removeAttr("data-nc-wysiwyg-border-" + v.toLowerCase());
							}
						});
					});
					break;
			}
		},

		chgDesignMode : function(mode) {
			if($.browser.msie && parseInt($.browser.version) >= 9) {
				// designModeをonにしたものをoffにした場合、
				// 「Internet Explorerは動作を停止しました」
				// と表示されてしまうため、offには設定させないように修正
				mode = 'on';
			}
			this.editorDoc.designMode = (mode != undefined) ? mode : ((this.editorDoc.designMode != 'on') ? 'on' : 'off');
			this._setStyleWithCSS();
		},

		_setStyleWithCSS : function(v) {
			var self = this;
			if($.browser.mozilla) {
				setTimeout(function() {
					try {
						self.editorDoc.execCommand("styleWithCSS", v || false, false);
					} catch (e) {
						try {self.editorDoc.execCommand("useCSS", v || false, true);} catch (e) {}
					}
				}, 500);
			}
		},

		_nodeChanged : function( el )
		{
			var self = this, sep = '&nbsp;&gt;&gt;&nbsp;', a, pa, t=0, nodeN;
			var spn = document.createElement('span');
			spn.innerHTML = __d('nc_wysiwyg', 'path') + '&nbsp;:&nbsp;';
			var path = document.getElementById('path-'+ this.id);
			path.innerHTML = '';
			path.appendChild(spn);
			var n_el = el, buf_n;
			do {
				nodeN = el.nodeName.toLowerCase();
			    if ( el.nodeType != 1 || nodeN == 'body' ||  nodeN == 'html')
			        break;
			    if(nodeN == "b")
			    	nodeN = "strong";
			    a = $('<a href="javascript:;"></a>').addClass('nc-wysiwyg-path-' + t)
	            .click(function(e) {
	            	var n = e.target;
	            	if (n.nodeName == 'A') {
	            		var cth = 0, th = n.className.replace(/^.*nc-wysiwyg-path-([0-9]+).*$/, '$1');
	            		n = n_el ;	//self.getSelectNode();
	            		do {
	            			if(n.nodeType == 1 && cth++ == th) {
	            				if(($.browser.opera || $.browser.safari) && n.nodeName == 'TABLE') {
	            					buf_n = $(n).children("TBODY")[0];
	            					if(buf_n)
	            						n = buf_n;
	            				}
	            				self.focus(true);
	            				self.rangeSelect(n);
	            				self.checkTargets();
	            				self.bookmark = self.getBookmark();	// IEはbookmarkを保持しないため
	            				self.currentNode = self.getSelectNode();
	            				self.closeDialogs();
	            				break;
	            			}
	            			if ( n.nodeName.toLowerCase() == 'body' ||  n.nodeName.toLowerCase() == 'html') break;
	            		} while ( n = n.parentNode );
					}
	            	e.preventDefault();
	            	return false;
	            }).html(nodeN);
	            if(t == 0) a.insertAfter(spn);
	            else a.insertBefore(pa);

	            pa = a;
			    t++;
			} while ( el = el.parentNode );
		},

        checkTargets : function( element )
        {
        	var o, bm;
        	element = element || this.getSelectNode();

			if(this._checkNode != element) {
				this._checkNode = element;
	        	// path
				this._nodeChanged( element );
				this.currentNode = element;

				// 上部ボタンの色変更
				for ( var line in this.options.controls )
				{
					for ( var group in this.options.controls[line] )
					{
						for ( var name in this.options.controls[line][group]["value"] )
						{
							var control = this.options.controls[line][group]["value"][name];
							var className = control.className || control.command || name || 'empty';
			                var li = this.panel_btns[className]; //$($('.' + className, this.panel)[0].parentNode);
			                if(!li) continue;
			                var a_className = li.hasClass("nc-wysiwyg-arrow") ? "nc-wysiwyg-arrow-active" : (li.hasClass("nc-wysiwyg-rarrow") ? "nc-wysiwyg-rarrow-active" : "nc-wysiwyg-active");
			                li.removeClass(a_className);
			                if ( !control.visible)
			                	continue;
			                if ( control.list) {
			                	var list_control = $("span.listcontent", li);
			                	list_control.html(control.list[""]);
			                }

			                if ( control.active_class )
			                {
			                	var el = $(element), break_flag = false;
			                    do {
			                        if ( el[0].nodeType != 1 )
			                            break;

			                        $.each(control.active_class, function(k, v) {
			                        	if ( $(el).hasClass(v) ) {
											// リスト表示に一致するものあり
											li.addClass(a_className);
											if(control.list && typeof control.list[v] != 'undefined')
												list_control.html(control.list[v]);
											if(control.extend_body == false) {
												break_flag = true;
											}
											return false;
										}
									});
			                        if(break_flag)
										break;
			                    } while ( el = el.parent() );
			                }

			                if ( control.tags )
			                {
			                    var el = element;

			                    do {
			                        if ( el.nodeType != 1 )
			                            break;

			                        if ( $.inArray(el.tagName.toLowerCase(), control.tags) != -1 ) {
			                            li.addClass(a_className);
			                            if(control.list && typeof control.list[el.tagName.toLowerCase()] != 'undefined')
			                            	list_control.html(control.list[el.tagName.toLowerCase()]);
			                        	if(control.extend_body == false) break;
			                        }
			                    } while ( el = el.parentNode );
			                }

			                if ( control.css )
			                {
			                    var el = $(element), break_flag = false;
			                    do {
			                        if ( el[0].nodeType != 1 )
			                            break;

			                        for ( var cssProperty in control.css ) {
										if(el[0].style[cssProperty] == undefined || el[0].style[cssProperty] == '')
											continue;
										if($.browser.safari && cssProperty == "fontFamily") {
											var p_key = el.css(cssProperty).toString().replace(/, /g, ",");
										} else {
											var p_key = el.css(cssProperty).toString();
										}
										if ( control.list && control.list[p_key]) {
											// リスト表示に一致するものあり
											li.addClass(a_className);
											list_control.html(control.list[p_key]);
											if(control.extend_body == false) {
												break_flag = true;
												break;
											}
										} else if ( el.css(cssProperty).toString().toLowerCase() == control.css[cssProperty] ) {
			                                li.addClass(a_className);
			                                if(control.extend_body == false) {
			                                	break_flag = true;
			                                	break;
			                                }
										}
									}
									if(break_flag)
										break;
			                    } while ( el = el.parent() );
			                }
			                if ( a_className != "nc-wysiwyg-active" && ( control.tags || control.css || control.active_class)) {
			                	li.css({opacity : ($(li).hasClass(a_className) ? '1.0' : '0.4')});
			                }
						}
					}
				}
			}

			// 選択範囲が折りたたまれている場合、ボタンを有効化しない
			bm = this.getBookmark();
			if(element && element.nodeName.toLowerCase() != "img" && (!bm || ($.browser.msie && bm.length == 0) || (!$.browser.msie && bm.start == bm.end))) {
				o = $("li.collapsedDis", this.panel);
				o.each(function(k, v) {
					$(v).css({opacity : (!$(v).hasClass("nc-wysiwyg-active")) ? '0.4' : '1.0'});
				});
			}else
				$("li.collapsedDis", this.panel).css({opacity : '1.0'});
        },

        parseContent : function(blank_flag, clone_flag, root) {
        	var self = this, blank_flag = blank_flag || false;
        	var mes  = [];
        	var edit_mode = self.edit_mode;
        	self.edit_mode = this.getMode();
			if( clone_flag ){
				root = root || $("<div></div>")[0];
				self.setContent(self.editorDoc.body.innerHTML, root);
			}else
				root = root || this.editorDoc.body;

        	if($.browser.mozilla && $(root.lastChild) && $(root.lastChild).attr("type") == "_moz") {
        		// mozBRの削除処理（type=_moz）
        		$(root.lastChild).remove();
			}
			if($.browser.mozilla && $(root.firstChild) && $(root.firstChild).attr("_moz_editor_bogus_node")) {
        		//_moz_editor_bogus_nodeの削除処理（type=_moz）
        		$(root.firstChild).remove();
			}
        	//WYSIWYGで自動的に付与される属性を削除（_moz_･･･等）
        	if( !clone_flag )
        		this.setContent(root.innerHTML);

        	//try{
        		var html ="";
        		var tab_space_num = (blank_flag) ? '' : this.options.tabStr;		// tabの半角スペース数
        		var closingTags = " head noscript script style div span tr td tbody table em strong b i code cite dfn abbr acronym font a title sub sup object em strike s ";
        		var allowEmpty = " td script object iframe video ";

        		var deleteNode = /[\t\r\n ]/g;
        		if($.browser.opera)
        			var deleteText = /(\t|\r|\n)/g;
        		else
        			var deleteText = /[\t\r\n]/g;
        		var parse = function(root, tab_space) {
        			var html = "",row_html,closed, a,name,value,attrs,alt_flag, node_name,pre_type = null,split_style, split_buf, split_params;
        			var n = (blank_flag) ? '' : self.options.lineBreak;
        			var re = new RegExp(n + "$"), unit, embed_flag, embed_at, param_html, param_name, althtml_html;
        			for (var node = root.firstChild; node; node = node.nextSibling) {
	        			row_html = "";
	        			switch (node.nodeType) {
						    case 1: // Node.ELEMENT_NODE
						    case 11: // Node.DOCUMENT_FRAGMENT_NODE
						    	node_name = node.tagName.toLowerCase();
								if(node_name == 'b') node_name = 'strong';
								//iframeの場合<iframe src="XXX" />と変換してしまうとjavascriptの読み込みがストップされるようなので
								//中の値があるなしにかかわらず、閉じタグを挿入する
								if(node.tagName.toLowerCase() == "iframe") {
									closed = false;
								} else {
									closed = (!(node.hasChildNodes() || node.nodeType == 1 && (closingTags.indexOf(" " + node.tagName.toLowerCase() + " ") != -1)));
								}
								if(closed == false && node.innerHTML.replace(deleteNode,'') == '' && allowEmpty.indexOf(" " + node.tagName.toLowerCase() + " ") == -1) {
									// block要素の中身が空なのでスルー
									if(pre_type == 3) {
										// 1つ前がTextならば、最後の改行削除
										html = html.replace(re, '');
									}
									if(!clone_flag) mes.push(__d(['nc_wysiwyg', 'mes'], 'del_empty').replace(/%s/, node_name));
									continue;
								} else if($._nc.nc_wysiwyg['allow_js'] == 0 && (node_name == "script" || node_name == "object")) {
									// allow_jsが0ならば、javascript上から無害化(サーバ上アプリからも無害化する必要あり)
									if(!clone_flag) mes.push(__d(['nc_wysiwyg', 'mes'], 'del_js'));
									continue;
								}
								attrs = node.attributes;
								alt_flag = false;
								embed_flag = false;
								althtml_html = '';
								if($.browser.msie && node_name == "embed") {
									embed_flag = true;
									embed_at = '';
									param_html = '';
									$.each(['width','height'], function(k, v) {
										value = $(node).attr(v);
										if(node.style[v]) {
											embed_at += v + ":" + node.style[v] + ";";
										} else if(value) {
											unit = (value.match(/%$/)) ? "%" : "px";
											embed_at += v + ":" + parseInt(value) + unit + ";";
										}
									});
									if(embed_at != '')
										embed_at = ' style="' + embed_at + '"';
								}

								if($.browser.msie && node_name.match(/^\//i))
									continue;
								row_html += "<" + node_name;
								for (var i = 0; i < attrs.length; ++i) {
									a = attrs.item(i);
									if (!a.specified) {
										continue;
									}
									name = self.getNodeName(a);
									value = self.getNodeValue(node, a);
									// border,width,height属性は、style指定に変換
									if((name == "border" || name == "width" || name == "height")) {
										if(parseInt(value) == "0")
											value = $(node).css(name);
										unit = (value.toString().match(/%$/)) ? "%" : "px";
										if(name == "border" && (node.style.borderWidth == "" || node.style.borderWidth == "0px"))
											$(node).css({borderWidth : parseInt(value) + unit});
										else if(node.style[name] =="") {
											node.style[name] = parseInt(value) + unit;
										}
									}
								}
								attrs = node.attributes;
								for (var i = 0; i < attrs.length; ++i) {
									a = attrs.item(i);
									if (!a.specified) {
										continue;
									}
									name = self.getNodeName(a);
									value = self.getNodeValue(node, a);
									// border,width,height属性は、style指定に変換
									if((name == "border" || name == "width" || name == "height")) {
										continue;
									}
									if(($.browser.safari || $.browser.opera || $.browser.msie) && name == "style") {
										// 小文字に変換し、border部分のスタイルを整理
										//value = value.toLowerCase();
										split_style = value.replace(/"/g, "'").split(/;/);
										split_params = {};

										for (var j = 0; j < split_style.length; ++j) {
											split_buf = split_style[j].split(/:/);
											var s_key = split_buf[0].replace(/^\s+/, '').replace(/\s+$/, '').toLowerCase();
											if(s_key == '') continue;
											var s_value = split_buf[1].replace(/^\s+/, '').replace(/\s+$/, '');
											if( s_key != "font-family" ) {
												s_value = s_value.toLowerCase();
											}
											split_params[s_key] = s_value;
										}
										value = '';
										if(split_params['border-top-width'] && split_params['border-top-width'] == split_params['border-right-width'] &&
													split_params['border-top-width'] == split_params['border-bottom-width'] &&
													split_params['border-top-width'] == split_params['border-left-width'])
											value = 'border-width:' + split_params['border-top-width'] + ';';
										else if(split_params['border-top'] && split_params['border-top'] == split_params['border-right'] &&
													split_params['border-top'] == split_params['border-bottom'] &&
													split_params['border-top'] == split_params['border-left'])
											value = 'border:' + split_params['border-top'] + ';';
										else if((split_params['border-top'] && split_params['border-top'] == split_params['border-bottom']) &&
													(split_params['border-right'] && split_params['border-right'] == split_params['border-left']))
											value = 'border:' + split_params['border-top'] + ' ' + split_params['border-right'] + ';';
										var c_value = '';
										if(split_params['border-top-color'] && split_params['border-top-color'] == split_params['border-right-color'] &&
													split_params['border-top-color'] == split_params['border-bottom-color'] &&
													split_params['border-top-color'] == split_params['border-left-color'])
											c_value = 'border-color:' + split_params['border-top-color'] + ';';
										var s_value = '';
										if(split_params['border-top-style'] && split_params['border-top-style'] == split_params['border-right-style'] &&
													split_params['border-top-style'] == split_params['border-bottom-style'] &&
													split_params['border-top-style'] == split_params['border-left-style'])
											s_value = 'border-style:' + split_params['border-top-style'] + ';';
										var m_value = '';
										if(split_params['margin-top'] && split_params['margin-top'] == split_params['margin-right'] &&
													split_params['margin-top'] == split_params['margin-bottom'] &&
													split_params['margin-top'] == split_params['margin-left'])
											m_value = 'margin:' + split_params['margin-top'] + ';';
										var p_value = '';
										if(split_params['padding-top'] && split_params['padding-top'] == split_params['padding-right'] &&
													split_params['padding-top'] == split_params['padding-bottom'] &&
													split_params['padding-top'] == split_params['padding-left'])
											p_value = 'padding:' + split_params['padding-top'] + ';';

										for (var k in split_params ) {
											var v = split_params[k];
											if(c_value != '' && (k == 'border-top-color' ||
												k == 'border-right-color' || k == 'border-bottom-color' || k == 'border-left-color'))
												continue;
											else if(s_value != '' && (k == 'border-top-style' ||
												k == 'border-right-style' || k == 'border-bottom-style' || k == 'border-left-style'))
												continue;
											else if(m_value != '' && (k == 'margin-top' ||
												k == 'margin-right' || k == 'margin-bottom' || k == 'margin-left'))
												continue;
											else if(p_value != '' && (k == 'padding-top' ||
												k == 'padding-right' || k == 'padding-bottom' || k == 'padding-left'))
												continue;
											else if(value == '' || (k != 'border' &&
												k != 'border-top' && k != 'border-right' && k != 'border-bottom' && k != 'border-left' &&
												k != 'border-top-width' && k != 'border-right-width' && k != 'border-bottom-width' && k != 'border-left-width'))
												value += k + ':' + v + ';';
										}
										value += c_value + s_value + m_value + p_value;
									} else if(name == "alt") {
										alt_flag = true;
									} else if($.browser.msie && name.match(/^jquery/i)) {
										// jquery用の属性がIEだと付与されてしまうため削除
										continue;
									} else if($.browser.msie && name == "althtml") {
										// object：IEのobjectタグ下のembedタグがalthtml属性となってしまう
										althtml_html = value;
										continue;
									}
									if( $.browser.mozilla && !clone_flag && (name == "src" || name == "href")) {
										// this.setContent(root.innerHTML)した段階でencodeされるようなので元に戻す
										try{
											value = decodeURIComponent(value);
										} catch(e){}
									}
									if(name=="value" || name=="alt" || name=="title") {
										value = $.Common.escapeHTML(value);
									}

									if(name=="style" && value == "")
										continue;
									if(name == "class" && $.browser.safari) {
										var apple_re = new RegExp(/\s*Apple-style.+\s*/i);
										value = value.replace(apple_re, '');
										if(value == "")
											continue;
									}

									row_html += " " + name + '="' + value.replace(/"/g, "'") + '"';
									if(embed_flag && " type style script style code alt hspace vspace border width height ".indexOf(" " + name + " ") == -1) {
										// param属性
										param_name = (name == "src") ? "movie" : name;
										param_html += '<param name="' + param_name + '" value="'+ value +'"></param>' + n;
									}
								}
								// 古いIEはvalue属性をattributesで取得してくれないため
								if ($.browser.msie
									&& parseInt($.browser.version) <= 8
									&& node_name == "input"
									&& node.value != undefined
									&& !row_html.match(/ value=".*"/i)) {
									row_html += " " + 'value="' + node.value + '"';
								}

								row_html += closed ? " />" : ">" + n;
								if($.browser.msie && (node_name == "script" || (node_name == "object" && parseInt($.browser.version) < 8))) {
									// trim
									// object：IEのobjectタグ下のembedタグがalthtml属性となってしまう
									// embedタグは判別しないが、paramタグは判別する
									var embed_html = node.innerHTML.replace(/^\s+/, '').replace(/\s+$/, '') + n;
									if(node_name == "object") {
										if(parseInt($.browser.version) < 7) {
											var embed_re = new RegExp(/(<embed(.|\s)+?(\/){1}>)/i);
											embed_html = embed_html.replace(embed_re, '')+ RegExp.$1;
											embed_re = new RegExp(/<param((.|\s)+?)>/ig);
											embed_html = embed_html.replace(embed_re, '<param$1></param>');
										}
										embed_html = embed_html.replace(/&/ig, "&amp;");
										row_html += embed_html.replace(/&amp;amp;/ig, "&amp;");
									} else
										row_html += embed_html;
								} else if(node_name == "pre") {
									// preタグはそのままの状態を保持
									row_html += node.innerHTML;
								} else {
									row_html += parse(node, tab_space + tab_space_num);
								}
								if(althtml_html)
									row_html += tab_space + $.trim(althtml_html) + n;
								if (!closed) {
									row_html += tab_space + "</" + node_name + ">";
								}
								if(embed_flag) {
									row_html = "<object"+ embed_at + ">" + n + param_html + n + row_html + "</object>" + n;
								}
								break;
		        			case 3: // Node.TEXT_NODE
		        				node.data = node.data.replace(deleteText,'');
		        				if(node.data == "") continue;

		        				if(/^noscript|script|style|table|tbody|tr|ul|li$/i.test(node.parentNode.tagName)) {
									row_html = node.data.replace(/^[ ]+/, '').replace(/[ ]+$/, '');
								} else {
									if(blank_flag) {
										if(edit_mode == "html")
											row_html = self.htmlEncode(node.data).replace(/^[ ]+/, '').replace(/[ ]+$/, '');
										else
											row_html = self.htmlEncode(node.data);
									} else {
										row_html = self.htmlEncode(node.data).replace(/^ /ig, "&nbsp;").replace(/ $/ig, "&nbsp;");
									}
								}
		        				break;
		        			case 4: // Node.CDATA_SECTION_NODE
		        				node.data = node.data.replace(deleteText,'');
		        				if(node.data == "") continue;
		        				row_html = "<![CDATA[" + node.data + "]]>";
								break;
		        			case 8: // Node.COMMENT_NODE
		        				node.data = node.data.replace(deleteText,'');
		        				if(node.data == "") continue;
		        				row_html = "<!--" + node.data + "-->";
								break;
		        		}
	        			if(node.nodeType == 1 || node.nodeType == 11 || node.nodeType == 3 || node.nodeType == 4 || node.nodeType == 8) {
		        			var act_n = (row_html != '') ? n : '';
		        			if(pre_type == 3 && !html.match(re))
		        				html += row_html + act_n;
		        			else
		        				html += tab_space + row_html + act_n;
		        		}
		        		pre_type = node.nodeType;
					}
					return html;
				}

				html = parse(root, "");
        	//} catch(e){
        	//	html = $(root).html();
	    	//}
	    	if( clone_flag ) $(root).remove();
	    	if ( clone_flag || this.options.rmUnwantedBr ) {
				var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
				html = html.replace(re, '');
			}
			if(mes.length > 0 && self.options.formatMes) {
				var dialog,con_mes = '';
				options = {
					id       : "nc-wysiwyg-mes-" + self.id,
					className: "nc-wysiwyg-mes",
					style    : {opacity : '0.8', left: "center", top : "center"},
					pos_base : self.el,
					callback : function(e){
						$.each(mes, function(k, v) {
							con_mes += '<li>' + v + '</li>';
						});
						$("#nc-wysiwyg-mes-" + self.id).html('<ul>' + con_mes + '</ul>');
						setTimeout(function() {
							if(dialog) dialog.hide();
						}, self.options.format_time);
					}
				}
				if(!dialog)
					dialog = $.nc_toggledialog(options);
				dialog.show(self.el, options);
			}
        	return html;
        },
        htmlEncode : function(str) {
			str = str.replace(/&/ig, "&amp;");
			str = str.replace(/</ig, "&lt;");
			str = str.replace(/>/ig, "&gt;");
			str = str.replace(/\x22/ig, "&quot;");
			str = str.replace(/\xA0/ig, "&nbsp;");
			return str;
		},
        // edit or html or preview
        getMode : function() {
        	var self = this, act_btn = $("li.nc-wysiwyg-active", this.statusbar);
        	if(act_btn.hasClass("edit"))
	        	return "edit";
			else if(act_btn.hasClass("html"))
				return "html";
			else if(act_btn.hasClass("preview"))
				return "preview";
        },

        focus : function(now, callback) {
        	var self = this, mode = this.getMode();
        	now = (now == undefined) ? false : now;
        	if(mode == "edit") {
	        	if(now)
	        		self.getWin().focus();
	        	else
	        		setTimeout(function() {
	        			self.getWin().focus();
	        			if(callback)
	        				callback.call(self);
	        		}, 100);
			} else if(mode == "html") {
				if(now)
					$(self.original).focus();
				else
					setTimeout(function() {
						$(self.original).focus();
						if(callback)
	        				callback.call(self);
					}, 100);
			}
        },

        /* Dialog関連 */
	    toggleDialog : function(o, options) {
	    	if(o.target) o = $(o.target);
	    	if(!options.id) options.id = this.dialog_id;
	    	this.dialogs.toggle(o, options);
	    },

	    showDialog : function(o, options) {
	    	if(o.target) o = $(o.target);
	    	if(!options.id) options.id = this.dialog_id;
	    	this.dialogs.show(o, options);
	    },

	    removeDialog : function(id) {
	    	this.dialogs.hide(id || this.dialog_id);
	    },

	    // 表示中のダイアログ非表示
	    // self以外を削除する
		closeDialogs : function(self) {
			$("div.listmenu", this.panel).each(function(k, n){
				if(!self || !self[0] || self[0] != n)
					$(n).css({display : "none"});
			});
			this.dialogs.removes(self);
		},

	    /* Undo Redo関連 */
	    addUndo : function(init_flag) {
	    	this.nc_undoManager.add(init_flag);
	    },

	    undo : function() {
	    	this.nc_undoManager.undo();
	    	this.checkTargets();
	    },

	    redo : function() {
	    	this.nc_undoManager.redo();
	    	this.checkTargets();
	    },

		/* 登録 */
		regist : function(success, error, autoregist_flag) {
			var t = this, content, params, dialog;
			autoregist_flag = autoregist_flag || false;
			if(!autoregist_flag && t.getMode() == 'html')
				t.setContent($(this.original).val());
			var root = $("<div></div>")[0];
			t.setContent(this.editorDoc.body.innerHTML, root);
			this.chgModeInit("html", root);
			content = this.content(root);
			var form = $(t.options.autoRegistForm);
			if(form.get(0) && (!autoregist_flag || (autoregist_flag && t.autoregist != content))) {
				t.autoregist = content;			// エラーがでても、contentがかわるまではPOSTを投げるのをやめる。
				var data = form.serializeArray();
				data[data.length] = {name : 'auto_regist',value : true};
				var options = {
					data : data,
					success : function(res, status, xhr){
						var post_id = $.trim(res);
						if(isFinite(post_id) && parseInt(post_id) > 0) {
							//t.autoregist = content;
							var autoregist_post_id = $(t.el).children("input[name=autoregist_post_id]:first");
							if(!autoregist_post_id.get(0)) {
								autoregist_post_id = $('<input type="hidden" name="autoregist_post_id" value="'+ post_id +'"/>');
								autoregist_post_id.appendTo( t.el );
							}
							autoregist_post_id.val(post_id);
							var mes = __d(['nc_wysiwyg', 'autoregist'], 'ok');
							now = new Date();
							h = now.getHours(), m = now.getMinutes();
							mes += ((h >= 10) ? h : '0'+h) + ':' + ((m >= 10) ? m : '0'+m);
							var autoregist = $(t.el).children(".nc-wysiwyg-autoregist:first");
							if(!autoregist.get(0)) {
								autoregist = $('<div class="nc-wysiwyg-autoregist"></div>');
								autoregist.appendTo( t.el );
							}
							autoregist.html(mes);
						}
					},
					error : function(xhr, textStatus, errorThrown) {
					}
				};
				var params = {
					'data-ajax' : '',
					'data-ajax-type' : 'post',
					'data-ajax-url' : form.attr('action')
				};

				$.Common.ajax(null, null, params, options);
			}
		},

        /* Selection関連 */
        getSelection : function() {
            return this.getWin().getSelection ? this.getWin().getSelection() : this.editorDoc.selection;
        },

        getRangeCount : function() {
        	var sel = this.getSelection();
        	return sel.rangeCount;
        },

        getRanges : function() {
        	var t = this, ranges=[], r_cnt;
        	r_cnt = t.getRangeCount();
        	if(r_cnt == undefined)
        		ranges.push(t.getRange());
        	else if(r_cnt > 0) {
        		for (var i = 0; i < r_cnt; i++)
        			ranges.push(t.getRange(i));
        	}
        	return ranges;
        },

        getRange : function(r_num) {
            var range, sel = this.getSelection();
            r_num = r_num || 0;
            if (!sel) return null;
            try {
				range = sel.rangeCount > 0 ? sel.getRangeAt(r_num) : (sel.createRange ? sel.createRange() : this.getWin().document.createRange());
			} catch (ex) {}
			if (!range || range == '') range = ($.browser.msie) ? this.editorDoc.body.createTextRange() : this.editorDoc.createRange();
			return range;
        },

        createRange : function() {
			var sel = this.getSelection();
			return ($.browser.msie) ? sel.createRange() : this.editorDoc.createRange();
		},

		setRange : function(range) {
			var sel = this.getSelection();
			if (sel) {
				if(($.browser.msie))
					range.select();
				else {
					sel.removeAllRanges();
					sel.addRange(range);
				}
			}
		},

		collapse : function(b) {
			var n, range = this.getRange();

			if (range.item) {
				n = range.item(0);
				range = this.editorDoc.body.createTextRange();
				range.moveToElementText(n);
			}

			range.collapse(!!b);
			this.setRange(range);
		},

		getSelectNode : function() {
			var r = this.getRange(),s, e;

			if (!$.browser.msie) {
				s = this.getSelection();
				if (!r) return this.editorDoc.body;

				e = r.commonAncestorContainer;
				if (!r.collapsed) {
					if ($.browser.safari && s.anchorNode && s.anchorNode.nodeType == 1)
						return s.anchorNode.childNodes[s.anchorOffset] || this.editorDoc.body;

					if (r.startContainer == r.endContainer) {
						if (r.startOffset - r.endOffset < 2) {
							if (r.startContainer.hasChildNodes())
								e = r.startContainer.childNodes[r.startOffset];
						}
					}
				}
				if( e.nodeType == 1 )
					return e || this.editorDoc.body;
				else
					return $(e).parent('*')[0] || this.editorDoc.body;
			}

			return r.item ? r.item(0) : r.parentElement();
		},

		getSelectBlockNode : function() {
			var blockTags = " blockquote center div dl form h1 h2 h3 h4 h5 h6 hr ol p pre table ul ";
        	var n = this.currentNode ? this.currentNode : this.getSelectNode();
			do {
      			if(blockTags.indexOf(" " + n.tagName.toLowerCase() + " ") != -1) {
      				break;
      			}
      			if ( n.nodeName.toLowerCase() == 'body' ||  n.nodeName.toLowerCase() == 'html') break;
      		} while ( n = n.parentNode );

			return n;
		},

		rangeSelect : function(n, c) {
			var t = this, r = t.getRange(), s = t.getSelection(), b, fn, ln, d = t.getWin().document;

			function find(n, start) {
				var walker, o;

				if (n) {
					walker = d.createTreeWalker(n, NodeFilter.SHOW_TEXT, null, false);

					// Find first/last non empty text node
					while (n = walker.nextNode()) {
						o = n;
						if (n.nodeValue.replace(/^\s*|\s*$/g, '').length != 0) {
							if (start)
								return n;
							else
								o = n;
						}
					}
				}

				return o;
			};

			if ($.browser.msie) {
				try {
					b = d.body;

					if (/^(IMG|TABLE)$/.test(n.nodeName)) {
						r = b.createControlRange();
						r.addElement(n);
					} else {
						r = b.createTextRange();
						r.moveToElementText(n);
					}

					r.select();
				} catch (ex) {
					// Throws illigal agrument in IE some times
				}
			} else {
				if (c) {
					fn = find(n, 1) || t.select('br:first', n)[0];
					ln = find(n, 0) || t.select('br:last', n)[0];
					if (fn && ln) {
						r = d.createRange();

						if (fn.nodeName == 'BR')
							r.setStartBefore(fn);
						else
							r.setStart(fn, 0);

						if (ln.nodeName == 'BR')
							r.setEndBefore(ln);
						else
							r.setEnd(ln, ln.nodeValue.length);
					} else
						r.selectNode(n);
				} else
					r.selectNode(n);

				t.setRange(r);
			}

			return n;
		},

		/**
		 * @return hash
		 *			sel_name   : string "table" or "row" or "col" or "cell" or false
		 *                        テーブル内を選択していないならば、falseを返す
		 *          table_el   : object table element
		 *          sel_els    : array object table element OR tr element OR td element
		 *                      elementをvalueにもつ配列で返す。
		 *                      複数選択されている場合を考慮するため。
		 *                      sel_nameが"cell"の場合、cell_elsと同じ値がセットされる
		 *                      sel_nameが"cell"の場合、cell_elsと同じ値がセットされる
		 *			cell_els   : array 選択されているtd elementをすべて配列で返す
		 *          ranges     : array 選択range
		 */
		getSelectTablePos : function() {
			var t = this, sel_el, ranges = t.getRanges(), table, rows = [], cells = [], row_cnt, col_cnt, commonCon, td;
			var buf_rows = [], buf_cols = [], sel_rows = [], sel_cols = [];
			var ret = {
				sel_name : false,
				table_el : [],
				sel_els  : [],
				cell_els : [],
				ranges   : ranges
			};
			sel_el = t.currentNode || t.getSelectNode();
			switch (sel_el.nodeName.toLowerCase()) {
				case "table":
					ret.sel_name = "table";
					ret.table_el = sel_el;
					ret.sel_els.push(sel_el);
					ret.cell_els = _getTdByTable(sel_el);
					break;
				case "tbody":
				case "thead":
				case "tfoot":
					// tbodyは、tableとする
					ret.sel_name = "table";
					table = $(sel_el).parents('table')[0];
					ret.table_el = table;
					ret.sel_els.push(table);
					ret.cell_els = _getTdByTable(table);
					break;
				case "tr":
					ret.sel_name = "row";
					table = $(sel_el).parents('table')[0];
					ret.table_el = table;
					ret.sel_els.push(sel_el);
					ret.cell_els = _getTdByTr(sel_el);
					break;
				case "th":
				case "td":
					if(ranges.length == 1) {
						ret.sel_name = "cell";
						table = $(sel_el).parents('table')[0];
						ret.table_el = table;
						ret.sel_els.push(sel_el);
						ret.cell_els.push(sel_el);
					} else {
						table = $(sel_el).parents('table')[0];
						rows = _getTr(table);
						cells = _getTdByTr(rows[0]); 	// 1行目のtd
						row_cnt = rows.length;
						col_cnt = 0;
						$.each(cells, function(k, cel){
							col_cnt += cel.colSpan;
						});
						for (var i = 0; i < row_cnt; i++)
							buf_rows[i] = col_cnt;
						for (var i = 0; i < col_cnt; i++)
							buf_cols[i] = row_cnt
						ret.table_el = table;
						for (var i = 0; i < ranges.length; i++) {
							commonCon = ranges[i].commonAncestorContainer;
							if(commonCon && commonCon.cells)
								td = commonCon.cells[ranges[i].startOffset];
							if(td) {
								for(var j = td.parentNode.rowIndex; j < td.parentNode.rowIndex + td.rowSpan; j++) {
									buf_rows[j] -= td.colSpan;
									if(buf_rows[j] == 0)
										sel_rows.push(td.parentNode);
								}
								for(var j = td.cellIndex; j < td.cellIndex + td.colSpan; j++) {
									buf_cols[j] -= td.rowSpan;
									if(buf_cols[j] == 0)
										sel_cols.push(td);
								}

								ret.cell_els.push(td);
								ret.sel_els.push(td);
							}
						}
						var row_eq = 0, sel_all = true, sel_cell = false;
						for (var i = 0; i < row_cnt; i++) {
							if(buf_rows[i] != 0) {
								sel_all = false;
								if(row_eq != 0 && buf_rows[i] != row_eq) {
									sel_cell = true;
									break;
								}
								row_eq = buf_rows[i];
							}
						}

						var col_eq = 0;
						for (var i = 0; i < col_cnt; i++) {
							if(buf_cols[i] != 0) {
								sel_all = false;
								if(col_eq != 0 && buf_cols[i] != col_eq) {
									sel_cell = true;
									break;
								}
								col_eq = buf_cols[i];
							}
						}
						if(sel_all) {
							// すべて選択
							ret.sel_name = "table";
							ret.sel_els = table;
						} else if(sel_cell) {
							// セルが選択
							ret.sel_name = "cell";
						} else if(sel_rows.length > 0) {
							// 行が選択
							ret.sel_name = "row";
							ret.sel_els = sel_rows;
						} else if(sel_cols.length > 0) {
							// 列が選択
							ret.sel_name = "col";
							ret.sel_els = sel_cols;
						}
					}
					break;
				default:
					sel_el = $(sel_el).parents('td,th')[0];
					if(sel_el) {
						ret.sel_name = "cell";
						ret.table_el = $(sel_el).parents('table')[0];
						ret.sel_els.push(sel_el);
						ret.cell_els.push(sel_el);
					}
			}
			return ret;

			function _getTr(table) {
				var ret = [], child;
				for (var i = 0; i < table.childNodes.length; i++) {
					child = table.childNodes[i];
					if(child.nodeName.toLowerCase() == "tbody" || child.nodeName.toLowerCase() == "thead" ||
						 child.nodeName.toLowerCase() == "tfoot") {
						for (var j = 0; j < child.childNodes.length; j++) {
							if(child.childNodes[j].nodeName.toLowerCase() == "tr")
								ret.push(child.childNodes[j]);
						}
					} else if(child.nodeName.toLowerCase() == "tr") {
						ret.push(child);
					}
				}
				return ret;
			}

			function _getTdByTr(tr, ret) {
				var ret = ret || [], child;
				for (var i = 0; i < tr.childNodes.length; i++) {
					child = tr.childNodes[i];
					if(child.nodeName.toLowerCase() == "td" || child.nodeName.toLowerCase() == "th") {
						ret.push(child);
					}
				}
				return ret;
			}

			function _getTdByTable(table) {
				var ret = [], tr = _getTr(table);
				$.each(tr, function(k, v) {
					ret = _getTdByTr(v, ret);
				});
				return ret;
			}
		},
		/**
		  * blockquoteタグ内部でリターンキーをクリックした場合、blockquote外部へ移動する
		  * 掲示板の返信などの引用文などでblockquoteタグを使用
		  * 基本、nc2系のまま実装
		  */
		detachBlockQuote : function(bq) {
			var t = this, r = t.getRange(), s = t.getSelection();

			// check
			$(bq).parents("blockquote").each( function(k, v) {
				bq = v;
			});

			if(bq.nodeName.toLowerCase() != 'blockquote')
				return false;

			// blockquote外部へ移動
			if ($.browser.msie) {
				var id_name = "nc-wysiwyg-split";
				r.pasteHTML('<span id="' + id_name + '"></span>');
				var id_name_el = $('#nc-wysiwyg-split', this.editorDoc)[0];
				var clone_el = id_name_el.cloneNode(false);
				var new_text_nd = _cloneTextElement(id_name_el, clone_el, bq);
				if(!bq.nextSibling) {
					bq.parentNode.appendChild(new_text_nd);
				} else {
					bq.parentNode.insertBefore(new_text_nd, bq.nextSibling);
				}
		        var br_el = this.editorDoc.createElement("BR");
		        bq.parentNode.insertBefore(br_el, new_text_nd);
		        var br_el = this.editorDoc.createElement("BR");
		        bq.parentNode.insertBefore(br_el, new_text_nd);
		        //r = t.getRange();r.move("character", 2);
		        //r.select();
		        if($.browser.msie && parseInt($.browser.version) >= 9) {
			        setTimeout(function() {
			        	r = t.getRange();
			        	r.move("character", 2);
				        r.select();
					}, 100);
		        } else {
		        	r = t.getRange();
		        	r.move("character", 2);
		        	r.select();
		        }
		        $(id_name_el).remove();
			} else {
				var text_nd, stNode = s.anchorNode;
				var cpRange = r.cloneRange();

				if(stNode.nodeType == 3) {
					//text Node:テキスト分割
					text_nd = stNode.splitText(s.anchorOffset);
				} else {
					stNode = this.editorDoc.createTextNode("");
					cpRange.insertNode(stNode);
					text_nd = this.editorDoc.createTextNode("");
					cpRange.insertNode(text_nd);
				}

				if(bq) {
					var new_text_nd = _cloneTextElement(stNode, text_nd, bq);
					if(!bq.nextSibling) {
						bq.parentNode.appendChild(new_text_nd)
					} else {
						bq.parentNode.insertBefore(new_text_nd, bq.nextSibling)
					}
					//分割したblockquote_el次にbr挿入
					var br_el = this.editorDoc.createElement("BR");
					bq.parentNode.insertBefore(br_el, new_text_nd);
					r.setStartBefore(br_el);
					r.setEndBefore(br_el);
					this.setRange(r);
				}
			}
			$("blockquote", bq.parentNode).each(function(k, el){
				if(el.innerHTML == '' || el.innerHTML.toLowerCase() == '<span id=nc-wysiwyg-split></span>') {
					el.parentNode.removeChild(el);
				}
			});
			return true;
			function _cloneTextElement(node_el, text_el, bq) {
				while(node_el != bq)
				{
					var parent_el = node_el.parentNode;
					if(!parent_el) {
						return false;
					}
					var clone_el = parent_el.cloneNode(false);
					clone_el.appendChild(text_el);
					var next_el = node_el.nextSibling;
					while(next_el != null) {
						parent_el.removeChild(next_el);
						clone_el.appendChild(next_el);
						next_el = node_el.nextSibling;
					}
					node_el = parent_el;
					text_el = clone_el;
				}
				return text_el;
			};
		},


		// 選択範囲のブックマーク取得
		getBookmark : function(si) {
			var t = this, r = t.getRange(), tr, sx, sy, w = this.getWin(), e, sp, bp, le, c = -0xFFFFFF, s
			var sc_pos = this.getScrollDoc(), ro = t.editorDoc.body, wb = 0, wa = 0, nv;
			sx = sc_pos['left'];
			sy = sc_pos['top'];

			// Simple bookmark fast but not as persistent
			if (si)
				return {rng : r, scrollX : sx, scrollY : sy};

			// Handle IE
			if ($.browser.msie) {
				// Control selection
				if (r.item) {
					e = r.item(0);

					$.each(t.select(e.nodeName), function(i, n) {
						if (e == n) {
							sp = i;
							return false;
						}
					});

					return {
						tag : e.nodeName,
						index : sp,
						scrollX : sx,
						scrollY : sy
					};
				}

				// Text selection
				tr = t.editorDoc.body.createTextRange();
				tr.moveToElementText(ro);
				tr.collapse(true);
				bp = Math.abs(tr.move('character', c));

				tr = r.duplicate();
				tr.collapse(true);
				sp = Math.abs(tr.move('character', c));

				tr = r.duplicate();
				tr.collapse(false);
				le = Math.abs(tr.move('character', c)) - sp;
				return {
					start : sp - bp,
					length : le,
					scrollX : sx,
					scrollY : sy
				};
			}

			// Handle W3C
			e = t.getSelectNode();
			s = t.getSelection();

			if (!s)
				return null;

			// Image selection
			if (e && e.nodeName == 'IMG') {
				return {
					scrollX : sx,
					scrollY : sy
				};
			}

			// Text selection

			function getPos(r, sn, en) {
				var w = t.editorDoc.createTreeWalker(r, NodeFilter.SHOW_TEXT, null, false), n, p = 0, d = {};

				while ((n = w.nextNode()) != null) {
					if (n == sn)
						d.start = p;

					if (n == en) {
						d.end = p;
						return d;
					}

					p += (n.nodeValue || '').replace(/[\n\r]+/g, '').length;
				}

				return null;
			};

			// Caret or selection
			if (s.anchorNode && s.anchorNode == s.focusNode && s.anchorOffset == s.focusOffset) {
				if(s.focusNode.nodeName.toLowerCase() == "body")
					e = getPos(ro, s.anchorNode, s.anchorNode);
				else
					e = getPos(ro, s.anchorNode, s.focusNode);

				if (!e)
					return {scrollX : sx, scrollY : sy};

				// Count whitespace before
				(s.anchorNode.nodeValue || '').replace(/[\n\r]+/g, '').replace(/^\s+/, function(a) {wb = a.length;});

				return {
					start : Math.max(e.start + s.anchorOffset - wb, 0),
					end : Math.max(e.end + s.focusOffset - wb, 0),
					scrollX : sx,
					scrollY : sy,
					beg : s.anchorOffset - wb == 0
				};
			} else {
				if(r.endContainer.nodeName.toLowerCase() == "body")
					e = getPos(ro, r.startContainer, r.startContainer);
				else
					e = getPos(ro, r.startContainer, r.endContainer);
				// Count whitespace before start and end container
				//(r.startContainer.nodeValue || '').replace(/^\s+/, function(a) {wb = a.length;});
				//(r.endContainer.nodeValue || '').replace(/^\s+/, function(a) {wa = a.length;});

				if (!e)
					return {scrollX : sx, scrollY : sy};

				return {
					start : Math.max(e.start + r.startOffset - wb, 0),
					end : Math.max(e.end + r.endOffset - wa, 0),
					scrollX : sx,
					scrollY : sy,
					beg : r.startOffset - wb == 0
				};
			}
		},

		moveToBookmark : function(b) {
			var t = this, r = t.getRange(), s = t.getSelection(), ro = t.editorDoc.body, sd, nvl, nv;

			function getPos(r, sp, ep) {
				var w = t.editorDoc.createTreeWalker(r, NodeFilter.SHOW_TEXT, null, false), n, p = 0, d = {}, o, v, wa, wb;

				while ((n = w.nextNode()) != null) {
					wa = wb = 0;

					nv = n.nodeValue || '';
					//nv.replace(/^\s+[^\s]/, function(a) {wb = a.length - 1;});
					//nv.replace(/[^\s]\s+$/, function(a) {wa = a.length - 1;});

					nvl = nv.replace(/[\n\r]+/g, '').length;
					p += nvl;

					if (p >= sp && !d.startNode) {
						o = sp - (p - nvl);

						// Fix for odd quirk in FF
						if (b.beg && o >= nvl)
							continue;

						d.startNode = n;
						d.startOffset = o + wb;
					}

					if (p >= ep) {
						d.endNode = n;
						d.endOffset = ep - (p - nvl) + wb;
						return d;
					}
				}

				return null;
			};

			if (!b)
				return false;

			t.getWin().scrollTo(b.scrollX, b.scrollY);

			t.bookmark = b;

			// Handle explorer
			if ($.browser.msie) {
				// Handle simple
				if (r = b.rng) {
					try {
						r.select();
					} catch (ex) {
						// Ignore
					}

					return true;
				}

				t.focus(true);

				// Handle control bookmark
				if (b.tag) {
					r = ro.createControlRange();

					$.each(t.select(b.tag), function(i, n) {
						if (i == b.index)
							r.addElement(n);
					});
				} else {
					// Try/catch needed since this operation breaks when TinyMCE is placed in hidden divs/tabs
					try {
						// Incorrect bookmark
						if (b.start < 0)
							return true;

						r = s.createRange();
						r.moveToElementText(ro);
						r.collapse(true);
						r.moveStart('character', b.start);
						r.moveEnd('character', b.length);
					} catch (ex2) {
						return true;
					}
				}

				try {
					r.select();
				} catch (ex) {
					// Needed for some odd IE bug #1843306
				}

				return true;
			}

			// Handle W3C
			if (!s)
				return false;

			// Handle simple
			if (b.rng) {
				s.removeAllRanges();
				s.addRange(b.rng);
			} else {
				if (typeof(b.start) != 'undefined' && typeof(b.end) != 'undefined') {
					try {
						sd = getPos(ro, b.start, b.end);

						if (sd) {
							r = t.editorDoc.createRange();
							r.setStart(sd.startNode, sd.startOffset);
							r.setEnd(sd.endNode, sd.endOffset);
							s.removeAllRanges();
							s.addRange(r);
						}

						if (!$.browser.opera)
							t.focus();
					} catch (ex) {
						// Ignore
					}
				}
			}
		},

		/* 共通 */
		select : function(expr, scope) {
			return $.find(expr, scope || this.editorDoc.body || this.editorDoc || []);
		},

		getParent : function(node, f, r_node) {
			return this.getParents(node, f, r_node, false);
		},

		getScrollDoc : function(w, d) {
			var t = this, w = (w == undefined) ? this.getWin() : w;
			var d = (d == undefined) ? t.editorDoc : d;
			var sx = $(d.documentElement).scrollLeft() || $(d.body).scrollLeft() || w.pageXOffset || 0;
			var sy = $(d.documentElement).scrollTop() || $(d.body).scrollTop() || w.pageYOffset || 0;
			return {left : sx, top : sy};
		},

		/**
		 * Nodeから親の要素を求める
		 * @param n        : node object
		 * @param f        : function
		 * @param r        : node    object 親要素のルート
		 * @param c        : boolean 複数のelementを返すかどうか(trueの場合、返り値　array )。 default true
		 */
		getParents : function(n, f, r, c) {
			var t = this, na, o = [];

			c = c === undefined;

			r = r || this.editorDoc.body;

			while (n) {
				if (n == r || !n.nodeType || n.nodeType === 9)
					break;

				if (!f || f(n)) {
					if (c)
						o.push(n);
					else
						return n;
				}

				n = n.parentNode;
			}

			return c ? o : null;
		},

		run : function(e, f, s) {

			var t = this, o;

			if (!e)
				return false;

			s = s || this;
			if (!e.nodeType && (e.length || e.length === 0)) {
				o = [];

				$.each(e, function(i, e) {
					if (e) {
						if (typeof(e) == 'string')
							e = t.editorDoc.getElementById(e);

						o.push(f.call(s, e, i));
					}
				});

				return o;
			}

			return f.call(s, e);
		},

		applyInlineStyle : function(na, at, collapsed) {
			var t = this, bm, lo = {}, r_el, c;
			var buf_at = at;
			at = at || {};
			collapsed = collapsed || false;

			//na = na.toUpperCase();

			//if (op && op.check_classes && at['class'])
			//	op.check_classes.push(at['class']);

			function removeEmpty() {
				$.each(t.select(na).reverse(), function(k, n) {
					var c = 0;

					// Check if there is any attributes
					$.each(t.attrs(n), function(k, v) {
						if (k.substring(0, 1) != '_' && v != '' && v != null) {
							c++;
						}
					});

					// No attributes then remove the element and keep the children
					if (c == 0) {
						for (i = n.childNodes.length - 1; i >= 0; i--)
							$(n).after(n.childNodes[i]);
						$(n).remove();
					}
				});
			};

			function replaceFonts() {
				var bm, c_el, r_el;
				$.each(t.select('span,font,img'), function(k, el) {
					if (el.style.fontFamily == 'nc-wysiwygfont' || (el.face && el.face == 'nc-wysiwygfont') || (el.src && el.src.match(/nc-wysiwygurl$/))) {
						if (!bm)
							bm = t.getBookmark();

						if(collapsed == false)
							$(at).attr('data-nc-wysiwyg-ins', '1');
						if(na.match(/^</))
							c_el = $(na, t.editorDoc)[0];
						else
							c_el = t.editorDoc.createElement(na);
						if (!r_el)
							r_el = c_el;
						t.replace(t.attrs($(c_el), at)[0], el, 1);
					}
				});

				// 重複するelementの削除
				if(collapsed == false) {
					$.each(t.select(na + '[data-nc-wysiwyg-ins]'), function(k, n) {
						function removeStyle(n) {
							if (n.nodeType == 1 && at.style) {
								$.each(at.style, function(k, v) {
									$(n).css(k, '');
								});

								// Remove spans with the same class or marked classes
								//if (at['class'] && n.className && op) {
								//	each(op.check_classes, function(c) {
								//		if (dom.hasClass(n, c))
								//			dom.removeClass(n, c);
								//	});
								//}
							}
						};

						// Remove specified style information from child elements
						$.each(t.select(na, n), function(k , n) {removeStyle(n);});

						// Remove the specified style information on parent if current node is only child (IE)
						if (n.parentNode && n.parentNode.nodeType == 1 && n.parentNode.childNodes.length == 1)
							removeStyle(n.parentNode);

						// Remove the child elements style info if a parent already has it

						t.getParent(n.parentNode, function(pn) {
							if (pn.nodeType == 1) {
								if (at.style) {
									$.each(at.style, function(k, v) {
										var sv;

										if (!lo[k] && (sv = $(pn).css(k))) {
											if (sv === v)
												$(n).css(k, '');

											lo[k] = 1;
										}
									});
								}

								// Remove spans with the same class or marked classes
								//if (at['class'] && pn.className && op) {
								//	each(op.check_classes, function(c) {
								//		if (dom.hasClass(pn, c))
								//			dom.removeClass(n, c);
								//	});
								//}
							}

							return false;
						});

						n.removeAttribute('data-nc-wysiwyg-ins');
					});
					if(buf_at != undefined && buf_at != null)
						removeEmpty();
				}
				t.moveToBookmark(bm);
				return r_el;
			};

			// Create inline elements
			t.focus();
			if(collapsed)
				t.editorDoc.execCommand('insertImage', false, 'nc-wysiwygurl');
			else
				t.editorDoc.execCommand('fontName', false, 'nc-wysiwygfont');
			r_el = replaceFonts();
			if(t._keyhandler) {
				$(this.editorDoc).unbind("keyup", t._keyhandler);
				$(this.editorDoc).unbind("keypress", t._keyhandler);
				$(this.editorDoc).unbind("keydown", t._keyhandler);
			}
			// nodechange
			if(r_el) {
				t.checkTargets(r_el);
				t._pendingStyles = 0;

			} else {
				// mozillaでなにも選択せずにサイズを指定後、文字をかくと、サイズ指定にならず、fontタグが表示されてしまうため
				// Start collecting styles
				t._pendingStyles = $.extend(t._pendingStyles || {}, at.style);

				t._keyhandler = function(e) {
					// Use pending styles
					if (t._pendingStyles) {
						at.style = t._pendingStyles;
						delete t._pendingStyles;
					}

					if (replaceFonts()) {
						$(t.editorDoc).unbind("keydown", t._keyhandler);
						$(t.editorDoc).unbind("keypress", t._keyhandler);
					}

					if (e.type == 'keyup') {
						$(t.editorDoc).unbind("keyup", t._keyhandler);
						if($.browser.mozilla)
							t.nc_undoManager.index = t.nc_undoManager.index - 2;
						else
							t.nc_undoManager.index = t.nc_undoManager.index - 1;
						t.addUndo();
					}
					t.checkTargets();
				};
				$(t.editorDoc).keydown(t._keyhandler);
				$(t.editorDoc).keypress(t._keyhandler);
				$(t.editorDoc).keyup(t._keyhandler);
			}
			return r_el;
		},

		/**
		 * Nodeを別Nodeにreplaceする
		 * @param node     : new node object
		 * @param o        : replace node object or array replace node
		 * @param k        : boolean      子供をコピーするかどうか
		 */
		replace : function(node, o, k) {
			var self = this;
			var ret, n = node;

			if (typeof(o) == 'array')
				node = node.cloneNode(true);

			ret = this.run(o, function(o) {
				if (k) {
					$.each(o.childNodes, function(k, c) {
						node.appendChild(c.cloneNode(true));
					});
				}

				// Fix IE psuedo leak for elements since replacing elements if fairly common
				// Will break parentNode for some unknown reason
				if (o.nodeType === 1) {
					o.parentNode.insertBefore(node, o);
					$(o).remove();
					return node;
				}

				return o.parentNode.replaceChild(node, o);
			});
			return ret;
		},

		//
		// attrの複数版
		//
		attrs : function(el, at) {
			var attrs, len, ret = true, i;

			if(at === undefined) {
				ret = {};
				attrs = el.attributes;
				for (var i = 0; i < attrs.length; ++i) {
					a = attrs.item(i);
					name = this.getNodeName(a);
					value = this.getNodeValue(el, a);
					ret[name] = value;
				}

				return ret;
			} else
				len = at.length;

			var len = at.length, ret = true;
			obj = $(el);
			if (typeof(at) == 'string')
				return obj.attr(at);

			if(len === undefined) {
				// hash
				for (var key in at ) {
					if (typeof(at[key]) == 'string')
						obj.attr(key, at[key]);
					else {
						for ( skey in at[key] )
							obj.css(skey, at[key][skey]);
					}
				}
				return el;
			} else {
				// array
				ret = [];
				for ( ; i < len; )
					ret.push(obj.attr(i));
			}

			return ret;
		},
		getNodeName: function(a) {
        	return a.nodeName.toLowerCase();
        },
        getNodeValue: function(node, a) {
        	var name, value ="";
        	try {
	        	name = this.getNodeName(a);
	        	if (name != "style") {
					// ブラウザによっては、height等の属性は、本来入力していないものを自動的に指定されてしまう可能性があるため、
					// a.nodeValueを用いる
					if (typeof node[a.nodeName] != "undefined" && name != "height"  && name != "width"  && name != "href" && name != "src" && !/^on/.test(name)) {
						value = node[a.nodeName];
					} else {
						if((name == "href" || name == "src") &&
							 $.browser.msie && parseInt($.browser.version) < 7) {
							// IE6では、URIEncodeしたURLを指定すると文字化けしてしまうため
							value = $(node).attr(name);
						} else
							value = a.nodeValue;
					}
				} else {
					value = node.style.cssText;
				}
			} catch(e){}
			return value;
        },


/**
 * リストメニュー作成
 * @param node          element  追加する親element
 * @param list          hash     リストのキーとvalueをhash配列で指定。valueはhtmlでも可
 * @param className     string   リストメニューとプルダウンした箇所に追加するclassName
 *                           CSS側でa.className div.classNameとして定義を分けることが可
 * @param callback      function callback function　メニューを選択時
 * @param args          array    callback function args デフォルト  event,リストのkey,value
 *                           argsをセットした場合、デフォルト値にpushされる
 * @param node_exists   boolean すでにnodeがある場合にtrueセット
 * @param node_event   boolean すでにnodeに表示非表示のイベントがある場合にtrueセット
 * @return object list element
 */
    	appendListMain : function (node, list, className, callback, args, node_exists, node_event) {
    		var t = this, name=null, li, a, a_flag = false;
    		var umenu = $('<ul></ul>').addClass("listmenu-outer");
    		var listmenu = $('<div></div>').addClass("listmenu").append(umenu).css("zIndex", $.Common.zIndex++);
    		node_exists = (node_exists == undefined) ? false : true;
    		node_event = (node_event == undefined) ? false : true;
    		if(!node_exists) {
    			a = $('<a href="javascript:;"></a>');
    			a_flag = true;
    		} else {
    			a = node;
    		}
    		if(className) listmenu.addClass(className);
    		for ( var k in list ) {
    			if (name === null && a_flag == true) name = list[k];
    			else if(list[k] != "") {
    				var a_menu = $('<a href="javascript:;"></a>').attr("title", k).attr("name", k).html(list[k]);
    				t.registListContentEvent(a_menu, callback, args);
    				li = $('<li></li>').append(
    						a_menu
    				).appendTo( umenu );
    			}
    		}

    		if(a_flag == true) {
    			a.addClass("listbox").append(
    	              	$('<span></span>').addClass("listbtn")
    	              ).append(
    	              	$('<span></span>').addClass("listcontent").attr("title", name).html(name)
    	              );
    		}
    		if(!node_event)
    			t.registListEvent(a, callback, args);
            if(className) a.addClass(className);
            if(a_flag == true) {node.append(a);}
            a.after(listmenu);
            return a;
    	},
    	registListContentEvent : function(a, callback, args) {
    		a.click(function(e) {
    			var d = $(this).parents(".listmenu:first");
    			var name = $(this).attr("name");
    			if(typeof name == "undefined") {
    				name = "";
    			}
    			var set_args = [e, name, this.innerHTML];
    			for(var j in args) {
    				set_args.push(args[j]);
    			}
    			$(".listcontent:first", d.prev()).html(this.innerHTML);
    			if(callback)
    				callback.apply(a, set_args);
    			d.hide();
    			//d.prev().focus();
    		});
    	},
    	registListEvent : function(a, callback, args) {
    		a.click(function(e) {
                 	// 表示非表示切替
                 	var ret = true;
                 	var self = this, listcontent = $(".listcontent", this);
                 	var html = listcontent.html();
                 	listcontent.html(listcontent.attr("title"));
                  	if(callback && $(this).next().css("display") == "block") {
                  		var name = $(self).attr("name");
            			if(typeof name == "undefined") {
            				name = "";
            			}
            			var set_args = [e, name, this.innerHTML];
    					for(var j in args) {
    						set_args.push(args[j]);
    					}
    					ret = callback.apply($(self), set_args);
                  	}
                  	if(ret) {
    	            	if($(this).next().css("display") == "block") {
    		            	$("a", $(this).next()).each(function(k, v){
    		            		if($(v).html() == html)
    		            			$(v).addClass("active");
    		            		else
    		            			$(v).removeClass("active");
    		            	});
    	            	}
    	            	$(this).next().toggle();
    	            }
                	e.preventDefault();
      		        return false;
            });
    	},
/**
 * リストメニューをjavascriptから選択メソッド
 * @param listbox       element  listbox element
 * @param name          string   リストのキー名称
 * @param callback_flag boolean  変更時にcallbackを実行するかどうか
 */
    	chgList : function(listbox, name, callback_flag) {
    		var a = $("a[name='" + name + "']", listbox.next());
    		var listcontent = $(".listcontent", listbox);
    		if(name == "") {
    			listcontent.html(listcontent.attr("title"));
    			return;
    		}
    		if(!a || !a[0])
    			return false;
    		if(callback_flag)
    			a.click();
    		else
    			listcontent.html(a.html());
    		return true;
    	},

/**
 * リストメニューの選択しているキー取得
 * @param listbox       element  listbox element
 * @return string
 */
		getList : function(listbox) {
			var ret= '', value = $(".listcontent", listbox).html();
			$("a", listbox.next()).each(function(k, v) {
				if(value == v.innerHTML) ret = $(v).attr("name");
			});
			return ret;
		}
	});

	/**
     * Undo Redo用
     * @param nc_wysiwyg
     */
	function nc_undoManager(nc_wysiwyg)
    {
        return this instanceof nc_undoManager
            ? this.init(nc_wysiwyg)
            : new nc_undoManager(nc_wysiwyg);
    }

    $.extend(nc_undoManager.prototype,
    {
    	nc_wysiwyg : null,
    	data       : null,
    	index      : 0,
    	bm         : null,

    	init : function(nc_wysiwyg)
        {
        	this.nc_wysiwyg = nc_wysiwyg;
        	this.data = [];
        	this.bm = [];
        	this.index = 0;
        },

        add : function(init_flag) {
        	var t = this, i, ed = t.nc_wysiwyg, content, re, la;

        	content = ed.getContent();
        	la = t.data[t.index > 0 && t.index == t.data.length ? t.index - 1 : t.index];

        	// br delete
        	var re = new RegExp(/(<br[ ]*\/*>\s*)+$/i);
            content = content.replace(re, '') + "<br />";

        	if(la == content)
        		return null;

        	if (t.data.length > ed.options.undo_level) {
				for (i = 0; i < t.data.length - 1; i++) {
					t.data[i] = t.data[i + 1];
					if($.browser.mozilla)
						t.bm[i] = ed.getBookmark();
				}

				t.data.length--;
				t.index = t.data.length;
			}

			if (t.index < t.data.length)
				t.index++;

			if (t.data.length === 0 && !init_flag)
				return null;

			// Add level
			t.data.length = t.index + 1;
			t.data[t.index] = content;
			if($.browser.mozilla)
				t.bm[t.index] = ed.getBookmark();
			t.index++

			if (init_flag)
				t.index = 0;

        	return content;
        },

        undo : function() {
        	var t = this, ed = t.nc_wysiwyg, content = null, i;

        	if (t.index > 0) {
				t.add();
        		// If undo on last index then take snapshot
				if (t.index == t.data.length && t.index > 1) {
					i = t.index;
					//t.typing = 0;

					if (!t.add())
						t.index = i;

					--t.index;
				}
        		content = t.data[--t.index];
        		ed.setContent(content);
        		if($.browser.mozilla)
	        		ed.moveToBookmark(t.bm[t.index]);
        	}

        	return content;
        },

        redo : function() {
			var t = this, ed = t.nc_wysiwyg, content = null;

			if (t.index < t.data.length - 1) {
				content = t.data[++t.index];
				ed.setContent(content);
				if($.browser.mozilla)
					ed.moveToBookmark(t.bm[t.index]);
			}

			return content;
		},

		clear : function() {
			var t = this;
			t.data = [];
			t.bm   = [];
			t.index = 0;
		}
	});
})(jQuery);