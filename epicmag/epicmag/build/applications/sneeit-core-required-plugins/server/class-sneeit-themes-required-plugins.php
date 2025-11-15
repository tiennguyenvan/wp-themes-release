<?php
/**
 * DragBlock's Sneeit-core-required-plugins.
 *
 * @package Class sneeit themes required plugins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once ABSPATH . 'wp-admin/includes/plugin.php';
// dev-reply#244.
if( ! class_exists( 'Sneeit_Themes_Required_Plugins' ) ) {
/**
	 * Check class-def#245
	 */
	class Sneeit_Themes_Required_Plugins {
		public $remain = array();
		public $checker = 'sneeit_update_checker';
		public $admin_slug = 'sneeit-core';
		public $admin_redirect = 'sneeit-core-import';
		public $admin_redirect_activate = 'sneeit-core-activate';
		public $admin_redirect_import = 'sneeit-core-import';
		public $sub_slug = 'sneeit-core-required-plugins';
		public $ajax_slug = '';
		public $theme_name = '';
		/**
		 * Check Documentation#2416
		 *
		 * @param object|array|string $epicmag_cstrp_checker check var-def#2416.
		 */
		private function epicmag_cstrp_remain( $epicmag_cstrp_checker ) {
			// dev-reply#2429.
			return strtolower( str_replace( [' '], '-', trim( $epicmag_cstrp_checker ) ) );
		}
		/**
		 * Check Documentation#2420
		 */
		public function __construct() {
			// dev-reply#2437.
			$epicmag_cstrp_admin = explode( ', ', EPICMAG_REQUIRED_PLUGINS );
			foreach ( $epicmag_cstrp_admin as $epicmag_cstrp_slug ) {
				$this->remain[ $this->epicmag_cstrp_remain( $epicmag_cstrp_slug ) ] = ''; // dev-reply#2440.
			}
			$epicmag_cstrp_redirect = get_plugins();
			$epicmag_cstrp_activate = get_site_option( 'active_sitewide_plugins' ) ?: [];
			$epicmag_cstrp_import = get_option( 'active_plugins' ) ?: [];
			$epicmag_cstrp_sub = array_merge( array_keys( $epicmag_cstrp_activate ), $epicmag_cstrp_import );
			foreach ( $epicmag_cstrp_sub as $epicmag_cstrp_ajax ) {
				if ( isset( $epicmag_cstrp_redirect[ $epicmag_cstrp_ajax ] ) ) {
					$epicmag_cstrp_theme = $epicmag_cstrp_redirect[ $epicmag_cstrp_ajax ];
					// dev-reply#2455.
					$epicmag_cstrp_checker = $this->epicmag_cstrp_remain( dirname( $epicmag_cstrp_ajax ) );
					unset( $this->remain[ $epicmag_cstrp_checker ] );
					// dev-reply#2460.
					if ( ! empty( $epicmag_cstrp_theme['TextDomain'] ) ) {
						$epicmag_cstrp_name = $this->epicmag_cstrp_remain( $epicmag_cstrp_theme['TextDomain'] );
						unset( $this->remain[ $epicmag_cstrp_name ] );
					}
				}
			}
			// dev-reply#2470.
			if ( count( $this->remain ) === 0 ) {
				return;
			}
			// dev-reply#2476.
			$epicmag_cstrp_normalize = wp_get_theme();
			$epicmag_cstrp_plugin = $epicmag_cstrp_normalize->get( 'UpdateURI' );
			// dev-reply#2479.
			$epicmag_cstrp_items = ( ! ( empty( $epicmag_cstrp_plugin ) || 'https://sneeit.com/free' === $epicmag_cstrp_plugin ) ) && 'https://sneeit.com/' === $epicmag_cstrp_plugin;
			if ( $epicmag_cstrp_items ) {
				$this->admin_redirect = $this->admin_redirect_activate;
			} else {
				$this->admin_redirect = $this->admin_redirect_import;
			}
			// dev-reply#2488.
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 1 );
			add_action( 'switch_theme', array( $this, 'refresh_plugin_update_checker' ), 1 );
			add_action( 'activated_plugin', array( $this, 'refresh_theme_update_checker' ), 1 );
			add_action( 'deactivated_plugin', array( $this, 'refresh_theme_update_checker' ), 1 );
			add_action( 'deactivated_plugin', array( $this, 'refresh_theme_update_checker' ), 1 );
			add_action( 'admin_footer', array( $this, 'refresh_update_checker' ), 1 );
			add_action( 'after_setup_theme', array( $this, 'load_languages' ), 1 );
			// dev-reply#24102.
			$this->ajax_slug = str_replace( '-', '_', $this->sub_slug );
			add_action( 'wp_ajax_nopriv_' . $this->sub_slug, array( $this, 'installer' ) );
			add_action( 'wp_ajax_' . $this->sub_slug, array( $this, 'installer' ) );
		}
		/**
		 * Check Documentation#2473
		 */
		public function load_languages() {
			// dev-reply#24115.
			load_theme_textdomain( 'epicmag', get_template_directory() . '/languages' );
		}
		/**
		 * Check Documentation#2478
		 */
		public function refresh_theme_update_checker() {
			delete_site_transient( 'update_plugins' );
			delete_transient( 'update_plugins' );
		}
		/**
		 * Check Documentation#2483
		 */
		public function refresh_plugin_update_checker() {
			delete_site_transient( 'update_plugins' );
			delete_transient( 'update_plugins' );
		}
		/**
		 * Check Documentation#2488
		 */
		public function refresh_update_checker() {
			if ( empty( get_transient( $this->checker ) ) ) {
				delete_site_transient( 'update_plugins' );
				delete_transient( 'update_plugins' );
				delete_site_transient( 'update_plugins' );
				delete_transient( 'update_plugins' );
				set_transient( $this->checker, true, 60 * 60 * 24 ); // dev-reply#24142.
			}
		}
		/**
		 * Check Documentation#2498
		 */
		public function admin_menu() {
			// dev-reply#24153.
			if ( empty( $GLOBALS['admin_page_hooks'][ $this->admin_slug ] ) ) {
				// dev-reply#24155.
				add_menu_page(
					'Sneeit Core', // dev-reply#24157.
					'Sneeit Core', // dev-reply#24158.
					'manage_options', // dev-reply#24159.
					$this->admin_slug, // dev-reply#24160.
					array( $this, 'add_submenu_page' ), // dev-reply#24161.
					get_template_directory_uri() . '/assets/images/sneeit-logo-16.png', // dev-reply#24162.
					6 // dev-reply#24163.
				);
			}
			global $menu;
			foreach ( $menu as $epicmag_cstrp_item => $epicmag_cstrp_this ) {
				if ( ! empty( $epicmag_cstrp_this[2] ) && $epicmag_cstrp_this[2] === $this->admin_slug ) {
					$menu[ $epicmag_cstrp_item ][0] .= ' <span class="awaiting-mod">' . count( $this->remain ) . '</span>';
					break;
				}
			}
			// dev-reply#24176.
			$epicmag_cstrp_all = wp_get_theme();
			$this->theme_name = $epicmag_cstrp_all->get( 'Name' );
			add_submenu_page(
				$this->admin_slug, // dev-reply#24181.
				$this->admin_slug, // dev-reply#24182.
				$this->theme_name . ' ' . 'Plugins' . ' <span class="awaiting-mod">' . count( $this->remain ) . '</span>', // dev-reply#24183.
				'manage_options', // dev-reply#24184.
				$this->sub_slug, // dev-reply#24185.
				array( $this, 'add_submenu_page' ) // dev-reply#24186.
			);
			remove_submenu_page( $this->admin_slug, $this->admin_slug );
		}
		/**
		 * Check Documentation#24133
		 */
		public function add_submenu_page() {
			echo '<div class="app ' . esc_attr( $this->sub_slug ) . '"></div>';
		}
		/**
		 * Check Documentation#24137
		 */
		public function admin_notices() {
			if ( ! empty( $_GET['page'] ) ) {
				$epicmag_cstrp_plugins = sanitize_text_field( wp_unslash( $_GET['page'] ) );
				// dev-reply#24208.
				if ( ( $epicmag_cstrp_plugins ) === $this->sub_slug ) {
					return;
				}
			}
			$epicmag_cstrp_network = array_keys( $this->remain );
			$epicmag_cstrp_activated = array_map( 'ucfirst', $epicmag_cstrp_network );
			$epicmag_cstrp_site = implode( ', ', $epicmag_cstrp_activated );
			echo '<section><div class="notice notice-large notice-warning is-dismissible">';
			echo '<h2 class="notice-title">';
			/* translators: see trans-note#24151 */
			echo sprintf( esc_html__( 'Missing required plugins for %s theme', 'epicmag' ), esc_html( $this->theme_name ) );
			echo '</h2>';
			echo '<p>';
			/* translators: see trans-note#24154 */
			echo sprintf( esc_html__( '%s requires following plugins to work: ', 'epicmag' ), esc_html( $this->theme_name ) ) . '<strong>' . esc_html( $epicmag_cstrp_site ) . '</strong>';
			echo '</p>';
			echo '<p>';
			echo '<a class="button button-large button-warning" href="' . esc_attr( menu_page_url( esc_attr( $this->sub_slug ), false ) ) . '">';
			echo esc_html__( 'Please Install Required Plugins', 'epicmag' );
			echo '</a>';
			echo '</p>';
			echo '</div></section>';
		}
		/**
		 * Check Documentation#24163
		 */
		public function admin_enqueue_scripts() {
			// dev-reply#24239.
			$epicmag_cstrp_active = get_template_directory_uri();
			$epicmag_cstrp_file = '/build/applications/';
			if ( empty( $_GET['page'] ) ) {
				wp_enqueue_style( $this->sub_slug, $epicmag_cstrp_active . $epicmag_cstrp_file . $this->sub_slug . '/client/index.css', null, time() );
				return;
			}
			// dev-reply#24248.
			$epicmag_cstrp_plugins = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			if ( ( $epicmag_cstrp_plugins ) !== $this->sub_slug ) {
				wp_enqueue_style( $this->sub_slug, $epicmag_cstrp_active . $epicmag_cstrp_file . $this->sub_slug . '/client/index.css', null, time() );
				return;
			}
			// dev-reply#24257.
			$epicmag_cstrp_data = get_template_directory() . $epicmag_cstrp_file . $this->sub_slug . '/client/index.asset.php';
			if ( ! file_exists( $epicmag_cstrp_data ) ) {
				return;
			}
			// dev-reply#24262.
			$epicmag_cstrp_text = include $epicmag_cstrp_data;
			// dev-reply#24266.
			wp_enqueue_style( $this->sub_slug, $epicmag_cstrp_active . $epicmag_cstrp_file . $this->sub_slug . '/client/style-index.css', null, time() );
			// dev-reply#24269.
			array_push( $epicmag_cstrp_text['dependencies'], 'wp-i18n', 'jquery' );
			wp_enqueue_script( $this->sub_slug, $epicmag_cstrp_active . $epicmag_cstrp_file . $this->sub_slug . '/client/index.js', $epicmag_cstrp_text['dependencies'], time(), true );
			wp_localize_script( $this->sub_slug, 'sneeitCoreRequiredPlugins', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'sneeitCoreUrl' => admin_url( 'admin.php?page=' . $this->admin_redirect ),
				'nonce'   => wp_create_nonce( $this->sub_slug ),
				// dev-reply#24276.
				'screenshot' => get_template_directory_uri() . '/assets/images/plugin-screenshot.png',
				'text' => array(
					'finished' => esc_html__( 'Finished', 'epicmag' ),
					'title' => esc_html__( 'Required Plugins for ', 'epicmag' ) . $this->theme_name,
					'button' => esc_html__( 'Install Required Plugins', 'epicmag' ),
					'redirecting' => esc_html__( 'Redirecting ...', 'epicmag' ),
					'error' => esc_html__( 'WordPress Server Error', 'epicmag' ),
					'label' => esc_html__( 'Required Plugins', 'epicmag' )
				),
				'plugins' => $this->remain,
			) );
		}
		// dev-reply#24290.
		/**
		 * Check Documentation#24208
		 *
		 * @param object|array|string $epicmag_cstrp_domain check var-def#24208.
		 */
		public function ajax_error_die( $epicmag_cstrp_domain ) {
			echo json_encode( array( 'error' => $epicmag_cstrp_domain ) );
			die();
		}
		/**
		 * Check Documentation#24213
		 *
		 * @param object|array|string $epicmag_cstrp_domain check var-def#24213.
		 */
		public function ajax_finished_die( $epicmag_cstrp_domain ) {
			echo json_encode( $epicmag_cstrp_domain );
			die();
		}
		/**
		 * Check Documentation#24218
		 *
		 * @param object|array|string $epicmag_cstrp_update check var-def#24218.
		 */
		public function ajax_request_verify_die( $epicmag_cstrp_update = array() ) {
			if ( empty( $_POST['nonce'] ) ) {
				$this->ajax_error_die( esc_html__( 'empty nonce', 'epicmag' ) );
			}
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), $this->sub_slug ) ) {
				$this->ajax_error_die( esc_html__( 'Timeout! Please reload the page.', 'epicmag' ) );
			}
			if ( is_string( $epicmag_cstrp_update ) ) {
				$epicmag_cstrp_update = explode( ',', $epicmag_cstrp_update );
			}
			if ( ! empty( $epicmag_cstrp_update ) ) {
				foreach ( $epicmag_cstrp_update as $epicmag_cstrp_uri ) {
					$epicmag_cstrp_uri = trim( $epicmag_cstrp_uri );
					if ( ! isset( $_POST[ $epicmag_cstrp_uri ] ) ) {
						/* translators: see trans-note#24233 */
						$this->ajax_error_die( sprintf( esc_html__( 'Missing required field: %s', 'epicmag' ), $epicmag_cstrp_uri ) );
					}
				}
			}
		}
		/**
		 * Check Documentation#24238
		 *
		 * @param object|array|string $epicmag_cstrp_checker check var-def#24238.
		 */
		public function plugin_install_file( $epicmag_cstrp_checker ) {
			// dev-reply#24326.
			$epicmag_cstrp_requires = WP_PLUGIN_DIR . '/' . $epicmag_cstrp_checker . '/';
			if ( file_exists( $epicmag_cstrp_requires . $epicmag_cstrp_checker . '.php' ) ) {
				$epicmag_cstrp_sneeit = file_get_contents( $epicmag_cstrp_requires . $epicmag_cstrp_checker . '.php' );
				if ( $epicmag_cstrp_sneeit && strpos( $epicmag_cstrp_sneeit, 'Plugin Name:' ) !== false ) {
					return $epicmag_cstrp_requires . $epicmag_cstrp_checker . '.php';
				}
			}
			// dev-reply#24335.
			$epicmag_cstrp_license = glob( WP_PLUGIN_DIR . '/' . $epicmag_cstrp_checker . '/*.php' );
			foreach ( $epicmag_cstrp_license as $epicmag_cstrp_globals ) {
				$epicmag_cstrp_sneeit = file_get_contents( $epicmag_cstrp_globals );
				if ( $epicmag_cstrp_sneeit && strpos( $epicmag_cstrp_sneeit, 'Plugin Name:' ) !== false ) {
					return $epicmag_cstrp_globals;
				}
			}
			return '';
		}
		/**
		 * Check Documentation#24258
		 *
		 * @param object|array|string $epicmag_cstrp_checker check var-def#24258.
		 */
		public function activate_plugin( $epicmag_cstrp_checker ) {
			$epicmag_cstrp_ajax = $this->plugin_install_file( $epicmag_cstrp_checker );
			if ( ! $epicmag_cstrp_ajax ) {
				/* translators: see trans-note#24262 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid file', 'epicmag' ), $epicmag_cstrp_checker ) );
			}
			// dev-reply#24353.
			wp_cache_delete( 'plugins', 'plugins' );
			$epicmag_cstrp_menu = activate_plugin( $epicmag_cstrp_ajax );
			if ( is_wp_error( $epicmag_cstrp_menu ) ) {
				/* translators: see trans-note#24268 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot active "%1$s": file %2$s %3$s', 'epicmag' ), $epicmag_cstrp_checker, $epicmag_cstrp_ajax, $epicmag_cstrp_menu->get_error_message() ) );
			}
			return true;
		}
		/**
		 * Check Documentation#24272
		 *
		 * @param object|array|string $epicmag_cstrp_key check var-def#24272.
		 * @param object|array|string $epicmag_cstrp_checker check var-def#24272.
		 */
		public function unzip_activate_plugin( $epicmag_cstrp_key, $epicmag_cstrp_checker ) {
			// dev-reply#24368.
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			$epicmag_cstrp_value = unzip_file( $epicmag_cstrp_key, WP_PLUGIN_DIR );
			if ( is_wp_error( $epicmag_cstrp_value ) ) {
				/* translators: see trans-note#24279 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot unzip "%1$s": %2$s', 'epicmag' ), $epicmag_cstrp_checker, $epicmag_cstrp_value->get_error_message() ) );
			}
			if ( ! is_dir( WP_PLUGIN_DIR . '/' . $epicmag_cstrp_checker ) ) {
				/* translators: see trans-note#24282 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid slug', 'epicmag' ), $epicmag_cstrp_checker ) );
			}
			return $this->activate_plugin( $epicmag_cstrp_checker );
		}
		/**
		 * Check Documentation#24286
		 *
		 * @param object|array|string $epicmag_cstrp_current check var-def#24286.
		 * @param object|array|string $epicmag_cstrp_key check var-def#24286.
		 * @param object|array|string $epicmag_cstrp_checker check var-def#24286.
		 */
		public function download_unzip_activate_plugin( $epicmag_cstrp_current, $epicmag_cstrp_key, $epicmag_cstrp_checker ) {
			$epicmag_cstrp_get = download_url( $epicmag_cstrp_current );
			if ( is_wp_error( $epicmag_cstrp_get ) ) {
				/* translators: see trans-note#24290 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot download "%1$s": %2$s', 'epicmag' ), $epicmag_cstrp_current, $epicmag_cstrp_get->get_error_message() ) );
			}
			$epicmag_cstrp_page = dirname( $epicmag_cstrp_key );
			// dev-reply#24392.
			if ( ! is_dir( $epicmag_cstrp_page ) ) {
				// dev-reply#24394.
				if ( ! mkdir( $epicmag_cstrp_page, 0777 ) ) {
					unlink( $epicmag_cstrp_get );
					/* translators: see trans-note#24298 */
					return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot create folder of %s', 'epicmag' ), $epicmag_cstrp_checker ) );
				}
			}
			// dev-reply#24401.
			if ( ! rename( $epicmag_cstrp_get, $epicmag_cstrp_key ) ) {
				unlink( $epicmag_cstrp_get );
				/* translators: see trans-note#24304 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot upload %s', 'epicmag' ), $epicmag_cstrp_checker ) );
			}
			return $this->unzip_activate_plugin( $epicmag_cstrp_key, $epicmag_cstrp_checker );
		}
		/**
		 * Check Documentation#24308
		 */
		public function installer() {
			$this->ajax_request_verify_die( 'plugin' );
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$epicmag_cstrp_keys = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );
			if ( ! empty( $this->remain[ $epicmag_cstrp_keys ] ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24422.
			if (
				is_dir( WP_PLUGIN_DIR . '/' . $epicmag_cstrp_keys ) &&
				// dev-reply#24425.
				$this->plugin_install_file( $epicmag_cstrp_checker )
			) {
				$epicmag_cstrp_menu = $this->activate_plugin( $epicmag_cstrp_keys );
				if ( is_wp_error( $epicmag_cstrp_menu ) ) {
					$this->ajax_error_die( 'error 1: ' . $epicmag_cstrp_menu->get_error_message() );
				}
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24436.
			$epicmag_cstrp_capitalized = get_template_directory() . '/plugins/' . $epicmag_cstrp_keys . '.zip';
			$epicmag_cstrp_imploded = $this->download_unzip_activate_plugin(
				"https://github.com/tiennguyenvan/wp-plugins-release/raw/main/{$epicmag_cstrp_keys}/{$epicmag_cstrp_keys}.zip",
				$epicmag_cstrp_capitalized,
				$epicmag_cstrp_keys
			);
			if ( ! is_wp_error( $epicmag_cstrp_imploded ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24447.
			if ( file_exists( $epicmag_cstrp_capitalized ) && ! is_wp_error( $this->unzip_activate_plugin( $epicmag_cstrp_capitalized, $epicmag_cstrp_keys ) ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24461.
			$epicmag_cstrp_url = $this->download_unzip_activate_plugin(
				"https://downloads.wordpress.org/plugin/{$epicmag_cstrp_keys}.zip",
				$epicmag_cstrp_capitalized,
				$epicmag_cstrp_keys
			);
			if ( is_wp_error( $epicmag_cstrp_url ) ) {
				/* translators: see trans-note#24349 */
				$this->ajax_error_die( sprintf( esc_html__( 'Cannot install "%1$s": %2$s', 'epicmag' ), $epicmag_cstrp_keys, $epicmag_cstrp_url->get_error_message() ) );
			}
			$this->ajax_finished_die( 'installed' );
		}
	}
}
new Sneeit_Themes_Required_Plugins();
