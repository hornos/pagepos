<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

class coFormThrobButton extends coFormButton {
  private $__table;
  //private $__label;
  //private $__image;

  public function __construct( $value = '', $attributes = array(), $images = array() ) {
	$attributes = array_merge( array( 'id' => __CLASS__, 'name' => __CLASS__, 'class' => __CLASS__,
	                                  'type' => 'button' ), $attributes );
	parent::__construct( $value, $attributes );

	$id = $this->id();
	
	$table_attributes = array( 'id' => $id.'_table',
	                           'name' => $id.'_table', 
	                           'cols' => 2, 'rows' => 1,
							   'idprefix' => $id );
	
	
	$label = new coFormLabel( $value, array( 'id' => 'label', 'name' => 'label' ) );
	$image = new coFormImage( $images, array( 'id' => 'image', 'name' => 'image' ) );
	
	$this->__table = new coFormTable( array( &$label, &$image ) , $table_attributes );
  }


  public function id( $id = NULL ) {
    if( is_string( $id ) ) {
	  $this->__table->id( $id.'_table' );
	  $this->table_attributes( array( 'idprefix' => $id ) );
	  // echo $id;
	}
	return parent::id( $id );
  }


  public function table_attributes( $attributes ) {
    $this->__table->attributes( $attributes );
  }


  public function html() {
    coHTML::out( '<button '.coHTML::a2s( $this->attributes() ).'>' );
    $this->__table->html();	
	coHTML::out( '</button>' );
  }

}
?>
