<?php
/*! \file kickstart.php
    \brief General system initialization
*/

// Cobra Cache Array
$__cobra_cache = array();


// basic exception class
class coException extends Exception {
  public function __construct( $exception = NULL ) {
    parent::__construct( empty( $exception ) ? __METHOD__ : $exception );
  }
}


// basic cobra class
class coCobra implements ArrayAccess {
  private $__cobra = NULL;

  public function __construct( $cobra = NULL, $config = NULL ) {
    if( empty( $cobra ) || empty( $config ) )
      throw new coException( __METHOD__ );

    $this->__cobra = $cobra;
    $this->config( $config );
    $this->cache( 'cobra', $this->__cobra['path.classes'], true );

    $config = $this->__cobra['sys.config'];
    if( empty( $config['app.bootstrap'] ) )
      return;

    foreach( $config['app.bootstrap'] as $app ) {
      $this->bootstrap( $app );
    }
  }


  // begin ArrayAccess interface
  public function offsetSet( $offset, $value ) {
    $this->__cobra[$offset] = $value;
  }

  public function offsetExists( $offset ) {
    return isset( $this->__cobra[$offset] );
  }

  public function offsetUnset( $offset ) {
    unset( $this->__cobra[$offset] );
  }

  public function offsetGet( $offset ) {
    if( isset( $this->__cobra[$offset] ) )
      return $this->__cobra[$offset];

    throw new coException( __METHOD__ );
  }
  // end ArrayAccess interface


  public function config( $config = NULL ) {
    if( empty( $config ) )
      throw new coException( __METHOD__ );

    if( empty( $this->__cobra['sys.config'] ) )
      $this->__cobra['sys.config'] = $config;
    else
      $this->__cobra['sys.config'] = array_merge( $this->__cobra['sys.config'], $config );
    return true;
  }


  public function cache( $cache_id = NULL, $cache_path = NULL, $recursive = true ) {
    if( empty( $cache_id ) || empty( $cache_path ) )
      throw new coException( __METHOD__ . '::' . $cache_id );

    $cache_arr = array( $cache_id => array( 'path' => $cache_path, 'recursive' => $recursive ) );
    if( isset( $this->__cobra['sys.cache'] ) )
      $this->__cobra['sys.cache'] = array_merge( $this->__cobra['sys.cache'], $cache_arr );
    else
      $this->__cobra['sys.cache'] = $cache_arr;
    return true;
  }


  function bootstrap( $app_id = NULL ) {
    if( empty( $app_id ) || ! isset( $this->__cobra['path.apps'] ) )
      throw new coException( __METHOD__ . '::' . $app_id );

    // load bootstrap
    $app_bootstrap = $this->__cobra['path.apps'] . '/' . cobra_safe_string( $app_id ) . '/' . COBRA_APP_BOOTSTRAP;
    if( is_readable( $app_bootstrap ) ) {
      require_once( $app_bootstrap );
    }
    
    $app_sys = isset( ${$app_id} ) ? ${$app_id} : array();
    $app_arr = array( $app_id => array( 'sys.app' => $app_sys, 'sys.config' => isset( $config ) ? $config : array() ) );
    if( isset( $this->__cobra['sys.apps'] ) )
      $this->__cobra['sys.apps'] = array_merge( $app_arr, $this->__cobra['sys.apps'] );
    else
      $this->__cobra['sys.apps'] = $app_arr;

    // set cache
    if( isset( $app_sys['path.classes'] ) )
      $this->cache( $app_id, $app_sys['path.classes'], true );

    return true;
  }

} // end class


// functions
function cobra_die( $str = '', $exit = 1 ) {
/*!
    \brief the die function
    \param $str error message
    \param $exit exit no
*/
  cobra_ob_clean();
  if( COBRA_CLI ) {
    cobra_print( $str );
  }
  else {
    $cobra  = cobra_cache_fetch( 'cobra' );
    $error_html = $cobra['path.errors'] . '/' . basename( $str ) . '.html';
    if( is_readable( $error_html ) ) {
      readfile( $error_html );
    }
    else {
      echo json_encode( array( 'type' => 'exception', 'data' => $str ) );
    }
  }
  exit( $exit );
}


function cobra_exception_handler( $exception = NULL ) {
/*!
    \brief Global exception handler
    \param $exception
*/
  cobra_die( $exception->getMessage() );
}


function cobra_redirect( $dir = NULL, $page = NULL, $direct = true ) {
  $dir  = basename( $dir );
  $page = basename( $page );

  $link = $dir . '/' . $page;
  if( is_readable( $link ) ) {
    if( $direct ) {
      readfile( $link );
    }
    else {
      cobra_ob_clean();
      $link = '<HTML><HEAD><META HTTP-EQUIV="refresh" CONTENT="0;URL='.$link.'"></HEAD></HTML>';
      print $link;
    }
  }
  else {
    cobra_die( __FUNCTION__ );
  }
}


