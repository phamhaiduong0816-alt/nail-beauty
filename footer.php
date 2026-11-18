<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Client Footer Layout
 */
?>
<footer class="footer-bloom">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="bg-white rounded-3 p-2 px-3 d-inline-block mb-3 shadow-sm">
                    <img src="<?= asset('images/logo.png') ?>" alt="<?= APP_NAME ?>" height="48" style="object-fit: contain;">
                </div>
                <p class="text-white-50 pe-lg-4">
                    <?= APP_NAME ?> là không gian làm đẹp và thư giãn cao cấp với các dịch vụ vẽ móng nghệ thuật, đắp
                    gel Hàn Quốc, nối mi và chăm sóc cơ thể toàn diện.
                </p>
                <div class="d-flex gap-3 fs-5 mt-3">
                    <a href="#" class="text-white-50"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-tiktok"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <h5>Thông tin liên hệ</h5>
                <ul class="list-unstyled text-white-50 d-flex flex-column gap-2 mb-0">
                    <li><i class="bi bi-geo-alt me-2 text-danger"></i> <?= APP_ADDRESS ?></li>
                    <li><i class="bi bi-telephone me-2 text-danger"></i> <?= APP_PHONE ?></li>
                    <li><i class="bi bi-envelope me-2 text-danger"></i> <?= APP_EMAIL ?></li>
                    <li><i class="bi bi-clock me-2 text-danger"></i> Giờ mở cửa: <?= APP_TIME_OPEN ?></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h5>Đường dẫn nhanh</h5>
                <div class="row">
                    <div class="col-6">
                        <ul class="list-unstyled d-flex flex-column gap-2">
                            <li><a href="<?= url('home') ?>">Trang chủ</a></li>
                            <li><a href="<?= url('services') ?>">Dịch vụ</a></li>
                            <li><a href="<?= url('booking') ?>">Đặt lịch hẹn</a></li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled d-flex flex-column gap-2">
                            <li><a href="<?= url('login') ?>">Đăng nhập</a></li>
                            <li><a href="<?= url('register') ?>">Đăng ký</a></li>
                            <li><a href="<?= url('admin/dashboard') ?>">Quản trị viên</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom text-center text-white-50">
            <p class="mb-0">
                &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong>. Đồ án cơ sở 2 - Khoa Công Nghệ Thông Tin. Xây
                dựng bằng PHP &amp; MySQL.
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Main App JS -->
<script src="<?= asset('js/main.js') ?>"></script>
<?php if (isset($extraJs)): ?>
    <script src="<?= asset('js/' . $extraJs) ?>"></script>
<?php endif; ?>
</body>

</html>