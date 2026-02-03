<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();

/**
 * Admin - Outlet History Page
 * Shows deliveries and returns for a specific outlet
 */
require_once __DIR__ . '/../app/bingo/config/config.php';


$currentUser = getCurrentUser();
$csrfToken = generateCsrfToken();

// Get outlet ID from URL
$outletId = (int) ($_GET['id'] ?? 0);
if (!$outletId) {
    redirect(baseUrl('/../app/bingo/pages/admin/outlets.php'));
}

// Get outlet details
$db = db();
$outlet = $db->fetchOne("SELECT * FROM us_outlets WHERE id = :id", ['id' => $outletId]);

if (!$outlet) {
    redirect(baseUrl('/../app/bingo/pages/admin/outlets.php'));
}

// Get filter parameters
$month = (int) ($_GET['month'] ?? date('n'));
$year = (int) ($_GET['year'] ?? date('Y'));

// Get sales for this outlet
$sales = $db->fetchAll(
    "SELECT s.*, u.name as user_name
     FROM us_sales s
     LEFT JOIN us_users u ON s.user_id = u.id
     WHERE s.outlet_id = :outlet_id AND s.month = :month AND s.year = :year
     ORDER BY s.sale_date DESC",
    ['outlet_id' => $outletId, 'month' => $month, 'year' => $year]
);

// Get deliveries for this outlet
$deliveries = $db->fetchAll(
    "SELECT d.*, u.name as delivered_by_name
     FROM us_deliveries d
     LEFT JOIN us_users u ON d.delivered_by = u.id
     WHERE d.outlet_id = :outlet_id AND d.month = :month AND d.year = :year
     ORDER BY d.delivery_date DESC",
    ['outlet_id' => $outletId, 'month' => $month, 'year' => $year]
);

// Get returns for this outlet
$returns = $db->fetchAll(
    "SELECT r.*, u.name as received_by_name
     FROM us_returns r
     LEFT JOIN us_users u ON r.received_by = u.id
     WHERE r.outlet_id = :outlet_id AND r.month = :month AND r.year = :year
     ORDER BY r.return_date DESC",
    ['outlet_id' => $outletId, 'month' => $month, 'year' => $year]
);

// Get totals
$salesTotal = $db->fetchOne(
    "SELECT COALESCE(SUM(quantity), 0) as total FROM us_sales
     WHERE outlet_id = :outlet_id AND month = :month AND year = :year",
    ['outlet_id' => $outletId, 'month' => $month, 'year' => $year]
);

$deliveryTotal = $db->fetchOne(
    "SELECT COALESCE(SUM(quantity), 0) as total FROM us_deliveries
     WHERE outlet_id = :outlet_id AND month = :month AND year = :year",
    ['outlet_id' => $outletId, 'month' => $month, 'year' => $year]
);

$returnTotal = $db->fetchOne(
    "SELECT COALESCE(SUM(quantity), 0) as total FROM us_returns
     WHERE outlet_id = :outlet_id AND month = :month AND year = :year",
    ['outlet_id' => $outletId, 'month' => $month, 'year' => $year]
);

// Get yearly summary
$yearlyDeliveries = $db->fetchAll(
    "SELECT month, COALESCE(SUM(quantity), 0) as total
     FROM us_deliveries
     WHERE outlet_id = :outlet_id AND year = :year
     GROUP BY month
     ORDER BY month",
    ['outlet_id' => $outletId, 'year' => $year]
);

$yearlyReturns = $db->fetchAll(
    "SELECT month, COALESCE(SUM(quantity), 0) as total
     FROM us_returns
     WHERE outlet_id = :outlet_id AND year = :year
     GROUP BY month
     ORDER BY month",
    ['outlet_id' => $outletId, 'year' => $year]
);

$monthNames = getMonthNames();

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
						<li class="breadcrumb-item active" aria-current="page">Utslagssteder med salgs tilgang</li>
					</ol>
				</nav>
			</div>

		</div>
		<!--end breadcrumb-->
    <!--top meny-->
    <div class="card rounded-4 rounded-4">
      <div class="card-body">
        <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-4 g-3">
          <div class="col">
            <div class="card rounded-4 mb-0 border">
              <div class="card-body">

                <div class="mt-4">
                  <h2 style="font-size: var(--font-size-xl); font-weight: 600; margin-bottom: 4px;">
                      <?= e($outlet['name']) ?>
                  </h2>
                  <p ><?= e($outlet['address'] ?: 'Ingen adresse') ?></p>
                  <span class="badge <?= $outlet['active'] ? 'badge-success' : 'badge-secondary' ?>">
                      <?= $outlet['active'] ? 'Aktiv' : 'Inaktiv' ?>
                  </span>
                </div>
                <div class="d-flex align-items-center justify-content-end gap-1 mt-3">
                  <p
                    class="dash-lable d-flex align-items-center gap-1 rounded mb-0 bg-danger text-danger bg-opacity-10">

                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="card rounded-4 mb-0 border">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                Velg Periode

                </div>
                <div class="mt-4">
                  <div class="card-body">
                      <form class="d-flex gap-2 align-center" method="GET">
                          <input type="hidden" name="id" value="<?= $outletId ?>">
                          <label style="font-weight: 500;">Periode:</label>
                          <select name="month" class="form-select" style="width: auto;">
                              <?php foreach ($monthNames as $m => $name): ?>
                              <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= e($name) ?></option>
                              <?php endforeach; ?>
                          </select>
                          <select name="year" class="form-select" style="width: auto;">
                              <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                              <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                              <?php endfor; ?>
                          </select>
                          <button type="submit" class="btn btn-primary">Vis</button>
                      </form>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <div class="col">
            <div class="card rounded-4 mb-0 border">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                Tilbake til oversikten

                </div>
                <div class="mt-4">
                  <div class="card-body">
                    <a href="app_bingo_utsalg.php">  <button type="button" class="btn btn-primary">Til oversikten</button></a>
                  </div>
                </div>

              </div>
            </div>
          </div>



        </div><!--end row-->
      </div>
    </div>
