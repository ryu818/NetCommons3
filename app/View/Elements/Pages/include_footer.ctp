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
		<?php /*同じscript src(link href)のファイルをajaxで読み込むと再度、includeされてしまうため、jquery側で対応。*/ ?>
		$._nc.jsExistingTags = {},$._nc.cssExistingTags = {};
		$(document).ready(function(){
			var nScript,nCss,i,head = $('head', document);
			for(i=0; (nScript = document.getElementsByTagName("SCRIPT")[i]); i++) {
				if ( nScript.src ) {
					$._nc.jsExistingTags[nScript.src] = true;
				}
			}
			$('link',head).each(function(){
				nCss = this;
				if ( nCss.href ) {
					$._nc.cssExistingTags[nCss.href] = true;
				}
			});
		});
		$(document).pjax('a[data-pjax]');
		$(document).on("submit", "form[data-pjax],form[data-ajax],form[data-ajax-replace]", function (e) {
			$.Common.ajax(e, $(this));
		});
		$(document).on("click", "a[data-ajax],a[data-ajax-replace],input[data-ajax],input[data-ajax-replace]", function (e) {
			$.Common.ajax(e, $(this));
		});

		var options = {items: '.nc-tooltip', track: true};
		$( document ).tooltip(options);
	});
</script>
<?php
	echo '<!--[if IE]>'."\n".$this->Html->script('html5/', array('inline' => true, 'data-title' => 'IE')).'<![endif]-->';
	echo "\n". $this->fetch('script');
?>
