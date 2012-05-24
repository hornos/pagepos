<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormComboField extends coFormObject {
  public function __construct( $id = __CLASS__, $label = 'Input', $checked = NULL, $values = array(), $field_attributes = array() ) {
  
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__,
	                                  'updateable' => true ), $field_attributes );
	
	$field_attributes = array_merge( array( 'class' => __CLASS__ ), $field_attributes );

	parent::__construct( $attributes );
	$this->label = new coFormLabel( $id . '_label', $label, array( 'required' => $this['required'] ) );
	array_push( $this->fields, new coFormCombo( $id . '_combo', $checked, $values, $field_attributes ) );
	$this->_pack();
  }
}

?>
