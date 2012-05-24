<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

cobra_define( 'COBRA_REQUEST_SIZE', 128 );
cobra_define( 'COBRA_REQUEST_POST', true );

class coRequest {
  public function __construct() { }
  
  public static function request( $id = NULL, $default = NULL, $post = COBRA_REQUEST_POST, $clear = true, $safe = true, $size = COBRA_REQUEST_SIZE ) {
    if( $post ) {
      $v = ( ! isset($_POST[$id] ) or $_POST[$id] == '' ) ? $default : $_POST[$id];
      if( $clear ) unset( $_POST[$id] );
    }
    else {
      $v = ( ! isset($_POST[$id] ) or $_REQUEST[$id] == '' ) ? $default : $_REQUEST[$id];
      if( $clear ) unset( $_REQUEST[$id] );
    }
    
    // print $id . ': ' . $v . "\n";
	
    if( $safe && $v != NULL ) $v = coString::subalnum( $v, $size );
	
    return $v;
  }


  public static function unsafe_request( $id = NULL, $default = NULL, $post = COBRA_REQUEST_POST, $clear = true ) {
    return self::request( $id, $default, $post, $clear, false );
  }


  public static function get( $id = NULL, $default = NULL, $clear = true, $safe = true, $size = COBRA_REQUEST_SIZE ) {
    $v = empty( $_REQUEST[$id] ) ? $default : $_REQUEST[$id];
    if( $clear ) unset( $_REQUEST[$id] );

    if( $safe && $v != NULL ) $v = coString::subalnum( $v, $size );
    
    return $v;
  }


  public static function jrequest( $id = NULL, $default = NULL, $size = COBRA_REQUEST_SIZE ) {
    return json_decode( self::request( $id, $default, true, true, true, $size ) );
  }


  public static function jsend( $obj = NULL ) {
    echo json_encode( $obj );
    return true;
  }
  
  
  public static function jresponse( $type = 'return', $data = NULL, $sleeptime = 0 ) {
    if( $sleeptime > 0 ) {
      sleep( $sleeptime );
    }
    return self::jsend( array( 'type' => $type, 'data' => $data ) );
  }


  public static function jexception( $exception = NULL ) {
    return self::jresponse( 'exception', $exception->getMessage() );
  }
}

?>
