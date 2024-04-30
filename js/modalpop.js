(function ($, Drupal, drupalSettings, Cookies) {
  'use strict';

  Drupal.behaviors.mpsettings = {
    attach: function (context, settings) {
      ////////////////////////////////////////

      let bodtell = '';
      // Only show this if we're passing any settings to it.
      if (typeof drupalSettings.modalpop.mpvalues !== 'undefined') {
        let mpvalues = drupalSettings.modalpop.mpvalues;

        $.each(mpvalues, function(key, value) {
          if (typeof Cookies.get('block_popup_' + key) == 'undefined') {
            let mpnid = key;
            let overlay_opacity = (value.overlay_opacity / 100);
            let user_uid = value.uid;
            let mptime = value.mptime;
            let cookie_name = 'block_popup_' + key;
            let thistarget = '_self';
            const myCookieValue = Cookies.get(cookie_name);
            $('#modalpop-overlay-' + key).css('opacity', overlay_opacity).fadeIn();
            $('.modalpop-container#mpc' + key).fadeIn();

            $('.modalpop-button a').one('click', function(e){
              if(e.handled !== true) {
                let thisalt = $(this).attr('alt');
                let cookie_days = Number($(this).attr('rel'));
                if (typeof $(this).attr('target') != 'undefined') {
                  thistarget = $(this).attr('target');
                }
                logclick($(this).attr('href'), thistarget, drupalSettings.path.baseUrl + 'modalpopstore', { 'nid': mpnid, 'uid': user_uid, 'whichbutt': thisalt, 'whichdate': mptime });
                Cookies.set(cookie_name, thisalt, { expires: cookie_days});
                $('#modalpop-overlay-' + key).css('opacity', overlay_opacity).fadeOut();
                $('.modalpop-container#mpc' + key).fadeOut();
                e.handled = true;
              }

              e.preventDefault();
            });

            return false;
          }
        });
        
      }

      $('.bodswitch').on('mouseup', function(e){
        if(e.handled !== true) {
          bodtell = $(this).attr('rel');
          $('#bod' + bodtell).slideToggle();
          e.handled = true;
        }
        e.preventDefault();
      });

      $('.m_total .data, .pop_d .day_bar').each(function(e) {
        let percent = $(this).attr('rel')/2;
        $(this).animate({width:percent + '%'}, 500);
      });

      //AJAX click-logging function with redirect on success
      function logclick(cl_href, cl_target, cl_url, cl_data) {
        $.ajax({
          url: cl_url,
          async: true,
          dataType: 'text',
          type: "POST",
          data: cl_data,
          cache: false,
          success: function(response_data) {
            follow_modalpop_link(
              cl_href,
              cl_target
            );
          },
        });
      }

      function follow_modalpop_link(destination, target) {
        if (destination.length > 0 && destination.substring(1,2) !== '#') {
          if (target === '_blank') {
            window.open(destination);
          }
          else {
            location.href = destination;
          }
        }
      }

      ////////////////////////////////////////
    }
  };

}(jQuery, Drupal, drupalSettings, Cookies));
