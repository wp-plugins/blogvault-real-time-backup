<?php
global $blogvault;
global $bvNotice;
$bvNotice = "";

if (!function_exists('bvAdminInitHandler')) :
	function bvAdminInitHandler() {
		global $bvNotice, $blogvault;

		if (isset($_REQUEST['blogvaultkey'])) {
			if (wp_verify_nonce($_REQUEST['bvnonce'] , "bvnonce") && (strlen($_REQUEST['blogvaultkey']) == 64)) {
				$keys = str_split($_REQUEST['blogvaultkey'], 32);
				$blogvault->updatekeys($keys[0], $keys[1]);
				bvActivateHandler();
				$bvNotice = "<b>Activated!</b> blogVault is now backing up your site.<br/><br/>";
				if (isset($_REQUEST['redirect'])) {
					$location = $_REQUEST['redirect'];
					wp_redirect("https://webapp.blogvault.net/dash/redir?q=".urlencode($location));
					exit();
				}
			} else {
				$bvNotice = "<b style='color:red;'>Invalid request!</b> Please try again with a valid key.<br/><br/>";
			}
		}

		if ($blogvault->getOption('bvActivateRedirect')) {
			$blogvault->updateOption('bvActivateRedirect', false);
			wp_redirect( 'plugins.php?page=bv-key-config' );
		}
	}
	add_action('admin_init', 'bvAdminInitHandler');
endif;

