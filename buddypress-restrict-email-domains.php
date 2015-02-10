<?php
/**
 * Plugin Name: WDS BuddyPress Restrict Email Domains
 * Plugin URI: http://webdevstudios.com
 * Description: A fork of BuddyPress Restrict Email Domains by <a href="http://buddypress.org/developers/nuprn1/">Rich Fuller</a>. Enables restriction of email domains for BuddyPress.
 * Author: WebDevStudios
 * Author URI: http://webdevstudios.com
 * Version: 1.0.0
 * License: GPLv2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WDS_BP_Restrict_Email_Domains' ) ) {

	class WDS_BP_Restrict_Email_Domains {

		/**
		 * Construct function to get things started.
		 */
		public function __construct() {
			// Setup some base variables for the plugin
			$this->basename       = plugin_basename( __FILE__ );
			$this->directory_path = plugin_dir_path( __FILE__ );
			$this->directory_url  = plugins_url( dirname( $this->basename ) );

			// Include any required files
			add_action( 'init', array( $this, 'includes' ) );

			// Load Textdomain
			load_plugin_textdomain( 'bp-restrict-email-domains', false, dirname( $this->basename ) . '/languages' );

			// Activation/Deactivation Hooks
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			// Make sure we have our requirements, and disable the plugin if we do not have them.
			add_action( 'admin_notices', array( $this, 'maybe_disable_plugin' ) );

		}

		/**
		 * Include our plugin dependencies.
		 */
		public function includes() {
			if ( $this->meets_requirements() ) {
				require ( $this->directory_path . '/admin/bp-restrict-email-domains-admin.php' );
			}
		}

		/**
		 * Do Hook things
		 */
		public function do_hooks() {
			if ( is_network_admin() ) {
				add_action( 'network_admin_menu', array( $this, 'admin_menus' ) );
			} else {
				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			}
			add_filter( 'bp_core_validate_user_signup', array( $this, 'validate_user_signup' ) );
		}

		/**
		 * Activation hook for the plugin.
		 */
		public function activate() {
			// If requirements are available, run our activation functions
			if ( $this->meets_requirements() ) {
			}
		}

		/**
		 * Deactivation hook for the plugin.
		 */
		public function deactivate() {
		}

		/**
		 * Check that all plugin requirements are met
		 *
		 * @return boolean
		 */
		public static function meets_requirements() {
			// Make sure buddypress exists, otherwise this plugin is pointless
			if ( ! function_exists( 'buddypress' ) ) {
				return false;
			}

			// We have met all requirements
			return true;
		}

		/**
		 * Check if the plugin meets requirements and
		 * disable it if they are not present.
		 */
		public function maybe_disable_plugin() {
			if ( ! $this->meets_requirements() ) {
				// Display our error
				echo '<div id="message" class="error">';
				echo '<p>' . sprintf( __( 'WDS BuddyPress Restrict Email Domains is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'bp-restrict-email-domains' ), admin_url( 'plugins.php' ) ) . '</p>';
				echo '</div>';

				// Deactivate our plugin
				deactivate_plugins( $this->basename );
			}
		}

		/**
		 * Add the admin menu
		 */
		public function admin_menus() {
			add_submenu_page( 'users.php', __( 'Restrict Email Admin', 'bp-restrict-email-domains' ), __( 'Restrict Email', 'bp-restrict-email-domains' ), 'manage_options', 'bp-restrict-email-domains-settings', 'bp_restrict_email_domains_admin' );
		}

		/**
		 * Function that delivers an error if the email address is not allowed
		 */
		public function validate_user_signup( $result ) {

			if ( $this->is_email_address_unsafe( $result['user_email'] ) )
				$result['errors']->add('user_email',  __('You cannot use that email address to signup. We are having problems with them blocking some of our email. Please use another email provider.', 'bp-restrict-email-domains' ) );

			return $result;

		}

		/**
		 * Checks the passed email and makes sure it's allowed
		 */
		public function is_email_address_unsafe( $user_email ) {

			$banned_names = get_site_option( 'banned_email_domains' );

			if ($banned_names && !is_array( $banned_names ))
				$banned_names = explode( "\n", $banned_names);

			if ( is_array( $banned_names ) && empty( $banned_names ) == false ) {

				$email_domain = strtolower( substr( $user_email, 1 + strpos( $user_email, '@' ) ) );

				foreach ( (array) $banned_names as $banned_domain ) {

					if ( $banned_domain == '' )
						continue;

					if (
						strstr( $email_domain, $banned_domain ) ||
						(
							strstr( $banned_domain, '/' ) &&
							preg_match( $banned_domain, $email_domain )
						)
					)
					return true;
				}

			}
			return false;
		}

	}

	$_GLOBALS['WDS_BP_Restrict_Email_Domains'] = new WDS_BP_Restrict_Email_Domains;
	$_GLOBALS['WDS_BP_Restrict_Email_Domains']->do_hooks();
}