<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormCombo extends coFormObject {
  protected $_values = array();
  
  public function __construct( $id = __CLASS__, $checked = NULL, $values = array(), $attributes = array() ) {
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__,
	                                'updateable' => true ), $attributes );
	parent::__construct( $attributes );
    $this->_values = $values;

	if( ! empty( $checked ) ) $this->set( $checked );

	$id = $this->id();

  }

  public function html() {
    $checked = $this->get();
    $this->_pre_html();
    coHTML::out( '<select '.coHTML::a2s( $this->attributes() ).'>' );
    foreach( $this->_values as $key => $val ) {
      $selected = $key == $checked ? ' selected="yes" ' : '';
      coHTML::out( '<option ' . $selected . 'value="' . $key . '">' . $val . '</option>' );
    }
    coHTML::out( '</select>' );
    $this->_post_html();
  }


/*
  public function validate() {
    $value = coRequest::request( $this->id() );

    $this->set( $value );

    return true;
  }
*/
}

?>
