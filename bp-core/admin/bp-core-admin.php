<?php

/**
 * Add an extra update message to the update plugin notification.
 *
 * @package BuddyPress Core
 */
function bp_core_update_message() {
	echo '<p style="color: red; margin: 3px 0 0 0; border-top: 1px solid #ddd; padding-top: 3px">' . __( 'IMPORTANT: <a href="http://codex.buddypress.org/getting-started/upgrading-from-10x/">Read this before attempting to update BuddyPress</a>', 'buddypress' ) . '</p>';
}
add_action( 'in_plugin_update_message-buddypress/bp-loader.php', 'bp_core_update_message' );

/**
 * When BuddyPress is activated we must make sure that mod_rewrite is enabled.
 * We must also make sure a BuddyPress compatible theme is enabled. This function
 * will show helpful messages to the administrator.
 *
 * @package BuddyPress Core
 */
function bp_core_activation_notice() {
	global $wp_rewrite, $current_blog, $bp;

	if ( isset( $_POST['permalink_structure'] ) )
		return false;

	if ( !is_super_admin() )
		return false;

	if ( !empty( $current_blog ) ) {
		if ( $current_blog->blog_id != BP_ROOT_BLOG )
			return false;
	}

	if ( empty( $wp_rewrite->permalink_structure ) ) { ?>

		<div id="message" class="updated fade">
			<p><?php printf( __( '<strong>BuddyPress is almost ready</strong>. You must <a href="%s">update your permalink structure</a> to something other than the default for it to work.', 'buddypress' ), admin_url( 'options-permalink.php' ) ) ?></p>
		</div><?php

	} else {
		// Get current theme info
		$ct = current_theme_info();

		// The best way to remove this notice is to add a "buddypress" tag to
		// your active theme's CSS header.
		if ( !defined( 'BP_SILENCE_THEME_NOTICE' ) && !in_array( 'buddypress', (array)$ct->tags ) ) { ?>

			<div id="message" class="updated fade">
				<p style="line-height: 150%"><?php printf( __( "<strong>BuddyPress is ready</strong>. You'll need to <a href='%s'>activate a BuddyPress compatible theme</a> to take advantage of all of the features. We've bundled a default theme, but you can always <a href='%s'>install some other compatible themes</a> or <a href='%s'>update your existing WordPress theme</a>.", 'buddypress' ), admin_url( 'themes.php' ), admin_url( 'theme-install.php?type=tag&s=buddypress&tab=search' ), admin_url( 'plugin-install.php?type=term&tab=search&s=%22bp-template-pack%22' ) ) ?></p>
			</div>

		<?php
		}
	}
}
add_action( 'admin_notices', 'bp_core_activation_notice' );

/**
 * Renders the main admin panel.
 *
 * @package BuddyPress Core
 * @since {@internal Unknown}}
 */
function bp_core_admin_dashboard() { ?>
	<div class="wrap" id="bp-admin">

		<div id="bp-admin-header">
			<h3><?php _e( 'BuddyPress', 'buddypress' ) ?></h3>
			<h2><?php _e( 'Dashboard',  'buddypress' ) ?></h2>
		</div>

		<?php do_action( 'bp_admin_notices' ) ?>

		<form action="<?php echo site_url( '/wp-admin/admin.php?page=bp-general-settings' ) ?>" method="post" id="bp-admin-form">
			<div id="bp-admin-content">
				<p>[TODO: All sorts of awesome things will go here. Latest plugins and themes, stats, version check, support topics, news, tips]</p>
			</div>
		</form>

	</div>

<?php
}

/**
 * Renders the Settings admin panel.
 *
 * @package BuddyPress Core
 * @since {@internal Unknown}}
 */
