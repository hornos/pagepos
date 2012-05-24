function __html_filter_email(field) {
  var filtered = __filter_email_input( field.value );
  var original = field.value;
  var ret = ( original.length == filtered.length );
  field.value = filtered;
  return ret;
}


function __html_filter_url(field) {
  var filtered = __filter_url_input( field.value );
  var original = field.value;
  var ret = ( original.length == filtered.length );
  field.value = filtered;
  return ret;
}



function __html_validate_email( field, alerttxt ) {
  __html_filter_email(field);
  var filtered = field.value;
  var atpos  = filtered.indexOf( "@" );
  var dotpos = filtered.lastIndexOf( "." );
  if( atpos < 1 || dotpos - atpos < 2 ) {
    alert( alerttxt );
    return false;
  }
  return true;
}


function __html_validate_web( field, alerttxt ) {
  __html_filter_url(field);
  var filtered = field.value;
  var fdotpos = filtered.indexOf( "." );
  var ldotpos = filtered.lastIndexOf( "." );
  if( fdotpos < 1 || ldotpos - fdotpos < 2 ) {
    alert( alerttxt );
    return false;
  }
  else {
    return true;
  }
}


function __html_validate_buy_form( formid ) {
  var form = $(formid);
  var _email = '';
  var _url   = '';
  var _geonameid = 0;
  var _price = 0;

  if( __html_validate_email( form[0].input_email, "Not a valid e-mail address!" ) == false) {
    form[0].input_email.focus();
    return false;
  }
  _email = form[0].input_email.value;
  
  if( __html_validate_web( form[0].input_web, "Not a valid webpage address!" ) == false) {
    form[0].input_web.focus();
    return false;
  }
  _url = form[0].input_web.value;
  _geonameid = form[0].item_number.value;
  _price = form[0].amount.value;

  if( _geonameid == 0 ) return false;

  return true;
}


function __html_validate_check_form( formid ) {
  var ret = __html_validate_buy_form( formid );
  if( ! ret ) return ret;

  var form = $(formid);
  var _email = form[0].input_email.value;
  var _url = form[0].input_web.value;
  var _geonameid = form[0].item_number.value;
  var _price = form[0].amount.value;
  var _payload = { 'method_id' : 'status_lock', 'geonameid' : _geonameid, 'email' : _email, 'url' : _url };


  global_buy_geonameid = _geonameid;
  global_buy_email = _email;
  global_buy_url   = _url;
  global_buy_price = _price;
 
  ui_hide_footer();
  ui_hide_header();
  ui_show_results_throb();

  global_geoEngine.php( _payload, callback_statusLock );
  return false;
}




 function __html_buy_form( geonameid, name, country, price ) {
  // sandbox
  var _paypal_url  = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
  var _business_id = '';
  var _server_url = '';

  var _return_url = _server_url + '/paypal_thankyou.php';
  var _cancel_url = _server_url + '/paypal_cancel.php';
  var _logo_url   = _server_url + '/img/paypal_logo.png';
  var _notify_url = _server_url + '/paypal_ipn_listener.php';

  //
  var _buy1 = ' You have 1 hour to complete the purchase process. We have registered the following e-mail and url for your link. Please proceed to PayPal!';
  var _buy2 = 'The subscription will cost you';
  var _html = '<div id="city_info">';
  _html += '<div id="text" align="left">Your are now to own <span class="city_name">';
  _html += '<b>' + name + '</b></span> (' + country + ').' + _buy1 + '<br><br>';
  _html += '<div align="center">' + _buy2 + ' ';
  _html += '<span class="city_name"><b>$' + price + '</b></span>.</div>';
  _html += '</div>';
  // begin paypal form  
  _html += "<form action='"+_paypal_url+"' METHOD='POST' onsubmit='return __html_validate_buy_form(this);'>";
  _html += '<div id="email_title"><span class="city_name">Your e-mail</span> (kate)</span></div>';
  _html += "<input readonly value=\""+global_buy_email+"\" class='buy' size='32' maxlength='64' type='text' id='input_email' name='input_email' onkeyup='return __html_filter_email(this)'>";
  _html += '<div id="email_title"><span class="city_name">Your website</span> (hello)</span><div>';
  _html += "<input readonly value=\""+global_buy_url+"\" class='buy' size='32' maxlength='128' type='text' id='input_web' name='input_web' onkeyup='return __html_filter_url(this)'>";
  _html += "<div id='email_title'><input type='image' id='submit' name='submit' src='./img/proceedtopaypal_f.png' border='0' align='top' alt='PayPal'/></div>";
  _html += "<div class='message'>PayPal is handling your purchase securely. If you encounter any problem during pruchase please contact us with your PayPal reciept.</div>";
  // cmd
  _html += '<input type="hidden" id="redirect_cmd" name="redirect_cmd"         value="_xclick"/>';
  _html += '<input type="hidden" id="cmd" name="cmd"                           value="_ext-enter"/>';
  _html += '<input type="hidden" id="bn" name="bn"                             value="ActiveMerchant"/>';
  // notify
  _html += '<input type="hidden" id="notify_url" name="notify_url"             value="'+_notify_url+'"/>';
  // shopping cart
  _html += '<input type="hidden" id="business" name="business"                 value="'+_business_id+'"/>';
  _html += '<input type="hidden" id="amount" name="amount"                     value="'+price+'"/>';
  _html += '<input type="hidden" id="shopping_url" name="shopping_url"         value="'+_server_url+'"/>';
  _html += '<input type="hidden" id="quantity" name="quantity"                 value="1"/>';
  _html += '<input type="hidden" id="item_name" name="item_name"               value="'+name+' ('+country+')'+'"/>';
  _html += '<input type="hidden" id="item_number" name="item_number"           value="'+geonameid+'"/>';
  // payment transaction
  _html += '<input type="hidden" id="address_override" name="address_override" value="0"/>';
  _html += '<input type="hidden" id="custom" name="custom"                     value="'+geonameid+'"/>';
  _html += '<input type="hidden" id="currency_code" name="currency_code"       value="USD"/>';
  _html += '<input type="hidden" id="no_note" name="no_note"                   value="1"/>';
  _html += '<input type="hidden" id="no_shipping" name="no_shipping"           value="1"/>';
  // display
  // _html += '<input type="hidden" id="page_style" name="page_style"            value="0"/>';
  _html += '<input type="hidden" id="rm" name="rm"                             value="2"/>';
  _html += '<input type="hidden" id="return" name="return"                     value="'+_return_url+'">';
  _html += '<input type="hidden" id="cancel_return" name="cancel_return"       value="'+_cancel_url+'"/>';
  _html += '<input type="hidden" id="cpp_header_image" name="cpp_header_image" value="'+_logo_url+'"/>';
  // _html += '<input type="hidden" value="true" name="custom"/>';
  _html += '<input type="hidden" id="charset" name="charset"                   value="utf-8"/>';
  _html += "</form>";
  
//  _html += "<div id='email_title'><img id='submit' src='/pagepos/images/proceedtopaypal_f.png'></div>";  
//  _html += '<img src="/pagepos/images/proceedtopaypal.png"> to complete your trasanction.<br><br>';
//  _html += 'The form above will take you to Paypal which we use to process out payments securely. ';
//  _html += 'You find terms and conitions <a href="">here</a>.';  
  _html += '</div>';

  return _html;
}


