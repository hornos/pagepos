<?php

// applications
$config['app.bootstrap'] = array( 'system', 'lumen' );

// db access
$config['db.host']   = '127.0.0.1';
$config['db.name']   = 'cobra';
$config['db.user']   = 'cobra_gc';
$config['db.port']   = 5432;
$config['db.pass']   = 'cobra_gc';
$config['db.engine'] = 'pgsql';
// $config['db.pdo_attributes'] = array( PDO::ATTR_PERSISTENT => true );
$config['db.pdo_attributes'] = array();

?>
