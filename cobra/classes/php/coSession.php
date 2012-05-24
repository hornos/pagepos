<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

//
// Session Management with Stored Procedures
//

cobra_define( 'COBRA_SESSION_ID_SIZE', 128 );

class coSessionException extends Exception {
  public function __construct( $message = __CLASS__ ) {
    parent::__construct( $message );
  }
}


class coSession extends coDB {
  protected $_time;
  protected $_microtime;

  public function __construct( $config = NULL ) {
	parent::__construct( $config );
	$this->_time      = time();
	$this->_microtime = microtime();
	
	// register session handlers
    session_set_save_handler( 
  	  array( &$this, 'open' ), 
  	  array( &$this, 'close' ),
      array( &$this, 'read' ),
      array( &$this, 'write' ),
      array( &$this, 'destroy' ),
      array( &$this, 'gc' )
    );
  } // end construct


  // Open the session
  public function open( $save_path, $session_name ) {
    /*! Session interface */
	global $sess_save_path;
	$sess_save_path = $save_path;
	return true;
  }


  // Close the session
  public function close() {	
    /*! Session interface */
	try {
      $this->Disconnect();
    } catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  return false;
	}
	return true;
  }


  // Read session data
  public function read( $sid ) {
    /*! Session interface */
    $sid = $this->_validate_id( $sid );
	try {
	  $result = $this->CallProcedure( 'sessions_read', array( $sid ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  return false;
	}
	return $result;
  }


  // Write session data
  public function write( $sid, $data ) {
    /*! Session interface */
    $sid     = $this->_validate_id( $sid );
    $expires = $this->_expiration();	
	try {
	  $this->CallProcedure( 'sessions_write', array( $sid, $expires, $data ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
      return false;
	}
	return true;
  }


  // Destroy session data
  public function destroy( $sid ) {
    /*! Session interface */
    $sid   = $this->_validate_id( $sid );
	// outdate the cookie
    if( session_id() != "" || isset( $_COOKIE[session_name()] ) ) {
      setcookie( session_name(), '', 0 );
	}
	try {
	  $this->CallProcedure( 'sessions_destroy', array( $sid ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  return false;
	}
	return true;
  }


  // Garbage collector
  public function gc() {
    /*! Session interface */
	return true;
  }


  // user must conatin a time field for session expiration for logout scripts
  protected function _expired() {
    $sid = session_id();
	try {
	  $result = $this->CallProcedure( 'sessions_expired', array( $sid ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
      throw new coSessionException( __METHOD__ );
	}
	return $result;	
  }



  //
  // Access Sysprofile
  //
  
  // get session encryption key
  protected function _session_key() {
 	$key = $this['session.key'];
	if( empty( $key ) ) throw new coSessionException( __METHOD__ );
	return $key;
  }


  // if strict check is on client ip and http user agent is stored and checked
  protected function _strict_client_check() {
    return $this['session.strict_client_check'];
  }


  protected function _session_name() {
    return $this['session.name'];
  }


  // default session expiration time
  protected function _expiration() {
	return $this['session.expiration'];
  }


  // key regeneration time
  protected function _id_expiration() {
    return $this['session.id_expiration'];
  }


  //
  // Session ID
  //

  // format session id
  protected function _validate_id( $sid ) {
    return coString::subalnum( $sid, COBRA_SESSION_ID_SIZE );
  }

  // TODO: add salt
  protected function _generate_id() {
    return sha1( uniqid( $this->_microtime ).$this->_remote_addr().$this->_http_user_agent() );
  }


  //
  // Access Globals
  //
  protected function _remote_addr() {
    return $_SERVER['REMOTE_ADDR'];
  }

  protected function _http_user_agent() {
    return $_SERVER['HTTP_USER_AGENT'];
  }


  //
  // Cookies
  //
  protected function _renew_cookie() {
    $expires = $this->_time + $this->_expiration();
    unset( $_COOKIE[session_name()] );
	setcookie( session_name(), session_id(), $expires );  
  }


  //
  // Session
  //
  protected function _change_id() {
    $sid     = session_id();
	$new_sid = $this->_generate_id();
	
	try {
	  $result = $this->CallProcedure( 'sessions_change_id', array( $sid, $new_sid ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
      return false;
	}
	session_id( $new_sid );
	$this->_renew_cookie();
	$this->save( 'last_id_change_time', $this->_time );
	// coDebug::message( 'New session id: '.$new_sid );
	return true;
  }


  protected function _check_change_id() {
    if( $this->_id_expiration() == 0 )
      return true;
	$delta_time = $this->_time - $this->load( 'last_id_change_time' );
	if( $delta_time > $this->_id_expiration() ) {
	  return $this->_change_id();
	}
	return false;
  }


  //
  // Strict Client Check
  //  
  protected function _check_client_ip() {
 	if( $this->load( 'ip_address' ) != $this->_remote_addr() ) {
	  throw new coSessionException( __METHOD__ );
	}
	return true;
  }


  protected function _check_client_user_agent() {
	if( $this->load( 'user_agent' ) != $this->_http_user_agent() ) {
	  throw new coSessionException( __METHOD__ );
	}
	return true;
  }
  
  
  //
  // Low Level Read and Write Session Data
  //
  protected function _set( $id, $val ) {
    $_SESSION[$id] = $val;
	return true;
  }


  protected function _del( $id ) {
    if( isset( $_SESSION[$id] ) ) {
      unset( $_SESSION[$id] );
	  return true;
	}
	throw new coSessionException( __METHOD__ );
  }


  protected function _get( $id ) {
    if( isset( $_SESSION[$id] ) ) return $_SESSION[$id];

	throw new coSessionException( __METHOD__ . ' ' . $id );
  }


  //
  // Top Level Read and Write Session Data with Encryption
  //  
  public function save( $id, $data = false ) {
    try {
      $key = $this->_session_key();
	} catch( Exception $e ) {
	  // store data unencrypted
	  return $this->_set( $id, serialize( $data ) );
	}
	// store data encrypted
	$id   = coCrypt::encrypt( serialize( $id ), $key );
	$data = coCrypt::encrypt( serialize( $data ), $key );
	return $this->_set( $id, $data );
  }


  public function load( $id ) {
    try {
      $key = $this->_session_key();
	} catch( Exception $e ) {
	  // load data unencrypted
	  $result = unserialize( $this->_get( $id ) );
	  return $result;
	}
	// load data encrypted
	$id   = coCrypt::encrypt( serialize( $id ), $key );
	$data = coCrypt::decrypt( $this->_get( $id ), $key );
	$result = unserialize( $data );
	return $result;
  }


  public function erase( $id ) {
    try {
      $key = $this->_session_key();
	} catch( Exception $e ) {
	  return $this->_del( $id );
    }
	$id = coCrypt::encrypt( serialize( $id ), $key );
	return $this->_del( $id );	
  }
  
  
  //
  // Start and Stop the Cobra Session
  //

  public function start() {
    // connect to the db
    $this->Connect();
	// get the time
	$this->_time      = $this->time();
	$this->_microtime = $this->microtime();
	
	// set session name in the cookie
    session_name( $this->_session_name() );
	// start or continue the session
    session_start();

    try {
	  $this->_expired();
	} catch( Exception $e ) {
	  // coDebug::message( 'Start a new session' );
	  // expired or no such sessions therefore
	  // start a new session, generate and set the session id
	  session_id( $this->_generate_id() );
	  // store session data
	  $this->save( 'last_id_change_time', $this->_time );

      // store date for strict session checking
      if( $this->_strict_client_check() ) {	  
	    $this->save( 'ip_address', $this->_remote_addr() );
	    $this->save( 'user_agent', $this->_http_user_agent() );
	  }
	  // reset session cookie id and time
	  $this->_renew_cookie();
	  return true;
	}

    // continue an old session
	// coDebug::message( 'Continue old session' );
	if( $this->_strict_client_check() ) {
      // check ip address
	  $this->_check_client_ip();	  
	  // check user agent
	  $this->_check_client_user_agent();
    }
	// if needed regenerate session id
	$this->_check_change_id();
	return true;
  } // end start


  public function stop( $start_session = true ) {
    // connect to the db
    $this->Connect();	
	// set session name in the cookie
    session_name( $this->_session_name() );
	// start or continue the session
	
    if( $start_session ) session_start();

    try {
	  $this->_expired();
	} catch( Exception $e ) {
      session_destroy();
      return false;
    }
    return session_destroy();
  }  

} // end coSession

?>
