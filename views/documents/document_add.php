<?php
require_once __DIR__ . '/../../functions/function.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = add_document($_POST, $_FILES['file'] ?? [], $_FILES['preview_file'] ?? []);
    set_flash($result > 0 ? 'success' : 'error', $result > 0 ? 'Berhasil' : 'Gagal', $result > 0 ? 'Dokumen berhasil ditambahkan dan menunggu persetujuan.' : 'Dokumen belum bisa ditambahkan.');
    redirect_to('views/documents/document_management.php');
}

$active_menu = 'documents';
$page_title = 'Tambah Dokumen - Outline';
$search_placeholder = 'Cari dokumen...';
$categories = get_categories();
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
    <link href="<?= h(app_url('lime/theme/assets/plugins/bootstrap/css/bootstrap.min.css')); ?>?v=1.9" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/plugins/font-awesome/css/all.min.css')); ?>?v=1.9" rel="stylesheet" />
    <?php if (!empty($use_datatables)) : ?>
    <link href="<?= h(app_url('plugins/datatables/dataTables.bootstrap4.min.css')); ?>?v=1.9" rel="stylesheet" />
    <link href="<?= h(app_url('plugins/datatables/responsive.bootstrap4.min.css')); ?>?v=1.9" rel="stylesheet" />
    <?php endif; ?>
    <link href="<?= h(app_url('lime/theme/assets/css/lime.min.css')); ?>?v=1.9" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/css/custom.css')); ?>?v=1.9" rel="stylesheet" />
    <link href="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.css')); ?>?v=1.9" rel="stylesheet" />
    <link href="<?= h(app_url('assets/css/app.css')); ?>?v=1.9" rel="stylesheet" />
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
                    <li class="breadcrumb-item"><a href="<?= h(app_url('views/dashboard/dashboard.php')); ?>">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= h(app_url('views/documents/document_management.php')); ?>">Dokumen</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah</li>
                  </ol>
                </nav>
                <h3>Tambah Dokumen</h3>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Data Dokumen</h5>
                  <form action="" method="post" enctype="multipart/form-data" class="js-action-loading">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="documentName">Nama Dokumen</label>
                        <input type="text" class="form-control" id="documentName" name="nama_dokumen" placeholder="Masukkan nama dokumen" required />
                      </div>
                      <div class="form-group col-md-6">
                        <label for="documentCategory">Kategori Dokumen</label>
                        <select id="documentCategory" name="id_category" class="form-control" required>
                          <option value="">Pilih kategori</option>
                          <?php foreach ($categories as $category) : ?>
                          <option value="<?= (int) $category['id_category']; ?>"><?= h($category['nama_kategori']); ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="documentDate">Tanggal Upload</label>
                        <input type="date" class="form-control" id="documentDate" name="tanggal_upload" value="<?= h(date('Y-m-d')); ?>" required />
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="documentFile">File Dokumen</label>
                      <input type="file" class="form-control" id="documentFile" name="file" accept=".pdf,.doc,.docx" required />
                    </div>
                    <div class="form-group">
                      <label for="documentPreviewFile">File Preview PDF</label>
                      <input type="file" class="form-control" id="documentPreviewFile" name="preview_file" accept=".pdf" />
                      <small class="form-text text-muted">Isi jika file utama DOC/DOCX dan preview harus sama persis.</small>
                    </div>
                    <div class="form-group">
                      <label for="documentDescription">Keterangan</label>
                      <textarea id="documentDescription" class="form-control" name="keterangan" rows="3" placeholder="Masukkan keterangan singkat"></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm">Simpan</button>
                    <a href="<?= h(app_url('views/documents/document_management.php')); ?>" class="btn btn-danger btn-sm js-link-loading">Batal</a>
                  </form>
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
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery/jquery-3.1.0.min.js')); ?>?v=1.9"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/popper.min.js')); ?>?v=1.9"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/js/bootstrap.min.js')); ?>?v=1.9"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js')); ?>?v=1.9"></script>
    <?php if (!empty($use_datatables)) : ?>
    <script src="<?= h(app_url('plugins/datatables/jquery.dataTables.min.js')); ?>?v=1.9"></script>
    <script src="<?= h(app_url('plugins/datatables/dataTables.bootstrap4.min.js')); ?>?v=1.9"></script>
    <script src="<?= h(app_url('plugins/datatables/dataTables.responsive.min.js')); ?>?v=1.9"></script>
    <script src="<?= h(app_url('plugins/datatables/responsive.bootstrap4.min.js')); ?>?v=1.9"></script>
    <?php endif; ?>
    <script src="<?= h(app_url('lime/theme/assets/js/lime.min.js')); ?>?v=1.9"></script>
    <script src="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.js')); ?>?v=1.9"></script>
    <script src="<?= h(app_url('assets/js/action-loading.js')); ?>?v=1.9"></script>
    <script src="<?= h(app_url('assets/js/app.js')); ?>?v=1.9"></script>
    <?php flash_script(); ?>
  </body>
</html>
