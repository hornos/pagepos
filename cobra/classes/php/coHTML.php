<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

class coHTML {
  public function __construct() { }

  public static function out( $html = '', $is_br = false ) {
    print( $html . ( $is_br ? '<br>' : '' ) . PHP_EOL );
  }


  public static function a2s( $attributes = NULL ) {
    $s = '';
    if( is_array( $attributes ) ) {
	  foreach( $attributes as $key => $value ) {
	    $s .= $key.'="'.$value.'" ';
	  }
	}
    return $s;
  }


  public static function span( $value = '', $attributes = NULL ) {
    self::out( '<span '.self::a2s( $attributes ).'>'.$value.'</span>' );	
  }


  public static function div( $value = '', $attributes = NULL ) {
    self::out( '<div '.self::a2s( $attributes ).'>'.$value.'</div>' );	
  }


  public static function img( $value = '', $attributes = NULL ) {
    self::out( '<img '.self::a2s( $attributes ).'src="'.$value.'" />');
  }


  public static function input( $value = '', $attributes = NULL, $label = '' ) {
    if( ! empty( $label ) ) {
      $label = '<span class="input_label">' . $label . '</span>';
    }
    self::out( '<input '.self::a2s( $attributes ).'value="'.$value.'" />'.$label );	
  }


  public static function button( $value = '', $attributes = NULL, $label = '' ) {
    self::out( '<button '.self::a2s( $attributes ).'>'.$value.'</button>'.$label );
  }


  public static function textarea( $value = '', $attributes = NULL, $label = '' ) {
    self::out( '<textarea '.self::a2s( $attributes ).'>'.$value.'</textarea>'.$label );
  }


  public static function ypad( $value = '&nbsp;' ) {
    self::out( '<div style="background-color: yellow;">'.$value.'</div>' );
  }
  

  public static function script( $js = '' ) {
    self::out( '<script>' . $js . '</script>' );
  }
  
  
  public static function redirect( $location = '' ) {
    self::script( 'window.location="' . $location . '"' );
  }  
  
}

?>
