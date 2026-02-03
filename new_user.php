<?php
require_once 'core/init.php';
include 'core/db_con.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();
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
  <style media="screen">
  input[type="date"]::-webkit-calendar-picker-indicator {
  filter: invert(1);
  }
  </style>
  <script>


  var keylist="abcdefghijklmnopqrstuvwxyz123456789"
  var temp=''

  function generatepass(plength){
  temp=''
  for (i=0;i<plength;i++)
  temp+=keylist.charAt(Math.floor(Math.random()*keylist.length))
  return temp
  }

  function populateform(enterlength){
  document.myForm.password.value=generatepass(enterlength)
  }

  $('.inputmask').inputmask({
    mask: '99 99 99 99'
  })
  </script>
</head>

<body>

 <!--start header-->
 <?php include 'nav.php'; ?>

  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content">
      <!--breadcrumb-->
		<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
			<div class="breadcrumb-title pe-3">Bruker</div>
			<div class="ps-3">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-0 p-0">
						<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
						</li>
						<li class="breadcrumb-item " aria-current="page">Bruker</li><li class="breadcrumb-item active" aria-current="page">Ny Bruker</li>
					</ol>
				</nav>
			</div>

		</div>
		<!--end breadcrumb-->

	  <div class="card rounded-4" >

	    <div class="card-body">
        <div class="col-12 col-xl-6">
          <div class="card border-top border-3 border-danger rounded-0">
            <div class="card-header py-3 px-4">
              <h5 class="mb-0 text-info">Legg til ny Bruker.</h5>
            </div>
            <div class="card-body p-4">
              <form class="row g-3" action="add_user_post.php" method="post" name="myForm" id="myForm">


                <div class="col-md-6">
                  <label class="form-label">Navn</label>
                  <input type="text" class="form-control rounded-0" placeholder="Navn på bruker" value="" id="name" name="name">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Stilling</label>
                  <input type="text" class="form-control rounded-0" placeholder="stilling" value="" id="stilling" name="stilling">
                </div>

                <div class="col-md-6">
                  <label class="form-label">TLF</label>
                  <input type="text" class="form-control rounded-0" id="tlf" name="tlf">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Passord</label>
                  <input type="text" class="form-control rounded-0" id="password" name="password">
                  <input type="button" value="lag passord" onClick="populateform(this.form.thelength.value)">
                      <input type="hidden" name="thelength" size=3 value="6"><br clear="all"><br clear="all">
                </div>


                <div class="col-md-12">
                  <div class="d-md-flex d-grid align-items-center gap-3">
                    <button type="submit" class="btn btn-grd-danger px-4 rounded-0">Opprett bruker</button>
                    <button type="button" class="btn btn-grd-info px-4 rounded-0">Nullstill</button>
                  </div>
                </div>
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

     <!-- form modal-->
     <div class="modal fade" id="FormModal">
       <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
           <div class="modal-header border-bottom-0 py-2 bg-grd-info">
             <h5 class="modal-title">Endre Kveldssending oppslag</h5>
             <a href="javascript:;" class="primaery-menu-close" data-bs-dismiss="modal">
               <i class="material-icons-outlined">close</i>
             </a>
           </div>
           <div class="modal-body">
             <div class="form-body">
               <form class="row g-3">
                 <div class="col-md-6">
                   <label for="input1" class="form-label">Dato</label>
                   <input type="date" class="form-control" id="dato" name="dato" placeholder="Dato">
                 </div>
                 <div class="col-md-6">
                   <label for="input2" class="form-label">Programledere</label>
                   <input type="text" class="form-control" id="prgl" name="prgl" placeholder="Programledere">
                 </div>
                 <div class="col-md-12">
                   <label for="input3" class="form-label">Tekniker</label>
                   <input type="text" class="form-control" id="tek" name="tek" placeholder="Tekniker">
                 </div>




                 <div class="col-md-12">
                   <div class="d-md-flex d-grid align-items-center gap-3">
                     <button type="button" class="btn btn-grd-danger px-4">Lagre</button>
                     <button type="button" class="btn btn-grd-info px-4">Reset</button>
                   </div>
                 </div>
               </form>
             </div>
           </div>
         </div>
       </div>
     </div>
     <!--/FormModal-->

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
