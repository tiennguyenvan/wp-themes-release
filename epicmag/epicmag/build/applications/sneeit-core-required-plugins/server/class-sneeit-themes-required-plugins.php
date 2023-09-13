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
if( ! class_exists( 'Sneeit_Themes_Required_Plugin_Installer' ) ) {
/**
	 * Check class-def#244
	 */
	class Sneeit_Themes_Required_Plugin_Installer {
		public $remain = EPICMAG_REQUIRED_PLUGINS;
		public $admin_slug = 'sneeit-core';
		public $admin_redirect = 'sneeit-core-import';
		public $admin_redirect_activate = 'sneeit-core-activate';
		public $admin_redirect_import = 'sneeit-core-import';
		public $sub_slug = 'sneeit-core-required-plugins';
		public $ajax_slug = '';
		public $theme_name = '';
		/**
		 * Check Documentation#2414
		 */
		public function __construct() {
			// dev-reply#2429.
			$epicmag_0 = get_option( 'active_plugins' );
			foreach ( $epicmag_0 as $epicmag_1 ) {
				$epicmag_2 = dirname( $epicmag_1 );
				unset( $this->remain[ $epicmag_2 ] );
			}
			// dev-reply#2439.
			if ( count( $this->remain ) === 0 ) {
				return;
			}
			// dev-reply#2445.
			$epicmag_3 = wp_get_theme();
			$epicmag_4 = $epicmag_3->get( 'UpdateURI' );
			// dev-reply#2448.
			$epicmag_5 = ( ! ( empty( $epicmag_4 ) || 'https://sneeit.com/free' === $epicmag_4 ) ) && 'https://sneeit.com/' === $epicmag_4;
			if ( $epicmag_5 ) {
				$this->admin_redirect = $this->admin_redirect_activate;
			} else {
				$this->admin_redirect = $this->admin_redirect_import;
			}
			// dev-reply#2457.
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 1 );
			add_action( 'switch_theme', array( $this, 'refresh_update_checker' ), 1 );
			add_action( 'activated_plugin', array( $this, 'refresh_update_checker' ), 1 );
			add_action( 'deactivated_plugin', array( $this, 'refresh_update_checker' ), 1 );
			// dev-reply#2464.
			$this->ajax_slug = str_replace( '-', '_', $this->sub_slug );
			add_action( 'wp_ajax_nopriv_' . $this->sub_slug, array( $this, 'installer' ) );
			add_action( 'wp_ajax_' . $this->sub_slug, array( $this, 'installer' ) );
		}
		/**
		 * Check Documentation#2448
		 */
		public function refresh_update_checker() {
			delete_site_transient( 'update_themes' );
			delete_transient( 'update_themes' );
			delete_site_transient( 'update_plugins' );
			delete_transient( 'update_plugins' );
		}
		/**
		 * Check Documentation#2455
		 */
		public function admin_menu() {
			// dev-reply#2487.
			if ( empty( $GLOBALS['admin_page_hooks'][ $this->admin_slug ] ) ) {
				// dev-reply#2489.
				add_menu_page(
					esc_html__( 'Sneeit Core', 'epicmag' ), // dev-reply#2491.
					esc_html__( 'Sneeit Core', 'epicmag' ), // dev-reply#2492.
					'manage_options', // dev-reply#2493.
					$this->admin_slug, // dev-reply#2494.
					array( $this, 'add_submenu_page' ), // dev-reply#2495.
					get_template_directory_uri() . '/assets/images/sneeit-logo-16.png', // dev-reply#2496.
					6 // dev-reply#2497.
				);
			}
			global $menu;
			foreach ( $menu as $epicmag_6 => $epicmag_7 ) {
				if ( ! empty( $epicmag_7[2] ) && $epicmag_7[2] === $this->admin_slug ) {
					$menu[ $epicmag_6 ][0] .= ' <span class="awaiting-mod">' . count( $this->remain ) . '</span>';
					break;
				}
			}
			// dev-reply#24110.
			$epicmag_8 = wp_get_theme();
			$this->theme_name = $epicmag_8->get( 'Name' );
			add_submenu_page(
				$this->admin_slug, // dev-reply#24115.
				$this->admin_slug, // dev-reply#24116.
				$this->theme_name . ' ' . esc_html__( 'Plugins', 'epicmag' ) . ' <span class="awaiting-mod">' . count( $this->remain ) . '</span>', // dev-reply#24117.
				'manage_options', // dev-reply#24118.
				$this->sub_slug, // dev-reply#24119.
				array( $this, 'add_submenu_page' ) // dev-reply#24120.
			);
			remove_submenu_page( $this->admin_slug, $this->admin_slug );
		}
		/**
		 * Check Documentation#2490
		 */
		public function add_submenu_page() {
			echo '<div class="app ' . $this->sub_slug . '"></div>';
		}
		/**
		 * Check Documentation#2494
		 */
		public function admin_notices() {
			if ( ! empty( $_GET['page'] ) ) {
				$epicmag_9 = $_GET['page'];
				// dev-reply#24142.
				if ( ( $epicmag_9 ) === $this->sub_slug ) {
					return;
				}
			}
			$epicmag_10 = array_keys( $this->remain );
			$epicmag_11 = array_map( 'ucfirst', $epicmag_10 );
			$epicmag_12 = implode( ', ', $epicmag_11 );
			echo '<section><div class="notice notice-large notice-warning is-dismissible">';
			echo '<h2 class="notice-title">';
			/* translators: see trans-note#24108 */
			echo sprintf( esc_html__( 'Missing required plugins for %s theme', 'epicmag' ), $this->theme_name );
			echo '</h2>';
			echo '<p>';
			/* translators: see trans-note#24111 */
			echo sprintf( esc_html__( '%s requires following plugins to work: <strong>%s</strong>', 'epicmag' ), $this->theme_name, $epicmag_12 );
			echo '</p>';
			echo '<p>';
			echo '<a class="button button-large button-primary" href="' . menu_page_url( $this->sub_slug, false ) . '">';
			echo esc_html__( 'Please Install and Active', 'epicmag' );
			echo '</a>';
			echo '</p>';
			echo '</div></section>';
		}
		/**
		 * Check Documentation#24120
		 */
		public function admin_enqueue_scripts() {
			if ( empty( $_GET['page'] ) ) {
				return;
			}
			// dev-reply#24176.
			$epicmag_9 = $_GET['page'];
			if ( ( $epicmag_9 ) !== $this->sub_slug ) {
				return;
			}
			// dev-reply#24182.
			$epicmag_13 = get_template_directory_uri();
			$epicmag_14 = '/build/applications/';
			// dev-reply#24186.
			$epicmag_15 = get_template_directory() . $epicmag_14 . $this->sub_slug . '/client/index.asset.php';
			if ( ! file_exists( $epicmag_15 ) ) {
				return;
			}
			// dev-reply#24191.
			$epicmag_16 = include $epicmag_15;
			// dev-reply#24195.
			wp_enqueue_style( $this->sub_slug, $epicmag_13 . $epicmag_14 . $this->sub_slug . '/client/style-index.css', null, time() );
			// dev-reply#24198.
			array_push( $epicmag_16['dependencies'], 'wp-i18n', 'jquery' );
			wp_enqueue_script( $this->sub_slug, $epicmag_13 . $epicmag_14 . $this->sub_slug . '/client/index.js', $epicmag_16['dependencies'], time(), true );
			wp_localize_script( $this->sub_slug, 'sneeitCoreRequiredPlugins', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'sneeitCoreUrl' => admin_url( 'admin.php?page=' . $this->admin_redirect ),
				'nonce'   => wp_create_nonce( $this->sub_slug ),
				'screenshot' => get_template_directory_uri() . '/screenshot.png',
				'text' => array(
					'finished' => esc_html__( 'Finished', 'epicmag' ),
					'title' => esc_html__( 'Required Plugins for ', 'epicmag' ) . $this->theme_name,
					'button' => esc_html__( 'Install and Activate', 'epicmag' ),
					'redirecting' => esc_html__( 'Redirecting ...', 'epicmag' ),
					'error' => esc_html__( 'WordPress Server Error', 'epicmag' )
				),
				'plugins' => $this->remain,
			) );
		}
		// dev-reply#24217.
		/**
		 * Check Documentation#24161
		 *
		 * @param object|array|string $epicmag_17 check var-def#24161.
		 */
		public function ajax_error_die( $epicmag_17 ) {
			echo json_encode( array( 'error' => $epicmag_17 ) );
			die();
		}
		/**
		 * Check Documentation#24166
		 *
		 * @param object|array|string $epicmag_17 check var-def#24166.
		 */
		public function ajax_finished_die( $epicmag_17 ) {
			echo json_encode( $epicmag_17 );
			die();
		}
		/**
		 * Check Documentation#24171
		 *
		 * @param object|array|string $epicmag_18 check var-def#24171.
		 */
		public function ajax_request_verify_die( $epicmag_18 = array() ) {
			if ( empty( $_POST['nonce'] ) ) {
				$this->ajax_error_die( esc_html__( 'empty nonce', 'epicmag' ) );
			}
			if ( ! wp_verify_nonce( $_POST['nonce'], $this->sub_slug ) ) {
				$this->ajax_error_die( esc_html__( 'Timeout! Please reload the page.', 'epicmag' ) );
			}
			if ( is_string( $epicmag_18 ) ) {
				$epicmag_18 = explode( ',', $epicmag_18 );
			}
			if ( ! empty( $epicmag_18 ) ) {
				foreach ( $epicmag_18 as $epicmag_19 ) {
					$epicmag_19 = trim( $epicmag_19 );
					if ( empty( $_POST[ $epicmag_19 ] ) ) {
						/* translators: see trans-note#24186 */
						$this->ajax_error_die( sprintf( esc_html__( 'Missing required field: %s', $epicmag_19 ) ) );
					}
				}
			}
		}
		/**
		 * Check Documentation#24191
		 *
		 * @param object|array|string $epicmag_2 check var-def#24191.
		 */
		public function plugin_install_file( $epicmag_2 ) {
			// dev-reply#24253.
			$epicmag_20 = WP_PLUGIN_DIR . '/' . $epicmag_2 . '/';
			if ( file_exists( $epicmag_20 . $epicmag_2 . '.php' ) ) {
				$epicmag_21 = file_get_contents( $epicmag_20 . $epicmag_2 . '.php' );
				if ( $epicmag_21 && strpos( $epicmag_21, 'Plugin Name:' ) !== false ) {
					return $epicmag_20 . $epicmag_2 . '.php';
				}
			}
			// dev-reply#24262.
			$epicmag_22 = glob( WP_PLUGIN_DIR . '/' . $epicmag_2 . '/*.php' );
			foreach ( $epicmag_22 as $epicmag_23 ) {
				$epicmag_21 = file_get_contents( $epicmag_23 );
				if ( $epicmag_21 && strpos( $epicmag_21, 'Plugin Name:' ) !== false ) {
					return $epicmag_23;
				}
			}
			return '';
		}
		/**
		 * Check Documentation#24211
		 *
		 * @param object|array|string $epicmag_2 check var-def#24211.
		 */
		public function activate_plugin( $epicmag_2 ) {
			$epicmag_24 = $this->plugin_install_file( $epicmag_2 );
			if ( ! $epicmag_24 ) {
				/* translators: see trans-note#24215 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid file', 'epicmag' ), $epicmag_2 ) );
			}
			// dev-reply#24280.
			wp_cache_delete( 'plugins', 'plugins' );
			$epicmag_25 = activate_plugin( $epicmag_24 );
			if ( is_wp_error( $epicmag_25 ) ) {
				/* translators: see trans-note#24221 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot active "%1$s": file %2$s %3$s', 'epicmag' ), $epicmag_2, $epicmag_24, $epicmag_25->get_error_message() ) );
			}
			return true;
		}
		/**
		 * Check Documentation#24225
		 *
		 * @param object|array|string $epicmag_26 check var-def#24225.
		 * @param object|array|string $epicmag_2 check var-def#24225.
		 */
		public function unzip_activate_plugin( $epicmag_26, $epicmag_2 ) {
			// dev-reply#24295.
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			$epicmag_27 = unzip_file( $epicmag_26, WP_PLUGIN_DIR );
			if ( is_wp_error( $epicmag_27 ) ) {
				/* translators: see trans-note#24232 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot unzip "%1$s": %2$s', 'epicmag' ), $epicmag_2, $epicmag_27->get_error_message() ) );
			}
			if ( ! is_dir( WP_PLUGIN_DIR . '/' . $epicmag_2 ) ) {
				/* translators: see trans-note#24235 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid slug', 'epicmag' ), $epicmag_2 ) );
			}
			return $this->activate_plugin( $epicmag_2 );
		}
		/**
		 * Check Documentation#24239
		 *
		 * @param object|array|string $epicmag_28 check var-def#24239.
		 * @param object|array|string $epicmag_26 check var-def#24239.
		 * @param object|array|string $epicmag_2 check var-def#24239.
		 */
		public function download_unzip_activate_plugin( $epicmag_28, $epicmag_26, $epicmag_2 ) {
			$epicmag_29 = download_url( $epicmag_28 );
			if ( is_wp_error( $epicmag_29 ) ) {
				/* translators: see trans-note#24243 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot download "%1$s": %2$s', 'epicmag' ), $epicmag_28, $epicmag_29->get_error_message() ) );
			}
			$epicmag_30 = dirname( $epicmag_26 );
			// dev-reply#24319.
			if ( ! is_dir( $epicmag_30 ) ) {
				// dev-reply#24321.
				if ( ! mkdir( $epicmag_30, 0777 ) ) {
					unlink( $epicmag_29 );
					/* translators: see trans-note#24251 */
					return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot create folder of %s', 'epicmag' ), $epicmag_2 ) );
				}
			}
			// dev-reply#24328.
			if ( ! rename( $epicmag_29, $epicmag_26 ) ) {
				unlink( $epicmag_29 );
				/* translators: see trans-note#24257 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot upload %s', 'epicmag' ), $epicmag_2 ) );
			}
			return $this->unzip_activate_plugin( $epicmag_26, $epicmag_2 );
		}
		/**
		 * Check Documentation#24261
		 */
		public function installer() {
			$this->ajax_request_verify_die( 'plugin' );
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$epicmag_31 = $_POST['plugin'];
			if ( empty( $this->remain[ $epicmag_31 ] ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24348.
			if ( is_dir( WP_PLUGIN_DIR . '/' . $epicmag_31 ) ) {
				$epicmag_25 = $this->activate_plugin( $epicmag_31 );
				if ( is_wp_error( $epicmag_25 ) ) {
					$this->ajax_error_die( $epicmag_25->get_error_message() );
				}
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24357.
			$epicmag_32 = get_template_directory() . '/plugins/' . $epicmag_31 . '.zip';
			$epicmag_33 = $this->download_unzip_activate_plugin(
				"https://github.com/tiennguyenvan/wp-plugins-release/raw/main/{$epicmag_31}/{$epicmag_31}.zip",
				$epicmag_32,
				$epicmag_31
			);
			if ( ! is_wp_error( $epicmag_33 ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24369.
			if ( file_exists( $epicmag_32 ) && ! is_wp_error( $this->unzip_activate_plugin( $epicmag_32, $epicmag_31 ) ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24379.
			$epicmag_34 = $this->download_unzip_activate_plugin(
				"https://downloads.wordpress.org/plugin/{$epicmag_31}.zip",
				$epicmag_32,
				$epicmag_31
			);
			if ( is_wp_error( $epicmag_34 ) ) {
				/* translators: see trans-note#24298 */
				$this->ajax_error_die( sprintf( esc_html__( 'Cannot install "%1$s": %2$s', 'epicmag' ), $epicmag_31, $epicmag_34->get_error_message() ) );
			}
			$this->ajax_finished_die( 'installed' );
		}
	}
}
new Sneeit_Themes_Required_Plugin_Installer();
