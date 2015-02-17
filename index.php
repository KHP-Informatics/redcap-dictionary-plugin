<?php
/*
* A ControlCentre page to allow administrators to manage dictionaries for the auto-complete plugin
*
*/


//load up the plugin stuff
require_once "../../redcap_connect.php";

// load the Dictionary class
require_once "Dictionary.php";

// load the ControlCentre header
include(APP_PATH_DOCROOT . 'ControlCenter/header.php');


// Your HTML page content goes here
?>
<h3 style="color:#800000;">
	Dictionaries
</h3>

<?php
  

  /* Fetch data_dictionary location */
  $query = "select * from redcap_config where field_name = 'dictionary_directory'";
  $result = mysqli_query($conn, $query);
  if($result->num_rows == 0){
    // default to the plugin directory
    $dict_dir = APP_PATH_DOCROOT.'../plugins/dictionaries';
    $query = "insert into redcap_config (field_name, value) values ('dictionary_directory', '$dict_dir')";
    $result = mysqli_query($conn, $query);
  } else {
    $query = 'select value from redcap_config where field_name="dictionary_directory"';
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $dict_dir = $row['value'];
  }
 
  /* process a change to the dictionary location */
  if(isset($_POST['config'])){
    $dictionary_dir = $_POST['dictionary_dir'];
    if(file_exists($dictionary_dir) && is_writable($dictionary_dir)){
      $query = 'update redcap_config set value=? where field_name="dictionary_directory"';
      $stmt = $conn->prepare($query);
      $stmt->bind_param("s", $dictionary_dir) or trigger_error($conn->error);
      $result = $stmt->execute() or trigger_error($stmt->error);
      if ($result){
        $dict_dir = $dictionary_dir;
        echo("Dictionary directory successfully updated to $dictionary_dir");
      }else{
        throw new Exception("Failed to update dictionary directory: $result->error");
      }
    }else{
      echo("Can't write to specified path");
    }
  }


  /* Fetch existing dictionaries */
  $existing_dicts = array_map(function($a) { return pathinfo($a, PATHINFO_FILENAME); }, glob("$dict_dir/*.sqlite"));


  // process an uploaded text file
  if(isset($_FILES['dictionary_file'])){
    $name = $_POST['dictionary_name'];
    if(in_array($name, $existing_dicts)){
      throw new Exception("Dictionary name already in use"); 
    }
    if(! preg_match('/^\w+$/',$name) ){
      throw new Exception('Attempt to create a dictionary with an invalid name. Please use only letters, numbers or underscores');
    }
    
    if (! ($_FILES['dictionary_file']['error'] == UPLOAD_ERR_OK
           && is_uploaded_file($_FILES['dictionary_file']['tmp_name']))){
      throw new Exception('Upload error');
    }              // check the upload went ok (includes size under that defined in php.ini)

    $finfo = new finfo();
    $fileMimeType = $finfo->file($_FILES['dictionary_file']['tmp_name'], FILEINFO_MIME_TYPE);       
    if(! preg_match(  "/^text\/plain/", $fileMimeType )){
      throw new Exception("File not a text file. Upload ignored");
    }
            
    $terms = file($_FILES['dictionary_file']['tmp_name']);
    $filename = $dict_dir.'/'.$name.'.sqlite'; 
    $dict = new Dictionary( $filename );
    $dict->append_terms($terms);
    echo "Dictionary $name successfully created";
  }

?>

<!-- Dictionary Location Configuration Form -->
<div>
  <hr/>
  <h4>Configuration</h4>
  <p>WARNING: This is just a configuration value. If you change it you will need to manually move any existing dictionaries to the new location if you want them to be found</p>
  <form id="dictionaries_config_form" name="dictionaries_config_form" method="post" 
        action="<?php echo APP_PATH_WEBROOT_FULL;?>plugins/dictionaries/"
        style="padding:0px 10px 20px 10px;">
    <p><label for="dictionary_dir">Dictionary Directory</label>
    <input style="width:50em" type="text" id="dictionary_dir" name="dictionary_dir" value="<?php echo $dict_dir?>" required/></p>    
    <button name="config" type="submit" value="config" >Submit</button>
  </form>
</div>


<!-- Upload Form -->
<div>
  <hr/>
  <h4>Upload a new completion dictionary</h4>
  <p>If your dictionary file is too large to upload, please contact your Redcap administrator</a>

  <form id="newAutoCompleteDictionary" enctype="multipart/form-data" method="post" 
        action="<?php echo APP_PATH_WEBROOT_FULL;?>plugins/dictionaries/"
        style="padding:0px 10px 20px 10px;">

    <p><label for="dictionary_name">Dictionary Name</label>
    <input type="text" id="dictionary_name" name="dictionary_name" required /></p>
    <p><input type="file" id="dictionary_file" name="dictionary_file" required /></p>
    <button type="submit" name="upload" value="upload">Submit</button>
  </form>
</div>





<!-- Delete Form -->
<div>
  <hr/>
  <h4>Delete existing dictionaries</h4>
  
  <p>If you want to replace an existing dictionary, just delete it and create a new one with an identical name</p>
 
  <form id="deleteDictionary" method="post" 
        action="<?php echo APP_PATH_WEBROOT_FULL;?>plugins/dictionaries/"
        style="padding:0px 10px 20px 10px;">

     <?php
       foreach ($existing_dicts as $d){
         echo("<input type='checkbox' name='deleteme' value='$d' id='$d' /><label for='$d'>$d</label><br/>");
       }
     ?>
     <br/>
    <button type="submit" name="delete" id="delete" value="delete">Submit</button>
  </form>

</div>


<?php

include('../../../redcap_v6.2.0/ControlCenter/footer.php');

