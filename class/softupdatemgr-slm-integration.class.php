<?php

class softupdatemgr_SLM_Integration{
    public function __construct(){
        add_filter( 'softupdatemgr/add_license_validation_method', array( $this, 'addLicenseValidationMethods' ) );
    }
    public function addLicenseValidationMethods( $methods ){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if( is_plugin_active( 'software-license-manager/slm_bootstrap.php' ) ){
			$methods['software-license-manager'] = array( 
				'title' => __( 'Software Manager License Key', 'softupdatemgr' ), 
				'callback' => array( $this, 'validateSLMLicense' ) 
			);
		}
        return $methods;
    }
    
    public function validateSLMLicense( $license ){
		$options = get_option('slm_plugin_options');
		$secret_verification_key = $options['lic_verification_secret'];
		if ( empty($secret_verification_key) ) {
			trigger_error( 'Unable to get SLM verification key.', 'E_WARNING' );
			return false;
		}
		
		return true;
	}
}