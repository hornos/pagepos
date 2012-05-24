function coMarkerIcon() {
  var _icon = null;

  this.init = function( path, name, w, h, z ) {
    var _w2 = Math.floor( w / 2 );
    var _h2 = Math.floor( h / 2 );
    var _cpath = path + '/' + name + '_c_' + z + '_' + w + '_' + h + '.png';
    var _tpath = path + '/' + name + '_t_' + z + '_' + w + '_' + h + '.png';
    var _gpath = path + '/' + name + '_c_' + z + '_' + w + '_' + h + '.gif';
    _icon = new GIcon( G_DEFAULT_ICON ); 
    _icon.image         = _cpath;
    _icon.printImage    = _gpath;
    _icon.mozPrintImage = _gpath;
    _icon.transparent   = _tpath;
    _icon.iconSize   = new GSize( w, h );
    _icon.imageMap   = [_w2,h, 0,_h2, _w2,0, w,_h2];
    _icon.shadowSize = new GSize( 0, 0 );
    _icon.iconAnchor = new GPoint( _w2, _h2 );
    _icon.infoWindowAnchor = new GPoint( _w2, _h2 );
  }
  
  this.get = function() {
    return _icon;
  }
} // end class
