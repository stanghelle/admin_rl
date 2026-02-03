<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include the database configuration file
require_once 'core/init.php';
include "core/db.php";

// Require authentication
Auth::requireLogin();

$statusMsg = '';
// File upload directory
$targetDir = "../img/prg/";

if(isset($_POST["submitny"])){
    if(!empty($_FILES["file"]["name"])){
        $fileName = basename($_FILES["file"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
     $navn=$_POST['navn'];
     $dag=$_POST['multiple-select-field'];
     $tid=$_POST['tid'];
     $info=$_POST['info'];

        // Allow certain file formats
        $allowTypes = array('jpg','png','jpeg');
        if(in_array($fileType, $allowTypes)){
            // Upload file to server
            if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)){
                // Insert image file name into database
                $insert = $con->query("INSERT INTO program (navn, dag, tid, info, img) VALUES ('$navn','$dag','$tid','$info','$fileName')");
                if($insert){
                  Session::flash('success', 'Programmet er blitt lagt inn.');
                  Redirect::to('programmer.php');
                }else{
                  Session::flash('error', 'Ein feil oppstod ved opplasting : feilkode:nyprg_insert.');
                  Redirect::to('programmer.php');
                }
            }else{
              Session::flash('error', 'Ein feil oppstod ved opplasting : feilkode:nyprg2 upload.');
              Redirect::to('programmer.php');
            }
        }else{
          Session::flash('info', 'Du kan du kun bruke JPG JPEG og PNG : infokode:nyprg_filetype.');
          Redirect::to('programmer.php');
        }
    }else{
      Session::flash('info', 'Velg et bilde for og bruke : infokode:nyprg2.');
      Redirect::to('programmer.php');
    }
}

// Display status message
echo $statusMsg;

?>
