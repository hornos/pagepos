<?php
// TODO: rewrite

if( ! defined( 'COBRA' ) ) die( 'No direct access' );


class coPayPal {
  private $__logger;
  private $__verbose;
  private $__post;
  private $__url;
  private $__port;
  private $__timeout;
  private $__timestamp;
  
  public function __construct( $logfile = NULL, $verbose = false ) {
    $this->__verbose   = $verbose;
    $this->__logger    = empty( $logfile ) ? NULL : new coLog( $logfile );
    $this->__timestamp = date( "Y-m-d H:i:s" );
    // $this->_log( "START: ", true );
  }


  protected function _log( $str = "", $force = false ) {
    if( empty( $this->__logger ) )  return false;

    if( $this->__verbose || $force )
      return $this->__logger->record( "\n" . $str );
  }


  public function record( $str = "", $force = false ) {
    return $this->_log( $this->__timestamp . " " . $str, $force );
  }

  
  public function post() {
    return $this->__post;
  }

  public function set( $url = 'ssl://www.sandbox.paypal.com', $port = 443, $timeout = 30 ) {
    $this->__url  = $url;
    $this->__port = $port;
    $this->__timeout = $timeout;
    $this->record( $url . " " . $port . " " . $timeout );
  }


  private function __gen_request( $arr = array(), $prefix = 'cmd=_notify-validate' ) {
    $this->__post = array();
    $req = $prefix;
    foreach( $arr as $key => $value ) {
      $value = urlencode( stripslashes( $value ) );
      $req .= "&$key=$value";
      // save post values
      $this->__post[$key] = $value;
    }
    return $req;
  }


  private function __gen_header( $req ) {
    $header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . strlen( $req ) . "\r\n\r\n";
    return $header;
  }


  public function gen_request( $prefix = "" ) {
    return $this->__gen_request( $_POST, $prefix );
  }


  public function verify_error() {
    // $req = $this->__gen_request( array() );
    // post back to PayPal for validation
    $header = $this->__gen_header( "ERROR" );
    // init connection
    $fp = fsockopen( $this->__url, $this->__port, $errno, $errstr, $this->__timeout );
    if( ! $fp ) return false;

    // send to verify
    if( ! fputs( $fp, $header ) ) {
      fclose( $fp );
      return false;
    }

    // process reply
    while( ! feof( $fp ) ) {
      $res = fgets( $fp, 1024 );
      if( strcmp( $res, "INVALID" ) == 0 ) {
        fclose( $fp );
        return true;
      }
    }
    fclose( $fp );
    return false;
  }


  public function verify() {
    // read the post from PayPal system and add 'cmd'
    $req = $this->__gen_request( $_POST );
    // post back to PayPal for validation
    $header = $this->__gen_header( $req );
    // init connection
    $fp = fsockopen( $this->__url, $this->__port, $errno, $errstr, $this->__timeout );
    if( ! $fp ) {
      $this->record( "HTTP ERROR", true );
      return false;
    }

    $this->record( "HTTP OK" );
    // send to verify
    if( ! fputs( $fp, $header . $req ) ) {
      $this->record( "WRITE ERROR", true );
      fclose( $fp );
      return false;
    }

    // process reply
    while( ! feof( $fp ) ) {
      $res = fgets( $fp, 1024 );
      if( strcmp( $res, "VERIFIED" ) == 0 ) {
        $this->record( "VERIFIED", true );
        $this->record( "txn_id: " . $this->__post['txn_id'] );
        fclose( $fp );
        return true;
      }
      else if( strcmp( $res, "INVALID" ) == 0 ) {
        // log for manual investigation
        $this->record( "INVALID", true );
        fclose( $fp );
        return false;
      }
    }
    fclose( $fp );
    return false;
  }

} // end class

?>
