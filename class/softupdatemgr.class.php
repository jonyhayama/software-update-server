<?php
/*
 * Base on https://github.com/YahnisElsts/wp-update-server;
 * URL Exemple: https://dev.jony.co/siscafelicense?softupdatemgr_action=get_metadata&softupdatemgr_slug=siscafe
 */
class softupdatemgr{
    protected $updateServer;
	protected $licenseValidationMethods = array();
	protected $integration = array();

    public function __construct(){
        require_once SOFTUPDATEMGR_DIR_PATH . 'class/softupdatemgr-server.class.php';
        $this->updateServer = new softupdatemgr_server( home_url( '/' ) );
        //The "action" and "slug" query parameters are often used by the WordPress core
		//or other plugins, so lets use different parameter names to avoid conflict.
		add_filter( 'query_vars', array( $this, 'addQueryVariables' ) );
		add_action( 'template_redirect', array( $this, 'handleUpdateApiRequest' ) );

		// Adds License Validation Methods Filter
		add_action( 'plugins_loaded', array( $this, 'applyLicenseValidationMethodsFilter' ) );
		add_filter( 'softupdatemgr/add_license_validation_method', array( $this, 'addLicenseValidationMethods' ) );
		add_filter( 'softupadtemgr/server/generateDownloadUrl/query', array( $this, 'ServerDownloadQuery' ) );
		
		// Load Settings Class
		require_once SOFTUPDATEMGR_DIR_PATH . 'class/softupdatemgr-settings.class.php'; 
		$this->settings = new softupdatemgr_settings;
		
		// Load SLM Integration
		require_once SOFTUPDATEMGR_DIR_PATH . 'class/softupdatemgr-slm-integration.class.php'; 
		$this->integration['slm'] = new softupdatemgr_SLM_Integration;
    }
	
	public function getModule( $module ){
		if( property_exists( $this, $module ) ){
			return $this->$module;
		}
		return false;
	}
	
    public function addQueryVariables($queryVariables) {
		$queryVariables = array_merge($queryVariables, array(
			'softupdatemgr_action',
			'softupdatemgr_slug',
			'softupdatemgr_license'
		));
		return $queryVariables;
	}

	public function handleUpdateApiRequest() {
		if ( get_query_var( 'softupdatemgr_action' ) ) {
			$this->updateServer->handleRequest( array_merge($_GET, array(
				'action' => get_query_var('softupdatemgr_action'),
				'slug'   => get_query_var('softupdatemgr_slug'),
			) ) );
		}
	}

	public function addLicenseValidationMethods( $methods ){
		$methods['no-validation'] = array( 
			'title' => __( 'No Validation', 'softupdatemgr' ), 
			'callback' => '__return_true' 
		);
		
		return $methods;
	}
	
	public function ServerDownloadQuery( $query ){
		if( $this->isAuthenticatedRequest() ){
            $license = get_query_var('softupdatemgr_license');
            if( $license ){
                $query['softupdatemgr_license'] = $license;
            }
        }
		return $query;
	}

	public function applyLicenseValidationMethodsFilter(){
		$this->licenseValidationMethods = apply_filters( 'softupdatemgr/add_license_validation_method', $this->licenseValidationMethods );
	}

	public function isValidLicense( $license ){
		$licenseValidationMethods = $this->getLicenseValidationMethods();
		$validationMethod = $this->settings->get_option( 'validation_method' );
		$validationMethod = $licenseValidationMethods[$validationMethod];
		return call_user_func( $validationMethod['callback'], $license );
	}

	public function isAuthenticatedRequest(){
		static $authenticatedRequest = null;
		if( !$authenticatedRequest ){
			$license = get_query_var('softupdatemgr_license');
			$authenticatedRequest = $this->isValidLicense( $license );
		}
		return $authenticatedRequest;
	}
	
	public function getLicenseValidationMethods(){
		return $this->licenseValidationMethods;
	}
}