function __html_check_form( geonameid, name, country, price ) {
  global_buy_name = name;
  global_buy_country = country;
  var _buy1 = 'is a very good choice to link your webpage to, since anyone who searches for it and clicks on it will immediately see your website. It will take just a moments from now to finish the whole process.';
  var _buy2 = 'The subscription will cost you';
  var _html = '<div id="city_info">';
  _html += '<div id="text" align="left"><span class="city_name">';
  _html += '<b>' + name + '</b></span> (' + country + ') ' + _buy1 + '<br><br>';
  _html += '<div align="center">' + _buy2 + ' ';
  _html += '<span class="city_name"><b>$' + price + '</b></span>.</div>';
  _html += '</div>';
  // begin paypal form  
  // _html += "<form onsubmit='return __html_validate_check_form(this);'>";
  _html += '<form id="check_form" name="check_form">';
  _html += '<div id="email_title"><span class="city_name">Your e-mail</span> (kate)</span></div>';
  _html += "<input class='buy' size='32' maxlength='64' type='text' id='input_email' name='input_email' onkeyup='return __html_filter_email(this)'>";
  _html += '<div id="email_title"><span class="city_name">Your website</span> (hello)</span><div>';
  _html += "<input class='buy' size='32' maxlength='128' type='text' id='input_web' name='input_web' onkeyup='return __html_filter_url(this)'>";
  _html += "<div id='email_title'><input type='image' id='check' name='check' src='./img/checkandbuy_f.png' ";
  _html += "onclick='return __html_validate_check_form(\"#page #check_form\");' border='0' align='top' alt='CheckAndBuy'/></div>";
  _html += "<div class='message'>By clicking 'Check and Buy' button you automatically accept our Terms & Conditions in the pagepos faq.</div>";
  _html += '<input type="hidden" id="item_number" name="item_number"           value="'+geonameid+'"/>';
  _html += '<input type="hidden" id="amount" name="amount"                     value="'+price+'"/>';
  _html += "</form>";
  _html += '</div>';
  return _html;
}



function __html_paypal_cancel( name, country, url ) {
  var _html = '<div id="city_info">'; 
  _html = 'You\'ve just canceled purchasing <span class="city_name">' + name + '</span>.';
  _html += '<br><br><a href="'+url+'">Back to Pagepos</a>';
  _html += '</div>';
  return _html;
}


