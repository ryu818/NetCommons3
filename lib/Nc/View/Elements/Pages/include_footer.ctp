<?php
/**
 * フッターー出力
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       View.Elements
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<script>
	$(function () {
		$(document).on("submit", "form[data-pjax],form[data-ajax]", function (e) {
			$.Common.ajax(e, $(this));
		});
		$(document).on("click", "a[data-ajax],a[data-pjax],input[data-ajax],input[data-pjax],button[data-ajax],button[data-pjax]", function (e) {
			$.Common.ajax(e, $(this));
		});
		if($.support.pjax) {
			$(window).bind('popstate', function(e) {
				if(!$(document.body).attr('data-onload')) {
					return;
				}
				$.Common.onPjaxPopstate(e);
			});
			/* Chromeでonload時にもpopstateイベントが実行されるため、実行させないように対処 */
			setTimeout(function() {
				$(document.body).attr('data-onload', true);
			}, 0);
		};

		var options = {items: '.nc-tooltip', track: true};
		$( document ).tooltip(options);
	});
</script>
<?php
	echo "\n". $this->fetch('script');
?>
