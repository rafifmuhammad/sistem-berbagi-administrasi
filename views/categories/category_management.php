<?php
require_once __DIR__ . '/../../functions/function.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $result = add_category($_POST);
        set_flash($result > 0 ? 'success' : 'error', $result > 0 ? 'Berhasil' : 'Gagal', $result > 0 ? 'Kategori berhasil ditambahkan.' : 'Kategori belum bisa ditambahkan.');
    }

    if ($action === 'update') {
        $result = update_category($_POST['id_category'] ?? '', $_POST);
        set_flash($result >= 0 ? 'success' : 'error', $result >= 0 ? 'Berhasil' : 'Gagal', $result >= 0 ? 'Kategori berhasil diperbarui.' : 'Kategori belum bisa diperbarui.');
    }

    redirect_to('views/categories/category_management.php');
}

if (($_GET['action'] ?? '') === 'delete') {
    $result = delete_category($_GET['id'] ?? '');
    set_flash($result > 0 ? 'success' : 'error', $result > 0 ? 'Berhasil' : 'Gagal', $result > 0 ? 'Kategori dan dokumen di dalamnya berhasil dihapus.' : 'Kategori tidak ditemukan atau belum bisa dihapus.');
    redirect_to('views/categories/category_management.php');
}

$active_menu = 'categories';
$use_datatables = true;
$table_search_id = 'categoriesTable';
$search_placeholder = 'Cari kategori...';
$page_title = 'Kategori - Outline';
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
                    <li class="breadcrumb-item active" aria-current="page">Kategori</li>
                  </ol>
                </nav>
                <h3>Kategori</h3>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Data Kategori</h5>
                  <div class="toolbar-row">
                    <div class="toolbar-actions">
                      <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#addCategoryModal">
                        <i class="material-icons">add_circle_outline</i> Tambah
                      </button>
                    </div>
                  </div>

                  <div class="table-responsive">
                    <table id="categoriesTable" class="table">
                      <thead>
                        <tr>
                          <th class="text-center">No.</th>
                          <th class="text-center">ID Kategori</th>
                          <th>Nama Kategori</th>
                          <th class="text-center">Jumlah Dokumen</th>
                          <th class="text-center">Ubah</th>
                          <th class="text-center">Hapus</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($categories as $index => $category) : ?>
                        <?php $category_modal_id = 'editCategoryModal' . html_id_suffix($category['id_category']); ?>
                        <tr>
                          <td class="text-center"><?= $index + 1; ?></td>
                          <td class="text-center"><?= h($category['id_category']); ?></td>
                          <td><?= h($category['nama_kategori']); ?></td>
                          <td class="text-center"><?= (int) $category['total_dokumen']; ?></td>
                          <td class="text-center">
                            <button type="button" class="btn btn-warning btn-icon btn-sm" data-toggle="modal" data-target="#<?= h($category_modal_id); ?>" title="Ubah kategori">
                              <i class="material-icons">edit</i>
                            </button>
                          </td>
                          <td class="text-center">
                            <a
                              href="#"
                              class="btn btn-danger btn-icon btn-sm js-confirm"
                              data-href="<?= h(app_url('views/categories/category_management.php?action=delete&id=' . rawurlencode($category['id_category']))); ?>"
                              data-title="Hapus kategori?"
                              data-text="Dokumen di dalam kategori ini ikut dihapus."
                              title="Hapus kategori"
                            >
                              <i class="material-icons">delete_outline</i>
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

    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <form method="post" class="js-action-loading">
            <input type="hidden" name="action" value="add" />
            <div class="modal-header">
              <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <i class="material-icons">close</i>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group mb-0">
                <label for="categoryName">Nama Kategori</label>
                <input type="text" class="form-control" id="categoryName" name="nama_kategori" placeholder="Masukkan nama kategori" required />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
              <button type="submit" class="btn btn-outline-primary btn-sm">Tambah</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php foreach ($categories as $category) : ?>
    <?php $category_modal_id = 'editCategoryModal' . html_id_suffix($category['id_category']); ?>
    <div class="modal fade" id="<?= h($category_modal_id); ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <form method="post" class="js-action-loading">
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="id_category" value="<?= h($category['id_category']); ?>" />
            <div class="modal-header">
              <h5 class="modal-title">Ubah Kategori</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <i class="material-icons">close</i>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group mb-0">
                <label>Nama Kategori</label>
                <input type="text" class="form-control" name="nama_kategori" value="<?= h($category['nama_kategori']); ?>" required />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Tutup</button>
              <button type="submit" class="btn btn-outline-primary btn-sm">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

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
