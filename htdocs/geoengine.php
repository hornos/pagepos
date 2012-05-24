<?php
// begin header
if( ! $bootstrap = getenv( 'COBRA_BOOTSTRAP' ) ) die( 'internal error' );
require_once( $bootstrap );
// end header

// defines
cobra_define( 'DEBUG', false );
cobra_define( 'COBRA_REQUEST_POST', false );
cobra_define( 'PAGEPOS_TEST_MODE', false );

// autoload
cobra_autoload( 'ppSite', 'pagepos' );

// init site
$ppsite = new ppSite();

// site lock
if( is_readable( COBRA_SITE_LOCK ) ) {
  coRequest::jresponse( 'exception', file_get_contents( COBRA_SITE_LOCK ) );
  die();
}

// proxy check
if( ! $ppsite->rpc( 'proxy_check' ) ) {
  coRequest::jresponse( 'exception', 'proxy mismatch' );
  die();
}

// session check
if( ! $ppsite->rpc( 'session_check') ) {
  // TODO: js reload
  coRequest::jresponse( 'message', 'Your session was expired. Please reload the page!' );
  die();
}

// run command
$ppsite->rpc( coRequest::request( 'cmd', 'location' ) );
?>
