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
	$locale = Configure::read(NC_SYSTEM_KEY.'.locale');

	$common_js = array('common/', 'plugins/jquery.pjax.js', 'plugins/chosen.jquery.js');
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
<?php
if($this->params['plugin'] == '' && $this->params['controller'] == 'pages') {
	echo '	$.ajaxSetup({headers: {"X-NC-PAGE":"true"}});'."\n";
	//TODO:必要ないかも？
	echo '	$._block_type = \'blocks\';'."\n";
} else {
	//TODO:必要ないかも？
	echo '	$._block_type = \'active-blocks\';'."\n";
}
echo '	$._nc = Array();'."\n";
echo '	$._mode = '.$nc_mode.";\n";
echo '	$._debug = '.intval(Configure::read('debug')).";\n";
echo '	$._lang = new Object();'."\n";
echo '	$._display_header_menu = '.intval(Configure::read(NC_CONFIG_KEY.'.'.'display_header_menu')).";\n";
echo '	$._base_url = \''.$this->Html->url('/').'\';'."\n";
echo '	$._full_base_url = \''.$this->Html->url('/', true).'\';'."\n";
echo '	$._current_url = \''.rtrim($this->Html->url(), '/'). '/'.'\';'."\n";
$pemalink = rtrim(Configure::read(NC_SYSTEM_KEY.'.permalink'), '/');
if($pemalink != '')
	$pemalink .= '/';
echo '	$._page_url = \''.$this->Html->url('/').$pemalink. '\';'."\n";


echo '	$._nc.nc_wysiwyg = new Object();'."\n";
echo '	$._nc.nc_wysiwyg[\'allow_attachment\'] = '.(isset($nc_user['allow_attachment']) ? $nc_user['allow_attachment'] : _OFF).';'."\n";
echo '	$._nc.nc_wysiwyg[\'allow_video\'] = '.(isset($nc_user['allow_video']) ? $nc_user['allow_video'] : _OFF).';'."\n";
echo '	$._nc.nc_wysiwyg[\'allow_js\'] = '.($nc_user['allow_htmltag_flag'] ? _ON : _OFF).';'."\n";
?>
	$(function () {
		<?php /*同じscript src(link href)のファイルをajaxで読み込むと再度、includeされてしまうため、jquery側で対応。*/ ?>
		$._nc.jsExistingTags = {},$._nc.cssExistingTags = {};
		$(document).ready(function(){
			var nScript,nCss,i;
			for(i=0; (nScript = document.getElementsByTagName("SCRIPT")[i]); i++) {
				if ( nScript.src ) {
					$._nc.jsExistingTags[nScript.src] = true;
				}
			}
			for(i=0; (nCss = document.getElementsByTagName("LINK")[i]); i++) {
				if ( nCss.href ) {
					$._nc.cssExistingTags[nCss.href] = true;
				}
			}
		});
		$(document).pjax('a[data-pjax]');
		$(document).on("submit", "form[data-pjax],form[data-ajax],form[data-ajax-replace]", function (e) {
			$.Common.postAjax(e, $(this));
		});
		$(document).on("click", "a[data-ajax],a[data-ajax-replace]", function (e) {
			$.Common.getAjax(e, $(this));
		});

		var options = {items: '.nc-tooltip', track: true};
		$( document ).tooltip(options);
	});
</script>
<?php
	echo '<!--[if IE]>'."\n".$this->Html->script('html5/', array('inline' => true, 'data-title' => 'IE')).'<![endif]-->';
	if($locale) {
		echo $this->Html->script(array('locale/'.$locale.'/lang.js', 'locale/'.$locale.'/jquery/ui/jquery.ui.datepicker.js'), array('inline' => true));
	}
	echo "\n".$this->Html->fetchScript('script');
?>