<!--/top meny-->

<!--stats meny-->
<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-4">
  <div class="col">
    <div class="card rounded-4">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <div
            class="wh-48 d-flex bg-success text-success bg-opacity-10 align-items-center justify-content-center rounded-circle">
            <span class="material-icons-outlined">account_circle</span>
          </div>
          <div class="">
            <div class="d-flex align-items-center align-self-end text-success mb-1">

              Solgt i <?= e($monthNames[$month]) ?>
            </div>
            <h4 class="mb-0"><?= number_format($salesTotal['total'], 0, ',', ' ') ?></h4>
            <p class="mb-0">Stk</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card rounded-4">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <div
            class="wh-48 d-flex bg-danger text-danger bg-opacity-10 align-items-center justify-content-center rounded-circle">
            <span class="material-icons-outlined">favorite_border</span>
          </div>
          <div class="">
            <div class="d-flex align-items-center align-self-end text-success mb-1">
              Levert i <?= e($monthNames[$month]) ?>
            </div>
            <h4 class="mb-0"><?= number_format($deliveryTotal['total'], 0, ',', ' ') ?></h4>
            <p class="mb-0">Stk</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card rounded-4">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <div
            class="wh-48 d-flex bg-info text-info bg-opacity-10 align-items-center justify-content-center rounded-circle">
            <span class="material-icons-outlined">play_circle</span>
          </div>
          <div class="">
            <div class="d-flex align-items-center align-self-end text-success mb-1">
              Retur i <?= e($monthNames[$month]) ?>
            </div>
            <h4 class="mb-0"><?= number_format($returnTotal['total'], 0, ',', ' ') ?></h4>
            <p class="mb-0">stk</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col">
    <div class="card rounded-4">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between gap-3">
          <div
            class="wh-48 d-flex bg-orange-light text-orange bg-opacity-10 align-items-center justify-content-center rounded-circle">
            <span class="material-icons-outlined">bookmarks</span>
          </div>
          <div class="">
            <div class="d-flex align-items-center align-self-end text-success mb-1">
              Beholdning
            </div>
            <h4 class="mb-0"><?= number_format($deliveryTotal['total'] - $salesTotal['total'] - $returnTotal['total'], 0, ',', ' ') ?></h4>
            <p class="mb-0">Stk</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div><!--end row-->
<!--/stats meny-->

<!--bunn row-->

<div class="row row-cols-2 row-cols-lg-2 row-cols-xl-2">
  <div class="col d-flex">
    <div class="card w-100 rounded-4">
      <div class="card-header p-3 bg-transparent">
        <div class="d-flex align-items-center justify-content-between">
          <div class="">
            <h5 class="mb-0">Leveringer</h5>
          </div>

        </div>
      </div>
      <div class="card-body">
        <?php if (empty($deliveries)): ?>
        <div class="empty-state">
            <p >Ingen leveringer i denne perioden</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle mb-0 table-striped">
            <thead>
              <tr>
                <th>Dato</th>
                <th>Levert av</th>
                <th>Antall</th>

              </tr>
            </thead>
            <tbody>
              <?php foreach ($deliveries as $delivery): ?>
              <tr>
                <td>
                  <div class="">
                    <h6 class="mb-0"><?= date('d.m.Y', strtotime($delivery['delivery_date'])) ?></h6>

                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center flex-row gap-3">

                    <div class="">
                      <h6 class="mb-0"><?= e($delivery['delivered_by_name'] ?? '-') ?></h6>

                    </div>
                  </div>
                </td>

                <td>
                  <h5 class="mb-0"><strong><?= number_format($delivery['quantity'], 0, ',', ' ') ?></strong></h5>
                </td>
              </tr>
              <?php endforeach; ?>

            </tbody>
          </table>
        </div>
  <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-6 d-flex">
    <div class="card w-100 rounded-4">
      <div class="card-header p-3 bg-transparent">
        <div class="d-flex align-items-center justify-content-between">
          <div class="">
            <h5 class="mb-0">Returer</h5>
          </div>

        </div>
      </div>
      <div class="card-body">
        <?php if (empty($returns)): ?>
        <div class="empty-state">
            <p>Ingen returer i denne perioden</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle mb-0 table-striped">
            <thead>
              <tr>
                <th>Dato</th>
                <th>Antall</th>
                <th>Årsak</th>
                <th>Mottat av</th>

              </tr>
            </thead>
            <tbody>
                <?php foreach ($returns as $return): ?>
              <tr>
                <td>
                  <div class="">
                    <h6 class="mb-0"><?= date('d.m.Y', strtotime($return['return_date'])) ?></h6>

                  </div>
                </td>
                <td>
                  <div class="d-flex align-items-center flex-row gap-3">

                    <div class="">
                      <h6 class="mb-0"><strong><?= number_format($return['quantity'], 0, ',', ' ') ?></strong></h6>

                    </div>
                  </div>
                </td>

                <td>
                  <h5 class="mb-0"><?= e($return['reason'] ?: '-') ?></h5>
                </td>
                <td>
                  <h5 class="mb-0"><?= e($return['received_by_name'] ?: '-') ?></h5>
                </td>
              </tr>
  <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>



</div><!--end row-->
<!--/bunn meny-->

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

  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>

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
</body>

</html>