if (!function_exists('bvAdminMenu')) :
	function bvAdminMenu() {
		add_submenu_page('plugins.php', 'blogVault', '<span id="bvAdminMenuLink">blogVault</span>', 'manage_options', 'bv-key-config', 'bvKeyConf');
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
		global $blogvault, $bvNotice;
		$_error = NULL;
		if (isset($_GET['error'])) {
			$_error = $_GET['error'];
		}
?>
<div style="font-size: 14px; margin-top: 40px; margin-bottom: 30px;">
	<iframe style="border: 1px solid gray; padding: 3px;" src="https://player.vimeo.com/video/88638675?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;" width="500" height="300" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe><br/>
	<a href="http://blogvault.net?bvsrc=wpplugin_knowmore&wpurl=<?php echo urlencode(network_site_url()) ?>">Learn more about blogVault</a>
</div>
<?php
		echo $bvNotice;
		if ($blogvault->getOption('bvPublic')) {
?>
			<font size='3'><a href='https://webapp.blogvault.net' target="_blank">Click here</a> to manage your backups from the blogVault Dashboard.</font>
			<br/><br/>
			<form method='post'>
				<font size='3'>Change blogVault Key:</font> <input type='text' name='blogvaultkey' size='65'>
				<input type='hidden' name='change_parameter' value='true'>
				<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
				<input type='submit' value='Change'>
			</form>
<?php
		} else {
?>
			<div style="display:none">
				<a href='http://blogvault.net?bvsrc=bvplugin&wpurl=<?php echo urlencode(network_site_url()) ?>'> Click here </a> to get your blogVault Key.</font>
				<form method='post'> 
					<font size='3'>Enter blogVault Key:</font> <input type='text' name='blogvaultkey' size='65'>
					<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
					<input type='submit' value='Activate'>	
				</form>
			</div>
<!-- form wrapper starts here-->
<div style="display: table; table-layout: fixed; width:100%;" id="form_wrapper">
	<!-- Signup form starts here -->
<?php
		if (!isset($_REQUEST['signin'])) {
?>
			<div><br/>
				<strong style="font-size: 14px;">Create a blogVault Account!</strong><br/>
				<span style="color:grey">All plans(<a href="http://blogvault.net/pricing?bvsrc=wpplugin&wpurl=<?php echo urlencode(network_site_url()) ?>">See Pricing</a>) come with free 1 week trial.</span>
			</div><br/>

			<form action="https://webapp.blogvault.net/home/api_signup" style="margin:0px; padding:0px;" method="post" name="signup">
				<input type="hidden" name="bvsrc" value="wpplugin" />
				<input type="hidden" name="url" value="<?php echo network_site_url(); ?>" />
				<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
<?php if ($_error == "email") { ?>
				<div style="color:red; font-weight: bold;">There is already an account with this email.</div>
<?php } else if ($_error == "pass") { ?>
				<div style="color:red; font-weight: bold;">Password does not match.</div>
<?php } else if ($_error == "blog") { ?>
				<div style="color:red; font-weight: bold;">Could not add the site. Please contact <a href="http://blogvault.net/contact/">blogVault Support</a></div>
<?php } ?>
				<table>
					<tr>
						<td><label id='label_email'<?php if ($_error == "email") echo 'style="color:red;"'; ?>><strong>Email</strong></label></td>
						<td><input type="text" id="email" name="email" value="<?php echo get_option('admin_email');?>"></td>
						<td><?php if ($_error == "email") echo '<p style="font-size:smaller;color:red;">has already been taken</p>' ?></td>
					</tr>
					<tr>
						<td><label id='label_password' <?php if ($_error == "pass") echo 'style="color:red;"'; ?>><strong>Password</strong></label></td>
						<td><input type="password" name="password" id="password"></td>
						<td></td>
					</tr>
					<tr>
						<td><label <?php if ($_error == "pass") echo 'style="color:red;"'; ?>><strong>Confirm Password</strong></label></td>
						<td><input type="password" name="password_confirmation" id="confirm_password"></td>
					</tr>
					<tr>
					<td><label><strong>Plan</strong></label></td>
						<td>
							<select name="plan">
								<option value="1sitem" selected>1 Site - $9/month</option>
								<option value="3sitem">3 Sites - $19/month</option>
								<option value="7sitem">7 Sites - $39/month</option>
								<option value="dev99m">Unlimited - $99/month</option>
							</select>
						</td>
					</tr>
				</table>
				<div>
					<button type="submit">Register</button> 
				</div>
			</form>

			<div> <!-- Sign in option-->
				<p><b>Already have an account? </b><a href="<?php echo admin_url('plugins.php?page=bv-key-config&signin=true') ?>">Sign in</a></p>
			</div>
			<!-- Signup form end here -->
<?php
		} else {
?>
			<div>
				<h3>Login to your blogVault Account!</h3>
			</div>
			<form action="https://webapp.blogvault.net/home/api_signin" style="margin:0px; padding:0px;" method="post" name="signin">
				<input type="hidden" name="bvsrc" value="wpplugin" />
				<input type="hidden" name="url" value="<?php echo network_site_url(); ?>">
				<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
<?php if ($_error == "user") { ?>
				<div style="color:red; font-weight: bold;">Incorrect Username or Password</div>
<?php } ?>
				<table>
					<tr>
						<td><label><strong>Email</strong></label></td>
						<td><input type="text" name="email" /></td>
					</tr>
					<tr>
						<td width="115"><label><strong>Password</strong></label></td>
						<td><input type="password" name="password" /></td>
					<tr/>
					<?php if ($_error == "pass") echo '<tr><td colspan=3><p style="color:red;">The Email or password provided is incorrect</p></td></tr>' ?>
				</table>
				<div>
					<button type="submit">Sign In</button>
					<a href="https://webapp.blogvault.net/password_resets/new?bvsrc=wpplugin&wpurl=<?php echo urlencode(network_site_url()) ?>" target="_blank">Forgot Password</a>
				</div>
			</form>

			<div> <!-- Sign up option -->
				<p><b>New to blogVault? </b><a href="<?php echo admin_url('plugins.php?page=bv-key-config') ?>">Sign up</a></p>
			</div>
			<!-- Signin  form ends here -->
<?php
		}
	}
}
endif;

if ( !function_exists('bvActivateWarning') ) :
	function bvActivateWarning() {
		global $hook_suffix;
		global $blogvault;
		if (!$blogvault->getOption('bvPublic') && $hook_suffix == 'plugins.php' ) {
?>
			<div id="message" class="updated" style="padding: 8px; font-size: 16px; background-color: #dff0d8">
						<a class="button-primary" href="<?php echo admin_url('plugins.php?page=bv-key-config') ?>">Activate blogVault</a>	
						&nbsp;&nbsp;&nbsp;<b>Almost Done:</b> Activate your blogVault account to backup your site.
			</div>
<?php
		}
	}
	add_action('admin_notices', 'bvActivateWarning');
endif;