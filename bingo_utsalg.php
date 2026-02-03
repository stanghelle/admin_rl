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
  <title>RLAdmin- Bingotopp historikk</title>
  <!--favicon-->
  <link rel="icon" href="assets/images/favicon-32x32.png" type="image/png">
  <!-- loader-->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>
  <link href="assets/css/pace.min.css" rel="stylesheet">
  <script src="assets/js/pace.min.js"></script>
  <link href="assets/css/filter.css" rel="stylesheet">
  <script src="assets/js/filter.js"></script>
  <!--plugins-->
  <link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/metisMenu.min.css">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/mm-vertical.css">
  <link rel="stylesheet" type="text/css" href="assets/plugins/simplebar/css/simplebar.css">

  <!--bootstrap css-->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.19.1/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
    <div class="main-content filterable">
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
    <div class="row g-3 " >
      <?php Template::output('msg'); ?>
      <div class="col-auto">
        <div class="position-relative">
          <input class="form-control px-5" type="search" placeholder="Søk etter utslagsted" id="myInput" onkeyup="myFunction()">
          <span class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50 fs-5">search</span>
        </div>
      </div>
      <div class="col-auto flex-grow-1 overflow-auto">
        <div class="btn-group position-static">



        </div>
      </div>
      <div class="col-auto">
        <div class="d-flex align-items-center gap-2 justify-content-lg-end">
<button class="btn btn-default btn-xs btn-filter"><span class="glyphicon glyphicon-filter"></span>Filter</button>
           <button class="btn btn-filter px-4"><i class="bi bi-box-arrow-right me-2"></i>Export</button>
           <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#FormModal"><i class="bi bi-plus-lg me-2"></i>Legg til Utsalgsted</button>
        </div>
      </div>
    </div><!--end row-->
	  <div class="card rounded-4" >

	    <div class="card-body">

        <div class="customer-table ">
          <div class="table-responsive white-space-nowrap">

             <table class="table align-middle" id="myTable">
              <thead class="table-light">
                <tr class="filters">
                    <th><input type="text" class="form-control" placeholder="valg" disabled></th>
                    <th><input type="text" class="form-control" placeholder="Navn" ></th>
                    <th><input type="text" class="form-control" placeholder="Plass" ></th>
                    <th><input type="text" class="form-control" placeholder="adr_" disabled></th>
                    <th><input type="text" class="form-control" placeholder="profil app id" disabled></th>


                </tr>

               </thead>
               <tbody>
                <?php $data = DB::getInstance()->query('SELECT * FROM bingo_utsalg INNER JOIN bingo_plass ON bingo_utsalg.pid = bingo_plass.bpid'); ?>
                 <?php  ?>
                 <?php if ($data->count()) : ?>


                     <?php
                     foreach ($data->results() as $result) {
                       $kid= $result->usid;
                       //$user = new User();

                         echo '
                 <tr>
                 <td><a href="edit_utsalg.php?usid='.$result->usid.'"><button class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></button></a></td>
                   <td>
                    <a class="d-flex align-items-center gap-3" href="javascript:;">
                      <div class="customer-pic">

                      </div>
                      <p class="mb-0 customer-name fw-bold">'.$result->navn.' '.$kid.'</p>
                    </a>
                   </td>

                   <td>'.$result->bpnavn.'</td>
                   <td>'.$result->adr_.'</td>
                   <td> <a href="app_bingo_utsalg_salg.php?id='.$result->app_id.'"> '.$result->app_id.'</a></td>


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
             <p>No.of Rows : <span id="rowcount"></span></p>
          </div>
        </div>
        <!-- utslagsted slutt-->











		</div>
	  </div>

    </div>
  </main>
  <!--end main wrapper-->
<!--Start ny utsalg-->
  <div class="modal fade" id="FormModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-bottom-0 py-2 bg-grd-info">
          <h5 class="modal-title">Nytt Utsalgsted</h5>
          <a href="javascript:;" class="primaery-menu-close" data-bs-dismiss="modal">
            <i class="material-icons-outlined">close</i>
          </a>
        </div>
        <div class="modal-body">
          <div class="form-body">
            <form class="row g-3" method="post" id="timer">
              <div class="col-md-12">
                <label for="navn" class="form-label">Navn på utslagsted</label>
                <input type="text" class="form-control" id="navn" name="navn" placeholder="Navn på utslagsted">
              </div>
              <div class="col-md-12">
                <label for="adr_" class="form-label">Adresse</label>
                <input type="text" class="form-control" id="adr_" name="adr_" placeholder="Adresse">
              </div>
              <div class="col-md-4">
                <label for="plass" class="form-label">Hvor er Utsalgsted</label>
                <select id="plass" name="plass" class="form-select">
                <?php
                $query = "SELECT * FROM `bingo_plass`;";

                // FETCHING DATA FROM DATABASE
                $result = mysqli_query($conn, $query);

                while($row = mysqli_fetch_array($result)) {
                $usid= $row["bpid"];
                $usnavn= $row["bpnavn"];
                ?>
                <option value="<?=$usid?>"><?=$usnavn?></option>
                 <?php } ?>
                  </select>
              </div>


              <div class="col-md-12">
                <div class="d-md-flex d-grid align-items-center gap-3">
                  <button type="submit" class="btn btn-grd-danger px-4" name="submit_timer">Submit</button>
                  <button type="button" class="btn btn-grd-info px-4">Reset</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!--slutt ny utsalg-->

    <!--start overlay-->
    <div class="overlay btn-toggle"></div>
    <!--end overlay-->


     <?php include 'footer.php'; ?>


  <!--bootstrap js-->
  <script src="assets/js/bootstrap.bundle.min.js"></script>

  <!--plugins-->

  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.all.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.19.1/dist/sweetalert2.all.min.js"></script>

  <script>
  function myFunction() {
    // Declare variables
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("myTable");
    tr = table.getElementsByTagName("tr");

    // Loop through all table rows, and hide those who don't match the search query
    for (i = 0; i < tr.length; i++) {
      td = tr[i].getElementsByTagName("td")[0];
      if (td) {
        txtValue = td.textContent || td.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
          tr[i].style.display = "";
        } else {
          tr[i].style.display = "none";
        }
      }
    }
  }

  // Highlight odd rows
