<?php
require_once 'core/init.php';
include 'core/db_con.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();

// Helper function to render sortable list for a day
function renderDayList($dagid, $tableName = 'program_oversikt') {
    $data = DB::getInstance()->query("SELECT * FROM {$tableName} WHERE dagid = ? ORDER BY sort_order ASC, id ASC", [$dagid]);

    if ($data->count()) {
        echo '<div class="sortable-list" data-table="'.$tableName.'" data-dagid="'.$dagid.'">';
        foreach ($data->results() as $result) {
            echo '
            <div class="sortable-item" data-id="'.$result->id.'">
                <div class="drag-handle" title="Dra for å endre rekkefølge">
                    <span class="material-icons-outlined">drag_indicator</span>
                </div>
                <div class="sortable-item-content">
                    <div class="sortable-item-time">
                        <div class="editable-content" contenteditable="true" data-id="'.$result->id.'" data-field="kl">'.htmlspecialchars($result->kl).'</div>
                    </div>
                    <div class="sortable-item-program">
                        <div class="editable-content" contenteditable="true" data-id="'.$result->id.'" data-field="program">'.htmlspecialchars($result->program).'</div>
                    </div>
                </div>
                <div class="sortable-item-actions">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="showSwalAlert(this)" data-uid="'.$result->id.'" title="Slett">
                        <span class="material-icons-outlined fs-6">delete</span>
                    </button>
                </div>
            </div>';
        }
        echo '</div>';
    } else {
        echo '<div class="sortable-list sortable-empty" data-table="'.$tableName.'" data-dagid="'.$dagid.'"><p class="text-muted">Det finnes ingen programmer listet for denne dagen.</p></div>';
    }

    // Add new item button
    echo '<button type="button" class="btn btn-outline-primary mt-3 add-program-btn" onclick="openAddModal('.$dagid.')">
        <span class="material-icons-outlined me-1">add</span> Legg til nytt program
    </button>';
}
?>
<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RL Admin - Programoversikt</title>
  <!--favicon-->
  <link rel="icon" href="assets/images/favicon-32x32.png" type="image/png">
  <!-- jQuery and jQuery UI -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>
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
  <!-- Sortable CSS -->
  <link href="assets/css/sortable.css" rel="stylesheet">
  <!-- SweetAlert -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.min.css">
</head>

