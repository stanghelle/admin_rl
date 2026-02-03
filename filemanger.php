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
  <title>File Manager | RL Admin</title>

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
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!--main css-->
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="sass/main.css" rel="stylesheet">
  <link href="sass/dark-theme.css" rel="stylesheet">
  <link href="sass/blue-theme.css" rel="stylesheet">
  <link href="sass/semi-dark.css" rel="stylesheet">
  <link href="sass/bordered-theme.css" rel="stylesheet">
  <link href="sass/responsive.css" rel="stylesheet">

  <!-- File Manager CSS -->
  <link href="assets/css/filemanager.css" rel="stylesheet">
</head>

<body>

  <?php include 'nav.php'; ?>

  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content">

      <!--breadcrumb-->
      <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Fil Lager</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item active" aria-current="page">File Manager</li>
            </ol>
          </nav>
        </div>
      </div>
      <!--end breadcrumb-->

      <!-- File Manager -->
      <div class="fm-wrap">

        <!-- Sidebar -->
        <aside class="fm-side">
          <div class="fm-nav-title">Quick Access</div>
          <ul class="fm-nav">
            <li class="active" data-filter="all">
              <i data-lucide="home"></i>
              <span>All Files</span>
            </li>
            <li data-filter="documents">
              <i data-lucide="file-text"></i>
              <span>Documents</span>
            </li>
            <li data-filter="images">
              <i data-lucide="image"></i>
              <span>Images</span>
            </li>
            <li data-filter="videos">
              <i data-lucide="video"></i>
              <span>Videos</span>
            </li>
            <li data-filter="audio">
              <i data-lucide="music"></i>
              <span>Audio</span>
            </li>
            <li data-filter="archives">
              <i data-lucide="archive"></i>
              <span>Archives</span>
            </li>
          </ul>

          <div class="fm-nav-title">Storage</div>
          <div class="fm-storage">
            <div class="fm-storage-bar">
              <div class="fm-storage-used"></div>
            </div>
            <p class="fm-storage-text">4.5 GB of 10 GB used</p>
          </div>
        </aside>

        <!-- Main Content -->
        <div class="fm-main">

          <!-- Toolbar -->
          <div class="fm-toolbar">
            <div class="fm-crumbs" id="crumbs">
              <span class="cur">files</span>
            </div>

            <div class="fm-search">
              <i data-lucide="search" class="fm-search-icon"></i>
              <input type="search" id="searchInput" placeholder="Search files...">
            </div>

            <div class="fm-actions">
              <button class="fm-btn primary" id="uploadBtn">
                <i data-lucide="upload"></i>
                <span>Upload</span>
              </button>
              <button class="fm-btn" id="newFolderBtn">
                <i data-lucide="folder-plus"></i>
                <span>New Folder</span>
              </button>
              <button class="fm-btn" id="refreshBtn">
                <i data-lucide="refresh-cw"></i>
              </button>
              <div class="fm-view">
                <button class="active" data-view="grid" title="Grid View">
                  <i data-lucide="layout-grid"></i>
                </button>
                <button data-view="list" title="List View">
                  <i data-lucide="list"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- File Container -->
          <div class="fm-content">

            <!-- Drop Zone -->
            <div class="fm-drop" id="dropZone">
              <div class="fm-drop-inner">
                <i data-lucide="upload-cloud"></i>
                <p>Drop files here to upload</p>
              </div>
            </div>

            <!-- File List -->
            <ul class="fm-list grid" id="fileList"></ul>

            <!-- Empty State -->
            <div class="fm-empty" id="emptyState">
              <div class="fm-empty-icon">
                <i data-lucide="folder-x"></i>
              </div>
              <h3>No files found</h3>
              <p>This folder is empty or no files match your search.</p>
              <button class="fm-btn primary" id="emptyUploadBtn">
                <i data-lucide="upload"></i>
                <span>Upload Files</span>
              </button>
            </div>

            <!-- Loading State -->
            <div class="fm-loading" id="loadingState">
              <div class="fm-spinner"></div>
              <p>Loading files...</p>
            </div>

          </div>
        </div>
      </div>

      <!-- Hidden File Input -->
      <input type="file" id="fileInput" multiple style="display: none;">

      <!-- Toast Container -->
      <div class="fm-toast-wrap" id="toastWrap"></div>

      <!-- Context Menu -->
      <div class="fm-ctx" id="ctxMenu">
        <button data-action="open">
          <i data-lucide="external-link"></i>
          <span>Open</span>
        </button>
        <button data-action="download">
          <i data-lucide="download"></i>
          <span>Download</span>
        </button>
        <div class="fm-ctx-div"></div>
        <button data-action="rename">
          <i data-lucide="pencil"></i>
          <span>Rename</span>
        </button>
        <div class="fm-ctx-div"></div>
        <button class="danger" data-action="delete">
          <i data-lucide="trash-2"></i>
          <span>Delete</span>
        </button>
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

  <!-- Initialize Lucide Icons -->
  <script>
    lucide.createIcons();
  </script>

  <!-- File Manager JS -->
  <script src="assets/js/filemanager.js"></script>

</body>
</html>
