<?php
// Direct access protection
if( ! defined( 'COBRA' ) ) die( 'No direct access' );

// Global cobra defines
cobra_define( 'COBRA_DB_SELECT_LIMIT', 128 );
cobra_define( 'COBRA_DB_PROCEDURE_SIZE', 128 );


// Exception class
class coDBException extends coException {
  public function __construct( $message = __CLASS__ ) {
    parent::__construct( $message );
  }
}


// Class
class coDB implements ArrayAccess {
  private $__config;    /**< config array */
  private $__dbconn;    /**< database connection object */

  // Constructor
  public function __construct( $config = NULL ) {
    if( empty( $config ) )
      throw new coDBException( __METHOD__ );
    /*!
      \param $config System profile which contains DB connection parameters
    */
    $this->__config = $config;
    $this->_set_dbconn( NULL );
  }


  // begin ArrayAccess interface
  public function offsetSet( $offset, $value ) {
    /*! ArrayAccess interface */
    $this->__config[ $offset ] = $value;
  }

  public function offsetExists( $offset ) {
    /*! ArrayAccess interface */
    return isset( $this->__config[$offset] );
  }
  
  public function offsetUnset( $offset ) {
    /*! ArrayAccess interface */
    unset( $this->__config[$offset] );
  }

  public function offsetGet( $offset ) {
    /*! ArrayAccess interface */
    if( isset( $this->__config[$offset] ) ) return $this->__config[$offset];

	throw new coDBException( __METHOD__ . '::' . $offset );
  }
  // end ArrayAccess interface


  // Time
  public function time() {
    try {
	  return $this->CallProcedure( 'cobra_time' );
	} catch( Exception $e ) {
	  return parent::time();
	}
  }

  public function microtime() {
    try {
	  return $this->CallProcedure( 'cobra_microtime' );
	} catch( Exception $e ) {
	  return parent::microtime();
	}
  }

  public function timestamp() {
    try {
	  return $this->CallProcedure( 'cobra_timestamp' );
	} catch( Exception $e ) {
	  return parent::timestamp();
	}
  }


  // Connection
  protected function _get_dbconn() {
    /*! Checks and returns the valid connection object */
    if( ! $this->__dbconn ) throw new coDBException( __METHOD__ );
	
	return $this->__dbconn;
  }

  protected function _set_dbconn( $dbconn = NULL ) {
    /*! Sets the connection object */
    $this->__dbconn = $dbconn;
  }

  public function Connect() {
	try {
	  $this->_get_dbconn();
	} catch( Exception $e ) {
	  $dsn  = $this['db.engine'].':';
	  $dsn .= 'host='.$this['db.host'].';';
	  $dsn .= 'port='.$this['db.port'].';';
	  $dsn .= 'dbname='.$this['db.name'];
	  // connect
	  $this->_set_dbconn( new PDO( $dsn, $this['db.user'], $this['db.pass'], $this['db.pdo_attributes'] ) );
	  // coDebug::message( 'Database connected' );
	  return true;
	}
    return false;	
  } // end Connect

  public function Disconnect() {
    $this->_set_dbconn( NULL );
	// coDebug::message( 'Database disconnected' );
  }
    

  // DB Access  
  protected function _Query( $query, $is_select = true ) {
    $dbconn = $this->_get_dbconn();

    if( empty( $query ) ) throw new coDBException( __METHOD__ );
    
	$statement = $dbconn->prepare( $query );
	if( ! $statement->execute() ) throw new coDBException( __METHOD__ . " " . implode( ",", $statement->errorInfo() ) );

    // coDebug::message( $query, 9, 'SQL' );
	
	// SELECT
	if( $is_select ) {
	  $affrows = $statement->columnCount();
	  if( $affrows < 1 ) throw new coDBException( __METHOD__ );

	  // coDebug::message( 'Affected Rows: '.$affrows );
	  $statement->setFetchMode( PDO::FETCH_ASSOC ); 
	  $rarray = $statement->fetchAll(); 
	  if( ! $rarray ) throw new coDBException( __METHOD__ );

	  return $rarray;
	}
	
	// INSERT, UPDATE, DELETE
	$affrows = $statement->rowCount();
	if( $affrows < 1 ) throw new coDBException( __METHOD__ . " no affected rows");
	
	// coDebug::message( 'Affected Rows: '.$affrows );
    return $affrows;	
  } // end _Query

  public function Execute( $query ) {
    return $this->_Query( $query, false );
  }

  public function Select( $query, $limit = COBRA_DB_SELECT_LIMIT, $offset = 0 ) {
    $suffix  = ( $limit  > 0 ) ? ' LIMIT '  . $limit  : '';
    $suffix .= ( $offset > 0 ) ? ' OFFSET ' . $offset : '';
    return $this->_Query( $query );
  }

  public function SelectRow( $query, $limit = COBRA_DB_SELECT_LIMIT, $offset = 0, $row = 0 ) {
    $results = $this->Select( $query, $limit, $offset );
	return $results[$row];
  }


  // Procedures
  public function CallProcedure( $procedure = NULL, $arguments = NULL ) {
	if( empty( $procedure ) ) throw new coDBException( __METHOD__ );

    $procedure = coString::subalnum( $procedure, COBRA_DB_PROCEDURE_SIZE );

    // begin query	
	$query  = 'SELECT ' . $procedure;
    $query .= '(' . coString::a2f( $arguments, '', true ) . ')';

	$result = $this->SelectRow( $query, 0, 0, 0 );
	if( isset( $result[$procedure] ) ) {
	  return $result[$procedure];
	}
	return $result;
  }
  
  public function CallProcedureSelect( $procedure = NULL, $arguments = NULL, $fields = NULL, $limit = COBRA_DB_SELECT_LIMIT, $offset = 0 ) {
	if( empty( $procedure ) ) throw new coDBException( __METHOD__ );
    
    $procedure = coString::subalnum( $procedure, COBRA_DB_PROCEDURE_SIZE );
    $fields    = coString::a2f( $fields );
	
	$query  = 'SELECT ' . $fields . ' FROM ' . $procedure;
    $query .= '(' . coString::a2f( $arguments, '', true ) . ')';	
	return $this->Select( $query, $limit, $offset );
  }
  
  public function CallProcedureSelectRow( $procedure = NULL, $arguments = NULL, $fields = NULL, $limit = COBRA_DB_SELECT_LIMIT, $offset = 0, $row = 0 ) {
    $result = $this->CallProcedureSelect( $procedure, $arguments, $fields, $limit, $offset );
    return $result[$row];
  }

} // end class

?>
