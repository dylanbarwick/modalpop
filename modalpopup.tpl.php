<?php
  global $base_url;
  print '<div class="modalpop-content-container">';
  if (substr($node->title, 0, 1) != '!') {
    print '<h2>' . $node->title . "</h2>\n";
  }
  
  print $node->body['und'][0]['value'];
  print "</div>\n";//end of .modalpop-content-container
  //buttons...
  print '<div class="modalpop-buttons-container">' . "\n";
  for ($i = 1; $i <= 3; $i++) {
    if (isset($node->{'pop_butt' . $i . '_label'}['und'][0]['value'])) {
      
      $label = $node->{'pop_butt' . $i . '_label'}['und'][0]['value'];
      
      if (isset($node->{'pop_butt' . $i . '_link'}['und'][0]['value'])) {
        $link = $node->{'pop_butt' . $i . '_link'}['und'][0]['value'];
      }
      else {
        $link = '';
        $link_attributes['fragment'] = 'x';
      }
      
      if (isset($node->{'pop_butt' . $i . '_expiry'}['und'][0]['value'])) {
        //if it's a date (YYYY-MM-DD) then convert it into the number of days between now and it
        if(strpos($node->{'pop_butt' . $i . '_expiry'}['und'][0]['value'], '-')){
          $expiry = ceil((strtotime($node->{'pop_butt' . $i . '_expiry'}['und'][0]['value']) - time())/(60*60*24));
        }else{
          $expiry = $node->{'pop_butt' . $i . '_expiry'}['und'][0]['value'];
        }
      }
      else {
        $expiry = variable_get('modalpop_cookie_expiry', 30);
      }
      $link_attributes['attributes']['rel'] = $expiry;
      $link_attributes['attributes']['alt'] = $i;
      if (substr($link, 0, 4) == 'http') {
        $link_attributes['attributes']['target'] = '_blank';
      }
      else {
        //substr($link, 0, 1) != '/' ? $slash = '/' : $slash = '';
        //$link = $base_url . $slash . $link;
      }
      //print_r($link_attributes);
      $butt_text = l($label, $link, $link_attributes);
      print '<div class="modalpop-button" id="popbutt' . $i . '">' . $butt_text . "</div>\n\n";
      unset($link_attributes);
    }
  }
  print "</div>\n";//end of .modalpop-buttons-container
?>
