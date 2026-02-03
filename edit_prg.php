<?php
require_once 'core/init.php';

// Require authentication
Auth::requireLogin();

if (!Input::exists('get')) {
	Session::flash('error', 'Feil navigering.');
	Redirect::to('dashboard.php');
}

if (Input::exists()) {

	DB::getInstance()->update('program', Input::get('id'), array(
		"navn" => Input::get('navn'),
		"dag" => Input::get('dag'),
		"tid" => Input::get('tid'),
		"info" => Input::get('info')
	));

	Session::flash('success', 'Innholdet har blitt oppdatert.');
	Redirect::to('programmer.php');

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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
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
<?php Template::output('msg'); ?>
          </div>
          <div class="col-auto flex-grow-1 overflow-auto">
            <div class="btn-group position-static">
<?php Template::output('msg'); ?>


            </div>
          </div>
          <div class="col-auto">
            <div class="d-flex align-items-center gap-2 justify-content-lg-end">
               <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#CreateProject"><i class="bi bi-plus-lg me-2"></i>Legg til nytt program</button>
            </div>
          </div>
        </div><!--end row-->

        <div class="card">
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="mb-2">
                        <h4 class="header-title mt-2">Våre programmer edit</h4>
                        <p class="text-muted font-14">Endre info om våre programmer.</p>
                        <?php
                        $getContent = DB::getInstance()->get('program', array('id', '=', Input::get('id')));
                        ?>
                        <div class="text-center">
                          <img src="../img/prg/<?php echo $getContent->first()->img; ?>" class="rounded" alt="..." height="150">
                          <button type="button" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne"> Endre bilde</button>
<div class="accordion accordion-flush" id="accordionFlushExample">
<div class="accordion-item">

<div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
<div class="accordion-body">
  <form id="imageUploadForm">
    <input type="file" name="imageFile" id="imageFile" accept="image/*">
    <input type="hidden" name="id" value="<?php echo $getContent->first()->id; ?>" /><br /><br />
    <button type="submit">Upload Image</button>
</form>
<div id="imagePreview">
    <!-- Current image will be displayed here -->
    <img src="path/to/current/image.jpg" alt="Current Image" id="currentImage" width="100">
</div>
<div id="responseMessage"></div>

</div>
</div>


</div>

                        </div>

                        <div class="tab-content">
                            <div class="tab-pane show active" id="hint-emoji-preview">
                              <form action="" method="POST">

                                <div class="row g-2">
<div class="col-md">
<div class="input-group mb-4">
<span class="input-group-text">Navn</span>
<div class="form-floating">
<input type="text" class="form-control" id="navn" name="navn" placeholder="Username" value="<?php echo $getContent->first()->navn; ?>">
<label for="floatingInputGroup1">Navn</label>
</div>
</div>
</div>
<div class="col-md">
<div class="input-group mb-4">
<span class="input-group-text">Tid</span>
<div class="form-floating">
<input type="text" class="form-control" id="tid" name="tid" placeholder="Username" value="<?php echo $getContent->first()->tid; ?>">
<label for="floatingInputGroup1">Tid</label>
</div>
</div>
</div>
<div class="col-md">
<div class="input-group mb-4">
<span class="input-group-text">Dag</span>
<div class="form-floating">
<input type="text" class="form-control" id="dag" name="dag" placeholder="Username" value="<?php echo $getContent->first()->dag; ?>">
<label for="floatingInputGroup1">Dag</label>
</div>
</div>
</div>
</div>




                                <textarea name="info" id="info" autofocus><?php echo $getContent->first()->info; ?></textarea>
                                <br />
                                <div class="text-right">
                                  <a href="programmer.php"><button type="button" class="btn btn-danger btn-lg">Avbryt</button></a><input type="submit" value="Lagre" class="btn btn-primary btn-lg" />
                                </div>
                              </form>
                            </div> <!-- end preview-->


                        </div> <!-- end tab-content-->

                    </div>
                </li>


            </ul> <!-- end list-->
        </div> <!-- end card-->


    </div>
  </main>
  <!--end main wrapper-->
  <!-- Modal -->
  <div class="modal fade" id="CreateProject" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Nytt programm</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="nyttprg.php" method="post" enctype="multipart/form-data">
          <div class="mb-3">
<label for="formFile" class="form-label">Velg bilde for programmet</label>
<input class="form-control" type="file" name="file">
</div>
          <div class="form mb-3">
              <label for="floatingInput">Navn på programmet</label>
          <input type="text" class="form-control" id="navn" name="navn" placeholder="Program navn">

          </div>
          <div class="form mb-3">
            <label for="floatingInput">Klokkeslett for programmet</label>
          <input type="text" class="form-control" id="tid" name="tid" placeholder="Klokkeslett for programmet">

          </div>
          <div class="mb-4">
            <label for="multiple-select-field" class="form-label">Velg dager denne skal gå</label>
            <select class="form-select" id="multiple-select-field" name="multiple-select-field" data-placeholder="Velg dag(er)" multiple>
              <option value="">Velg dager</option>
              <option value="Mandag">Mandag</option>
              <option value="Tirsdag">Tirsdag</option>
              <option value="Onsdag">Onsdag</option>
              <option value="Torsdag">Torsdag</option>
              <option value="Fredag">Fredag</option>
              <option value="Lørdag">Lørdag</option>
              <option value="Søndag">Søndag</option>
            </select>
          </div>

          <div class="form">
            <label for="floatingTextarea">Kort info om programmet</label>
<textarea class="form-control" placeholder="" id="info" name="info"></textarea>

</div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Avbryt</button>
          <button type="submit" class="btn btn-primary" name="submitny">Lagre nytt programm!</button>
        </form>
        </div>
      </div>
    </div>
  </div>
<!-- Modal -->

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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.all.min.js"></script>
  <script src='https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.min.js'></script>
  	<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.2.13/dist/semantic.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
      <script src="assets/plugins/select2/js/select2-custom.js"></script>
      <script src="https://cdn.tiny.cloud/1/nxype513zfaqgtsrnj4o7fva68sg96yxfdevmtwa722c1a4n/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

      <script>
      tinymce.init({
      selector: 'textarea#info',
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
<script type="text/javascript">
document.getElementById('imageUploadForm').addEventListener('submit', function(e) {
  e.preventDefault(); // Prevent the default form submission (page reload)

  const formData = new FormData(this); // Collect the form data, including the file

  fetch('upload.php', { // Send the data to the server-side script
      method: 'POST',
      body: formData
  })
  .then(response => response.json()) // Expect a JSON response from the server
  .then(data => {
      if (data.status === 'success') {
          // Update the image source in the DOM without reloading the page
          document.getElementById('currentImage').src = data.newImagePath + '?' + new Date().getTime(); // Append timestamp to force refresh
          document.getElementById('responseMessage').innerText = data.message;
      } else {
          document.getElementById('responseMessage').innerText = 'Error: ' + data.message;
      }
  })
  .catch(error => {
      console.error('Error:', error);
      document.getElementById('responseMessage').innerText = 'An error occurred.';
  });
});

</script>
</body>

</html>
