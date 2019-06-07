(function ($) {

  Drupal.behaviors.modalpop = {
    attach: function (context, settings) {
    ///////////////////////////////////////////////////////
      /**/
      if (typeof Drupal.settings.modalpop != "undefined") {
        var mpnid = Drupal.settings.modalpop.mpnid;
        var overlay_opacity = (Drupal.settings.modalpop.overlay_opacity / 100);
        var user_uid = Drupal.settings.modalpop.uid;
        var mptime = Drupal.settings.modalpop.time;
      }
      
      $('.modalpop-button').click(function(){
        $('.modalpop-container').fadeOut();
        $('#modalpop-overlay').fadeOut();
      });
      
      $('a.bodswitch').click(function(){
        $(this).next().slideToggle();
      });
      
      
      
      function trim(str) { 
        return str.replace(/^\s+|\s+$/g,'');
      }
      
      /**/
      function get_cookie(c_name){
        var cookies = document.cookie.split(';');
        for (var i=0; i < cookies.length; i++) {
          var cookieCrumbs = cookies[i].split('=');
          var cookieName = cookieCrumbs[0];
          var cookieValue = cookieCrumbs[1];


          if (trim(cookieName) == c_name) {
            return cookieValue;
          }
        }
        return false;
      }

      function set_cookie(c_name, c_value, c_days){
        var thisDate = new Date();
        thisDate.setTime(thisDate.getTime()+(c_days*24*60*60*1000))
        var cookieDate = thisDate.toGMTString();
        var theCookie = c_name + '=' + c_value + ' ;expires=' + cookieDate;
        document.cookie = theCookie;
      }
      
      function follow_modalpop_link(destination, target) {
        if (destination.length > 0 && destination.substring(1,2) != '#') {
          //alert('we have a link (' + destination + ') and it is not `#`:: ' + destination.substring(0,1));
          if (target == '_blank') { //open in a new window?
            window.open(destination);
          }
          else {
            location.href = destination;
          }
        }
        else {
          //alert('we have no link or it could be an anchor:: ' + destination);
        }
      }
      
      
      var cookie_name = 'block_popup_' + mpnid;
      
      if(typeof mpnid != "undefined" && !get_cookie(cookie_name)){//no cookie
        
        $('#modalpop-overlay').css('opacity', overlay_opacity).fadeIn();
        $('.modalpop-container#mpc' + mpnid).fadeIn();
        $('.modalpop-button a').click(function(e){
          var thisalt = $(this).attr('alt');
          var cookie_days = $(this).attr('rel');
          logclick($(this).attr('href'), $(this).attr('target'), Drupal.settings.basePath + 'modalpopstore', { 'nid': mpnid, 'uid': user_uid, 'whichbutt': thisalt, 'whichdate': mptime });
          set_cookie(cookie_name, thisalt, cookie_days);
          e.preventDefault();
        });
        
      }else{
        
      };
      
      //AJAX click-logging function with redirect on success
      function logclick(cl_href, cl_target, cl_url, cl_data) {
        //alert('about to do ajax magic...' + cl_url + cl_data);
        $.ajax({
          url: cl_url,
          async: true,
          dataType: 'text',
          type: "POST",
          data: cl_data,
          cache: false,
          success: function() {
            follow_modalpop_link(
              cl_href, 
              cl_target
            );
          },
        });
      }
      
      
    
      
      
      
    ///////////////////////////////////////////////////////
    }
  };

})(jQuery);