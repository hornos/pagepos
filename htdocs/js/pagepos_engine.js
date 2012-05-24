// Debug
var DEBUG = false;

// Globals varibles
var global_geoEngine      = new coGeoEngine();
var global_markerEngine   = new coMarkerEngine();
var global_positionEngine = new coPositionEngine()
var global_search_results = '';

var global_buy_email = "";
var global_buy_geonameid = 0;
var global_buy_url = "";
var global_buy_price = 5;
var global_buy_name = "";
var global_buy_country = "";
var global_link = 'undefined';
var global_semaphore_mapMoveend = false;
var global_semaphore_search = false;

var global_close_button   = new coImage();
global_close_button.init( '#page #header #close', { 'active' : './img/close.png', 'inactive' : './img/close_f.png' } );
var global_goback_button  = new coImage();
global_goback_button.init( '#page #header #goback', { 'active' : './img/goback.png', 'inactive' : './img/goback_f.png' } );
var global_paypal_button  = new coImage();
global_paypal_button.init( '#page #city_info #submit', { 'active' : './img/proceedtopaypal.png', 'inactive' : './img/proceedtopaypal_f.png' } );
var global_check_button  = new coImage();
global_check_button.init( '#page #city_info #check', { 'active' : './img/checkandbuy.png', 'inactive' : './img/checkandbuy_f.png' } );


var global_offset = 0;
var global_limit  = 20;
var global_search = '';

// Global Callbacks
function callback_check( data, textSatus ) {
  if( __check_exception( data ) ) return false;

  return true;
}


function callback_setMarkerInfo( data, textStatus ) {
  if( __check_exception( data ) ) return false;

  global_markerEngine.openInfoWindow( data['data'] );
  global_positionEngine.setCenter( global_geoEngine );
}


function callback_getMarkerInfo() {
  global_markerEngine.setMarker( this );
  var _payload = { 'method_id' : 'get_geoinfo', 'geonameid' : this.geonameid };
  global_geoEngine.php( _payload, callback_setMarkerInfo );
}


function callback_mapRefresh( data, textStatus ) {
  if( __check_exception( data ) ) {
    ui_hide_progress_throb();
    return false;
  }
  global_geoEngine.showZoomlevel( '#dashbar #content #info #zoomlevel' );
  global_geoEngine.addMarkers( data['data'] );
  ui_hide_progress_throb();
  global_semaphore_mapMoveend = false;
}


function callback_mapMoveend() {
  if( global_semaphore_mapMoveend )
    return false;

  global_semaphore_mapMoveend = true;
  ui_show_progress_throb();
  var _zoomlevel = global_geoEngine.getZoomlevel();
  var _bounds = global_geoEngine.getBounds();
  var _payload = { 'method_id' : 'get_geonames', 'zoomlevel' : _zoomlevel, 
                   'nelat' : _bounds[0], 'nelng' : _bounds[1], 
                   'swlat' : _bounds[2], 'swlng' : _bounds[3] };
  global_geoEngine.php( _payload, callback_mapRefresh );
}


function callback_mapShowPosition( latlng ) {
  global_positionEngine.update( latlng.lat(), latlng.lng() );
  $( '#dashbar #content #info #latitude' ).html( latlng.lat() );
  $( '#dashbar #content #info #longitude' ).html( latlng.lng() );
}


function callback_mapPanto( lat, lng, zl ) {
  gzl = global_geoEngine.getZoomlevel();
  zl = ( typeof(zl) == 'undefined' ? gzl : zl );
  global_positionEngine.mapPanto( global_geoEngine, lat, lng, zl );
}


function callback_buyCity( data, textSatus ) {
  if( __check_exception( data ) ) return false;

  var _data = data['data'];
  var _row = _data[0];
  var _name    = _row['name'];
  var _country = _row['country_name'];
  var _admin1  = __filter_admin1( _row['admin1_name'] );
  var _location = __filter_location( _country, _admin1 ); 
  var _geonameid = _row['geonameid'];
  var _link_link = _row['link'];
  var _price = _row['price'];

  ui_show_goback();
  
  if( ! _row['status']  ) {
    var _html = __html_check_form( _geonameid, _name, _country, _price );
  }
  else if( _row['status'] == 'lock' ) {
    var _html = __html_purchase_warning( _name );
  }
  else if( _row['status'] == 'demo' ) {
    var _html = __html_demo_warning( _name );
  }
  else {
    var _html = 'internal error';
  }
  ui_set_results( _html );
  ui_hide_throb();
/*
  $('#page #city_info #submit').mouseenter( function() {
    global_paypal_button.active();
  } );
  
  $('#page #city_info #submit').mouseleave( function() {
    global_paypal_button.inactive();
  } );
*/
  $('#page #city_info #check').mouseenter( function() {
    global_check_button.active();
  } );
  
  $('#page #city_info #check').mouseleave( function() {
    global_check_button.inactive();
  } );
}


