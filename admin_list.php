<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Danh mục Dịch vụ</h4>
        <p class="text-muted small mb-0">Quản lý bảng giá, thời lượng thực hiện và trạng thái kinh doanh dịch vụ</p>
    </div>
    <a href="<?= url('admin/services/create') ?>" class="btn btn-bloom">
        <i class="bi bi-plus-lg me-1"></i> Thêm dịch vụ mới
    </a>
</div>

<!-- Search & Filter Card -->
<div class="admin-card p-3 mb-4">
    <form action="<?= url('admin/services') ?>" method="GET" class="row g-3">
        <div class="col-md-7">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="keyword" class="form-control" placeholder="Tìm theo tên dịch vụ hoặc mã..." value="<?= e($keyword) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $selectedCategory === $cat ? 'selected' : '' ?>>
                        <?= e($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-outline-dark">
                <i class="bi bi-funnel me-1"></i> Lọc
            </button>
        </div>
    </form>
</div>

<!-- Services Table Card -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 70px;">Mã</th>
                    <th>Dịch vụ</th>
                    <th>Danh mục</th>
                    <th>Giá niêm yết</th>
                    <th>Thời lượng</th>
                    <th>Lượt đặt</th>
                    <th>Trạng thái</th>
                    <th class="text-end" style="width: 140px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Không có dịch vụ nào trong hệ thống.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= e($s['code']) ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= serviceImage($s['image_url'] ?? '') ?>" alt="" width="44" height="44" class="rounded border" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark"><?= e($s['name']) ?></div>
                                        <div class="text-muted small text-truncate" style="max-width: 260px;"><?= e($s['description']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border"><?= e($s['category']) ?></span>
                            </td>
                            <td>
                                <span class="fw-bold text-danger"><?= formatMoney($s['price']) ?></span>
                            </td>
                            <td>
                                <span><i class="bi bi-clock me-1 text-muted"></i><?= e($s['duration_minutes']) ?> phút</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border-0"><?= e($s['booking_count'] ?? 0) ?></span>
                            </td>
                            <td>
                                <?php if ($s['is_active']): ?>
                                    <span class="badge badge-success-soft"><i class="bi bi-check-circle me-1"></i>Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary-soft"><i class="bi bi-eye-slash me-1"></i>Tạm dừng</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="<?= url('admin/services/edit?id=' . $s['id']) ?>" class="btn btn-sm btn-outline-primary" title="Sửa thông tin">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <!-- Toggle Active Form -->
                                    <form action="<?= url('admin/services/toggle') ?>" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn đổi trạng thái dịch vụ này?');">
                                        <?= Session::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="btn btn-sm <?= $s['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" title="<?= $s['is_active'] ? 'Tạm ẩn' : 'Kích hoạt lại' ?>">
                                            <i class="bi <?= $s['is_active'] ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
