<?php
/*
Plugin Name: Software Update Server
Plugin URI: https://bitbucket.org/jonyhayama/software-update-server
Description: Easily serve WordPress Themes or Plugins. Based on [WP Update Server](https://github.com/YahnisElsts/wp-update-server) by [Yahnis Elsts](https://github.com/YahnisElsts/)
Version: 0.1
Author: Jony Hayama
Author URI: https://jony.co
*/

define( 'SOFTUPDATEMGR_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'SOFTUPDATEMGR_PLUGIN_URL', plugins_url('', __FILE__ ) );

require_once( SOFTUPDATEMGR_DIR_PATH . 'class/softupdatemgr.class.php' );

function softupdatemgr( $module = '' ){
	static $_softupdatemgr_obj = null;
	if( !$_softupdatemgr_obj ){
		$_softupdatemgr_obj = new softupdatemgr();
	} 
	if( $module ){
		return $_softupdatemgr_obj->getModule( $module );
	}
	return $_softupdatemgr_obj;
}
softupdatemgr();