function callback_getCity( geonameid ) {
  ui_hide_footer();
  ui_show_throb();
  var _payload = { 'method_id' : 'get_geoinfo', 'geonameid' : geonameid };
  global_geoEngine.php( _payload, callback_buyCity );
}


function callback_clickCity( geonameid ) {
  var _payload = { 'method_id' : 'increment_counts', 'geonameid' : geonameid };
  global_geoEngine.php( _payload, callback_check );
}


function callback_statusLock( data, textStatus ) {
  ui_hide_throb();
  if( __check_exception( data ) ) {
    ui_show_message_failed();
    return false;
  }
  var _html = __html_buy_form( global_buy_geonameid, global_buy_name, global_buy_country, global_buy_price );
  ui_show_close();
  ui_set_results( _html );
  ui_hide_throb();

  $('#page #city_info #submit').mouseenter( function() {
    global_paypal_button.active();
  } );
  
  $('#page #city_info #submit').mouseleave( function() {
    global_paypal_button.inactive();
  } );
}

function callback_searchShowResults( data, textSatus ) {
  ui_hide_throb();
  if( __check_exception( data ) ) {
    ui_show_message_again();
    return false;
  }
  // result
  var _max_hits = global_limit;
  var _html = '';
  var _data = data['data'];
  
  ui_hide_goback();
  ui_show_header();
  ui_show_footer();
  ui_show_page();
  ui_set_choose_title();
  
  _html += '<table border="0" cellspacing="5" cellpadding="10"><tr></td valign="top" width="50%">';
  for( i = 0; i < Math.min( _max_hits, _data.length ); ++i ) {
    if( i % Math.floor( _max_hits / 2 ) == 0 ) {
      _html += '</td><td valign="top" width="50%">';
    }
    _html += __html_link( _data[i], false );
  }
  _html += '</td></tr></table>';
  global_search_results = _html;
  ui_set_results( _html );
  ui_hide_throb();
  if( global_offset > 0 ) {
    ui_show_prev();
  }
  if( _data.length < global_limit ) {
    ui_hide_next();
  }
  ui_show_footer();
  global_semaphore_search = false;
}


function callback_offset_search() {
  if( global_search.length < 1 ) {
    ui_show_message_again();
    return false;
  }
  ui_hide_footer();
  ui_hide_prev();
  ui_show_next();
  ui_set_results( '' );
  ui_show_results_throb();
  var _payload = { 'method_id' : 'search_geoname', 'data' : global_search, 'offset' : global_offset, 'limit' : global_limit };
  global_geoEngine.php( _payload, callback_searchShowResults );
}

function callback_search() {
  if( global_semaphore_search )
    return false
  
  global_semaphore_search = true;
  var _val = $('#dashbar #content #search #input').val();
  global_search = __filter_search_input( _val );
  $('#dashbar #content #search #input').val( global_search );
  global_offset = 0;
  callback_offset_search();
}


function callback_recent( data, textStatus ) {
  if( __check_exception( data ) ) {
    return false;
  }
  var _max_hits = 11;
  var _html = '';
  var _data = data['data'];
  for( i = 0; i < Math.min( _max_hits, _data.length ); ++i ) {
    _html += __html_link( _data[i], true );
  }
  ui_set_recent( _html );
}


function callback_hall( data, textStatus ) {
  if( __check_exception( data ) ) {
    return false;
  }
  var _max_hits = 11;
  var _html = '';
  var _data = data['data'];
  for( i = 0; i < Math.min( _max_hits, _data.length ); ++i ) {
    _html += __html_link( _data[i], true );
  }
  ui_set_hall( _html );
}

function callback_marker_enter() {
  this.tooltip.show();
}

function callback_marker_leave() {
  this.tooltip.hide();
}

