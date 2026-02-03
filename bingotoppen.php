<?php
require_once 'core/init.php';
include 'core/db_con.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();


$id = "1";
$query="SELECT * FROM rl_bingo WHERE id = '".$id."'";
$result = mysqli_query($conn, $query);


while($get = mysqli_fetch_array($result)) {
        $tall = $get["tall"];
        $dato= $get["dato"];
        $topp = $get["topp"];


        }





?>
<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maxton | Bootstrap 5 Admin Dashboard Template</title>
  <!--favicon-->
  <link rel="icon" href="assets/images/favicon-32x32.png" type="image/png">
  <!-- loader-->
  <link href="assets/css/pace.min.css" rel="stylesheet">
  <script src="assets/js/pace.min.js"></script>

  <!--plugins-->
  <link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/metisMenu.min.css">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/mm-vertical.css">
  <link rel="stylesheet" type="text/css" href="assets/plugins/simplebar/css/simplebar.css">
  <!--bootstrap css-->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <!--main css-->
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="sass/main.css" rel="stylesheet">
  <link href="sass/dark-theme.css" rel="stylesheet">
  <link href="sass/blue-theme.css" rel="stylesheet">
  <link href="sass/semi-dark.css" rel="stylesheet">
  <link href="sass/bordered-theme.css" rel="stylesheet">
  <link href="sass/responsive.css" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
  <style media="screen">
  input[type="date"]::-webkit-calendar-picker-indicator {
  filter: invert(1);
  }
  </style>
</head>

<body>

 <!--start header-->
 <?php include 'nav.php'; ?>

  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content">
      <!--breadcrumb-->
		<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
			<div class="breadcrumb-title pe-3">Bingotoppen</div>
			<div class="ps-3">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-0 p-0">
						<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">oversikt</li>
					</ol>
				</nav>
			</div>

		</div>
		<!--end breadcrumb-->

	  <div class="card rounded-4" >
<?php Template::output('msg'); ?>
	    <div class="card-body">
        <div class="col-12 col-xl-6">
          <div class="card border-top border-3 border-danger rounded-0">
            <div class="card-header py-3 px-4">
              <h5 class="mb-0 text-danger">Endre bingootoppen og antall lykketall.</h5>
            </div>
            <div class="card-body p-4">
              <form action="edit.php?t=bingo" method="POST" enctype="multipart/form-data" >
                 <div class="mb-3 " id="date-format">
<label class="form-label">Dato</label>
<input type="text" class="form-control date-format" id="dato" name="dato" value="<?=$dato?>">
</div>
                   <div class="mb-3">
                       <label for="exampleInputEmail1" class="form-label">Bingo toppen</label>
                       <input type="text" class="form-control" id="topp" name="topp" aria-describedby="emailHelp" placeholder="Bingo toppen" value="<?=$topp?>">
                       <input type="hidden" id="id" name="id" placeholder="Topp summen" required="" value="<?=$id?>" class="form-control">
                   </div>
                   <div class="mb-3">
                       <label for="exampleInputPassword1" id="tall" name="tall" class="form-label">Lykketall</label>
                       <input type="text" class="form-control" id="tall" name="tall" placeholder="Lykketall" value="<?=$tall?>">
                   </div>



                   <button type="submit" class="btn btn-primary">Send</button>
               </form>
            </div>
          </div>
        </div>

		</div>
	  </div>

    </div>
  </main>
  <!--end main wrapper-->


    <!--start overlay-->
    <div class="overlay btn-toggle"></div>
    <!--end overlay-->


     <?php include 'footer.php'; ?>



  <!--bootstrap js-->
  <script src="assets/js/bootstrap.bundle.min.js"></script>

  <!--plugins-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src='https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/no.js'></script>
  <script>



    flatpickr('.date-format', {
        "locale": "no",
        "dateFormat": "d/m",
          "firstDayOfWeek": 1,
    });

  </script>

</body>

</html>
