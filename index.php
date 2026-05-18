<?php
require_once __DIR__ . '/functions/function.php';

$use_datatables = true;
$page_title = 'Dokumen - Outline';
$documents = get_documents(true);
$categories = get_categories();
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
    <?php if (!empty($use_datatables)) : ?>
    <link href="<?= h(app_url('plugins/datatables/dataTables.bootstrap4.min.css')); ?>?v=1.29" rel="stylesheet" />
    <link href="<?= h(app_url('plugins/datatables/responsive.bootstrap4.min.css')); ?>?v=1.29" rel="stylesheet" />
    <?php endif; ?>
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
          <form class="form-inline my-2 my-lg-0 search" action="#">
            <input
              class="form-control mr-sm-2"
              data-table-search="publicDocumentsTable"
              type="search"
              placeholder="Cari dokumen publik..."
              aria-label="Cari dokumen publik"
            />
          </form>
          <ul class="navbar-nav ml-auto">
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
                    <li class="breadcrumb-item active" aria-current="page">Publik</li>
                  </ol>
                </nav>
                <h3>Dokumen</h3>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card bg-info text-white">
                <div class="card-body">
                  <div class="dashboard-info row">
                    <div class="info-text col-md-7">
                      <h5 class="card-title">Ringkasan Sistem</h5>
                      <p>
                        Outline membantu menyediakan dokumen administratif kampus yang sering
                        dibutuhkan, tetapi belum tersusun rapi dalam satu tempat.
                      </p>
                      <ul>
                        <li>Dokumen baru masuk sebagai status menunggu.</li>
                        <li>Admin menyetujui dokumen sebelum tampil untuk publik.</li>
                        <li>Publik hanya melihat dokumen yang sudah disetujui.</li>
                      </ul>
                    </div>
                    <div class="info-image col-md-5"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <div class="row align-items-center m-b-md">
                    <div class="col-md-8">
                      <h5 class="card-title">Daftar Dokumen Publik</h5>
                    </div>
                    <div class="col-md-4 mt-3 mt-md-0">
                      <select id="publicCategoryFilter" class="form-control" aria-label="Filter kategori dokumen">
                        <option value="">Semua kategori</option>
                        <?php foreach ($categories as $category) : ?>
                        <option value="<?= h($category['nama_kategori']); ?>"><?= h($category['nama_kategori']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table id="publicDocumentsTable" class="table">
                      <thead>
                        <tr>
                          <th class="text-center">No.</th>
                          <th>Nama Dokumen</th>
                          <th>Kategori Dokumen</th>
                          <th class="text-center">Detail</th>
                          <th class="text-center">Link</th>
                          <th class="text-center">Diunduh</th>
                          <th class="text-center">Lihat</th>
                          <th class="text-center">Unduh</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($documents as $index => $document) : ?>
                        <tr data-category="<?= h($document['nama_kategori']); ?>">
                          <td class="text-center"><?= (int) $index + 1; ?></td>
                          <td><?= h($document['nama_dokumen']); ?></td>
                          <td><?= h($document['nama_kategori']); ?></td>
                          <td class="text-center">
                            <a href="<?= h(app_url('views/documents/document_public_detail.php?id=' . rawurlencode($document['id_document']))); ?>" class="btn btn-outline-success btn-sm js-link-loading">
                              <i class="material-icons">article</i> Detail
                            </a>
                          </td>
                          <td class="text-center">
                            <?php if (document_has_link($document)) : ?>
                            <a href="<?= h(document_link_url($document)); ?>" class="btn btn-outline-success btn-sm" target="_blank" rel="noopener noreferrer">
                              <i class="material-icons">link</i> Link
                            </a>
                            <?php else : ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-center"><?= (int) document_download_count($document); ?></td>
                          <td class="text-center">
                            <?php if (document_file_available($document)) : ?>
                            <a href="<?= h(app_url(document_preview_url($document))); ?>" class="btn btn-outline-info btn-sm preview-btn" data-file="<?= h(app_url(document_preview_url($document))); ?>">
                              <i class="material-icons">visibility</i> Lihat
                            </a>
                            <?php else : ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                          <td class="text-center">
                            <?php if (document_file_available($document)) : ?>
                            <a href="<?= h(app_url('files/download.php?id=' . rawurlencode($document['id_document']))); ?>" class="btn btn-outline-primary btn-sm">
                              <i class="material-icons">download</i> Unduh
                            </a>
                            <?php else : ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                          </td>
                        </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
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
    <?php if (!empty($use_datatables)) : ?>
    <script src="<?= h(app_url('plugins/datatables/jquery.dataTables.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('plugins/datatables/dataTables.bootstrap4.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('plugins/datatables/dataTables.responsive.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('plugins/datatables/responsive.bootstrap4.min.js')); ?>?v=1.29"></script>
    <?php endif; ?>
    <script src="<?= h(app_url('lime/theme/assets/js/lime.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('assets/js/action-loading.js')); ?>?v=1.29"></script>
    <script src="<?= h(app_url('assets/js/app.js')); ?>?v=1.29"></script>
    <?php flash_script(); ?>
  </body>
</html>
