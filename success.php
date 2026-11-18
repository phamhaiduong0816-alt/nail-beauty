<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-7">
            <div class="card card-bloom p-4 p-md-5 text-center">
                <div class="mb-4">
                    <div class="mx-auto rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 72px; height: 72px;">
                        <i class="bi bi-check-lg fs-1"></i>
                    </div>
                </div>

                <h3 class="fw-bold font-heading mb-1">Đặt Lịch Hẹn Thành Công!</h3>
                <p class="text-muted mb-4">Cảm ơn bạn đã tin tưởng dịch vụ tại <?= APP_NAME ?>. Lịch hẹn của bạn đã được ghi nhận trên hệ thống.</p>

                <!-- Appointment Ticket -->
                <div class="card bg-light border-0 rounded-4 p-4 text-start mb-4">
                    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                        <div>
                            <span class="text-muted small">Mã lịch hẹn:</span>
                            <h5 class="fw-bold text-danger font-monospace mb-0"><?= e($appointment['code']) ?></h5>
                        </div>
                        <div>
                            <?= appointmentStatusBadge($appointment['status']) ?>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Dịch vụ:</span>
                            <strong><?= e($appointment['service_name']) ?></strong>
                            <div class="text-muted small"><?= e($appointment['duration_minutes']) ?> phút làm đẹp</div>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Kỹ thuật viên phụ trách:</span>
                            <strong><?= e($appointment['staff_name']) ?></strong>
                            <div class="text-muted small"><?= e($appointment['staff_title']) ?></div>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Ngày hẹn:</span>
                            <strong><?= formatDate($appointment['appointment_date']) ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Khung thời gian:</span>
                            <strong class="text-danger">
                                <?= formatTime($appointment['start_time']) ?> - <?= formatTime($appointment['end_time']) ?>
                            </strong>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Giá dịch vụ (đã chốt):</span>
                            <span class="fs-5 fw-bold text-dark"><?= formatMoney($appointment['price_at_booking']) ?></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Khách hàng:</span>
                            <strong><?= e($appointment['customer_name']) ?></strong> (<?= e($appointment['customer_phone']) ?>)
                        </div>

                        <?php if (!empty($appointment['notes'])): ?>
                            <div class="col-12 pt-2 border-top">
                                <span class="text-muted small d-block">Ghi chú của bạn:</span>
                                <em><?= e($appointment['notes']) ?></em>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <?php if (Session::isLoggedIn() && Session::isCustomer()): ?>
                        <a href="<?= url('customer/appointments') ?>" class="btn btn-outline-bloom px-4">
                            <i class="bi bi-calendar2-check me-1"></i> Xem lịch hẹn của tôi
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('home') ?>" class="btn btn-bloom px-4">
                        <i class="bi bi-house-door me-1"></i> Quay về trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
