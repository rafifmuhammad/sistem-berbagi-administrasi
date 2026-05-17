<?php
require_once __DIR__ . '/../../functions/function.php';
require_admin();

$id = $_GET['id'] ?? '';
$document = get_document($id);

if (!$document) {
    set_flash('error', 'Gagal', 'Dokumen tidak ditemukan.');
    redirect_to('views/documents/document_management.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_upload_error = request_upload_error();

    if ($request_upload_error !== '') {
        set_flash('error', 'Gagal', $request_upload_error);
        redirect_to('views/documents/document_management.php');
    }

    $result = update_document($id, $_POST, $_FILES['file'] ?? [], $_FILES['preview_file'] ?? []);
    $success = $result !== false && $result >= 0;
    $message = $success
        ? 'Dokumen berhasil diperbarui dan kembali menunggu persetujuan.'
        : (document_file_error() ?: 'Dokumen belum bisa diperbarui.');

    set_flash($success ? 'success' : 'error', $success ? 'Berhasil' : 'Gagal', $message);
    redirect_to('views/documents/document_management.php');
}

$active_menu = 'documents';
$page_title = 'Edit Dokumen - Outline';
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
    <link href="<?= h(app_url('lime/theme/assets/plugins/bootstrap/css/bootstrap.min.css')); ?>?v=1.27" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/plugins/font-awesome/css/all.min.css')); ?>?v=1.27" rel="stylesheet" />
    <?php if (!empty($use_datatables)) : ?>
    <link href="<?= h(app_url('plugins/datatables/dataTables.bootstrap4.min.css')); ?>?v=1.27" rel="stylesheet" />
    <link href="<?= h(app_url('plugins/datatables/responsive.bootstrap4.min.css')); ?>?v=1.27" rel="stylesheet" />
    <?php endif; ?>
    <link href="<?= h(app_url('lime/theme/assets/css/lime.min.css')); ?>?v=1.27" rel="stylesheet" />
    <link href="<?= h(app_url('lime/theme/assets/css/custom.css')); ?>?v=1.27" rel="stylesheet" />
    <link href="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.css')); ?>?v=1.27" rel="stylesheet" />
    <link href="<?= h(app_url('assets/css/app.css')); ?>?v=1.27" rel="stylesheet" />
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
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                  </ol>
                </nav>
                <h3>Edit Dokumen</h3>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Perubahan Dokumen</h5>
                  <form action="" method="post" enctype="multipart/form-data" class="js-action-loading">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label for="editDocumentName">Nama Dokumen</label>
                        <input type="text" class="form-control" id="editDocumentName" name="nama_dokumen" value="<?= h($document['nama_dokumen']); ?>" required />
                      </div>
                      <div class="form-group col-md-6">
                        <label for="editDocumentCategory">Kategori Dokumen</label>
                        <select id="editDocumentCategory" name="id_category" class="form-control" required>
                          <?php foreach ($categories as $category) : ?>
                          <option value="<?= h($category['id_category']); ?>" <?= (string) $category['id_category'] === (string) $document['id_category'] ? 'selected' : ''; ?>>
                            <?= h($category['nama_kategori']); ?>
                          </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="editDocumentFile">Ganti File</label>
                      <input type="file" class="form-control" id="editDocumentFile" name="file" accept=".pdf,.doc,.docx" />
                      <small class="form-text text-muted">File saat ini: <?= h(basename($document['file'])); ?></small>
                    </div>
                    <div class="form-group">
                      <label for="editDocumentPreviewFile">Ganti Preview PDF</label>
                      <input type="file" class="form-control" id="editDocumentPreviewFile" name="preview_file" accept=".pdf" />
                      <small class="form-text text-muted">
                        Preview saat ini:
                        <?= !empty($document['preview_file']) ? h(basename($document['preview_file'])) : 'mengikuti file utama atau render DOCX'; ?>
                      </small>
                    </div>
                    <div class="form-group">
                      <label for="editDocumentDescription">Keterangan</label>
                      <textarea id="editDocumentDescription" class="form-control" name="keterangan" rows="3"><?= h($document['keterangan']); ?></textarea>
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
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery/jquery-3.1.0.min.js')); ?>?v=1.27"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/popper.min.js')); ?>?v=1.27"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/bootstrap/js/bootstrap.min.js')); ?>?v=1.27"></script>
    <script src="<?= h(app_url('lime/theme/assets/plugins/jquery-slimscroll/jquery.slimscroll.min.js')); ?>?v=1.27"></script>
    <?php if (!empty($use_datatables)) : ?>
    <script src="<?= h(app_url('plugins/datatables/jquery.dataTables.min.js')); ?>?v=1.27"></script>
    <script src="<?= h(app_url('plugins/datatables/dataTables.bootstrap4.min.js')); ?>?v=1.27"></script>
    <script src="<?= h(app_url('plugins/datatables/dataTables.responsive.min.js')); ?>?v=1.27"></script>
    <script src="<?= h(app_url('plugins/datatables/responsive.bootstrap4.min.js')); ?>?v=1.27"></script>
    <?php endif; ?>
    <script src="<?= h(app_url('lime/theme/assets/js/lime.min.js')); ?>?v=1.27"></script>
    <script src="<?= h(app_url('plugins/sweet-alert2/sweetalert2.min.js')); ?>?v=1.27"></script>
    <script src="<?= h(app_url('assets/js/action-loading.js')); ?>?v=1.27"></script>
    <script src="<?= h(app_url('assets/js/app.js')); ?>?v=1.27"></script>
    <?php flash_script(); ?>
  </body>
</html>