function __html_paypal_thankyou( geonameid, name, country, url ) {
  var _html = '<div id="city_info">'; 
  _html = 'Congratulate! You\'ve just purchased <span class="city_name">' + name + '</span>.';
  _html += '<br><br><a href="'+url+'">Back to Pagepos</a>';
  _html += '</div>';
  return _html;
}


function __html_purchase_warning( name, country ) {
  var _html = '<div id="city_info"><div class="purchase">'; 
  _html = '<br><br>Somebody is purchasing <span class="city_name">' + name + '</span> now.<br>';
  _html += ' However, it can happen that the buyer cancels the process.<br><br>';
  _html += ' Check back later for availability!';
  _html += '</div></div>';
  return _html;
}

function __html_demo_warning( name, country ) {
  var _html = '<div id="city_info">'; 
  _html = '<span class="city_name">' + name + '</span> is a demo link.';
  _html += ' We will make it free at some arbitrary time.';
  _html += ' So check back soon for availability!';
  _html += '</div>';
  return _html;
}

function __html_sold_callback( geonameid, latitude, longitude, link ) {
  global_semaphore_mapMoveend = false;
  global_link = link;
  ui_hide_page();
  callback_clickCity( geonameid );
  // callback_mapPanto( latitude, longitude );
  // callback_mapMoveend();
  window.open( link );
  return false;
}

function __html_demo_callback( geonameid, latitude, longitude, link ) {
  global_semaphore_mapMoveend = false;
  global_link = link;
  ui_hide_page();
  callback_clickCity( geonameid );
  // callback_mapPanto( latitude, longitude );
  // callback_mapMoveend();
  window.open( link );
  return false;
}

function __html_free_callback( geonameid, latitude, longitude ) {
  ui_set_welcome_title();
  ui_show_results_throb();
  callback_mapPanto( latitude, longitude, 10 );
  callback_getCity( geonameid );
  return false;
}



function __html_link( data, islist ) {
  var _html	= '';
  var _name     = data['name'];
  var _country  = data['country_name'];
  var _ccode    = data['country_code'];  
  var _admin1   = __filter_admin1( data['admin1_name'] );
  var _location = ' (' + __filter_location( _country, _admin1 ) + ')';
  var _geonameid = data['geonameid'];
  var _status    = data['status'];
  var _link_link = 'http://' + data['link'];
  var _latitude  = data['latitude'];
  var _longitude = data['longitude']; 
  
  if( islist ) { _location = ' (' + _country + ')'; }
  var _link_name = '<span class="search_result">' + _name + _location + '</span>';

  if( _status == 'sold' ) {
    var _link_callback = 'onclick="return __html_sold_callback(' + _geonameid + ',' + _latitude + ',' + _longitude + ',\'' + _link_link + '\');"';
    _link_name = '<a href="" ' + _link_callback + '>' + _link_name + '</a>';
  }
  else if( _status == 'demo' ) {
    var _link_callback = 'onclick="return __html_demo_callback(' + _geonameid + ',' + _latitude + ',' + _longitude + ',\'' + _link_link + '\');"';
    _link_name = '<a href="" ' + _link_callback + '>' + _link_name + '</a>';
  }
  else {
    var _link_callback = 'onclick="__html_free_callback(' + _geonameid + ',' + _latitude + ',' + _longitude + ');"';
    _link_name = '<span ' + _link_callback +  '>' + _link_name + '</span>';
  }
  
  var _link = '<div class="search_result">' + _link_name + '</div>';
  if( islist ) { _link = '<div class="list">' + _link_name + '</div> '; }
  
  return _html + _link;
}


function __html_tooltip( geoname, status ) {
  var _demo_message = '';
/*
  if( status == 'demo' ) {
    _demo_message = 'This is a demo link and will be free soon.';
    _demo_message = '<br><span style="color:grey; font-size: 70%">' + _demo_message + '</span>'
  }
*/
  var _tooltip = '';
  _tooltip  = '<div id="tooltip">';
  _tooltip += '<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">';
  _tooltip += '<tr><td id="topleft85"></td><td id="top85"></td><td id="topright85"></td></tr>';
  _tooltip += '<tr><td id="left85"></td>';
  _tooltip += '<td id="center85" valign="middle" align="center"><div id="content" align="center">';
  _tooltip += '<span class="white">Click on</span> ' + geoname + '<span class="white">!' + _demo_message;
  _tooltip += '</div></td>';
  _tooltip += '<td id="right85"></td></tr>';
  _tooltip += '<tr><td id="bottomleft85"></td><td id="bottom85"></td><td id="bottomright85"></td></tr>';
  _tooltip += '<tr><td id="bottomarrow85" align="center" valign="top" colspan="3"></td></tr>';
  _tooltip += '</table>';
  return _tooltip;
}


  