<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();

// Handle AJAX delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    header('Content-Type: application/json');

    if (!Token::validate()) {
        echo json_encode(['success' => false, 'error' => 'Ugyldig sikkerhetstoken']);
        exit;
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Ugyldig ID']);
        exit;
    }

    try {
        // Get program info first (for image deletion)
        $program = $db->get('program', ['id', '=', $id]);

        if ($program->count()) {
            $programData = $program->first();

            // Delete from database
            $result = $db->delete('program', ['id', '=', $id]);

            if ($result) {
                // Try to delete image file if it exists
                $imgPath = '../img/prg/' . $programData->img;
                if (!empty($programData->img) && file_exists($imgPath)) {
                    @unlink($imgPath);
                }
                echo json_encode(['success' => true, 'message' => 'Programmet ble slettet']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Kunne ikke slette programmet']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Programmet ble ikke funnet']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'En feil oppstod ved sletting']);
    }
    exit;
}

// Get all programs
$programs = $db->query('SELECT * FROM program ORDER BY id ASC');
$csrfToken = Token::get();

// Debug removed - data confirmed OK
?>
<!doctype html>
<html lang="no" data-bs-theme="blue-theme">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Programmer | RL Admin</title>
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
        .program-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .program-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .program-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 12px;
        }
        .program-img-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        .program-img-large {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .search-box {
            max-width: 300px;
        }
        .day-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        .fade-out {
            animation: fadeOut 0.3s ease forwards;
        }
        @keyframes fadeOut {
            to { opacity: 0; transform: scale(0.95); }
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
                <div class="breadcrumb-title pe-3">Programmer</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Oversikt</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#createProgramModal">
                        <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">add</span>
                        Nytt program
                    </button>
                </div>
            </div>
            <!--end breadcrumb-->

            <!-- Flash Messages -->
            <?php Template::output('msg'); ?>

            <!-- Search and Stats Bar -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="search-box">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <span class="material-icons-outlined">search</span>
                            </span>
                            <input type="text" class="form-control border-start-0" id="searchPrograms" placeholder="Søk etter program...">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2">
                        <span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">radio</span>
                        <?php echo $programs->count(); ?> programmer totalt
                    </span>
                </div>
            </div>

            <!-- Programs Grid -->
            <div class="card rounded-4">
                <div class="card-body">
                    <?php if ($programs->count()) : ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 row-cols-xxl-4 g-4" id="programsGrid">
                        <?php foreach ($programs->results() as $program) : ?>
                        <div class="col program-item" data-name="<?php echo htmlspecialchars(strtolower(($program->navn ?? ''))); ?>">
                            <div class="card program-card rounded-4 mb-0 border h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3">
                                        <?php if (!empty($program->img)) : ?>
                                        <img src="../img/prg/<?php echo htmlspecialchars(($program->img ?? '')); ?>"
                                             class="program-img" alt="<?php echo htmlspecialchars(($program->navn ?? '')); ?>"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                        <div class="program-img-placeholder" style="display:none;">
                                            <?php echo strtoupper(substr(($program->navn ?? '') ?: 'P', 0, 1)); ?>
                                        </div>
                                        <?php else : ?>
                                        <div class="program-img-placeholder">
                                            <?php echo strtoupper(substr(($program->navn ?? '') ?: 'P', 0, 1)); ?>
                                        </div>
                                        <?php endif; ?>

                                        <div class="flex-grow-1">
                                            <h5 class="mb-1 fw-semibold"><?php echo htmlspecialchars($program->navn ?? ''); ?></h5>
                                            <p class="text-muted mb-0 small">
                                                <span class="material-icons-outlined me-1" style="font-size: 14px; vertical-align: middle;">schedule</span>
                                                <?php echo htmlspecialchars($program->tid ?? ''); ?>
                                            </p>
                                        </div>

                                        <div class="dropdown">
                                            <a href="javascript:;" class="text-secondary" data-bs-toggle="dropdown">
                                                <span class="material-icons-outlined">more_vert</span>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="javascript:;" data-bs-toggle="modal" data-bs-target="#previewModal<?php echo (int)$program->id; ?>">
                                                        <span class="material-icons-outlined me-2" style="font-size: 18px;">visibility</span>
                                                        Forhåndsvis
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="edit_prg.php?id=<?php echo (int)$program->id; ?>">
                                                        <span class="material-icons-outlined me-2" style="font-size: 18px;">edit</span>
                                                        Rediger
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger btn-delete" href="javascript:;"
                                                       data-id="<?php echo (int)$program->id; ?>"
                                                       data-name="<?php echo htmlspecialchars(($program->navn ?? '')); ?>">
                                                        <span class="material-icons-outlined me-2" style="font-size: 18px;">delete</span>
                                                        Slett
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    <div class="d-flex flex-wrap gap-1">
                                        <?php
                                        $dagValue = $program->dag ?? '';
                                        $days = !empty($dagValue) ? explode(',', $dagValue) : [];
                                        $dayColors = [
                                            'Mandag' => 'primary',
                                            'Tirsdag' => 'success',
                                            'Onsdag' => 'info',
                                            'Torsdag' => 'warning',
                                            'Fredag' => 'danger',
                                            'Lørdag' => 'secondary',
                                            'Søndag' => 'dark'
                                        ];
                                        foreach ($days as $day) :
                                            $day = trim($day);
                                            if (empty($day)) continue;
                                            $color = $dayColors[$day] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color; ?> bg-opacity-10 text-<?php echo $color; ?> day-badge">
                                            <?php echo htmlspecialchars($day); ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if (!empty($program->info)) : ?>
                                    <p class="text-muted small mt-3 mb-0" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php echo htmlspecialchars(($program->info ?? '')); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Modal -->
                        <div class="modal fade" id="previewModal<?php echo (int)$program->id; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title">Programdetaljer</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Lukk"></button>
                                    </div>
                                    <div class="modal-body text-center pt-0">
                                        <?php if (!empty($program->img)) : ?>
                                        <img src="../img/prg/<?php echo htmlspecialchars(($program->img ?? '')); ?>"
                                             class="program-img-large mb-3" alt="<?php echo htmlspecialchars(($program->navn ?? '')); ?>"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                        <div class="program-img-placeholder mx-auto mb-3" style="display:none;width: 120px; height: 120px; font-size: 48px; border-radius: 50%;">
                                            <?php echo strtoupper(substr(($program->navn ?? '') ?: 'P', 0, 1)); ?>
                                        </div>
                                        <?php else : ?>
                                        <div class="program-img-placeholder mx-auto mb-3" style="width: 120px; height: 120px; font-size: 48px; border-radius: 50%;">
                                            <?php echo strtoupper(substr(($program->navn ?? '') ?: 'P', 0, 1)); ?>
                                        </div>
                                        <?php endif; ?>

                                        <h4 class="mb-2"><?php echo htmlspecialchars(($program->navn ?? '')); ?></h4>
                                        <p class="text-muted mb-3">
                                            <span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">schedule</span>
                                            Kl. <?php echo htmlspecialchars(($program->tid ?? '')); ?>
                                        </p>

                                        <div class="d-flex flex-wrap justify-content-center gap-1 mb-4">
                                            <?php foreach ($days as $day) :
                                                $day = trim($day);
                                                if (empty($day)) continue;
                                                $color = $dayColors[$day] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?> day-badge">
                                                <?php echo htmlspecialchars($day); ?>
                                            </span>
                                            <?php endforeach; ?>
                                        </div>

                                        <?php if (!empty($program->info)) : ?>
                                        <div class="text-start bg-light rounded-3 p-3">
                                            <h6 class="mb-2">Beskrivelse</h6>
                                            <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars(($program->info ?? ''))); ?></p>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Lukk</button>
                                        <a href="edit_prg.php?id=<?php echo (int)$program->id; ?>" class="btn btn-primary">
                                            <span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">edit</span>
                                            Rediger
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- No results message (hidden by default) -->
                    <div id="noResults" class="empty-state d-none">
                        <span class="material-icons-outlined" style="font-size: 4rem; color: #dee2e6;">search_off</span>
                        <h5 class="text-muted mt-3">Ingen programmer funnet</h5>
                        <p class="text-muted">Prøv et annet søkeord</p>
                    </div>

                    <?php else : ?>
                    <div class="empty-state">
                        <span class="material-icons-outlined" style="font-size: 4rem; color: #dee2e6;">radio</span>
                        <h5 class="text-muted mt-3">Ingen programmer ennå</h5>
                        <p class="text-muted">Klikk på "Nytt program" for å legge til ditt første program.</p>
                        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createProgramModal">
                            <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">add</span>
                            Legg til program
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <!--end main wrapper-->

    <!-- Create Program Modal -->
    <div class="modal fade" id="createProgramModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="nyttprg.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <span class="material-icons-outlined me-2" style="vertical-align: middle;">add_circle</span>
                            Nytt program
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Lukk"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Programbilde</label>
                            <input class="form-control" type="file" name="file" accept="image/*">
                            <small class="text-muted">Anbefalt størrelse: 200x200px</small>
                        </div>

                        <div class="mb-3">
                            <label for="programName" class="form-label">Navn på programmet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="programName" name="navn" placeholder="F.eks. Morgenshow" required>
                        </div>

                        <div class="mb-3">
                            <label for="programTime" class="form-label">Sendetid <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="programTime" name="tid" placeholder="F.eks. 07:00 - 10:00" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sendedager <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php
                                $allDays = ['Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag', 'Søndag'];
                                foreach ($allDays as $day) :
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="days[]" value="<?php echo $day; ?>" id="day<?php echo $day; ?>">
                                    <label class="form-check-label" for="day<?php echo $day; ?>"><?php echo $day; ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label for="programInfo" class="form-label">Beskrivelse</label>
                            <textarea class="form-control" id="programInfo" name="info" rows="3" placeholder="Kort beskrivelse av programmet..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt</button>
                        <button type="submit" class="btn btn-primary" name="submitny">
                            <span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">save</span>
                            Lagre program
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <span class="material-icons-outlined me-2" style="vertical-align: middle;">warning</span>
                        Bekreft sletting
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Lukk"></button>
                </div>
                <div class="modal-body">
                    <p>Er du sikker på at du vil slette programmet <strong id="deleteProgramName"></strong>?</p>
                    <div class="alert alert-warning mb-0">
                        <span class="material-icons-outlined me-2" style="vertical-align: middle;">info</span>
                        Denne handlingen kan ikke angres.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">delete</span>
                        Ja, slett programmet
                    </button>
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
    <script src="assets/js/jquery.min.js"></script>
    <!--plugins-->
    <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
    <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
    <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
    $(document).ready(function() {
        var deleteId = null;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        var csrfToken = '<?php echo htmlspecialchars($csrfToken); ?>';

        // Search functionality
        $('#searchPrograms').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            var visibleCount = 0;

            $('.program-item').each(function() {
                var name = $(this).data('name');
                if (name.indexOf(searchTerm) > -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            // Show/hide no results message
            if (visibleCount === 0 && searchTerm.length > 0) {
                $('#noResults').removeClass('d-none');
                $('#programsGrid').addClass('d-none');
            } else {
                $('#noResults').addClass('d-none');
                $('#programsGrid').removeClass('d-none');
            }
        });

        // Delete button click
        $(document).on('click', '.btn-delete', function() {
            deleteId = $(this).data('id');
            var name = $(this).data('name');
            $('#deleteProgramName').text(name);
            deleteModal.show();
        });

        // Confirm delete
        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;

            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sletter...');

            $.ajax({
                url: 'programmer.php',
                method: 'POST',
                data: {
                    action: 'delete',
                    id: deleteId,
                    csrf_token: csrfToken
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Fade out and remove the card
                        var card = $('.btn-delete[data-id="' + deleteId + '"]').closest('.program-item');
                        card.addClass('fade-out');
                        setTimeout(function() {
                            card.remove();
                            // Update count
                            var count = $('.program-item').length;
                            if (count === 0) {
                                location.reload();
                            }
                        }, 300);
                        deleteModal.hide();
                    } else {
                        alert(response.error || 'Kunne ikke slette programmet');
                    }
                },
                error: function() {
                    alert('En feil oppstod. Vennligst prøv igjen.');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">delete</span> Ja, slett programmet');
                    deleteId = null;
                }
            });
        });
    });
    </script>
</body>

</html>
