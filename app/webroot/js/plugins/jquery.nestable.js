/*!
 * Nestable jQuery Plugin - Copyright (c) 2012 David Bushell - http://dbushell.com/
 * Dual-licensed under the BSD or MIT licenses
 */
/**
 * IE8以降ならびに、移動のロジックを全面的に見直し。
 * draggableのUIを用いるように修正。(touchイベントには非対応)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       webroot.js.main
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 */
;(function($, window, document, undefined)
{
    var defaults = {
            listNodeName      : 'ol',
            itemNodeName      : 'li',
            listClass         : 'dd-list',
            itemClass         : 'dd-item',
            handleClass       : 'dd-handle:first',
            collapsedClass    : 'dd-collapsed',
            contentClass      : 'dd-handle',			// Add Ryuji.M
            singleHandleClass : '',						// Add Ryuji.M
            active            : '',	    				// Add Ryuji.M
            activeItemClass   : 'dd-active',	    	// Add Ryuji.M
            placeClass        : 'dd-placeholder',		// Add Ryuji.M
            placeUpClass      : 'dd-placeholder-up',	// Add Ryuji.M
            placeDownClass    : 'dd-placeholder-down',	// Add Ryuji.M
            containment       : null,					// Add Ryuji.M
            isInner           : true,					// Add Ryuji.M
            emptyClass        : 'dd-empty',
            expandBtnHTML     : '<button title="' + __d('pages', 'Open') + '" data-action="expand" type="button">Expand</button>',
            collapseBtnHTML   : '<button title="' + __d('pages', 'Close') + '"data-action="collapse" type="button">Collapse</button>',
            group             : ''
        };

    function Plugin(element, options)
    {
        this.w  = $(window);
        this.el = $(element);
        this.options = $.extend({}, defaults, options);
// Edit Start Ryuji.M IE8でAjaxを用いて画面を表示した場合、CSSの描画が終わった後にjavascriptが実行されずにCSSが適用されなくなる場合があるため修正。
		if ( !window.DOMParser ) {
        	var t = this;
        	setTimeout(function(){
        		t.init();
        	}, 0);
        } else {
        	this.init();
        }
        this.ret = null;
        //this.init();
// Edit End Ryuji.M
    }

    Plugin.prototype = {

        init: function()
        {
            var list = this;

            list.reset();

            list.placeEl = $('<div><div class="nc-arrow-up"></div></div>');
            $.Common.zIndex++;

            $.each(this.el.find(list.options.itemNodeName), function(k, el) {
                list.setParent($(el));
                list.addEvent($(el));
            });

            list.el.on('click', 'button', function(e) {
                var target = $(e.currentTarget),
                    action = target.data('action'),
                    item   = target.parent(list.options.itemNodeName);
                if (action === 'collapse') {
                    list.collapseItem(item);
                }
                if (action === 'expand') {
                    list.expandItem(item);
                }
            });
        },

        addEvent: function(el, active) {
        	var list = this;
        	if(typeof active != 'undefined'){
        		list.options.active = active;
        	}

        	list.setParent($(el));
            var handle = $('.' + list.options.handleClass, el);

            if(handle.get(0)) {
            	$(el).draggable({
					opacity  : 0.5,    //ドラッグ時の不透明度
					//distance:3,
					handle: handle,
					scroll:true,
					containment:list.options.containment,
					revert: true,
					//helper : 'clone',
					start : function(e, ui){
						list.dragStart(e, ui);
					},
					drag : function(e, ui){
						var o = $(this).data('draggable').options;
						list.dragMove(e, ui, o);
					},
					stop : function(e, ui){
						var o = $(this).data('draggable').options;
						if(!o.revert) {
							list.dragStop(e);
						}
					},
					zIndex:++$.Common.zIndex
				});
			}
        },
/*
        serialize: function()
        {
            var data,
                depth = 0,
                list  = this;
                step  = function(level, depth)
                {
                    var array = [ ],
                        items = level.children(list.options.itemNodeName);
                    items.each(function()
                    {
                        var li   = $(this),
                            item = $.extend({}, li.data()),
                            sub  = li.children(list.options.listNodeName);
                        if (sub.length) {
                            item.children = step(sub, depth + 1);
                        }
                        array.push(item);
                    });
                    return array;
                };
            data = step(list.el.find(list.options.listNodeName).first(), depth);
            return data;
        },

        serialise: function()
        {
            return this.serialize();
        },
*/
        reset: function()
        {
            this.dragEl     = null;
            this.dropEl       = null;
            this.dragPosition = null;
        },

        expandItem: function(li)
        {
            li.removeClass(this.options.collapsedClass);
            li.children('[data-action="expand"]').hide();
            li.children('[data-action="collapse"]').show();
            li.children(this.options.listNodeName).show();
        },

        collapseItem: function(li)
        {
            var lists = li.children(this.options.listNodeName);
            if (lists.length) {
                li.addClass(this.options.collapsedClass);
                li.children('[data-action="collapse"]').hide();
                li.children('[data-action="expand"]').show();
                li.children(this.options.listNodeName).hide();
            }
        },

        expandAll: function()
        {
            var list = this;
            list.el.find(list.options.itemNodeName).each(function() {
                list.expandItem($(this));
            });
        },
// highlight_selectorが指定されていれば、activeなItemがある個所以外を折りたたむ。
        collapseAll: function(highlight_selector)
        {
            var list = this;
            list.el.find(list.options.itemNodeName).each(function() {
            	if(highlight_selector) {
            		var select = $(highlight_selector, $(this));
            		if(!select.get(0)) {
            			list.collapseItem($(this));
            		}
            	} else {
            		list.collapseItem($(this));
            	}
            	// list.collapseItem($(this));
            });
        },

        setParent: function(li, chg_active)
        {
        	var id = li.data("id");
        	var childs = li.children(':not('+ this.options.listNodeName + ')');
        	var child = $('.'+ this.options.contentClass, childs);
        	if(!child.get(0) && childs.hasClass(this.options.contentClass)) {
        		child = childs;
        	}
        	//var child = li.children('.'+ this.options.contentClass);
        	//var child = $('.'+ this.options.contentClass, li);
        	chg_active = (typeof chg_active == 'undefined') ? true : chg_active;
        	if(chg_active) {
	        	if(this.options.active == id) {
	        		child.addClass(this.options.activeItemClass);
	        	} else {
	        		child.removeClass(this.options.activeItemClass);
	        	}
        	}

            if (li.children(this.options.listNodeName).length) {
            	if(li.children('[data-action="expand"]').length == 0) {
					li.prepend($(this.options.expandBtnHTML));
					li.prepend($(this.options.collapseBtnHTML));
				}

				if (this.options.singleHandleClass)
                	child.removeClass(this.options.singleHandleClass);

            } else {
            	li.children('[data-action="expand"]').remove();
            	li.children('[data-action="collapse"]').remove();
            	if(this.options.singleHandleClass) {
            		child.addClass(this.options.singleHandleClass);
            	}
            }
            li.children('[data-action="expand"]').hide();
        },

        unsetParent: function(li)
        {
            li.removeClass(this.options.collapsedClass);
            li.children('[data-action]').remove();
            li.children(this.options.listNodeName).remove();
        },

        dragStart: function(e, ui)
        {
        	//var list = this;
        	var dragItem = $(e.target);
        	this.dragEl = dragItem;

        	//var offset = dragItem.position();
        	var offset = dragItem.offset();
        	this.placeEl.css({
        		'position' : 'absolute',
        		'z-index' : dragItem.css('zIndex') - 1
        	});
        	$(document.body).append(this.placeEl);
        	//dragItem.before(this.placeEl);


            var offset = this.placeEl.position();
            var content = $('.'+ this.options.contentClass + ':first', dragItem);
            //content.css("margin" , 0);

        },

        dragStop: function(e)
        {
        	var t = this;
        	t.ret = null;
        	$('.' + t.options.placeClass, this.el).removeClass(t.options.placeClass);
        	t.placeEl.remove();
        	var ret = $.Common.fireResult('change',[t.dragEl.data('id'), t.dropEl.data('id'), t.dragPosition], t.el);
        	if(ret === null) {
        		// wait
        		var timer = setInterval(function(){
        			if(t.ret !== null) {
        				clearInterval(timer);
        				if(t.ret === true) {
			        		t.success();
			        	} else if(t.ret === false) {
			        		t.error();
			        	}
        			}
        		},500);
        	} else if(ret === true) {
        		t.success();
        	} else if(ret === false) {
        		t.error();
        	}

        	return ret;
        },

        dragMove: function(e, ui, o)
        {
        	var parent, t = this, child, offset, h,
        		opt   = this.options,
        		dragItem = $(e.target),
        		content = $('.'+ this.options.contentClass + ':first', dragItem);
        	o.revert = true;

        	$.each(this.el.find('.'+ opt.contentClass), function(k, el) {
        		offset = $(el).offset();
        		if(content.get(0) != el && $.Common.within($(el), e.pageX, e.pageY)) {
        			parent = $(el).parents(opt.itemNodeName+':first');
        			var sequence = parent.data("dd-sequence");
        			var group = t.dragEl.data("dd-group");
        			if(group) {
        				if(group != parent.data("dd-group")) {
        					return;
        				} else {
        					var group_sequence = parent.data("dd-group-sequence");
        					if(group_sequence) {
        						sequence = group_sequence;
        					}
        				}
        			}
					h = $(el).outerHeight();
        			if (!opt.isInner || sequence == 'top-bottom-only') {
                    	if(offset.top + h/2 >= e.pageY) {
							insert_pos = "top";
						} else {
							insert_pos = "bottom";
						}
					} else if(sequence == 'inner-only') {
						insert_pos = "inner";
					} else if(sequence == 'top-only') {
						insert_pos = "top";
					} else if(sequence == 'bottom-only') {
						insert_pos = "bottom";
                    } else {
			        	if(offset.top + h/3 >= e.pageY) {
							insert_pos = "top";
						} else if(offset.top + (h/3)*2 >= e.pageY) {
							insert_pos = "inner";
						} else {
							insert_pos = "bottom";
						}
					}

					if(insert_pos == "inner") {
						t.placeEl.css({
			        		'display' : 'none'
			        	});
			        	$(el).addClass(t.options.placeClass);
					} else {
						$(el).removeClass(t.options.placeClass);
        				child = t.placeEl.children(':first');
			        	if(insert_pos == "top") {
			        		t.placeEl.addClass(t.options.placeUpClass);
			        		t.placeEl.removeClass(t.options.placeDownClass);
			        		if(child.hasClass('nc-arrow')) {
				        		child.addClass('nc-arrow-up');
				        		child.removeClass('nc-arrow');
				        	}
			        		t.placeEl.css({
				        		'top' : offset['top']  - t.placeEl.outerHeight() + 'px'
				        	});
			        	} else {
			        		t.placeEl.removeClass(t.options.placeUpClass);
			        		t.placeEl.addClass(t.options.placeDownClass);
			        		if(child.hasClass('nc-arrow-up')) {
				        		child.removeClass('nc-arrow-up');
				        		child.addClass('nc-arrow');
				        	}
			        		t.placeEl.css({
				        		'top' : offset['top'] + h + 'px'
				        	});
			        	}
			        	t.placeEl.css({
			        		'width' : parent.width() + 'px',
			        		'left' : offset['left'] + 'px',
			        		'display' : 'block'
			        	});
		        	}
		        	t.dropEl = parent;
		        	t.dragPosition = insert_pos;
		        	o.revert = false;
        		}
        	});

        	if(o.revert) {
        		$('.' + t.options.placeClass, this.el).removeClass(t.options.placeClass);
        		t.placeEl.css({
			        'display' : 'none'
				});
        	}
        },

        success: function()
        {
        	var t = this, el;
        	var parent = t.dragEl.parents(t.options.itemNodeName + ':first');
        	t.appendList(t.dropEl, t.dragEl, t.dragPosition, false);

            // キャンセル
            t.dragEl.attr( 'style', 'position: relative;' );
            el = parent.children(t.options.listNodeName).first();
            if(el.get(0) && el.children(t.options.itemNodeName).length == 0) {
            	el.remove();
            }
            t.setParent(parent, false);
			t.reset();
        },

        appendList: function(li, new_li, position, add_event)
        {
        	var t = this, el, ol;
        	add_event = (add_event == undefined) ? true : add_event;

        	if(li.get(0).tagName.toLowerCase() == t.options.listNodeName) {
        		// liではなくolが指定されていれば、append
        		li.append(new_li);
        	} else if(position == 'inner') {
            	el = li.children(t.options.listNodeName).first();
            	t.expandItem(li);
            	if(el.get(0)) {
            		el.append(new_li);
            	} else {
            		ol = $(document.createElement(this.options.listNodeName)).addClass(this.options.listClass).appendTo(li);
            		ol.append(new_li);
            		t.setParent(li, false);
            	}
            } else if(position == 'top') {
            	li.before(new_li);
            } else if(position == 'bottom') {
            	li.after(new_li);
            }
			if(add_event) {
				t.addEvent(new_li);
			}
        },

// Dropした際にダイアログ等でcancelするかどうか確認する場合に、
// changeイベントでresutn nullをすることで遅延を発生させ、
// setStopでbooeleanをセットすることで、Drop処理かcancel処理を行う。
        setStop: function(ret)
        {
			var t = this;
			t.ret = ret;
        },

        error: function()
        {
        	var t = this;
            // キャンセル
            t.dragEl.attr( 'style', 'position: relative;' );
            t.reset();
        }
    };

    $.fn.nestable = function(params)
    {
        var lists  = this,
            retval = this,
        arg = arguments;

        lists.each(function()
        {
            var plugin = $(this).data("nestable");

            if (!plugin) {
                $(this).data("nestable", new Plugin(this, params));
                $(this).data("nestable-id", new Date().getTime());
            } else {
                if (typeof params === 'string' && typeof plugin[params] === 'function') {
                	var r = [];
					if(arg[1].length >= 1) {
						retval = plugin[params].apply(plugin, arg[1]);
					} else {
                    	retval = plugin[params]();
					}
                }
            }
        });

        return retval || lists;
    };

})(window.jQuery || window.Zepto, window, document);
