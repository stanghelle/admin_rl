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
  <title>RL Admin</title>
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!--main css-->
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="sass/main.css" rel="stylesheet">
  <link href="sass/dark-theme.css" rel="stylesheet">
  <link href="sass/blue-theme.css" rel="stylesheet">
  <link href="sass/semi-dark.css" rel="stylesheet">
  <link href="sass/bordered-theme.css" rel="stylesheet">
  <link href="sass/responsive.css" rel="stylesheet">

  <style>
    /* Live indicator */
    .live-dot {
      display: inline-block;
      width: 8px;
      height: 8px;
      background: #10b981;
      border-radius: 50%;
      margin-right: 6px;
      animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.6; transform: scale(0.9); }
    }
    /* Update flash animation */
    .card.updating {
      animation: update-flash 0.3s ease;
    }
    @keyframes update-flash {
      0% { opacity: 1; }
      50% { opacity: 0.7; }
      100% { opacity: 1; }
    }
  </style>
</head>

<body>

  <?php include 'nav.php'; ?>

  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content">
      <!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Dashboard</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Fremsiden</li>
							</ol>
						</nav>
					</div>

				</div>
				<!--end breadcrumb-->

        <div class="row">
          <div class="col-xxl-8 d-flex align-items-stretch">
            <div class="card w-100 overflow-hidden rounded-4">
              <div class="card-body position-relative p-4">
                <div class="row">
                  <div class="col-6 col-sm-6">
                    <div class="d-flex align-items-center gap-3 mb-5">
                      <img src="../img/user/user.png" class="rounded-circle bg-grd-info p-1"  width="60" height="60" alt="user">
                      <div class="">
                        <p class="mb-0 fw-semibold">Velkommen tilbake</p>
                        <h4 class="fw-semibold mb-0 fs-4 mb-0"><?php echo $user->data()->name; ?></h4>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-5">



                    </div>
                  </div>
                  <div class="col-12 col-sm-5">
                    <div class="welcome-back-img pt-4">
                       <img src="assets/images/gallery/welcome-back-3.png" height="180" alt="">
                    </div>
                  </div>
                </div><!--end row-->
              </div>
            </div>
          </div>
          <div class="col-xl-6 col-xxl-2 d-flex align-items-stretch">
            <div class="card w-100 rounded-4">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-1">
                  <div class="">
                    <h5 class="mb-0">Bingo toppen</h5>
                    <?php $data = DB::getInstance()->query('SELECT * FROM rl_bingo'); ?>
                    <?php  ?>
                    <?php if ($data->count()) : ?>


                        <?php
                        foreach ($data->results() as $result) {

                          //$user = new User();

                            echo '
                    <p class="mb-3"><h5>Dato: '.$result->dato.'</h5></p>
                    <p class="mb-0"><h6´5>Lykketall : '.$result->tall.'</h5></p>
                  </div>

                </div>

                <div class="">
                  <h2>'.$result->topp.'</h2>
                </div>
                ';
              }
              ?>

              <?php else : ?>
              <h4 class="text-muted text-center">Det finnes ingen Nyheter listet.</h4>
              <?php endif; ?>
              <a href="bingotoppen.php"><button type="button" name="button" class="btn btn-primary">Endre bingotoppen</button></a>
              </div>
            </div>
          </div>
          <div class="col-xl-6 col-xxl-2 d-flex align-items-stretch">
            <div class="card w-100 rounded-4">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="">
                    <h5 class="mb-0">Nett bestillinger</h5>
                    <p class="mb-0">Nye bestillinger</p>
                  </div>

                </div>
                <div class="chart-container2">
                  <img src="assets/images/scart_w.png" width="100" alt="">
                </div>

              </div>
            </div>
          </div>
          <div class="col-xl-6 col-xxl-4 d-flex align-items-stretch">
            <div class="card w-100 rounded-4">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                  <div class="">
                    <h6 class="mb-0">Besøkende denne uken</h6>
                    <p class="mb-0 text-secondary small">
                      <span class="live-dot"></span>
                      Sanntid - oppdatert <span id="lastUpdateTime">nå</span>
                    </p>
                  </div>
                  <a href="traffic_analytics.php" class="btn btn-sm btn-outline-primary">
                    <span class="material-icons-outlined fs-6">analytics</span>
                  </a>
                </div>
                <div id="weeklyTrafficChart" style="height: 180px;"></div>
                <div class="d-flex align-items-center justify-content-between mt-3">
                  <div class="">
                    <h4 class="mb-0 text-primary" id="weeklyVisitsTotal">--</h4>
                    <small class="text-muted">Totalt besøk</small>
                  </div>
                  <div class="d-flex align-items-center">
                    <span id="weeklyChangeIcon" class="material-icons-outlined text-success">trending_up</span>
                    <span id="weeklyChangePercent" class="text-success fw-semibold">--%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-6 d-flex align-items-stretch">
            <div class="card w-100 rounded-4">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="">
                    <h6 class="mb-0">Månedlig trafikk</h6>
                    <p class="mb-0 text-secondary small">Siste 30 dager</p>
                  </div>
                  <a href="traffic_analytics.php" class="btn btn-sm btn-outline-primary">Se mer</a>
                </div>
                <div id="monthlyTrafficChart" style="height: 250px;"></div>
              </div>
            </div>
          </div>
          <div class="col-xl-6 d-flex align-items-stretch">
            <div class="card w-100 rounded-4">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="">
                    <h6 class="mb-0">Enheter og nettlesere</h6>
                    <p class="mb-0 text-secondary small">Denne måneden</p>
                  </div>
                </div>
                <div class="row">
                  <div class="col-6">
                    <div id="deviceChart" style="height: 200px;"></div>
                    <p class="text-center small text-muted mb-0">Enheter</p>
                  </div>
                  <div class="col-6">
                    <div id="browserChart" style="height: 200px;"></div>
                    <p class="text-center small text-muted mb-0">Nettlesere</p>
                  </div>
                </div>
              </div>
            </div>
          </div>






          <div class="col-lg-12 col-xxl-8 d-flex align-items-stretch">
            <div class="card w-100 rounded-4">
              <div class="card-body">
               <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="">
                    <h5 class="mb-0">Dev logg (det er ferdig finner du i denne listen)</h5>
                  </div>
									<div class="">
									<a href="dev.php">	<button type="button" name="button" class="btn btn-primary">Se hele dev listen</button></a>
									</div>

                </div>
                <div class="order-search position-relative my-3">
                  <input class="form-control rounded-5 px-5" type="text" placeholder="Search">
                  <span class="material-icons-outlined position-absolute ms-3 translate-middle-y start-0 top-50">search</span>
                </div>
                 <div class="table-responsive">
                     <table class="table align-middle">
                       <thead>
                        <tr>
                          <th>Tittel</th>
                          <th>Beskrivelse</th>
                          <th>Prio</th>
                          <th>Tag</th>
                          <th>Dato</th>
                        </tr>
                       </thead>
                        <tbody>
													<?php $data = DB::getInstance()->query('SELECT * FROM kanban_tasks WHERE column_id = 5'); ?>
													<?php  ?>
													<?php if ($data->count()) : ?>


															<?php
															foreach ($data->results() as $result) {

																//$user = new User();
																$json = $result->labels;
																$arr = json_decode($json, true);
																foreach($arr as  $value) {
																$labels = $value;

	}
																	echo '
                          <tr>
                            <td>
                              <div class="d-flex align-items-center gap-3">
                                 <div class="">
                                    <img src="assets/images/top-products/01.png" class="rounded-circle" width="50" height="50" alt="">
                                 </div>
                                 <p class="mb-0">'.$result->title.'</p>
                              </div>
                            </td>
                            <td>'.$result->description.'</td>
                            <td>'.$result->priority.'</td>
                            <td><p class="dash-lable mb-0 bg-success bg-opacity-10 text-success rounded-2">'.$labels.'</p></td>
                            <td>
                              <div class="d-flex align-items-center gap-1">
                                <p class="mb-0">'.$result->updated_at.'</p>
                              </div>
                            </td>
                          </tr>
													';
						            }
						            ?>

						            <?php else : ?>
						            <h4 class="text-muted text-center">Det finnes ingen Programmer listet.</h4>
						            <?php endif; ?>

                        </tbody>
                     </table>
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
  <script src="assets/js/jquery.min.js"></script>
  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/plugins/peity/jquery.peity.min.js"></script>
  <script>
    $(".data-attributes span").peity("donut")
  </script>
  <script src="assets/js/main.js"></script>
  <script src="assets/js/dashboard1.js"></script>
  <script>
	   new PerfectScrollbar(".user-list")
  </script>

  <!-- Traffic Charts -->
  <script>
  // Chart instances for updating
  var weeklyChartInstance = null;
  var monthlyChartInstance = null;
  var deviceChartInstance = null;
  var browserChartInstance = null;
  var refreshInterval = 30000; // 30 seconds
  var lastUpdate = new Date();

  $(document).ready(function() {
    // Initial load
    loadAllTrafficData();

    // Auto-refresh every 30 seconds
    setInterval(function() {
      refreshTrafficData();
    }, refreshInterval);

    // Update "last updated" display
    setInterval(updateLastUpdatedDisplay, 1000);
  });

  function loadAllTrafficData() {
    loadWeeklyTraffic();
    loadMonthlyTraffic();
    loadDeviceChart();
    loadBrowserChart();
    lastUpdate = new Date();
  }

  function refreshTrafficData() {
    // Only refresh the stats, not the full charts (smoother UX)
    loadWeeklyStats();
    lastUpdate = new Date();

    // Show refresh indicator
    showRefreshIndicator();
  }

  function loadWeeklyStats() {
    $.ajax({
      url: 'api/traffic.php?action=stats&period=week',
      method: 'GET',
      success: function(response) {
        if (response.success) {
          $('#weeklyVisitsTotal').text(response.data.total_visits);
          var change = response.data.change_percent;
          $('#weeklyChangePercent').text((change >= 0 ? '+' : '') + change + '%');
          if (change >= 0) {
            $('#weeklyChangeIcon').removeClass('text-danger').addClass('text-success').text('trending_up');
            $('#weeklyChangePercent').removeClass('text-danger').addClass('text-success');
          } else {
            $('#weeklyChangeIcon').removeClass('text-success').addClass('text-danger').text('trending_down');
            $('#weeklyChangePercent').removeClass('text-success').addClass('text-danger');
          }
        }
      }
    });
  }

  function showRefreshIndicator() {
    // Brief flash to indicate update
    $('.card').addClass('updating');
    setTimeout(function() {
      $('.card').removeClass('updating');
    }, 300);
  }

  function updateLastUpdatedDisplay() {
    var seconds = Math.floor((new Date() - lastUpdate) / 1000);
    var text = seconds < 5 ? 'Akkurat nå' : seconds + 's siden';
    $('#lastUpdateTime').text(text);
  }

  function loadWeeklyTraffic() {
    $.ajax({
      url: 'api/traffic.php?action=daily&days=7',
      method: 'GET',
      success: function(response) {
        if (response.success) {
          renderWeeklyChart(response.data);
        }
      },
      error: function() {
        console.log('Failed to load weekly traffic');
      }
    });

    // Load stats for the summary
    $.ajax({
      url: 'api/traffic.php?action=stats&period=week',
      method: 'GET',
      success: function(response) {
        if (response.success) {
          $('#weeklyVisitsTotal').text(response.data.total_visits);
          var change = response.data.change_percent;
          $('#weeklyChangePercent').text((change >= 0 ? '+' : '') + change + '%');
          if (change >= 0) {
            $('#weeklyChangeIcon').removeClass('text-danger').addClass('text-success').text('trending_up');
            $('#weeklyChangePercent').removeClass('text-danger').addClass('text-success');
          } else {
            $('#weeklyChangeIcon').removeClass('text-success').addClass('text-danger').text('trending_down');
            $('#weeklyChangePercent').removeClass('text-success').addClass('text-danger');
          }
        }
      }
    });
  }

  function renderWeeklyChart(data) {
    var options = {
      series: [{
        name: 'Besøk',
        data: data.visits
      }],
      chart: {
        type: 'area',
        height: 180,
        sparkline: { enabled: false },
        toolbar: { show: false },
        zoom: { enabled: false }
      },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2 },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.4,
          opacityTo: 0.1,
        }
      },
      colors: ['#0d6efd'],
      xaxis: {
        categories: data.labels,
        labels: { style: { fontSize: '10px' } }
      },
      yaxis: { show: false },
      grid: { show: false },
      tooltip: {
        theme: 'dark',
        y: { formatter: function(val) { return val + ' besøk'; } }
      }
    };

    var chart = new ApexCharts(document.querySelector("#weeklyTrafficChart"), options);
    chart.render();
  }

  function loadMonthlyTraffic() {
    $.ajax({
      url: 'api/traffic.php?action=daily&days=30',
      method: 'GET',
      success: function(response) {
        if (response.success) {
          renderMonthlyChart(response.data);
        }
      }
    });
  }

  function renderMonthlyChart(data) {
    var options = {
      series: [{
        name: 'Besøk',
        data: data.visits
      }, {
        name: 'Unike besøkende',
        data: data.unique_visitors
      }],
      chart: {
        type: 'bar',
        height: 250,
        toolbar: { show: false }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '60%',
          borderRadius: 4
        }
      },
      dataLabels: { enabled: false },
      colors: ['#0d6efd', '#20c997'],
      xaxis: {
        categories: data.labels,
        labels: {
          style: { fontSize: '10px' },
          rotate: -45,
          rotateAlways: data.labels.length > 14
        }
      },
      yaxis: {
        labels: { style: { fontSize: '10px' } }
      },
      legend: {
        position: 'top',
        horizontalAlign: 'right'
      },
      tooltip: {
        theme: 'dark'
      }
    };

    var chart = new ApexCharts(document.querySelector("#monthlyTrafficChart"), options);
    chart.render();
  }

  function loadDeviceChart() {
    $.ajax({
      url: 'api/traffic.php?action=devices&period=month',
      method: 'GET',
      success: function(response) {
        if (response.success && response.data.length > 0) {
          renderDeviceChart(response.data);
        } else {
          $('#deviceChart').html('<p class="text-muted text-center small mt-5">Ingen data</p>');
        }
      }
    });
  }

  function renderDeviceChart(data) {
    var labels = data.map(function(d) { return d.device; });
    var series = data.map(function(d) { return d.visits; });

    var options = {
      series: series,
      chart: {
        type: 'donut',
        height: 200
      },
      labels: labels,
      colors: ['#0d6efd', '#6f42c1', '#20c997', '#ffc107'],
      legend: { show: false },
      dataLabels: { enabled: false },
      plotOptions: {
        pie: {
          donut: {
            size: '70%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Totalt',
                fontSize: '12px'
              }
            }
          }
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#deviceChart"), options);
    chart.render();
  }

  function loadBrowserChart() {
    $.ajax({
      url: 'api/traffic.php?action=browsers&period=month',
      method: 'GET',
      success: function(response) {
        if (response.success && response.data.length > 0) {
          renderBrowserChart(response.data);
        } else {
          $('#browserChart').html('<p class="text-muted text-center small mt-5">Ingen data</p>');
        }
      }
    });
  }

  function renderBrowserChart(data) {
    var labels = data.map(function(d) { return d.browser; });
    var series = data.map(function(d) { return d.visits; });

    var options = {
      series: series,
      chart: {
        type: 'donut',
        height: 200
      },
      labels: labels,
      colors: ['#fd7e14', '#0dcaf0', '#198754', '#dc3545', '#6c757d'],
      legend: { show: false },
      dataLabels: { enabled: false },
      plotOptions: {
        pie: {
          donut: {
            size: '70%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Totalt',
                fontSize: '12px'
              }
            }
          }
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#browserChart"), options);
    chart.render();
  }
  </script>

</body>

</html>
