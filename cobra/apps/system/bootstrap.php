<?php
$__home = dirname( __FILE__ );

// system paths  
$system['path.home']    = $__home;
$system['path.classes'] = $__home.'/classes';

// system files
$system['path.bootstrap'] = __FILE__;
$system['path.kickstart'] = $__home.'/kickstart.php';
$system['path.config']    = $__home.'/config.php';

// load config
require_once( $system['path.config'] );

// load kickstart
require_once( $system['path.kickstart'] );

// init app
?>
