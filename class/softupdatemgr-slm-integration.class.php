<?php
/* Example URL: https://dev.jony.co/siscafelicense/?softupdatemgr_action=get_metadata&softupdatemgr_slug=siscafe&softupdatemgr_license=SISCAFE-5aa7c9ab99c58&softupdatemgr_domain=dev.jony.co */
class softupdatemgr_SLM_Integration{
    public function __construct(){
        add_filter( 'softupdatemgr/add_license_validation_method', array( $this, 'addLicenseValidationMethods' ) );
        add_filter( 'query_vars', array( $this, 'addQueryVariables' ) );
        add_filter( 'softupadtemgr/server/generateDownloadUrl/query', array( $this, 'ServerDownloadQuery' ) );
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
    
    public function addQueryVariables($queryVariables) {
		$queryVariables = array_merge($queryVariables, array(
			'softupdatemgr_domain'
		));
		return $queryVariables;
	}
    
    public function ServerDownloadQuery( $query ){
		if( softupdatemgr()->isAuthenticatedRequest() ){
            $domain = get_query_var('softupdatemgr_domain');
            if( $domain ){
                $query['softupdatemgr_domain'] = $domain;
            }
        }
		return $query;
	}
    
    protected function api_call( $api_params ){
        $query = esc_url_raw( add_query_arg( $api_params, home_url()     ) );
        $response = wp_remote_get( $query, array( 'timeout' => 20 ) );
        if( is_wp_error( $response ) ){
          return false;
        }
        return json_decode( wp_remote_retrieve_body( $response ) );
    }
    
    protected function check_license_key( $key ){
        $options = get_option('slm_plugin_options');
		$secret_verification_key = $options['lic_verification_secret'];
        if ( empty($secret_verification_key) ) {
			trigger_error( 'Unable to get SLM verification key.', 'E_WARNING' );
			return false;
		}
        $api_params = array(
          'slm_action' => 'slm_check',
          'secret_key' => $secret_verification_key,
          'license_key' => $key,
        );
        
        return $this->api_call( $api_params );
    }
    
    protected function is_domain_registered( $domain, $registered_domains ){
        foreach( $registered_domains as $lic ){
            if( $lic->registered_domain == $domain ){
                return true;
            }
        }
        return false;
    }
    
    public function validateSLMLicense( $license ){
        $license_data = $this->check_license_key( $license );
        if( !$license_data ){
            // Couldn't Fetch data
            return false;
        }        
        if( $license_data->result != 'success' || $license_data->status != 'active' ){
            // Results are an error or license is not active
            return false;
        }
        $domain = get_query_var('softupdatemgr_domain');
        if( empty( $domain ) || !$this->is_domain_registered( $domain, $license_data->registered_domains ) ){
            // Correct license but wrong domain
            return false;
        }
        
        $timezone = date_default_timezone_get();
        date_default_timezone_set( get_option('timezone_string') );
        $date_format = 'Y-m-d';
        $today = DateTime::createFromFormat( $date_format, date( $date_format ) );
        $expiry = DateTime::createFromFormat( $date_format, $license_data->date_expiry );
        if( $expiry < $today ){
            // License is expired
            return false;
        }
		
		return true;
	}
}