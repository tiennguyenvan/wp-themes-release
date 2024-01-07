<?php
/**
 * DragBlock's Sneeit-core-required-plugins.
 *
 * @package Class sneeit themes required plugins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// dev-reply#242.if (!class_exists('Sneeit_Themes_Required_Plugins')) {
/**
	 * Check class-def#243
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
		 * Check Documentation#2419
		 */
		public function __construct() {
			// dev-reply#2430.			$epicmag_cstrp_remain = explode(', ', EPICMAG_REQUIRED_PLUGINS);
			foreach ( $epicmag_cstrp_remain as $epicmag_cstrp_checker ) {
				$this->remain[ $epicmag_cstrp_checker ] = ''; // dev-reply#2433.			}

									$epicmag_cstrp_admin = get_site_option( 'active_sitewide_plugins' );

			// dev-reply#2440.			$epicmag_cstrp_slug = get_option('active_plugins');

			// dev-reply#2443.			$epicmag_cstrp_redirect = array_merge(
				is_array( $epicmag_cstrp_admin ) ? array_keys( $epicmag_cstrp_admin ) : [],
				is_array( $epicmag_cstrp_slug ) ? $epicmag_cstrp_slug : []
			);

						
			foreach ( $epicmag_cstrp_redirect as $epicmag_cstrp_activate ) {
				$epicmag_cstrp_import = dirname( $epicmag_cstrp_activate );
				unset( $this->remain[ $epicmag_cstrp_import ] );
			}


			// dev-reply#2458.						if (count($this->remain) === 0) {
				return;
			}

			// dev-reply#2464.			$epicmag_cstrp_sub = wp_get_theme();
			$epicmag_cstrp_ajax = $epicmag_cstrp_sub->get( 'UpdateURI' );
			// dev-reply#2467.						$epicmag_cstrp_theme = (!(empty($epicmag_cstrp_ajax) ||  'https://sneeit.com/free' === $epicmag_cstrp_ajax)) && 'https://sneeit.com/' === $epicmag_cstrp_ajax;
			if ( $epicmag_cstrp_theme ) {
				$this->admin_redirect = $this->admin_redirect_activate;
			} else {
				$this->admin_redirect = $this->admin_redirect_import;
			}

			// dev-reply#2476.			add_action('admin_menu', array($this, 'admin_menu'));
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 1 );
			add_action( 'switch_theme', array( $this, 'refresh_plugin_update_checker' ), 1 );
			add_action( 'activated_plugin', array( $this, 'refresh_theme_update_checker' ), 1 );
			add_action( 'deactivated_plugin', array( $this, 'refresh_theme_update_checker' ), 1 );
			add_action( 'deactivated_plugin', array( $this, 'refresh_theme_update_checker' ), 1 );
			add_action( 'admin_footer', array( $this, 'refresh_update_checker' ), 1 );
			add_action( 'after_setup_theme', array( $this, 'load_languages' ), 1 );




			// dev-reply#2490.
						

			$this->ajax_slug = str_replace( '-', '_', $this->sub_slug );
			add_action( 'wp_ajax_nopriv_' . $this->sub_slug, array( $this, 'installer' ) );
			add_action( 'wp_ajax_' . $this->sub_slug, array( $this, 'installer' ) );
		}
		/**
		 * Check Documentation#2474
		 */
		public function load_languages() {

			// dev-reply#24103.			load_theme_textdomain('epicmag', get_template_directory() . '/languages');
		}


		
		/**
		 * Check Documentation#2482
		 */
		public function refresh_theme_update_checker() {
			delete_site_transient( 'update_plugins' );
			delete_transient( 'update_plugins' );
		}
		/**
		 * Check Documentation#2487
		 */
		public function refresh_plugin_update_checker() {
			delete_site_transient( 'update_plugins' );
			delete_transient( 'update_plugins' );
		}
		/**
		 * Check Documentation#2492
		 */
		public function refresh_update_checker() {
			if ( empty( get_transient( $this->checker ) ) ) {
				delete_site_transient( 'update_plugins' );
				delete_transient( 'update_plugins' );
				delete_site_transient( 'update_plugins' );
				delete_transient( 'update_plugins' );
				set_transient( $this->checker, true, 60 * 60 * 24 ); // dev-reply#24130.			}
		}



		
		/**
		 * Check Documentation#24105
		 */
		public function admin_menu() {
			// dev-reply#24141.			if (empty($GLOBALS['admin_page_hooks'][$this->admin_slug])) {
				// dev-reply#24143.				add_menu_page(
					'Sneeit Core', // dev-reply#24145.					'Sneeit Core', // dev-reply#24146.					'manage_options', // dev-reply#24147.					$this->admin_slug, // dev-reply#24148.					array($this, 'add_submenu_page'), // dev-reply#24149.					get_template_directory_uri() . '/assets/images/sneeit-logo-16.png', // dev-reply#24150.					6 // dev-reply#24151.				);
			}

			global $menu;

			foreach ( $menu as $epicmag_cstrp_name => $epicmag_cstrp_items ) {
				if ( ! empty( $epicmag_cstrp_items[2] ) && $epicmag_cstrp_items[2] === $this->admin_slug ) {
					$menu[ $epicmag_cstrp_name ][0] .= ' <span class="awaiting-mod">' . count( $this->remain ) . '</span>';
					break;
				}
			}

			// dev-reply#24164.			$epicmag_cstrp_item = wp_get_theme();
			$this->theme_name = $epicmag_cstrp_item->get( 'Name' );

			add_submenu_page(
				$this->admin_slug, // dev-reply#24169.				$this->admin_slug, // dev-reply#24170.				$this->theme_name . ' ' . 'Plugins' . ' <span class="awaiting-mod">' . count($this->remain) . '</span>', // dev-reply#24171.				'manage_options', // dev-reply#24172.				$this->sub_slug, // dev-reply#24173.				array($this, 'add_submenu_page') // dev-reply#24174.			);

						remove_submenu_page( $this->admin_slug, $this->admin_slug );
		}

		
		/**
		 * Check Documentation#24131
		 */
		public function add_submenu_page() {
			echo '<div class="app ' . esc_attr( $this->sub_slug ) . '"></div>';
		}

		
		/**
		 * Check Documentation#24137
		 */
		public function admin_notices() {
			if ( ! empty( $_GET['page'] ) ) {
				$epicmag_cstrp_this = sanitize_text_field( wp_unslash( $_GET['page'] ) );
				// dev-reply#24196.				if ($epicmag_cstrp_this === $this->sub_slug) {
					return;
				}
			}


			$epicmag_cstrp_network = array_keys( $this->remain );
			$epicmag_cstrp_activated = array_map( 'ucfirst', $epicmag_cstrp_network );
			$epicmag_cstrp_plugins = implode( ', ', $epicmag_cstrp_activated );
			echo '<section><div class="notice notice-large notice-warning is-dismissible">';
			echo '<h2 class="notice-title">';
			/* translators: see trans-note#24152 */
			echo sprintf( esc_html__( 'Missing required plugins for %s theme', 'epicmag' ), esc_html( $this->theme_name ) );
			echo '</h2>';
			echo '<p>';
			/* translators: see trans-note#24155 */
			echo sprintf( esc_html__( '%s requires following plugins to work: ', 'epicmag' ), esc_html( $this->theme_name ) ) . '<strong>' . esc_html( $epicmag_cstrp_plugins ) . '</strong>';
			echo '</p>';
			echo '<p>';
			echo '<a class="button button-large button-warning" href="' . esc_attr( menu_page_url( esc_attr( $this->sub_slug ), false ) ) . '">';
			echo esc_html__( 'Please Install Required Plugins', 'epicmag' );
			echo '</a>';
			echo '</p>';
			echo '</div></section>';
		}


		
		/**
		 * Check Documentation#24167
		 */
		public function admin_enqueue_scripts() {
			// dev-reply#24227.			$epicmag_cstrp_site = get_template_directory_uri();
			$epicmag_cstrp_specific = '/build/applications/';


			if ( empty( $_GET['page'] ) ) {
				wp_enqueue_style( $this->sub_slug, $epicmag_cstrp_site . $epicmag_cstrp_specific . $this->sub_slug . '/client/index.css', null, time() );
				return;
			}
			// dev-reply#24236.			$epicmag_cstrp_this = sanitize_text_field(wp_unslash($_GET['page']));
			if ( ( $epicmag_cstrp_this ) !== $this->sub_slug ) {
				wp_enqueue_style( $this->sub_slug, $epicmag_cstrp_site . $epicmag_cstrp_specific . $this->sub_slug . '/client/index.css', null, time() );
				return;
			}



			// dev-reply#24245.			$epicmag_cstrp_active = get_template_directory() . $epicmag_cstrp_specific . $this->sub_slug . '/client/index.asset.php';
			if ( ! file_exists( $epicmag_cstrp_active ) ) {
				return;
			}
			// dev-reply#24250.						$epicmag_cstrp_plugin = include $epicmag_cstrp_active;

			// dev-reply#24254.			wp_enqueue_style($this->sub_slug, $epicmag_cstrp_site . $epicmag_cstrp_specific . $this->sub_slug . '/client/style-index.css', null, time());

			// dev-reply#24257.			array_push($epicmag_cstrp_plugin['dependencies'], 'wp-i18n', 'jquery');
			wp_enqueue_script( $this->sub_slug, $epicmag_cstrp_site . $epicmag_cstrp_specific . $this->sub_slug . '/client/index.js', $epicmag_cstrp_plugin['dependencies'], time(), true );
			wp_localize_script( $this->sub_slug, 'sneeitCoreRequiredPlugins', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'sneeitCoreUrl' => admin_url( 'admin.php?page=' . $this->admin_redirect ),
				'nonce'   => wp_create_nonce( $this->sub_slug ),
				// dev-reply#24264.				'screenshot' => get_template_directory_uri() . '/assets/images/plugin-screenshot.png',
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

		// dev-reply#24278.		public function ajax_error_die($epicmag_cstrp_update) {
			echo json_encode( array( 'error' => $epicmag_cstrp_update ) );
			die();
		}
		/**
		 * Check Documentation#24217
		 *
		 * @param object|array|string $epicmag_cstrp_update check var-def#24217.
		 */
		public function ajax_finished_die( $epicmag_cstrp_update ) {
			echo json_encode( $epicmag_cstrp_update );
			die();
		}

		/**
		 * Check Documentation#24223
		 *
		 * @param object|array|string $epicmag_cstrp_uri check var-def#24223.
		 */
		public function ajax_request_verify_die( $epicmag_cstrp_uri = array() ) {
			if ( empty( $_POST['nonce'] ) ) {
				$this->ajax_error_die( esc_html__( 'empty nonce', 'epicmag' ) );
			}
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), $this->sub_slug ) ) {
				$this->ajax_error_die( esc_html__( 'Timeout! Please reload the page.', 'epicmag' ) );
			}
			if ( is_string( $epicmag_cstrp_uri ) ) {
				$epicmag_cstrp_uri = explode( ',', $epicmag_cstrp_uri );
			}

			if ( ! empty( $epicmag_cstrp_uri ) ) {
				foreach ( $epicmag_cstrp_uri as $epicmag_cstrp_requires ) {
					$epicmag_cstrp_requires = trim( $epicmag_cstrp_requires );
					if ( ! isset( $_POST[ $epicmag_cstrp_requires ] ) ) {
						/* translators: see trans-note#24239 */
						$this->ajax_error_die( sprintf( esc_html__( 'Missing required field: %s', 'epicmag' ), $epicmag_cstrp_requires ) );
					}
				}
			}
		}

		/**
		 * Check Documentation#24245
		 *
		 * @param object|array|string $epicmag_cstrp_import check var-def#24245.
		 */
		public function plugin_install_file( $epicmag_cstrp_import ) {
			// dev-reply#24314.			$epicmag_cstrp_sneeit = WP_PLUGIN_DIR . '/' . $epicmag_cstrp_import . '/';
			if ( file_exists( $epicmag_cstrp_sneeit . $epicmag_cstrp_import . '.php' ) ) {
				$epicmag_cstrp_license = file_get_contents( $epicmag_cstrp_sneeit . $epicmag_cstrp_import . '.php' );
				if ( $epicmag_cstrp_license && strpos( $epicmag_cstrp_license, 'Plugin Name:' ) !== false ) {
					return $epicmag_cstrp_sneeit . $epicmag_cstrp_import . '.php';
				}
			}

			// dev-reply#24323.			$epicmag_cstrp_globals = glob(WP_PLUGIN_DIR . '/' . $epicmag_cstrp_import . '/*.php');
			foreach ( $epicmag_cstrp_globals as $epicmag_cstrp_menu ) {
				$epicmag_cstrp_license = file_get_contents( $epicmag_cstrp_menu );
				if ( $epicmag_cstrp_license && strpos( $epicmag_cstrp_license, 'Plugin Name:' ) !== false ) {
					return $epicmag_cstrp_menu;
				}
			}

			return '';
		}
		/**
		 * Check Documentation#24265
		 *
		 * @param object|array|string $epicmag_cstrp_import check var-def#24265.
		 */
		public function activate_plugin( $epicmag_cstrp_import ) {
			$epicmag_cstrp_key = $this->plugin_install_file( $epicmag_cstrp_import );
			if ( ! $epicmag_cstrp_key ) {
				/* translators: see trans-note#24269 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid file', 'epicmag' ), $epicmag_cstrp_import ) );
			}

			// dev-reply#24341.												wp_cache_delete('plugins', 'plugins');

			$epicmag_cstrp_value = activate_plugin( $epicmag_cstrp_key );
			if ( is_wp_error( $epicmag_cstrp_value ) ) {
				/* translators: see trans-note#24276 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot active "%1$s": file %2$s %3$s', 'epicmag' ), $epicmag_cstrp_import, $epicmag_cstrp_key, $epicmag_cstrp_value->get_error_message() ) );
			}
			return true;
		}

		/**
		 * Check Documentation#24281
		 *
		 * @param object|array|string $epicmag_cstrp_current check var-def#24281.
		 * @param object|array|string $epicmag_cstrp_import check var-def#24281.
		 */
		public function unzip_activate_plugin( $epicmag_cstrp_current, $epicmag_cstrp_import ) {
			// dev-reply#24356.												require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			$epicmag_cstrp_get = unzip_file( $epicmag_cstrp_current, WP_PLUGIN_DIR );
			if ( is_wp_error( $epicmag_cstrp_get ) ) {
				/* translators: see trans-note#24287 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot unzip "%1$s": %2$s', 'epicmag' ), $epicmag_cstrp_import, $epicmag_cstrp_get->get_error_message() ) );
			}
			if ( ! is_dir( WP_PLUGIN_DIR . '/' . $epicmag_cstrp_import ) ) {
				/* translators: see trans-note#24290 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid slug', 'epicmag' ), $epicmag_cstrp_import ) );
			}
			return $this->activate_plugin( $epicmag_cstrp_import );
		}

		/**
		 * Check Documentation#24295
		 *
		 * @param object|array|string $epicmag_cstrp_page check var-def#24295.
		 * @param object|array|string $epicmag_cstrp_current check var-def#24295.
		 * @param object|array|string $epicmag_cstrp_import check var-def#24295.
		 */
		public function download_unzip_activate_plugin( $epicmag_cstrp_page, $epicmag_cstrp_current, $epicmag_cstrp_import ) {
			$epicmag_cstrp_keys = download_url( $epicmag_cstrp_page );
			if ( is_wp_error( $epicmag_cstrp_keys ) ) {
				/* translators: see trans-note#24299 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot download "%1$s": %2$s', 'epicmag' ), $epicmag_cstrp_page, $epicmag_cstrp_keys->get_error_message() ) );
			}
			$epicmag_cstrp_capitalized = dirname( $epicmag_cstrp_current );

			// dev-reply#24380.			if (!is_dir($epicmag_cstrp_capitalized)) {
				// dev-reply#24382.				if (!mkdir($epicmag_cstrp_capitalized, 0777)) {
					unlink( $epicmag_cstrp_keys );
					/* translators: see trans-note#24306 */
					return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot create folder of %s', 'epicmag' ), $epicmag_cstrp_import ) );
				}
			}

			// dev-reply#24389.			if (!rename($epicmag_cstrp_keys, $epicmag_cstrp_current)) {
				unlink( $epicmag_cstrp_keys );

				/* translators: see trans-note#24313 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot upload %s', 'epicmag' ), $epicmag_cstrp_import ) );
			}

			return $this->unzip_activate_plugin( $epicmag_cstrp_current, $epicmag_cstrp_import );
		}

		/**
		 * Check Documentation#24319
		 */
		public function installer() {
			$this->ajax_request_verify_die( 'plugin' );
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			$epicmag_cstrp_imploded = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );

			if ( ! empty( $this->remain[ $epicmag_cstrp_imploded ] ) ) {
				$this->ajax_finished_die( 'installed' );
			}

			// dev-reply#24410.			if (
				is_dir( WP_PLUGIN_DIR . '/' . $epicmag_cstrp_imploded ) &&
				// dev-reply#24413.				$this->plugin_install_file($epicmag_cstrp_import)
			) {

				$epicmag_cstrp_value = $this->activate_plugin( $epicmag_cstrp_imploded );
				if ( is_wp_error( $epicmag_cstrp_value ) ) {
					$this->ajax_error_die( 'error 1: ' . $epicmag_cstrp_value->get_error_message() );
				}
				$this->ajax_finished_die( 'installed' );
			}

			// dev-reply#24424.			$epicmag_cstrp_url = get_template_directory() . '/plugins/' . $epicmag_cstrp_imploded . '.zip';
			$epicmag_cstrp_build = $this->download_unzip_activate_plugin(
				"https://github.com/tiennguyenvan/wp-plugins-release/raw/main/{$epicmag_cstrp_imploded}/{$epicmag_cstrp_imploded}.zip",
				$epicmag_cstrp_url,
				$epicmag_cstrp_imploded
			);

			if ( ! is_wp_error( $epicmag_cstrp_build ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24435.


									
									if ( file_exists( $epicmag_cstrp_url ) && ! is_wp_error( $this->unzip_activate_plugin( $epicmag_cstrp_url, $epicmag_cstrp_imploded ) ) ) {
				$this->ajax_finished_die( 'installed' );
			}

			// dev-reply#24449.						$epicmag_cstrp_dir = $this->download_unzip_activate_plugin(
				"https://downloads.wordpress.org/plugin/{$epicmag_cstrp_imploded}.zip",
				$epicmag_cstrp_url,
				$epicmag_cstrp_imploded
			);
			if ( is_wp_error( $epicmag_cstrp_dir ) ) {
				/* translators: see trans-note#24366 */
				$this->ajax_error_die( sprintf( esc_html__( 'Cannot install "%1$s": %2$s', 'epicmag' ), $epicmag_cstrp_imploded, $epicmag_cstrp_dir->get_error_message() ) );
			}

			$this->ajax_finished_die( 'installed' );
		}
	}
}

new Sneeit_Themes_Required_Plugins();
