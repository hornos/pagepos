<?php

if( ! defined( 'COBRA' ) ) die( 'No direct access' );

cobra_define( 'PAGEPOS_LATEST_LIMIT', 10 );
cobra_define( 'PAGEPOS_POPULAR_LIMIT', 10 );
cobra_define( 'PAGEPOS_SEARCH_LIMIT', 100 );
cobra_define( 'PAGEPOS_TEST_MODE', false );
cobra_define( 'PAGEPOS_STRING_SIZE', 128 );
cobra_define( 'PAGEPOS_LOCK_TIMEOUT', 3600 );

class ppLocation extends coRPC {

  public function __construct() { }

  private function __dbquery( $method = NULL, $argv = NULL, $fldv = NULL, $select = true ) {
    $cobra   = cobra_cache_fetch( 'cobra' );
    $apps    = $cobra['sys.apps'];
    $pagepos = $apps['pagepos'];

    $db = new coDB( $pagepos['sys.config'] );
    $db->Connect();
    if( $select ) {
      $result = $db->CallProcedureSelect( $method, $argv, $fldv, 0, 0 );
    }
    else {
      $result = $db->CallProcedure( $method, $argv );
    }
    $db->Disconnect();
    return $result;
  }


  protected function _rpc_get_geonames() {
    $zoomlevel = coString::int( coRequest::request( 'zoomlevel', 10 ), 5, 10 );
    $nelat = coString::float( coRequest::unsafe_request( 'nelat', 0.000 ) );
    $nelng = coString::float( coRequest::unsafe_request( 'nelng', 0.000 ) );
    $swlat = coString::float( coRequest::unsafe_request( 'swlat', 0.000 ) );
    $swlng = coString::float( coRequest::unsafe_request( 'swlng', 0.000 ) );    
    // $argv = array( max( $nelat, $swlat), min( $nelat, $swlat), max( $nelng, $swlng ), min( $nelng, $swlng ), $zoomlevel );
    $argv = array( $nelat, $swlat, $nelng, $swlng, $zoomlevel );
    $fldv = array( 'geonameid', 'name', 'ascii_name', 'latitude', 'longitude', 'population', 'country_name', 'admin1_name', 'link', 'status' );
    return coRequest::jresponse( 'result', $this->__dbquery( (PAGEPOS_TEST_MODE ? 'get_geonames' : 'get_geonames_users') , $argv, $fldv ) );
  }


  protected function _rpc_search_geoname() {
    $data   = coRequest::request( 'data', '^budapest' );
    $offset = coString::int( coRequest::request( 'offset', 0 ), 0, 100000 );
    $limit  = coString::int( coRequest::request( 'limit', PAGEPOS_SEARCH_LIMIT ), 0, 100000 );
    $argv = array( '^' . $data, $limit, $offset );
    $fldv = array( 'geonameid', 'name', 'ascii_name', 'latitude', 'longitude', 'population', 'country_name', 'admin1_name', 'link', 'status' );
    return coRequest::jresponse( 'result', $this->__dbquery( 'search_geoname', $argv, $fldv ) );
  }


  protected function _rpc_get_latest() {
    $limit = PAGEPOS_LATEST_LIMIT;
    $argv = array( $limit );
    $fldv = array( 'geonameid', 'name', 'latitude', 'longitude', 'population', 'country_name', 'country_code', 'admin1_name', 'link', 'status' );
    return coRequest::jresponse( 'result', $this->__dbquery( 'get_geonames_latest', $argv, $fldv ) );
  }


  protected function _rpc_get_popular() {
    $limit = PAGEPOS_POPULAR_LIMIT;
    $argv = array( $limit );
    $fldv = array( 'geonameid', 'name', 'latitude', 'longitude', 'population', 'country_name', 'country_code', 'admin1_name', 'link', 'status' );
    return coRequest::jresponse( 'result', $this->__dbquery( 'get_geonames_popular', $argv, $fldv ) );
  }


  protected function _rpc_get_geoinfo() {
    $geonameid = coString::int( coRequest::request( 'geonameid', 0 ), 0, 100000000 );
    $argv = array( $geonameid );
    $fldv = array( 'geonameid', 'name', 'ascii_name', 'population', 'longitude', 'latitude', 'country_name', 'admin1_name', 'link', 'status', 'price' );
    $data = $this->__dbquery( 'get_geoinfo', $argv, $fldv );
    $result = $data[0];
    // $_SESSION['Payment_Amount'] = serialize( $result );
    // $_SESSION['Payment_Amount'] = isset( $result['price'] ) ? $result['price'] : 0;
    $_SESSION['name']         = isset( $result['name'] ) ? $result['name'] : false;
    $_SESSION['ascii_name']   = isset( $result['ascii_name'] ) ? $result['ascii_name'] : false;
    $_SESSION['country_name'] = isset( $result['country_name'] ) ? $result['country_name'] : false;
    $_SESSION['status']       = isset( $result['status'] ) ? $result['status'] : false;
    $_SESSION['geonameid']    = isset( $result['geonameid'] ) ? $result['geonameid'] : false;
    $_SESSION['price']        = isset( $result['price'] ) ? $result['price'] : false;
    $_SESSION['latitude']     = isset( $result['latitude'] ) ? $result['latitude'] : false;
    $_SESSION['longitude']    = isset( $result['longitude'] ) ? $result['longitude'] : false;
    return coRequest::jresponse( 'result', $data );
  }


  protected function _rpc_increment_counts() {
    $geonameid = coString::int( coRequest::request( 'geonameid', 0 ), 0, 100000000 );
    $argv = array( $geonameid );
    return coRequest::jresponse( 'result', $this->__dbquery( 'increment_counts', $argv, NULL, false ) );
  }


  protected function _rpc_status_lock() {
    $geonameid = coString::int( coRequest::request( 'geonameid', 0 ), 0, 100000000 );
    $email     = coString::trunc( coString::email( coRequest::unsafe_request( 'email', '' ) ), 64 );
    $url       = coString::trunc( coString::url( coRequest::unsafe_request( 'url', '' ) ), 128 );
    // $price     = coString::int( coRequest::request( 'price', 0 ), 5, 100 );
    $argv = array( $geonameid, $email, $url );
    return coRequest::jresponse( 'result', $this->__dbquery( 'status_lock', $argv, NULL, false ) );
  }


  protected function _rpc_verify_lock( $post = array() ) {
    $argv = array( $post['geonameid'], $post['price'] );
    return $this->__dbquery( 'verify_lock', $argv, NULL, false );
  }


  // TODO: arg check clean
  protected function _rpc_sell_city( $post = array() ) {
    $argv = array( $post['verify_sign'], $post['txn_id'], $post['txn_type'],
                   $post['payment_date'], $post['item_number'], $post['payment_gross'],
                   $post['first_name'], $post['last_name'], $post['payer_email'] );
    return $this->__dbquery( 'sell_city', $argv, NULL, false );
  }


  protected function _rpc_gc_city( $time = 30 ) {
    $cobra   = cobra_cache_fetch( 'cobra' );
    $apps    = $cobra['sys.apps'];
    $pagepos = $apps['pagepos'];
    $db = new coDB( $pagepos['sys.config'] );
    $db->Connect();

    try {
      $result = $db->CallProcedure( 'gc_city', array( $time ) );
    } catch( Exception $e ) {
      throw new coException( __METHOD__ . $e->getMessage());
    }
    $ts = $db->timestamp();
    cobra_print( $ts . ' Pagepos: '.$result.' cities was delocked' );
    $db->Disconnect();
  } 
  
}


?>
