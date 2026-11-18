<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold font-heading mb-1">Hóa Đơn Của Tôi</h2>
            <p class="text-muted small mb-0">Lịch sử thanh toán các dịch vụ làm đẹp tại <?= APP_NAME ?></p>
        </div>
        <a href="<?= url('customer/appointments') ?>" class="btn btn-outline-bloom">
            <i class="bi bi-calendar-check me-1"></i> Xem lịch hẹn
        </a>
    </div>

    <?php if (empty($invoices)): ?>
        <div class="card card-bloom p-5 text-center">
            <i class="bi bi-receipt fs-1 text-danger mb-3"></i>
            <h5 class="fw-bold">Bạn chưa có hóa đơn nào</h5>
            <p class="text-muted">Hóa đơn sẽ xuất hiện tại đây khi bạn hoàn thành các buổi làm đẹp tại tiệm.</p>
            <div class="mt-2">
                <a href="<?= url('services') ?>" class="btn btn-bloom px-4">Khám phá dịch vụ</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card card-bloom overflow-hidden">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã hóa đơn</th>
                            <th>Dịch vụ</th>
                            <th>Kỹ thuật viên</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td>
                                    <span class="fw-bold font-monospace text-danger"><?= e($inv['invoice_code']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($inv['service_name']) ?></div>
                                </td>
                                <td>
                                    <span><?= e($inv['staff_name']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= formatMoney($inv['total_amount']) ?></span>
                                </td>
                                <td>
                                    <?= invoiceStatusBadge($inv['status']) ?>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= formatDate($inv['created_at']) ?></span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= url('admin/invoices/detail?id=' . $inv['id']) ?>" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-eye me-1"></i> Xem hóa đơn
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
