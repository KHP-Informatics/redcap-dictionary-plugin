<?php

/* Add in a link to the Dictionary plugin to the control centre*/
function redcap_control_center()
{
    print  "<div id='ac_cc_link' style='clear:both;margin:0 -6px 3px;border-top:1px solid #ddd;'>
              <b style='padding:5px'>Dictionaries Plugin</b>
                <div style='padding-top:5px;'></div>
                <span style='padding-left:10px'>
                  <a href='/plugins/dictionaries/'>Dictionaries</a><br/>
                </span>
            </div>";

    // Use JavaScript/jQuery to append our link to the bottom of the left-hand menu
    print  "<script type='text/javascript'>
            $(document).ready(function(){
                // Append link to left-hand menu
                $( 'div#ac_cc_link' ).appendTo( 'div#control_center_menu' );
            });
            </script>";
}



?>
