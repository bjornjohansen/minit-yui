<?php
/*
Plugin Name: Minit YUI
Plugin URI: https://github.com/bjornjohansen/minit-yui
Description: Adds YUI Compressor minification to the Minit plugin by Kaspars Dambis
Version: 0.0.1
Author: Bjørn Johansen
Author URI: https://bjornjohansen.no
*/

new Minit_YUI;

class Minit_YUI {

	protected $_java_path;
	protected $_yui_path;

	function __construct() {
		$java_path = trim( shell_exec( "which java" ) );
		$yui_path = dirname( __FILE__ ) . '/yuicompressor-2.4.8.jar';

		if ( strlen( $java_path ) && is_file( $yui_path ) ) {
			$this->_java_path = & $java_path;
			$this->_yui_path = & $yui_path;

			add_filter( 'minit-content-css', array( $this, 'minit_content_css' ), 11, 3 );
			add_filter( 'minit-content-js', array( $this, 'minit_content_js' ), 11, 3 );
		}

	}

	public function minit_content_css( $content = '', $object = '', $script = '' ) {
		return $this->_minify( $content, 'css' );
	}

	public function minit_content_js( $content = '', $object = '', $script = '' ) {
		return $this->_minify( $content, 'js' );
	}

	protected function _minify ( $content = '', $type = 'css' ) {

		if ( strlen( $content ) ) {

			$wp_upload_dir = wp_upload_dir();
			$filename = tempnam( $wp_upload_dir['basedir'], 'minit' . $type );
			file_put_contents( $filename, $content );
			$filename_esc = escapeshellarg( $filename );

			$command = sprintf( '%s -jar %s --charset utf-8 --type %s %s', $this->_java_path, $this->_yui_path, ( 'css' == $type ? 'css' : 'js' ), $filename_esc );
			$minified = shell_exec( $command );

			unlink( $filename );

			if ( strlen( $minified ) ) {
				$content = $minified;
			}

		}

		return $content;
	}

}
