<?php
/*
Plugin Name: Jetpack Module Control
Plugin URI: http://status301.net/wordpress-plugins/jetpack-module-control/
Description: Control Jetpack module activation and availability across your WordPress Multisite.
Text Domain: jetpack-mc
Domain Path: languages
Network: true
Version: 0.4
Author: RavanH
Author URI: http://status301.net/
*/

/*
 * ROADMAP
 * 
 * version 1.0
 * Add "Use Jetpack without connecting to WordPress.com" option.
 * see http://jeremy.hu/customize-the-list-of-modules-available-in-jetpack/
 * >> add_filter( 'jetpack_development_mode', '__return_true' );
 * 
 * version 2.0
 * Replace "Prevent the Jetpack plugin from auto-activating (new) modules" with 
 * finer grained "Select which modules to auto-activate"
 * see http://jeremy.hu/customize-the-list-of-modules-available-in-jetpack/
 * 	function jeherve_auto_activate_stats() {
		return array( 'stats' );
	}
	add_filter( 'jetpack_get_default_modules', 'jeherve_auto_activate_stats' );
 * 
 * TO CONSIDER
 * Make blacklist or whitelist optional
 * Option to disable JUMPSTART
 * Can we disable Debug link in the footer menu?
 * Option to "force_deactivate" (same as blacklist?) as described on https://github.com/Automattic/jetpack/issues/1452
 * 
 */


/**
 * Jetpack Module Control Class
 * 
 * since 0.1
 */
class Jetpack_Module_Control {

	/**
	 * The single instance.
	 * @var 	object
	 * @access  private
	 * @since 	0.1
	 */
	private static $instance = null;

	/**
	 * The plugins basename.
	 * @var 	string
	 * @access  private
	 * @since 	0.1
	 */
	private static $plugin_basename = null;

	/**
	 * Current plugin version.
	 * @since 0.1
	 * @var string 
	 */
	public $version = '0.3';
	
	/**
	 * Available modules array
	 * @since 0.1
	 * @access  private
	 * @var array 
	 */
	private static $modules = null;

	/**
	 * Know modules array with names
	 * @since 0.2
	 * @access  private
	 * @var array 
	 */
	private static $known_modules_names = array(
						'after-the-deadline' 	=> 'Spelling and Grammar',
						'carousel' 				=> 'Carousel',
						'comments' 				=> 'Jetpack Comments',
						'contact-form' 			=> 'Contact Form',
						'custom-content-types' 	=> 'Custom Content Types',
						'custom-css' 			=> 'Custom CSS',
						'enhanced-distribution'	=> 'Enhanced Distribution',
						'gravatar-hovercards' 	=> 'Gravatar Hovercards',
						'infinite-scroll' 		=> 'Infinite Scroll',
						'json-api' 				=> 'JSON API',
						'latex' 				=> 'Beautiful Math',
						'likes' 				=> 'Likes',
						'manage' 				=> 'Manage',
						'markdown' 				=> 'Markdown',
						'minileven' 			=> 'Mobile Theme',
						'monitor' 				=> 'Monitor',
						'notes' 				=> 'Notifications',
						'omnisearch' 			=> 'Omnisearch',
						'photon' 				=> 'Photon',
						'post-by-email' 		=> 'Post by Email',
						'protect' 				=> 'Protect',
						'publicize' 			=> 'Publicize',
						'related-posts' 		=> 'Related Posts',
						'sharedaddy' 			=> 'Sharing',
						'shortcodes' 			=> 'Shortcode Embeds',
						'shortlinks' 			=> 'WP.me Shortlinks',
						'site-icon' 			=> 'Site Icon',
						'sso' 					=> 'Jetpack Single Sign On',
						'stats' 				=> 'WordPress.com Stats',
						'subscriptions' 		=> 'Subscriptions',	
						'tiled-gallery' 		=> 'Tiled Galleries',
						'vaultpress' 			=> 'VaultPress',
						'verification-tools' 	=> 'Site Verification',
						'videopress' 			=> 'VideoPress',
						'widget-visibility' 	=> 'Widget Visibility',
						'widgets' 				=> 'Extra Sidebar Widgets'
					);

