<input type="hidden" name="select_lang" value="<?php echo($select_lang); ?>" />
<?php
if(isset($config) && $this->action != 'database') {
	echo $this->Form->input('Database.site_name', array(
		'type' => 'hidden',
		'default' => $config['site_name']
	))."\n";
	echo $this->Form->input('Database.datasource', array(
		'type' => 'hidden',
		'default' => $config['datasource']
	))."\n";
	echo $this->Form->input('Database.host', array(
		'type' => 'hidden',
		'default' => $config['host']
	))."\n";

	echo $this->Form->input('Database.login', array(
		'type' => 'hidden',
		'default' => $config['login']
	))."\n";
	echo $this->Form->input('Database.password', array(
		'type' => 'hidden',
		'default' => $config['password']
	))."\n";
	echo $this->Form->input('Database.database', array(
		'type' => 'hidden',
		'default' => $config['database']
	))."\n";
	echo $this->Form->input('Database.prefix', array(
		'type' => 'hidden',
		'default' => $config['prefix']
	))."\n";
	echo $this->Form->input('Database.port', array(
		'type' => 'hidden',
		'default' => $config['port']
	))."\n";
	echo $this->Form->input('Database.persistent', array(
		'type' => 'hidden',
		'default' => $config['persistent']
	))."\n";
} else {
	echo '<input type="hidden" name="chk_database" value="'._ON.'" />';
}
?>