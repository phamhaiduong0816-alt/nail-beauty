<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-bloom p-4 p-md-5">
                <div class="text-center mb-4">
                    <img src="<?= asset('images/logo.png') ?>" alt="<?= APP_NAME ?>" height="65" class="mb-3" style="object-fit: contain;">
                    <h3 class="fw-bold mb-1">Đăng ký thành viên</h3>
                    <p class="text-muted small">Tạo tài khoản để đặt lịch làm đẹp và tích điểm ưu đãi</p>
                </div>

                <form action="<?= url('register') ?>" method="POST">
                    <?= Session::csrfField() ?>

                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required placeholder="Ví dụ: Trần Ngọc Thảo">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="username" class="form-label fw-semibold">Tên đăng nhập <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required placeholder="ngocthao">
                        </div>
                        <div class="col-sm-6">
                            <label for="phone" class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" required placeholder="0912345678">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Địa chỉ Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="thao.tran@gmail.com">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label fw-semibold">Địa chỉ</label>
                        <input type="text" class="form-control" id="address" name="address" placeholder="Số nhà, tên đường, quận/huyện...">
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label for="password" class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="Tối thiểu 6 ký tự">
                        </div>
                        <div class="col-sm-6">
                            <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="6" placeholder="Nhập lại mật khẩu">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-bloom w-100 py-2 fs-6 mb-3">
                        <i class="bi bi-person-plus me-2"></i> Hoàn tất đăng ký
                    </button>
                </form>

                <div class="text-center mt-3 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Đã có tài khoản? <a href="<?= url('login') ?>" class="text-danger fw-semibold">Đăng nhập ngay</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
