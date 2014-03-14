<?php
global $blogvault;

if ( !function_exists('bvAdminMenu') ) :
	function bvAdminMenu() {
		add_submenu_page('plugins.php', 'blogVault', 'blogVault', 9, 'bv-key-config', 'bvKeyConf');
	}
	add_action('admin_menu', 'bvAdminMenu');
endif;

if ( !function_exists('bvSettingsLink') ) :
	function bvSettingsLink($links, $file) {
		if ( $file == plugin_basename( dirname(__FILE__).'/blogvault.php' ) ) {
			$links[] = '<a href="' . admin_url( 'plugins.php?page=bv-key-config' ) . '">'.__( 'Settings' ).'</a>';
		}
		return $links;
	}
	add_filter('plugin_action_links', 'bvSettingsLink', 10, 2);
endif;

if ( !function_exists('bvKeyConf') ) :
	function bvKeyConf() {
		global $blogvault;
?>
<div style="font-size: 14px; margin-top: 40px; margin-bottom: 30px;">
	<iframe style="border: 1px solid gray; padding: 3px;" src="https://player.vimeo.com/video/88638675?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;" width="500" height="300" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe><br/>
	<a href="http://blogvault.net?bvsrc=wpplugin_knowmore&wpurl=<?php echo urlencode(network_site_url()) ?>">Learn more about blogVault</a>
</div>
<?php
		if (isset($_REQUEST['blogvaultkey'])) {
			if (wp_verify_nonce($_REQUEST['bvnonce'] , "bvnonce") && (strlen($_REQUEST['blogvaultkey']) == 64)) {
				$keys = str_split($_REQUEST['blogvaultkey'], 32);
				$blogvault->updatekeys($keys[0], $keys[1]);
				bvActivateHandler();
				if (isset($_REQUEST['change_parameter'])) {
?>
					<b><font color='green'>Keys Updated!</font></b> blogVault is now backing up your site.<br/><br/>
<?php
				} else {
?>
					<b>Activated!</b> blogVault is now backing up your site.<br/><br/>
<?php
				}
			} else {
?>
				<b style="color:red;">Invalid request!</b> Please try again with a valid key.<br/><br/>
<?php
			}
		}
		if ($blogvault->getOption('bvPublic')) {
?>
			<font size='3'><a href='https://webapp.blogvault.net' target="_blank">Click here</a> to manage your backups from the blogVault Dashboard.</font>
			<br/><br/>
			<form method='post'>
				<font size='3'>Change blogVault Key:</font> <input type='text' name='blogvaultkey' size='65'>
				<input type='hidden' name='change_parameter' value='true'>
				<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
				<input type='submit' value='Change'>
			<form>
<?php
		} else {
?>
			<a href='http://blogvault.net?bvsrc=bvplugin&wpurl=<?php echo urlencode(network_site_url()) ?>'> Click here </a> to get your blogVault Key.</font>
			<form method='post'> 
				<font size='3'>Enter blogVault Key:</font> <input type='text' name='blogvaultkey' size='65'>
				<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
				<input type='submit' value='Activate'>	
			<form>
<?php
		}
	}
endif;

if ( !function_exists('bvActivateWarning') ) :
	function bvActivateWarning() {
		global $hook_suffix;
		global $blogvault;
		if (!$blogvault->getOption('bvPublic') && $hook_suffix == 'plugins.php' ) {
?>
			<div id="message" class="updated" style="text-align: center;">

				<p><b><font color="red">Almost Done</font>: </b><a href="<?php echo admin_url('plugins.php?page=bv-key-config') ?>">Click here</a> to get your blogVault Key and backup your site.</p>
			</div>
<?php
		}
	}
	add_action('admin_notices', 'bvActivateWarning');
endif;