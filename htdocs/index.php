<?php
// begin cobra header
if( ! $bootstrap = getenv( 'COBRA_BOOTSTRAP' ) ) die( 'internal error' );
require_once( $bootstrap );
// end cobra header

// defines
cobra_define( 'DEBUG', false );

// autoload
cobra_autoload( 'ppSite', 'pagepos' );

// init site
$ppsite = new ppSite();

// site lock
if( is_readable( COBRA_SITE_LOCK ) ) die( file_get_contents( "./unavailable.html" ) );

// proxy check
if( ! $jsapi_key = $ppsite->rpc( 'proxy_check' ) ) die();

// run command
$ppsite->rpc( 'session_start' );
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
    <link rel="stylesheet" type="text/css" href="./css/page.css" >
    <!-- Script -->
    <script type="text/javascript">
      var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
      document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
      try {
        var pageTracker = _gat._getTracker("");
        pageTracker._trackPageview();
      } catch(err) {}
    </script>
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
    <script language="JavaScript" src="./js/pagepos_index_init.js"></script>
    <title>PAGEPOS - THIS IS MY WORLD</title>
</head>
  <body onunload="GUnload();">

  <div id="dashbar">
    <div id="content">
      <div id="logo">
        <img src="./img/logo.png" onclick='window.location=""' style="cursor: pointer;">
      </div>
      <!-- Progress bar -->
      <div id="progress" align="center">
        <img id="throb" src="./img/throb_16x16.gif">
        <script>ui_hide_progress_throb();</script>                  
      </div>
      <!-- Search field -->
      <div id="search" align="center">
        <table border="0" cellpadding="0" cellspacing="0"><tr>
          <td id="left"></td>
          <td id="center">
            <input type="text" id="input" size="16" maxlength="32">
          </td>
          <td id="right">
            <img src="./img/search_button_dark.png" id="button">
          </td>
        </tr></table>
      </div>
      <!-- Most popular -->
      <div id="hall">
        <div id="logo" align="left">
          <img src="./img/halloffame.png">
        </div>
        <div id="hall_list"></div>
      </div>
      <!-- Most recent -->
      <div id="recent">
        <div id="logo" align="left">
          <img src="./img/mostrecent.png">
        </div>
        <div id="recent_list"></div>
      </div>
      <!-- FAQ -->
      <div id="faq" align="center">
        <div id="title">
          pagepos faq
        </div>
      </div>

      <!-- Copyright -->
      <div id="copy" align="center">
        <div id="logo">
          <img src="./img/copyright.png">
        </div>
      </div>
    </div> <!-- end content -->
    <!-- Bottom -->
    <div id="bottom">
      <table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>
        <td id="left"></td>
        <td id="center"></td>
      </tr></table>
    </div>

  </div> <!-- end dashbar -->

  <div id="map"></div>

  <div id="page">
  <script>ui_hide_page();</script>
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
                </td>
              </tr>
            </table>
          </div>
        </td>
        <td id="right85"></td>
      </tr>

      <tr>
        <td id="left85"></td>
        <td id="center85" valign="top" height="100%">
          <div id="content" align="center">
            <div id="results">
            <div id="throb">
              <img class="throb_image" src="./img/throb_16x16.gif">
            </div>
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
