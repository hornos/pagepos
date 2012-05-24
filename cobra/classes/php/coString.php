<?php

if( ! defined( 'COBRA' ) ) die();

cobra_define( 'COBRA_STRING_SIZE', 32 );
cobra_define( 'COBRA_STRING_REGEX_ALPHA', '[^[:alpha:]_:./ -]' );
cobra_define( 'COBRA_STRING_REGEX_NAME', '[^[:alpha:] -]' );
cobra_define( 'COBRA_STRING_REGEX_ALNUM', '[^[:alnum:]_:./ -]' );
cobra_define( 'COBRA_STRING_REGEX_EMAIL', '[^[:alnum:]@_./ -]' );
cobra_define( 'COBRA_STRING_REGEX_PREURL', '^.*:\/\/' );
cobra_define( 'COBRA_STRING_REGEX_NUM', '[^[:digit:].-]' );
cobra_define( 'COBRA_STRING_REGEX_TEL', '[^[:digit:].+-]' );


class coString {
  public function __construct() { }

  public static function lowercase( $str ) {
    return mb_strtolower( $str );
  }

  public static function alpha( $str ) {
    return mb_ereg_replace( COBRA_STRING_REGEX_ALPHA, '', $str );
  }

  public static function name( $str ) {
    return mb_ereg_replace( COBRA_STRING_REGEX_NAME, '', $str );
  }

  public static function email( $str ) {
    return mb_ereg_replace( COBRA_STRING_REGEX_EMAIL, '', $str );
  }

  public static function url( $str, $clean = true ) {
    $rstr = $clean ? mb_ereg_replace( COBRA_STRING_REGEX_PREURL, '', $str ) : $str;
    return mb_ereg_replace( COBRA_STRING_REGEX_ALNUM, '', $rstr );
  }

  public static function num( $str ) {
    return mb_ereg_replace( COBRA_STRING_REGEX_NUM, '', $str );
  }

  public static function tel( $str ) {
    return mb_ereg_replace( COBRA_STRING_REGEX_TEL, '', $str );
  }

  public static function int( $str, $ll = 0, $ul = 100 ) {
    $str = self::num( $str );
    $v = intval( $str );
    if( $v < $ll ) return $ll;
    
    if( $v > $ul ) return $ul;
    
    return $v;
  }

  public static function float( $str ) {
    $str = self::num( $str );
    return floatval( $str );
  }
  
  public static function alnum( $str ) {
    return mb_ereg_replace( COBRA_STRING_REGEX_ALNUM, '', $str );
  }

 
  public static function trunc( $str, $size = COBRA_STRING_SIZE ) {
    if( $size == 0 )
      return $str;

    return mb_substr( trim( $str ), 0, $size );
  }


  public static function subalpha( $str, $size = COBRA_STRING_SIZE ) {
    return self::alpha( self::trunc( $str, $size ) );
  }


  public static function subalnum( $str, $size = COBRA_STRING_SIZE ) {
    return self::alnum( self::trunc( $str, $size )  );
  }


  public static function subnum( $str, $size = COBRA_STRING_SIZE ) {
    return self::num( self::trunc( $str, $size ) );
  }  
  
  
  public static function sqlf( $str ) {
    return '\''.addslashes( $str ).'\'';
  }

  public static function a2f( $arr = NULL, $default = '*', $sqlf = false ) {
    $str = '';
    
    if( ! is_array( $arr ) ) {
      return $default;
    }
    
	$size = count( $arr );
	if( $size < 1 ) {
      return $default;
    }
    
	$j = 1;  
	foreach( $arr as $a ) {
	  $str .= ( $sqlf ? self::sqlf( $a ) : $a );
	  if( $j < $size ) $str .= ',';
	  
      ++$j;
	} // end foreach
  
    return $str;
  }


  public static function tof( $str ) {
    if( empty( $str ) ) return false;

    $str = self::subalnum( $str, 4 );
    if( $str == 't' || $str == 'true' ) return true;
    
    return false;
  }


  public static function is_true( $val ) {
    if( empty( $val ) ) return false;
	
	if( is_numeric( $val ) ) return ( ( $val > 0 ) ? true : false );
	
	if( is_string( $val ) ) {
      $val = self::subalnum( $val, 4 );
      if( $val == 't' || $val == 'true' || $val == '1' ) {
	    return true;
	  }
	  return false;
	}
	return $val;
  }

}

?>
