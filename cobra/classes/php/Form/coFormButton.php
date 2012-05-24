<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormButton extends coFormObject {

  public function __construct( $id = __CLASS__, $value = '', $attributes = array() ) {
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__,
	                                  'type' => 'button' ), $attributes );
	parent::__construct( $attributes );
	$this->set( $value );
  }

  public function html() {
    coHTML::button( $this->get(), $this->attributes() );
  }

}

?>
