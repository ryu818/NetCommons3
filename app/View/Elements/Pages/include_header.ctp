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

// TODO:test
if (!empty($page_style['file'])) {
	echo '<link href="theme/page_styles/'.$page_style['file'].'" rel="stylesheet "type="text/css">';
}
//$this->Html->css(array('Default.page', 'Default.gray/page'), null, array('frame' => true));

	echo "\n".$this->fetch('meta');
	echo "\n".$this->Html->script(array('jquery/'), array('inline' => true, 'data-title' => 'jquery'));
?>
<?php
	$common_css = array('common/vendors/', 'common/main/', 'jquery/base/', 'common/editable/common', 'plugins/chosen.css', );
	//if($this->params['controller'] == 'pages') {	// TODO:system_flagがOFFの場合にincludeするように後に修正。
		$common_css[] = 'pages/common/';
	//}
	if($nc_mode == NC_BLOCK_MODE) {
		$common_css[] = 'pages/block/';
	}
	echo $this->Html->css($common_css, null, array('inline' => true));
	$this->Html->css(array('Default.page', 'Default.gray/page'), null, array('frame' => true));	//TODO:test 固定
	echo $this->Html->fetchCss('css');
	//echo $this->fetch('css');
?>