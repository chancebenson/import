<?php
// a sinple function to create a database connection
function db_connect() {
    $conn = mysql_connect("localhost", "whmcs_import", "import");
    mysql_select_db("whmcs_import", $conn);
}
// this function will open the chosen directory and returns and array with all filenames
function select_files($dir) {
    if (is_dir($dir)) {
        if ($handle = opendir($dir)) {
            $files = array();
            while (false !== ($file = readdir($handle))) {
                if (is_file($dir.$file) && $file != basename($_SERVER['PHP_SELF'])) $files[] = $file;
            }
            closedir($handle);
            if (is_array($files)) sort($files);
            return $files;
        }
    }
} 
// this function inserts the filename and the modification date of the current file
function insert_record($file) {
    $sql = sprintf("INSERT INTO icons SET file = '%s', category = 'Application'", $file);
    if (mysql_query($sql)) {
        return true;
    } else {
        return false;
    }
}
// establish database connection
db_connect();
// enter the table structure if not exists
/*mysql_query("
CREATE TABLE IF NOT EXISTS icons (
  id bigint(20) unsigned NOT NULL auto_increment,
  filename varchar(255) NOT NULL default '',
  lastdate datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  FULLTEXT KEY domain (filename)
) TYPE=MyISAM;");
*/

// creating the current path 
$path = dirname(__FILE__);
// the trailing slash for windows or linux
$path .= (substr($path, 0, 1) == "/") ? "/" : "\\";
// get the filenames from the directory
$file_array = select_files($path);
// creating some controle variables and arrays
$num_files = count($file_array);
$success = 0;
$error_array = array();
// if the file array is not empty the loop will start
if ($num_files > 0) {
    foreach ($file_array as $val) {
        $fdate = date("Y-m-d", filectime($path.$val));
        if (insert_record($val, $fdate)) {
            $success++;
        } else {
            $error_array[] = $val;
        }   
    }
    echo "Copied ".$success." van ".$num_files." files...";
    if (count($error_array) > 0) echo "\n\n<blockquote>\n".print_r($error_array)."\n</blockquote>";
} else {
    echo "No files or error while opening directory";
}
?>