function callback_marker_click() {
  callback_clickCity( this.geonameid );
  callback_mapPanto( this.latitude, this.longitude );
  window.open( this.link );
}
// end callbacks


function callback_ipLocation( data, textStatus ) {
  if( __check_exception( data ) ) return false;
  
  var _data = data['data'];
  if( _data == false ) {
    callback_mapMoveend();
    return;
  }
  var _lat = _data['latitude'];
  var _lng = _data['longitude'];
  callback_mapPanto( _lat, _lng );
  callback_mapMoveend();
}

// begin main
function geoengine_init( lat, lng, zl ) {
  lat = typeof(lat) == 'undefined' ? 46.8400 : lat;
  lng = typeof(lng) == 'undefined' ? 13.2270 : lng;
  zl  = typeof(zl)  == 'undefined' ? 5 : zl;
  global_geoEngine.init( 'map', lat, lng, zl );
  global_geoEngine.setPHPEngine( 'geoengine.php' );
  global_geoEngine.setIPLocation( 'iplocation.php' );  
  global_positionEngine.shift( 0, 50 ); // lat, lng shift in pixels
  global_markerEngine.setCallback( 'click', callback_marker_click );
  global_markerEngine.setCallback( 'mouseover', callback_marker_enter );
  global_markerEngine.setCallback( 'mouseout', callback_marker_leave );
  // marker icons
  var _icon = new coMarkerIcon();
  _icon.init( './img', 'm', 42, 42, 10 );
  global_markerEngine.setMarkerIcon( 10, _icon );
  _icon = new coMarkerIcon();
  _icon.init( './img', 'm', 38, 38, 9 );
  global_markerEngine.setMarkerIcon( 9, _icon );
  _icon = new coMarkerIcon();
  _icon.init( './img', 'm', 34, 34, 8 );
  global_markerEngine.setMarkerIcon( 8, _icon );
  _icon = new coMarkerIcon();
  _icon.init( './img', 'm', 30, 30, 7 );
  global_markerEngine.setMarkerIcon( 7, _icon );
  _icon = new coMarkerIcon();
  _icon.init( './img', 'm', 25, 25, 6 );
  global_markerEngine.setMarkerIcon( 6, _icon );
  _icon = new coMarkerIcon();
  _icon.init( './img', 'm', 21, 21, 5 );
  global_markerEngine.setMarkerIcon( 5, _icon );
  
  // init icons
  global_geoEngine.setMarkerEngine( global_markerEngine );
 
  // listeners
  global_geoEngine.addListener( 'moveend', callback_mapMoveend );  
  global_geoEngine.addListener( 'mousemove', callback_mapShowPosition );
  
  // init map
  var _payload = {};
  callback_mapMoveend();
}


function page_init() { 
  ui_hide_page();

  $('#page #header #close').click(
    function(e) { ui_hide_page(); }
  );

  $('#page #header #goback').click(
    function(e) {
      ui_set_choose_title();
      ui_hide_goback();
      ui_set_results( global_search_results );
      ui_show_footer();
    }
  );

  $('#page #footer #next').click(
    function(e) {
      global_offset += global_limit;
      callback_offset_search();
    }
  );

  $('#page #footer #prev').click(
    function(e) {
      if( global_offset >= global_limit ) {
        global_offset -= global_limit;
      }
      callback_offset_search();
    }
  );
}


function cancel_page_init() { 
  ui_hide_footer();
  ui_hide_throb();
  ui_hide_header();
  ui_hide_goback();
  ui_hide_close();
  ui_show_page();
}



function dashbar_init() {
  // Search Input
  $('#dashbar #content #search #button').click( callback_search );
  // ENTER
  $('#dashbar #content #search #input').keypress(
    function(e) {
      if( e.which == 13 ) callback_search();
    }
  );
  
  $('#dashbar #content #search #input').keyup(
    function(e) { 
      if( e.which == 27 ) ui_page_hide();
    }
  );
  
  $('#dashbar #content #faq #title').click(
    function(e) {
      ui_show_faq( 'pagepos_faq.html' );
    }
  );
}


function button_init() {
}

function hall_init() {
  var _payload = { 'method_id' : 'get_popular' };
  global_geoEngine.php( _payload, callback_hall );  
}


function recent_init() {
  var _payload = { 'method_id' : 'get_latest' };
  global_geoEngine.php( _payload, callback_recent );  
}
