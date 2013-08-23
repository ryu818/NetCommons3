<?php
	$flashMes = $this->Session->flash();

	if($flashMes) {
		echo '<script>$(function(){$.Common.flash("'.$this->Js->escape($flashMes).'"'.(isset($pause) ? ','.intval($pause) : '').');});</script>';
	}
?>