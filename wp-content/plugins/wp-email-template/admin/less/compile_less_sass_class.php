<?php
class Compile_Less_Sass {
	
	public function Compile_Less_Sass(){
		$this->init();
	}
	public function init(){
	}
	
	public function compileLessFile( $less_file = '', $css_file = '', $css_min_file = '' ){
	
		if( empty( $less_file ) )
			$less_file      = dirname( __FILE__ ) . '/assets/css/style.less';
		if( empty( $css_file ) )
			$css_file       = dirname( __FILE__ ) . '/assets/css/style.css';
		if( empty( $css_min_file ) )
			$css_min_file       = dirname( __FILE__ ) . '/assets/css/style.min.css';
			
		//@chmod( $css_file, 0777 );
		//@chmod( $css_min_file, 0777 );
		
		// Write less file
    	if ( is_writable( $css_file ) && is_writable( $css_min_file ) ) {
			
			if ( ! class_exists( 'lessc' ) ){
				include( dirname( __FILE__ ) . '/lib/lessc.inc.php' );
			}
			if ( ! class_exists( 'cssmin' ) ){
				include( dirname( __FILE__ ) . '/lib/cssmin.inc.php' );
			}
		
			try {
				
				$less         = new lessc;
				
				$compiled_css = $less->compileFile( $less_file );
				
				if ( $compiled_css != '' ){
					file_put_contents( $css_file, $compiled_css );
					
					$compiled_css_min = CssMin::minify( $compiled_css );
					if ( $compiled_css_min != '' )
						file_put_contents( $css_min_file, $compiled_css_min );
				}
	
			} catch ( exception $ex ) {
				
				//echo ( __( 'Could not compile .less:', 'sass' ) . ' ' . $ex->getMessage() );
			}
		}

	}
}
?>
