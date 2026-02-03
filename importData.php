<?php
require_once 'core/init.php';
include_once("core/db.php");

// Require authentication
Auth::requireLogin();

if(isset($_POST["Export"])){

     header('Content-Type: text/csv; charset=utf-8');
     header('Content-Disposition: attachment; filename=Kveldssending-liste.csv');
     $output = fopen("php://output", "w");
     fputcsv($output, array('id', 'dato', 'navn', 'tek'));
     $query = "SELECT * from kveld ORDER BY id ASC";
     $result = mysqli_query($con, $query);
     while($row = mysqli_fetch_assoc($result))
     {
          fputcsv($output, $row);
     }
     fclose($output);
}

if(isset($_POST['import_data'])){
    // validate to check uploaded file is a valid csv file
    $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values',
 'application/octet-stream', 'application/vnd.ms-excel',
'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel',
'application/vnd.msexcel', 'text/plain');
    if(!empty($_FILES['file']['name']) &&
 in_array($_FILES['file']['type'],$file_mimes)){
        if(is_uploaded_file($_FILES['file']['tmp_name'])){
            $csv_file = fopen($_FILES['file']['tmp_name'], 'r');
            //fgetcsv($csv_file);
            // get data records from csv file
            while(($emp_record = fgetcsv($csv_file)) !== FALSE){
                // Check if employee already exists with same email
                $sql_query = "SELECT id FROM kveld
 WHERE id = '".$emp_record[0]."'";
                $resultset = mysqli_query($con, $sql_query) or
 die("database error:". mysqli_error($con));
				// if employee already exist then update details otherwise insert new record
                if(mysqli_num_rows($resultset)) {
					$sql_update =
"UPDATE kveld set dato='".$emp_record[1]."',
navn='".$emp_record[2]."',
tek='".$emp_record[3]."' WHERE id = '".$emp_record[0]."'";
                    mysqli_query($con, $sql_update) or
die("database error:". mysqli_error($con));
                } else{
					$mysql_insert = "INSERT INTO kveld (dato, navn, tek )VALUES('".$emp_record[1]."', '".$emp_record[2]."',
 '".$emp_record[3]."')";
					mysqli_query($con, $mysql_insert) or
die("database error:". mysqli_error($con));
                }
            }
            fclose($csv_file);
            $import_status = '?import_status=success';
        } else {
            $import_status = '?import_status=error';
        }
    } else {
        $import_status = '?import_status=invalid_file';
    }
}
header("Location: kveld.php".$import_status);
?>
