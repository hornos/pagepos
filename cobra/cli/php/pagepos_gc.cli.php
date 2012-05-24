#!/usr/bin/php -q
<?php

// begin header
if( ! $bootstrap = getenv( 'COBRA_BOOTSTRAP' ) ) die( 'COBRA_BOOTSTRAP' );
require_once( $bootstrap );
// end header

// autoload
cobra_autoload( 'ppSite', 'pagepos' );

$expires = 60 * 60 * 24 * 1;

// init site
$ppsite = new ppSite();
$ppsite->gc_city( $expires );
?>
