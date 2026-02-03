<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();

$t = $_GET['t'];
header('Content-Type: text/html; charset=UTF-8');

if ($t == "user") {
$id=$_POST['id'];
$name=$_POST['name'];
$email=$_POST['email'];
$tlf=$_POST['tlf'];
$group=$_POST['responsibility'];
include "core/db.php";

$results = $con->query("UPDATE users SET email='$email', tlf='$tlf', name='$name', gruppe='$group' WHERE ID=$id");
if($results){
    Session::flash('success', 'Brukeren '.$name.' er blitt oppdatert.');
    Redirect::to('user.php');
}else{

    print 'Error : ('. $con->errno .') '. $con->error;
}


	mysql_close($con);
	}

  if ($t == "ostatus") {
    $status = $_GET['status'];
    $oid = $_GET['id'];
    $sid = $_GET['sid'];
    $dato = date("d/m/y h:i");

  include "core/db.php";

  //Writes the information to the database
  $results = $con->query("UPDATE rsorders SET status='$status' WHERE id='$oid'") ;
  $results2 = $con->query("INSERT INTO rsorders_hist (order_id, dato, sid, status) VALUES ('$oid', '$dato', '$sid', '$status')") ;

  Session::flash('success', 'ordre nr '.$oid.' har endret staus til '.$status.' ');
    Redirect::to('ecom_ordre.php');
  mysql_close($link);
  }

	if ($t == "bingo") {
$tall=$_POST['tall'];
$topp=$_POST['topp'];
$dato=$_POST['dato'];
$dato_hist = date("d-m-Y");
$id=$_POST['id'];

include "core/db.php";

 //Writes the information to the database
 $results = $con->query("UPDATE rl_bingo SET topp='$topp', dato='$dato', tall='$tall' WHERE id='$id'") ;
 $results2 = $con->query("INSERT INTO bingo_hist (dato_spill, dato, topp, tall) VALUES ('$dato', '$dato_hist', '$topp', '$tall')") ;

Session::flash('success', 'Bingo topp info oppdatert.');
	Redirect::to('bingotoppen.php');
	mysql_close($link);
	}

  if ($t == "bingo_utsalg") {
$navn=$_POST['navn'];
$pid=$_POST['pid'];
$adr_=$_POST['adr_'];
$app_id=$_POST['app_id'];
$usid=$_POST['usid'];

include "core/db.php";

 //Writes the information to the database
 $results = $con->query("UPDATE bingo_utsalg SET pid='$pid', navn='$navn', app_id='$app_id', adr_='$adr_' WHERE usid='$usid'") ;

Session::flash('success', 'Bingo Utsalgsted info oppdatert.');
	Redirect::to('bingo_utsalg.php');
	mysql_close($link);
	}

  if ($t == "varsel") {
$id=$_POST['aid'];

include "core/db.php";

 //Writes the information to the database
 $results = $con->query("UPDATE us_notifications SET read_at= NOW() WHERE id='$id'") ;

	mysql_close($link);
	}

	if ($t == "medarb") {
$id=$_POST['id'];
$navn=$_POST['navn'];
$stilling=$_POST['stilling'];
$protek=$_POST['protek'];
$prog=$_POST['prog'];

include "core/db.php";

 //Writes the information to the database
 $results = $con->query("UPDATE medarb SET navn='$navn', stilling='$stilling', protek='$protek', program='$prog' WHERE id='$id'") ;

Session::flash('success', ' Medarbeider '.$navn.' er blitt oppdatert.');
	Redirect::to('se_medarb.php?id='.$id.'');
	mysql_close($link);
	}

  if ($t == "program_oversikt") {
$id=$_POST['id'];
$kl=$_POST['kl'];
$prog=$_POST['prog'];

include "core/db.php";

 //Writes the information to the database
 $results = $con->query("UPDATE program_oversikt SET kl='$kl', program='$prog' WHERE id='$id'") ;

Session::flash('success', ' '.$prog.' er blitt oppdatert.');
	Redirect::to('program_oversikt.php');
	mysql_close($link);
	}

	if ($t == "mail") {
$id=$_POST['id'];
$navn=$_POST['navn'];
$txt=$_POST['txt'];

include "core/db.php";

 //Writes the information to the database
 mysql_query("UPDATE epost SET navn='$navn', tekst='$txt' WHERE id='$id'") ;

Session::flash('success', ' teksten '.$navn.' er blitt oppdatert.');
	Redirect::to('mailtxt.php');
	mysql_close($link);
	}



	if ($t == "show_produkt") {
$pid=$_POST['pid'];
$show=$_POST['show'];

include "core/db.php";

 //Writes the information to the database
 $results = $con->query("UPDATE produkt_pris SET show='$show' WHERE id='$pid'");


Session::flash('success', 'produktet  er blitt oppdatert.');
	Redirect::to('produkter.php');

	}

	if ($t == "produkt") {
$id=$_POST['id'];
$produkt=$_POST['produkt'];
$artnr=$_POST['artnr'];
$inn=$_POST['inn'];
$mva=$_POST['mva'];
$nyutpris=$_POST['nyutpris'];
$dato=$_POST['dato'];
$size=$_POST['size'];
$vnyutpris=$_POST['vnyutpris'];
$img=$_POST['img'];

$results = $link->query("UPDATE produkt_pris SET produkt='$produkt', artnr='$artnr', inn='$inn', mva='$mva', nyutpris='$nyutpris', dato='$dato', size='$size', vnyutpris='$vnyutpris', img='$img'	 WHERE id='$id'");

Session::flash('success', 'produktet '.$navn.' er blitt oppdatert.');
	Redirect::to('produkter.php');
	mysql_close($link);
	}

	if ($t == "kunde") {
$pid=$_POST['id'];
$name=$_POST['name'];
$email=$_POST['email'];
$tlf=$_POST['tlf'];
$username=$_POST['username'];

include "core/db.php";

 //Writes the information to the database

 $results = $con->query("UPDATE kunde SET name='$name', email='$email', tlf='$tlf' WHERE id=$pid");
Session::flash('success', 'kunde '.$name.' er blitt oppdatert.');
	Redirect::to('kunde.php?id='.$pid.'');
	mysql_close($con);
	}

	if ($t == "pdf") {
$id=$_POST['id'];
$name=$_POST['navn'];
$pdf=$_POST['pdf'];
$img=$_POST['img'];

include "core/db.php";

 //Writes the information to the database
 mysql_query("UPDATE pdf SET name='$name', pdf='$pdf', img='$img' WHERE id='$id'") ;

Session::flash('success', 'pdf filen '.$name.' er blitt oppdatert.');
	Redirect::to('pdf.php');
	mysql_close($con);
	}


	if ($t == "ramme") {
$pid=$_POST['id'];
$navn=$_POST['navn'];
$artnr=$_POST['artnr'];
$pgr=$_POST['pgr'];

include "core/db.php";

 //Writes the information to the database

 $results = $con->query("UPDATE ramme SET navn='$navn', artnr='$artnr', pris_gr='$pgr' WHERE id=$pid");
Session::flash('success', 'Ramme '.$navn.' er blitt oppdatert.');
	Redirect::to('rammeliste.php');
	mysql_close($con);
	}
?>
