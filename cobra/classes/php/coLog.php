<?php
// TODO: rewrite

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

// inline css always works
class coLog {
  private $__logfile;
  
  public function __construct( $logfile = NULL ) {
    if( is_null( $logfile ) ) throw new coException( __METHOD__ );

    $cobra  = cobra_cache_fetch( 'cobra' );
    $this->__logfile = $cobra['path.log'] . '/' . basename( $logfile ) . '.log';
  }
  
  public function record( $str, $append = true ) {
    return file_put_contents( $this->__logfile, $str, $append ? FILE_APPEND | FILE_TEXT : FILE_TEXT );
  }
}

?>
