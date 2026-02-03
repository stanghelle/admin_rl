<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();
?>
<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RLAdmin- Bingotopp historikk</title>
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
			<div class="breadcrumb-title pe-3">Bingo</div>
			<div class="ps-3">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-0 p-0">
						<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">Utslagssteder</li>
					</ol>
				</nav>
			</div>

		</div>
		<!--end breadcrumb-->
    <div class="row g-3">
      <div class="col-auto">
        <div class="position-relative">
          <input class="form-control px-5" type="search" placeholder="Søk etter utslagsted">
          <span class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50 fs-5">search</span>
        </div>
      </div>
      <div class="col-auto flex-grow-1 overflow-auto">
        <div class="btn-group position-static">
          <div class="btn-group position-static">
            <button type="button" class="btn btn-filter dropdown-toggle px-4" data-bs-toggle="dropdown" aria-expanded="false">
              Plass
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="javascript:;">Action</a></li>
              <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
            </ul>
          </div>

          <div class="btn-group position-static">
            <button type="button" class="btn btn-filter dropdown-toggle px-4" data-bs-toggle="dropdown" aria-expanded="false">
              Flere filter
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="javascript:;">Action</a></li>
              <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-auto">
        <div class="d-flex align-items-center gap-2 justify-content-lg-end">
           <button class="btn btn-filter px-4"><i class="bi bi-box-arrow-right me-2"></i>Export</button>
           <button class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Legg til Utsalgsted</button>
        </div>
      </div>
    </div><!--end row-->
	  <div class="card rounded-4" >

	    <div class="card-body">

        <div class="customer-table">
          <div class="table-responsive white-space-nowrap">
             <table class="table align-middle">
              <thead class="table-light">
                <tr>

                  <th>Navn</th>
                  <th>Plass</th>
                  <th>profil app id</th>


                </tr>
               </thead>
               <tbody>
                <?php $data = DB::getInstance()->query('SELECT * FROM bingo_utsalg INNER JOIN bingo_plass ON bingo_utsalg.pid = bingo_plass.bpid'); ?>
                 <?php  ?>
                 <?php if ($data->count()) : ?>


                     <?php
                     foreach ($data->results() as $result) {

                       //$user = new User();

                         echo '
                 <tr>

                   <td>
                    <a class="d-flex align-items-center gap-3" href="javascript:;">
                      <div class="customer-pic">
                        <img src="assets/images/avatars/01.png" class="rounded-circle" width="40" height="40" alt="">
                      </div>
                      <p class="mb-0 customer-name fw-bold">'.$result->navn.'</p>
                    </a>
                   </td>

                   <td>'.$result->bpnavn.'</td>
                   <td> <a href="bingoappuser.php=id='.$result->app_id.'"> '.$result->app_id.'</a></td>


                 </tr>

                 </tr>
                 ';
               }
               ?>
               </table>
               <?php else : ?>
               <h4 class="text-muted text-center">Det finnes ingen Nyheter listet.</h4>
               <?php endif; ?>
               </tbody>
             </table>
          </div>
        </div>
        <!-- utslagsted slutt-->











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
  <script src="assets/js/jquery.min.js"></script>
  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>


</body>

</html>
