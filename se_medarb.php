<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();

// Validate ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    Session::flash('error', 'Ugyldig ID.');
    Redirect::to('medarb.php');
}

// Get employee data using prepared statement
$employee = $db->get('medarb', ['id', '=', $id]);
if (!$employee || !$employee->count()) {
    Session::flash('error', 'Medarbeider ikke funnet.');
    Redirect::to('medarb.php');
}

$data = $employee->first();
$navn = $data->navn ?? '';
$stilling = $data->stilling ?? '';
$protek = $data->protek ?? '';
$program = $data->program ?? '';
$img = $data->img ?? '';
$tlf = $data->tlf ?? '';
$epost = $data->epost ?? '';

$csrfToken = Token::get();
$statusMsg = '';
$statusType = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    if (!Token::validate()) {
        $statusType = 'danger';
        $statusMsg = 'Ugyldig sikkerhetstoken. Vennligst prøv igjen.';
    } elseif (!empty($_FILES["uploadfile"]["name"])) {
        $filename = $_FILES["uploadfile"]["name"];
        $tempname = $_FILES["uploadfile"]["tmp_name"];
        $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileType, $allowedTypes)) {
            $statusType = 'danger';
            $statusMsg = 'Ugyldig filtype. Kun JPG, PNG, GIF og WEBP er tillatt.';
        } else {
            // Generate unique filename
            $newfilename = md5($filename . time()) . '.' . $fileType;
            $folder = "../img/user/" . $newfilename;

            if (move_uploaded_file($tempname, $folder)) {
                // Update database using prepared statement
                $result = $db->update('medarb', $id, ['img' => $newfilename]);

                if ($result) {
                    // Delete old image if exists
                    if (!empty($img) && file_exists("../img/user/" . $img)) {
                        @unlink("../img/user/" . $img);
                    }
                    $img = $newfilename; // Update for display
                    $statusType = 'success';
                    $statusMsg = 'Bilde er blitt oppdatert.';
                } else {
                    $statusType = 'danger';
                    $statusMsg = 'Kunne ikke oppdatere databasen.';
                }
            } else {
                $statusType = 'danger';
                $statusMsg = 'Kunne ikke laste opp bildet.';
            }
        }
    } else {
        $statusType = 'warning';
        $statusMsg = 'Velg et bilde å laste opp.';
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit-user'])) {
    if (!Token::validate()) {
        $statusType = 'danger';
        $statusMsg = 'Ugyldig sikkerhetstoken. Vennligst prøv igjen.';
    } else {
        $updateId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($updateId !== $id) {
            $statusType = 'danger';
            $statusMsg = 'Ugyldig forespørsel.';
        } else {
            // Sanitize inputs
            $newNavn = trim($_POST['navn'] ?? '');
            $newStilling = trim($_POST['stilling'] ?? '');
            $newProtek = trim($_POST['protek'] ?? '');
            $newProg = trim($_POST['prog'] ?? '');
            $newEpost = trim($_POST['epost'] ?? '');
            $newTlf = trim($_POST['tlf'] ?? '');

            // Validate email if provided
            if (!empty($newEpost) && !filter_var($newEpost, FILTER_VALIDATE_EMAIL)) {
                $statusType = 'danger';
                $statusMsg = 'Ugyldig epostadresse.';
            } else {
                // Update using prepared statement
                $result = $db->update('medarb', $id, [
                    'navn' => $newNavn,
                    'stilling' => $newStilling,
                    'protek' => $newProtek,
                    'program' => $newProg,
                    'epost' => $newEpost,
                    'tlf' => $newTlf
                ]);

                if ($result) {
                    // Update local variables for display
                    $navn = $newNavn;
                    $stilling = $newStilling;
                    $protek = $newProtek;
                    $program = $newProg;
                    $epost = $newEpost;
                    $tlf = $newTlf;

                    $statusType = 'success';
                    $statusMsg = 'Medarbeider er oppdatert!';
                } else {
                    $statusType = 'danger';
                    $statusMsg = 'Kunne ikke oppdatere medarbeider.';
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="no" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($navn); ?> | Medarbeider | RL Admin</title>
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

  <style>
    .profile-avatar img {
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .profile-avatar img:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    .info-list-item {
      padding: 0.5rem 0;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .info-list-item:last-child {
      border-bottom: none;
    }
    .alert-floating {
      position: fixed;
      top: 80px;
      right: 20px;
      z-index: 1050;
      min-width: 300px;
      animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
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
        <div class="breadcrumb-title pe-3">Medarbeidere</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item"><a href="medarb.php">Medarbeidere</a></li>
              <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($navn); ?></li>
            </ol>
          </nav>
        </div>
        <div class="ms-auto">
          <a href="medarb.php" class="btn btn-outline-secondary">
            <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">arrow_back</span>
            Tilbake
          </a>
        </div>
      </div>
      <!--end breadcrumb-->

      <!-- Status Messages -->
      <?php if (!empty($statusMsg)): ?>
      <div class="alert alert-<?php echo htmlspecialchars($statusType); ?> alert-dismissible fade show alert-floating" role="alert" id="statusAlert">
        <?php echo htmlspecialchars($statusMsg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <!-- Profile Header Card -->
      <div class="card rounded-4">
        <div class="card-body p-4">
          <div class="position-relative mb-5">
            <div class="profile-avatar position-absolute top-100 start-50 translate-middle">
              <img src="../img/user/<?php echo htmlspecialchars($img); ?>"
                   onerror="this.onerror=null;this.src='assets/images/noimg.png';"
                   class="img-fluid rounded-circle p-1 bg-grd-danger shadow"
                   width="170" height="170" alt="<?php echo htmlspecialchars($navn); ?>"
                   data-bs-toggle="modal" data-bs-target="#bildeModal"
                   title="Klikk for å endre bilde">
            </div>
          </div>
          <div class="profile-info pt-5 d-flex align-items-center justify-content-between">
            <div>
              <h3 class="mb-1"><?php echo htmlspecialchars($navn); ?></h3>
              <?php if (!empty($stilling)): ?>
              <p class="text-muted mb-0"><?php echo htmlspecialchars($stilling); ?></p>
              <?php endif; ?>
            </div>
            <div>
              <?php if (!empty($epost)): ?>
              <a href="mailto:<?php echo htmlspecialchars($epost); ?>" class="btn btn-grd-primary rounded-5 px-4">
                <i class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">email</i>
                Send epost
              </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Edit Form -->
        <div class="col-12 col-xl-8">
          <div class="card rounded-4 border-top border-4 border-primary border-gradient-1">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                  <h5 class="mb-0 fw-bold">Rediger profil</h5>
                  <p class="text-muted small mb-0">Oppdater informasjon om medarbeideren</p>
                </div>
              </div>

              <form class="row g-4" method="post" id="profileForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

                <div class="col-md-6">
                  <label for="navn" class="form-label">Navn <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="navn" name="navn"
                         placeholder="Fullt navn" value="<?php echo htmlspecialchars($navn); ?>" required>
                </div>

                <div class="col-md-6">
                  <label for="tlf" class="form-label">Telefon</label>
                  <input type="tel" class="form-control" id="tlf" name="tlf"
                         placeholder="Telefonnummer" value="<?php echo htmlspecialchars($tlf); ?>">
                </div>

                <div class="col-md-6">
                  <label for="epost" class="form-label">Epost</label>
                  <input type="email" class="form-control" id="epost" name="epost"
                         placeholder="epost@eksempel.no" value="<?php echo htmlspecialchars($epost); ?>">
                </div>

                <div class="col-md-6">
                  <label for="stilling" class="form-label">Stilling</label>
                  <input type="text" class="form-control" id="stilling" name="stilling"
                         placeholder="F.eks. Programleder" value="<?php echo htmlspecialchars($stilling); ?>">
                </div>

                <div class="col-md-6">
                  <label for="protek" class="form-label">Funksjon</label>
                  <input type="text" class="form-control" id="protek" name="protek"
                         placeholder="F.eks. Tekniker, Programleder" value="<?php echo htmlspecialchars($protek); ?>">
                </div>

                <div class="col-md-6">
                  <label for="prog" class="form-label">Programmer</label>
                  <textarea class="form-control" id="prog" name="prog"
                            placeholder="Programmer medarbeideren er med på..." rows="3"><?php echo htmlspecialchars($program); ?></textarea>
                </div>

                <div class="col-md-12">
                  <div class="d-md-flex d-grid align-items-center gap-3">
                    <button type="submit" class="btn btn-grd-primary px-4" name="submit-user">
                      <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">save</span>
                      Lagre endringer
                    </button>
                    <a href="medarb.php" class="btn btn-light px-4">Avbryt</a>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-12 col-xl-4" id="updatekundeinfo">
          <div class="card rounded-4">
            <div class="card-body">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <h5 class="mb-0 fw-bold">Kontaktinfo</h5>
              </div>
              <div class="full-info">
                <div class="info-list d-flex flex-column gap-2">
                  <div class="info-list-item d-flex align-items-center gap-3">
                    <span class="material-icons-outlined text-primary">account_circle</span>
                    <div>
                      <small class="text-muted d-block">Navn</small>
                      <p class="mb-0"><?php echo htmlspecialchars($navn ?: '-'); ?></p>
                    </div>
                  </div>
                  <div class="info-list-item d-flex align-items-center gap-3">
                    <span class="material-icons-outlined text-info">work</span>
                    <div>
                      <small class="text-muted d-block">Stilling</small>
                      <p class="mb-0"><?php echo htmlspecialchars($stilling ?: '-'); ?></p>
                    </div>
                  </div>
                  <div class="info-list-item d-flex align-items-center gap-3">
                    <span class="material-icons-outlined text-warning">badge</span>
                    <div>
                      <small class="text-muted d-block">Funksjon</small>
                      <p class="mb-0"><?php echo htmlspecialchars($protek ?: '-'); ?></p>
                    </div>
                  </div>
                  <div class="info-list-item d-flex align-items-center gap-3">
                    <span class="material-icons-outlined text-success">radio</span>
                    <div>
                      <small class="text-muted d-block">Programmer</small>
                      <p class="mb-0"><?php echo htmlspecialchars($program ?: '-'); ?></p>
                    </div>
                  </div>
                  <div class="info-list-item d-flex align-items-center gap-3">
                    <span class="material-icons-outlined text-danger">email</span>
                    <div>
                      <small class="text-muted d-block">Epost</small>
                      <p class="mb-0">
                        <?php if (!empty($epost)): ?>
                        <a href="mailto:<?php echo htmlspecialchars($epost); ?>"><?php echo htmlspecialchars($epost); ?></a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                      </p>
                    </div>
                  </div>
                  <div class="info-list-item d-flex align-items-center gap-3">
                    <span class="material-icons-outlined text-secondary">call</span>
                    <div>
                      <small class="text-muted d-block">Telefon</small>
                      <p class="mb-0">
                        <?php if (!empty($tlf)): ?>
                        <a href="tel:<?php echo htmlspecialchars($tlf); ?>"><?php echo htmlspecialchars($tlf); ?></a>
                        <?php else: ?>
                        -
                        <?php endif; ?>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!--end row-->

    </div>
  </main>
  <!--end main wrapper-->

  <!--start overlay-->
  <div class="overlay btn-toggle"></div>
  <!--end overlay-->

  <!-- Image Upload Modal -->
  <div class="modal fade" id="bildeModal" tabindex="-1" aria-labelledby="bildeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

          <div class="modal-header">
            <h5 class="modal-title" id="bildeModalLabel">
              <span class="material-icons-outlined me-2" style="vertical-align: middle;">photo_camera</span>
              Endre profilbilde
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Lukk"></button>
          </div>
          <div class="modal-body">
            <div class="text-center mb-4">
              <img src="../img/user/<?php echo htmlspecialchars($img); ?>"
                   class="rounded-circle" width="150" height="150"
                   style="object-fit: cover;"
                   alt="Nåværende bilde"
                   onerror="this.onerror=null;this.src='assets/images/noimg.png';"
                   id="previewImage">
            </div>

            <div class="mb-3">
              <label for="uploadfile" class="form-label">Velg nytt bilde</label>
              <input class="form-control" type="file" name="uploadfile" id="uploadfile" accept="image/*">
              <small class="text-muted">Aksepterte formater: JPG, PNG, GIF, WEBP. Maks 5MB.</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt</button>
            <button type="submit" class="btn btn-primary" name="upload">
              <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">upload</span>
              Last opp
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

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
  $(document).ready(function() {
    // Image preview
    $('#uploadfile').on('change', function() {
      var file = this.files[0];
      if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
          $('#previewImage').attr('src', e.target.result);
        }
        reader.readAsDataURL(file);
      }
    });

    // Auto-hide status alert
    var statusAlert = document.getElementById('statusAlert');
    if (statusAlert) {
      setTimeout(function() {
        $(statusAlert).fadeOut(500, function() {
          $(this).remove();
        });
      }, 5000);
    }
  });
  </script>
</body>

</html>
