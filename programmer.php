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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
<?php Template::output('msg'); ?>
          </div>
          <div class="col-auto flex-grow-1 overflow-auto">
            <div class="btn-group position-static">
<?php Template::output('msg'); ?>


            </div>
          </div>
          <div class="col-auto">
            <div class="d-flex align-items-center gap-2 justify-content-lg-end">
               <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#CreateProject"><i class="bi bi-plus-lg me-2"></i>Legg til nytt program</button>
            </div>
          </div>
        </div><!--end row-->

        <div class="card rounded-4 rounded-4">
          <div class="card-body">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-4 g-3">
              <?php $data = DB::getInstance()->query('SELECT * FROM program'); ?>
              <?php  ?>
              <?php if ($data->count()) : ?>
                <?php
                foreach ($data->results() as $result) {

                  //$user = new User();


                  echo'
              <div class="col">
                <div class="card rounded-4 mb-0 border">
                  <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                      <img src="../img/prg/'. $result->img .'" width="35" alt=""><h4 class="mb-0 fw-light">'. $result->navn .'</h4>
                      <div class="dropdown">
                        <a href="javascript:;" class="dropdown-toggle-nocaret options dropdown-toggle"
                          data-bs-toggle="dropdown">
                          <span class="material-icons-outlined fs-5">more_vert</span>
                        </a>
                        <ul class="dropdown-menu">
                          <li><a class="dropdown-item" href="edit_prg.php?id='.$result->id.'">Endre</a></li>
                          <li><a class="dropdown-item" href="javascript:;" data-bs-toggle="modal" data-bs-target="#delModal'. $result->id .'">Slett</a></li>

                        </ul>
                      </div>
                    </div>
                    <div class="mt-4">
                      <h4 class="mb-0 fw-light">KL: '. $result->tid .'</h4>

                      <p class="mb-0" >
                      <span style="float: left;">Dag: '. $result->dag .'</span>
    <span style="float: right;"><i class="fa-solid fa-eye" data-bs-toggle="modal" data-bs-target="#semodal'. $result->id .'"></i></span>

                    </div>

                  </div>
                </div>
              </div>
              <!-- Modal -->
<div class="modal fade" id="delModal'. $result->id .'" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<div class="alert alert-danger" role="alert">
<h4 class="alert-heading">Wow der!</h4>
<p>Du er i ferd med og slette '. $result->navn .' fra våre programmer</p>
<hr>
<p class="mb-0">Er du sikker du vil dette ? Det kan ikke tilbakeføres</p>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt</button>
<a href="delete_prg.php?id='. $result->id .'"> <button type="button" class="btn btn-danger">Ja slett programmet</button></a>
</div>
</div>
</div>
</div>
<!-- /Modal -->
<!-- Modal -->
<div class="modal fade" id="semodal'. $result->id .'" tabindex="-1" aria-labelledby="semodal'. $result->id .'" aria-hidden="true">
  <div class="modal-dialog  modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="exampleModalLabel">Forhåndasvis program</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div class="card">
        <div class="card-body">
          <div class="row row-cols-1 row-cols-lg-1 g-3">
            <div class="col">
              <div class="card shadow-none border mb-0">
                <div class="card-body">
                  <div class="text-center">
                    <img src="../img/prg/'. $result->img .'" width="100" height="100"
                      class="rounded-circle raised bg-white" alt="">
                  </div>
                  <div class="text-center mt-4">
                    <h5 class="mb-2">'. $result->navn .'</h5>
                    <p class="mb-0">KL: '. $result->tid .' <br>KL: '. $result->dag .'</p>
                  </div>
                  <hr>
                  <div class="d-flex align-items-center justify-content-around mt-5">
                    <div class="d-flex flex-column gap-2">
                      <h5 class="mb-0">Beskrivelse</h5>
                      <p class="mb-0">'. $result->info .'</p>
                    </div>

                  </div>


                </div>
              </div>
            </div>


          </div><!--end row-->
        </div>
      </div>
<!--end card-->

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<!-- /Modal -->
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
  <!-- Modal -->
  <div class="modal fade" id="CreateProject" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Nytt programm</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="nyttprg.php" method="post" enctype="multipart/form-data">
          <div class="mb-3">
<label for="formFile" class="form-label">Velg bilde for programmet</label>
<input class="form-control" type="file" name="file">
</div>
          <div class="form mb-3">
              <label for="floatingInput">Navn på programmet</label>
          <input type="text" class="form-control" id="navn" name="navn" placeholder="Program navn">

          </div>
          <div class="form mb-3">
            <label for="floatingInput">Klokkeslett for programmet</label>
          <input type="text" class="form-control" id="tid" name="tid" placeholder="Klokkeslett for programmet">

          </div>
          <div class="mb-4">
            <label for="multiple-select-field" class="form-label">Velg dager denne skal gå</label>
            <select class="form-select" id="multiple-select-field" name="multiple-select-field" data-placeholder="Velg dag(er)" multiple>
              <option value="">Velg dager</option>
              <option value="Mandag">Mandag</option>
              <option value="Tirsdag">Tirsdag</option>
              <option value="Onsdag">Onsdag</option>
              <option value="Torsdag">Torsdag</option>
              <option value="Fredag">Fredag</option>
              <option value="Lørdag">Lørdag</option>
              <option value="Søndag">Søndag</option>
            </select>
          </div>

          <div class="form">
            <label for="floatingTextarea">Kort info om programmet</label>
<textarea class="form-control" placeholder="" id="info" name="info"></textarea>

</div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt</button>
          <button type="submit" class="btn btn-primary" name="submitny">Lagre nytt programm!</button>
        </form>
        </div>
      </div>
    </div>
  </div>
<!-- Modal -->

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
  	<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.2.13/dist/semantic.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
      <script src="assets/plugins/select2/js/select2-custom.js"></script>
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
