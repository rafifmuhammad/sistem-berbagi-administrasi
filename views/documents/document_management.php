<?php
require_once __DIR__ . '/../../functions/function.php';
require_login();

$active_menu = 'documents';
$use_datatables = true;
$table_search_id = 'documentsTable';
$search_placeholder = 'Cari dokumen...';
$page_title = 'Dokumen - Outline';
$documents = is_admin() ? get_documents(false) : get_documents(false, current_user_id());
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
          <?php if (is_admin()) : ?>
          <li class="sidebar-title">Menu Utama</li>
          <li>
            <a href="<?= h(app_url('views/dashboard/dashboard.php')); ?>" class="<?= ($active_menu ?? '') === 'dashboard' ? 'active' : ''; ?>">
              <i class="material-icons">dashboard</i>Dashboard
            </a>
          </li>
          <?php endif; ?>
          <li class="sidebar-title">Pengurusan Dokumen</li>
          <li>
            <a href="<?= h(app_url('views/documents/document_management.php')); ?>" class="<?= ($active_menu ?? '') === 'documents' ? 'active' : ''; ?>">
              <i class="material-icons">description</i>Dokumen
            </a>
          </li>
          <?php if (is_admin()) : ?>
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
          <?php endif; ?>
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
        <a class="navbar-brand" href="<?= h(app_url(is_admin() ? 'views/dashboard/dashboard.php' : 'views/documents/document_management.php')); ?>">Outline</a>
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
                    <?php if (is_admin()) : ?>
                    <li class="breadcrumb-item"><a href="<?= h(app_url('views/dashboard/dashboard.php')); ?>">Dashboard</a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active" aria-current="page">Dokumen</li>
                  </ol>
                </nav>
                <h3>Dokumen</h3>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Data Dokumen</h5>
                  <div class="date-filter">
                    <div class="form-group row">
                      <div class="col-sm-4">
                        <label for="documentCategoryFilter">Kategori</label>
                        <select id="documentCategoryFilter" class="form-control">
                          <option value="">Semua kategori</option>
                          <?php foreach ($categories as $category) : ?>
                          <option value="<?= h($category['nama_kategori']); ?>"><?= h($category['nama_kategori']); ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-sm-4">
                        <label for="documentDateStart">Tanggal Awal</label>
                        <input type="date" id="documentDateStart" class="form-control" />
                      </div>
                      <div class="col-sm-4">
                        <label for="documentDateEnd">Tanggal Akhir</label>
                        <input type="date" id="documentDateEnd" class="form-control" />
                      </div>
                    </div>
                    <div class="button-list m-b-md">
                      <a href="<?= h(app_url('views/documents/document_add.php')); ?>" class="btn btn-outline-primary btn-sm js-link-loading">
                        <i class="material-icons">add_circle_outline</i> Tambah
                      </a>
                      <button type="button" id="applyDocumentDate" class="btn btn-outline-success btn-sm">
                        <i class="material-icons">event_available</i> Periksa Tanggal
                      </button>
                    </div>
                  </div>

                  <div class="table-responsive documents-table-wrap">
                    <table id="documentsTable" class="table">
                      <colgroup>
                        <?php if (is_admin()) : ?>
                        <col class="documents-col-id" />
                        <col class="documents-col-name" />
                        <col class="documents-col-category" />
                        <col class="documents-col-date" />
                        <col class="documents-col-status" />
                        <col class="documents-col-file" />
                        <col class="documents-col-icon" />
                        <col class="documents-col-icon" />
                        <col class="documents-col-action" />
                        <?php else : ?>
                        <col class="documents-col-id" />
                        <col class="documents-col-name" />
                        <col class="documents-col-category" />
                        <col class="documents-col-date" />
                        <col class="documents-col-status" />
                        <col class="documents-col-file" />
                        <col class="documents-col-action" />
                        <?php endif; ?>
                      </colgroup>
                      <thead>
                        <tr>
                          <th class="text-center">ID Dokumen</th>
                          <th>Nama Dokumen</th>
                          <th>Kategori</th>
                          <th class="text-center">Tanggal Upload</th>
                          <th class="text-center">Status</th>
                          <th>File</th>
                          <?php if (is_admin()) : ?>
                          <th class="text-center no-sort documents-icon-heading" title="Lihat dokumen">
                            <span class="sr-only">Lihat</span>
                            <i class="material-icons" aria-hidden="true">visibility</i>
                          </th>
                          <th class="text-center no-sort documents-icon-heading" title="Unduh dokumen">
                            <span class="sr-only">Unduh</span>
                            <i class="material-icons" aria-hidden="true">download</i>
                          </th>
                          <?php endif; ?>
                          <th class="all text-center no-sort">Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($documents as $document) : ?>
                        <?php $document_action_id = 'documentAction' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $document['id_document']); ?>
                        <tr data-category="<?= h($document['nama_kategori']); ?>" data-date="<?= h($document['tanggal_upload']); ?>">
                          <td class="text-center"><?= h($document['id_document']); ?></td>
                          <td><?= h($document['nama_dokumen']); ?></td>
                          <td><?= h($document['nama_kategori']); ?></td>
                          <td class="text-center"><?= h($document['tanggal_upload']); ?></td>
                          <td class="text-center">
                            <?php if (is_admin()) : ?>
                            <form method="post" action="<?= h(app_url('views/documents/document_action.php')); ?>" class="m-0">
                              <input type="hidden" name="action" value="status" />
                              <input type="hidden" name="id" value="<?= h($document['id_document']); ?>" />
                              <select
                                name="status"
                                class="form-control form-control-sm status-select js-status-select"
                                data-current="<?= h($document['status']); ?>"
                                data-id="<?= h($document['id_document']); ?>"
                                data-reason="<?= h($document['rejection_reason'] ?? ''); ?>"
                                aria-label="Status dokumen"
                              >
                                <option value="menunggu" <?= $document['status'] === 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="disetujui" <?= $document['status'] === 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="ditolak" <?= $document['status'] === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                              </select>
                            </form>
                            <?php else : ?>
                            <?= document_status_badge($document['status'], $document['rejection_reason'] ?? '', true); ?>
                            <?php endif; ?>
                          </td>
                          <td><?= h(basename($document['file'])); ?></td>
                          <?php if (is_admin()) : ?>
                          <td class="text-center">
                            <a
                              href="#"
                              class="btn btn-outline-info btn-icon btn-sm preview-btn"
                              data-file="<?= h(app_url(document_preview_url($document))); ?>"
                              aria-label="Lihat dokumen"
                              title="Lihat dokumen"
                            >
                              <i class="material-icons">visibility</i>
                            </a>
                          </td>
                          <td class="text-center">
                            <a
                              href="<?= h(app_url('files/download.php?id=' . rawurlencode($document['id_document']))); ?>"
                              class="btn btn-outline-primary btn-icon btn-sm"
                              aria-label="Unduh dokumen"
                              title="Unduh dokumen"
                            >
                              <i class="material-icons">download</i>
                            </a>
                          </td>
                          <?php endif; ?>
                          <td class="text-center">
                            <div class="dropdown action-dropdown">
                              <button
                                class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                type="button"
                                id="<?= h($document_action_id); ?>"
                                data-action-dropdown-toggle
                                data-boundary="viewport"
                                aria-haspopup="true"
                                aria-expanded="false"
                              >
                                Aksi Lanjut
                              </button>
                              <div class="dropdown-menu dropdown-menu-right" data-action-dropdown-menu aria-labelledby="<?= h($document_action_id); ?>">
                                <?php if (is_admin()) : ?>
                                <a href="<?= h(app_url('views/documents/document_detail.php?id=' . rawurlencode($document['id_document']))); ?>" class="dropdown-item js-link-loading">
                                  <i class="material-icons">article</i> Detail
                                </a>
                                <a href="<?= h(app_url('views/documents/document_edit.php?id=' . rawurlencode($document['id_document']))); ?>" class="dropdown-item js-link-loading">
                                  <i class="material-icons">edit</i> Ubah
                                </a>
                                <?php endif; ?>
                                <a
                                  href="#"
                                  class="dropdown-item text-danger js-confirm"
                                  data-href="<?= h(app_url('views/documents/document_action.php?action=delete&id=' . rawurlencode($document['id_document']))); ?>"
                                  data-title="Hapus dokumen?"
                                  data-text="Dokumen akan dihapus dari daftar."
                                >
                                  <i class="material-icons">delete_outline</i> Hapus
                                </a>
                              </div>
                            </div>
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
            <iframe
              id="pdfFrame"
              class="preview-frame"
              title="Preview dokumen"
            ></iframe>
          </div>
        </div>
      </div>
    </div>

    <?php if (is_admin()) : ?>
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <form method="post" action="<?= h(app_url('views/documents/document_action.php')); ?>" class="js-action-loading">
            <input type="hidden" name="action" value="status" />
            <input type="hidden" name="status" value="ditolak" />
            <input type="hidden" name="id" value="" />
            <div class="modal-header">
              <h5 class="modal-title">Alasan Ditolak</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <i class="material-icons">close</i>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group mb-0">
                <label for="rejectReasonText">Alasan</label>
                <textarea id="rejectReasonText" class="form-control" name="rejection_reason" rows="4" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-outline-primary btn-sm">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="rejectionReasonViewModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Alasan Ditolak</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <i class="material-icons">close</i>
            </button>
          </div>
          <div class="modal-body">
            <p class="rejection-reason-text mb-0"></p>
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
