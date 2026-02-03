<?php
require_once 'core/init.php';
include 'core/db_con.php';
include 'core/database_connection.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();

if(!empty($_GET['status'])){
    switch($_GET['status']){
        case 'succ':
            $statusType = 'alert-success';
            $statusMsg = 'Members data has been imported successfully.';
            break;
        case 'err':
            $statusType = 'alert-danger';
            $statusMsg = 'Some problem occurred, please try again.';
            break;
        case 'invalid_file':
            $statusType = 'alert-danger';
            $statusMsg = 'Please upload a valid CSV file.';
            break;
        default:
            $statusType = '';
            $statusMsg = '';
    }
} ?>
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
  <link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css'>
  <!--bootstrap css-->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
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
			<div class="breadcrumb-title pe-3">Kveldssendinger</div>
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
      <?php if(!empty($statusMsg)){ ?>
      <div class="col-xs-12">
          <div class="alert <?php echo $statusType; ?>"><?php echo $statusMsg; ?></div>
      </div>
      <?php } ?>
	    <div class="card-body">
        <table class="table mb-0 table-hover">
          <thead>
            <tr>
              <th scope="col">Dato</th>
              <th scope="col">Programledere</th>
              <th scope="col">Tekniker</th>
              <th scope="col">Valg</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $query = "
            SELECT * FROM kveld

            ";

            $result = $connect->query($query);
            foreach($result as $row)
            {
            echo '
            <tr>
              <th scope="row">'.$row["dato"].'</th>
              <td>'.$row["navn"].'</td>
              <td>'.$row["tek"].'</td>
              <td><button type="button" class="btn btn-grd-primary px-4" data-bs-toggle="modal" data-bs-target="#FormModal'.$row["id"].'">Endre</button>
                  <button type="button" name="button" class="btn btn-danger">Fjerne</button> </td>
            </tr>

            <!-- form modal-->
            <div class="modal fade" id="FormModal'.$row["id"].'">
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
                        <div class="col-md-6" >
                          <label for="input1" class="form-label">Dato</label>
                          <input type="text" class="form-control date-format" id="dato" name="dato" placeholder="'.$row["dato"].'" value="'.$row["dato"].'">
                        </div>
                        <div class="col-md-6">
                          <label for="input2" class="form-label">Programledere</label>
                          <input type="text" class="form-control" id="prgl" name="prgl" placeholder="Programledere" value="'.$row["navn"].'">
                        </div>
                        <div class="col-md-12">
                          <label for="input3" class="form-label">Tekniker</label>
                          <input type="text" class="form-control" id="tek" name="tek" placeholder="Tekniker" value="'.$row["tek"].'">
                        </div>



                        <div class="col-md-12">
                          <div class="d-md-flex d-grid align-items-center gap-3">
                            <button type="button" class="btn btn-grd-danger px-4">Lagre</button>
                            <button type="button" class="btn btn-grd-info px-4" data-bs-dismiss="modal">Avbyrt endring</button>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!--/FormModal-->
            ';
            }
            ?>
          </tbody>
        </table>

		</div>
	  </div>
    <div class="card rounded-4" >
      <div class="card-body">
<button type="button" name="button" class="btn btn-info" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">Import/Export
<div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
  <div class="accordion-body">
    <div class="col-md-12" id="importFrm" >
        <form action="importData.php" method="post" enctype="multipart/form-data" id="import_form">
            <input type="file" name="file" id="file"/>
            <input type="submit" class="btn btn-primary" name="import_data" id="import_data" value="IMPORT">
        </form>
        <form class="form-horizontal" action="importData.php" method="post" name="upload_excel"
                         enctype="multipart/form-data">
                     <div class="form-group">
                               <div class="col-md-4 col-md-offset-4">
                                   <input type="submit" name="Export" class="btn btn-success" value="export Kveldssending listen"/>

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



  <!--bootstrap js-->
  <script src="assets/js/bootstrap.bundle.min.js"></script>

  <!--plugins-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!--plugins-->
  <script src="assets/plugins/datepicker/js/bootstrap-datepicker.min.js"></script>
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
