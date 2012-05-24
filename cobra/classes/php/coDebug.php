<?php
// TODO: rewrite

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

// inline css always works
class coDebug {
  public function __construct() { }

  private static function __http_sql_line( $str ) {
    return 'SQL Query<div style="color: #555555; font-size: 12px; font-weight: normal; padding: 5px; padding-bottom: 10px;">'.$str.'</div>';
  }


  private static function __http_debug_line( $str ) {
    return '<div style="font-size: 12px; font-weight: bold; padding-bottom: 5px;">'.$str.'</div>';
  }


  protected static function _message( $message, $level, $type ) {
    if( $level > COBRA_DEBUG )
      return false;
      
    if( COBRA_CLI )
      return cobra_print( 'Debug('.$level.'): '.$message );
    
	if( $type == 'SQL' )
      $message = self::__http_sql_line( $message );

    return cobra_print( self::__http_debug_line( 'Debug('.$level.'): '.$message ) );
  }
  
    
  public static function message( $object, $level = 9, $type = 'PLAIN' ) {
    if( is_numeric( $object ) ) {
      $message = strval( $object );
      return self::_message( $message, $level, $type );
    }
  
    if( is_string( $object ) ) {
      $message = $object;
      return self::_message( $message, $level, $type );
    }
  
    if( is_a( $object, 'Exception' ) || is_subclass_of( $object, 'Exception' ) ) {
	 return self::_message( $object->getMessage(), $level, $type );
	}
  }  
      
}

?>
