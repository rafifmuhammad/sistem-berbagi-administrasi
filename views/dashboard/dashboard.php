<?php
require_once __DIR__ . '/../../functions/function.php';
require_admin();

$active_menu = 'dashboard';
$use_datatables = true;
$table_search_id = 'recentDocumentsTable';
$search_placeholder = 'Cari dokumen terbaru...';
$page_title = 'Dashboard - Outline';
$stats = document_stats();
$total_categories = query("SELECT COUNT(*) AS total FROM tb_kategori")[0]['total'] ?? 0;
$total_users = query("SELECT COUNT(*) AS total FROM tb_user")[0]['total'] ?? 0;
$recent_documents = array_slice(get_documents(false), 0, 8);
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
    <link href="<?= h(app_url('lime/theme/assets/plugins/bootstrap/css/bootstrap.min.css')); ?>?v=1.26" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/plugins/font-awesome/css/all.min.css')); ?>?v=1.26" rel="stylesheet" />
    <?php if (!empty($use_datatables)) : ?>
    <link href="<?= h(app_url('plugins/datatables/dataTables.bootstrap4.min.css')); ?>?v=1.26" rel="stylesheet" />
    <link href="<?= h(app_url('plugins/datatables/responsive.bootstrap4.min.css')); ?>?v=1.26" rel="stylesheet" />
    <?php endif; ?>
    <link href="<?= h(app_url('lime/theme/assets/css/lime.min.css')); ?>?v=1.26" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/css/custom.css')); ?>?v=1.26" rel="stylesheet" />
    <link href="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.css')); ?>?v=1.26" rel="stylesheet" />
    <link href="<?= h(app_url('assets/css/app.css')); ?>?v=1.26" rel="stylesheet" />
  </head>
  <body>
    <div class="loader">
      <div class="spinner-grow text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>

    <div class="lime-sidebar">
      <div class="lime-sidebar-inner slimscroll">
        <ul class="accordion-menu">
          <li class="sidebar-title">Menu Utama</li>
          <li>
            <a href="<?= h(app_url('views/dashboard/dashboard.php')); ?>" class="<?= ($active_menu ?? '') === 'dashboard' ? 'active' : ''; ?>">
              <i class="material-icons">dashboard</i>Dashboard
            </a>
          </li>
          <li class="sidebar-title">Pengurusan Dokumen</li>
          <li>
            <a href="<?= h(app_url('views/documents/document_management.php')); ?>" class="<?= ($active_menu ?? '') === 'documents' ? 'active' : ''; ?>">
              <i class="material-icons">description</i>Dokumen
            </a>
          </li>
          <li>
            <a href="<?= h(app_url('views/categories/category_management.php')); ?>" class="<?= ($active_menu ?? '') === 'categories' ? 'active' : ''; ?>">
              <i class="material-icons">sell</i>Kategori
            </a>
          </li>
          <li class="sidebar-title">Menu Lainnya</li>
          <li>
            <a href="<?= h(app_url('views/users/user_management.php')); ?>" class="<?= ($active_menu ?? '') === 'users' ? 'active' : ''; ?>">
              <i class="material-icons">people_outline</i>Pengguna
            </a>
          </li>
          <li>
            <a href="<?= h(app_url('logout.php')); ?>" class="js-link-loading">
              <i class="material-icons">logout</i>Keluar
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div class="lime-header">
      <nav class="navbar navbar-expand-lg">
        <section class="material-design-hamburger navigation-toggle">
          <a href="javascript:void(0)" class="button-collapse material-design-hamburger__icon">
            <span class="material-design-hamburger__layer"></span>
          </a>
        </section>
        <a class="navbar-brand" href="<?= h(app_url('views/dashboard/dashboard.php')); ?>">Outline</a>
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
              <?php if (!empty($table_search_id)) : ?>
              data-table-search="<?= h($table_search_id); ?>"
              <?php endif; ?>
              type="search"
              placeholder="<?= h($search_placeholder ?? 'Cari data...'); ?>"
              aria-label="<?= h($search_placeholder ?? 'Cari data...'); ?>"
            />
          </form>
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
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                  </ol>
                </nav>
                <h3>Dashboard</h3>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 col-xl-3">
              <div class="card stat-card">
                <div class="card-body">
                  <h5 class="card-title">Total Dokumen</h5>
                  <h2 class="float-right"><?= (int) $stats['total']; ?></h2>
                  <p>Dokumen tersimpan</p>
                  <div class="progress" style="height: 10px">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 78%"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-3">
              <div class="card stat-card">
                <div class="card-body">
                  <h5 class="card-title">Menunggu</h5>
                  <h2 class="float-right"><?= (int) $stats['menunggu']; ?></h2>
                  <p>Perlu persetujuan</p>
                  <div class="progress" style="height: 10px">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 64%"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-3">
              <div class="card stat-card">
                <div class="card-body">
                  <h5 class="card-title">Kategori</h5>
                  <h2 class="float-right"><?= (int) $total_categories; ?></h2>
                  <p>Kategori aktif</p>
                  <div class="progress" style="height: 10px">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 52%"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-xl-3">
              <div class="card stat-card">
                <div class="card-body">
                  <h5 class="card-title">Pengguna</h5>
                  <h2 class="float-right"><?= (int) $total_users; ?></h2>
                  <p>Akun terdaftar</p>
                  <div class="progress" style="height: 10px">
                    <div class="progress-bar bg-secondary" role="progressbar" style="width: 83%"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Dokumen Terbaru</h5>
                  <div class="table-responsive">
                    <table id="recentDocumentsTable" class="table">
                      <thead>
                        <tr>
                          <th class="text-center">ID</th>
                          <th>Nama Dokumen</th>
                          <th>Kategori</th>
                          <th class="text-center">Tanggal</th>
                          <th class="text-center">Status</th>
                          <th class="text-center">Lihat</th>
                          <th class="text-center">Unduh</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($recent_documents as $document) : ?>
                        <tr>
                          <td class="text-center"><?= h($document['id_document']); ?></td>
                          <td><?= h($document['nama_dokumen']); ?></td>
                          <td><?= h($document['nama_kategori']); ?></td>
                          <td class="text-center"><?= h($document['tanggal_upload']); ?></td>
                          <td class="text-center"><?= document_status_badge($document['status']); ?></td>
                          <td class="text-center">
                            <a href="#" class="btn btn-outline-info btn-sm preview-btn" data-file="<?= h(app_url(document_preview_url($document))); ?>">
                              <i class="material-icons">visibility</i> Lihat
                            </a>
                          </td>
                          <td class="text-center">
                            <a href="<?= h(app_url('files/download.php?id=' . rawurlencode($document['id_document']))); ?>" class="btn btn-outline-primary btn-sm">
                              <i class="material-icons">download</i> Unduh
                            </a>
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
          <div class="modal-body">
            <iframe id="pdfFrame" width="100%" height="520"></iframe>
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
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery/jquery-3.1.0.min.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/popper.min.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/js/bootstrap.min.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js')); ?>?v=1.26"></script>
    <?php if (!empty($use_datatables)) : ?>
    <script src="<?= h(app_url('plugins/datatables/jquery.dataTables.min.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('plugins/datatables/dataTables.bootstrap4.min.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('plugins/datatables/dataTables.responsive.min.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('plugins/datatables/responsive.bootstrap4.min.js')); ?>?v=1.26"></script>
    <?php endif; ?>
    <script src="<?= h(app_url('lime/theme/assets/js/lime.min.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('assets/js/action-loading.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('assets/js/app.js')); ?>?v=1.26"></script>
    <?php flash_script(); ?>
  </body>
</html>
