<?php
$this->extend('/Frame/block');

echo $this->element('Pages/column', array('blocks' => $blocks, 'parent_id' => $parent_id));
?>