<!--start footer-->
<footer class="page-footer">
  <center>
 <p class="mb-0">Copyright © 2025. <br>
 Laget med &hearts; av Stanghelle Media </p>
</center>
</footer>
<!--end footer-->




<!--start switcher-->
<button class="btn btn-grd btn-grd-primary position-fixed bottom-0 end-0 m-3 d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop">
 <i class="material-icons-outlined">tune</i>Tilpass
</button>

<div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="staticBackdrop">
 <div class="offcanvas-header border-bottom h-70">
   <div class="">
     <h5 class="mb-0">Tematilpasser</h5>
     <p class="mb-0">Tilpass temaet ditt</p>
   </div>
   <a href="javascript:;" class="primaery-menu-close" data-bs-dismiss="offcanvas">
     <i class="material-icons-outlined">close</i>
   </a>
 </div>
 <div class="offcanvas-body">
   <div>
     <p>Temavariasjon</p>

     <div class="row g-3">
       <div class="col-12 col-xl-6">
         <input type="radio" class="btn-check" name="theme-options" id="BlueTheme" checked>
         <label class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4" for="BlueTheme">
           <span class="material-icons-outlined">contactless</span>
           <span>Blå</span>
         </label>
       </div>
       <div class="col-12 col-xl-6">
         <input type="radio" class="btn-check" name="theme-options" id="LightTheme">
         <label class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4" for="LightTheme">
           <span class="material-icons-outlined">light_mode</span>
           <span>Lys</span>
         </label>
       </div>
       <div class="col-12 col-xl-6">
         <input type="radio" class="btn-check" name="theme-options" id="DarkTheme">
         <label class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4" for="DarkTheme">
           <span class="material-icons-outlined">dark_mode</span>
           <span>Mørk</span>
         </label>
       </div>
       <div class="col-12 col-xl-6">
         <input type="radio" class="btn-check" name="theme-options" id="SemiDarkTheme">
         <label class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4" for="SemiDarkTheme">
           <span class="material-icons-outlined">contrast</span>
           <span>Halvmørk</span>
         </label>
       </div>
       <div class="col-12 col-xl-6">
         <input type="radio" class="btn-check" name="theme-options" id="BoderedTheme">
         <label class="btn btn-outline-secondary d-flex flex-column gap-1 align-items-center justify-content-center p-4" for="BoderedTheme">
           <span class="material-icons-outlined">border_style</span>
           <span>Kantet</span>
         </label>
       </div>
     </div><!--end row-->

   </div>
 </div>
</div>
<!--start switcher-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.min.css">
<link rel='stylesheet' href='https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.css'>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.all.min.js"></script>
<script src='https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.min.js'></script>
<script>
        function showSwalAlert(favoritt) {
          let aid = favoritt.getAttribute("data-id");

            $.ajax({
                type: "POST",
                url: "edit.php?t=varsel", // PHP script to handle the update
                data: {
      						aid: aid,

      					},
                success: function(response) {
                    // Display SweetAlert on success (no confirmation needed)
                    Swal.fire({
                      position: "top-end",
                      icon: "success",
                      title: "Varsel oppdater ...",
                      text: "Varsel er satt som lest!",
                      showConfirmButton: false,
                        timer: 1500
                    });
                     $( "#varsel" ).load(window.location.href + " #varsel" );
                    // Optional: You can update the page content dynamically here if needed
                },
                error: function(xhr, status, error) {
                    // Display an error SweetAlert if something goes wrong
                    Swal.fire({
                        title: 'Error!',
                        text: 'There was an error updating the status: ' + error,
                        icon: 'error'
                    });
                }
            });
        }
    </script>
