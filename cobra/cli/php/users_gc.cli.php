#!/usr/bin/php -q
<?php

// begin header
if( ! $bootstrap = getenv( 'COBRA_BOOTSTRAP' ) ) die( 'COBRA_BOOTSTRAP' );
require_once( $bootstrap );
// end header

$method_id = str_replace( ".cli.php", "", basename( __FILE__ ) );
$cobra = cobra_cache_fetch( 'cobra' );
$gc = new coGC( $cobra['sys.config'] );  
$gc->Connect();
$gc->$method_id();
$gc->Disconnect();  

?>
