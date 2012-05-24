<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormTextareaField extends coFormObject {  
  public function __construct( $id = __CLASS__, $label = 'Input', $value = '', $field_attributes = array() ) {
  
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__, 
	                                  'validable' => true, 'updateable' => true ), $field_attributes );
	
	$field_attributes = array_merge( array( 'class' => __CLASS__, 'type' => 'text', 
	                                        'size' => 16 ), $field_attributes );

	parent::__construct( $attributes );
	$this->label = new coFormLabel( $id . '_label', $label, array( 'required' => $this['required'] ) );
	array_push( $this->fields, new coFormTextarea( $id . '_textarea', $value, $field_attributes ) );
	$this->_pack();
  }

  public function size( $max = 256 ) {
    $this->fields[0]->size( $max, $max );
  }

  public function filters( $filter = array() ) {
    $this->fields[0]->filters( $filter );
  }
  
  public function match( $match = NULL ) {
    $this->fields[0]->match( $match );
  }

}

?>
