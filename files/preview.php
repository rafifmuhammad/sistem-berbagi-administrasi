<?php
require_once __DIR__ . '/../functions/function.php';

$id = $_GET['id'] ?? '';
$document = get_document($id);

if (!$document) {
    http_response_code(404);
    echo 'Dokumen tidak ditemukan.';
    exit;
}

if (!is_logged_in() && $document['status'] !== 'disetujui') {
    http_response_code(403);
    echo 'Dokumen belum tersedia untuk publik.';
    exit;
}

if (is_logged_in() && !is_admin() && $document['status'] !== 'disetujui' && (string) ($document['id_user'] ?? '') !== current_user_id()) {
    http_response_code(403);
    echo 'Dokumen tidak tersedia untuk akun ini.';
    exit;
}

$base_dir = realpath(__DIR__ . '/..');
$preview_path = document_preview_path($document);
$relative_file = $preview_path !== '' ? $preview_path : $document['file'];
$absolute_file = realpath(__DIR__ . '/../' . $relative_file);

if (!$absolute_file || strpos($absolute_file, $base_dir) !== 0 || !is_file($absolute_file)) {
    http_response_code(404);
    echo 'File tidak ditemukan.';
    exit;
}

$source_extension = document_file_extension($document['file']);
$extension = document_file_extension($absolute_file);

header_remove('X-Frame-Options');
header("Content-Security-Policy: frame-ancestors 'self'");
header('X-Content-Type-Options: nosniff');

if ($extension === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . str_replace('"', '', basename($absolute_file)) . '"');
    header('Content-Length: ' . filesize($absolute_file));
    header('Cache-Control: private, max-age=3600');
    readfile($absolute_file);
    exit;
}

$content = '';
$notice = '';

if ($source_extension === 'docx') {
    $content = docx_preview_html($absolute_file);
    $notice = 'Preview DOCX ditampilkan langsung di browser. Jika gagal dimuat, sistem memakai fallback HTML sederhana.';
} else {
    $notice = 'Format file ini tidak bisa dipreview langsung. Silakan unduh untuk membuka file asli.';
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Preview <?= h($document['nama_dokumen']); ?></title>
    <style>
      body {
        background: #f6f8fb;
        color: #263445;
        font-family: Arial, sans-serif;
        margin: 0;
      }

      .preview-shell {
        margin: 0 auto;
        max-width: 920px;
        padding: 24px;
      }

      .preview-header,
      .preview-document {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 4px;
      }

      .preview-header {
        margin-bottom: 14px;
        padding: 16px 18px;
      }

      .preview-header h1 {
        font-size: 20px;
        margin: 0 0 6px;
      }

      .preview-header p {
        color: #6f7c8d;
        margin: 0;
      }

      .preview-document {
        min-height: 420px;
        padding: 28px 34px;
      }

      .preview-document p {
        line-height: 1.65;
        margin: 0 0 12px;
      }

      .preview-document h2,
      .preview-document h3 {
        margin: 18px 0 10px;
      }

      .preview-document table {
        border-collapse: collapse;
        margin: 14px 0;
        width: 100%;
      }

      .preview-document td {
        border: 1px solid #dfe5ef;
        padding: 8px 10px;
        vertical-align: top;
      }

      .preview-notice {
        background: #fff8e6;
        border: 1px solid #f2d58d;
        border-radius: 4px;
        color: #6b4e00;
        margin-bottom: 14px;
        padding: 10px 12px;
      }

      .docx-render-target .docx-wrapper {
        background: transparent;
        padding: 0;
      }

      .docx-render-target .docx {
        box-shadow: none;
        margin: 0 auto 18px;
      }

      .is-hidden {
        display: none;
      }
    </style>
  </head>
  <body>
    <main class="preview-shell">
      <section class="preview-header">
        <h1><?= h($document['nama_dokumen']); ?></h1>
        <p><?= h(basename($document['file'])); ?></p>
      </section>

      <?php if ($notice !== '') : ?>
      <div class="preview-notice"><?= h($notice); ?></div>
      <?php endif; ?>

      <section class="preview-document">
        <?php if ($source_extension === 'docx') : ?>
        <div id="docxPreview" class="docx-render-target"></div>
        <div id="docxFallback" class="<?= $content !== '' ? 'is-hidden' : ''; ?>">
          <?php if ($content !== '') : ?>
          <?= $content; ?>
          <?php else : ?>
          <p>Preview belum tersedia untuk format ini.</p>
          <?php endif; ?>
        </div>
        <?php elseif ($content !== '') : ?>
        <?= $content; ?>
        <?php else : ?>
        <p>Preview belum tersedia untuk format ini.</p>
        <?php endif; ?>
      </section>
    </main>

    <?php if ($source_extension === 'docx') : ?>
    <script src="<?= h(app_url('plugins/datatables/jszip.min.js')); ?>?v=1.26"></script>
    <script src="<?= h(app_url('plugins/docx-preview/docx-preview.min.js')); ?>?v=1.26"></script>
    <script>
      (function () {
        var target = document.getElementById('docxPreview');
        var fallback = document.getElementById('docxFallback');

        if (!target || !window.docx || typeof window.docx.renderAsync !== 'function') {
          if (fallback) {
            fallback.classList.remove('is-hidden');
          }
          return;
        }

        fetch(<?= json_encode(app_url('files/download.php?id=' . rawurlencode($document['id_document']))); ?>, {
          credentials: 'same-origin'
        })
          .then(function (response) {
            if (!response.ok) {
              throw new Error('Preview gagal dimuat.');
            }

            return response.arrayBuffer();
          })
          .then(function (buffer) {
            return window.docx.renderAsync(buffer, target, null, {
              breakPages: true,
              ignoreWidth: false,
              ignoreHeight: false,
              ignoreFonts: false,
              renderHeaders: true,
              renderFooters: true,
              useBase64URL: true
            });
          })
          .then(function () {
            if (fallback) {
              fallback.classList.add('is-hidden');
            }
          })
          .catch(function () {
            if (fallback) {
              fallback.classList.remove('is-hidden');
            }
          });
      })();
    </script>
    <?php endif; ?>
  </body>
</html>
