function coPositionEngine() {
  var _mouse_lat = 0.0;
  var _mouse_lng = 0.0;
  var _lat_shift = 0;
  var _lng_shift = 0;

  this.shift = function( lat_shift, lng_shift ) {
    _lat_shift = lat_shift;
    _lng_shift = lng_shift;
  }

  this.update = function( lat, lng ) {
    _mouse_lat = lat;
    _mouse_lng = lng;
  }  
  
  this.setCenter = function( engine ) {
    engine.setCenter( _mouse_lat, _mouse_lng, _lat_shift, _lng_shift );
  }


  this.mapPanto = function( engine, lat, lng, zl ) {
    zl = typeof(zl) == 'undefined' ? 5 : zl;
    engine.setCenter( lat, lng, _lat_shift, _lng_shift, zl );
  }
  
} // end class

