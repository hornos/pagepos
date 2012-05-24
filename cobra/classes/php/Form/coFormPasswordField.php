<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormPasswordField extends coFormObject {
  public function __construct( $id = __CLASS__, $label = 'Input', $value = '', $field_attributes = array() ) {
  
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__, 
	                                  'validable' => true, 'updateable' => false ), $field_attributes );
	
	$input_attributes = array_merge( array( 'class' => __CLASS__, 'type' => 'text',
	                                        'size' => 16 ), $field_attributes );

	parent::__construct( $attributes );
	$this->label = new coFormLabel( $id . '_label', $label, array( 'required' => $this['required'] ) );
	
	$input1 = new coFormInput( $id . '_input1', $value, $field_attributes );
	$input2 = new coFormInput( $id . '_input2', $value, $field_attributes );
	$this->fields = array( $input1, $input2 );
	$this->_pack( array( 'cols' => 3, 'rows' => 1 ) );
  }

  public function size( $min = 16, $max = 64 ) {
    $this->fields[0]->size( $min, $max );
    $this->fields[1]->size( $min, $max );
  }

  public function validate() {
    return parent::validate() && ( $this->value( 0 ) == $this->value( 1 ) );
  }
   
}

?>