<body>

 <!--start header-->
 <?php include 'nav.php'; ?>

  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content">
      <!--breadcrumb-->
      <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Programoversikt</div>
        <div class="ps-3">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
              <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
              <li class="breadcrumb-item active" aria-current="page">Endre Programoversikt</li>
            </ol>
          </nav>
        </div>
      </div>
      <!--end breadcrumb-->

      <div class="card rounded-4">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">Endre Programoversikt som ligger ute</h6>
            <span class="sort-indicator">
              <span class="material-icons-outlined">swap_vert</span>
              Dra elementer for å endre rekkefølge
            </span>
          </div>
          <hr>

          <div class="card">
            <div class="card-body" id="myList">
              <ul class="nav nav-tabs nav-success" role="tablist">
                <li class="nav-item" role="presentation">
                  <a class="nav-link active" data-bs-toggle="tab" href="#man" role="tab" aria-selected="true">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">calendar_today</i></div>
                      <div class="tab-title">Mandag</div>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#tirs" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">calendar_today</i></div>
                      <div class="tab-title">Tirsdag</div>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#ons" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">calendar_today</i></div>
                      <div class="tab-title">Onsdag</div>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#tors" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">calendar_today</i></div>
                      <div class="tab-title">Torsdag</div>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#fre" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">calendar_today</i></div>
                      <div class="tab-title">Fredag</div>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#lor" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">calendar_today</i></div>
                      <div class="tab-title">Lørdag</div>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#son" role="tab" aria-selected="false">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">calendar_today</i></div>
                      <div class="tab-title">Søndag</div>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" href="copy_uke.php">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">publish</i></div>
                      <div class="tab-title">Legg ut ny uke</div>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" href="prg_pdf.php">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">edit_note</i></div>
                      <div class="tab-title">Endre neste uke</div>
                    </div>
                  </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link text-success" href="#" onclick="generateOversiktPDF(); return false;">
                    <div class="d-flex align-items-center">
                      <div class="tab-icon"><i class="material-icons-outlined me-1 fs-6">picture_as_pdf</i></div>
                      <div class="tab-title">Lag PDF</div>
                    </div>
                  </a>
                </li>
              </ul>

              <div class="tab-content py-3">
                <div class="tab-pane fade show active" id="man" role="tabpanel">
                  <?php renderDayList(1, 'program_oversikt'); ?>
                </div>
                <div class="tab-pane fade" id="tirs" role="tabpanel">
                  <?php renderDayList(2, 'program_oversikt'); ?>
                </div>
                <div class="tab-pane fade" id="ons" role="tabpanel">
                  <?php renderDayList(3, 'program_oversikt'); ?>
                </div>
                <div class="tab-pane fade" id="tors" role="tabpanel">
                  <?php renderDayList(4, 'program_oversikt'); ?>
                </div>
                <div class="tab-pane fade" id="fre" role="tabpanel">
                  <?php renderDayList(5, 'program_oversikt'); ?>
                </div>
                <div class="tab-pane fade" id="lor" role="tabpanel">
                  <?php renderDayList(6, 'program_oversikt'); ?>
                </div>
                <div class="tab-pane fade" id="son" role="tabpanel">
                  <?php renderDayList(7, 'program_oversikt'); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
  <!--end main wrapper-->

  <!-- Add Program Modal -->
  <div class="modal fade" id="addProgramModal" tabindex="-1" aria-labelledby="addProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addProgramModalLabel">Legg til nytt program</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addProgramForm">
            <input type="hidden" id="addDagId" name="dagid" value="">
            <div class="mb-3">
              <label for="addKl" class="form-label">Klokkeslett</label>
              <input type="text" class="form-control" id="addKl" name="kl" placeholder="f.eks. 10:00" required>
            </div>
            <div class="mb-3">
              <label for="addProgram" class="form-label">Program</label>
              <input type="text" class="form-control" id="addProgram" name="program" placeholder="Programnavn" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt</button>
          <button type="button" class="btn btn-primary" onclick="addProgram()">Legg til</button>
        </div>
      </div>
    </div>
  </div>

  <!--start overlay-->
  <div class="overlay btn-toggle"></div>
  <!--end overlay-->

  <?php include 'footer.php'; ?>

  <!--bootstrap js-->
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>
  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.all.min.js"></script>
  <!-- Sortable List JS -->
  <script src="assets/js/sortable-list.js"></script>

  <script>
  $(document).ready(function() {
      // Editable content handler
      $('.editable-content').on('blur', function() {
          var id = $(this).data('id');
          var field = $(this).data('field');
          var newContent = $(this).text().trim();

          $.ajax({
              url: 'posttest.php',
              method: 'POST',
              data: { id: id, field: field, content: newContent },
              success: function(response) {
                  Swal.fire({
                      position: 'top-end',
                      icon: 'success',
                      title: 'Oppdatert',
                      showConfirmButton: false,
                      timer: 1000,
                      toast: true
                  });
              },
              error: function() {
                  Swal.fire({
                      position: 'top-end',
                      icon: 'error',
                      title: 'Kunne ikke oppdatere',
                      showConfirmButton: false,
                      timer: 2000,
                      toast: true
                  });
              }
          });
      });

      // Prevent enter key from creating new lines, blur instead
      $('.editable-content').on('keydown', function(e) {
          if (e.key === 'Enter') {
              e.preventDefault();
              $(this).blur();
          }
      });

      // Re-initialize sortable when tab changes
      $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
          $('.sortable-list').sortable('refresh');
      });
  });

  // Generate PDF of current program overview
  function generateOversiktPDF() {
      var popupwin = window.open('print_program_oversikt.php', 'pdfwindow', 'width=10,height=1,left=5,top=3');
      setTimeout(function() { popupwin.close(); }, 3000);
  }

  // Open add program modal
  function openAddModal(dagid) {
      document.getElementById('addDagId').value = dagid;
      document.getElementById('addKl').value = '';
      document.getElementById('addProgram').value = '';
      var modal = new bootstrap.Modal(document.getElementById('addProgramModal'));
      modal.show();
  }

  // Add new program
  function addProgram() {
      var dagid = document.getElementById('addDagId').value;
      var kl = document.getElementById('addKl').value.trim();
      var program = document.getElementById('addProgram').value.trim();

      if (!kl || !program) {
          Swal.fire({
              icon: 'warning',
              title: 'Manglende data',
              text: 'Fyll inn både klokkeslett og programnavn'
          });
          return;
      }

      $.ajax({
          url: 'api/add_program.php',
          method: 'POST',
          data: { table: 'program_oversikt', dagid: dagid, kl: kl, program: program },
          success: function(response) {
              if (response.success) {
                  // Close modal
                  bootstrap.Modal.getInstance(document.getElementById('addProgramModal')).hide();

                  // Reload the page to show new item
                  Swal.fire({
                      position: 'top-end',
                      icon: 'success',
                      title: 'Program lagt til',
                      showConfirmButton: false,
                      timer: 1000,
                      toast: true
                  }).then(function() {
                      location.reload();
                  });
              } else {
                  Swal.fire({
                      icon: 'error',
                      title: 'Feil',
                      text: response.error || 'Kunne ikke legge til program'
                  });
              }
          },
          error: function() {
              Swal.fire({
                  icon: 'error',
                  title: 'Feil',
                  text: 'Serverfeil ved lagring'
              });
          }
      });
  }

  // Delete item handler
  function showSwalAlert(button) {
      let uid = button.getAttribute('data-uid');

      Swal.fire({
          title: 'Slett innslag?',
          text: 'Er du sikker på at du vil slette dette innslaget?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Ja, slett',
          cancelButtonText: 'Avbryt'
      }).then((result) => {
          if (result.isConfirmed) {
              $.ajax({
                  type: 'POST',
                  url: 'del_innslag.php',
                  data: { uid: uid },
                  success: function() {
                      // Remove the item from DOM
                      $('[data-id="' + uid + '"]').fadeOut(300, function() {
                          $(this).remove();
                      });

                      Swal.fire({
                          position: 'top-end',
                          icon: 'success',
                          title: 'Slettet',
                          showConfirmButton: false,
                          timer: 1000,
                          toast: true
                      });
                  },
                  error: function() {
                      Swal.fire({
                          icon: 'error',
                          title: 'Feil',
                          text: 'Kunne ikke slette innslaget'
                      });
                  }
              });
          }
      });
  }
  </script>
</body>

</html>
