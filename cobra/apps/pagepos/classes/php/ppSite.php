<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class ppSite extends coSite {
  public function __construct() {
    parent::__construct();
  }

  public function html_redirect( $url = NULL ) {
    echo '<html><head><meta HTTP-EQUIV="REFRESH" content="0; url='.$url.'"></head></html>';
  }
  
  protected function _rpc_rpc() {}

  protected function _rpc_proxy_check() {
    $remote = $_SERVER['REMOTE_ADDR'];
    // real server
    $jsapi_key = false;
    if( $remote == "" )
      $jsapi_key = "";
    // test server
    if( $remote == "" )
      $jsapi_key = "";
    // proxy server
    if( $remote == "" )
      $jsapi_key = "";

    return $jsapi_key;
  }
  
  
  protected function _rpc_session_check() {
    $cobra   = cobra_cache_fetch( 'cobra' );
    $session = new coSession( $cobra['sys.config'] ); 
    $session->start();
    try {
      return $session->load( 'pagepos' );
    } catch( Exception $e ) {
      $session->stop( false );
      return false;
    }
  }


  protected function _rpc_session_start() {
    $cobra   = cobra_cache_fetch( 'cobra' );
    $session = new coSession( $cobra['sys.config'] ); 
    $session->start();
    $session->save( 'pagepos', true );
  }

  
  protected function _rpc_session_stop() {
    $cobra   = cobra_cache_fetch( 'cobra' );
    $session = new coSession( $cobra['sys.config'] ); 
    $session->stop();
  }


  protected function _rpc_location() {
    $cobra  = cobra_cache_fetch( 'cobra' );
    $app_id       = 'pagepos';
    $module_id    = 'ppLocation';
    $method_id    = coRequest::request( 'method_id', 'test' );
    $method_argv  = coRequest::request( 'method_argv' );
    $module_class = $module_id;
    cobra_autoload( $module_id, $app_id );    
    $module = new $module_class();
    try {
      $data = $module->rpc( $method_id, $method_argv );
    } catch( Exception $e ) {
      $type = 'exception';
      $data = $e->getMessage();
      return coRequest::jresponse( $type, $data );      
    }
    return $data;
  }


  public function sell_city( $argv = NULL ) {
    $cobra  = cobra_cache_fetch( 'cobra' );
    $app_id       = 'pagepos';
    $module_id    = 'ppLocation';
    $method_id    = 'sell_city';
    $method_argv  = $argv;
    $module_class = $module_id;
    cobra_autoload( $module_id, $app_id );    
    $module = new $module_class();
    return $module->rpc( $method_id, $method_argv );
  }


  public function verify_lock( $argv = NULL ) {
    $cobra  = cobra_cache_fetch( 'cobra' );
    $app_id       = 'pagepos';
    $module_id    = 'ppLocation';
    $method_id    = 'verify_lock';
    $method_argv  = $argv;
    $module_class = $module_id;
    cobra_autoload( $module_id, $app_id );
    $module = new $module_class();
    return $module->rpc( $method_id, $method_argv );
/*
    try {
    }
    catch( Exception $e ) {
      return false;
    }
*/
  }


  public function gc_city( $argv = NULL ) {
    $cobra  = cobra_cache_fetch( 'cobra' );
    $app_id       = 'pagepos';
    $module_id    = 'ppLocation';
    $method_id    = 'gc_city';
    $method_argv  = $argv;
    $module_class = $module_id;
    cobra_autoload( $module_id, $app_id );    
    $module = new $module_class();
    return $module->rpc( $method_id, $method_argv );
  }
 
}

?>
