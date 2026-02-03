<?php
// Get unread notification count using PDO
$db = DB::getInstance();
$notifyResult = $db->query("SELECT COUNT(*) AS null_rows_count FROM us_notifications WHERE read_at IS NULL");
$ulest_varsel = 0;
if ($notifyResult && $notifyResult->count() > 0) {
    $ulest_varsel = $notifyResult->first()->null_rows_count;
}
?>
<!--start header-->
<header class="top-header">
  <nav class="navbar navbar-expand align-items-center gap-4">
    <div class="btn-toggle">
      <a href="javascript:;"><i class="material-icons-outlined">menu</i></a>
    </div>
    <div class="search-bar flex-grow-1">
      <div class="position-relative">

        <div class="search-popup p-3">
          <div class="card rounded-4 overflow-hidden">

            <div class="card-body search-content">
            </div>

          </div>
        </div>
      </div>
    </div>
    <ul class="navbar-nav gap-1 nav-right-links align-items-center">



<div id="varsel">



      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" data-bs-auto-close="outside"
          data-bs-toggle="dropdown" href="javascript:;"><i class="material-icons-outlined">notifications</i>
          <span class="badge-notify"><?=$ulest_varsel?></span>
        </a>
        <div class="dropdown-menu dropdown-notify dropdown-menu-end shadow">
          <div class="px-3 py-1 d-flex align-items-center justify-content-between border-bottom">
            <h5 class="notiy-title mb-0">Varsel</h5>
            <div class="dropdown">
              <button class="btn btn-secondary dropdown-toggle dropdown-toggle-nocaret option" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <span class="material-icons-outlined">
                  more_vert
                </span>
              </button>
              <div class="dropdown-menu dropdown-option dropdown-menu-end shadow">
                <div><</div>
                <div><a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
                      class="material-icons-outlined fs-6">done_all</i>Mark all as read</a></div>

                <div>
                  <hr class="dropdown-divider">
                </div>
                <div></div>
              </div>
            </div>
          </div>
          <div class="notify-list">
            <?php
            // Fetch notifications using PDO
            $notifications = $db->query("SELECT * FROM us_notifications ORDER BY sent_at DESC LIMIT 20");

            if ($notifications && $notifications->count() > 0):
              foreach ($notifications->results() as $row):
                $varsel_id = $row->id;
                $title = htmlspecialchars($row->title);
                $body = htmlspecialchars($row->body);
                $sent_at = $row->sent_at;
                $type = $row->type;
                $read_at = $row->read_at;
            ?>
            <div>
              <a class="dropdown-item border-bottom py-2" href="javascript:;">
                <div class="d-flex align-items-center gap-3">
                  <div class="">
                    <?php
                    if ($type == "sale") {
                      echo '<span class="material-icons-outlined">point_of_sale</span>';
                    } elseif ($type == "delivery") {
                      echo '<span class="material-icons-outlined">local_shipping</span>';
                    }
                     elseif ($type == "return") {
                    echo '<span class="material-icons-outlined">replay</span>';

                    } elseif ($type == "info") {
                    echo '<span class="material-icons-outlined">info</span>';
                  }
                    else {
                      echo '<span class="material-icons-outlined">crisis_alert</span>';
                    }
                     ?>
                  </div>
                  <div class="">
                    <?php if (!empty($read_at)) { ?>
                    <h5 class="notify-title"><?=$title?> <span class="badge text-bg-secondary">Lest</span></h5>
                    <?php } else { ?>
                      <h5 class="notify-title"><?=$title?> <span class="badge text-bg-secondary">Ulest</span></h5>
                  <?php } ?>
                    <p class="mb-0 notify-desc"><?=$body?></p>
                    <p class="mb-0 notify-time"><?=$sent_at?></p>

                  </div>
                  <div class="notify-close position-absolute end-0 me-3">
                  <button type="button" name="button_read" class="btn btn-outline-info" onclick="showSwalAlert(this)" data-id="<?=$varsel_id?>"> <span class="material-icons-outlined">mark_chat_read</span></button>
                  </div>
                </div>
              </a>
            </div>
<?php
              endforeach;
            endif;
?>
          </div>
        </div>
        </div>
      </li>

      <li class="nav-item dropdown">
        <a href="javascrpt:;" class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
           <img src="../img/user/user.png" class="rounded-circle p-1 border" width="45" height="45" alt="">
        </a>
        <div class="dropdown-menu dropdown-user dropdown-menu-end shadow">
          <a class="dropdown-item  gap-2 py-2" href="javascript:;">
            <div class="text-center">
              <img src="../img/user/user.png" class="rounded-circle p-1 shadow mb-3" width="90" height="90"
                alt="">
              <h5 class="user-name mb-0 fw-bold"> <?php echo $user->data()->name; ?></h5>
            </div>
          </a>
          <hr class="dropdown-divider">
          <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
            class="material-icons-outlined">person_outline</i>Profile</a>
          <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
            class="material-icons-outlined">local_bar</i>Setting</a>
          <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:;"><i
            class="material-icons-outlined">dashboard</i>Dashboard</a>

          <hr class="dropdown-divider">
          <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="logout.php"><i
          class="material-icons-outlined">power_settings_new</i>Logg ut</a>
        </div>
      </li>
    </ul>

  </nav>
