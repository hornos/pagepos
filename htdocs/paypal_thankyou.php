<?php
// begin cobra header
if( ! $bootstrap = getenv( 'COBRA_BOOTSTRAP' ) ) die( 'Internal Error' );
require_once( $bootstrap );
// end cobra header

// defines
cobra_define( 'DEBUG', false );

// autoload
cobra_autoload( 'ppSite', 'pagepos' );

// init site
$ppsite = new ppSite();

// site lock
if( is_readable( COBRA_SITE_LOCK ) ) {
  die( file_get_contents( COBRA_SITE_LOCK ) );
}

// proxy check
if( ! $jsapi_key = $ppsite->rpc( 'proxy_check' ) ) {
  coRequest::jresponse( 'exception', 'proxy error' );
  die();
}

// session check
if( ! $ppsite->rpc( 'session_check') ) {
  $ppsite->html_redirect( PAGEPOS_HOME_URL );
  die();
}

// setup location
if( $_SESSION['name'] != false ) {
 $cancel = true;
 $lat = $_SESSION['latitude'];
 $lng = $_SESSION['longitude'];
 $country = $_SESSION['country_name'];
 $name    = $_SESSION['name'];
 $geonameid = $_SESSION['geonameid'];
}
else {
  $ppsite->html_redirect( PAGEPOS_HOME_URL );
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="./img/favicon.png" >
    <!-- Style -->
    <link rel="stylesheet" type="text/css" href="./css/common.css" >
    <link rel="stylesheet" type="text/css" href="./css/dashbar.css" >
    <link rel="stylesheet" type="text/css" href="./css/map.css" >
    <link rel="stylesheet" type="text/css" href="./css/tooltip.css" >
    <link rel="stylesheet" type="text/css" href="./css/cancel_page.css" >
    <!-- Script -->
    <script type="text/javascript" src="http://www.google.com/jsapi?key=<?php echo $jsapi_key;?>"></script>
    <script>
      google.load( "maps", "2.x", {"other_params":"sensor=true"} );
    </script>
    <script language="JavaScript" src="./js/Tooltip.js"></script>
    <script language="JavaScript" src="./js/jquery.js"></script>
    <script language="JavaScript" src="./js/coMarkerIcon.jq.js"></script>	
    <script language="JavaScript" src="./js/coMarkerEngine.jq.js"></script>	
    <script language="JavaScript" src="./js/coPositionEngine.jq.js"></script>	
    <script language="JavaScript" src="./js/coGeoEngine.jq.js"></script>	
    <script language="JavaScript" src="./js/coImage.jq.js"></script>	
    <script language="JavaScript" src="./js/pagepos_util.js"></script>	
    <script language="JavaScript" src="./js/pagepos_ui.js"></script>	
    <script language="JavaScript" src="./js/pagepos_html.js"></script>	
    <script language="JavaScript" src="./js/pagepos_engine.js"></script>	
    <!-- <script language="JavaScript" src="./js/pagepos_paypal_init.js"></script>-->
    <script>
      function paypal_init() {
<?php
  if( $cancel ) {
    echo "geoengine_init(".$lat.",".$lng.", 10 );";
  }
  else {
    echo "geoengine_init();";
  }
?>
        cancel_page_init();
<?php
  if( $cancel ) {
    $home_url = PAGEPOS_HOME_URL;
    echo "ui_set_results( __html_paypal_thankyou('".$geonameid."','".$name."','".$country."','".$home_url."' ) );";
  }
?>
  }
    $('document').ready(paypal_init);
    </script>
    <title>PAGEPOS - THIS IS MY WORLD</title>
</head>
  <body onunload="GUnload();">

  <div id="map"></div>

  <div id="page">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
      <tr>
        <td id="topleft85"></td><td id="top85"></td><td id="topright85"></td>
      </tr>
      
      <tr>
        <td id="left85"></td>
        <td id="center85" valign="top">
          <div id="header">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td id="title" align="left" valign="top"></td>
                <td id="buttons" align="right" valign="top">
                  <span id="goback">Go Back</span>
                  &nbsp;&nbsp;
                  <span id="close">Close</span>
                  <!--
                  <img id="goback" src="./img/goback_f.png">
                  <img id="close" src="./img/close_f.png">
                  -->
                </td>
              </tr>
            </table>
          </div>
        </td>
        <td id="right85"></td>
      </tr>

      <tr>
        <td id="left85"></td>
        <td id="center85" valign="top">        
          <div id="content" align="center">
            <div id="results">
            <img id="throb" src="./img/throb_16x16.gif">
            </div>
          </div>
        </td>
        <td id="right85"></td>
      </tr>

      <tr>
        <td id="left85"></td>
        <td id="center85" valign="top">        
          <div id="footer" align="center">
          <span id="prev">Previous</span>
          &nbsp;&nbsp;
          <span id="next">Next</span>
          </div>
        </td>
        <td id="right85"></td>
      </tr>

      <tr>
        <td id="bottomleft85"></td><td id="bottom85"></td><td id="bottomright85"></td>
      </tr>
    </table>
  </div> <!-- end page -->
  
  
  </body>
</html>
