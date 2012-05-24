<?php
$__home = dirname( __FILE__ );

// system paths  
$pagepos['path.home']    = $__home;
$pagepos['path.classes'] = $__home.'/classes';

// system files
$pagepos['path.bootstrap'] = __FILE__;
$pagepos['path.kickstart'] = $__home.'/kickstart.php';
$pagepos['path.config']    = $__home.'/config.php';

// load config
require_once( $pagepos['path.config'] );

// load kickstart
require_once( $pagepos['path.kickstart'] );

// init app
?>
