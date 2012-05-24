<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormTextarea extends coFormInput {  
  public function __construct( $id = __CLASS__, $value = '', $attributes = array() ) {
	$attributes = array_merge( array( 'class' => __CLASS__, 'cols' => 48, 'rows' => 5 ), $attributes );
	parent::__construct( $id, $value, $attributes );
	// $this->set( $value );
  }

  public function html() {
    try {
      $label = $this['label'];
    } catch( Exception $e ) {
      $label = '';
    }
    coHTML::textarea( $this->get(), $this->attributes(), $label );
  }

}

?>
