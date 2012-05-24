
function coImage() {
  var __image_id = null;
  var __images   = [];
  
  this.init = function( id, images ) {
    __image_id = id;
    for( i in images ) {
	  __images[i] = new Image();
	  __images[i].src = images[i];
    }
  }
   

  this.active = function() {
    $(__image_id).attr( 'src', __images['active'].src );
  },
  
  this.inactive = function() {
    $(__image_id).attr( 'src', __images['inactive'].src );
  }
  
}
