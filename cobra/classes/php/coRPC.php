<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

class coRPCException extends coException {
  public function __construct( $message = __CLASS__ ) {
    parent::__construct( $message );
  }
}


class coRPC {
  public function __construct() { }
    
  protected function _rpc( $method = NULL, $argv = NULL ) {
    if( empty( $method ) ) throw new coRPCException( __METHOD__ );
    
    $method = $method;
    if( ! method_exists( $this, $method ) ) throw new coRPCException( __CLASS__ . '::' . $method );
    
    return empty( $argv ) ? $this->$method() : $this->$method( $argv );
  }


  protected function _rpc_test() { return __METHOD__ . ' OK'; }


  public function rpc( $method = NULL, $argv = NULL ) {
    if( empty( $method ) ) throw new coRPCException( __METHOD__ );

    return $this->_rpc( '_rpc_' . $method, $argv );
  }
  
}

?>