function cobra_define( $id = NULL, $v = NULL ) {
/*!
    \brief define globals
    \param $id name of the global
    \param $v value of the global
    \param $force force to redefine
*/
  return defined( $id ) ? false : define( $id, $v );
}


function cobra_safe_string( $str = NULL ) {
  return mb_ereg_replace( '[^[:alpha:]_]', '', $str );
}


function cobra_print( $str = '' ) {
/*!
    \brief echo a string with the correct en of line trailing
    \param $str string
*/
  echo $str . COBRA_EOL;
}

function cobra_encoding( $encoding = 'UTF-8' ) {
/*!
    \brief set encoding
    \param $encoding encoding
*/
  mb_internal_encoding( $encoding );
  mb_regex_encoding( $encoding );
}

function cobra_ob_flush() {
  if( COBRA_OB && ! COBRA_CLI ) ob_flush();
}


function cobra_ob_start() {
  if( COBRA_OB && ! COBRA_CLI ) ob_start();
}


function cobra_ob_clean() {
  if( COBRA_OB && ! COBRA_CLI ) ob_end_clean();
}


function cobra_ob_restart() {
  cobra_ob_clean();
  cobra_ob_start();
}


function cobra_cache_store( $id = NULL, $var = NULL ) {
  global $__cobra_cache;
  
  if( empty( $id ) || empty( $var ) )
    throw new coException( __FUNCTION__ );

  $__cobra_cache[$id] = $var;

  if( ! isset( $var['sys.cache'] ) )
    return true;

  foreach( $var['sys.cache'] as $cache => $desc ) {
    $cache_file = $var['path.cache'] . '/' . $cache . '.' . COBRA_CACHE_EXTENSION;
    if( is_readable( $cache_file ) )
      $__cobra_cache[$cache . '.' . COBRA_CACHE_EXTENSION] = file( $cache_file );
  }
  return true;  
}


function cobra_cache_fetch( $id = NULL ) {
  global $__cobra_cache;
  
  if( empty( $id ) || ! isset( $__cobra_cache[$id] ) )
    throw new coException( __FUNCTION__ );

  return $__cobra_cache[$id];
}


function cobra_apc_autoload( $class = NULL, $cache_id = NULL ) {
/*!
    \brief apc autoloader
    \param $class class name
    \param $cache_id cache name
*/
  $cache = cobra_cache_fetch( $cache_id . '.' . COBRA_CACHE_EXTENSION );
  $cache = unserialize( $cache[0] );
  
  if( ! isset( $cache[$class] ) ) throw new coException( __FUNCTION__ . ' ' . $class . ' ' . $cache_id );

  require_once( $cache[$class] );
  return true;
}


function cobra_autoload( $class = NULL, $cache_id = NULL ) {
/*!
    \brief class autoloader
    \param $class class name
*/
  $class = cobra_safe_string( $class );
  $cache_id = cobra_safe_string( $cache_id );
  
  if( ! empty( $cache_id ) ) {
    return cobra_apc_autoload( $class, $cache_id );
  }

  $cobra = cobra_cache_fetch( 'cobra' );
  foreach( $cobra['sys.cache'] as $cache_id => $desc ) {
    try {
      return cobra_apc_autoload( $class, $cache_id );
    } catch( Exception $e ) {}
  }

  throw new coException( __FUNCTION__ );
}


function __autoload( $class = NULL ) {
  try {
    return cobra_autoload( $class, 'cobra' );
  } catch( Exception $e ) {
    return false;
  }
}


function cobra_init( $encoding = 'UTF-8', $error_handler = NULL ) {
  // defines
  cobra_define( 'COBRA', true );
  cobra_define( 'COBRA_DEBUG', 9 );
  cobra_define( 'COBRA_CLI', ( PHP_SAPI == 'cli' ? true : false ) );
  cobra_define( 'COBRA_OB', true );
  cobra_define( 'COBRA_EOL', COBRA_CLI ? PHP_EOL : '<br>'.PHP_EOL );
  cobra_define( 'COBRA_CLASS_EXTENSION', 'php' );
  cobra_define( 'COBRA_APP_BOOTSTRAP', 'bootstrap.php' );
  cobra_define( 'COBRA_CACHE_EXTENSION', 'cache' );
  // output buffering
  cobra_ob_start();

  // encoding
  cobra_encoding( $encoding );
  
  // exception handling
  if( ! empty( $error_handler ) )
    set_exception_handler( $error_handler );
    
  return true;
}

?>
