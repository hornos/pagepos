<?php
// begin cobra header
if( ! $bootstrap = getenv( 'COBRA_BOOTSTRAP' ) ) die( 'Internal Error' );
require_once( $bootstrap );
// end cobra header

// defines
cobra_define( 'DEBUG', false );

// autoload
cobra_autoload( 'ppSite', 'pagepos' );

// init site
$ppsite = new ppSite();

// PayPal

$paypal = new coPayPal( PAGEPOS_PAYPAL_LOG ? basename( __FILE__ ) . ".log" : NULL, PAGEPOS_PAYPAL_VERBOSE );
$paypal->set();


// site lock
if( is_readable( COBRA_SITE_LOCK ) ) {
  $paypal->verify_error();
  die();
}

// proxy check
if( ! $jsapi_key = $ppsite->rpc( 'proxy_check' ) ) {
  // coRequest::jresponse( 'exception', 'proxy error' );
  $paypal->verify_error();
}


// TODO
// 1. check geonameid price status
// 2. send back verify
// 3. register sold

$post = array();
$paypal->gen_request();
$post = $paypal->post();

if( empty( $post['item_number'] ) || empty( $post['payment_gross'] ) ) {
  $paypal->verify_error();
  die();
}

// clean input
$geonameid = coString::int( $post['item_number'], 0, 100000000 );
$price = coString::float( $post['item_number'] );

/*
try {
  $verify = $ppsite->verify_lock( array( 'geonameid' => $geonameid, 'price' => $price ) );
  $paypal->record( 'VERIFY: ' . $verify );
} catch( Exception $e ) {
  $paypal->verify_error();
  $paypal->record( 'LOCK ERROR: ' . $e->getMessage() );
  die();
}
*/

if( $paypal->verify() ) {
  try {
    $post = $paypal->post();
    $ret = $ppsite->sell_city( $post );
  } catch( Exception $e ) {
    $paypal->record( "EXCEPTION: " . $e->getMessage(), true );
    $paypal->record( "STACK LIST:", true );
    foreach( $post as $k => $v ) {
      $paypal->record( $k . ": " . $v, true );
    }
    die();
  }
  $paypal->record( "SOLD: ". $post['item_number'] . "\n", true );
}

?>
