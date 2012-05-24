<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


// TODO: TableObject
class coFormRadio extends coFormObject {
  private $__radio;
  
  public function __construct( $id = __CLASS__, $checked = NULL, $values = array(), $attributes = array() ) {
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__,
                                      'validable' => true, 'updateable' => false, 'unique' => true,
                                      'type' => 'radio', 'cols' => 1, 'rows' => 1,
                                      'cellpadding' => '5', 'cellspacing' => '0' ), $attributes );
	parent::__construct( $attributes );

	if( ! empty( $checked ) ) $this->set( $checked );

	$id = $this->id();

    // create radio table
    $radio = array();
    foreach( $values as $key => $val ) {
      $input_attributes = array( 'type' => $this['type'], 'label' => $val );
      if( $key == $checked ) {
        $input_attributes = array_merge( array( 'checked' => 'checked' ), $input_attributes );
      }
      $input = new coFormInput( $id, $key, $input_attributes , 0 );
      array_push( $radio, $input );
    }
    $this->__radio = new coFormTable( $id, $radio, $attributes ); // attributes
  }


  public function html() {
    $this->_pre_html();
    $this->__radio->html();
    $this->_post_html();
  }


  public function validate() {
    $value = coRequest::unsafe_request( $this->id() );
    $this->set( $value );
    return true;
  }

}
?>
