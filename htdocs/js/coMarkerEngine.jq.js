function coMarkerEngine() {
  var _marker = null;
  var _marker_icons = [];
  var _callbacks = [];
  
  this.setCallback = function( event, callback ) {
    _callbacks[event] = callback;
  }
  
  this.getCallback = function( event ) {
    return _callbacks[event];
  }
  
  this.setMarker = function( marker ) {
    _marker = marker;
  }
  
  
  this.getMarker = function() {
    return _marker;
  }
  
  
  this.setMarkerIcon = function( id, icon ) {
    _marker_icons[id] = icon.get();
  }
  
  
  this.getMarkerIcon = function( id ) {
    return _marker_icons[id];
  }
} // end class

