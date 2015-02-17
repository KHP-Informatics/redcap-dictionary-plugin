<?php
/* A class to represent a sqlite based dictionary */
class Dictionary extends SQLite3
{

  private $file;        // Location (full path) of the sqlite file, required

  public $display_name; // Display name for this dictionary, defaults to filename
  public $info;         // Additional information about this dictionary, optional. 

  /* Construct Object */
  function __construct($file, $display_name='', $info='' ){

    // DB setup
    if(file_exists($file)){        // connect to DB if it exists
      parent::__construct($file);
     } else {
       parent::__construct($file); // create, connect and initialise if it doesn't
       $create_q = 'CREATE TABLE dictionary (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, term TEXT NOT NULL);';
       $result = $this->query($create_q);
       if(!$result){
         throw(new Exception($this->lastErrorMsg()));
       }
       $index_q = 'CREATE INDEX term_i ON dictionary(term)';
       $result = $this->query($index_q);
       if(!$result){
         throw(new Exception($this->lastErrorMsg()));
       }
     }

    // other setup
    $this->file = $file; 
    if($display_name){
       $this->display_name = $display_name;
    } else { 
      $this->display_name = pathinfo($file, PATHINFO_BASENAME); 
    }
  }

  /* readonly get/set definitions for $file */
  public function getFile(){
    return $this->file;
  }
  public function setFile(){
    throw new Exception('Attempt to set read-only field File');
  }

  /* append the array of terms to the dictionary  */
  public function append_terms($terms){
     $stmt = $this->prepare('INSERT INTO dictionary (term) VALUES (:term)');
     if(!$stmt){
      throw(new Exception($this->lastErrorMsg()));
     }
     foreach ($terms as $term){
       $term = rtrim($term);
       $stmt->bindValue(':term', $term, SQLITE3_TEXT);
       $result = $stmt->execute();
       if(!$result){
         throw(new Exception($this->lastErrorMsg()));
       }
     }
  }
  
  /* Tidy up */
  function __destruct(){
    $this->close();
  }

}
?>

