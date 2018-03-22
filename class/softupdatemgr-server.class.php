<?php
require_once SOFTUPDATEMGR_DIR_PATH . 'lib/wp-update-server/loader.php';
class softupdatemgr_server extends Wpup_UpdateServer{
    
    protected function generateDownloadUrl(Wpup_Package $package) {
        $query = array(
            'softupdatemgr_action' => 'download',
            'softupdatemgr_slug' => $package->slug,
        );
        return self::addQueryArg( $query, $this->serverUrl );
    }
}