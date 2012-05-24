<?php

class coFormTable extends coFormObject {
  public function __construct( $id = __CLASS__, $objects = array(), $attributes = array() ) { 
    $attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__,
	                                  'rows' => 1, 'cols' => 1,
									  'border' => 0, 'cellpadding' => 0, 'unique' => false,
									  'cellspacing' => 0 ) , $attributes );
	parent::__construct( $attributes );
	$this->set( $objects );
  }


  public function idprefix() {
    try {
	  $idprefix = $this['idprefix'].'_';
	} catch( Exception $e ) {
      $idprefix = '';
	}
    return $idprefix;  
  }


  public function set( $objects ) {
    $idprefix = $this->idprefix();
    $c = 0;
    if( is_array( $objects ) ) {
      foreach( $objects as $obj ) {
        $newid = $idprefix . $obj->id();
	    $obj->id( $newid );
        parent::set( $obj, $this['unique'] ? $newid . '_' . $c : $newid );
        ++$c;
      }
	}
	else {
	  $objects->id( $idprefix.$objects->id() );	
      parent::set( $objects, $objects->id() );
	}
  }
  
  
  public function &get( $i = 0 ) {
    if( is_numeric( $i ) ) {
	  return parent::get( $i );
	}
    $idprefix = $this->idprefix();
    return parent::get( $idprefix.$i );
  }
  
  
  public function html() {
    $rows = $this['rows'];
    $cols = $this['cols'];
    $dimension = $rows * $cols;
	
	coHTML::out( '<!-- BEGIN TABLE ('.$this->id().') -->');
    coHTML::out( '<table '.coHTML::a2s( $this->attributes() ).'>' );
	for( $i = 0; $i < $rows; ++$i ) {
	  coHTML::out( '<tr>' );
	  for( $j = 0; $j < $cols; ++$j ) {
	    coHTML::out( '<td>' );
		try {
	      $obj = $this->get( $i * $cols + $j );
	      coHTML::out( '<!-- BEGIN OBJECT ('.$obj->id().') -->');
	      $obj->html();
	      coHTML::out( '<!-- END OBJECT ('.$obj->id().') -->');		  
		} catch( Exception $e ) {
		  coHTML::ypad( $i.' '.$j );
		}
	    coHTML::out( '</td>' );
	  }
	  coHTML::out( '</tr>' );	
	}
	coHTML::out( '</table>' );
	coHTML::out( '<!-- END TABLE ('.$this->id().') -->');
  }

/*
  public function validate() {
    $validation = array();
    foreach( $this as $key => $value ) {
      echo '<br><br>Object: '.$key.'<br>'.$value;
      if( is_object( $value ) && method_exists( $value, 'validate' ) ) {
        $valid = $value->validate() ? true : false;
      }
      
      $validation = array_merge( $validation, array( $key => array( 'value' => , 'valid' => $valid ) ) );
    }
    return $validation;
  }
*/
}
?>
