<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold font-heading mb-1">Lịch Hẹn Của Tôi</h2>
            <p class="text-muted small mb-0">Theo dõi lịch hẹn làm móng &amp; spa đã đặt tại <?= APP_NAME ?></p>
        </div>
        <a href="<?= url('booking') ?>" class="btn btn-bloom">
            <i class="bi bi-calendar-plus me-1"></i> Đặt lịch hẹn mới
        </a>
    </div>

    <?php if (empty($appointments)): ?>
        <div class="card card-bloom p-5 text-center">
            <i class="bi bi-calendar-heart fs-1 text-danger mb-3"></i>
            <h5 class="fw-bold">Bạn chưa có lịch hẹn nào</h5>
            <p class="text-muted">Hãy chọn dịch vụ yêu thích và đặt lịch để được phục vụ chu đáo nhất.</p>
            <div class="mt-2">
                <a href="<?= url('booking') ?>" class="btn btn-bloom px-4">Đặt lịch làm đẹp ngay</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card card-bloom overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã lịch</th>
                            <th>Dịch vụ</th>
                            <th>Kỹ thuật viên</th>
                            <th>Ngày hẹn</th>
                            <th>Khung giờ</th>
                            <th>Giá chốt</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-danger"><?= e($apt['code']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($apt['service_name']) ?></div>
                                    <div class="text-muted small"><?= e($apt['duration_minutes']) ?> phút</div>
                                </td>
                                <td>
                                    <span><?= e($apt['staff_name']) ?></span>
                                </td>
                                <td>
                                    <strong><?= formatDate($apt['appointment_date']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= formatTime($apt['start_time']) ?> - <?= formatTime($apt['end_time']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= formatMoney($apt['price_at_booking']) ?></span>
                                </td>
                                <td>
                                    <?= appointmentStatusBadge($apt['status']) ?>
                                </td>
                                <td class="text-end">
                                    <?php if (in_array($apt['status'], ['pending', 'confirmed'])): ?>
                                        <form action="<?= url('customer/appointments/cancel') ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?');">
                                            <?= Session::csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $apt['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle me-1"></i> Hủy hẹn
                                            </button>
                                        </form>
                                    <?php elseif ($apt['status'] === 'completed' && !empty($apt['invoice_id'])): ?>
                                        <a href="<?= url('customer/invoices') ?>" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-receipt me-1"></i> Xem hóa đơn
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
