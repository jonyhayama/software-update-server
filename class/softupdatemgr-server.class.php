<?php
require_once SOFTUPDATEMGR_DIR_PATH . 'lib/wp-update-server/loader.php';
class softupdatemgr_server extends Wpup_UpdateServer{

    protected function generateDownloadUrl(Wpup_Package $package) {
        $query = array(
            'softupdatemgr_action' => 'download',
            'softupdatemgr_slug' => $package->slug,
        );
		$query = apply_filters( 'softupadtemgr/server/generateDownloadUrl/query', $query );
        return self::addQueryArg( $query, $this->serverUrl );
    }

    //Secure Download URL
    protected function filterMetadata($meta, $request) {
		$meta = parent::filterMetadata($meta, $request);
        if( !softupdatemgr()->isAuthenticatedRequest() ){
		    unset( $meta['download_url'] );
        }
		return $meta;
	}
    protected function actionDownload(Wpup_Request $request) {
        if( !softupdatemgr()->isAuthenticatedRequest() ){
		    $this->exitWithError('Unauthorized download.', 403);
        }
		parent::actionDownload( $request );
	}
}
