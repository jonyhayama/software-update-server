<?php
/*
 * Base on https://github.com/YahnisElsts/wp-update-server;
 * URL Exemple: https://dev.jony.co/siscafelicense?softupdatemgr_action=get_metadata&softupdatemgr_slug=siscafe
 */
class softupdatemgr{
    protected $updateServer;
	protected $licenseValidationMethods = array(
		'no-validation' => '__return_true',
	);

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
		$is_plugin_active = true;
		if( $is_plugin_active ){
			$methods['software-license-manager'] = array( $this, 'validateSLMLicense' );
		}
		return $methods;
	}

	public function applyLicenseValidationMethodsFilter(){
		$this->licenseValidationMethods = apply_filters( 'softupdatemgr/add_license_validation_method', $this->licenseValidationMethods );
	}

	public function isValidLicense( $license ){
		$validationMethod = 'no-validation';
		$validationMethod = $this->licenseValidationMethods[$validationMethod];
		return call_user_func( $validationMethod, $license );
	}

	public function isAuthenticatedRequest(){
		$license = get_query_var('softupdatemgr_license');
		return $this->isValidLicense( $license );
	}

	public function validateSLMLicense( $license ){
		return true;
	}
}