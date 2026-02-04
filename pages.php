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
        $result = $db->delete('pages', ['id', '=', $id]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Siden ble slettet']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Kunne ikke slette siden']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'En feil oppstod ved sletting']);
    }
    exit;
}

// Get all pages
$pages = $db->query('SELECT * FROM pages ORDER BY title ASC');
$pageList = $pages->results();
$pageCount = count($pageList);
$csrfToken = Token::get();

// Helper function to get user name by ID
function getEditorName($db, $userId) {
    if (empty($userId)) return 'Ukjent';

    try {
        $user = $db->get('users', ['id', '=', $userId]);
        if ($user && $user->count()) {
            return $user->first()->name ?? 'Ukjent';
        }
    } catch (Exception $e) {
        // Ignore errors
    }
    return 'Ukjent';
}
?>
<!doctype html>
<html lang="no" data-bs-theme="blue-theme">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sider | RL Admin</title>
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
        .page-card {
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .page-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .page-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .search-box {
            max-width: 300px;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        .fade-out {
            animation: fadeOut 0.3s ease forwards;
        }
        @keyframes fadeOut {
            to { opacity: 0; transform: scale(0.95); }
        }
        .page-meta {
            font-size: 0.8rem;
        }
        .status-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
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
                <div class="breadcrumb-title pe-3">Sider</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item active" aria-current="page">Oversikt</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="create_page.php" class="btn btn-primary px-4">
                        <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">add</span>
                        Ny side
                    </a>
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
                            <input type="text" class="form-control border-start-0" id="searchPages" placeholder="Søk etter side...">
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2">
                        <span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">description</span>
                        <?php echo $pageCount; ?> side<?php echo $pageCount !== 1 ? 'r' : ''; ?> totalt
                    </span>
                </div>
            </div>

            <!-- Pages Grid -->
            <div class="card rounded-4">
                <div class="card-body">
                    <?php if ($pageCount > 0) : ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" id="pagesGrid">
                        <?php foreach ($pageList as $page) :
                            $editorName = getEditorName($db, $page->last_edit_by ?? null);
                            $lastEdit = !empty($page->last_edit_datetime)
                                ? date('d.m.Y H:i', strtotime($page->last_edit_datetime))
                                : 'Aldri';
                            $isPublished = isset($page->status) && $page->status === 'published';
                        ?>
                        <div class="col page-item" data-id="<?php echo (int)$page->id; ?>"
                             data-search="<?php echo htmlspecialchars(strtolower($page->title ?? '')); ?>">
                            <div class="card page-card rounded-4 mb-0 border">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="page-icon bg-gradient bg-primary bg-opacity-10 text-primary">
                                            <span class="material-icons-outlined">article</span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold"><?php echo htmlspecialchars($page->title ?? 'Uten tittel'); ?></h6>
                                            <?php if ($isPublished) : ?>
                                            <span class="badge bg-success bg-opacity-10 text-success status-badge">Publisert</span>
                                            <?php else : ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning status-badge">Kladd</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="dropdown">
                                            <a href="javascript:;" class="text-secondary" data-bs-toggle="dropdown">
                                                <span class="material-icons-outlined">more_vert</span>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="edit_page.php?id=<?php echo (int)$page->id; ?>">
                                                        <span class="material-icons-outlined me-2" style="font-size: 18px;">edit</span>
                                                        Rediger
                                                    </a>
                                                </li>
                                                <?php if (!empty($page->slug)) : ?>
                                                <li>
                                                    <a class="dropdown-item" href="../<?php echo htmlspecialchars($page->slug); ?>" target="_blank">
                                                        <span class="material-icons-outlined me-2" style="font-size: 18px;">visibility</span>
                                                        Vis side
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger btn-delete" href="javascript:;"
                                                       data-id="<?php echo (int)$page->id; ?>"
                                                       data-title="<?php echo htmlspecialchars($page->title ?? ''); ?>">
                                                        <span class="material-icons-outlined me-2" style="font-size: 18px;">delete</span>
                                                        Slett
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="page-meta text-muted">
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <span class="material-icons-outlined" style="font-size: 14px;">schedule</span>
                                            <span>Sist endret: <?php echo $lastEdit; ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="material-icons-outlined" style="font-size: 14px;">person</span>
                                            <span>av <?php echo htmlspecialchars($editorName); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0 pt-0">
                                    <a href="edit_page.php?id=<?php echo (int)$page->id; ?>" class="btn btn-sm btn-outline-primary w-100">
                                        <span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">edit</span>
                                        Rediger side
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- No search results message -->
                    <div id="noResults" class="empty-state d-none">
                        <span class="material-icons-outlined" style="font-size: 4rem; color: #dee2e6;">search_off</span>
                        <h5 class="text-muted mt-3">Ingen sider funnet</h5>
                        <p class="text-muted">Prøv et annet søkeord</p>
                    </div>

                    <?php else : ?>
                    <div class="empty-state">
                        <span class="material-icons-outlined" style="font-size: 4rem; color: #dee2e6;">description</span>
                        <h5 class="text-muted mt-3">Ingen sider ennå</h5>
                        <p class="text-muted">Klikk på "Ny side" for å opprette din første side.</p>
                        <a href="create.php" class="btn btn-primary mt-2">
                            <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">add</span>
                            Opprett side
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <!--end main wrapper-->

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
                    <p>Er du sikker på at du vil slette siden <strong id="deletePageTitle"></strong>?</p>
                    <div class="alert alert-warning mb-0">
                        <span class="material-icons-outlined me-2" style="vertical-align: middle;">info</span>
                        Denne handlingen kan ikke angres.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">delete</span>
                        Ja, slett siden
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
        $('#searchPages').on('input', function() {
            var searchTerm = $(this).val().toLowerCase().trim();
            var visibleCount = 0;

            $('.page-item').each(function() {
                var searchData = $(this).data('search') || '';
                if (searchData.indexOf(searchTerm) > -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            // Show/hide no results message
            if (visibleCount === 0 && searchTerm.length > 0) {
                $('#noResults').removeClass('d-none');
                $('#pagesGrid').addClass('d-none');
            } else {
                $('#noResults').addClass('d-none');
                $('#pagesGrid').removeClass('d-none');
            }
        });

        // Delete button click
        $(document).on('click', '.btn-delete', function() {
            deleteId = $(this).data('id');
            var title = $(this).data('title');
            $('#deletePageTitle').text(title || 'denne siden');
            deleteModal.show();
        });

        // Confirm delete
        $('#confirmDelete').on('click', function() {
            if (!deleteId) return;

            var btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sletter...');

            $.ajax({
                url: 'pages.php',
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
                        var card = $('.page-item[data-id="' + deleteId + '"]');
                        card.addClass('fade-out');
                        setTimeout(function() {
                            card.remove();
                            // Check if grid is empty
                            if ($('.page-item').length === 0) {
                                location.reload();
                            }
                        }, 300);
                        deleteModal.hide();
                    } else {
                        alert(response.error || 'Kunne ikke slette siden');
                    }
                },
                error: function() {
                    alert('En feil oppstod. Vennligst prøv igjen.');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<span class="material-icons-outlined me-1" style="font-size: 16px; vertical-align: middle;">delete</span> Ja, slett siden');
                    deleteId = null;
                }
            });
        });
    });
    </script>
</body>

</html>
