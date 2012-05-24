<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormCheck extends coFormObject {
  
  public function __construct( $id = __CLASS__, $value = NULL, $attributes = array() ) {
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__,
	                                  'type' => 'checkbox', 
	                                  'validable' => true, 'updateable' => true ), $attributes );
	parent::__construct( $attributes );
	$this->set( $value );
  }

  public function html() {
    coHTML::input( $this->get(), $this->attributes() );
  }
  

  public function validate() {
    if( ! coRequest::unsafe_request( $this->id() ) ) {
      $this->set( false );
    }
    else {
      $this->set( true );
    }
    return true;
  }

}

?>
