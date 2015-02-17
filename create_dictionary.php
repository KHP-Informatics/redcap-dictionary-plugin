<?php

// Call the REDCap Connect file in the main "redcap" directory
require_once "../../redcap_connect.php";

// Bail if we don't have a project ID
if(empty($project_id)){
  // no idea how you're supposed to do this gracefully in Redcap.
}

// header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';



//START HTML CONTENT 
?>

<h3 style="color:#800000;">Auto-Complete Fields</h3>


<?php 
// END HTML CONTENT

// Footer
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
