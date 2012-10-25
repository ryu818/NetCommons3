<?php
/**
 * ヘッダー出力
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       View.Elements
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

	$nc_mode = intval($this->Session->read(NC_SYSTEM_KEY.'.'.'mode'));
	$locale = Configure::read('locale');
	$this->Html->script('locale/'.$locale.'/');

// TODO:test
$this->Html->css(array('Default.page.css', 'Default.gray/page'), null, array('frame' => true));

	echo "\n".$this->fetch('meta');
	$common_js = array('jquery/', 'plugins/jquery.pjax.js', 'plugins/chosen.jquery.js', 'common/');
	if($this->params['controller'] == 'pages') {
		$common_js[] = 'pages/common/';
	}
	if($nc_mode == NC_BLOCK_MODE) {
		$common_js[] = 'pages/block/';
	}
	echo "\n".$this->Html->script($common_js, array('inline' => true, 'data-title' => 'Common'));
?>
<script>
<?php
echo '	$._mode = '.$nc_mode.";\n";
echo '	$._debug = '.intval(Configure::read('debug')).";\n";
echo '	$._lang = new Object();'."\n";
echo '	$._display_header_menu = '.intval(Configure::read(NC_CONFIG_KEY.'.'.'display_header_menu')).";\n";
echo '	$._base_url = \''.$this->Html->url('/').'\';'."\n";
echo '	$._current_url = \''.rtrim($this->Html->url(), '/'). '/'.'\';'."\n";
//TODO:必要ないかも？
echo '	$._block_type = \''.$this->params['block_type'].'\';'."\n";
?>
	$(function () {
		$(document).pjax('a[data-pjax]');
	});
</script>
<?php
	echo '<!--[if IE]>'."\n".$this->Html->script('html5/', array('inline' => true, 'data-title' => 'IE')).'<![endif]-->';
	echo "\n".$this->Html->fetchScript('script', ' data-title="Themes and Plugins" ');

	//echo $this->fetch('script');
	//echo $scripts_for_layout;

	$common_css = array('common/vendors/', 'common/main/', 'jquery/base/', 'common/editable/common', 'plugins/chosen.css', );
	if($this->params['controller'] == 'pages') {
		$common_css[] = 'pages/common/';
	}
	if($nc_mode == NC_BLOCK_MODE) {
		$common_css[] = 'pages/block/';
	}
	echo $this->Html->css($common_css, null, array('inline' => true, 'data-title' => 'Common'));
	echo $this->Html->fetchCss('css', ' data-title="Themes and Plugins" ');
	//echo $this->fetch('css');
?>
