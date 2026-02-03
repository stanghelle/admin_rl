<?php
require_once 'core/init.php';

if (Session::exists('error') || Session::exists('warning') || Session::exists('success') || Session::exists('info')) {
	?>


	<?php
	if (Session::exists('error')) {
		?>
		<!-- Translucent Toast -->
<div class="alert alert-success alert-fullrow alert-dismissible fade show"><div class="container-responsive"><i class="fa fa-fw fa-lg fa-info"></i>&nbsp;&nbsp;<b>Feil: </b>
       <?php echo Session::flash('error'); ?>
    </div>
</div> <!--end toast-->
		<?php
	}
	if (Session::exists('warning')) {
		?> <div class="alert alert-warning alert-fullrow alert-dismissible fade show"><div class="container-responsive"><i class="fa fa-fw fa-lg fa-exclamation-triangle"></i>&nbsp;&nbsp;<b>Advarsel: </b><?php echo Session::flash('warning'); ?></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div> <?php
	}
	if (Session::exists('success')) {
		?>

		<div class="alert alert-success alert-fullrow alert-dismissible fade show"><div class="container-responsive"><i class="fa fa-fw fa-lg fa-info"></i>&nbsp;&nbsp;<b>success: </b><?php echo Session::flash('success'); ?></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
		<?php
	}
	if (Session::exists('info')) {
		?> <div class="alert alert-info alert-fullrow alert-dismissible fade show"><div class="container-responsive"><i class="fa fa-fw fa-lg fa-info"></i>&nbsp;&nbsp;<b>Info: </b><?php echo Session::flash('info'); ?></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div> <?php
	}
	?>

	<?php
}
?>
<div class="alert alert-info alert-fullrow alert-dismissible fade show"><div class="container-responsive"><i class="fa fa-fw fa-lg fa-info"></i>&nbsp;&nbsp;<b>Info: Siden er forsatt under utvikling</b></div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
