<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Admin Footer Layout
 */
?>
    </div> <!-- End .admin-content -->
</div> <!-- End .admin-main -->

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Main App JS -->
<script src="<?= asset('js/main.js') ?>"></script>
<?php if (isset($extraJs)): ?>
    <script src="<?= asset('js/' . $extraJs) ?>"></script>
<?php endif; ?>
</body>
</html>
