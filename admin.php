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


<div class="bv_page_wide" style="display:block;background:#fff;padding-right:1%;overflow:hidden; margin-right:2.5%;margin-top:1%;"> <!-- SOWP MAIN -->

	<div class="bv_inside_heading" style="padding:0.25% 0 0 2%;overflow:hidden;border-bottom:1px solid #ebebeb;">
	<a href="http://blogvault.net/"><img src="<?php echo plugins_url('img/logo.png', __FILE__); ?>" /></a>
	</div>


	<div style="overflow:hidden;">	<!-- SOP 1 -->
			<div class="bv_inside_column1" style="width:100%;max-width:75%;float:left;padding:1% 2.5% 1% 2.5%;border-right:1px solid #ebebeb;overflow:hidden;"> <!-- MCA -->
<?php if (!isset($_REQUEST['free'])) { ?>
						<div align="center" style="margin-bottom: 25px;">
									<iframe style="border: 1px solid gray; padding: 3px;" src="https://player.vimeo.com/video/88638675?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff" width="450" height="275" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
					</div>
<?php } ?>


<?php
		echo $bvNotice;
		if ($blogvault->getOption('bvPublic')) {
?>
		<div style="display:table;table-layout:fixed;width:100%;float:left;padding:1% 2.5% 2em 2.5%;overflow:hidden;" id="form_wrapper">
				<font size='3'><a href='https://webapp.blogvault.net' target="_blank">Click here</a> to manage your backups from the <a href='https://webapp.blogvault.net' target="_blank">blogVault Dashboard.</a></font>
				<br/><br/>
<?php if (isset($_REQUEST['changekey'])) { ?>
				<form method='post'>
					<font size='3'>Change blogVault Key:</font> <input type='text' name='blogvaultkey' size='65'>
					<input type='hidden' name='change_parameter' value='true'>
					<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
					<input type='submit' value='Change'>
				</form>
<?php } ?>
		</div>
<?php } else { ?> <!-- Change keys ELSE -->
			<div style="display:none">
				<a href='http://blogvault.net?bvsrc=bvplugin&wpurl=<?php echo urlencode($blogvault->wpurl()) ?>'> Click here </a> to get your blogVault Key.</font>
				<form method='post'> 
					<font size='3'>Enter blogVault Key:</font> <input type='text' name='blogvaultkey' size='65'>
					<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
					<input type='submit' value='Activate'>	
				</form>
			</div>
<!-- form wrapper starts here-->
<div style="display:table;table-layout:fixed;width:100%;max-width:40%;float:left;padding:1% 2.5% 2em 2.5%;overflow:hidden;border: 1px solid #ebebeb;" id="form_wrapper">
<?php if (!isset($_REQUEST['signin'])) { ?>
	<!-- Signup form starts here -->
			<div>
<?php 	if (isset($_REQUEST['free'])) { ?>
					<!-- blogVault Badge Code -->
					<h2>Step 1:<span style="color: gray;">&nbsp;Add the blogVault Badge to your homepage</span></h2>
					<div style="background-color: #eff2f7; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; width: 80%; padding: 5px 27px 5px 27px; margin-bottom: 40px;">
						<span style="color: #70798f;">Copy the following code to your site</span>
						
						<div style='width: 100%; padding: 5px; margin: 8px 0 8px; background-color: #fff; border-radius: 3px; font-size: 13px; overflow-x: auto; word-wrap: break-word;'>&lt;a href="http://blogvault.net?src=wpbadge"&gt;&lt;img src="//s3.amazonaws.com/bvimgs/bv_badge_dark_1.png" alt="WordPress Backup" /&gt;&lt;/a&gt;</div>
						<a href="http://blogvault.net?src=wpbadge"><img src="//s3.amazonaws.com/bvimgs/bv_badge_dark_1.png" alt="Mobile Analytics" /></a>
					</div>
					<h2>Step 2:<span style="color: gray;">&nbsp;Create a free blogVault Account</span></h2>
<?php 	} else { ?>
				<div style="display:block;padding-bottom:1%;"><font size="3">Create a blogVault Account!</font></div>
				<span style="color:grey;padding:1% 2.5% 0 2.5%;">All plans(<a href="http://blogvault.net/pricing?bvsrc=wpplugin&wpurl=<?php echo urlencode($blogvault->wpurl()) ?>">See Pricing</a>) come with free 1 week trial.</span>
<?php 	} ?>
			</div>

			<form action="https://webapp.blogvault.net/home/api_signup" style="padding:0 2% 2em 1%;" method="post" name="signup">
				<input type="hidden" name="bvsrc" value="wpplugin" />
				<input type="hidden" name="url" value="<?php echo $blogvault->wpurl(); ?>" />
				<input type="hidden" name="secret" value="<?php echo $blogvault->getOption('bvSecretKey'); ?>">
				<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
<?php if ($_error == "email") { ?>
				<div style="color:red; font-weight: bold;" align="right">There is already an account with this email.</div>
<?php } else if ($_error == "pass") { ?>
				<div style="color:red; font-weight: bold;" align="right">Password does not match.</div>
<?php } else if ($_error == "blog") { ?>
				<div style="color:red; font-weight: bold;" align="right">Could not add the site. Please contact <a href="http://blogvault.net/contact/">blogVault Support</a></div>
<?php } ?>
				<table style="border-spacing:10px 10px;">
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
<?php 	if (isset($_REQUEST['free'])) { ?>
							<strong>FREE OFFER</strong>
							<input type='hidden' name='loc' value='WPPLUGINFREEOFFER'>
<?php 	} else { ?>
							<select name="plan">
								<option value="1sitey" selected>1 Site - $89/year</option>
								<option value="3sitey">3 Sites - $189/year</option>
								<option value="7sitey">7 Sites - $389/year</option>
								<option value="dev99m">Unlimited - $99/month</option>
							</select>
<?php 	} ?>
						</td>
					</tr>
				<tr>
					<td></td>
					<td align="right">
					<button type="submit">Register</button> 
				</td></tr>
				<tr>
					<td></td>
<?php 	if (isset($_REQUEST['free'])) { ?>
					<td><p><b>Is free plan unsuitable? </b><a href="<?php echo admin_url('plugins.php?page=bv-key-config') ?>">Signup for Premium Plans</a></p></td>
<?php 	} else { ?>
					<td><p><b>Already have an account? </b><a href="<?php echo admin_url('plugins.php?page=bv-key-config&signin=true') ?>">Sign in</a></p></td>
<?php 	} ?>
				</tr>
				</table>
			</form>

			<!-- Signup form end here -->
<?php } else { ?>
			<!-- Signin form end here -->
			<div>
			  <font size="3">Login to your blogVault Account!</font>
			</div>
			<form action="https://webapp.blogvault.net/home/api_signin" style="padding:0 2% 2em 1%;" method="post" name="signin">
				<input type="hidden" name="bvsrc" value="wpplugin" />
				<input type="hidden" name="url" value="<?php echo $blogvault->wpurl(); ?>">
				<input type="hidden" name="secret" value="<?php echo $blogvault->getOption('bvSecretKey'); ?>">
				<input type='hidden' name='bvnonce' value='<?php echo wp_create_nonce("bvnonce") ?>'>
<?php if ($_error == "user") { ?>
				<div style="color:red; font-weight: bold;">Incorrect Username or Password</div>
<?php } ?>
				<table style="border-spacing:50px 10px;">
					<tr>
						<td><label><strong>Email</strong></label></td>
						<td><input type="text" name="email" /></td>
					</tr>
					<tr>
						<td width="115"><label><strong>Password</strong></label></td>
						<td><input type="password" name="password" /></td>
					<tr/>
					<?php if ($_error == "pass") echo '<tr><td colspan=3><p style="color:red;">The Email or password provided is incorrect</p></td></tr>' ?>
					<tr>
						<td></td>
						<td align="right"><button type="submit">Sign In</button></td>
					</tr>
					<tr>
						<td></td>
						<td align="right"><a href="https://webapp.blogvault.net/password_resets/new?bvsrc=wpplugin&wpurl=<?php echo urlencode($blogvault->wpurl()) ?>" target="_blank">Forgot Password</a></td>
					</tr>
					<tr>
						<td></td>
						<td align="right"><p><b>New to blogVault? </b><a href="<?php echo admin_url('plugins.php?page=bv-key-config') ?>">Sign up</a></p></td>
					</tr>
				</table>
			</form>

<?php } ?>

		</div>	<!-- Signin  form ends here -->
		<div class="bv_3part_column1" style="width:100%;max-width:45%;float:left;padding:3% 2.5% 0 2.5%;overflow:hidden;">
					<div style="width:100%;overflow:hidden; margin-bottom: 10px;">
								<blockquote><span class="bqstart" style="float:left;font-size:400%;color:#cfcfcf;">&#8220;</span><h2>blogVault is my favorite way to backup, migrate, and restore WordPress websites.&nbsp;&nbsp;<font size='2'><a href="http://bit.ly/mightyreview" style="text-decoration:none;" align="right" target="_blank">Read the complete review.</a></font></h2> <span style="float:right;"> - Kristin &#38; Mickey &#64; <a href="http://www.mightyminnow.com" style="text-decoration:none;" target="_blank">MIGHTYminnow</a> <font size='1'>(A Top WordPress Agency)</font></span></blockquote>
					</div>
				<font size='2' color="gray">As seen on:</font>
				<div align="center" style="padding-top:3%;"><img src="<?php echo plugins_url('img/as_seen_in.png', __FILE__); ?>" /></div>
		</div>

	<?php
	}
