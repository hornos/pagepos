
// begin geoEngine class
function coGeoEngine() {
  var _map = null;
  var _php_engine = null;
  var _zoomlevel = 5;
  var _zoomlevel_changed = false;
  var _clean_markers = false;
  var _bounds = Array(0.0,0.0,0.0,0.0);
  var _size = Array(0,0);

  var _shift0 = {
    10 : { 'lat' : 0.0 , 'lng' : 0.0 },
     9 : { 'lat' : 0.0 , 'lng' : 0.0 },
     8 : { 'lat' : 0.0 , 'lng' : 0.0 },
     7 : { 'lat' : 0.0 , 'lng' : 0.0 },
     6 : { 'lat' : 0.0 , 'lng' : 0.0 },
     5 : { 'lat' : 0.0 , 'lng' : 0.0 }
   };

  var _shift = _shift0;
  var _marker_icons = null;
  var _marker_engine = null;
  var _markers = [];
  var _infoid = null;
  var _pixel2lat = 0.0;
  var _pixel2lng = 0.0;

  this.init = function( mapid, lat, lng, zl ) {
    _map = new google.maps.Map2(document.getElementById( mapid ) );
    var _maptype = G_PHYSICAL_MAP;
    _maptype.getMinimumResolution = function() { return 5; }
    _maptype.getMaximumResolution = function() { return 10; }
    _zoomlevel = zl;
    _map.setMapType( _maptype );
    _map.setCenter( new google.maps.LatLng( lat, lng ), _zoomlevel );
    var _mapcontrol = new GLargeMapControl3D();
    _map.addControl( _mapcontrol );
    this.getBounds();
    this.getZoomlevel();
    this.setUnits();
  }
  
  this.setPHPEngine = function( engine ) {
    _php_engine = engine;
  }

  this.setIPLocation = function( engine ) {
    _ip_location = engine;
  }
  
  this.php = function( payload, callback ) {
    $.post( _php_engine, payload, callback, 'json' );
  }
  
  this.iploc = function( payload, callback ) {
    $.post( _ip_location, payload, callback, 'json' );
  }

  this.getZoomlevel = function() {
    var __zoomlevel = _map.getZoom();
    _zoomlevel_changed = false;
    if( _zoomlevel != __zoomlevel ) {
      _zoomlevel_changed = true;
      this.setUnits();
    }
    _zoomlevel = __zoomlevel;
    return _zoomlevel;
  }
  
  this.showZoomlevel = function( id ) {
    $( id ).html( _zoomlevel );
  }

  this.getBounds = function() {
    var __bounds = _map.getBounds();
    var __sw = __bounds.getSouthWest();
    var __ne = __bounds.getNorthEast();
    _bounds = Array( __ne.lat(), __ne.lng(), __sw.lat(), __sw.lng() );
    return _bounds;
  }

  this.getSize = function() {
    var __size = _map.getSize();
    _size = Array( __size.width, __size.height );
    return _size;
  }

  this.setUnits = function( latshift, lngshift ) {
    var __bounds = this.getBounds();
    var __size   = this.getSize();
    _pixel2lat = Math.abs( __bounds[0] - __bounds[2] ) / __size[0];
    _pixel2lng = Math.abs( __bounds[1] - __bounds[3] ) / __size[1];
    $('#latunit').html( _pixel2lat );
    $('#lngunit').html( _pixel2lng );    
  }
 
  this.panTo = function( lat, lng ) {
    _map.panTo( new GLatLng( lat, lng ) );
  }

  this.setCenter = function( lat, lng, latsft, lngsft, zl ) {
    if( typeof(zl) == 'undefined' ) {
      zl = 5;
    }
    else {
      _clean_markers = true;
    }
    _map.setZoom( zl );
    this.setUnits();
    var __lat = eval(lat) + eval(_pixel2lat) * eval(latsft);
    var __lng = eval(lng) + eval(_pixel2lng) * eval(lngsft);
    _map.setCenter( new GLatLng( __lat, __lng ) );
  }

  this.openMarker = function( marker, html, mouse_lat, mouse_lng ) {
    marker.openInfoWindowHtml( html );
    _map.panTo( new GLatLng( mouse_lat, mouse_lng ) );
  }

  this.addListener = function( event, callback ) {
    GEvent.addListener( _map, event, callback );
  }

  this.setMarkerIcons = function( icons ) {
     _marker_icons = icons;
  }

  this.setMarkerEngine = function( engine ) {
     _marker_engine = engine;
  }

  this.addMarker = function( lat, lng ) {
    _map.addOverlay( new GMarker( new GLatLng( lat, lng ) ) );
  }
  
  this.addMarkers = function( data ) {
    if( _zoomlevel_changed || _clean_markers ) {
      _map.clearOverlays();
      _markers = [];
      _clean_markers = false;
    }
    var _icon = _marker_engine.getMarkerIcon( _zoomlevel );
    var _marker_options = {};
    if( _icon ) {
      _marker_options = {icon:_icon};
    }
    var __shift = _shift[_zoomlevel];
    var _size = data.length;
    for( i = 0; i < _size; ++i ) {
      var _m = data[i];
      var _geonameid = _m['geonameid'];
      var _geoname   = _m['name'];
      var _status    = _m['status'];
      if( ! _markers[_geonameid] ) {
        var _latitude  = _m['latitude'];
        var _longitude = _m['longitude'];
        var _link = 'http://' + _m['link'];
        var _marker  = new GMarker( new GLatLng( eval(_latitude)+eval(__shift['lat']), eval(_longitude)+eval(__shift['lng']) ), _marker_options );
        var _tooltip = new Tooltip( _marker, __html_tooltip( _geoname, _status ), 3 );
        _marker.tooltip   = _tooltip; 
        _marker.geonameid = _geonameid;
        _marker.geoname   = _geoname;
        _marker.latitude  = _latitude;
        _marker.longitude = _longitude;
        _marker.link      = _link;
        GEvent.addListener( _marker, 'click', _marker_engine.getCallback( "click" ) );
        GEvent.addListener( _marker, 'mouseover', _marker_engine.getCallback( "mouseover" ) );
        GEvent.addListener( _marker, 'mouseout', _marker_engine.getCallback( "mouseout" ) );
        _map.addOverlay( _marker );
        _map.addOverlay( _tooltip );        
        _markers[_geonameid] = true;
      }
    }
  }
  
}
// end geoEngine class
