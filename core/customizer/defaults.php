<?php /**
 * Customizer Default Settings File
 *
 * This file is used to setup the defaults used in the Layers customizer
 *
 * @package Layers
 * @since Layers 1.0.0
 */

class Layers_Customizer_Defaults {

	public $prefix;
	
	public $config;
	
	public $defaults;

	private static $instance;
    
    /**
    *  Get Instance creates a singleton class that's cached to stop duplicate instances
    */
    public static function get_instance() {
        if ( ! self::$instance ) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    /**
    *  Construct empty on purpose
    */

    private function __construct() {}

    /**
    *  Init behaves like, and replaces, construct
    */
    
    public function init() {
    	
    	$this->config = Layers_Customizer_Config::get_instance();
		
		// Setup prefix to use
		$this->prefix  = LAYERS_THEME_SLUG . '-';
		
		$filtered_defaults = apply_filters( 'layers_customizer_control_defaults' , array() );
		
		foreach( $this->config->controls as $section_key => $controls ) {

			foreach( $controls as $control_key => $control_data ){

				// Set key to use for the default
				//$setting_key = $this->prefix . $control_key;
				$setting_key = $control_key;
				
				$default = ( isset( $control_data['default'] ) ? $control_data['default'] : NULL );
				
				if ( isset( $filtered_defaults[ $setting_key ] ) ) $default = $filtered_defaults[ $setting_key ];

				// Register default
				$this->register_control_default( $setting_key, $control_data[ 'type' ], $default );
				
			}
		}

		$this->defaults = apply_filters( 'layers_customizer_defaults', $this->defaults );
	}

	/**
	* Register Control Defaults
	*/

	public function register_control_default( $key = NULL , $type = NULL, $value = NULL ){

		if( !isset( $this->defaults ) ) $this->defaults = array();

		if( NULL != $key ){
			$this->defaults[ $key ] = array(
					'value' => esc_attr( $value ),
					'type' =>$type
				);
		}
	}

}

Layers_Customizer_Defaults::get_instance();
