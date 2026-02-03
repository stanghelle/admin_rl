<?php
require_once 'core/init.php';
include 'core/db_con.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();
/**
 * Reports Page
 */
require_once __DIR__ . '/../app/bingo/config/config.php';



$month = (int) ($_GET['month'] ?? date('n'));
$year = (int) ($_GET['year'] ?? date('Y'));

// Get sales for the period
$db = db();

    $sales = $db->fetchAll(
        "SELECT s.*, u.name as user_name
         FROM us_sales s
         JOIN us_users u ON s.user_id = u.id
         WHERE s.month = :month AND s.year = :year
         ORDER BY s.sale_date DESC",
        ['month' => $month, 'year' => $year]
    );


$monthNames = getMonthNames();
$pris_bonge = '50';
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
						<li class="breadcrumb-item"></li>
            <li class="breadcrumb" aria-current="page">Bingo app - </li>
						<li class="breadcrumb-item active" aria-current="page">Salgsrapport</li>
					</ol>
				</nav>
			</div>

		</div>
		<!--end breadcrumb-->

<!--Main content-->
<div class="col-12 col-xxl-12 d-flex">
  <div class="card rounded-4 w-100">
    <div class="card-body">
      <div class="d-flex align-items-start justify-content-between mb-3">
        <div class="">
          <h5 class="mb-0">Salgsrapport</h5>
        </div>
        <form class="d-flex gap-2" method="GET">
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
            <button type="submit" class="btn btn-secondary">Filtrer</button>
        </form>
      </div>

      <div class="table-responsive">
        <?php if (empty($sales)): ?>
        <div class="empty-state">

            <h3 class="empty-state-title">Ingen salg funnet</h3>
            <p class="empty-state-message">Det er ingen registrerte salg for <?= e($monthNames[$month]) ?> <?= $year ?>.</p>
        </div>
        <?php else: ?>
        <table class="table align-middle mb-0 table-striped">
          <thead>
            <tr>
              <th>Dato</th>
              <th>Bruker / Utsalgssted</th>
              <th>Antall</th>
              <th>Notat</th>
              <th>Sum</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $totalQuantity = 0;
            foreach ($sales as $sale):
                $totalQuantity += $sale['quantity'];
            ?>
            <tr>
              <td>
                <div class="">
                  <h6 class="mb-0"><?= date('d.m.Y', strtotime($sale['sale_date'])) ?></h6>

                </div>
              </td>
              <td>
                <div class="d-flex align-items-center flex-row gap-3">

                  <div class="">
                    <h6 class="mb-0"><?= e($sale['user_name']) ?></h6>
                  </div>
                </div>
              </td>
              <td>
                <div class="card-lable bg-success text-success bg-opacity-10">
                  <p class="text-success mb-0"><?= number_format($sale['quantity'], 0, ',', ' ') ?> stk</p>
                </div>
              </td>
              <td>
                <h5 class="mb-0"><?= e($sale['notes'] ?: '-') ?></h5>
              </td>
              <td>
                <h5 class="mb-0"><?= number_format($sale['quantity'] * $pris_bonge, 0, ',', ' ') ?> kr</h5>
              </td>
            </tr>
              <?php endforeach; ?>

          </tbody>
          <tfoot>
              <tr>
                  <th>Totalt</th>
                  <th></th>
                  <th class="text-right"><?= number_format($totalQuantity, 0, ',', ' ') ?> stk</th>
                  <th></th>
                  <th class="text-right"><?= number_format($totalQuantity * $pris_bonge, 0, ',', ' ') ?> kr</th>
              </tr>
          </tfoot>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!--/Main content-->




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


</body>

</html>
