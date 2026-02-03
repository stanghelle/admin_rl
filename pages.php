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
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <!--main css-->
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="sass/main.css" rel="stylesheet">
  <link href="sass/dark-theme.css" rel="stylesheet">
  <link href="sass/blue-theme.css" rel="stylesheet">
  <link href="sass/semi-dark.css" rel="stylesheet">
  <link href="sass/bordered-theme.css" rel="stylesheet">
  <link href="sass/responsive.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.min.css">
  <link rel='stylesheet' href='https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.css'>
</head>

<body>

 <?php include 'nav.php'; ?>


  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content">
      <!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Sider</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Oversikt</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">

					</div>
				</div>
				<!--end breadcrumb-->



        <div class="row g-3">
          <div class="col-auto">

          </div>
          <div class="col-auto flex-grow-1 overflow-auto">
            <div class="btn-group position-static">



            </div>
          </div>
          <div class="col-auto">
            <div class="d-flex align-items-center gap-2 justify-content-lg-end">
               <button class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Legg til Medarbeider</button>
            </div>
          </div>
        </div><!--end row-->

        <div class="card">
          <div class="card-body">
            <div class="row row-cols-2 row-cols-lg-6 g-6">
              <?php $data = DB::getInstance()->query('SELECT * FROM pages'); ?>
              <?php  ?>
              <?php if ($data->count()) : ?>
                <?php
                foreach ($data->results() as $result) {

                  //$user = new User();
                  $user->find($result->last_edit_by);

                  echo'
              <div class="col">
                <div class="card shadow-none bg-grd-voilet mb-0" style="height: 120px;">
                  <div class="card-body">
                    <h5 class="mb-0 text-white">'. $result->title .'</h5>
                      <a href="edit_page.php?id='.$result->id.'" ><button type="button" class="btn btn-info btn-sm">Endre side</button></a><br>
Sist endret: '. date('d.m.Y H:i:s', strtotime($result->last_edit_datetime)) . ' <br>av '. $user->data()->name .'
                  </div>
                </div>
              </div>

              ';
              }
              ?>
              </table>
              <?php else : ?>
              <h4 class="text-muted text-center">Det finnes ingen sider listet.</h4>
              <?php endif; ?>

            </div><!--end row-->
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.all.min.js"></script>
  <script src='https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.min.js'></script>
<script type="text/javascript">
// Unlink folder
function showSwalAlert(favoritt){

  let uid = favoritt.getAttribute("data-uid");
  swal({
      title: "Er du sikker du vil slette denne medarbeideren?",
      text: "Er du sikker?",
      type: "warning",
      showCancelButton: true,
      cancelButtonText: "Avbryt",
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Slett Medarbeider",
      closeOnConfirm: false
    },
    function() {
      $.ajax({
          type: "post",
          url: "del_medarb.php",
          data: {
            uid: uid,
          },
          success: function(data) {

          }
        })
        .done(function(data) {
          swal("Deleted!", "Data successfully Deleted!", "success");
          $( "#myList" ).load(window.location.href + " #myList" );
        })
        .error(function(data) {
          swal("Oops", "We couldn't connect to the server!", "error");
        });
    }
  );
 }
// slutt unlink folder

</script>

</body>

</html>
