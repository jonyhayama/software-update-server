<?php
class softupdatemgr_settings {
    protected $default_settings = array(
        'validation_method' => 'no-validation'
    );
    
    public function __construct(){
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'register_page' ) );
    }
    
    public function get_option( $option = null ){
        $options = array_merge( $this->default_settings, get_option( 'softupdatemgr_settings' ) );
        if( !$option ){
            return $options;
        }
        if( array_key_exists( $option, $options ) ){
            return $options[ $option ];
        }
        return false;
    }
    
    public function register_settings(){
        register_setting( 'softupdatemgr_settings_group', 'softupdatemgr_settings' );
        
        add_settings_section(
            'softupdatemgr_settings_section',
            '',
            '__return_false',
            'softupdatemgr_settings_group' 
        );
        
        add_settings_field( 
            'softupdatemgr_validation_method', 
            __( 'Validation Method', 'softupdatemgr' ), 
            array( $this, 'render_validation_method_field' ), 
            'softupdatemgr_settings_group', 
            'softupdatemgr_settings_section' 
        );
    }
    public function register_page(){
        $page_title = 'Software Update Manager';
        $menu_title = 'Software Update Manager';
        $capability = 'manage_options';
        $menu_slug = 'softupdatemgr_settings';
        $callback = array( $this, 'render_page' ); 
        add_options_page( $page_title, $menu_title, $capability, $menu_slug, $callback);
    }
    
    public function settings_section_callback(){
        echo __( 'Software Update Manager Settings Section Description', 'softupdatemgr' );
    }
    
    public function render_page(){
        include SOFTUPDATEMGR_DIR_PATH . 'templates/settings-page.php';
    }
    public function render_validation_method_field(){
        $validationMethod = $this->get_option( 'validation_method' );
        $validationMethods = softupdatemgr()->getLicenseValidationMethods();
        ?>
            <select name='softupdatemgr_settings[validation_method]'>
                <?php foreach( $validationMethods as $method_key => $method ){ 
                    $selected = ($validationMethod == $method_key) ? "selected='selected'" : '';
                    echo "<option value='$method_key' $selected>{$method['title']}</option>";
                } ?>
            </select>

        <?php
    }
   
}