<?php
/**
 * DragBlock's Sneeit-core-required-plugins.
 *
 * @package Class sneeit themes required plugins
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// dev-reply#242.
if( ! class_exists( 'Sneeit_Themes_Required_Plugins' ) ) {
/**
	 * Check class-def#244
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
		 * Check Documentation#2415
		 */
		public function __construct() {
			// dev-reply#2430.
			$epicmag_cstrp_items = explode( ', ', EPICMAG_REQUIRED_PLUGINS );
			foreach ( $epicmag_cstrp_items as $epicmag_cstrp_item ) {
				$this->remain[ $epicmag_cstrp_item ] = ''; // dev-reply#2433.
			}
			$epicmag_cstrp_active_plugins = get_option( 'active_plugins' );
			foreach ( $epicmag_cstrp_active_plugins as $epicmag_cstrp_plugin ) {
				$epicmag_cstrp_slug = dirname( $epicmag_cstrp_plugin );
				unset( $this->remain[ $epicmag_cstrp_slug ] );
			}
			// dev-reply#2446.
			if ( count( $this->remain ) === 0 ) {
				return;
			}
			// dev-reply#2452.
			$epicmag_cstrp_theme = wp_get_theme();
			$epicmag_cstrp_theme_update_uri = $epicmag_cstrp_theme->get( 'UpdateURI' );
			// dev-reply#2455.
			$epicmag_cstrp_requires_sneeit_license = ( ! ( empty( $epicmag_cstrp_theme_update_uri ) || 'https://sneeit.com/free' === $epicmag_cstrp_theme_update_uri ) ) && 'https://sneeit.com/' === $epicmag_cstrp_theme_update_uri;
			if ( $epicmag_cstrp_requires_sneeit_license ) {
				$this->admin_redirect = $this->admin_redirect_activate;
			} else {
				$this->admin_redirect = $this->admin_redirect_import;
			}
			// dev-reply#2464.
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 1 );
			add_action( 'switch_theme', array( $this, 'refresh_plugin_update_checker' ), 1 );
			add_action( 'activated_plugin', array( $this, 'refresh_theme_update_checker' ), 1 );
			add_action( 'deactivated_plugin', array( $this, 'refresh_theme_update_checker' ), 1 );
			add_action( 'deactivated_plugin', array( $this, 'refresh_theme_update_checker' ), 1 );
			add_action( 'admin_footer', array( $this, 'refresh_update_checker' ), 1 );
			// dev-reply#2475.
			$this->ajax_slug = str_replace( '-', '_', $this->sub_slug );
			add_action( 'wp_ajax_nopriv_' . $this->sub_slug, array( $this, 'installer' ) );
			add_action( 'wp_ajax_' . $this->sub_slug, array( $this, 'installer' ) );
		}
		/**
		 * Check Documentation#2455
		 */
		public function refresh_theme_update_checker() {
			delete_site_transient( 'update_plugins' );
			delete_transient( 'update_plugins' );
		}
		/**
		 * Check Documentation#2460
		 */
		public function refresh_plugin_update_checker() {
			delete_site_transient( 'update_plugins' );
			delete_transient( 'update_plugins' );
		}
		/**
		 * Check Documentation#2465
		 */
		public function refresh_update_checker() {
			if ( empty( get_transient( $this->checker ) ) ) {
				delete_site_transient( 'update_plugins' );
				delete_transient( 'update_plugins' );
				delete_site_transient( 'update_plugins' );
				delete_transient( 'update_plugins' );
				set_transient( $this->checker, true, 60 * 60 * 24 ); // dev-reply#24109.
			}
		}
		/**
		 * Check Documentation#2475
		 */
		public function admin_menu() {
			// dev-reply#24120.
			if ( empty( $GLOBALS['admin_page_hooks'][ $this->admin_slug ] ) ) {
				// dev-reply#24122.
				add_menu_page(
					esc_html__( 'Sneeit Core', 'epicmag' ), // dev-reply#24124.
					esc_html__( 'Sneeit Core', 'epicmag' ), // dev-reply#24125.
					'manage_options', // dev-reply#24126.
					$this->admin_slug, // dev-reply#24127.
					array( $this, 'add_submenu_page' ), // dev-reply#24128.
					get_template_directory_uri() . '/assets/images/sneeit-logo-16.png', // dev-reply#24129.
					6 // dev-reply#24130.
				);
			}
			global $menu;
			foreach ( $menu as $epicmag_cstrp_key => $epicmag_cstrp_value ) {
				if ( ! empty( $epicmag_cstrp_value[2] ) && $epicmag_cstrp_value[2] === $this->admin_slug ) {
					$menu[ $epicmag_cstrp_key ][0] .= ' <span class="awaiting-mod">' . count( $this->remain ) . '</span>';
					break;
				}
			}
			// dev-reply#24143.
			$epicmag_cstrp_current_theme = wp_get_theme();
			$this->theme_name = $epicmag_cstrp_current_theme->get( 'Name' );
			add_submenu_page(
				$this->admin_slug, // dev-reply#24148.
				$this->admin_slug, // dev-reply#24149.
				$this->theme_name . ' ' . esc_html__( 'Plugins', 'epicmag' ) . ' <span class="awaiting-mod">' . count( $this->remain ) . '</span>', // dev-reply#24150.
				'manage_options', // dev-reply#24151.
				$this->sub_slug, // dev-reply#24152.
				array( $this, 'add_submenu_page' ) // dev-reply#24153.
			);
			remove_submenu_page( $this->admin_slug, $this->admin_slug );
		}
		/**
		 * Check Documentation#24110
		 */
		public function add_submenu_page() {
			echo '<div class="app ' . esc_attr( $this->sub_slug ) . '"></div>';
		}
		/**
		 * Check Documentation#24114
		 */
		public function admin_notices() {
			if ( ! empty( $_GET['page'] ) ) {
				$epicmag_cstrp_page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
				// dev-reply#24175.
				if ( ( $epicmag_cstrp_page ) === $this->sub_slug ) {
					return;
				}
			}
			$epicmag_cstrp_keys = array_keys( $this->remain );
			$epicmag_cstrp_capitalized_keys = array_map( 'ucfirst', $epicmag_cstrp_keys );
			$epicmag_cstrp_imploded_keys = implode( ', ', $epicmag_cstrp_capitalized_keys );
			echo '<section><div class="notice notice-large notice-warning is-dismissible">';
			echo '<h2 class="notice-title">';
			/* translators: see trans-note#24128 */
			echo sprintf( esc_html__( 'Missing required plugins for %s theme', 'epicmag' ), esc_html( $this->theme_name ) );
			echo '</h2>';
			echo '<p>';
			/* translators: see trans-note#24131 */
			echo sprintf( esc_html__( '%s requires following plugins to work: ', 'epicmag' ), esc_html( $this->theme_name ) ) . '<strong>' . esc_html( $epicmag_cstrp_imploded_keys ) . '</strong>';
			echo '</p>';
			echo '<p>';
			echo '<a class="button button-large button-primary" href="' . esc_attr( menu_page_url( esc_attr( $this->sub_slug ), false ) ) . '">';
			echo esc_html__( 'Please Install Required Plugins', 'epicmag' );
			echo '</a>';
			echo '</p>';
			echo '</div></section>';
		}
		/**
		 * Check Documentation#24140
		 */
		public function admin_enqueue_scripts() {
			if ( empty( $_GET['page'] ) ) {
				return;
			}
			// dev-reply#24209.
			$epicmag_cstrp_page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			if ( ( $epicmag_cstrp_page ) !== $this->sub_slug ) {
				return;
			}
			// dev-reply#24215.
			$epicmag_cstrp_theme_url = get_template_directory_uri();
			$epicmag_cstrp_build_dir = '/build/applications/';
			// dev-reply#24219.
			$epicmag_cstrp_asset_path = get_template_directory() . $epicmag_cstrp_build_dir . $this->sub_slug . '/client/index.asset.php';
			if ( ! file_exists( $epicmag_cstrp_asset_path ) ) {
				return;
			}
			// dev-reply#24224.
			$epicmag_cstrp_asset_file = include $epicmag_cstrp_asset_path;
			// dev-reply#24228.
			wp_enqueue_style( $this->sub_slug, $epicmag_cstrp_theme_url . $epicmag_cstrp_build_dir . $this->sub_slug . '/client/style-index.css', null, time() );
			// dev-reply#24231.
			array_push( $epicmag_cstrp_asset_file['dependencies'], 'wp-i18n', 'jquery' );
			wp_enqueue_script( $this->sub_slug, $epicmag_cstrp_theme_url . $epicmag_cstrp_build_dir . $this->sub_slug . '/client/index.js', $epicmag_cstrp_asset_file['dependencies'], time(), true );
			wp_localize_script( $this->sub_slug, 'sneeitCoreRequiredPlugins', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'sneeitCoreUrl' => admin_url( 'admin.php?page=' . $this->admin_redirect ),
				'nonce'   => wp_create_nonce( $this->sub_slug ),
				'screenshot' => get_template_directory_uri() . '/screenshot.png',
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
		// dev-reply#24251.
		/**
		 * Check Documentation#24182
		 *
		 * @param object|array|string $epicmag_cstrp_text check var-def#24182.
		 */
		public function ajax_error_die( $epicmag_cstrp_text ) {
			echo json_encode( array( 'error' => $epicmag_cstrp_text ) );
			die();
		}
		/**
		 * Check Documentation#24187
		 *
		 * @param object|array|string $epicmag_cstrp_text check var-def#24187.
		 */
		public function ajax_finished_die( $epicmag_cstrp_text ) {
			echo json_encode( $epicmag_cstrp_text );
			die();
		}
		/**
		 * Check Documentation#24192
		 *
		 * @param object|array|string $epicmag_cstrp_fields check var-def#24192.
		 */
		public function ajax_request_verify_die( $epicmag_cstrp_fields = array() ) {
			if ( empty( $_POST['nonce'] ) ) {
				$this->ajax_error_die( esc_html__( 'empty nonce', 'epicmag' ) );
			}
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), $this->sub_slug ) ) {
				$this->ajax_error_die( esc_html__( 'Timeout! Please reload the page.', 'epicmag' ) );
			}
			if ( is_string( $epicmag_cstrp_fields ) ) {
				$epicmag_cstrp_fields = explode( ',', $epicmag_cstrp_fields );
			}
			if ( ! empty( $epicmag_cstrp_fields ) ) {
				foreach ( $epicmag_cstrp_fields as $epicmag_cstrp_field ) {
					$epicmag_cstrp_field = trim( $epicmag_cstrp_field );
					if ( empty( $_POST[ $epicmag_cstrp_field ] ) ) {
						/* translators: see trans-note#24207 */
						$this->ajax_error_die( sprintf( esc_html__( 'Missing required field: %s', 'epicmag' ), $epicmag_cstrp_field ) );
					}
				}
			}
		}
		/**
		 * Check Documentation#24212
		 *
		 * @param object|array|string $epicmag_cstrp_slug check var-def#24212.
		 */
		public function plugin_install_file( $epicmag_cstrp_slug ) {
			// dev-reply#24287.
			$epicmag_cstrp_plugin_path = WP_PLUGIN_DIR . '/' . $epicmag_cstrp_slug . '/';
			if ( file_exists( $epicmag_cstrp_plugin_path . $epicmag_cstrp_slug . '.php' ) ) {
				$epicmag_cstrp_file_content = file_get_contents( $epicmag_cstrp_plugin_path . $epicmag_cstrp_slug . '.php' );
				if ( $epicmag_cstrp_file_content && strpos( $epicmag_cstrp_file_content, 'Plugin Name:' ) !== false ) {
					return $epicmag_cstrp_plugin_path . $epicmag_cstrp_slug . '.php';
				}
			}
			// dev-reply#24296.
			$epicmag_cstrp_file_paths = glob( WP_PLUGIN_DIR . '/' . $epicmag_cstrp_slug . '/*.php' );
			foreach ( $epicmag_cstrp_file_paths as $epicmag_cstrp_file_path ) {
				$epicmag_cstrp_file_content = file_get_contents( $epicmag_cstrp_file_path );
				if ( $epicmag_cstrp_file_content && strpos( $epicmag_cstrp_file_content, 'Plugin Name:' ) !== false ) {
					return $epicmag_cstrp_file_path;
				}
			}
			return '';
		}
		/**
		 * Check Documentation#24232
		 *
		 * @param object|array|string $epicmag_cstrp_slug check var-def#24232.
		 */
		public function activate_plugin( $epicmag_cstrp_slug ) {
			$epicmag_cstrp_plugin_file = $this->plugin_install_file( $epicmag_cstrp_slug );
			if ( ! $epicmag_cstrp_plugin_file ) {
				/* translators: see trans-note#24236 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid file', 'epicmag' ), $epicmag_cstrp_slug ) );
			}
			// dev-reply#24314.
			wp_cache_delete( 'plugins', 'plugins' );
			$epicmag_cstrp_active = activate_plugin( $epicmag_cstrp_plugin_file );
			if ( is_wp_error( $epicmag_cstrp_active ) ) {
				/* translators: see trans-note#24242 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot active "%1$s": file %2$s %3$s', 'epicmag' ), $epicmag_cstrp_slug, $epicmag_cstrp_plugin_file, $epicmag_cstrp_active->get_error_message() ) );
			}
			return true;
		}
		/**
		 * Check Documentation#24246
		 *
		 * @param object|array|string $epicmag_cstrp_path check var-def#24246.
		 * @param object|array|string $epicmag_cstrp_slug check var-def#24246.
		 */
		public function unzip_activate_plugin( $epicmag_cstrp_path, $epicmag_cstrp_slug ) {
			// dev-reply#24329.
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			$epicmag_cstrp_unzip = unzip_file( $epicmag_cstrp_path, WP_PLUGIN_DIR );
			if ( is_wp_error( $epicmag_cstrp_unzip ) ) {
				/* translators: see trans-note#24253 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot unzip "%1$s": %2$s', 'epicmag' ), $epicmag_cstrp_slug, $epicmag_cstrp_unzip->get_error_message() ) );
			}
			if ( ! is_dir( WP_PLUGIN_DIR . '/' . $epicmag_cstrp_slug ) ) {
				/* translators: see trans-note#24256 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid slug', 'epicmag' ), $epicmag_cstrp_slug ) );
			}
			return $this->activate_plugin( $epicmag_cstrp_slug );
		}
		/**
		 * Check Documentation#24260
		 *
		 * @param object|array|string $epicmag_cstrp_url check var-def#24260.
		 * @param object|array|string $epicmag_cstrp_path check var-def#24260.
		 * @param object|array|string $epicmag_cstrp_slug check var-def#24260.
		 */
		public function download_unzip_activate_plugin( $epicmag_cstrp_url, $epicmag_cstrp_path, $epicmag_cstrp_slug ) {
			$epicmag_cstrp_download = download_url( $epicmag_cstrp_url );
			if ( is_wp_error( $epicmag_cstrp_download ) ) {
				/* translators: see trans-note#24264 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot download "%1$s": %2$s', 'epicmag' ), $epicmag_cstrp_url, $epicmag_cstrp_download->get_error_message() ) );
			}
			$epicmag_cstrp_dir = dirname( $epicmag_cstrp_path );
			// dev-reply#24353.
			if ( ! is_dir( $epicmag_cstrp_dir ) ) {
				// dev-reply#24355.
				if ( ! mkdir( $epicmag_cstrp_dir, 0777 ) ) {
					unlink( $epicmag_cstrp_download );
					/* translators: see trans-note#24272 */
					return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot create folder of %s', 'epicmag' ), $epicmag_cstrp_slug ) );
				}
			}
			// dev-reply#24362.
			if ( ! rename( $epicmag_cstrp_download, $epicmag_cstrp_path ) ) {
				unlink( $epicmag_cstrp_download );
				/* translators: see trans-note#24278 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot upload %s', 'epicmag' ), $epicmag_cstrp_slug ) );
			}
			return $this->unzip_activate_plugin( $epicmag_cstrp_path, $epicmag_cstrp_slug );
		}
		/**
		 * Check Documentation#24282
		 */
		public function installer() {
			$this->ajax_request_verify_die( 'plugin' );
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$epicmag_cstrp_plugin_slug = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );
			if ( ! empty( $this->remain[ $epicmag_cstrp_plugin_slug ] ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24383.
			if ( is_dir( WP_PLUGIN_DIR . '/' . $epicmag_cstrp_plugin_slug ) ) {
				$epicmag_cstrp_active = $this->activate_plugin( $epicmag_cstrp_plugin_slug );
				if ( is_wp_error( $epicmag_cstrp_active ) ) {
					$this->ajax_error_die( $epicmag_cstrp_active->get_error_message() );
				}
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24392.
			$epicmag_cstrp_local = get_template_directory() . '/plugins/' . $epicmag_cstrp_plugin_slug . '.zip';
			$epicmag_cstrp_github_install = $this->download_unzip_activate_plugin(
				"https://github.com/tiennguyenvan/wp-plugins-release/raw/main/{$epicmag_cstrp_plugin_slug}/{$epicmag_cstrp_plugin_slug}.zip",
				$epicmag_cstrp_local,
				$epicmag_cstrp_plugin_slug
			);
			if ( ! is_wp_error( $epicmag_cstrp_github_install ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24404.
			if ( file_exists( $epicmag_cstrp_local ) && ! is_wp_error( $this->unzip_activate_plugin( $epicmag_cstrp_local, $epicmag_cstrp_plugin_slug ) ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24414.
			$epicmag_cstrp_wp_install = $this->download_unzip_activate_plugin(
				"https://downloads.wordpress.org/plugin/{$epicmag_cstrp_plugin_slug}.zip",
				$epicmag_cstrp_local,
				$epicmag_cstrp_plugin_slug
			);
			if ( is_wp_error( $epicmag_cstrp_wp_install ) ) {
				/* translators: see trans-note#24319 */
				$this->ajax_error_die( sprintf( esc_html__( 'Cannot install "%1$s": %2$s', 'epicmag' ), $epicmag_cstrp_plugin_slug, $epicmag_cstrp_wp_install->get_error_message() ) );
			}
			$this->ajax_finished_die( 'installed' );
		}
	}
}
new Sneeit_Themes_Required_Plugins();
