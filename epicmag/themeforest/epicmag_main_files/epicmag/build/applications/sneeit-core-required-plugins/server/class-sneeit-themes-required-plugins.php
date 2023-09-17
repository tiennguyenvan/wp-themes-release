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
			$epicmag_0 = explode( ', ', EPICMAG_REQUIRED_PLUGINS );
			foreach ( $epicmag_0 as $epicmag_1 ) {
				$this->remain[ $epicmag_1 ] = ''; // dev-reply#2433.
			}
			$epicmag_2 = get_option( 'active_plugins' );
			foreach ( $epicmag_2 as $epicmag_3 ) {
				$epicmag_4 = dirname( $epicmag_3 );
				unset( $this->remain[ $epicmag_4 ] );
			}
			// dev-reply#2446.
			if ( count( $this->remain ) === 0 ) {
				return;
			}
			// dev-reply#2452.
			$epicmag_5 = wp_get_theme();
			$epicmag_6 = $epicmag_5->get( 'UpdateURI' );
			// dev-reply#2455.
			$epicmag_7 = ( ! ( empty( $epicmag_6 ) || 'https://sneeit.com/free' === $epicmag_6 ) ) && 'https://sneeit.com/' === $epicmag_6;
			if ( $epicmag_7 ) {
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
			foreach ( $menu as $epicmag_8 => $epicmag_9 ) {
				if ( ! empty( $epicmag_9[2] ) && $epicmag_9[2] === $this->admin_slug ) {
					$menu[ $epicmag_8 ][0] .= ' <span class="awaiting-mod">' . count( $this->remain ) . '</span>';
					break;
				}
			}
			// dev-reply#24143.
			$epicmag_10 = wp_get_theme();
			$this->theme_name = $epicmag_10->get( 'Name' );
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
			echo '<div class="app ' . $this->sub_slug . '"></div>';
		}
		/**
		 * Check Documentation#24114
		 */
		public function admin_notices() {
			if ( ! empty( $_GET['page'] ) ) {
				$epicmag_11 = $_GET['page'];
				// dev-reply#24175.
				if ( ( $epicmag_11 ) === $this->sub_slug ) {
					return;
				}
			}
			$epicmag_12 = array_keys( $this->remain );
			$epicmag_13 = array_map( 'ucfirst', $epicmag_12 );
			$epicmag_14 = implode( ', ', $epicmag_13 );
			echo '<section><div class="notice notice-large notice-warning is-dismissible">';
			echo '<h2 class="notice-title">';
			/* translators: see trans-note#24128 */
			echo sprintf( esc_html__( 'Missing required plugins for %s theme', 'epicmag' ), $this->theme_name );
			echo '</h2>';
			echo '<p>';
			/* translators: see trans-note#24131 */
			echo sprintf( esc_html__( '%s requires following plugins to work: ', 'epicmag' ), $this->theme_name ) . '<strong>' . $epicmag_14 . '</strong>';
			echo '</p>';
			echo '<p>';
			echo '<a class="button button-large button-primary" href="' . menu_page_url( $this->sub_slug, false ) . '">';
			echo esc_html__( 'Please Install and Activate', 'epicmag' );
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
			$epicmag_11 = $_GET['page'];
			if ( ( $epicmag_11 ) !== $this->sub_slug ) {
				return;
			}
			// dev-reply#24215.
			$epicmag_15 = get_template_directory_uri();
			$epicmag_16 = '/build/applications/';
			// dev-reply#24219.
			$epicmag_17 = get_template_directory() . $epicmag_16 . $this->sub_slug . '/client/index.asset.php';
			if ( ! file_exists( $epicmag_17 ) ) {
				return;
			}
			// dev-reply#24224.
			$epicmag_18 = include $epicmag_17;
			// dev-reply#24228.
			wp_enqueue_style( $this->sub_slug, $epicmag_15 . $epicmag_16 . $this->sub_slug . '/client/style-index.css', null, time() );
			// dev-reply#24231.
			array_push( $epicmag_18['dependencies'], 'wp-i18n', 'jquery' );
			wp_enqueue_script( $this->sub_slug, $epicmag_15 . $epicmag_16 . $this->sub_slug . '/client/index.js', $epicmag_18['dependencies'], time(), true );
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
		 * @param object|array|string $epicmag_19 check var-def#24182.
		 */
		public function ajax_error_die( $epicmag_19 ) {
			echo json_encode( array( 'error' => $epicmag_19 ) );
			die();
		}
		/**
		 * Check Documentation#24187
		 *
		 * @param object|array|string $epicmag_19 check var-def#24187.
		 */
		public function ajax_finished_die( $epicmag_19 ) {
			echo json_encode( $epicmag_19 );
			die();
		}
		/**
		 * Check Documentation#24192
		 *
		 * @param object|array|string $epicmag_20 check var-def#24192.
		 */
		public function ajax_request_verify_die( $epicmag_20 = array() ) {
			if ( empty( $_POST['nonce'] ) ) {
				$this->ajax_error_die( esc_html__( 'empty nonce', 'epicmag' ) );
			}
			if ( ! wp_verify_nonce( $_POST['nonce'], $this->sub_slug ) ) {
				$this->ajax_error_die( esc_html__( 'Timeout! Please reload the page.', 'epicmag' ) );
			}
			if ( is_string( $epicmag_20 ) ) {
				$epicmag_20 = explode( ',', $epicmag_20 );
			}
			if ( ! empty( $epicmag_20 ) ) {
				foreach ( $epicmag_20 as $epicmag_21 ) {
					$epicmag_21 = trim( $epicmag_21 );
					if ( empty( $_POST[ $epicmag_21 ] ) ) {
						/* translators: see trans-note#24207 */
						$this->ajax_error_die( sprintf( esc_html__( 'Missing required field: %s', $epicmag_21 ) ) );
					}
				}
			}
		}
		/**
		 * Check Documentation#24212
		 *
		 * @param object|array|string $epicmag_4 check var-def#24212.
		 */
		public function plugin_install_file( $epicmag_4 ) {
			// dev-reply#24287.
			$epicmag_22 = WP_PLUGIN_DIR . '/' . $epicmag_4 . '/';
			if ( file_exists( $epicmag_22 . $epicmag_4 . '.php' ) ) {
				$epicmag_23 = file_get_contents( $epicmag_22 . $epicmag_4 . '.php' );
				if ( $epicmag_23 && strpos( $epicmag_23, 'Plugin Name:' ) !== false ) {
					return $epicmag_22 . $epicmag_4 . '.php';
				}
			}
			// dev-reply#24296.
			$epicmag_24 = glob( WP_PLUGIN_DIR . '/' . $epicmag_4 . '/*.php' );
			foreach ( $epicmag_24 as $epicmag_25 ) {
				$epicmag_23 = file_get_contents( $epicmag_25 );
				if ( $epicmag_23 && strpos( $epicmag_23, 'Plugin Name:' ) !== false ) {
					return $epicmag_25;
				}
			}
			return '';
		}
		/**
		 * Check Documentation#24232
		 *
		 * @param object|array|string $epicmag_4 check var-def#24232.
		 */
		public function activate_plugin( $epicmag_4 ) {
			$epicmag_26 = $this->plugin_install_file( $epicmag_4 );
			if ( ! $epicmag_26 ) {
				/* translators: see trans-note#24236 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid file', 'epicmag' ), $epicmag_4 ) );
			}
			// dev-reply#24314.
			wp_cache_delete( 'plugins', 'plugins' );
			$epicmag_27 = activate_plugin( $epicmag_26 );
			if ( is_wp_error( $epicmag_27 ) ) {
				/* translators: see trans-note#24242 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot active "%1$s": file %2$s %3$s', 'epicmag' ), $epicmag_4, $epicmag_26, $epicmag_27->get_error_message() ) );
			}
			return true;
		}
		/**
		 * Check Documentation#24246
		 *
		 * @param object|array|string $epicmag_28 check var-def#24246.
		 * @param object|array|string $epicmag_4 check var-def#24246.
		 */
		public function unzip_activate_plugin( $epicmag_28, $epicmag_4 ) {
			// dev-reply#24329.
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			$epicmag_29 = unzip_file( $epicmag_28, WP_PLUGIN_DIR );
			if ( is_wp_error( $epicmag_29 ) ) {
				/* translators: see trans-note#24253 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot unzip "%1$s": %2$s', 'epicmag' ), $epicmag_4, $epicmag_29->get_error_message() ) );
			}
			if ( ! is_dir( WP_PLUGIN_DIR . '/' . $epicmag_4 ) ) {
				/* translators: see trans-note#24256 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( '"%s" has invalid slug', 'epicmag' ), $epicmag_4 ) );
			}
			return $this->activate_plugin( $epicmag_4 );
		}
		/**
		 * Check Documentation#24260
		 *
		 * @param object|array|string $epicmag_30 check var-def#24260.
		 * @param object|array|string $epicmag_28 check var-def#24260.
		 * @param object|array|string $epicmag_4 check var-def#24260.
		 */
		public function download_unzip_activate_plugin( $epicmag_30, $epicmag_28, $epicmag_4 ) {
			$epicmag_31 = download_url( $epicmag_30 );
			if ( is_wp_error( $epicmag_31 ) ) {
				/* translators: see trans-note#24264 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot download "%1$s": %2$s', 'epicmag' ), $epicmag_30, $epicmag_31->get_error_message() ) );
			}
			$epicmag_32 = dirname( $epicmag_28 );
			// dev-reply#24353.
			if ( ! is_dir( $epicmag_32 ) ) {
				// dev-reply#24355.
				if ( ! mkdir( $epicmag_32, 0777 ) ) {
					unlink( $epicmag_31 );
					/* translators: see trans-note#24272 */
					return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot create folder of %s', 'epicmag' ), $epicmag_4 ) );
				}
			}
			// dev-reply#24362.
			if ( ! rename( $epicmag_31, $epicmag_28 ) ) {
				unlink( $epicmag_31 );
				/* translators: see trans-note#24278 */
				return new WP_Error( 'epicmag-plugin-installer', sprintf( esc_html__( 'Cannot upload %s', 'epicmag' ), $epicmag_4 ) );
			}
			return $this->unzip_activate_plugin( $epicmag_28, $epicmag_4 );
		}
		/**
		 * Check Documentation#24282
		 */
		public function installer() {
			$this->ajax_request_verify_die( 'plugin' );
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			$epicmag_33 = $_POST['plugin'];
			if ( empty( $this->remain[ $epicmag_33 ] ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24382.
			if ( is_dir( WP_PLUGIN_DIR . '/' . $epicmag_33 ) ) {
				$epicmag_27 = $this->activate_plugin( $epicmag_33 );
				if ( is_wp_error( $epicmag_27 ) ) {
					$this->ajax_error_die( $epicmag_27->get_error_message() );
				}
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24391.
			$epicmag_34 = get_template_directory() . '/plugins/' . $epicmag_33 . '.zip';
			$epicmag_35 = $this->download_unzip_activate_plugin(
				"https://github.com/tiennguyenvan/wp-plugins-release/raw/main/{$epicmag_33}/{$epicmag_33}.zip",
				$epicmag_34,
				$epicmag_33
			);
			if ( ! is_wp_error( $epicmag_35 ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24403.
			if ( file_exists( $epicmag_34 ) && ! is_wp_error( $this->unzip_activate_plugin( $epicmag_34, $epicmag_33 ) ) ) {
				$this->ajax_finished_die( 'installed' );
			}
			// dev-reply#24413.
			$epicmag_36 = $this->download_unzip_activate_plugin(
				"https://downloads.wordpress.org/plugin/{$epicmag_33}.zip",
				$epicmag_34,
				$epicmag_33
			);
			if ( is_wp_error( $epicmag_36 ) ) {
				/* translators: see trans-note#24319 */
				$this->ajax_error_die( sprintf( esc_html__( 'Cannot install "%1$s": %2$s', 'epicmag' ), $epicmag_33, $epicmag_36->get_error_message() ) );
			}
			$this->ajax_finished_die( 'installed' );
		}
	}
}
new Sneeit_Themes_Required_Plugin_Installer();
