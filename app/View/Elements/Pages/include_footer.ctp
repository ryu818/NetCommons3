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
	$nc_mode = intval($this->Session->read(NC_SYSTEM_KEY.'.'.'mode'));

	$common_js = array('plugins/jquery.pjax.js', 'plugins/chosen.jquery.js');
	if($this->params['controller'] == 'pages') {
		$common_js[] = 'pages/common/';
	}
	if($nc_mode == NC_BLOCK_MODE) {
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
		$(document).on("submit", "form[data-pjax],form[data-ajax],form[data-ajax-replace]", function (e) {
			$.Common.ajax(e, $(this));
		});
		$(document).on("click", "a[data-ajax],a[data-ajax-replace],a[data-pjax],input[data-ajax],input[data-ajax-replace],input[data-pjax],button[data-ajax],button[data-ajax-replace],button[data-pjax]", function (e) {
			$.Common.ajax(e, $(this));
		});

		var options = {items: '.nc-tooltip', track: true};
		$( document ).tooltip(options);
	});
</script>
<?php
	echo "\n". $this->fetch('script');
?>
