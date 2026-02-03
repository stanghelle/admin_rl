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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

 <?php include 'nav.php'; ?>


  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content">
      <!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Medarbeider</div>
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
            <div class="position-relative">

            </div>
          </div>
          <div class="col-auto flex-grow-1 overflow-auto">
            <div class="btn-group position-static">



            </div>
          </div>
          <div class="col-auto">
            <div class="d-flex align-items-center gap-2 justify-content-lg-end">

               <a href="new_appuser.php"> <button class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Ny bruker</button></a>
            </div>
          </div>
        </div><!--end row-->

        <div class="card mt-4" id="myList">
          <div class="card-body">
            <div class="customer-table">
              <div class="table-responsive white-space-nowrap">
                <table class="table align-middle">
                 <thead class="table-light">
                   <tr>
                     <th>
                       <input class="form-check-input" type="checkbox">
                     </th>
                     <th>Navn</th>
                     <th>brukernavn</th>
                     <th>Mobil</th>
                     <th>Epost</th>
                     <th>Sist innlogget</th>
                     <th>Valg</th>
                   </tr>
                  </thead>
                  <tbody>
                    <?php


                 $query = "SELECT * FROM `us_users`;";

// FETCHING DATA FROM DATABASE
$result = $conn->query($query);

if ($result->num_rows > 0)
{
 // OUTPUT DATA OF EACH ROW
 while($row = $result->fetch_assoc())
 {
     echo '
                    <tr>
                      <td>
                        <input class="form-check-input" type="checkbox">
                      </td>
                      <td>
                       <a class="d-flex align-items-center gap-3" href="se_buser.php?id='.$row["id"].'">
                         <div class="customer-pic">
                           <img src="assets/images/avatars/01.png" class="rounded-circle" width="40" height="40" alt="">
                         </div>
                         <p class="mb-0 customer-name fw-bold">'.$row["name"].'</p>
                       </a>
                      </td>
                      <td>
                         '.$row["username"].'
                      </td>
                      <td>
                         '.$row["phone"].'
                      </td>
                      <td>'.$row["email"].'</td>
                      <td>'.$row["last_login"].'</td>


                      <td><a href="se_buser.php?id='.$row["id"].'" class="action-icon"> <i class="fa-regular fa-eye"></i></a>
                      <a href="javascript:void(0);" class="action-icon" data-bs-toggle="modal" data-bs-target="#slettModal'.$row["id"].'"> <i class="fa-solid fa-trash"></i></a></td>
                    </tr>

                    <!-- Modal slett nyhet -->
                    <div class="modal fade" id="slettModal'.$row["id"].'" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">slette bruker til '.$row["name"].'</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <div class="card">
                    <div class="card-header">
                    Slette Bruker '.$row["name"].' ?<br>

                    </div>
                    <div class="card-body">
                    </div>
                    </div>
                    <div class="alert alert-danger" role="alert">
                      <h4 class="alert-heading">Advarsel ! </h4>
                      <p>Er du sikker du vil slette denne Brukeren ? </p>
                      <hr>
                      <p class="mb-0">Når denne er slettet er det permanent, kan ikke tilbakeføres  </p>
                    </div>

                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Lukk </button>
                    <a href="delete_buser.php?id='.$row["id"].'"><button type="button" class="btn btn-danger"><i class="mdi mdi-trash-can-outline me-1"></i> Slette Bruker</button></a>
                    </div>
                    </div>
                    </div>
                    </div>
                     <!-- end Modal senyhet -->
                    ';
                  }
                  }
                  else {
                  echo "0 results";
                  }

                  $conn->close();

                  ?>

                  </tbody>
                </table>
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
