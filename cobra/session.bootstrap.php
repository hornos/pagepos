<?php

// get out global home path
$__home = dirname( __FILE__ );

// system paths  
$cobra['path.home']    = $__home;
$cobra['path.classes'] = $__home.'/classes/php';
$cobra['path.apps']    = $__home.'/apps';
$cobra['path.cache']   = $__home.'/cache';
$cobra['path.log']     = $__home.'/log';
$cobra['path.errors']  = $__home.'/errors';
$cobra['path.htdocs']  = $__home.'/../htdocs';

// system files
$cobra['path.bootstrap'] = __FILE__;
$cobra['path.kickstart'] = $__home.'/kickstart.php';
$cobra['path.config']    = $__home.'/session.config.php';

// load kickstart
if( ! is_readable( $cobra['path.kickstart'] ) )
  die( 'bootstrap::path.kickstart' ) ;
// else
require_once( $cobra['path.kickstart'] );


// load config
if( ! is_readable( $cobra['path.config'] ) )
  die( 'bootstrap::path.config' );
// else
require_once( $cobra['path.config'] );


// init cobra
// cobra_init( 'UTF-8', 'cobra_exception_handler' );
cobra_init( 'UTF-8' );
// cache cobra
cobra_cache_store( 'cobra', new coCobra( $cobra, $config ) );

// defines
cobra_define( 'COBRA_SITE_LOCK', $cobra['path.htdocs'] . '/nologin' );

// clean up
unset( $__home, $cobra, $config );
?>
