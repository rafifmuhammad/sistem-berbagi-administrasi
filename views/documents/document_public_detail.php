<?php
require_once __DIR__ . '/../../functions/function.php';

$id = $_GET['id'] ?? '';
$document = get_document($id);

if (!$document || ($document['status'] ?? '') !== 'disetujui') {
    set_flash('error', 'Gagal', 'Dokumen publik tidak ditemukan.');
    redirect_to('index.php');
}

$page_title = 'Detail Dokumen - Outline';
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= h($page_title ?? 'Outline'); ?></title>
    <?php render_favicon_links(); ?>

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
    <link href="<?= h(app_url('lime/theme/assets/plugins/bootstrap/css/bootstrap.min.css')); ?>?v=1.29" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/plugins/font-awesome/css/all.min.css')); ?>?v=1.29" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/css/lime.min.css')); ?>?v=1.29" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/css/custom.css')); ?>?v=1.29" rel="stylesheet" />
    <link href="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.css')); ?>?v=1.29" rel="stylesheet" />
    <link href="<?= h(app_url('assets/css/app.css')); ?>?v=1.29" rel="stylesheet" />
  </head>
  <body>
    <div class="loader">
      <div class="spinner-grow text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>

    <div class="lime-header">
      <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="<?= h(app_url('index.php')); ?>">Outline</a>
        <button
          class="navbar-toggler"
          type="button"
          data-toggle="collapse"
          data-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <i class="material-icons">keyboard_arrow_down</i>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item">
              <a class="nav-link" href="<?= h(app_url('index.php')); ?>">Publik</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?= h(app_url('login.php')); ?>">Masuk</a>
            </li>
          </ul>
        </div>
      </nav>
    </div>

    <div class="lime-container">
      <div class="lime-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              <div class="page-title">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb breadcrumb-separator-1">
                    <li class="breadcrumb-item"><a href="<?= h(app_url('index.php')); ?>">Publik</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                  </ol>
                </nav>
                <h3>Detail Dokumen</h3>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title"><?= h($document['nama_dokumen']); ?></h5>
                  <div class="document-identity">
                    <div class="row">
                      <div class="col-md-4">
                        <p><span>ID Dokumen</span><?= h($document['id_document']); ?></p>
                      </div>
                      <div class="col-md-4">
                        <p><span>Kategori</span><?= h($document['nama_kategori']); ?></p>
                      </div>
                      <div class="col-md-4">
                        <p><span>Status</span><?= document_status_badge($document['status']); ?></p>
                      </div>
                      <div class="col-md-4">
                        <p><span>Tanggal Upload</span><?= h($document['tanggal_upload']); ?></p>
                      </div>
                      <div class="col-md-4">
                        <p><span>Diunggah Oleh</span><?= h($document['uploader'] ?: '-'); ?></p>
                      </div>
                      <div class="col-md-4">
                        <p><span>File</span><?= h(basename($document['file'])); ?></p>
                      </div>
                      <div class="col-md-12">
                        <p><span>Keterangan</span><?= h($document['keterangan'] ?: '-'); ?></p>
                      </div>
                    </div>
                    <div class="button-list">
                      <a href="<?= h(app_url('index.php')); ?>" class="btn btn-outline-secondary btn-sm js-link-loading">
                        <i class="material-icons">arrow_back</i> Kembali
                      </a>
                      <a href="<?= h(app_url(document_preview_url($document))); ?>" class="btn btn-outline-info btn-sm preview-btn" data-file="<?= h(app_url(document_preview_url($document))); ?>">
                        <i class="material-icons">visibility</i> Lihat
                      </a>
                      <a href="<?= h(app_url('files/download.php?id=' . rawurlencode($document['id_document']))); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="material-icons">download</i> Unduh
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Preview Dokumen</h5>
                  <iframe class="document-viewer" src="<?= h(app_url(document_preview_url($document))); ?>" title="Preview dokumen"></iframe>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="lime-footer">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              <span class="footer-text">2026 &copy; Outline - rafifbanner</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="previewModal" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Preview Dokumen</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body preview-modal-body">
            <iframe id="pdfFrame" class="preview-frame" title="Preview dokumen"></iframe>
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
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery/jquery-3.1.0.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/popper.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/js/bootstrap.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('lime/theme/assets/js/lime.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('assets/js/action-loading.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('assets/js/app.js')); ?>?v=1.29"></script>
    <?php flash_script(); ?>
  </body>
</html>
