<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

if (!Input::exists('get')) {
	Session::flash('error', 'Feil navigering.');
	Redirect::to('dashboard.php');
}

if (Input::exists()) {

	DB::getInstance()->update('pages', Input::get('id'), array(
		"content" => Input::get('content'),
		"last_edit_by" => $user->data()->id,
		"last_edit_datetime" => date("Y-m-d H:i:s", time())
	));

	Session::flash('success', 'Innholdet har blitt oppdatert.');
	Redirect::to('pages.php');

}

?>
<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Maxton | Bootstrap 5 Admin Dashboard Template</title>
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
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <!--main css-->
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="sass/main.css" rel="stylesheet">
  <link href="sass/dark-theme.css" rel="stylesheet">
  <link href="sass/blue-theme.css" rel="stylesheet">
  <link href="sass/semi-dark.css" rel="stylesheet">
  <link href="sass/bordered-theme.css" rel="stylesheet">
  <link href="sass/responsive.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.min.css">
  <link rel='stylesheet' href='https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.css'>
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
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Oversikt</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">

					</div>
				</div>
				<!--end breadcrumb-->



        <div class="row g-3">
          <div class="col-auto">

          </div>
          <div class="col-auto flex-grow-1 overflow-auto">
            <div class="btn-group position-static">



            </div>
          </div>
          <div class="col-auto">
            <div class="d-flex align-items-center gap-2 justify-content-lg-end">
               <button class="btn btn-primary px-4"><i class="bi bi-plus-lg me-2"></i>Legg til Medarbeider</button>
            </div>
          </div>
        </div><!--end row-->

        <div class="col-12">
            <div class="card">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="mb-2">
                            <h4 class="header-title mt-2">Quill Editor</h4>
                            <p class="text-muted font-14">Snow is a clean, flat toolbar theme.</p>

                            <?php
                            $getContent = DB::getInstance()->get('pages', array('id', '=', Input::get('id')));
                            ?>
                            <div class="tab-content">
                                <div class="tab-pane show active" id="hint-emoji-preview">
                                  <form action="" method="POST">
                                    <textarea name="content" id="content" autofocus><?php echo $getContent->first()->content; ?></textarea>
                                    <br />
                                    <div class="text-right">
                                      <a href="pages.php"><button type="button" class="btn btn-danger btn-lg">Avbryt</button></a><input type="submit" value="Lagre" class="btn btn-primary btn-lg" />
                                    </div>
                                  </form>


                            </div> <!-- end tab-content-->

                        </div>
                    </li>


                </ul> <!-- end list-->
            </div> <!-- end card-->
        </div> <!-- end col-->


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
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="https://cdn.tiny.cloud/1/nxype513zfaqgtsrnj4o7fva68sg96yxfdevmtwa722c1a4n/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

  <script>
  tinymce.init({
  selector: 'textarea#content',
  plugins: 'image code',
  toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | | code',
  images_upload_url: 'postAcceptor.php',
  image_title: true,
  /* enable automatic uploads of images represented by blob or data URIs*/
  automatic_uploads: true,
  /*
    URL of our upload handler (for more details check: https://www.tiny.cloud/docs/configure/file-image-upload/#images_upload_url)
    images_upload_url: 'postAcceptor.php',
    here we add custom filepicker only to Image dialog
  */
  file_picker_types: 'image',
  /* and here's our custom image picker*/
  file_picker_callback: (cb, value, meta) => {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');

    input.addEventListener('change', (e) => {
      const file = e.target.files[0];

      const reader = new FileReader();
      reader.addEventListener('load', () => {
        /*
          Note: Now we need to register the blob in TinyMCEs image blob
          registry. In the next release this part hopefully won't be
          necessary, as we are looking to handle it internally.
        */
        const id = 'blobid' + (new Date()).getTime();
        const blobCache =  tinymce.activeEditor.editorUpload.blobCache;
        const base64 = reader.result.split(',')[1];
        const blobInfo = blobCache.create(id, file, base64);
        blobCache.add(blobInfo);

        /* call the callback and populate the Title field with the file name */
        cb(blobInfo.blobUri(), { title: file.name });
      });
      reader.readAsDataURL(file);
    });

    input.click();
  },
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }'
});

</script>
</body>

</html>
