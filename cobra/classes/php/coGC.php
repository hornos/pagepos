<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

class coGCException extends coException {
  public function __construct( $message = __CLASS__ ) {
    parent::__construct( $message );
  }
}


class coGC extends coDB {
  public function __construct( $sysprofile = NULL ) {
    parent::__construct( $sysprofile );
  }


  //
  // Session Garbage Collector
  public function sessions_gc() {
    try {
      $result = $this->CallProcedure( 'sessions_gc' );
	} catch( Exception $e ) {
      throw new coGCException( __METHOD__ );    
	}
    $ts = $this->timestamp();
    cobra_print( $ts.' Cobra GC: '.$result.' session(s) was deleted' );
  }
  

  //
  // User Garbage Collector
  public function users_gc() {
    try {
	  $result = $this->CallProcedure( 'users_gc' );
	} catch( Exception $e ) {
	  throw new coGCException( __METHOD__ );    
	}
    $ts = $this->timestamp();
    cobra_print( $ts.' Cobra GC: '.$result.' user(s) was logged out' );
  }
  
} // end coGC

?>
