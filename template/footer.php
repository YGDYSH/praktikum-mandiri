  <!--begin::Footer-->
  <footer class="app-footer">
    <div class="float-end d-none d-sm-inline">v1.0</div>
    <strong>Copyright &copy; <?= date('Y') ?> <?= APP_NAME ?>.</strong>
    Semua hak dilindungi.
  </footer>
  <!--end::Footer-->
</div><!-- /.app-wrapper, pasangan dari header.php -->

<!-- Urutan penting: OverlayScrollbars -> Popper -> Bootstrap -> AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="<?= BASE_URL ?>assets/js/adminlte.js"></script>

<!-- Konfigurasi OverlayScrollbars -->
<script>
  const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
  const Default = {
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'leave',
    scrollbarClickScroll: true,
  };
  document.addEventListener('DOMContentLoaded', function () {
    const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
    if (sidebarWrapper && typeof OverlayScrollbarsGlobal !== 'undefined' &&
        OverlayScrollbarsGlobal.OverlayScrollbars !== undefined) {
      OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
        scrollbars: {
          theme: Default.scrollbarTheme,
          autoHide: Default.scrollbarAutoHide,
          clickScroll: Default.scrollbarClickScroll,
        },
      });
    }
  });
</script>

<!-- Pengganti tema (color mode toggle) -->
<script>
  (function () {
    const resolve = (t) => t === 'auto'
      ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : t;
    document.querySelectorAll('[data-bs-theme-value]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const t = btn.getAttribute('data-bs-theme-value');
        localStorage.setItem('theme', t);
        document.documentElement.setAttribute('data-bs-theme', resolve(t));
      });
    });
  })();
</script>
</body>
</html>
