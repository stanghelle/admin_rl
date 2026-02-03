<?php
/**
 * Traffic Settings Page
 * Configure tracking and get embed code for your website
 */
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

// Get the current domain for the tracking script
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$domain = $_SERVER['HTTP_HOST'] ?? 'your-domain.com';
$baseUrl = $protocol . '://' . $domain;
?>
<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RL Admin - Sporingsinnstillinger</title>
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
    .code-block {
      background: #1e1e1e;
      color: #d4d4d4;
      padding: 1rem;
      border-radius: 0.5rem;
      font-family: 'Consolas', 'Monaco', monospace;
      font-size: 0.85rem;
      overflow-x: auto;
      position: relative;
    }
    .code-block code {
      color: #d4d4d4;
    }
    .code-block .string { color: #ce9178; }
    .code-block .keyword { color: #569cd6; }
    .code-block .comment { color: #6a9955; }
    .copy-btn {
      position: absolute;
      top: 0.5rem;
      right: 0.5rem;
    }
    .step-number {
      width: 32px;
      height: 32px;
      background: var(--bs-primary);
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      flex-shrink: 0;
    }
    .live-indicator {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    .live-indicator .dot {
      width: 8px;
      height: 8px;
      background: #10b981;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
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
        <div class="breadcrumb-title pe-3">Sporingsinnstillinger</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item"><a href="traffic_analytics.php">Trafikkanalyse</a></li>
              <li class="breadcrumb-item active" aria-current="page">Innstillinger</li>
            </ol>
          </nav>
        </div>
      </div>
      <!--end breadcrumb-->

      <div class="row">
        <div class="col-lg-8">
          <!-- Tracking Code Section -->
          <div class="card rounded-4 mb-4">
            <div class="card-body">
              <h5 class="mb-3">
                <span class="material-icons-outlined text-primary me-2">code</span>
                Sporingskode for din nettside
              </h5>
              <p class="text-secondary">Legg til denne koden på alle sider du vil spore. Plasser den rett før <code>&lt;/body&gt;</code> taggen.</p>

              <div class="code-block mb-3">
                <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyCode('simpleCode')">
                  <span class="material-icons-outlined fs-6">content_copy</span>
                </button>
                <code id="simpleCode">&lt;<span class="keyword">script</span> <span class="string">src</span>=<span class="string">"<?= htmlspecialchars($baseUrl) ?>/assets/js/tracker.js"</span> <span class="string">data-api</span>=<span class="string">"<?= htmlspecialchars($baseUrl) ?>/api/traffic.php"</span>&gt;&lt;/<span class="keyword">script</span>&gt;</code>
              </div>

              <div class="alert alert-info d-flex align-items-start gap-2">
                <span class="material-icons-outlined">info</span>
                <div>
                  <strong>Tips:</strong> Sporingsskriptet er lett (under 5KB) og blokkerer ikke sidelasting. Det bruker <code>sendBeacon</code> API for pålitelig sporing.
                </div>
              </div>
            </div>
          </div>

          <!-- Advanced Configuration -->
          <div class="card rounded-4 mb-4">
            <div class="card-body">
              <h5 class="mb-3">
                <span class="material-icons-outlined text-primary me-2">settings</span>
                Avansert konfigurasjon
              </h5>
              <p class="text-secondary">For mer kontroll over sporingen, bruk denne konfigurasjonen:</p>

              <div class="code-block mb-3">
                <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyCode('advancedCode')">
                  <span class="material-icons-outlined fs-6">content_copy</span>
                </button>
<pre id="advancedCode"><span class="comment">// Konfigurer før du laster sporingsskriptet</span>
<span class="keyword">window</span>.RLTracker = {
    <span class="string">apiUrl</span>: <span class="string">'<?= htmlspecialchars($baseUrl) ?>/api/traffic.php'</span>,
    <span class="string">trackOnLoad</span>: <span class="keyword">true</span>,        <span class="comment">// Spor automatisk ved sidelasting</span>
    <span class="string">trackSPA</span>: <span class="keyword">true</span>,            <span class="comment">// Spor SPA-navigasjon (React, Vue, etc.)</span>
    <span class="string">excludePaths</span>: [<span class="string">'/admin'</span>, <span class="string">'/login'</span>],  <span class="comment">// Stier som ikke skal spores</span>
    <span class="string">debug</span>: <span class="keyword">false</span>              <span class="comment">// Vis debug-meldinger i konsollen</span>
};

<span class="comment">// Last sporingsskriptet</span>
(<span class="keyword">function</span>() {
    <span class="keyword">var</span> script = document.createElement(<span class="string">'script'</span>);
    script.src = <span class="string">'<?= htmlspecialchars($baseUrl) ?>/assets/js/tracker.js'</span>;
    script.async = <span class="keyword">true</span>;
    document.body.appendChild(script);
})();</pre>
              </div>
            </div>
          </div>

          <!-- Manual Tracking -->
          <div class="card rounded-4 mb-4">
            <div class="card-body">
              <h5 class="mb-3">
                <span class="material-icons-outlined text-primary me-2">touch_app</span>
                Manuell sporing
              </h5>
              <p class="text-secondary">For hendelsesbasert sporing (f.eks. knappetrykk):</p>

              <div class="code-block mb-3">
                <button class="btn btn-sm btn-outline-light copy-btn" onclick="copyCode('manualCode')">
                  <span class="material-icons-outlined fs-6">content_copy</span>
                </button>
<pre id="manualCode"><span class="comment">// Spor en egendefinert sidevisning</span>
RLTracker.track({
    page_url: <span class="string">'/custom-page'</span>,
    page_title: <span class="string">'Min egendefinerte side'</span>
});

<span class="comment">// Spor utgående lenker</span>
RLTracker.trackOutbound(<span class="string">'https://example.com'</span>, <span class="keyword">function</span>() {
    window.location.href = <span class="string">'https://example.com'</span>;
});</pre>
              </div>
            </div>
          </div>

          <!-- Setup Steps -->
          <div class="card rounded-4">
            <div class="card-body">
              <h5 class="mb-4">
                <span class="material-icons-outlined text-primary me-2">checklist</span>
                Oppsettsveiledning
              </h5>

              <div class="d-flex gap-3 mb-4">
                <div class="step-number">1</div>
                <div>
                  <h6 class="mb-1">Kjør database-migrasjonen</h6>
                  <p class="text-secondary mb-0">Kjør SQL-filen <code>.same/migrations/create_website_traffic_table.sql</code> på databasen din.</p>
                </div>
              </div>

              <div class="d-flex gap-3 mb-4">
                <div class="step-number">2</div>
                <div>
                  <h6 class="mb-1">Legg til sporingskoden</h6>
                  <p class="text-secondary mb-0">Kopier sporingskoden ovenfor og lim den inn på alle sider du vil spore.</p>
                </div>
              </div>

              <div class="d-flex gap-3 mb-4">
                <div class="step-number">3</div>
                <div>
                  <h6 class="mb-1">Verifiser at det fungerer</h6>
                  <p class="text-secondary mb-0">Besøk nettsiden din og sjekk at besøk registreres i <a href="traffic_analytics.php">trafikkanalysen</a>.</p>
                </div>
              </div>

              <div class="d-flex gap-3">
                <div class="step-number">4</div>
                <div>
                  <h6 class="mb-1">Se statistikk i sanntid</h6>
                  <p class="text-secondary mb-0">Dashbordet og analysen oppdateres automatisk hvert 30. sekund.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <!-- Status Card -->
          <div class="card rounded-4 mb-4">
            <div class="card-body">
              <h6 class="mb-3">Sporingsstatus</h6>
              <div class="d-flex align-items-center justify-content-between mb-3">
                <span>API-endepunkt</span>
                <span class="live-indicator">
                  <span class="dot"></span>
                  Aktiv
                </span>
              </div>
              <div class="d-flex align-items-center justify-content-between mb-3">
                <span>Besøk i dag</span>
                <strong id="todayVisits">--</strong>
              </div>
              <div class="d-flex align-items-center justify-content-between mb-3">
                <span>Siste besøk</span>
                <span id="lastVisit" class="text-secondary">--</span>
              </div>
              <hr>
              <a href="traffic_analytics.php" class="btn btn-primary w-100">
                <span class="material-icons-outlined me-1">analytics</span>
                Se fullstendig analyse
              </a>
            </div>
          </div>

          <!-- Quick Links -->
          <div class="card rounded-4">
            <div class="card-body">
              <h6 class="mb-3">Hurtiglenker</h6>
              <ul class="list-unstyled mb-0">
                <li class="mb-2">
                  <a href="traffic_analytics.php" class="text-decoration-none d-flex align-items-center gap-2">
                    <span class="material-icons-outlined fs-6">analytics</span>
                    Trafikkanalyse
                  </a>
                </li>
                <li class="mb-2">
                  <a href="dashboard.php" class="text-decoration-none d-flex align-items-center gap-2">
                    <span class="material-icons-outlined fs-6">dashboard</span>
                    Dashboard
                  </a>
                </li>
                <li>
                  <a href="assets/js/tracker.js" target="_blank" class="text-decoration-none d-flex align-items-center gap-2">
                    <span class="material-icons-outlined fs-6">download</span>
                    Last ned tracker.js
                  </a>
                </li>
              </ul>
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
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
  function copyCode(elementId) {
    var codeElement = document.getElementById(elementId);
    var text = codeElement.innerText || codeElement.textContent;

    navigator.clipboard.writeText(text).then(function() {
      // Show success feedback
      var btn = event.target.closest('.copy-btn');
      var originalHtml = btn.innerHTML;
      btn.innerHTML = '<span class="material-icons-outlined fs-6">check</span>';
      btn.classList.remove('btn-outline-light');
      btn.classList.add('btn-success');

      setTimeout(function() {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-light');
      }, 2000);
    });
  }

  // Load status data
  $(document).ready(function() {
    loadStatus();
    setInterval(loadStatus, 30000); // Refresh every 30 seconds
  });

  function loadStatus() {
    $.ajax({
      url: 'api/traffic.php?action=stats&period=today',
      method: 'GET',
      success: function(response) {
        if (response.success) {
          $('#todayVisits').text(response.data.total_visits);
        }
      }
    });
  }
  </script>
</body>

</html>
