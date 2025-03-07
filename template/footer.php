<?php
if (!isset($settings)) {
    require_once dirname(__FILE__) . '/../config/connect.php';
    $settings = getSettings();
}
?>
            </div>
            <!-- /.content-wrapper -->
            <footer class="main-footer">
                <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="<?php echo isset($settings['url']) ? $settings['url'] : '#'; ?>"><?php echo isset($settings['judul_app']) ? $settings['judul_app'] : 'PAM Warga'; ?></a>.</strong>
                All rights reserved.
                <div class="float-right d-none d-sm-inline-block">
                    <b>Version</b> 1.0.0
                </div>
            </footer>

            <!-- Control Sidebar -->
            <aside class="control-sidebar control-sidebar-dark">
                <!-- Control sidebar content goes here -->
            </aside>
            <!-- /.control-sidebar -->
        </div>
        <!-- ./wrapper -->

        <!-- jQuery -->
        <script src="<?php echo $base_url_style; ?>plugins/jquery/jquery.min.js"></script>
        <!-- Bootstrap 4 -->
        <script src="<?php echo $base_url_style; ?>plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- AdminLTE App -->
        <script src="<?php echo $base_url_style; ?>dist/js/adminlte.min.js"></script>
        <!-- Other scripts can follow -->
    </body>
</html> 