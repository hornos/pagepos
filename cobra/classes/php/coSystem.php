<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

//
// System Management with Stored Procedures
//

// TODO: error messages
class coSystemException extends Exception {
  public function __construct( $message = __CLASS__ ) {
    parent::__construct( $message );
  }
}


class coSystem extends coSession {
  public function __construct( $config = NULL ) {
	parent::__construct( $config );
  }

  protected function _user_expiration() {
    return $this['system.user_expiration'];
  }

  protected function _max_login_tries() {
    return $this['system.max_login_tries'];
  }

  //
  // Data Access
  //
  protected function _users_read( $user_id = NULL ) {
	try {
	  $user = $this->CallProcedureSelectRow( 'users_read', array( $user_id ) ); 
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );
	}
	return $user;
  }


  protected function _groups_read( $group_id = NULL ) {
	try {
	  $group = $this->CallProcedureSelect( 'groups_read', array( $group_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__  );
	}
	return $group;
  }


  protected function _applications_read( $app_id = NULL ) {
	try {
	  $application = $this->CallProcedureSelect( 'applications_read', array( $app_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__  );
	}
	return $application;
  }


  protected function _modules_read( $app_id = NULL, $module_id = NULL ) {
	try {
	  $module = $this->CallProcedureSelect( '_modules_read', array( $app_id, $module_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );
	}
	return $module;
  }


  private function __permission( $user_id = NULL, $group_id = NULL, $perm = NULL ) {
	$permission = $perm['permission'];
	$uperm = floor( $permission / 100 );
	$gperm = floor( ( $permission - $uperm * 100 ) / 10 );
	$operm = floor( $permission - $uperm * 100 - $gperm * 10 );
	if( $user_id == $perm['user_id'] ) return $uperm;

	if( $group_id == $perm['group_id'] ) return $gperm;

	return $operm;
  }


  protected function _permissions_check_read( $user_id = NULL, $group_id = NULL, $app_id = NULL, $module_id = NULL, $method_id = NULL ) {
	try {
	  $perm = $this->CallProcedureSelectRow( 'permissions_check_read', array(  $user_id, $group_id, $app_id, $module_id, $method_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );
	}
    return $this->__permission( $user_id, $group_id, $perm );
  }


  protected function _connections_read( $app_id = NULL, $user_id = NULL, $group_id = NULL ) {
    // 1. try for the user
    try {
      $connection = $this->CallProcedureSelect( 'user_connections_read', array( $app_id, $user_id ) );
	} catch( Exception $e ) {
	  // 2. try for the group
	  try {
        $connection = $this->CallProcedureSelect( 'group_connections_read', array( $app_id, $group_id ) );  
	  } catch ( Exception $e ) {
	    throw new coSystemException( __METHOD__ );
	  }
	  return $connection;
	}
	return $connection;
  }


  //
  // Actions
  //  
  protected function _increment_login_tries( $user_id = NULL ) {
    if( $this->_max_login_tries() == 0 )
      return true;
	try {
	  $result = $this->CallProcedure( 'users_increment_login_tries', array( $user_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );
	}
	return $result;
  }


  protected function _reset_login_tries( $user_id = NULL ) {
	try {
	  $result = $this->CallProcedure( 'users_reset_login_tries', array( $user_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );    
	}
  }
  
  
  protected function _login( $app_id = NULL, $user_id = NULL ) {
	try {
	  $result = $this->CallProcedure( 'users_login', array( $app_id, $user_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );    
	}	
  }


  protected function _logout( $user_id = NULL ) {
	try {
	  $result = $this->CallProcedure( 'users_logout', array( $user_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );    
	}	
  }


  protected function _save_user( $user = NULL ) {
    $usr = array( 'user_id'    => $user['user_id'],
	              'group_id'   => $user['group_id'],
				  'app_id'     => $user['app_id'],
	              'grace_time' => $user['grace_time'],				  
	              'locale'     => $user['locale'] );
	$this->save( 'user', $usr );
  }


  protected function _save_connection( $connection = NULL ) {	
/*	$conn = array( 'dbhost' => $dbconn['dbhost'],
				   'dbname' => $dbconn['dbname'],
				   'dbuser' => $dbconn['dbuser'],
				   'dbport' => $dbconn['dbport'],
				   'dbpass' => $dbconn['dbpass'],
				   'engine' => $dbconn['engine'] );
*/
	$this->save( 'connection', $connection );
  }  


  protected function _save_last_action_time( $user_id = NULL ) {
	try {
	  $result = $this->CallProcedure( 'users_set_last_action_time', array( $user_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );    
	}
	$this->save( 'last_action_time', $result );
  }


  protected function _clean() {
	$this->erase( 'user' );
	$this->erase( 'connection' );
  }

 
  //
  // Checks
  //
  protected function _check_passcode( $user_id = NULL, $passcode = NULL, $passtype = 'SHA1' ) {
	$passcode = coCrypt::encrypt_passcode( $passcode, $passtype );
	try {
	  $result = $this->CallProcedure( 'users_check_passcode', array( $user_id, $passcode ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );
	}
	return coString::is_true( $result );
  }


  protected function _check_login_tries( $user = NULL ) {
    $max = $this->_max_login_tries();
    if( $max == 0 )
      return true;
	$login_tries = $user['login_tries'];
	if( $login_tries > $max ) {
	  throw new coSystemException( __METHOD__ );
	}
    return true;
  }
  
  
  protected function _check_grace_time( $user = NULL, $last_action_time = NULL ) {
    if( empty( $last_action_time ) ) {
	  $last_action_time = $this->load( 'last_action_time' );
	}
	$grace_time = $user['grace_time'];
	$action_time = $this->_time - $last_action_time;
	
	if( $action_time > $grace_time ) {
	  throw new coSystemException( __METHOD__ );
	}
	return true;
  }


  protected function _check_online( $user_id = NULL ) {
	try {
	  $result = $this->CallProcedure( 'users_check_online', array( $user_id ) );
	} catch( Exception $e ) {
	  // coDebug::message( basename(__FILE__).'['.__LINE__.'] '.__METHOD__.'  Exception  '.$e->getMessage() );
	  throw new coSystemException( __METHOD__ );    
	}
	return coString::is_true( $result );  
  }


  protected function _check_online_grace( $user_id = NULL, $user = NULL ) {
    // is online ?
	$this->_check_online( $user_id );
    // grace exceeded ?
	$last_action_time = $this->load( 'last_action_time' );
	$this->_check_grace_time( $user, $last_action_time );
	return $last_action_time;
  }


  protected function _check_save_user( $user = NULL, $last_action_time = NULL ) {
    if( empty( $last_action_time ) ) {
	  $last_action_time = $this->load( 'last_action_time' );
	}  
    $action_time = $this->_time - $last_action_time;
	if( $action_time > $this->_user_expiration() ) {
	  $this->_save_user( $user );
	}
	return true;
  }
  
  
  //
  //  LOGIN
  //

  public function login( $user_id = NULL, $passcode = NULL, $app_id = NULL ) {
    if( empty( $user_id ) || empty( $passcode ) || empty( $app_id ) ) {
	  throw new coSystemException( __METHOD__ );
	}
    $this->start();
    coDebug::message( 'Start login' );

	// 1. valid
	$user = $this->_users_read( $user_id );
    $group_id = $user['group_id'];
    coDebug::message( 'User OK' );

    // 2. login tries
	$this->_check_login_tries( $user );
    coDebug::message( 'Tries OK' );
	
	// 3. passcode
	try {
	  $this->_check_passcode( $user_id, $passcode );
	} catch( Exception $e ) {
	  $this->_increment_login_tries( $user_id );
	  throw $e;
	}
    coDebug::message( 'Passcode OK' );
   
	// 4. online
	try {
	  $this->_check_online( $user_id );
	  $this->_expired();
	} catch( Exception $e ) {
	  coDebug::message( 'New login' );
	  $this->_login( $app_id, $user_id );
	  $user['app_id'] = $app_id;
	  $this->_save_last_action_time( $user_id );
	  $this->_save_user( $user );
      try {
	    $connection = $this->_connections_read( $app_id, $user_id, $group_id );
        coDebug::message( 'Connection OK' );
      } catch( Exception $e ) {
	    coDebug::message( 'Login OK (without DB connection)' );
		return true;
	  }
	  $this->_save_connection( $connection );
	  coDebug::message( 'Login OK' );
	  return true;
	}
	
	coDebug::message( 'Continue old login' );
	// strict
	$this->_save_last_action_time( $user_id );
//	$last_action_time = $this->load( 'last_action_time' );
//	$this->_check_grace_time( $user, $last_action_time );
//	$this->_check_save_user( $user, $last_action_time );
	coDebug::message( 'Login Check OK' );
/*
	print session_id() . '<br>';
	print_r( $_SESSION );
    print '<br><br>';
	print_r( $_COOKIE );
*/
    return true;
  } // end login


  public function logout() {
    $this->start();
	try {
	  $this->_expired();
	  $user = $this->load( 'user' );
	  // coDebug::message( 'Load user from session' );
	  $user_id = $user['user_id'];
	  $user = $this->_users_read( $user_id );
	  $this->_check_online( $user_id );
	  $this->_logout( $user_id );
	} catch( Exception $e ) {
	  try{ $this->_clean(); } catch( Exception $e ) {}
	  throw $e;
	}
	try{ $this->_clean(); } catch( Exception $e ) {}
	return true;
  }


  public function authenticate( $app_id = NULL, $module_id = NULL, $method_id = NULL ) {
    $this->start();
/*
	print session_id() . '<br>';
	print_r( $_SESSION );
    print '<br><br>';
	print_r( $_COOKIE );
*/
	try {
	  $this->_expired();
	} catch( Exception $e ) {
	  $this->stop( false );
	  throw $e;
	}

    $user     = $this->load( 'user' );
	$user_id  = $user['user_id'];
	$user     = $this->_users_read( $user_id );
	$group_id = $user['group_id'];


    // is online ?
	$last_action_time = $this->_check_online_grace( $user_id, $user );

/*
    $permission = $this->_permissions_check_read( $user_id, $group_id, $app_id, $module_id, $method_id );
	if( $permission == 0 ) throw new coSystemException( __METHOD__ );
*/
    // svae session
	$this->_check_save_user( $user, $last_action_time );
	$this->_save_last_action_time( $user_id );
//    return $permission;
    return true;
  }
  
} // end coSession

?>