function bp_core_admin_settings() {
	global $wpdb, $bp, $current_blog;

	$ud = get_userdata( $bp->loggedin_user->id );

	if ( isset( $_POST['bp-admin-submit'] ) && isset( $_POST['bp-admin'] ) ) {
		if ( !check_admin_referer('bp-admin') )
			return false;

		// Settings form submitted, now save the settings.
		foreach ( (array)$_POST['bp-admin'] as $key => $value ) {

			if ( bp_is_active( 'xprofile' ) ) {
				if ( 'bp-xprofile-base-group-name' == $key )
					$wpdb->query( $wpdb->prepare( "UPDATE {$bp->profile->table_name_groups} SET name = %s WHERE id = 1", stripslashes( $value ) ) );
				elseif ( 'bp-xprofile-fullname-field-name' == $key )
					$wpdb->query( $wpdb->prepare( "UPDATE {$bp->profile->table_name_fields} SET name = %s WHERE group_id = 1 AND id = 1", stripslashes( $value ) ) );
			}

			update_site_option( $key, $value );
		}
	}
	?>

	<div class="wrap">

		<h2><?php _e( 'BuddyPress Settings', 'buddypress' ) ?></h2>

		<?php if ( isset( $_POST['bp-admin'] ) ) : ?>

			<div id="message" class="updated fade">
				<p><?php _e( 'Settings Saved', 'buddypress' ) ?></p>
			</div>

		<?php endif; ?>

		<form action="" method="post" id="bp-admin-form">

			<table class="form-table">
				<tbody>

					<?php if ( bp_is_active( 'xprofile' ) ) : ?>

						<tr>
							<th scope="row"><?php _e( 'Base profile group name', 'buddypress' ) ?>:</th>
							<td>
								<input name="bp-admin[bp-xprofile-base-group-name]" id="bp-xprofile-base-group-name" value="<?php echo esc_attr( stripslashes( get_site_option( 'bp-xprofile-base-group-name' ) ) ) ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Full Name field name', 'buddypress' ) ?>:</th>
							<td>
								<input name="bp-admin[bp-xprofile-fullname-field-name]" id="bp-xprofile-fullname-field-name" value="<?php echo esc_attr( stripslashes( get_site_option( 'bp-xprofile-fullname-field-name' ) ) ) ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Disable BuddyPress to WordPress profile syncing?', 'buddypress' ) ?>:</th>
							<td>
								<input type="radio" name="bp-admin[bp-disable-profile-sync]"<?php if ( (int)get_site_option( 'bp-disable-profile-sync' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-profile-sync" value="1" /> <?php _e( 'Yes', 'buddypress' ) ?> &nbsp;
								<input type="radio" name="bp-admin[bp-disable-profile-sync]"<?php if ( !(int)get_site_option( 'bp-disable-profile-sync' ) || '' == get_site_option( 'bp-disable-profile-sync' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-profile-sync" value="0" /> <?php _e( 'No', 'buddypress' ) ?>
							</td>
						</tr>

					<?php endif; ?>

					<tr>
						<th scope="row"><?php _e( 'Hide admin bar for logged out users?', 'buddypress' ) ?>:</th>
						<td>
							<input type="radio" name="bp-admin[hide-loggedout-adminbar]"<?php if ( (int)get_site_option( 'hide-loggedout-adminbar' ) ) : ?> checked="checked"<?php endif; ?> id="bp-admin-hide-loggedout-adminbar-yes" value="1" /> <?php _e( 'Yes', 'buddypress' ) ?> &nbsp;
							<input type="radio" name="bp-admin[hide-loggedout-adminbar]"<?php if ( !(int)get_site_option( 'hide-loggedout-adminbar' ) ) : ?> checked="checked"<?php endif; ?> id="bp-admin-hide-loggedout-adminbar-no" value="0" /> <?php _e( 'No', 'buddypress' ) ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Disable avatar uploads? (Gravatars will still work)', 'buddypress' ) ?>:</th>
						<td>
							<input type="radio" name="bp-admin[bp-disable-avatar-uploads]"<?php if ( (int)get_site_option( 'bp-disable-avatar-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="bp-admin-disable-avatar-uploads-yes" value="1" /> <?php _e( 'Yes', 'buddypress' ) ?> &nbsp;
							<input type="radio" name="bp-admin[bp-disable-avatar-uploads]"<?php if ( !(int)get_site_option( 'bp-disable-avatar-uploads' ) ) : ?> checked="checked"<?php endif; ?> id="bp-admin-disable-avatar-uploads-no" value="0" /> <?php _e( 'No', 'buddypress' ) ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Disable user account deletion?', 'buddypress' ) ?>:</th>
						<td>
							<input type="radio" name="bp-admin[bp-disable-account-deletion]"<?php if ( (int)get_site_option( 'bp-disable-account-deletion' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-account-deletion" value="1" /> <?php _e( 'Yes', 'buddypress' ) ?> &nbsp;
							<input type="radio" name="bp-admin[bp-disable-account-deletion]"<?php if ( !(int)get_site_option( 'bp-disable-account-deletion' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-account-deletion" value="0" /> <?php _e( 'No', 'buddypress' ) ?>
						</td>
					</tr>

					<?php if ( function_exists( 'bp_forums_setup') ) : ?>

						<tr>
							<th scope="row"><?php _e( 'Disable global forum directory?', 'buddypress' ) ?>:</th>
							<td>
								<input type="radio" name="bp-admin[bp-disable-forum-directory]"<?php if ( (int)get_site_option( 'bp-disable-forum-directory' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-forum-directory" value="1" /> <?php _e( 'Yes', 'buddypress' ) ?> &nbsp;
								<input type="radio" name="bp-admin[bp-disable-forum-directory]"<?php if ( !(int)get_site_option( 'bp-disable-forum-directory' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-forum-directory" value="0" /> <?php _e( 'No', 'buddypress' ) ?>
							</td>
						</tr>

					<?php endif; ?>

					<?php if ( bp_is_active( 'activity' ) ) : ?>

						<tr>
							<th scope="row"><?php _e( 'Disable activity stream commenting on blog and forum posts?', 'buddypress' ) ?>:</th>
							<td>
								<input type="radio" name="bp-admin[bp-disable-blogforum-comments]"<?php if ( (int)get_site_option( 'bp-disable-blogforum-comments' ) || false === get_site_option( 'bp-disable-blogforum-comments' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-blogforum-comments" value="1" /> <?php _e( 'Yes', 'buddypress' ) ?> &nbsp;
								<input type="radio" name="bp-admin[bp-disable-blogforum-comments]"<?php if ( !(int)get_site_option( 'bp-disable-blogforum-comments' ) ) : ?> checked="checked"<?php endif; ?> id="bp-disable-blogforum-comments" value="0" /> <?php _e( 'No', 'buddypress' ) ?>
							</td>
						</tr>

					<?php endif; ?>

					<?php do_action( 'bp_core_admin_screen_fields' ) ?>

				</tbody>
			</table>

			<?php do_action( 'bp_core_admin_screen' ) ?>

			<p class="submit">
				<input class="button-primary" type="submit" name="bp-admin-submit" id="bp-admin-submit" value="<?php _e( 'Save Settings', 'buddypress' ) ?>"/>
			</p>

			<?php wp_nonce_field( 'bp-admin' ) ?>

		</form>

	</div>

<?php
}

/**
 * Renders the Component Setup admin panel.
 *
 * @package BuddyPress Core
 * @since {@internal Unknown}}
 * @uses bp_core_admin_component_options()
 */
function bp_core_admin_component_setup() {
	global $wpdb, $bp;

	if ( isset( $_POST['bp-admin-component-submit'] ) && isset( $_POST['bp_components'] ) ) {
		if ( !check_admin_referer('bp-admin-component-setup') )
			return false;

		// Settings form submitted, now save the settings. First, set active components
		foreach ( (array)$_POST['bp_components'] as $key => $value ) {
			if ( !(int) $value )
				$disabled[$key] = 1;
		}
		update_site_option( 'bp-deactivated-components', $disabled );
		
		// Then, update the directory pages
		$directory_pages = array();	
		foreach ( (array)$_POST['bp_pages'] as $key => $value ) {
			if ( !empty( $value ) )
				$directory_pages[$key] = (int)$value;
		}
		bp_core_update_page_meta( $directory_pages );
	} ?>

	<div class="wrap">

		<h2><?php _e( 'BuddyPress Component Setup', 'buddypress' ) ?></h2>

		<?php if ( isset( $_POST['bp-admin-component-submit'] ) ) : ?>

			<div id="message" class="updated fade">
				<p><?php _e( 'Settings Saved', 'buddypress' ) ?></p>
			</div>

		<?php endif; ?>

		<form action="" method="post" id="bp-admin-component-form">

			

			<?php $disabled_components = get_site_option( 'bp-deactivated-components' ); ?>
			
			<?php bp_core_admin_component_options() ?>
			
			<?php bp_core_admin_page_options() ?>

			<p class="submit clear">
				<input class="button-primary" type="submit" name="bp-admin-component-submit" id="bp-admin-component-submit" value="<?php _e( 'Save Settings', 'buddypress' ) ?>"/>
			</p>

			<?php wp_nonce_field( 'bp-admin-component-setup' ) ?>

		</form>
	</div>

<?php
}

/**
 * Creates reusable markup for component setup on the Components and Pages dashboard panel.
 *
 * This markup has been abstracted so that it can be used both during the setup wizard as well as
 * when BP has been fully installed.
 *
 * @package BuddyPress Core
 * @since 1.3
 */
function bp_core_admin_component_options() {
	global $bp_wizard;
	
	$disabled_components = apply_filters( 'bp_deactivated_components', get_site_option( 'bp-deactivated-components' ) ); 
	
	// An array of strings looped over to create component setup markup
	$optional_components = array(
		'xprofile' => array(
			'title' 	=> __( "Extended Profiles", 'buddypress' ),
			'description' 	=> __( "Fully editable profile fields allow you to define the fields users can fill in to describe themselves. Tailor profile fields to suit your audience.", 'buddypress' )
		),
		'friends' => array(
			'title' 	=> __( "Friend Connections", 'buddypress' ),
			'description' 	=> __( "Let your users make connections so they can track the activity of others, or filter on only those users they care about the most.", 'buddypress' ) 
		),
		'messages' => array(
			'title'		=> __( "Private Messaging", 'buddypress' ),
			'description' 	=> __( "Private messaging will allow your users to talk to each other directly, and in private. Not just limited to one on one discussions, your users can send messages to multiple recipients.", 'buddypress' )
		),
		'activity' => array(
			'title' 	=> __( "Activity Streams", 'buddypress' ),
			'description' 	=> __( "Global, personal and group activity streams with threaded commenting, direct posting, favoriting and @mentions. All with full RSS feed and email notification support.", 'buddypress' )
		),
		'groups' => array(
			'title' 	=> __( "Extensible Groups", 'buddypress' ),
			'description' 	=> __( "Powerful public, private or hidden groups allow your users to break the discussion down into specific topics with a separate activity stream and member listing.", 'buddypress' )
		),
		'forums' => array(
			'title' 	=> __( "Discussion Forums", 'buddypress' ),
			'description' 	=> __( "Full powered discussion forums built directly into groups allow for more conventional in-depth conversations. NOTE: This will require an extra (but easy) setup step.", 'buddypress' )
		)
	);
	
	if ( is_multisite() ) {
		$optional_components['blogs'] = array(
			'title'		=> __( "Blog Tracking", 'buddypress' ),
			'description'	=> __( "Track new blogs, new posts and new comments across your entire blog network.", 'buddypress' )
		);
	}
	
	?>
	
	<?php /* The setup wizard uses different, more descriptive text here */ ?>
	<?php if ( empty( $bp_wizard ) ) : ?>
		<h3><?php _e( 'Optional Components', 'buddypress' ) ?></h3>
				
		<p><?php _e( "Select the BuddyPress components you'd like to enable.", 'buddypress' ) ?></p>
	<?php endif ?>
	
	<table class="form-table">
		<tbody>
			<?php foreach ( $optional_components as $name => $labels ) : ?>
			<tr valign="top">
				<th scope="row">
					<?php echo esc_html( $labels['title'] ) ?>
				</th>
				
				<td>
					<label for="bp_components[bp-<?php echo esc_attr( $name ) ?>.php]">
						<input type="checkbox" name="bp_components[bp-<?php echo esc_attr( $name ) ?>.php]" value="1"<?php if ( !isset( $disabled_components['bp-' . esc_attr( $name ) . '.php'] ) ) : ?> checked="checked"<?php endif ?> />
						<?php echo esc_html( $labels['description'] ) ?>
					</label>

				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<?php /* Make sure that the blogs component is deactivated when multisite is shut off */ ?>
	<?php if ( !is_multisite() ) : ?>
		<input type="hidden" name="bp_components[bp-blogs.php]" value="0" />
	<?php endif ?>
	
	<?php
}

/**
 * Creates reusable markup for page setup on the Components and Pages dashboard panel.
 *
 * This markup has been abstracted so that it can be used both during the setup wizard as well as
 * when BP has been fully installed.
 *
 * @package BuddyPress Core
 * @since 1.3
 */
function bp_core_admin_page_options() {
	// Get the existing WP pages
	$existing_pages = bp_core_get_page_meta();
	
	// An array of strings looped over to create component setup markup
	$directory_pages = array(
		'groups' => __( "Groups Directory", 'buddypress' ),
		'forums' => __( "Forums Directory", 'buddypress' ),
		'members' => __( "Members Directory", 'buddypress' ),
		'activity' => __( "Activity", 'buddypress' ),
		'register' => __( "Register", 'buddypress' ),
		'activate' => __( "Activate", 'buddypress' )
	);
	
	if ( is_multisite() ) {
		$directory_pages['blogs'] = __( "Blogs Directory", 'buddypress' );
	}
	
	?>
	
	<h3><?php _e( "BuddyPress Page Setup", 'buddypress' ) ?></h3>
	
	<p><?php _e( "Choose the WordPress pages you'd like to associate with the following BuddyPress content.", 'buddypress' ) ?></p>
	
	<p><?php _e( "Leaving any of these items unset makes that content inaccessible to visitors, so be sure to fill this section out carefully.", 'buddypress' ) ?></p>
	
	<table class="form-table">
		<tbody>
			<?php foreach ( $directory_pages as $name => $label ) : ?>
			<tr valign="top">
				<th scope="row">
					<label for="bp_pages[<?php echo esc_attr( $name ) ?>]"><?php echo esc_html( $label ) ?></label>
				</th>
				
				<td>
					<?php echo wp_dropdown_pages( array(
						'name' => 'bp_pages[' . esc_attr( $name ) . ']',
						'echo' => false,
						'show_option_none' => __( '- Select -', 'buddypress' ),
						'selected' => !empty( $existing_pages[$name] ) ? $existing_pages[$name] : false
					) ) ?>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	
	<?php
}

/**
 * Loads admin panel styles and scripts.
 *
 * @package BuddyPress Core
 * @since {@internal Unknown}}
 */
function bp_core_add_admin_menu_styles() {
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
		wp_enqueue_style( 'bp-admin-css', apply_filters( 'bp_core_admin_css', BP_PLUGIN_URL . '/bp-core/css/admin.dev.css' ), array(), BP_VERSION );
	else
		wp_enqueue_style( 'bp-admin-css', apply_filters( 'bp_core_admin_css', BP_PLUGIN_URL . '/bp-core/css/admin.css' ), array(), BP_VERSION );

	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );
}

?>