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
	$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	$ncMode = intval($this->Session->read(NC_SYSTEM_KEY.'.'.'mode'));

	$common_js = array('plugins/chosen.jquery.js');
	if($this->params['controller'] == 'pages') {
		$common_js[] = 'pages/common/';
	}
	if($ncMode == NC_BLOCK_MODE) {
		$common_js[] = 'pages/block/';
		//$common_js[] = 'pages/style/';
	}
	if ($nc_user != '0') {
		$common_js[] = 'pages/main/';
	}
	echo "\n".$this->Html->script($common_js, array('inline' => true));
?>
<script>
	$(function () {
		$(document).on("submit", "form[data-pjax],form[data-ajax],form[data-ajax-inner]", function (e) {
			$.Common.ajax(e, $(this));
		});
		$(document).on("click", "a[data-ajax],a[data-ajax-inner],a[data-pjax],input[data-ajax],input[data-ajax-inner],input[data-pjax],button[data-ajax],button[data-ajax-inner],button[data-pjax]", function (e) {
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