highlightRows = () => {
	let oddRows = document.querySelectorAll('tbody tr.show')
	oddRows.forEach((row, index)=> {
		if (index % 2 == 0) {
			row.style.background = '#f1f1f1'
		} else {
			row.style.background = '#fff'
		}
	})
}

  </script>
  <script>
    function filterTableplass(){
      var dropdown=document.getElementById("plass");
      var selectedValue=dropdown.value;
      var table=document.getElementById('myTable');
      var rows=table.getElementsByTagName("tr");

      for(var i=1;i<rows.length;i++)
      {
        var row=rows[i];
        var plass=row.cells[1].textContent.trim();

        if(selectedValue==="all" || plass===selectedValue)
        {
          row.style.display="";
        }
        else
        {
          row.style.display="none";
      }
    }
      }
  </script>

  <?php




   if(isset($_POST['submit_timer'])){

    $navn = $_POST['navn'];
    $adr_ = $_POST['adr_'];
    $plass = $_POST['plass'];


      $insert = "insert into bingo_utsalg(pid,navn,adr_)values('$plass','$navn','$adr_')";

      $query= mysqli_query($conn,$insert);

      if($query){

        ?>

        <script>

        Swal.fire({
          position: "top-end",
          icon: "success",
          title: "Nytt utslagsted er lagt inn",
          showConfirmButton: false,
          timer: 1500
  });



        </script>

        <?php

      }else{

        ?>

        <script>

        Swal.fire({
  icon: "error",
  title: "Oops...",
  text: "Something went wrong!",
  footer: '<a href="#">Why do I have this issue?</a>'
  });



        </script>

        <?php


      }


   }


   ?>
</body>

</html>
