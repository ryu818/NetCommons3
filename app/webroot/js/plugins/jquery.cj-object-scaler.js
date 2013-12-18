/* ****************************************************************************

	CJ Object Scaler jQuery Plug-In v3.0.0

	Copyright (c) 2011, Doug Jones. All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	Redistributions of source code must retain the above copyright notice, this
	list of conditions and the following disclaimer. Redistributions in binary
	form must reproduce the above copyright notice, this list of conditions and
	the following disclaimer in the documentation and/or other materials
	provided with the distribution. Neither the name BernieCode nor
	the names of its contributors may be used to endorse or promote products
	derived from this software without specific prior written permission.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE FOR
	ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
	SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
	CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
	LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
	OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
	DAMAGE.

	For more information please visit www.cjboco.com.

	Example:

	$("#myImage").cjObjectScaler({
		destElem: $("#myParent"),	// option parent node. If not passed, it will use it's immediate parent
		method: "fit",					// fit | fill (default)
		fade: 500						// 0 no fade, positive integer fade duration
	}, function() {
		// custom callback function called when scaling is complete...
	});

	CHANGELOG

		v1.0	09/10/08 - Initial Release
		v2.0	09/22/09 - Coverted it to a jQuery plug-in
		v2.0.1	10/14/09 - Fixed a bug where the scaling function
								wasn't being triggered, do to the
								image already being loaded.
								(Discovered by Ben Visser)
		v2.1	05/13/10 - Added a new image onLoad check.
		v2.1.1	05/14/10 - The border width check was failing in IE
								Big thanks to Funger for discovering this bug.
		v2.1.2 	06/10/10 - It was recommended to add a callback function,
								which is called once the scaling is complete
								*Credit goes to Chris Bellew <Chris.Bellew@luxus.fi>
								for this. Thanks Chris!
		v3.0.0 	01/31/11 - Updated the imagesLoaded plug-in. The previous
								version was causing a page request by
								using this.src = "#".
							Used new jQuery template structure.


**************************************************************************** */

/*
 $('img.photo',this).imagesLoaded(myFunction)
 execute a callback when all images have loaded.
 needed because .load() doesn't work on cached images

 mit license. paul irish. 2010. (https://gist.github.com/268257)
 webkit fix from Oren Solomianik. thx!

 callback function is passed the last image to load
 as an argument, and the collection as `this`
*/
(function ($) {
	$.fn.imagesLoaded = function (callback) {
		var elems = this.filter('img'),
			len = elems.length;

		elems.bind('load', function () {
			if (--len <= 0) {
				callback.call(elems, this);
			}
		}).each(function () {
			// cached images don't fire load sometimes, so we reset src.
			if (this.complete || this.complete === undefined) {
				var src = this.src;
				// webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
				// data uri bypasses webkit log warning (thx doug jones)
				this.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
				this.src = src;
			}
		});
		return this;
	};
}(jQuery));

/*
 CJ Object Scaler
*/
(function ($) {
	$.fn.extend({

		cjObjectScaler: function (opts, callback) {

			var methods = {

				scaleObj: function ($obj) {

					// declare some local variables
					var data = $obj.data('cj'),
						o = data.options,
						destW = $(o.destElem).width(),
						destH = $(o.destElem).height(),
						ratioX, ratioY, scale, newWidth, newHeight,
						borderW = parseInt($obj.css("borderLeftWidth"), 10) + parseInt($obj.css("borderRightWidth"), 10),
						borderH = parseInt($obj.css("borderTopWidth"), 10) + parseInt($obj.css("borderBottomWidth"), 10);

					// check for valid border values. IE takes in account border size when calculating width/height so just set to 0
					borderW = isNaN(borderW) ? 0 : borderW;
					borderH = isNaN(borderH) ? 0 : borderH;

					// calculate scale ratios
					ratioX = destW / $obj.width();
					ratioY = destH / $obj.height();

					// Determine which algorithm to use
// Modify for NetCommons Extentions By Ryuji.M --START
					//if (!$obj.hasClass("cf_image_scaler_fill") && ($obj.hasClass("cf_image_scaler_fit") || o.method === "fit")) {
					if(o.expand == false && ratioX > 1 && ratioY > 1) {
						scale = 1;
					} else if (!$obj.hasClass("cf_image_scaler_fill") && ($obj.hasClass("cf_image_scaler_fit") || o.method === "fit")) {
// Modify for NetCommons Extentions By Ryuji.M --E N D
						scale = ratioX < ratioY ? ratioX : ratioY;
					} else if (!$obj.hasClass("cf_image_scaler_fit") && ($obj.hasClass("cf_image_scaler_fill") || o.method === "fill")) {
						scale = ratioX > ratioY ? ratioX : ratioY;
					}

					// calculate our new image dimensions
					newWidth = parseInt($obj.width() * scale, 10) - borderW;
					newHeight = parseInt($obj.height() * scale, 10) - borderH;

					// Set new dimensions & offset
					$obj.css({
						"width": newWidth + "px",
						"height": newHeight + "px",
						"position": "absolute",
						"top": (parseInt((destH - newHeight) / 2, 10) - parseInt(borderH / 2, 10)) + "px",
						"left": (parseInt((destW - newWidth) / 2, 10) - parseInt(borderW / 2, 10)) + "px"
					}).attr({
						"width": newWidth,
						"height": newHeight
					});

					// do our fancy fade in, if user supplied a fade amount
					if (o.fade > 0) {
						$obj.fadeIn(o.fade);
					}

					// call the user supplied callback function, if one was provided
					if (typeof callback === "function") {
						callback();
					}
				}

			};

			if (typeof opts === "object" || !opts) {

				// call to initialize
				return this.each(function () {

					var $obj = $(this),
						data = $obj.data('cj'),
						o;

					// store our options in our object.
					if (!data) {
						$obj.data('cj', {
							options: {
								method: "fill",
								destElem: null,
								fade: 0
// Add for NetCommons Extentions By Ryuji.M --START
								,expand: false
// Add for NetCommons Extentions By Ryuji.M --E N D
							}
						});
						data = $obj.data('cj');
					}

					// add any user defined options, if they were passed
					if (opts) {
						data.options = $.extend(data.options, opts);
					}

					// simplify our data variables
					o = data.options;

					// if they don't provide a destObject, use parent
					if (o.destElem === null) {

						o.destElem = $obj.parent();
					}

					// need to make sure the user set the parent's position. Things go bonker's if not set.
					// valid values: absolute|relative|fixed
					if ($(o.destElem).css("position") === "static") {
						$(o.destElem).css({
							"position": "relative"
						});
					}

					// if our object to scale is an image, we need to make sure it's loaded before we continue.
					if (typeof $obj === "object" && typeof o.destElem === "object" && typeof o.method === "string") {

						// if the user supplied a fade amount, hide our image
						if (o.fade > 0) {
							$obj.hide();
						}

						if ($obj.get(0).nodeName === "IMG") {

							// to fix the weird width/height caching issue we set the image dimensions to be auto;
							$obj.width("auto");
							$obj.height("auto");

							// wait until the image is loaded before scaling
							$obj.imagesLoaded(function () {
								methods.scaleObj($obj);
							});

						} else {

							// scale immediately
							methods.scaleObj($obj);
						}

					} else {

						// wonky parameters were not passed
						$.error("CJ Object Scaler could not initialize. Bad parameters.");

					}

				});

			// unknown call to our plugin
			} else {

				$.error('Method/Option ' + opts + ' does not exist.');

			}
		}
	});
}(jQuery));