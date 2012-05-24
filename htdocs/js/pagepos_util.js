
function __trim( str ) {
  return str.replace(/^\s+|\s+$/g,"");
}


function __ltrim(str) {
  return str.replace(/^\s+/,"");
}


function __rtrim(str) {
  return str.replace(/\s+$/,"");
}


function __filter_search_input( str ) {
  re = new RegExp( '[\'\\\\"]', "g" );
  var _str = "";
  _str = __trim( str );
  _str = _str.replace( re, '' );
  _str = _str.substr(0,12);
  return _str;
}


function __filter_email_input( str ) {
  re = new RegExp( '[^a-zA-Z0-9@-_. ]', "g" );
  var _str = "";
  _str = __trim( str );
  _str = _str.replace( re, '' );
  _str = _str.substr(0,64);
  return _str;
}


function __filter_url_input( str ) {
  re1 = new RegExp( '[^a-zA-Z0-9?=-_:./ ]', "g" );
  re2 = new RegExp( '^.*://' );
  var _str = "";
  _str = __trim( str );
  _str = _str.replace( re1, '' );
  _str = _str.replace( re2, '' );
  _str = _str.substr(0,128);
  return _str;
}


function __check_exception( data, showalert ) {
  showalert = typeof(showalert) == 'undefined' ? true : showalert;
  if( data['type'] == 'exception' ) {
    if( DEBUG && showalert ) {
      // alert( data['data'] );
      ui_show_message( data['data'] );
    }
    return true;
  }
  if( data['type'] == 'message' ) {
    if( showalert ) {
      // alert( data['data'] );
      ui_show_message( data['data'] );
    }
    return true;
  }
  
  return false;
}


function __filter_admin1( data ) {
  return data.replace( ' (general)', '' );
}


function __filter_location( country, admin1 ) {
  if( country == admin1 ) {
    return country;
  }
  return country + ', ' + admin1;    
}

function __sleep( ms ) {
  var dt = new Date();
  dt.setTime(dt.getTime() + ms);
  while (new Date().getTime() < dt.getTime());
}
