<?php
if (!defined('ABSPATH')) exit;
// delete_site_transient('update_plugins');
if (!class_exists('Sneeit_Required_Plugin_Installer')) {
	class Sneeit_Required_Plugin_Installer
	{
		public $remain = EPICMAG_REQUIRED_PLUGINS;

		/**
		Don't change this slug
		It should match the slug of sneeit-core plugin
		 */
		public $admin_slug = 'sneeit-core';
		public $admin_redirect = 'sneeit-core-import';
		public $admin_redirect_activate = 'sneeit-core-activate';
		public $admin_redirect_import = 'sneeit-core-import';

		/**
		Don't change this slug
		It should match with the folder name in the build directory
		 */
		public $sub_slug = 'sneeit-core-required-plugins';
		public $ajax_slug = '';
		public $theme_name = '';
		/**
		 * 
		 */
		public function __construct()
		{
			// check if there are plugins needed to be installed
			$active_plugins = get_option('active_plugins');

			foreach ($active_plugins as $plugin) {
				$slug = dirname($plugin);
				unset($this->remain[$slug]);
			}



			// all required plugins have been installed
			// @todo: compare the versions and provide updates
			if (count($this->remain) === 0) {
				return;
			}

			// did not install sneeit core
			$theme = wp_get_theme();
			$theme_update_uri = $theme->get('UpdateURI');
			// prioritizing free for themes without update URI or themes with sneeit.com/free
			// then themes with update URI different than sneeit.com
			$requires_sneeit_license = (!(empty($theme_update_uri) ||  'https://sneeit.com/free' === $theme_update_uri)) && 'https://sneeit.com/' === $theme_update_uri;
			if ($requires_sneeit_license) {
				$this->admin_redirect = $this->admin_redirect_activate;
			} else {
				$this->admin_redirect = $this->admin_redirect_import;
			}

			// otherwise, create install page
			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
			add_action('admin_notices', array($this, 'admin_notices'), 1);
			add_action('switch_theme', array($this, 'refresh_update_checker'), 1);
			add_action('activated_plugin', array($this, 'refresh_update_checker'), 1);
			add_action('deactivated_plugin', array($this, 'refresh_update_checker'), 1);


			$this->ajax_slug = str_replace('-', '_', $this->sub_slug);
			add_action('wp_ajax_nopriv_' . $this->sub_slug, array($this, 'installer'));
			add_action('wp_ajax_' . $this->sub_slug, array($this, 'installer'));
		}

		public function refresh_update_checker() {
			delete_site_transient('update_themes');			
			delete_transient('update_themes');
			delete_site_transient('update_plugins');
			delete_transient('update_plugins');
		}

		/**
		 * 
		 */
		public function admin_menu()
		{
			// register admin menu if did not
			if (empty($GLOBALS['admin_page_hooks'][$this->admin_slug])) {
				// Add the main menu page								
				add_menu_page(
					__('Sneeit Core', 'epicmag'), // page title
					__('Sneeit Core', 'epicmag'), // menu title
					'manage_options', // capabilities
					$this->admin_slug, // menu slug
					array($this, 'add_submenu_page'), // render function
					get_template_directory_uri() . '/assets/images/sneeit-logo-16.png', // icon
					6 // position
				);
			}



			global $menu;

			foreach ($menu as $key => $value) {
				if (!empty($value[2]) && $value[2] === $this->admin_slug) {
					$menu[$key][0] .= ' <span class="awaiting-mod">' . count($this->remain) . '</span>';
					break;
				}
			}


			// register sub menu for the plugin install page						
			$current_theme = wp_get_theme();
			$this->theme_name = $current_theme->get('Name');

			add_submenu_page(
				$this->admin_slug, // parent slug
				$this->admin_slug, // page title 
				$this->theme_name . ' ' . __('Plugins', 'epicmag') . ' <span class="awaiting-mod">' . count($this->remain) . '</span>', // menu title
				'manage_options', // capabilities
				$this->sub_slug, // menu slug,
				array($this, 'add_submenu_page') // render function
			);

			// Remove the auto-generated submenu
			remove_submenu_page($this->admin_slug, $this->admin_slug);
		}

		/**
		 * 
		 */
		public function add_submenu_page()
		{
			echo '<div class="app ' . $this->sub_slug . '"></div>';
		}

		/**
		 * Show required plugins
		 */
		public function admin_notices()
		{
			if (!empty($_GET['page'])) {
				$page = $_GET['page'];
				// don't need to notice in our own app
				if ($page === $this->sub_slug) {
					return;
				}
			}


			$keys = array_keys($this->remain);
			$capitalized_keys = array_map('ucfirst', $keys);
			$imploded_keys = implode(', ', $capitalized_keys);
			echo '<section><div class="notice notice-large notice-warning is-dismissible">';
			echo '<h2 class="notice-title">';
			echo sprintf(__('Missing required plugins for %s theme', 'epicmag'), $this->theme_name);
			echo '</h2>';
			echo '<p>';
			echo sprintf(__('%s requires following plugins to work: <strong>%s</strong>', 'epicmag'), $this->theme_name, $imploded_keys);
			echo '</p>';
			echo '<p>';
			echo '<a class="button button-large button-primary" href="' . menu_page_url($this->sub_slug, false) . '">';
			echo __('Please Install and Active', 'epicmag');
			echo '</a>';
			echo '</p>';
			echo '</div></section>';
		}


		/**
		 * then register the plugin page
		 */
		public function admin_enqueue_scripts()
		{
			if (empty($_GET['page'])) {
				return;
			}
			// only enqueue for our own app
			$page = $_GET['page'];
			if ($page !== $this->sub_slug) {
				return;
			}

			// register sub menu for the plugin install page									
			$theme_url = get_template_directory_uri();
			$build_dir = '/build/admin/';

			// enqueue dependencies
			$asset_path = get_template_directory() . $build_dir . $this->sub_slug . '/index.asset.php';
			if (!file_exists($asset_path)) {
				return;
			}
			// Load the required WordPress packages.
			// Automatically load imported dependencies and assets version.
			$asset_file = include $asset_path;

			// Enqueue STYLES			
			wp_enqueue_style($this->sub_slug, $theme_url . $build_dir . $this->sub_slug . '/style-index.css', null, time());

			// Enqueue SCRIPT		
			array_push($asset_file['dependencies'], 'wp-i18n', 'jquery');
			wp_enqueue_script($this->sub_slug, $theme_url . $build_dir . $this->sub_slug . '/index.js', $asset_file['dependencies'], time(), true);
			wp_localize_script($this->sub_slug, 'sneeitCoreRequiredPlugins', array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'sneeitCoreUrl' => admin_url('admin.php?page=' . $this->admin_redirect),
				'nonce'   => wp_create_nonce($this->sub_slug),
				'screenshot' => get_template_directory_uri() . '/screenshot.png',
				'text' => array(
					'finished' => __('Finished', 'epicmag'),
					'title' => __('Required Plugins for ', 'epicmag') .  $this->theme_name,
					'button' => __('Install and Activate', 'epicmag'),
					'redirecting' => __('Redirecting ...', 'epicmag'),
					'error' => __('WordPress Server Error', 'epicmag')
				),
				'plugins' => $this->remain
			));
		}

		// and process what the app sends
		public function ajax_error_die($text)
		{
			echo json_encode(array('error' => $text));
			die();
		}
		public function ajax_finished_die($text)
		{
			echo json_encode($text);
			die();
		}

		public function ajax_request_verify_die($fields = array())
		{
			if (empty($_POST['nonce'])) {
				$this->ajax_error_die(__('empty nonce', 'epicmag'));
			}
			if (!wp_verify_nonce($_POST['nonce'], $this->sub_slug)) {
				$this->ajax_error_die(__('Timeout! Please reload the page.', 'epicmag'));
			}
			if (is_string($fields)) {
				$fields = explode(',', $fields);
			}

			if (!empty($fields)) {
				foreach ($fields as $field) {
					$field = trim($field);
					if (empty($_POST[$field])) {
						$this->ajax_error_die(sprintf(__('Missing required field: %s', $field)));
					}
				}
			}
		}

		public function plugin_install_file($slug)
		{
			// check if have the exact file
			$plugin_path = WP_PLUGIN_DIR . '/' . $slug . '/';
			if (file_exists($plugin_path . $slug . '.php')) {
				$file_content = file_get_contents($plugin_path . $slug . '.php');
				if ($file_content && strpos($file_content, 'Plugin Name:') !== false) {
					return $plugin_path . $slug . '.php';
				}
			}

			// scan all other files to find the plugin install file
			$file_paths = glob(WP_PLUGIN_DIR . '/' . $slug . '/*.php');
			foreach ($file_paths as $file_path) {
				$file_content = file_get_contents($file_path);
				if ($file_content && strpos($file_content, 'Plugin Name:') !== false) {
					return $file_path;
				}
			}

			return '';
		}
		public function activate_plugin($slug)
		{
			$plugin_file = $this->plugin_install_file($slug);
			if (!$plugin_file) {
				return new WP_Error('epicmag-plugin-installer', sprintf(__('"%s" has invalid file', 'epicmag'), $slug));
			}

			// clear cache so they have to scan all plugin data again
			// before activating a plugin,
			// this is to avoid the case when a plugin is already loaded
			// into the plugin folder but it is not in the cache
			wp_cache_delete('plugins', 'plugins');

			$active = activate_plugin($plugin_file);
			if (is_wp_error($active)) {
				return new WP_Error('epicmag-plugin-installer', sprintf(__('Cannot active "%1$s": file %2$s %3$s', 'epicmag'), $slug, $plugin_file, $active->get_error_message()));
			}
			return true;
		}

		public function unzip_activate_plugin($path, $slug)
		{
			// global $wp_filesystem;
			// if (!$wp_filesystem) {
			//     $this->ajax_error_die(__('Can not access file system', 'epicmag'));
			// }
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
			$unzip = unzip_file($path, WP_PLUGIN_DIR);
			if (is_wp_error($unzip)) {
				return new WP_Error('epicmag-plugin-installer', sprintf(__('Cannot unzip "%1$s": %2$s', 'epicmag'), $slug, $unzip->get_error_message()));
			}
			if (!is_dir(WP_PLUGIN_DIR . '/' . $slug)) {
				return new WP_Error('epicmag-plugin-installer', sprintf(__('"%s" has invalid slug', 'epicmag'), $slug));
			}
			return $this->activate_plugin($slug);
		}

		public function download_unzip_activate_plugin($url, $path, $slug)
		{
			$download = download_url($url);
			if (is_wp_error($download)) {
				return new WP_Error('epicmag-plugin-installer', sprintf(__('Cannot download "%1$s": %2$s', 'epicmag'), $url, $download->get_error_message()));
			}
			$dir = dirname($path);

			// check if the dir exists
			if (!is_dir($dir)) {
				// if not, create the base folder
				if (!mkdir($dir, 0777)) {
					unlink($download);
					return new WP_Error('epicmag-plugin-installer', sprintf(__('Cannot create folder of %s', 'epicmag'), $slug));
				}
			}

			// if user want to import another demos
			if (!rename($download, $path)) {
				unlink($download);

				return new WP_Error('epicmag-plugin-installer', sprintf(__('Cannot upload %s', 'epicmag'), $slug));
			}

			return $this->unzip_activate_plugin($path, $slug);
		}

		public function installer()
		{
			$this->ajax_request_verify_die('plugin');
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			$plugin_slug = $_POST['plugin'];
			if (empty($this->remain[$plugin_slug])) {
				$this->ajax_finished_die('installed');
			}

			// the plugin is already installed, so active it
			if (is_dir(WP_PLUGIN_DIR . '/' . $plugin_slug)) {
				$active = $this->activate_plugin($plugin_slug);
				if (is_wp_error($active)) {
					$this->ajax_error_die($active->get_error_message());
				}
				$this->ajax_finished_die('installed');
			}

			// try to get from the github first to have the latest version of the plugin	
			$local = get_template_directory() . '/plugins/' . $plugin_slug . '.zip';
			$github_install = $this->download_unzip_activate_plugin(
				"https://github.com/tiennguyenvan/wp-plugins-release/raw/main/{$plugin_slug}/{$plugin_slug}.zip",
				$local,
				$plugin_slug
			);
			
			if (!is_wp_error($github_install)) {
				$this->ajax_finished_die('installed');
			}
			
			// else {
			// 	$this->ajax_error_die(sprintf(__('Cannot install from github "%1$s": %2$s', 'epicmag'), $plugin_slug, $github_install->get_error_message()));
			// }						

			// plugin is not available on github or installed failed
			// and if local file is available then try to use our local script first			
			if (file_exists($local) && !is_wp_error($this->unzip_activate_plugin($local, $plugin_slug))) {
				$this->ajax_finished_die('installed');
			}

			// here, github file is not available and the local file is not exist
			// try from wordpress repository
			$wp_install = $this->download_unzip_activate_plugin(
				"https://downloads.wordpress.org/plugin/{$plugin_slug}.zip",
				$local,
				$plugin_slug
			);
			if (is_wp_error($wp_install)) {
				$this->ajax_error_die(sprintf(__('Cannot install "%1$s": %2$s', 'epicmag'), $plugin_slug, $wp_install->get_error_message()));
			}

			$this->ajax_finished_die('installed');
		}
	}
}

new Sneeit_Required_Plugin_Installer();
