<?php

function set_flash($icon, $title, $text)
{
    $_SESSION['flash'] = [
        'icon' => $icon,
        'title' => $title,
        'text' => $text,
    ];
}

function flash_script()
{
    if (empty($_SESSION['flash'])) {
        return;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    ?>
    <script>
      if (window.Swal && typeof window.Swal.fire === 'function') {
        Swal.fire({
          title: <?= json_encode($flash['title']); ?>,
          text: <?= json_encode($flash['text']); ?>,
          icon: <?= json_encode($flash['icon']); ?>,
          confirmButtonText: 'OK'
        });
      }
    </script>
    <?php
}
