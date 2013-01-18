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
	$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	$nc_mode = intval($this->Session->read(NC_SYSTEM_KEY.'.'.'mode'));
	$locale = Configure::read(NC_SYSTEM_KEY.'.locale');

// TODO:test
if (!empty($page_style['file'])) {
	echo '<link href="theme/page_styles/'.$page_style['file'].'" rel="stylesheet "type="text/css">';
}
//$this->Html->css(array('Default.page', 'Default.gray/page'), null, array('frame' => true));

	echo "\n".$this->fetch('meta');
	echo "\n".$this->Html->script(array('jquery/', 'common/'), array('inline' => true, 'data-title' => 'jquery'));
	echo '<!--[if IE]>'."\n".$this->Html->script('html5/', array('inline' => true, 'data-title' => 'IE')).'<![endif]-->';
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
</script>
<?php
	if($locale) {
		echo $this->Html->script(array('locale/'.$locale.'/lang.js', 'locale/'.$locale.'/jquery/ui/jquery.ui.datepicker.js'), array('inline' => true));
	}
	$common_css = array('common/vendors/', 'common/main/', 'jquery/base/', 'common/editable/common', 'plugins/chosen.css', );
	//if($this->params['controller'] == 'pages') {	// TODO:system_flagがOFFの場合にincludeするように後に修正。
		$common_css[] = 'pages/common/';
	//}
	if($nc_mode == NC_BLOCK_MODE) {
		$common_css[] = 'pages/block/';
	}
	echo $this->Html->css($common_css, null, array('inline' => true));
	$this->Html->css(array('Default.page', 'Default.gray/page'), null, array('frame' => true));	//TODO:test 固定
	echo $this->fetch('css');

?>