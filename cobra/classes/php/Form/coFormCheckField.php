<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormCheckField extends coFormObject {
  public function __construct( $id = __CLASS__, $label = 'Input', $value = '', $field_attributes = array() ) {
  
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__, 
	                                  'validable' => true, 'updateable' => true ), $field_attributes );
	
	$field_attributes = array_merge( array( 'class' => __CLASS__, 'type' => 'checkbox' ), $field_attributes );

	parent::__construct( $attributes );
	$this->label = new coFormLabel( $id . '_label', $label, array( 'required' => $this['required'] ) );
	$this->fields = array( new coFormCheck( $id . '_check', $value, $field_attributes ) );
	$this->_pack();
  }

}

?>
