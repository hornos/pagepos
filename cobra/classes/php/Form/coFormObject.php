<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormObject implements ArrayAccess, Iterator {
  private $__attributes;
  private $__values;
  private $__valpos;

  public $label;
  public $fields;
  private $__table;

  public function __construct( $attributes = array() ) {
    $this->__attributes = array_merge( array( 'id' => __CLASS__, 'name' => __CLASS__, 
                                              'sqlid' => __CLASS__, 'class' => __CLASS__, 
                                              'validable' => false, 'updateable' => false, 'required' => false ), $attributes );
  	$this->__values = array();
    $this->__valpos = 0;
    $this->label = NULL;
    $this->fields = array();
    $this->__table = NULL;
  }
  
  //
  // Access attributes (ArrayAccess interface)
  //  
  public function offsetSet( $offset, $value ) {
    $this->__attributes[$offset] = $value;
  }

  public function offsetExists( $offset ) {
    return isset( $this->__attributes[$offset] );
  }
  
  public function offsetUnset( $offset ) {
    unset( $this->__attributes[$offset] );
  }

  public function offsetGet( $offset ) {
    if( isset( $this->__attributes[$offset] ) ) {
	  return $this->__attributes[$offset];
	}
	throw new coException( __METHOD__ . ' ' . $offset );
  }


  public function attributes( $attributes = NULL ) {
    if( $attributes == NULL ) {
      return $this->__attributes;
    }
    if( ! is_array( $attributes ) ) {
	  throw new coException( __METHOD__ );
	}
	$this->__attributes = array_merge( $this->__attributes, $attributes );
	return $this->__attributes;
  }


  public function id( $id = NULL ) {
    if( is_string( $id ) ) {
	  $this->attributes( array( 'id' => $id, 'name' => $id, 'sqlid' => $id ) );
	  return $id;
	}
    return $this['id'];
  }
 

  
  // TODO: set / get filters  
  protected function _key( $i = 0 ) {
    $keys = array_keys( $this->__values );
    if( is_numeric( $i ) ) {
	  if( isset( $keys[$i] ) && isset( $this->__values[$keys[$i]] ) ) {
        return $keys[$i];
	  }
	  throw new coException( __METHOD__ );
	}
	return $i;
  }


  public function set( $value = '', $i = 0, $sqlid = '' ) {
	$this->__values[$i] = array( 'value' => $value, 'sqlid' => $sqlid );
  }


  public function &get( $i = 0 ) {
    $key = $this->_key( $i );
	if( isset( $this->__values[$key] ) ) {
	  $values = $this->__values[$key];
	  return $values['value'];
    }
	throw new coException( __METHOD__ );	
  }


  public function del( $i = 0 ) {
    $key = $this->_key( $i );
	if( isset( $this->__values[$key] ) ) {
      unset( $this->__values[$key] );
	  return true;
	}
	throw new coException( __METHOD__ );		
  }

                
  public function rewind() {
    $this->__valpos = 0;
  }

  public function &current() {
    try {
      $key = $this->_key( $this->__valpos );
    } catch( Exception $e ) {
      return false;
    }
    $values = $this->__values[$key];
	return $values['value'];    
  }
  
  public function key() {
    try {
      $key = $this->_key( $this->__valpos );
    } catch( Exception $e ) {
      return false;
    }
    return $key;
  }

  public function next() {
    ++$this->__valpos;
  }

  public function valid() {
    try {
      $key = $this->_key( $this->__valpos );
    } catch( Exception $e ) {
      return false;
    }
    return isset( $this->__values[$key] );  
  }

  public function validate() {
    $valid = true;
    foreach( $this->fields as $field ) {
      try {
        $valid = $valid && $field->validate();
      } catch( Exception $e ) {}
    }
    return $valid;
  }

  protected function _pre_html() {
    try {
      $legend = $this['fieldset'];
      coHTML::out( '<fieldset>' );
      coHTML::out( '<legend class="'.$this->id().'">'.$legend.'</legend>' );
    } catch( Exception $e ) {}
  }


  protected function _post_html() {
    try {
      $legend = $this['fieldset'];
      coHTML::out( '</fieldset>' );
    } catch( Exception $e ) {}
  }

  protected function _pack( $table_attributes = array() ) {
   $table_attributes = array_merge( array( 'cols' => 2, 'rows' => 1 ), $table_attributes );
   $objects = array( &$this->label );
   for( $i = 0; $i < sizeof( $this->fields ); ++$i ) {
     $objects = array_merge( $objects, array( &$this->fields[$i] ) );
   }
   $this->__table = new coFormTable( $this->id() . '_table', $objects, $table_attributes );
  }

  public function fid( $id = 0 ) {
    return $this->fields[$id]->id();
  }

  public function value( $id = 0, $i = 0 ) {
    if( isset( $this->fields[$i] ) && method_exists( $this->fields[$id], "get" ) )
      return $this->fields[$id]->get( $i );
      
    return $this->get( $i );
  }

  public function html() {
    if( ! empty( $this->__table ) ) $this->__table->html();
  }

}

?>
