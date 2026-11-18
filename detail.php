<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('home') ?>" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="<?= url('services') ?>" class="text-decoration-none">Dịch vụ</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($service['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Service Media & Quick Highlights -->
        <div class="col-lg-5">
            <div class="card card-bloom p-0 overflow-hidden text-center border-0 mb-4 position-relative" style="background: linear-gradient(135deg, #FDF4F6 0%, #FCE8ED 100%); min-height: 240px;">
                <img src="<?= serviceImage($service['image_url'] ?? '') ?>" alt="<?= e($service['name']) ?>" class="w-100 service-detail-img" style="max-height: 280px; object-fit: cover;">
                <span class="badge bg-white text-danger border rounded-pill py-2 px-3 fw-bold position-absolute top-0 start-0 m-3 shadow-sm">
                    <?= e($service['category']) ?>
                </span>
            </div>

            <!-- Booking Box -->
            <div class="card card-bloom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Giá trọn gói:</span>
                    <span class="fs-2 fw-bold text-danger"><?= formatMoney($service['price']) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <span class="text-muted"><i class="bi bi-clock me-1"></i> Thời lượng dự kiến:</span>
                    <span class="fw-semibold text-dark"><?= e($service['duration_minutes']) ?> phút</span>
                </div>
                <a href="<?= url('booking?service_id=' . $service['id']) ?>" class="btn btn-bloom btn-lg w-100 py-3">
                    <i class="bi bi-calendar-check me-2"></i> Đặt lịch dịch vụ này ngay
                </a>
            </div>
        </div>

        <!-- Service Information Details -->
        <div class="col-lg-7">
            <span class="badge bg-light text-muted border font-monospace px-2 py-1 mb-2">
                Mã DV: <?= e($service['code']) ?>
            </span>
            <h1 class="display-6 fw-bold font-heading mb-3"><?= e($service['name']) ?></h1>
            <p class="lead text-muted mb-4"><?= nl2br(e($service['description'])) ?></p>

            <div class="card card-bloom p-4 mb-4 border-0 bg-light">
                <h5 class="fw-bold mb-3 font-heading"><i class="bi bi-check2-circle text-danger me-2"></i>Quy Trình Thực Hiện Chuẩn 6 Bước</h5>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                    <li class="d-flex align-items-start gap-2">
                        <span class="badge bg-danger rounded-circle p-1 px-2">1</span>
                        <div><strong>Thăm khám &amp; Tư vấn:</strong> Kiểm tra tình trạng móng, da tay và tư vấn phong cách kiểu dáng phù hợp.</div>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <span class="badge bg-danger rounded-circle p-1 px-2">2</span>
                        <div><strong>Làm sạch &amp; Tạo form:</strong> Ngâm mềm da, nhặt da sạch sẽ bằng kềm vô trùng y tế, tạo dáng form chuẩn.</div>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <span class="badge bg-danger rounded-circle p-1 px-2">3</span>
                        <div><strong>Phủ lớp liên kết &amp; Bảo vệ:</strong> Sử dụng Base Coat hữu cơ nuôi dưỡng móng không ố vàng.</div>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <span class="badge bg-danger rounded-circle p-1 px-2">4</span>
                        <div><strong>Kỹ thuật chuyên sâu:</strong> Tiến hành sơn gel 2-3 lớp chuẩn sắc hoặc đắp bột, vẽ hoa văn nghệ thuật theo mẫu.</div>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <span class="badge bg-danger rounded-circle p-1 px-2">5</span>
                        <div><strong>Phủ Top Coat siêu bóng:</strong> Khóa màu bằng Top Coat chống trầy xước và sấy đèn UV/LED khô tức thì.</div>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <span class="badge bg-danger rounded-circle p-1 px-2">6</span>
                        <div><strong>Dưỡng tinh dầu thảo mộc:</strong> Thoa tinh dầu OPI cấp ẩm viền móng và massage nhẹ nhàng thư giãn.</div>
                    </li>
                </ul>
            </div>

            <!-- Cam kết chất lượng -->
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="p-3 border rounded-3 bg-white h-100">
                        <div class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-success me-1"></i> Bảo hành bền đẹp</div>
                        <div class="text-muted small">Miễn phí dặm chỉnh sửa trong 5 ngày nếu bong tróc ngoài ý muốn.</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 border rounded-3 bg-white h-100">
                        <div class="fw-bold text-dark mb-1"><i class="bi bi-clock-history text-primary me-1"></i> Đúng giờ hẹn</div>
                        <div class="text-muted small">Đặt trước được ưu tiên phục vụ ngay không phải chờ đợi.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
