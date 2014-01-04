<?php
add_action('admin_menu', 'bvAdminMenu');
add_filter('plugin_action_links', 'bvSettingsLink', 10, 2);
add_action('admin_notices', 'bvActivateWarning');

function bvSplitKey($pub_and_sec_key) {
	$keys = str_split($pub_and_sec_key, 32);
	return $keys;
}

function bvAdminMenu() {
	add_submenu_page('plugins.php', 'blogVault', 'blogVault', 9, 'bv-key-config', 'bvKeyConf');
}

function bvSettingsLink($links, $file) {
	if ( $file == plugin_basename( dirname(__FILE__).'/blogvault.php' ) ) {
		$links[] = '<a href="' . admin_url( 'plugins.php?page=bv-key-config' ) . '">'.__( 'Settings' ).'</a>';
	}
	return $links;
}

function bvKeyConf() {
	if (isset($_POST['blogvaultkey']) && (strlen($_POST['blogvaultkey']) == 64)) {
		$keys = str_split($_POST['blogvaultkey'], 32);
		bvUpdateKeys($keys[0], $keys[1]);
		bvActivateHandler();
	}
	if (get_option('bvPublic')) {
?>
		<p style="font-size: 14px; margin-top: 40px;">
			<b>Activated!</b> blogVault is now backing up your site.<br/><br/>
			<u><a href='https://webapp.blogvault.net'>Click here</a></u> to see access the blogVault Dashboard.
		</p>
<?php
	} else {
?>
		<p style="font-size: 14px; margin-top: 40px;">
			<a href='http://blogvault.net?bvsrc=bvplugin&wpurl=<?php echo urlencode(network_site_url()) ?>'> Click here </a> to get your blogVault Key.</font>
			<form method='post'> 
				<font size='3'>Enter blogVault Key:</font> <input type='text' name='blogvaultkey' size='65'>
				<input type='submit' value='Activate'>	
			<form>
		</p>
<?php
	}
}

function bvActivateWarning() {
	global $hook_suffix;
	if (!get_option('bvPublic') && $hook_suffix == 'plugins.php' ) {
?>
		<div id="message" class="updated" style="padding: 5px; margin: 5px auto; border: 1px solid #ff9900; text-align: center; background-color:#FFFFCC; font-size: 14px;">

		<p><b>Almost Done: </b><a href="<?php echo admin_url('plugins.php?page=bv-key-config') ?>">Click here</a> to get your blogVault Key and backup your site.</p>
		</div>
<?php
	}
}