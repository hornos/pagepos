<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormInput extends coFormObject {
  private $__filters;
  private $__match;
  
  public function __construct( $id = __CLASS__, $value = '', $attributes = array() ) {
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'sqlid' => $id, 'class' => __CLASS__,
	                                  'type' => 'text', 'validable' => true, 'updateable' => true ), $attributes );
	parent::__construct( $attributes );
	$this->set( $value );
	$this->__filters = array();
	$this->__match = NULL;
	$this->size();
  }

  public function html() {
    try {
      $label = $this['label'];
    } catch( Exception $e ) {
      $label = '';
    }
    coHTML::input( $this->get(), $this->attributes(), $label );
  }
  
  public function size( $size = 16, $maxlength = 256 ) {
    if( $size > 0 ) $this->attributes( array( 'maxlength' => $maxlength, 'size' => $size + 2 ) );
  }

  public function filters( $filters = NULL ) {
    $this->__filters =  $filters;
  }
  
  public function match( $match = NULL ) {
    $this->__match = $match;
  }

  public function validate() {
    $id = $this->id();
    $log_str = $id;

    // truncate
    $value = coRequest::unsafe_request( $id );
    $log_str .= ' >' . $value . '< ';

    $value = coString::trunc( $value, $this['maxlength'] );
    $log_str .= ' >' . $value . '< ';

    // run filters
    foreach( $this->__filters as $filter ) {
      // $log_str .=  ' ' .$filter;
      if( is_callable( $filter ) ) {
        // $log_str .=  ' ok ';
        try {
          $value = call_user_func( $filter, $value );
          // $log_str .= ' ok ';
        } catch( Exception $e ) { }
      }
    }

    // reset

    $this->set( $value );
    $log_str .= ' >' . $value . '< ';
    $log = new coLog( __FILE__ );
    $log->record( $log_str . "\n" );

    if( $this['required'] && empty( $value ) )
      return false;

    // check match
    if( ! empty( $value ) && ! empty( $this->__match ) ) {
      try {
        return mb_ereg_match( $this->__match, $value );
      } catch( Exception $e ) {
        return true;
      }
    }
    return true;
  }

}

?>
