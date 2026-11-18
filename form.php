<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="admin-card p-4">
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <h5 class="fw-bold mb-0">
                    <i class="bi <?= $isEdit ? 'bi-person-gear' : 'bi-person-plus' ?> text-danger me-2"></i>
                    <?= e($title) ?>
                </h5>
                <a href="<?= url('admin/staff') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
                </a>
            </div>

            <form action="<?= url($isEdit ? 'admin/staff/update' : 'admin/staff/store') ?>" method="POST">
                <?= Session::csrfField() ?>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="id" value="<?= $staff['id'] ?>">
                <?php endif; ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="full_name" class="form-label fw-semibold">Họ và tên nhân viên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required 
                               placeholder="VD: Nguyễn Lan Anh" value="<?= e($staff['full_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" required 
                               placeholder="0901112233" value="<?= e($staff['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label fw-semibold">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" 
                               <?= $isEdit ? 'readonly disabled class="form-control bg-light"' : 'required' ?> 
                               placeholder="VD: staff_lananh" value="<?= e($staff['username'] ?? '') ?>">
                        <?php if ($isEdit): ?>
                            <div class="form-text text-muted">Tên đăng nhập không thể thay đổi sau khi tạo.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">Địa chỉ Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               <?= $isEdit ? 'readonly disabled class="form-control bg-light"' : 'required' ?> 
                               placeholder="lananh@bloomnail.vn" value="<?= e($staff['email'] ?? '') ?>">
                    </div>
                </div>

                <?php if (!$isEdit): ?>
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Mật khẩu khởi tạo</label>
                        <input type="text" class="form-control font-monospace" id="password" name="password" value="staff123">
                        <div class="form-text text-muted">Mật khẩu mặc định là <code>staff123</code>. Nhân viên có thể tự đổi sau.</div>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label fw-semibold">Chức danh / Chuyên môn <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required 
                               placeholder="VD: Master Nail Artist / Chuyên viên Mi & Brow" value="<?= e($staff['title'] ?? 'Kỹ thuật viên Nail & Spa') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-semibold">Trạng thái công việc <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select">
                            <option value="active" <?= ($staff['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Đang làm việc (Active)</option>
                            <option value="inactive" <?= ($staff['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Tạm ngừng / Nghỉ việc (Inactive)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="bio" class="form-label fw-semibold">Giới thiệu tay nghề &amp; Kinh nghiệm</label>
                    <textarea class="form-control" id="bio" name="bio" rows="3" 
                              placeholder="Mô tả kỹ năng nổi bật, số năm kinh nghiệm, các thế mạnh nghệ thuật móng..."><?= e($staff['bio'] ?? '') ?></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= url('admin/staff') ?>" class="btn btn-light px-4">Hủy bỏ</a>
                    <button type="submit" class="btn btn-bloom px-4">
                        <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Lưu thay đổi' : 'Thêm nhân viên' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
