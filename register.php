<?php
require_once __DIR__ . '/functions/function.php';

if (is_logged_in()) {
    redirect_to('views/dashboard/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = register_user($_POST);

    if ($result === -1) {
        set_flash('info', 'Terdaftar', 'Email sudah digunakan.');
        redirect_to('register.php');
    }

    if ($result > 0) {
        set_flash('success', 'Berhasil', 'Akun berhasil dibuat. Silakan masuk.');
        redirect_to('login.php');
    }

    set_flash('error', 'Gagal', 'Akun belum bisa dibuat.');
    redirect_to('register.php');
}

$page_title = 'Daftar - Outline';
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= h($page_title ?? 'Outline'); ?></title>

    <link
      href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900&display=swap"
      rel="stylesheet"
      media="print"
      onload="this.media='all'"
    />
    <link
      href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet"
      media="print"
      onload="this.media='all'"
    />
    <link href="<?= h(app_url('lime/theme/assets/plugins/bootstrap/css/bootstrap.min.css')); ?>?v=1.28" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/plugins/font-awesome/css/all.min.css')); ?>?v=1.28" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/css/lime.min.css')); ?>?v=1.28" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/css/custom.css')); ?>?v=1.28" rel="stylesheet" />
    <link href="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.css')); ?>?v=1.28" rel="stylesheet" />
    <link href="<?= h(app_url('assets/css/app.css')); ?>?v=1.28" rel="stylesheet" />
  </head>
  <body class="login-page err-500">
    <div class="loader">
      <div class="spinner-grow text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>

    <div class="container">
      <div class="login-container">
        <div class="row">
          <div class="col-lg-4 col-md-5 col-sm-9 lfh">
            <div class="card login-box">
              <div class="card-body">
                <h5 class="card-title">Daftar</h5>
                <form action="<?= h(app_url('register.php')); ?>" method="post" class="js-action-loading">
                  <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="Email" required />
                  </div>
                  <div class="form-group">
                    <input type="text" class="form-control" name="nama" placeholder="Nama" required />
                  </div>
                  <div class="form-group">
                    <input type="date" class="form-control" name="tanggal_lahir" required />
                  </div>
                  <div class="form-group">
                    <input type="password" class="form-control" name="password" placeholder="Password" required />
                  </div>
                  <div class="custom-control custom-checkbox form-group">
                    <input type="checkbox" class="custom-control-input" id="agreeRegister" required />
                    <label class="custom-control-label" for="agreeRegister">Setuju membuat akun</label>
                  </div>
                  <button type="submit" class="btn btn-primary">Daftar</button>
                  <a href="<?= h(app_url('login.php')); ?>" class="btn btn-secondary">Masuk</a>
                </form>
              </div>
            </div>
            <p class="auth-watermark">2026 &copy; Outline - rafifbanner</p>
          </div>
        </div>
      </div>
    </div>

    <script>
      (function () {
        function hideLoader() {
          if (document.body) {
            document.body.classList.add('no-loader');
          }

          var loaders = document.querySelectorAll('.loader');
          for (var i = 0; i < loaders.length; i += 1) {
            loaders[i].style.display = 'none';
          }
        }

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', function () {
            setTimeout(hideLoader, 120);
          });
        } else {
          setTimeout(hideLoader, 120);
        }

        setTimeout(hideLoader, 1500);
      })();
    </script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery/jquery-3.1.0.min.js')); ?>?v=1.28"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/popper.min.js')); ?>?v=1.28"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/js/bootstrap.min.js')); ?>?v=1.28"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js')); ?>?v=1.28"></script>
    <script src="<?= h(app_url('lime/theme/assets/js/lime.min.js')); ?>?v=1.28"></script>
    <script src="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.js')); ?>?v=1.28"></script>
    <script src="<?= h(app_url('assets/js/action-loading.js')); ?>?v=1.28"></script>
    <script src="<?= h(app_url('assets/js/app.js')); ?>?v=1.28"></script>
    <?php flash_script(); ?>
  </body>
</html>
