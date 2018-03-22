<?php
class softupdatemgr{
    protected $updateServer;
    
    public function __construct(){
        require_once SOFTUPDATEMGR_DIR_PATH . 'class/softupdatemgr-server.class.php';
        $this->updateServer = new softupdatemgr_server( home_url( '/' ) );
        //The "action" and "slug" query parameters are often used by the WordPress core
		//or other plugins, so lets use different parameter names to avoid conflict.
		add_filter( 'query_vars', array( $this, 'addQueryVariables' ) );
		add_action( 'template_redirect', array( $this, 'handleUpdateApiRequest' ) );
    }
    
    public function addQueryVariables($queryVariables) {
		$queryVariables = array_merge($queryVariables, array(
			'softupdatemgr_action',
			'softupdatemgr_slug',
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
}