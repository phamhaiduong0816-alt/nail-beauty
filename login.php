<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-bloom p-4 p-md-5">
                <div class="text-center mb-4">
                    <img src="<?= asset('images/logo.png') ?>" alt="<?= APP_NAME ?>" height="65" class="mb-3" style="object-fit: contain;">
                    <h3 class="fw-bold mb-1">Đăng nhập</h3>
                    <p class="text-muted small">Chào mừng bạn đến với hệ thống <?= APP_NAME ?></p>
                </div>

                <form action="<?= url('login') ?>" method="POST" id="loginForm">
                    <?= Session::csrfField() ?>

                    <div class="mb-3">
                        <label for="login" class="form-label fw-semibold">Tên đăng nhập hoặc Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" id="login" name="login" required placeholder="admin / staff1 / customer1">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" required placeholder="Nhập mật khẩu...">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-bloom w-100 py-2 fs-6 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Đăng nhập hệ thống
                    </button>
                </form>

                <!-- Quick Demo Login Selection (Dành cho Giảng viên / Người chấm thi & Tester) -->
                <div class="border-top pt-3 mt-2">
                    <div class="text-center text-muted small fw-semibold mb-2">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Tài khoản Demo (Click để điền nhanh):
                    </div>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="fillDemo('admin', 'admin123')">
                            <i class="bi bi-shield-lock me-1"></i> Admin
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="fillDemo('staff1', 'staff123')">
                            <i class="bi bi-person-badge me-1"></i> Staff 1
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="fillDemo('customer1', 'customer123')">
                            <i class="bi bi-person-heart me-1"></i> Khách 1
                        </button>
                    </div>
                </div>

                <div class="text-center mt-4 pt-2 border-top">
                    <p class="text-muted small mb-0">
                        Chưa có tài khoản? <a href="<?= url('register') ?>" class="text-danger fw-semibold">Đăng ký thành viên mới</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(username, password) {
    document.getElementById('login').value = username;
    document.getElementById('password').value = password;
}
</script>
