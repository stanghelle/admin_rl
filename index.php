<?php
require_once 'core/init.php';

// Redirect if already logged in
Auth::requireGuest('dashboard.php');

if (Input::exists()) {
	// Validate CSRF token
	if (!Token::validate()) {
		Session::flash('error', 'Ugyldig sikkerhetstoken. Vennligst prøv igjen.');
	} else {
		$validate = new Validate();
		$validation = $validate->check($_POST, array(
			'email' => array('required' => true),
			'password' => array('required' => true)
		));

		if ($validation->passed()) {
			$remember = (Input::get('remember') === 'on') ? true : false;
			$login = Auth::attempt(Input::get('email'), Input::get('password'), $remember);

			if ($login) {
				if (Auth::user()->data()->enabled >= 1) {
					if (Auth::user()->data()->activated >= 1) {
						Session::flash('success', 'Du er nå logget inn!');
						Redirect::to('dashboard.php');
					} else {
						Session::flash('error', 'Du har ikke aktivert kontoen din. Sjekk e-posten din for instrukser om hvordan du bekrefter din konto, eller ta kontakt med oss.');
						Auth::logout();
					}
				} else {
					Auth::logout();
					Session::flash('error', 'Din konto er deaktivert. Ta kontakt med administrator.');
				}
			} else {
				Session::flash('error', 'Brukernavn og/eller passord stemmer ikke. Prøv på nytt.');
			}
		} else {
			$error_str = '';
			foreach ($validate->errors() as $error) {
				$error_str = $error_str . $error . '<br />';
			}
			Session::flash('error', $error_str);
		}
	}
}
?>
<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RL Admin - Logg inn</title>
  <!--favicon-->
	<link rel="icon" href="assets/images/favicon-32x32.png" type="image/png">
  <!-- loader-->
	<link href="assets/css/pace.min.css" rel="stylesheet">
	<script src="assets/js/pace.min.js"></script>

  <!--plugins-->
  <link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/metisMenu.min.css">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/mm-vertical.css">
  <!--bootstrap css-->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <!--main css-->
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="sass/main.css" rel="stylesheet">
  <link href="sass/dark-theme.css" rel="stylesheet">
  <link href="sass/blue-theme.css" rel="stylesheet">
  <link href="sass/responsive.css" rel="stylesheet">
  <?php echo Token::meta(); ?>
</head>

<body>

  <!--authentication-->

  <div class="mx-3 mx-lg-0">

  <div class="card my-5 col-xl-9 col-xxl-8 mx-auto rounded-4 overflow-hidden p-4">
    <div class="row g-4">
      <div class="col-lg-6 d-flex">
        <div class="card-body">
          <img src="assets/images/logo1.png" class="mb-4" width="145" alt="">
          <h4 class="fw-bold">Velkommen</h4>
          <p class="mb-0">Vennligst logg inn</p>
<?php Template::output('msg'); ?>
          <div class="form-body mt-4">
            <form class="row g-3" action="" method="post">
              <?php echo Token::input(); ?>
              <div class="col-12">
                <label for="inputEmailAddress" class="form-label">Epost</label>
                <input type="email" class="form-control" name="email" id="email" placeholder="jhon@example.com" value="<?php echo escape(Input::get('email')); ?>">
              </div>
              <div class="col-12">
                <label for="inputChoosePassword" class="form-label">Passord</label>
                <div class="input-group" id="show_hide_password">
                  <input type="password" class="form-control border-end-0" name="password" id="password"
                    placeholder="Enter Password">
                  <a href="javascript:;" class="input-group-text bg-transparent"><i
                      class="bi bi-eye-slash-fill"></i></a>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="checkbox-signin" name="remember" checked>
                  <label class="form-check-label" for="checkbox-signin">Husk meg</label>
                </div>
              </div>
              <div class="col-md-6 text-end"> <a href="glemt_passord.php">Glemt passord?</a>
              </div>
              <div class="col-12">
                <div class="d-grid">
                  <button type="submit" class="btn btn-grd-primary">Logg inn</button>
                </div>
              </div>
              <div class="col-12">
                <div class="text-start">
                  <p class="mb-0">Treng du konto ta kontakt med webansvarlig eller ansvarlig redaktør
                  </p>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-6 d-lg-flex d-none">
        <div class="p-3 rounded-4 w-100 d-flex align-items-center justify-content-center bg-grd-primary">
          <img src="assets/images/auth/login1.png" class="img-fluid" alt="">
        </div>
      </div>

    </div><!--end row-->
  </div>

</div>

  <!--authentication-->

  <!--plugins-->
  <script src="assets/js/jquery.min.js"></script>

  <script>
    $(document).ready(function () {
      $("#show_hide_password a").on('click', function (event) {
        event.preventDefault();
        if ($('#show_hide_password input').attr("type") == "text") {
          $('#show_hide_password input').attr('type', 'password');
          $('#show_hide_password i').addClass("bi-eye-slash-fill");
          $('#show_hide_password i').removeClass("bi-eye-fill");
        } else if ($('#show_hide_password input').attr("type") == "password") {
          $('#show_hide_password input').attr('type', 'text');
          $('#show_hide_password i').removeClass("bi-eye-slash-fill");
          $('#show_hide_password i').addClass("bi-eye-fill");
        }
      });
    });
  </script>

</body>

</html>
