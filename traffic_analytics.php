<?php
/**
 * Traffic Analytics Page
 * Comprehensive website traffic analysis with multiple time periods
 */
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();
?>
<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RL Admin - Trafikkanalyse</title>
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
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <!--main css-->
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="sass/main.css" rel="stylesheet">
  <link href="sass/dark-theme.css" rel="stylesheet">
  <link href="sass/blue-theme.css" rel="stylesheet">
  <link href="sass/semi-dark.css" rel="stylesheet">
  <link href="sass/bordered-theme.css" rel="stylesheet">
  <link href="sass/responsive.css" rel="stylesheet">

  <style>
    .stat-card {
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .stat-icon {
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
    }
    .period-btn.active {
      background-color: var(--bs-primary) !important;
      color: white !important;
    }
    .chart-container {
      min-height: 300px;
    }
    .top-pages-table td {
      vertical-align: middle;
    }
    .page-url {
      max-width: 300px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    /* Live indicator */
    .live-dot {
      display: inline-block;
      width: 8px;
      height: 8px;
      background: #10b981;
      border-radius: 50%;
      margin-right: 4px;
      animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.6; transform: scale(0.9); }
    }
    /* Refresh animation */
    .stat-card.refreshing {
      animation: refresh-pulse 0.5s ease;
    }
    @keyframes refresh-pulse {
      0% { transform: scale(1); }
      50% { transform: scale(0.98); opacity: 0.8; }
      100% { transform: scale(1); opacity: 1; }
    }
    #manualRefresh:active .material-icons-outlined {
      animation: spin 0.5s ease;
    }
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
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
        <div class="breadcrumb-title pe-3">Trafikkanalyse</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item active" aria-current="page">Trafikkanalyse</li>
            </ol>
          </nav>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
          <span id="refreshStatus" class="text-secondary small">
            <span class="live-dot"></span> Sanntid
          </span>
          <button id="toggleAutoRefresh" class="btn btn-sm btn-outline-secondary" title="Pause auto-oppdatering">
            <span class="material-icons-outlined fs-6">pause</span>
          </button>
          <button id="manualRefresh" class="btn btn-sm btn-outline-primary" title="Oppdater nå">
            <span class="material-icons-outlined fs-6">refresh</span>
          </button>
          <a href="traffic_settings.php" class="btn btn-sm btn-outline-secondary" title="Innstillinger">
            <span class="material-icons-outlined fs-6">settings</span>
          </a>
        </div>
      </div>
      <!--end breadcrumb-->

      <!-- Stats Overview Cards -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
          <div class="card stat-card rounded-4 h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10">
                  <span class="material-icons-outlined text-primary">visibility</span>
                </div>
                <div>
                  <p class="mb-1 text-secondary small">Besøk i dag</p>
                  <h3 class="mb-0 fw-bold" id="statToday">--</h3>
                </div>
              </div>
              <div class="mt-2">
                <span id="statTodayChange" class="badge bg-success bg-opacity-10 text-success">--%</span>
                <small class="text-muted ms-1">vs i går</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="card stat-card rounded-4 h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10">
                  <span class="material-icons-outlined text-success">date_range</span>
                </div>
                <div>
                  <p class="mb-1 text-secondary small">Besøk denne uken</p>
                  <h3 class="mb-0 fw-bold" id="statWeek">--</h3>
                </div>
              </div>
              <div class="mt-2">
                <span id="statWeekChange" class="badge bg-success bg-opacity-10 text-success">--%</span>
                <small class="text-muted ms-1">vs forrige uke</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="card stat-card rounded-4 h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10">
                  <span class="material-icons-outlined text-info">calendar_month</span>
                </div>
                <div>
                  <p class="mb-1 text-secondary small">Besøk denne måneden</p>
                  <h3 class="mb-0 fw-bold" id="statMonth">--</h3>
                </div>
              </div>
              <div class="mt-2">
                <span id="statMonthChange" class="badge bg-success bg-opacity-10 text-success">--%</span>
                <small class="text-muted ms-1">vs forrige måned</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="card stat-card rounded-4 h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10">
                  <span class="material-icons-outlined text-warning">people</span>
                </div>
                <div>
                  <p class="mb-1 text-secondary small">Unike besøkende (måned)</p>
                  <h3 class="mb-0 fw-bold" id="statUnique">--</h3>
                </div>
              </div>
              <div class="mt-2">
                <small class="text-muted">Basert på økter</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-6 col-xl-3">
          <div class="card stat-card rounded-4 h-100">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10">
                  <span class="material-icons-outlined text-danger">public</span>
                </div>
                <div>
                  <p class="mb-1 text-secondary small">Land (måned)</p>
                  <h3 class="mb-0 fw-bold" id="statCountries">--</h3>
                </div>
              </div>
              <div class="mt-2">
                <small class="text-muted">Unike land</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Traffic Chart -->
      <div class="card rounded-4 mb-4">
        <div class="card-body">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
              <h5 class="mb-1">Trafikkoverikt</h5>
              <p class="mb-0 text-secondary">Besøk over tid</p>
            </div>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-secondary period-btn" data-period="hourly">Time</button>
              <button type="button" class="btn btn-outline-secondary period-btn active" data-period="daily">Dag</button>
              <button type="button" class="btn btn-outline-secondary period-btn" data-period="weekly">Uke</button>
              <button type="button" class="btn btn-outline-secondary period-btn" data-period="monthly">Måned</button>
            </div>
          </div>
          <div class="chart-container" id="mainTrafficChart"></div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Device & Browser Stats -->
        <div class="col-lg-6">
          <div class="card rounded-4 h-100">
            <div class="card-body">
              <h6 class="mb-3">Enhetsfordeling</h6>
              <div id="devicePieChart" style="height: 280px;"></div>
              <div id="deviceLegend" class="d-flex flex-wrap justify-content-center gap-3 mt-3"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card rounded-4 h-100">
            <div class="card-body">
              <h6 class="mb-3">Nettleserfordeling</h6>
              <div id="browserPieChart" style="height: 280px;"></div>
              <div id="browserLegend" class="d-flex flex-wrap justify-content-center gap-3 mt-3"></div>
            </div>
          </div>
        </div>

        <!-- Top Pages -->
        <div class="col-12">
          <div class="card rounded-4">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0">Mest besøkte sider</h6>
                <select id="topPagesPeriod" class="form-select form-select-sm" style="width: auto;">
                  <option value="today">I dag</option>
                  <option value="week" selected>Denne uken</option>
                  <option value="month">Denne måneden</option>
                </select>
              </div>
              <div class="table-responsive">
                <table class="table table-hover top-pages-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Side</th>
                      <th class="text-end">Besøk</th>
                      <th class="text-end">Unike</th>
                      <th style="width: 200px;">Andel</th>
                    </tr>
                  </thead>
                  <tbody id="topPagesBody">
                    <tr>
                      <td colspan="5" class="text-center text-muted py-4">Laster...</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Geo Stats Section -->
        <div class="col-lg-6">
          <div class="card rounded-4 h-100">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0">Besøk per land</h6>
                <select id="countryPeriod" class="form-select form-select-sm" style="width: auto;">
                  <option value="today">I dag</option>
                  <option value="week" selected>Denne uken</option>
                  <option value="month">Denne måneden</option>
                </select>
              </div>
              <div id="countryChart" style="height: 300px;"></div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card rounded-4 h-100">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0">Topp byer</h6>
                <select id="cityPeriod" class="form-select form-select-sm" style="width: auto;">
                  <option value="today">I dag</option>
                  <option value="week" selected>Denne uken</option>
                  <option value="month">Denne måneden</option>
                </select>
              </div>
              <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-hover mb-0">
                  <thead class="sticky-top bg-body">
                    <tr>
                      <th>By</th>
                      <th>Land</th>
                      <th class="text-end">Besøk</th>
                    </tr>
                  </thead>
                  <tbody id="topCitiesBody">
                    <tr>
                      <td colspan="3" class="text-center text-muted py-4">Laster...</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Country List -->
        <div class="col-12">
          <div class="card rounded-4">
            <div class="card-body">
              <h6 class="mb-3">Landfordeling</h6>
              <div id="countryList" class="row g-2"></div>
            </div>
          </div>
        </div>

        <!-- Hourly Heatmap -->
        <div class="col-12">
          <div class="card rounded-4">
            <div class="card-body">
              <h6 class="mb-3">Trafikk per time (siste 24 timer)</h6>
              <div id="hourlyHeatmap" style="height: 120px;"></div>
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
  <script src="assets/js/jquery.min.js"></script>
  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
  // Global chart instances
  var mainChart = null;
  var deviceChart = null;
  var browserChart = null;
  var heatmapChart = null;
  var countryChart = null;

  var refreshInterval = 30000; // 30 seconds
  var lastUpdate = new Date();
  var autoRefreshEnabled = true;

  $(document).ready(function() {
    // Initial load
    loadAllData();

    // Auto-refresh every 30 seconds
    setInterval(function() {
      if (autoRefreshEnabled) {
        refreshStats();
      }
    }, refreshInterval);

    // Update countdown display
    setInterval(updateRefreshCountdown, 1000);

    // Period button handlers
    $('.period-btn').on('click', function() {
      $('.period-btn').removeClass('active');
      $(this).addClass('active');
      loadMainChart($(this).data('period'));
    });

    // Top pages period change
    $('#topPagesPeriod').on('change', function() {
      loadTopPages($(this).val());
    });

    // Country period change
    $('#countryPeriod').on('change', function() {
      loadCountryStats($(this).val());
    });

    // City period change
    $('#cityPeriod').on('change', function() {
      loadCityStats($(this).val());
    });

    // Toggle auto-refresh
    $('#toggleAutoRefresh').on('click', function() {
      autoRefreshEnabled = !autoRefreshEnabled;
      updateRefreshButton();
    });

    // Manual refresh
    $('#manualRefresh').on('click', function() {
      refreshStats();
    });
  });

  function loadAllData() {
    loadStats('today', '#statToday', '#statTodayChange');
    loadStats('week', '#statWeek', '#statWeekChange');
    loadStats('month', '#statMonth', '#statMonthChange');
    loadUniqueVisitors();
    loadCountriesCount();
    loadMainChart('daily');
    loadDeviceStats();
    loadBrowserStats();
    loadTopPages('week');
    loadHourlyHeatmap();
    loadCountryStats('week');
    loadCityStats('week');
    lastUpdate = new Date();
  }

  function refreshStats() {
    // Quick refresh of stats only
    loadStats('today', '#statToday', '#statTodayChange');
    loadStats('week', '#statWeek', '#statWeekChange');
    loadStats('month', '#statMonth', '#statMonthChange');
    loadUniqueVisitors();
    loadCountriesCount();
    lastUpdate = new Date();
    showRefreshAnimation();
  }

  function showRefreshAnimation() {
    $('.stat-card').addClass('refreshing');
    setTimeout(function() {
      $('.stat-card').removeClass('refreshing');
    }, 500);
  }

  function updateRefreshCountdown() {
    var seconds = Math.floor((new Date() - lastUpdate) / 1000);
    var nextRefresh = Math.max(0, Math.floor((refreshInterval / 1000) - seconds));

    if (autoRefreshEnabled) {
      $('#refreshStatus').html('<span class="live-dot"></span> Sanntid - oppdateres om ' + nextRefresh + 's');
    } else {
      $('#refreshStatus').html('<span class="text-warning">Pauset</span> - sist oppdatert ' + seconds + 's siden');
    }
  }

  function updateRefreshButton() {
    if (autoRefreshEnabled) {
      $('#toggleAutoRefresh').html('<span class="material-icons-outlined fs-6">pause</span>');
      $('#toggleAutoRefresh').attr('title', 'Pause auto-oppdatering');
    } else {
      $('#toggleAutoRefresh').html('<span class="material-icons-outlined fs-6">play_arrow</span>');
      $('#toggleAutoRefresh').attr('title', 'Start auto-oppdatering');
    }
  }

  function loadStats(period, totalEl, changeEl) {
    $.ajax({
      url: 'api/traffic.php?action=stats&period=' + period,
      method: 'GET',
      success: function(response) {
        if (response.success) {
          $(totalEl).text(formatNumber(response.data.total_visits));
          var change = response.data.change_percent;
          var changeText = (change >= 0 ? '+' : '') + change + '%';
          $(changeEl).text(changeText);

          if (change >= 0) {
            $(changeEl).removeClass('bg-danger text-danger').addClass('bg-success bg-opacity-10 text-success');
          } else {
            $(changeEl).removeClass('bg-success text-success').addClass('bg-danger bg-opacity-10 text-danger');
          }
        }
      }
    });
  }

  function loadUniqueVisitors() {
    $.ajax({
      url: 'api/traffic.php?action=stats&period=month',
      method: 'GET',
      success: function(response) {
        if (response.success) {
          $('#statUnique').text(formatNumber(response.data.unique_visitors));
        }
      }
    });
  }

  function loadMainChart(period) {
    var endpoint = '';
    var params = '';

    switch(period) {
      case 'hourly':
        endpoint = 'hourly';
        break;
      case 'daily':
        endpoint = 'daily';
        params = '&days=14';
        break;
      case 'weekly':
        endpoint = 'weekly';
        params = '&weeks=12';
        break;
      case 'monthly':
        endpoint = 'monthly';
        params = '&months=12';
        break;
    }

    $.ajax({
      url: 'api/traffic.php?action=' + endpoint + params,
      method: 'GET',
      success: function(response) {
        if (response.success) {
          renderMainChart(response.data, period);
        }
      }
    });
  }

  function renderMainChart(data, period) {
    if (mainChart) {
      mainChart.destroy();
    }

    var options = {
      series: [{
        name: 'Besøk',
        data: data.visits
      }, {
        name: 'Unike besøkende',
        data: data.unique_visitors
      }],
      chart: {
        type: 'area',
        height: 300,
        toolbar: {
          show: true,
          tools: {
            download: true,
            selection: false,
            zoom: false,
            zoomin: false,
            zoomout: false,
            pan: false,
            reset: false
          }
        }
      },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2 },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.4,
          opacityTo: 0.1
        }
      },
      colors: ['#0d6efd', '#20c997'],
      xaxis: {
        categories: data.labels,
        labels: {
          rotate: -45,
          rotateAlways: data.labels.length > 12,
          style: { fontSize: '11px' }
        }
      },
      yaxis: {
        labels: { style: { fontSize: '11px' } }
      },
      legend: {
        position: 'top',
        horizontalAlign: 'right'
      },
      tooltip: {
        theme: 'dark',
        shared: true,
        intersect: false
      },
      grid: {
        borderColor: '#e0e0e0',
        strokeDashArray: 3
      }
    };

    mainChart = new ApexCharts(document.querySelector("#mainTrafficChart"), options);
    mainChart.render();
  }

  function loadDeviceStats() {
    $.ajax({
      url: 'api/traffic.php?action=devices&period=month',
      method: 'GET',
      success: function(response) {
        if (response.success && response.data.length > 0) {
          renderDeviceChart(response.data);
        } else {
          $('#devicePieChart').html('<p class="text-muted text-center py-5">Ingen data tilgjengelig</p>');
        }
      }
    });
  }

  function renderDeviceChart(data) {
    var labels = data.map(function(d) { return d.device; });
    var series = data.map(function(d) { return d.visits; });
    var colors = ['#0d6efd', '#6f42c1', '#20c997', '#ffc107'];

    var options = {
      series: series,
      chart: {
        type: 'donut',
        height: 280
      },
      labels: labels,
      colors: colors,
      legend: { show: false },
      dataLabels: {
        enabled: true,
        formatter: function(val) { return Math.round(val) + '%'; }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '65%',
            labels: {
              show: true,
              name: { show: true },
              value: { show: true, fontSize: '18px', fontWeight: 'bold' },
              total: {
                show: true,
                label: 'Totalt',
                fontSize: '14px'
              }
            }
          }
        }
      }
    };

    deviceChart = new ApexCharts(document.querySelector("#devicePieChart"), options);
    deviceChart.render();

    // Render legend
    var legendHtml = '';
    data.forEach(function(d, i) {
      legendHtml += '<span class="badge" style="background-color: ' + colors[i] + '">' + d.device + ': ' + d.visits + '</span>';
    });
    $('#deviceLegend').html(legendHtml);
  }

  function loadBrowserStats() {
    $.ajax({
      url: 'api/traffic.php?action=browsers&period=month',
      method: 'GET',
      success: function(response) {
        if (response.success && response.data.length > 0) {
          renderBrowserChart(response.data);
        } else {
          $('#browserPieChart').html('<p class="text-muted text-center py-5">Ingen data tilgjengelig</p>');
        }
      }
    });
  }

  function renderBrowserChart(data) {
    var labels = data.map(function(d) { return d.browser; });
    var series = data.map(function(d) { return d.visits; });
    var colors = ['#fd7e14', '#0dcaf0', '#198754', '#dc3545', '#6c757d', '#0d6efd'];

    var options = {
      series: series,
      chart: {
        type: 'donut',
        height: 280
      },
      labels: labels,
      colors: colors,
      legend: { show: false },
      dataLabels: {
        enabled: true,
        formatter: function(val) { return Math.round(val) + '%'; }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '65%',
            labels: {
              show: true,
              name: { show: true },
              value: { show: true, fontSize: '18px', fontWeight: 'bold' },
              total: {
                show: true,
                label: 'Totalt',
                fontSize: '14px'
              }
            }
          }
        }
      }
    };

    browserChart = new ApexCharts(document.querySelector("#browserPieChart"), options);
    browserChart.render();

    // Render legend
    var legendHtml = '';
    data.forEach(function(d, i) {
      legendHtml += '<span class="badge" style="background-color: ' + colors[i % colors.length] + '">' + d.browser + ': ' + d.visits + '</span>';
    });
    $('#browserLegend').html(legendHtml);
  }

  function loadTopPages(period) {
    $('#topPagesBody').html('<tr><td colspan="5" class="text-center text-muted py-4">Laster...</td></tr>');

    $.ajax({
      url: 'api/traffic.php?action=pages&period=' + period + '&limit=10',
      method: 'GET',
      success: function(response) {
        if (response.success && response.data.length > 0) {
          renderTopPages(response.data);
        } else {
          $('#topPagesBody').html('<tr><td colspan="5" class="text-center text-muted py-4">Ingen data tilgjengelig</td></tr>');
        }
      }
    });
  }

  function renderTopPages(data) {
    var totalVisits = data.reduce(function(sum, page) { return sum + page.visits; }, 0);
    var html = '';

    data.forEach(function(page, index) {
      var percent = totalVisits > 0 ? Math.round((page.visits / totalVisits) * 100) : 0;
      html += '<tr>';
      html += '<td class="fw-semibold">' + (index + 1) + '</td>';
      html += '<td><div class="page-url" title="' + escapeHtml(page.url) + '">' + escapeHtml(page.title || page.url) + '</div><small class="text-muted">' + escapeHtml(page.url) + '</small></td>';
      html += '<td class="text-end fw-semibold">' + formatNumber(page.visits) + '</td>';
      html += '<td class="text-end">' + formatNumber(page.unique_visitors) + '</td>';
      html += '<td><div class="progress" style="height: 8px;"><div class="progress-bar" role="progressbar" style="width: ' + percent + '%"></div></div><small class="text-muted">' + percent + '%</small></td>';
      html += '</tr>';
    });

    $('#topPagesBody').html(html);
  }

  function loadHourlyHeatmap() {
    $.ajax({
      url: 'api/traffic.php?action=hourly',
      method: 'GET',
      success: function(response) {
        if (response.success) {
          renderHourlyHeatmap(response.data);
        }
      }
    });
  }

  function renderHourlyHeatmap(data) {
    var options = {
      series: [{
        name: 'Besøk',
        data: data.visits
      }],
      chart: {
        type: 'bar',
        height: 120,
        toolbar: { show: false },
        sparkline: { enabled: false }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '80%',
          borderRadius: 2,
          distributed: true
        }
      },
      dataLabels: { enabled: false },
      colors: data.visits.map(function(v) {
        var max = Math.max.apply(null, data.visits) || 1;
        var intensity = v / max;
        if (intensity > 0.7) return '#0d6efd';
        if (intensity > 0.4) return '#6ea8fe';
        if (intensity > 0.1) return '#9ec5fe';
        return '#cfe2ff';
      }),
      xaxis: {
        categories: data.labels,
        labels: { style: { fontSize: '9px' } }
      },
      yaxis: { show: false },
      legend: { show: false },
      tooltip: {
        theme: 'dark',
        y: { formatter: function(val) { return val + ' besøk'; } }
      },
      grid: { show: false }
    };

    heatmapChart = new ApexCharts(document.querySelector("#hourlyHeatmap"), options);
    heatmapChart.render();
  }

  function loadCountriesCount() {
    $.ajax({
      url: 'api/traffic.php?action=stats&period=month',
      method: 'GET',
      success: function(response) {
        if (response.success) {
          $('#statCountries').text(formatNumber(response.data.countries || 0));
        }
      }
    });
  }

  function loadCountryStats(period) {
    $.ajax({
      url: 'api/traffic.php?action=countries&period=' + period + '&limit=10',
      method: 'GET',
      success: function(response) {
        if (response.success && response.data.length > 0) {
          renderCountryChart(response.data);
          renderCountryList(response.data);
        } else {
          $('#countryChart').html('<p class="text-muted text-center py-5">Ingen geodata tilgjengelig</p>');
          $('#countryList').html('<div class="col-12 text-center text-muted py-3">Ingen geodata tilgjengelig</div>');
        }
      }
    });
  }

  function renderCountryChart(data) {
    if (countryChart) {
      countryChart.destroy();
    }

    var labels = data.map(function(d) { return d.flag + ' ' + d.country; });
    var series = data.map(function(d) { return d.visits; });
    var colors = ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754', '#0dcaf0'];

    var options = {
      series: series,
      chart: {
        type: 'donut',
        height: 300
      },
      labels: labels,
      colors: colors,
      legend: {
        position: 'right',
        fontSize: '12px'
      },
      dataLabels: {
        enabled: true,
        formatter: function(val) { return Math.round(val) + '%'; }
      },
      plotOptions: {
        pie: {
          donut: {
            size: '55%',
            labels: {
              show: true,
              name: { show: true, fontSize: '12px' },
              value: { show: true, fontSize: '16px', fontWeight: 'bold' },
              total: {
                show: true,
                label: 'Totalt',
                fontSize: '12px'
              }
            }
          }
        }
      },
      responsive: [{
        breakpoint: 480,
        options: {
          chart: { height: 280 },
          legend: { position: 'bottom' }
        }
      }]
    };

    countryChart = new ApexCharts(document.querySelector("#countryChart"), options);
    countryChart.render();
  }

  function renderCountryList(data) {
    var html = '';
    data.forEach(function(country) {
      html += '<div class="col-6 col-md-4 col-lg-3">';
      html += '<div class="d-flex align-items-center p-2 rounded bg-light">';
      html += '<span class="me-2" style="font-size: 1.5rem;">' + country.flag + '</span>';
      html += '<div class="flex-grow-1">';
      html += '<div class="fw-semibold small">' + escapeHtml(country.country) + '</div>';
      html += '<div class="text-muted small">' + formatNumber(country.visits) + ' besøk (' + country.percentage + '%)</div>';
      html += '</div>';
      html += '</div>';
      html += '</div>';
    });
    $('#countryList').html(html);
  }

  function loadCityStats(period) {
    $('#topCitiesBody').html('<tr><td colspan="3" class="text-center text-muted py-4">Laster...</td></tr>');

    $.ajax({
      url: 'api/traffic.php?action=cities&period=' + period + '&limit=15',
      method: 'GET',
      success: function(response) {
        if (response.success && response.data.length > 0) {
          renderCityList(response.data);
        } else {
          $('#topCitiesBody').html('<tr><td colspan="3" class="text-center text-muted py-4">Ingen bydata tilgjengelig</td></tr>');
        }
      }
    });
  }

  function renderCityList(data) {
    var html = '';
    data.forEach(function(city) {
      html += '<tr>';
      html += '<td class="fw-semibold">' + escapeHtml(city.city) + '</td>';
      html += '<td class="text-muted small">' + escapeHtml(city.country) + '</td>';
      html += '<td class="text-end">' + formatNumber(city.visits) + '</td>';
      html += '</tr>';
    });
    $('#topCitiesBody').html(html);
  }

  // Utility functions
  function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
  }

  function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
  }
  </script>
</body>

</html>
