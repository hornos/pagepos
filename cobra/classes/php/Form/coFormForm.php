<?php

class coFormForm extends coFormObject {
  private $__table;
  private $__form_id;
  private $__module_id;
  private $__method_id;
  private $__method_argv;
  private $__submitter;
  private $__states;
  
  public function __construct( $id = __CLASS__, $module_id = __CLASS__, $attributes = array(), 
                               $objects = array(), $table_attributes = array(), $submitter = array() ) {
	
	$attributes = array_merge( array( 'id' => $id, 'name' => $id, 'class' => __CLASS__, 
	                                  'method' => 'POST', 'fieldset' => 'Form',
	                                  'dispatcher' => 'dispatcher.php' ), $attributes );
    parent::__construct( $attributes );
    
    // states
    $this->__states = array( 'init', 'check', 'work', 'finish' );
	
	$id = $this->id();
    $table_attributes = array_merge( array(), $table_attributes );
	$this->__table = new coFormTable( $id . '_table', $objects, $table_attributes );
	$this->__form_id     = new coFormInput( '__form_id', $id, array( 'type' => 'hidden', 'required' => true ) );
	$this->__module_id   = new coFormInput( '__module_id', $module_id, array( 'type' => 'hidden', 'required' => true ) );
	$this->__method_id   = new coFormInput( '__method_id', 'validate', array( 'type' => 'hidden', 'required' => true ) );
	$this->__method_argv = new coFormInput( '__method_argv', '', array( 'type' => 'hidden', 'required' => true ) );
	$this->__dispatcher  = new coFormInput( '__dispatcher', $this['dispatcher'], array( 'type' => 'hidden', 'required' => true ) );
	$this->__state       = new coFormInput( '__state', 0, array( 'type' => 'hidden', 'required' => true ) );
    $this->__state->size( 32, 32 );
    $this->__state->filters( array( 'coString:alnum' ) );
  }

  public function state_init() {
    return true;
  }

  protected function _build( $options = array() ) {
    return true;
  }

  protected function _process( $options = array() ) {
    $state = $this->__state->value();
    return true;
  }


  public function set( $objects ) {
    $this->__table->set( $objects );
  }
  
  
  public function &get( $i = 0 ) {
    return $this->__table->get( $i );
  }
  
  
  public function table_attributes( $attributes ) {
    $this->__table->attributes( $attributes );
  }
  
  
  public function id( $id = NULL ) {
    if( is_string( $id ) ) $this->__table->id( $id.'_table' );

	return parent::id( $id );
  }


  public function module_id( $id = NULL ) {
    return $this->__module_id->set( $id );
  }

  
  public function action( $action = NULL ) {
    if( is_string( $action ) ) {
	  $this->attributes( array( 'action' => $action ) );
	}
  }


  public function submitter( $submitter = array() ) {
    $this->__submitter = $submitter;
  }


  public function html() {
    $form_id  = $this->id();
	$idprefix = $this->__table->idprefix();
    
    // 
    
    // write out HTML
    coHTML::out( '<!-- BEGIN FORM ('.$form_id.') -->');
    $this->_pre_html();
    coHTML::out( '<form '.coHTML::a2s( $this->attributes() ).'>' );
    $this->__form_id->html();
	$this->__module_id->html();
	$this->__method_id->html();
	$this->__method_argv->html();
	$this->__dispatcher->html();
    $this->__state->html();

    $is_submitter = false;
    if( isset( $this->__submitter['submitter_id'] ) ) {
	  $submitter_id = $idprefix.$this->__submitter['submitter_id'];
	  $is_submitter = true;
    }
    
    if( $is_submitter ) {
      $submitter = new coFormInput( '__submitter_id', $submitter_id, array( 'type' => 'hidden', 'required' => true ) );
      $submitter->html();
    }
	$this->__table->html();
    coHTML::out( '</form>' );    
    $this->_post_html();

    if( $is_submitter ) {
      $event   = isset( $this->__submitter['event'] ) ? $this->__submitter['event'] : 'click';
      $request = 'global_request_' . $form_id;

	  coHTML::out( '<!-- BEGIN FORM SUBMIITER -->' );
	  coHTML::out( '<script type="text/javascript">' );
      coHTML::out( $request . ' = new coRequest();' );
      coHTML::out( $request . '.init( "'.$form_id.'", "'.$submitter_id.'", "'.$event.'" );' );
	  
	  if( isset( $this->__submitter['output_id'] ) ) {
	    coHTML::out( $request . '.output("'.$this->__submitter['output_id'].'");' );
	  }
	  // TODO: image
	  if( isset( $this->__submitter['image_id'] ) ) {
	    coHTML::out( $request . '.image("'.$this->__submitter['image_id'].'");' );
	  }
	  coHTML::out( '</script>' );
	  coHTML::out( '<!-- END FORM SUBMIITER -->' );	
    }

    coHTML::out( '<!-- END FORM ('.$form_id.') -->');  
  }

  public function validate() {
    $validation = array();
    $valid = true;

    // get form state
    $this->__state->validate();
    foreach( $this->__table as $key => $obj ) {
      if( ! $obj['validable'] ) {
        continue;
      }
      $obj_valid = $obj->validate();
      $obj_validation = array( 'valid' => $obj_valid, 'fid' => $obj->fid() );

      if( $obj['updateable'] ) {
          $obj_value = $obj->value();
          $obj_validation = array_merge( $obj_validation, array( 'value' => $obj_value ) );
      }
      $validation = array_merge( $validation, array( $key => $obj_validation ) );
      $valid = $valid && $obj_valid;
    }

    // make return array
    $return  = array( '__form_id' => $this->id(), '__method_id' => 'validation', 
                      '__submitter_id' => $this->__submitter['submitter_id'],
                      '__valid' => $valid );

    // determine state
    $state = (int)$this->__state->value();
    $state = 0;
    if( $valid ) {
      // $state += 1;
      $this->__state->value( $state );
    }
    $return = array_merge( $return, array( '__state' => $state ) );

    if( isset( $this->__states[$state] ) ) {
      $state_method = 'state_' . $this->__states[$state];
      if( method_exists( $this, $state_method ) ) {
        $return = array_merge( $return, array( 'state' => $this->$state_method() ) );
      }
    }

    // return
    return array_merge( $return, array( 'validation' => $validation ) );
  }
  
}

?>
