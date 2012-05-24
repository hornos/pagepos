<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coModule extends coRPC {
  public $app_id;
  
  public function __construct( $app_id = NULL ) { 
    $this->app_id = $app_id;
  }
  
}

?>
