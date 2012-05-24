<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coFormImage extends coFormObject {
  public function __construct( $id = __CLASS__, $values = array(), $attributes = array() ) {
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__ ), $attributes );
    parent::__construct( $attributes );
	
	foreach( $values as $key => $value ) {
	  $this->set( $value, $key );
	}
  }


  public function html() {
    coHTML::img( $this->get( 'inactive' ), $this->attributes() );
	coHTML::out( '<script type="text/javascript">' );  
    coHTML::out( 'var '.$this->id().' = new coImage( "'.$this->id().'", { "active": "'.$this->get( 'active' ).'", "inactive" : "'.$this->get( 'inactive' ).'" } );' );
    coHTML::out( '</script>' );
  }

}

?>