	/**
	 * Know modules array with dashicons
	 * @since 0.3
	 * @access  private
	 * @var array 
	 */
	private static $known_modules_icons = array(
						'after-the-deadline' 	=> 'edit',
						'carousel' 				=> 'camera',
						'comments' 				=> 'format-chat',
						'contact-form' 			=> 'feedback',
						'custom-content-types' 	=> 'media-default',
						'custom-css' 			=> 'admin-appearance',
						'enhanced-distribution'	=> 'share',
						'gravatar-hovercards' 	=> 'id', // not available
						'infinite-scroll' 		=> 'star-filled',
						'json-api' 				=> 'share-alt',
						'latex' 				=> 'star-filled',
						'likes' 				=> 'star-filled',
						'manage' 				=> 'wordpress-alt',
						'markdown' 				=> 'editor-code',
						'minileven' 			=> 'smartphone',
						'monitor' 				=> 'flag',
						'notes' 				=> 'admin-comments',
						'omnisearch' 			=> 'search',
						'photon' 				=> 'visibility',
						'post-by-email' 		=> 'email',
						'protect' 				=> 'lock',
						'publicize' 			=> 'share',
						'related-posts' 		=> 'update',
						'sharedaddy' 			=> 'share-alt',
						'shortcodes' 			=> 'text',
						'shortlinks' 			=> 'admin-links',
						'site-icon' 			=> 'admin-site',
						'sso' 					=> 'wordpress-alt',
						'stats' 				=> 'wordpress-alt',
						'subscriptions' 		=> 'email',
						'tiled-gallery' 		=> 'schedule', // not available. maybe tagcloud ?
						'vaultpress' 			=> 'shield-alt', // not availabe
						'verification-tools' 	=> 'clipboard', // maye yes
						'videopress' 			=> 'controls-play',
						'widget-visibility' 	=> 'welcome-widgets-menus',
						'widgets' 				=> 'welcome-widgets-menus',
					);

	/**
	 * Return Jetpack available modules
	 * @since 0.1
	 * @return array
	 */	
	private function get_available_modules() {
		if ( null === self::$modules ) {
			if ( class_exists('Jetpack') ) {
				//remove_filter( 'jetpack_get_available_modules', array($this, 'blacklist') ); // remove filter useless here because of bug in Jetpack
				$modules = array();
				foreach ( Jetpack::get_available_modules() as $module_slug ) {
					$module = Jetpack::get_module( $module_slug );
					if ( $module )
						$modules[$module_slug] = $module['name'];
				}
				self::$modules = $modules;
				add_filter( 'jetpack_get_available_modules', array($this, 'blacklist') ); 
			} else {
				self::$modules = self::$known_modules_names;
			}
		}
		return self::$modules;
	}
	
	/**
	 * Return plugin basename
	 * 
	 * @since 0.1
	 * 
	 * @return string Plugin basename
	 */	
	private function plugin_basename() {
		if ( null === self::$plugin_basename ) {
			self::$plugin_basename = plugin_basename( __FILE__ );
		}
		return self::$plugin_basename;
	}
	
	/**
	 * MANUAL CONTROL
	 */
	    	
	/**
	 * Adds the Manual Control option
	 * 
	 * @since 0.1
	 * @see get_site_option(), checked(), disabled()
	 * 
	 * @uses jetpack_mc_manual_control network option
	 * @echo Html Checkbox input field for jetpack_mc_manual_control option
	 */	
	public function manual_control_settings() {		

		$cws_manual_control = class_exists('CWS_Manual_Control_for_Jetpack_Plugin') ? true : false;
		$option = $cws_manual_control ? '1' : get_site_option('jetpack_mc_manual_control');	
		$disabled = $cws_manual_control || ( defined('JETPACK_MC_LOCKDOWN') && JETPACK_MC_LOCKDOWN == true ) ? true : false;

		?>
		<label>
			<input type='checkbox' name='jetpack_mc_manual_control' value='1' 
			<?php checked( $option, '1' ); ?> 
			<?php disabled( $disabled ); ?>> 
			<?php _e('Prevent the Jetpack plugin from auto-activating (new) modules.','jetpack-mc'); ?>
		</label>
		<?php

	} // END manual_control_settings()

	/**
	 * Activates Manual Control by returning an empty array on module auto-activation. 
	 * Modelled after Manual Control for Jetpack by Mark Jaquith http://coveredwebservices.com/
	 * 
	 * @since 0.1
	 * @see add_filter()
	 */
	public function manual_control() {
		
		if ( get_site_option('jetpack_mc_manual_control', false) )
			add_filter( 'jetpack_get_default_modules', '__return_empty_array', 99 );

	} // END manual_control()

	/**
	 * Disables Centralized Site Management banner by returning false on can_display_jetpack_manage_notice filter. 
	 * 
	 * @since 0.1
	 * @see add_filter()
	 */
	private function no_manage_notice() {
		
		$blacklist = get_site_option('jetpack_mc_blacklist', array() );

		if ( in_array('manage', (array)$blacklist ) )
			add_filter( 'can_display_jetpack_manage_notice', '__return_false' );

	} // END no_manage_notice()


