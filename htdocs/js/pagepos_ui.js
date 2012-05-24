// Global UI Callbacks

function ui_show_page() { $('#page').show(); }
function ui_hide_page() { $('#page').hide(); }

function ui_show_footer() { $('#page #footer').show(); }
function ui_hide_footer() { $('#page #footer').hide(); }

function ui_show_header() { $('#page #header').show(); }
function ui_hide_header() { $('#page #header').hide(); }

function ui_show_prev() { $('#page #footer #prev').show(); }
function ui_hide_prev() { $('#page #footer #prev').hide(); }

function ui_show_next() { $('#page #footer #next').show(); }
function ui_hide_next() { $('#page #footer #next').hide(); }

function ui_show_goback() { $('#page #header #goback').show(); }
function ui_hide_goback() { $('#page #header #goback').hide(); }

function ui_show_close() { $('#page #header #close').show(); }
function ui_hide_close() { $('#page #header #close').hide(); }

function ui_show_throb() { $('#page #content #throb').show(); }
function ui_hide_throb() { $('#page #content #throb').hide(); }

function ui_show_progress_throb() { $('#dashbar #content #progress #throb').show(); }
function ui_hide_progress_throb() { $('#dashbar #content #progress #throb').hide(); }

function ui_set_content( _html ) { $('#page #content').html( _html ); }
function ui_set_results( _html ) { $('#page #content #results').html( _html ); }

function ui_set_hall( _html ) { $('#dashbar #content #hall_list').html( _html ); }
function ui_set_recent( _html ) { $('#dashbar #content #recent_list').html( _html ); }
function ui_set_title( _html ) { $('#page #header #title').html( _html ); }

function ui_set_choose_title() { ui_set_title( '<img src="./img/chooseacity.png">' ); }
function ui_set_welcome_title() { ui_set_title( '<img src="./img/welcomehere.png">' ); }

function ui_show_results_throb() { ui_set_results( '<div id="throb"><img class="throb_image" src="./img/throb_16x16.gif"></div>' ); }

function ui_show_message( msg ) {
  ui_hide_goback();
  ui_hide_footer();
  ui_hide_throb();
  ui_set_title('');
  ui_set_results( '<div id="message">'+msg+'</div>' );
  ui_show_page();
}


function ui_show_faq( url ) {
  ui_hide_goback();
  ui_hide_footer();
  ui_hide_throb();
  ui_show_header();
  ui_show_close();
  ui_set_title('<span style="color: rgb(235,255,25);">Frequently Asked Questions</span>');
  ui_set_results( '<iframe frameborder="0" scrolling="auto" src ="'+url+'" width="100%" height="440"></iframe>' );
  ui_show_page();
}


function ui_show_message_reload() {
  ui_show_message( 'Reload the page!' );
}


function ui_show_message_again() {
  ui_show_message( 'Unfortunately the place you are looking for is not included in our database or your session has expired. Please check the spelling or reload the page!');
}


function ui_show_message_failed() {
  ui_show_message( 'Unfortunately we could not register your request. Please reload the page and try again!' );
}