</header>
<!--end top header-->


 <!--start sidebar-->
 <aside class="sidebar-wrapper" data-simplebar="true">
  <div class="sidebar-header">
    <div class="logo-icon">
      <img src="assets/images/logo-icon.png" class="logo-img" alt="">
    </div>
    <div class="logo-name flex-grow-1">
      <h5 class="mb-0">RL Admin</h5>
    </div>
    <div class="sidebar-close">
      <span class="material-icons-outlined">close</span>
    </div>
  </div>
  <div class="sidebar-nav">
      <!--navigation-->
      <ul class="metismenu" id="sidenav">
        <li>
          <a href="dashboard.php" >
            <div class="parent-icon"><i class="material-icons-outlined">home</i>
            </div>
            <div class="menu-title">Dashboard</div>
          </a>

        </li>
        <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon"><i class="material-icons-outlined">analytics</i>
            </div>
            <div class="menu-title">Trafikkanalyse</div>
          </a>
          <ul>
            <li><a href="traffic_analytics.php"><i class="material-icons-outlined">arrow_right</i>Oversikt</a>
            </li>
            <li><a href="traffic_settings.php"><i class="material-icons-outlined">arrow_right</i>Sporingsinnstillinger</a>
            </li>
          </ul>
        </li>


        <li class="menu-label">Sider</li>
        <li>
          <a href="kveld.php">
            <div class="parent-icon"><i class="material-icons-outlined">help_center</i>
            </div>
            <div class="menu-title">Kveldssending</div>
          </a>
        </li>

        <li>
          <a href="javascript:;" class="has-arrow">
            <div class="parent-icon"><i class="material-icons-outlined">casino</i>
            </div>
            <div class="menu-title">Bingo</div>
          </a>
          <ul>
            <li><a href="bingotoppen.php"><i class="material-icons-outlined">arrow_right</i>Bingotoppen</a>
            </li>
            <li><a href="bingotoppen_hist.php"><i class="material-icons-outlined">arrow_right</i>Bingotoppen historikk</a>
            </li>
            <li><a href="bingo_utsalg.php"><i class="material-icons-outlined">arrow_right</i>Utsalgssteder</a>
            </li>


          </ul>
        </li>
        <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon"><i class="material-icons-outlined">schedule</i>
            </div>
            <div class="menu-title">Programoversikt</div>
          </a>
          <ul>
            <li><a href="program_oversikt.php"><i class="material-icons-outlined">arrow_right</i>oversikt</a>
            </li>
            <li><a href="prg_pdf.php"><i class="material-icons-outlined">arrow_right</i>Ny PDF</a>
            </li>

          </ul>
        </li>

        <li class="menu-label">Bruker og medarbeider</li>
        <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon"><i class="material-icons-outlined">person</i>
            </div>
            <div class="menu-title">Bruker</div>
          </a>
          <ul>
            <li><a href="users.php"><i class="material-icons-outlined">arrow_right</i>Oversikt</a>
            </li>
            <li><a href="new_user.php"><i class="material-icons-outlined">arrow_right</i>Ny bruker</a>
            </li>

          </ul>
        </li>
        <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon"><i class="material-icons-outlined">groups</i>
            </div>
            <div class="menu-title">medarbeider</div>
          </a>
          <ul>
            <li><a href="medarb.php"><i class="material-icons-outlined">arrow_right</i>Oversikt</a>
            </li>
            <li><a href="ny_medarb.php"><i class="material-icons-outlined">arrow_right</i>Ny medarbeider</a>
            </li>
          </ul>
        </li>
        <li class="menu-label">Diverse sider</li>
        <li>
          <a href="pages.php">
            <div class="parent-icon"><i class="material-icons-outlined">web</i>
            </div>
            <div class="menu-title">Tekst sider</div>
          </a>

        </li>
        <li>
          <a href="programmer.php">
            <div class="parent-icon"><i class="material-icons-outlined">person</i>
            </div>
            <div class="menu-title">Våre programmer</div>
          </a>
        </li>
        <li>
          <a href="filemanger.php?f=">
            <div class="parent-icon"><i class="material-icons-outlined">folder</i>
            </div>
            <div class="menu-title">Fil lager</div>
          </a>
        </li>


        <li>
          <a href="dev.php">
            <div class="parent-icon"><i class="material-icons-outlined">code</i>
            </div>
            <div class="menu-title">Dev logg</div>
          </a>
        </li>
        <li class="menu-label">Apper admin</li>
        <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon"><i class="material-icons-outlined">on_device_training</i>
            </div>
            <div class="menu-title">Bingo app</div>
          </a>
          <ul>
            <li><a href="bapp_users.php"><i class="material-icons-outlined">arrow_right</i>Bruker</a>
            </li>
            <li><a href="app_bingo_utsalg.php"><i class="material-icons-outlined">arrow_right</i>Utsalgssted</a>
            </li>
            <li><a href="app_bingo_rapport.php"><i class="material-icons-outlined">arrow_right</i>Salgs rapport</a>
            </li>
          </ul>
        </li>
        <li>
          <a class="has-arrow" href="javascript:;">
            <div class="parent-icon"><i class="material-icons-outlined">phone_iphone</i>
            </div>
            <div class="menu-title">Medarbeider app(kommer)</div>
          </a>
          <ul>
            <li><a href="#"><i class="material-icons-outlined">arrow_right</i>-</a>
            </li>
            <li><a href="#"><i class="material-icons-outlined">arrow_right</i>-</a>
            </li>
          </ul>
        </li>




       </ul>
      <!--end navigation-->
  </div>
</aside>
<!--end sidebar-->
