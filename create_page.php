<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();
$csrfToken = Token::get();

// Get current user
$currentUser = new User();

// Handle form submission
$statusMsg = '';
$statusType = '';
$formData = [
    'title' => '',
    'slug' => '',
    'content' => '',
    'status' => 'draft',
    'meta_description' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Token::validate()) {
        $statusType = 'danger';
        $statusMsg = 'Ugyldig sikkerhetstoken. Vennligst prøv igjen.';
    } else {
        $action = $_POST['action'] ?? 'save';

        // Get form data
        $formData['title'] = trim($_POST['title'] ?? '');
        $formData['slug'] = trim($_POST['slug'] ?? '');
        $formData['content'] = $_POST['content'] ?? '';
        $formData['status'] = $_POST['status'] ?? 'draft';
        $formData['meta_description'] = trim($_POST['meta_description'] ?? '');

        // Validate
        if (empty($formData['title'])) {
            $statusType = 'danger';
            $statusMsg = 'Sidetittel er påkrevd.';
        } else {
            // Generate slug if empty
            if (empty($formData['slug'])) {
                $formData['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $formData['title']), '-'));
            } else {
                $formData['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $formData['slug']), '-'));
            }

            // Check if slug already exists
            $existingPage = $db->get('pages', ['slug', '=', $formData['slug']]);
            if ($existingPage && $existingPage->count()) {
                $formData['slug'] = $formData['slug'] . '-' . time();
            }

            // Build insert data
            $insertData = [
                'title' => $formData['title'],
                'slug' => $formData['slug'],
                'content' => $formData['content'],
                'status' => in_array($formData['status'], ['draft', 'published']) ? $formData['status'] : 'draft',
                'meta_description' => $formData['meta_description'],
                'last_edit_by' => $currentUser->data()->id ?? null,
                'last_edit_datetime' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            try {
                $result = $db->insert('pages', $insertData);

                if ($result) {
                    if ($action === 'publish') {
                        Session::flash('success', 'Siden "' . htmlspecialchars($formData['title']) . '" er opprettet og publisert!');
                    } else {
                        Session::flash('success', 'Siden "' . htmlspecialchars($formData['title']) . '" er opprettet som kladd.');
                    }
                    Redirect::to('pages.php');
                } else {
                    $statusType = 'danger';
                    $statusMsg = 'Kunne ikke opprette siden.';
                }
            } catch (Exception $e) {
                $statusType = 'danger';
                $statusMsg = 'En feil oppstod: ' . htmlspecialchars($e->getMessage());
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
    <title>Ny side | RL Admin</title>
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
        .editor-container {
            min-height: 400px;
        }
        .tox-tinymce {
            border-radius: 8px !important;
        }
        .status-toggle .btn {
            min-width: 100px;
        }
        .preview-frame {
            width: 100%;
            height: 500px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: #fff;
        }
        .meta-section {
            background: rgba(var(--bs-primary-rgb), 0.05);
            border-radius: 8px;
            padding: 1rem;
        }
        .slug-preview {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .tip-box {
            background: linear-gradient(135deg, rgba(var(--bs-info-rgb), 0.1) 0%, rgba(var(--bs-primary-rgb), 0.1) 100%);
            border-radius: 8px;
            padding: 1rem;
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
                            <li class="breadcrumb-item"><a href="pages.php">Sider</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Ny side</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#previewModal">
                        <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">visibility</span>
                        Forhåndsvis
                    </button>
                    <a href="pages.php" class="btn btn-outline-secondary">
                        <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">arrow_back</span>
                        Avbryt
                    </a>
                </div>
            </div>
            <!--end breadcrumb-->

            <!-- Status Messages -->
            <?php if (!empty($statusMsg)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($statusType); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($statusMsg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="POST" id="pageForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="save" id="formAction">

                <div class="row">
                    <!-- Main Content Area -->
                    <div class="col-lg-9">
                        <div class="card rounded-4 mb-4">
                            <div class="card-body">
                                <!-- Title -->
                                <div class="mb-4">
                                    <label for="title" class="form-label fw-semibold">Sidetittel <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg" id="title" name="title"
                                           value="<?php echo htmlspecialchars($formData['title']); ?>"
                                           placeholder="Skriv inn sidetittel..." required autofocus>
                                </div>

                                <!-- Slug -->
                                <div class="mb-4">
                                    <label for="slug" class="form-label fw-semibold">URL-slug</label>
                                    <div class="input-group">
                                        <span class="input-group-text">/</span>
                                        <input type="text" class="form-control" id="slug" name="slug"
                                               value="<?php echo htmlspecialchars($formData['slug']); ?>"
                                               placeholder="genereres-automatisk">
                                    </div>
                                    <div class="slug-preview mt-1" id="slugPreview">
                                        Forhåndsvisning: <strong>/<?php echo htmlspecialchars($formData['slug'] ?: 'side-url'); ?></strong>
                                    </div>
                                </div>

                                <!-- Editor -->
                                <div class="mb-3">
                                    <label for="content" class="form-label fw-semibold">Innhold</label>
                                    <div class="editor-container">
                                        <textarea name="content" id="content"><?php echo htmlspecialchars($formData['content']); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SEO Section -->
                        <div class="card rounded-4">
                            <div class="card-header bg-transparent">
                                <h6 class="mb-0">
                                    <span class="material-icons-outlined me-2" style="vertical-align: middle;">search</span>
                                    SEO-innstillinger
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta-beskrivelse</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" rows="3"
                                              maxlength="160" placeholder="Kort beskrivelse for søkemotorer (maks 160 tegn)..."><?php echo htmlspecialchars($formData['meta_description']); ?></textarea>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">Vises i søkeresultater</small>
                                        <small class="text-muted"><span id="metaCharCount">0</span>/160</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-3">
                        <!-- Publish Box -->
                        <div class="card rounded-4 mb-4">
                            <div class="card-header bg-transparent">
                                <h6 class="mb-0">
                                    <span class="material-icons-outlined me-2" style="vertical-align: middle;">publish</span>
                                    Publisering
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Status -->
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="btn-group status-toggle w-100" role="group">
                                        <input type="radio" class="btn-check" name="status" value="draft" id="statusDraft"
                                               <?php echo $formData['status'] === 'draft' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-secondary" for="statusDraft">Kladd</label>

                                        <input type="radio" class="btn-check" name="status" value="published" id="statusPublished"
                                               <?php echo $formData['status'] === 'published' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-success" for="statusPublished">Publisert</label>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary" id="saveBtn">
                                        <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">save</span>
                                        Lagre som kladd
                                    </button>
                                    <button type="button" class="btn btn-success" id="publishBtn">
                                        <span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">publish</span>
                                        Lagre og publiser
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tips Box -->
                        <div class="card rounded-4">
                            <div class="card-header bg-transparent">
                                <h6 class="mb-0">
                                    <span class="material-icons-outlined me-2" style="vertical-align: middle;">lightbulb</span>
                                    Tips
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="tip-box">
                                    <ul class="mb-0 ps-3 small">
                                        <li class="mb-2">Bruk en beskrivende tittel som forklarer hva siden handler om</li>
                                        <li class="mb-2">URL-slug genereres automatisk fra tittelen</li>
                                        <li class="mb-2">Legg til bilder for å gjøre innholdet mer engasjerende</li>
                                        <li>Forhåndsvis siden før du publiserer</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
    <!--end main wrapper-->

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="material-icons-outlined me-2" style="vertical-align: middle;">visibility</span>
                        Forhåndsvisning
                    </h5>
                    <div class="btn-group me-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" data-preview="desktop">
                            <span class="material-icons-outlined" style="font-size: 18px;">desktop_windows</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-preview="tablet">
                            <span class="material-icons-outlined" style="font-size: 18px;">tablet</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-preview="mobile">
                            <span class="material-icons-outlined" style="font-size: 18px;">phone_android</span>
                        </button>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Lukk"></button>
                </div>
                <div class="modal-body p-0 d-flex justify-content-center" style="background: #f5f5f5;">
                    <iframe id="previewFrame" class="preview-frame" style="max-width: 100%;"></iframe>
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

    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/nxype513zfaqgtsrnj4o7fva68sg96yxfdevmtwa722c1a4n/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
    $(document).ready(function() {
        var hasChanges = false;

        // Initialize TinyMCE
        tinymce.init({
            selector: 'textarea#content',
            height: 500,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount code fullscreen preview',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat | code fullscreen preview',
            menubar: 'file edit view insert format tools table help',
            images_upload_url: 'postAcceptor.php',
            automatic_uploads: true,
            image_title: true,
            file_picker_types: 'image',
            file_picker_callback: function(cb, value, meta) {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');

                input.addEventListener('change', function(e) {
                    var file = e.target.files[0];
                    var reader = new FileReader();
                    reader.addEventListener('load', function() {
                        var id = 'blobid' + (new Date()).getTime();
                        var blobCache = tinymce.activeEditor.editorUpload.blobCache;
                        var base64 = reader.result.split(',')[1];
                        var blobInfo = blobCache.create(id, file, base64);
                        blobCache.add(blobInfo);
                        cb(blobInfo.blobUri(), { title: file.name });
                    });
                    reader.readAsDataURL(file);
                });

                input.click();
            },
            content_style: 'body { font-family: "Noto Sans", Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.6; padding: 20px; }',
            setup: function(editor) {
                editor.on('change', function() {
                    hasChanges = true;
                });
            }
        });

        // Track changes
        $('#title, #slug, #meta_description, input[name="status"]').on('change input', function() {
            hasChanges = true;
        });

        // Auto-generate slug from title
        $('#title').on('input', function() {
            var title = $(this).val();
            var slug = title.toLowerCase()
                .replace(/[æ]/g, 'ae')
                .replace(/[ø]/g, 'o')
                .replace(/[å]/g, 'a')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            if ($('#slug').val() === '' || $('#slug').data('auto') !== false) {
                $('#slug').val(slug).data('auto', true);
                updateSlugPreview(slug);
            }
        });

        $('#slug').on('input', function() {
            $(this).data('auto', false);
            var slug = $(this).val().toLowerCase()
                .replace(/[^a-z0-9-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $(this).val(slug);
            updateSlugPreview(slug);
        });

        function updateSlugPreview(slug) {
            $('#slugPreview').html('Forhåndsvisning: <strong>/' + (slug || 'side-url') + '</strong>');
        }

        // Meta description character count
        function updateMetaCount() {
            var count = $('#meta_description').val().length;
            $('#metaCharCount').text(count);
            if (count > 160) {
                $('#metaCharCount').addClass('text-danger');
            } else {
                $('#metaCharCount').removeClass('text-danger');
            }
        }
        $('#meta_description').on('input', updateMetaCount);
        updateMetaCount();

        // Update save button text based on status
        $('input[name="status"]').on('change', function() {
            if ($(this).val() === 'published') {
                $('#saveBtn').html('<span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">publish</span> Lagre og publiser');
            } else {
                $('#saveBtn').html('<span class="material-icons-outlined me-1" style="font-size: 18px; vertical-align: middle;">save</span> Lagre som kladd');
            }
        });

        // Publish button
        $('#publishBtn').on('click', function() {
            $('#statusPublished').prop('checked', true);
            $('#formAction').val('publish');
            $('#pageForm').submit();
        });

        // Form submission
        $('#pageForm').on('submit', function() {
            // Sync TinyMCE content
            tinymce.triggerSave();
            hasChanges = false;
        });

        // Preview functionality
        $('#previewModal').on('show.bs.modal', function() {
            tinymce.triggerSave();
            var content = $('#content').val();
            var title = $('#title').val() || 'Uten tittel';

            var previewHtml = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
            previewHtml += '<title>' + title + '</title>';
            previewHtml += '<style>body { font-family: "Noto Sans", Arial, sans-serif; line-height: 1.6; padding: 40px; max-width: 800px; margin: 0 auto; } img { max-width: 100%; height: auto; }</style>';
            previewHtml += '</head><body>';
            previewHtml += '<h1>' + title + '</h1>';
            previewHtml += content || '<p style="color: #999; font-style: italic;">Ingen innhold ennå...</p>';
            previewHtml += '</body></html>';

            var iframe = document.getElementById('previewFrame');
            iframe.srcdoc = previewHtml;
        });

        // Preview device toggle
        $('[data-preview]').on('click', function() {
            $('[data-preview]').removeClass('active');
            $(this).addClass('active');

            var device = $(this).data('preview');
            var frame = $('#previewFrame');

            switch(device) {
                case 'mobile':
                    frame.css({ 'max-width': '375px', 'height': '667px' });
                    break;
                case 'tablet':
                    frame.css({ 'max-width': '768px', 'height': '500px' });
                    break;
                default:
                    frame.css({ 'max-width': '100%', 'height': '500px' });
            }
        });

        // Warn before leaving with unsaved changes
        $(window).on('beforeunload', function() {
            if (hasChanges) {
                return 'Du har ulagrede endringer. Er du sikker på at du vil forlate siden?';
            }
        });
    });
    </script>
</body>

</html>
