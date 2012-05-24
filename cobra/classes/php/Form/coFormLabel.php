<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormLabel extends coFormObject {
  public function __construct( $id = __CLASS__, $value = 'label', $attributes = array() ) {
    $attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__ ), $attributes );
    parent::__construct( $attributes );
    $this->set( $value );
  }

  public function html() {
    $label = $this->get() . ( $this['required'] ? '<span class="required">*</span>' : '' );
    coHTML::div( $label, $this->attributes() );
  }
}

?>
