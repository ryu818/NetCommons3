<?php
$enrollOptions = array();
$options = array();
foreach($modules as $module) {
	if(in_array($module['Module']['id'], $enroll_modules)) {
		$enrollOptions[$module['Module']['id']] = $module['Module']['module_name'];
	} else {
		$options[$module['Module']['id']] = $module['Module']['module_name'];
	}
}
$id = $prefix.$id;
if($prefix == 'Myportal') {
	$title = __('Myportal');
} else {
	$title = __('Private room');
}
?>
<div class="nc-top-description">
	<?php echo __d('authority', 'Please select the modules using in the %s, <br />then click [Next].', $title); ?>
</div>
<table summary="<?php echo __('Select form'); ?>">
	<tr>
		<th class="nowrap align-center" scope="col">
			<?php echo __d('authority', 'Modules allowed to select');?>
		</th>
		<td rowspan="2" class="authority-selectlist-arrow-btn-area nowrap align-center">
			<?php /* 追加 */ ?>
			<input class="nc-common-btn-min" type="button" value="<?php echo __('Add&gt;&gt;'); ?>" onclick="$.Common.frmTransValue($('#NoEnrollModuleLinkModuleId<?php echo $id?>'),$('#ModuleLinkModuleId<?php echo $id?>'));" />
			<br />
			<br />
			<?php /* 削除 */ ?>
			<input class="nc-common-btn-min" type="button" value="<?php echo __('&lt;&lt;Delete'); ?>" onclick="$.Common.frmTransValue($('#ModuleLinkModuleId<?php echo $id?>'),$('#NoEnrollModuleLinkModuleId<?php echo $id?>'));" />
		</td>
		<th class="nowrap align-center" scope="col">
			<?php echo __d('authority', 'Modules allowed to use now');?>
		</th>
	</tr>
	<tr>
		<td class="authority-selectlist nowrap align-center top">
			<div>
				<input class="nc-common-btn-min" type="button" value="<?php echo __('Select All'); ?>" onclick="$.Common.frmAllSelectList($('#NoEnrollModuleLinkModuleId<?php echo $id?>'));" />
				<input class="nc-common-btn-min" type="button" value="<?php echo __('Release All'); ?>" onclick="$.Common.frmAllReleaseList($('#NoEnrollModuleLinkModuleId<?php echo $id?>'));" />
			</div>
			<?php
				$settings = array(
					'id' => 'NoEnrollModuleLinkModuleId'.$id,
					'label' => false,
					'div' => false,
					'type' =>'select',
					'class' => 'authority-selectlist',
					'options' => $options,
					'multiple' => true,
					'size' => 14,
					'escape' => false,
					'name' => false,
				);
				echo $this->Form->input('NoEnrollModuleLink.module_id', $settings);
			?>
		</td>
		<td class="authority-selectlist nowrap align-center top">
			<div>
				<input class="nc-common-btn-min" type="button" value="<?php echo __('Select All'); ?>" onclick="$.Common.frmAllSelectList($('#ModuleLinkModuleId<?php echo $id?>'));" />
				<input class="nc-common-btn-min" type="button" value="<?php echo __('Release All'); ?>" onclick="$.Common.frmAllReleaseList($('#ModuleLinkModuleId<?php echo $id?>'));" />
			</div>
			<?php
				$settings = array(
					'id' => 'ModuleLinkModuleId'.$id,
					'label' => false,
					'div' => false,
					'type' =>'select',
					'class' => 'authority-selectlist',
					'options' => $enrollOptions,
					'multiple' => true,
					'size' => 14,
					'escape' => false,
					'name' => false,
				);
				echo $this->Form->input('ModuleLink.module_id', $settings);
			?>
		</td>
	</tr>
</table>
<div class="note">
	<?php echo __('[Hold down the Ctrl-key (Windows) / Command-key (Macintosh) while click for multiple selections.]'); ?>
</div>