	/**
	 * BLACKLIST
	 */
	
	/**
	 * Adds a checkmark list of modules to blacklist
	 * 
	 * @since 0.1
	 * @see get_site_option(), checked()
	 * 
	 * @uses jetpack_mc_blacklist network option
	 * @echo Html Checkbox input field table for jetpack_mc_blacklist option
	 */
	public function blacklist_settings() {	
		
		$blacklist = get_site_option( 'jetpack_mc_blacklist', array() );
		
		$modules = $this->get_available_modules();
		asort($modules);
		
		$icons = self::$known_modules_icons;

		$disabled = defined('JETPACK_MC_LOCKDOWN') ? JETPACK_MC_LOCKDOWN : false;

		?>
		<fieldset><legend class="screen-reader-text"><span><?php _e('Blacklist Modules','jetpack-mc'); ?></span></legend>
		<?php
		foreach ( $modules as $module => $name ) {
			$icon = isset($icons[$module]) ? $icons[$module] : 'star-empty';
			?>
			<label>
				<input type='checkbox' name='jetpack_mc_blacklist[]' value='<?php echo $module; ?>' 
				<?php checked( in_array($module,(array)$blacklist), true ); ?> 
				<?php disabled( $disabled ); ?>> 
				<span class="dashicons dashicons-<?php echo $icon; ?>"></span> <?php echo $name; ?>
			</label><br>
			<?php
		}
		?>
		</fieldset>
		<?php

	} // END blacklist_settings()

	/**
	 * Blacklist Jetpack modules
	 * Modelled after ParhamG's blacklist_jetpack_modules.php https://gist.github.com/ParhamG/6494979
	 * 
	 * @since 0.1
	 * @param $modules 
	 * 
	 * @return Array Allowed modules after unsetting blacklisted modules from all modules array
	 */
	public function blacklist ( $modules ) {
		
		$blacklist = get_site_option('jetpack_mc_blacklist');	

		foreach ( (array)$blacklist as $mod ) {
			if ( isset( $modules[$mod] ) ) {
				unset( $modules[$mod] );
			}
		}

		return $modules;

	} // END blacklist()

	/**
	 * ADMIN
	 */

	/** 
	 * Initiate plugins admin stuff
	 * 
	 * @since 0.1
	 */

	public function admin_init(){
	
		$this->no_manage_notice();

		if ( is_plugin_active_for_network( $this->plugin_basename() ) ) {

			add_filter( 'network_admin_plugin_action_links_' . $this->plugin_basename(), array($this, 'add_action_link') );

			// Add settings to Network Settings
			// thanks to http://zao.is/2013/07/adding-settings-to-network-settings-for-wordpress-multisite/
			if ( is_network_admin() ) {
				add_filter( 'wpmu_options'       , array( $this, 'show_network_settings' ) );
				add_action( 'update_wpmu_options', array( $this, 'save_network_settings' ) );
			}

		} else {
			
			add_filter( 'plugin_action_links_' . $this->plugin_basename(), array($this, 'add_action_link') );

			// Do regular register/add_settings stuff in 'general' settings on options-general.php 
			$settings = 'general';

			add_settings_section('jetpack-mc', '<a name="jetpack-mc"></a>' . __('Jetpack Module Control','jetpack-mc'), array($this, 'add_settings_section'), $settings);

			// register settings
			if ( !defined('JETPACK_MC_LOCKDOWN') || JETPACK_MC_LOCKDOWN == false ) {
				register_setting( $settings, 'jetpack_mc_manual_control' ); // sanitize_callback 'boolval' ?
				register_setting( $settings, 'jetpack_mc_blacklist', 'array_values' );
			}
			
			// add settings fields
			add_settings_field( 'jetpack_mc_manual_control', __('Manual Control','jetpack-mc'), array($this, 'manual_control_settings'), $settings, 'jetpack-mc' ); // array('label_for' => 'elementid')
			add_settings_field( 'jetpack_mc_blacklist', __('Blacklist Modules','jetpack-mc'), array($this, 'blacklist_settings'), $settings, 'jetpack-mc' );

		}

	}

