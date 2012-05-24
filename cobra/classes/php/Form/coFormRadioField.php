<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormRadioField extends coFormObject {
  public function __construct( $id = __CLASS__, $label = 'Input', $checked = NULL, $values = array(), $field_attributes = array() ) {
  
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__,
	                                  'validable' => true, 'updateable' => true ), $field_attributes );
	
	$field_attributes = array_merge( array( 'class' => __CLASS__ ), $field_attributes );

	parent::__construct( $attributes );
	$this->label = new coFormLabel( $id . '_label', $label, array( 'required' => $this['required'] ) );
	$this->fields = array( new coFormRadio( $id . '_radio', $checked, $values, $field_attributes ) );
    $this->_pack();
  }

}

?>