?>
			</div> <!-- MCA -->
			<div class="bv_ selectedinside_column2" style="margin-top:0.5%;margin-right:0;border:0;max-width:19%;padding:0.5% 0 2em 1%;overflow:hidden;" align="center">
					<!-- SIDE COLUMN CONTENT GOES HERE -->
<?php
	if (!$blogvault->getOption('bvPublic')) {
?>
					<!-- FREE PLAN AD -->
					<div style="background: #a6a49e; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px; padding: 16px 32px; position: relative;">
						<h4 style="text-align: center; color: #dbd8cf; margin: 0px 0 0px; text-transform: uppercase; font-size: 16px;">Offer</h4>
						<span class="price" style="display: block; font-family: 'Average', serif; font-weight: 400; color: #ffffff; text-align: center; margin: 0; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);">
							<strong style="font-size: 60px; line-height: 1em; font-weight: 400;">Free</strong>
						</span>
						<small style="display: block; text-align: center; color: #dbd8cf; font-weight: 400; font-size: 12px;">With blogVault Badge</small>
						<p style="color: #595752; border-top: 1px solid #8e8b83; padding-top: 8px; font-size: 16px; line-height: 1.6em;">
							Personal Sites<br/>
							Weekly Backups<br/>
							100 MB Space<br/>
							Self Service<br/>
						</p>
						<a align="center" href="<?php echo admin_url('plugins.php?page=bv-key-config&free=true') ?>" style="display: block; border: 0; background: #61af05; -webkit-border-radius: 8px; -moz-border-radius: 8px; border-radius: 8px; font-family: 'PT Sans', sans-serif; font-weight: 400; font-weight: 700; text-transform: uppercase; color: #ffffff; font-size: 25.6px; text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3); text-decoration: none; text-align: center; padding: 16px 0 16px; transition: all 0.4s; -moz-transition: all 0.4s; -webkit-transition: all 0.4s; -o-transition: all 0.4s; -moz-box-shadow: 0px 2px 32px rgba(0, 0, 0, 0.3); -webkit-box-shadow: 0px 2px 32px rgba(0, 0, 0, 0.3); box-shadow: 0px 2px 32px rgba(0, 0, 0, 0.3);">
							Sign Up
							<small style="display: block; font-size: 0.5em; text-transform: none; font-weight: 400; color: #dbeac9; line-height: 1em; margin-top: 5.333333333333333px;"></small>
						</a>
					</div> <!-- EOP FREEPLANAD -->
<?php } ?>
			</div>
	</div> <!-- EOP 1 -->

</div> <!-- EOWP MAIN -->
<?php
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