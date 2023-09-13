<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/// all plugins are required
//  other plugins related to design are included in the demo json files
define('EPICMAG_REQUIRED_PLUGINS', array(    
    'dragblock' => '0.0.1',
    'sneeit-core' => '0.0.1',
));

if (is_admin()) {
    // don't rename the 'sneeit-core-required-plugins' as it the page slug to enqueue folder name in apps
	require_once 'build/applications/sneeit-core-required-plugins/server/index.php';
}