	/**
	 * Saves the network settings 
	 * 
	 * @since 0.2
	 */
	public function save_network_settings() {

		if ( defined('JETPACK_MC_LOCKDOWN') && JETPACK_MC_LOCKDOWN == true )
			return;
		
		$posted_settings = array();

		if( isset( $_POST['jetpack_mc_manual_control'] ) )
			$posted_settings['jetpack_mc_manual_control'] = '1';

		if( isset( $_POST['jetpack_mc_blacklist'] ) )
			$posted_settings['jetpack_mc_blacklist'] = array_values($_POST['jetpack_mc_blacklist']);

        foreach ( $posted_settings as $name => $value )
            update_site_option( $name, $value );

	}

	/**
	 * Prints the network settings 
	 * 
	 * @since 0.2
	 */
    public function show_network_settings() {
        
		?>
		<h3><a name="jetpack-mc"></a><?php _e('Jetpack Module Control','jetpack-mc'); ?></h3>
		<?php
        $this->add_settings_section();
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e('Manual Control','jetpack-mc'); ?></th>
					<td>
						<?php
						$this->manual_control_settings();
						?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Blacklist Modules','jetpack-mc'); ?></th>
					<td>
						<?php
						$this->blacklist_settings();
						?>
					</td>
				</tr>
			</tbody>
        </table>
        <?php
    }
 
 	/** 
	 * Echos a settings section header
	 * 
	 * @since 0.1
	 * 
	 * @param $option (unused)
	 * @echo Html
	 */
	public function add_settings_section( $option ) {
		echo '<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=Jetpack%20Module%20Control&item_number='
				. $this->version
				. '&no_shipping=0&tax=0&charset=UTF%2d8&currency_code=EUR" title="'
				. __('Donate to keep plugin development going!','jetpack-mc')
				. '"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" style="border:none;float:right;margin:5px 0 0 10px" alt="'
				. __('Donate to keep plugin development going!','jetpack-mc')
				. '" width="92" height="26" /></a>'
				. sprintf(__('The options in this section are provided by the plugin %1$s to allow individual Jetpack module blacklisting. These settings can be locked down by adding %2$s to your wp-config.php file.','jetpack-mc'),'<strong><a href="http://status301.net/wordpress-plugins/jetpack-module-control/">'
				. __('Jetpack Module Control','jetpack-mc') . ' ' . $this->version
				. '</a></strong>','<code> define( \'JETPACK_MC_LOCKDOWN\', true);</code>')
			. '</p>';

	} // END add_setting_section()
 
	/**
	 * Adds an action link on the Plugins page
	 * 
	 * @since 0.1
	 * @see is_plugin_active_for_network(), admin_url(), network_admin_url()
	 * 
	 * @param array $links Plugin de/activation and deletion links
	 * @return array Plugin links plus Settings link
	 */
	public function add_action_link( $links ) {
		$settings_link = is_plugin_active_for_network( $this->plugin_basename() ) ? 
			'<a href="' . network_admin_url('settings.php#jetpack-mc') . '">' . translate('Network Settings') . '</a>' : 
			'<a href="' . admin_url('options-general.php#jetpack-mc') . '">' . translate('Settings') . '</a>';
		return array_merge(
			array( 'settings' => $settings_link ),
			$links
		);
		return $links;
	}

	/**
	 * Routines to execute on plugins_loaded
	 * 
	 * We need to add our jetpack_get_available_modules filter 
	 * AFTER running get_available_modules() because of bug in Jetpack
	 * https://github.com/Automattic/jetpack/issues/2026
	 * https://github.com/Automattic/jetpack/pull/2027
	 *
	 * @since 0.1
	 */
	public function plugins_loaded() {
		global $pagenow;

		// only need translations on admin page
		if ( is_admin() ) {			
			load_plugin_textdomain('jetpack-mc', false, $this->plugin_basename() . '/languages/');

			// populate jetpack modules list before filter is added 
			if ( in_array( $pagenow, array( 'options-general.php', 'settings.php' ) ) )
				$this->get_available_modules();
		}

		add_filter( 'jetpack_get_available_modules', array( $this, 'blacklist' ) );
	}

	/**
	 * Getter method for retrieving single object instance.
	 *
	 * @since 0.1
	 *
	 * @return Jetpack_Module_Control|null instance object
	 */
	public static function instance() {
		if(is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	} // End instance ()

	/**
	 * Constructor
	 *
	 * @since  0.1
	 */
	private function __construct() { 
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 0 );
		add_action( 'init', array( $this, 'manual_control' ), 11 );
		add_action( 'admin_init', array($this,'admin_init') );
	}

	/**
	 * Cloning
	 *
	 * @since  0.1
	 */
	private function __clone() { }

}

Jetpack_Module_Control::instance();
