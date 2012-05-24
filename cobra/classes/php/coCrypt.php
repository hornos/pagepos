<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

//
// TODO: better
//
class coCrypt {
  public function __construct() { }

  public static function encrypt( $str, $key ) {
	$iv = mcrypt_create_iv( mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC ), MCRYPT_RAND );
	return bin2hex( mcrypt_encrypt( MCRYPT_RIJNDAEL_128, trim( $key ), $str, MCRYPT_MODE_ECB, $iv ) );
  }


  public static function decrypt( $estr, $key ) {
	$iv = mcrypt_create_iv( mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC ), MCRYPT_RAND );
	return trim( mcrypt_decrypt( MCRYPT_RIJNDAEL_128, trim( $key ), pack( "H*", $estr ), MCRYPT_MODE_ECB, $iv ) );
  }
  
  
  public static function encrypt_passcode( $str, $method = "SHA1" ) {
    if( $method == "SHA1" ) {
      return sha1( $str );
	}
	return md5( $str );
  }


  public static function desalt( $str, $salt_vector = array( 0 ) , $salt_size = 0 ) {
    if( ! is_array( $salt_vector ) || $salt_size < 1 ) return $str;

    $str_size = strlen( $str );
    sort( $salt_vector );

    $psv = 0;
    $csv = $psv;
    $str_desalted = '';
    $str_end = 0;

    $i = 0;
    foreach( $salt_vector as $sv ) {
      $csv = $sv + $salt_size * $i;      
      
      if( $csv > $str_size ) {
        $str_end += $salt_size;
        continue;
      }
      
      $str_sub = substr( $str, $psv, $csv - $psv );
      // coHTML::out( $sv . ' ' . $psv . ' ' . $csv . ' |' . $str_sub . '|' , true );
      $str_desalted .= $str_sub;
      $psv = $csv + $salt_size;
      ++$i;
    }
    
    $str_desalted .= substr( $str, $psv, -$str_end );
    return $str_desalted;
  }  


  public static function ensalt( $str, $salt_vector = array( 0 ) , $salt_size = 0 ) {
    if( ! is_array( $salt_vector ) || $salt_size < 1 ) return $str;

    $salt_str = sha1( uniqid( microtime() ) );
    $salt_str_size = strlen( $salt_str );
    $str_size = strlen( $str );

    sort( $salt_vector );

    $psv = 0;
    $csv = $psv;
    
    $str_salted = '';
    foreach( $salt_vector as $sv ) {
      $csv = $sv;
      $str_sub      = substr( $str, $psv, $csv - $psv );
      $salt_str_sub = substr( $salt_str, ( $csv > $salt_size ? $csv - $salt_size : $csv ) % $salt_str_size , $salt_size );
      $str_salted  .= $str_sub . $salt_str_sub;
      // $str_salted  .= '|' . $str_sub . '|' . $salt_str_sub;
      $psv = $csv;
    }
    $str_salted .= substr( $str, $csv, $str_size );
    return $str_salted;
  }  

}

?>
