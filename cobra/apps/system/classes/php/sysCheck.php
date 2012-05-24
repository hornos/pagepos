<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

class sysCheck extends coModule {
  
  public function __construct( $app_id = NULL ) {
    parent::__construct( $app_id );
  }

  protected function _rpc_test() {
    return __METHOD__ . ' OK';
  }
}


?>
