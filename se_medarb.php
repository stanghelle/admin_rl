<?php
require_once 'core/init.php';
include 'core/db_con.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    Session::flash('error', 'Ugyldig ID.');
    Redirect::to('medarb.php');
}
$query="SELECT * FROM medarb WHERE id = '".$id."'";
$result = mysqli_query($conn, $query);


while($get = mysqli_fetch_array($result)) {
        $navn = $get["navn"];
        $stilling= $get["stilling"];
        $protek = $get["protek"];
        $program = $get["program"];
        $img = $get["img"];
        $tlf = $get["tlf"];
        $epost = $get["epost"];
        }

        // If upload button is clicked ...
        if (isset($_POST['upload'])) {

            $filename = $_FILES["uploadfile"]["name"];
            $tempname = $_FILES["uploadfile"]["tmp_name"];
            $newfilename = md5($filename);
            $folder = "../img/user/" . $newfilename;

            $db = mysqli_connect("localhost", "root", "root", "radio");

            // Get all the submitted data from the form
            $sql = "UPDATE medarb SET img='$newfilename' WHERE ID=$id";

            // Execute query
            mysqli_query($db, $sql);

            // Now let's move the uploaded image into the folder: image
            if (move_uploaded_file($tempname, $folder)) {
              Session::flash('success', 'Bilde er blitt oppdatert.');
              Redirect::to('se_medarb.php?id='.$id.'');
            } else {
                echo "<h3>  Failed to upload image!</h3>";
            }
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
					<div class="breadcrumb-title pe-3">Components</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">User Profile</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">

					</div>
				</div>
				<!--end breadcrumb-->


        <div class="card rounded-4">
          <div class="card-body p-4">
             <div class="position-relative mb-5">

              <div class="profile-avatar position-absolute top-100 start-50 translate-middle">
                <img src="../img/user/<?=$img?>" onerror="this.onerror=null;this.src='assets/images/noimg.png';" class="img-fluid rounded-circle p-1 bg-grd-danger shadow" width="170" height="170" alt="" data-bs-toggle="modal" data-bs-target="#bildeModal">
              </div>
             </div>
              <div class="profile-info pt-5 d-flex align-items-center justify-content-between">
                <div class="">
                  <h3><?=$navn?></h3>

                </div>
                <div class="">
                  <a href="javascript:;" class="btn btn-grd-primary rounded-5 px-4"><i class="bi bi-chat me-2"></i>Send Message</a>
                </div>
              </div>
              <!---<div class="kewords d-flex align-items-center gap-3 mt-4 overflow-x-auto">
                 <button type="button" class="btn btn-sm btn-light rounded-5 px-4">UX Research</button>
                 <button type="button" class="btn btn-sm btn-light rounded-5 px-4">CX Strategy</button>
                 <button type="button" class="btn btn-sm btn-light rounded-5 px-4">Management</button>
              </div>-->
          </div>
        </div>

        <div class="row" >
           <div class="col-12 col-xl-8">
            <div class="card rounded-4 border-top border-4 border-primary border-gradient-1">
              <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="">
                    <h5 class="mb-0 fw-bold">Endre Medarbeider Profil</h5>
                  </div>

                 </div>
								<form class="row g-4" onsubmit="validateForm(event)" method="post">
									<div class="col-md-6">
										<label for="navn" class="form-label">Navn</label>
										<input type="text" class="form-control" id="navn" name="navn" placeholder="Navn" value="<?=$navn?>">
                    <input type="hidden" class="form-control" id="id" name="id" placeholder="Navn" value="<?=$id?>">
									</div>

									<div class="col-md-6">
										<label for="tlf" class="form-label">TLF</label>
										<input type="text" class="form-control" id="tlf" name="tlf" placeholder="Tlf / mobil" value="<?=$tlf?>">
									</div>
									<div class="col-md-6">
										<label for="epost" class="form-label">Epost</label>
										<input type="email" class="form-control" id="epost" name="epost" value="<?=$epost?>" placeholder="Epost adresse">
									</div>


									<div class="col-md-6">
										<label for="stilling" class="form-label">Stilling</label>
										<input type="text" class="form-control" id="stilling" name="stilling" placeholder="Stilling" value="<?=$stilling?>">
									</div>
                  <div class="col-md-6">
                    <label for="protek" class="form-label">funksjon</label>
                    <input type="text" class="form-control" id="protek" name="protek" placeholder="funksjon" value="<?=$protek?>">
                  </div>

									<div class="col-md-6">
										<label for="prog" class="form-label">Programmer</label>
										<textarea class="form-control" id="prog" name="prog" placeholder="Programmer ..." rows="4" cols="4"><?=$program?></textarea>
									</div>
									<div class="col-md-12">
										<div class="d-md-flex d-grid align-items-center gap-3">
											<button type="submit" class="btn btn-grd-primary px-4" name="submit-user">oppdater Profil</button>
											<button type="button" class="btn btn-light px-4">Nullstill</button>
										</div>
									</div>
								</form>
							</div>
            </div>
           </div>
           <div class="col-12 col-xl-4" id="updatekundeinfo">
            <div class="card rounded-4">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="">
                    <h5 class="mb-0 fw-bold">Om medarbeider</h5>
                  </div>

                 </div>
                 <div class="full-info">
                  <div class="info-list d-flex flex-column gap-3">
                    <div class="info-list-item d-flex align-items-center gap-3"><span class="material-icons-outlined">account_circle</span><p class="mb-0">Navn: <?=$navn?></p></div>
                    <div class="info-list-item d-flex align-items-center gap-3"><span class="material-icons-outlined">code</span><p class="mb-0">Stilling: <?=$stilling?></p></div>
                    <div class="info-list-item d-flex align-items-center gap-3"><span class="material-icons-outlined">flag</span><p class="mb-0">Programmer: <?=$program?></p></div>
                    <div class="info-list-item d-flex align-items-center gap-3"><span class="material-icons-outlined">flag</span><p class="mb-0">funksjon: <?=$protek?></p></div>
                    <div class="info-list-item d-flex align-items-center gap-3"><span class="material-icons-outlined">send</span><p class="mb-0">Epost: <?=$epost?></p></div>
                    <div class="info-list-item d-flex align-items-center gap-3"><span class="material-icons-outlined">call</span><p class="mb-0">Tlf: <?=$tlf?></p></div>
                  </div>
                </div>
              </div>
            </div>
          <!---  <div class="card rounded-4">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="">
                    <h5 class="mb-0 fw-bold">Accounts</h5>
                  </div>
                  <div class="dropdown">
                    <a href="javascript:;" class="dropdown-toggle-nocaret options dropdown-toggle"
                      data-bs-toggle="dropdown">
                      <span class="material-icons-outlined fs-5">more_vert</span>
                    </a>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                      <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                      <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                    </ul>
                  </div>
                 </div>

                <div class="account-list d-flex flex-column gap-4">
                  <div class="account-list-item d-flex align-items-center gap-3">
                    <img src="assets/images/apps/05.png" width="35" alt="">
                    <div class="flex-grow-1">
                      <h6 class="mb-0">Google</h6>
                      <p class="mb-0">Events and Reserch</p>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" checked>
                    </div>
                  </div>
                  <div class="account-list-item d-flex align-items-center gap-3">
                    <img src="assets/images/apps/02.png" width="35" alt="">
                    <div class="flex-grow-1">
                      <h6 class="mb-0">Skype</h6>
                      <p class="mb-0">Events and Reserch</p>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" checked>
                    </div>
                  </div>
                  <div class="account-list-item d-flex align-items-center gap-3">
                    <img src="assets/images/apps/03.png" width="35" alt="">
                    <div class="flex-grow-1">
                      <h6 class="mb-0">Slack</h6>
                      <p class="mb-0">Communication</p>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" checked>
                    </div>
                  </div>
                  <div class="account-list-item d-flex align-items-center gap-3">
                    <img src="assets/images/apps/06.png" width="35" alt="">
                    <div class="flex-grow-1">
                      <h6 class="mb-0">Instagram</h6>
                      <p class="mb-0">Social Network</p>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" checked>
                    </div>
                  </div>
                  <div class="account-list-item d-flex align-items-center gap-3">
                    <img src="assets/images/apps/17.png" width="35" alt="">
                    <div class="flex-grow-1">
                      <h6 class="mb-0">Facebook</h6>
                      <p class="mb-0">Social Network</p>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" checked>
                    </div>
                  </div>
                  <div class="account-list-item d-flex align-items-center gap-3">
                    <img src="assets/images/apps/11.png" width="35" alt="">
                    <div class="flex-grow-1">
                      <h6 class="mb-0">Paypal</h6>
                      <p class="mb-0">Social Network</p>
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" checked>
                    </div>
                  </div>
                </div>



              </div>
            </div> -->

           </div>
        </div><!--end row-->



    </div>

  </main>
  <!--end main wrapper-->


    <!--start overlay-->
    <div class="overlay btn-toggle"></div>
    <!--end overlay-->

    <!-- Modal bilde -->
    <div class="modal fade" id="bildeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="card mb-3" style="max-width: 540px;">
              <div class="row g-0">
                <div class="col-md-4">
                  <img src="../img/user/<?=$img?>" class="img-fluid rounded-start" alt="..." onerror="this.onerror=null;this.src='assets/images/noimg.png';">
                </div>
                <div class="col-md-8">
                  <div class="card-body">
                    <h5 class="card-title">Endre bilde</h5>
                    <p class="card-text"><form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <input class="form-control" type="file" name="uploadfile" value="" />
                </div>
                <div class="form-group">

                </div>
            </p>
                    <p class="card-text"><small class="text-muted"></small></p>
                  </div>
                </div>
              </div>
            </div>



          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Lukk</button>
            <button class="btn btn-primary" type="submit" name="upload">Last opp</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  <!-- Modal bilde -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.all.min.js"></script>
  <script src='https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.min.js'></script>
  <!-- mysql user-->
  <?php

  if(isset($_POST['submit-user'])){

    $id=$_POST['id'];
    $navn=$_POST['navn'];
    $stilling=$_POST['stilling'];
    $protek=$_POST['protek'];
    $prog=$_POST['prog'];
    $epost=$_POST['epost'];
    $tlf=$_POST['tlf'];



  $insert = "UPDATE medarb SET navn='$navn', stilling='$stilling', protek='$protek', program='$prog', epost='$epost', tlf='$tlf' WHERE id=$id";

  $query= mysqli_query($conn,$insert);

  if($query){

  ?>

  <script>

  Swal.fire({
    position: "top-end",
  icon: "success",
  title: "Medarbeider er oppdatert ...",
  text: "Info om medarbeider er oppdatert!",
  showConfirmButton: false,
    timer: 1500

  });
  $( "#updatekundeinfo" ).load(window.location.href + " #updatekundeinfo" );

  </script>

  <?php
  }else{
  ?>
  <script>

  Swal.fire({
  icon: "error",
  title: "Oops...",
  text: "Klarte ikke og oppdatere Epost / brukernavn!",
  footer: 'Kontakt IT om feilen forsette'
  });

  </script>

  <?php
  }
  }
  ?>
  <!-- /mysql user-->
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
