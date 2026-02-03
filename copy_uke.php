<?php
require_once 'core/init.php';
include 'core/database_connection.php';

// Require authentication
Auth::requireLogin();





         if ($connect->query("delete FROM program_oversikt")) {

         }

         if ($connect->query("INSERT INTO program_oversikt SELECT * FROM prg_pdf")) {

         }


         Session::flash('success', ' Programoversikten er blitt Publisert.');
           Redirect::to('program_oversikt.php');



         	mysql_close($con);
      ?>
