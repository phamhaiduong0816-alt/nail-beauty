<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="admin-card p-4">
            <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-calendar3-week text-danger me-2"></i>
                        Cấu hình Ca làm việc trong tuần: <?= e($staff['full_name']) ?>
                    </h5>
                    <span class="badge bg-light text-muted border"><?= e($staff['title']) ?></span>
                </div>
                <a href="<?= url('admin/staff') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            <p class="text-muted small mb-4">
                Hệ thống chỉ cho phép khách hàng đặt lịch hẹn trong khoảng thời gian nhân viên có ca làm việc (được bật "Đi làm"). Các ngày tắt "Đi làm" được tính là ngày nghỉ của nhân viên.
            </p>

            <form action="<?= url('admin/staff/hours') ?>" method="POST">
                <?= Session::csrfField() ?>
                <input type="hidden" name="staff_id" value="<?= $staff['id'] ?>">

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 160px;">Thứ trong tuần</th>
                                <th style="width: 140px;" class="text-center">Trạng thái ca</th>
                                <th>Giờ bắt đầu</th>
                                <th>Giờ kết thúc</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $dayNames = [
                                1 => 'Thứ Hai',
                                2 => 'Thứ Ba',
                                3 => 'Thứ Tư',
                                4 => 'Thứ Năm',
                                5 => 'Thứ Sáu',
                                6 => 'Thứ Bảy',
                                7 => 'Chủ Nhật'
                            ];

                            for ($d = 1; $d <= 7; $d++): 
                                $h = $hoursMap[$d] ?? null;
                                $isWorking = $h ? (bool)$h['is_working'] : ($d !== 7);
                                $start = $h ? substr($h['start_time'], 0, 5) : '08:30';
                                $end   = $h ? substr($h['end_time'], 0, 5) : '20:00';
                            ?>
                                <tr>
                                    <td>
                                        <strong class="<?= $d === 7 ? 'text-danger' : 'text-dark' ?>">
                                            <?= $dayNames[$d] ?>
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" role="switch" 
                                                   id="working_<?= $d ?>" name="days[<?= $d ?>][is_working]" value="1" 
                                                   <?= $isWorking ? 'checked' : '' ?>>
                                            <label class="form-check-label small" for="working_<?= $d ?>">
                                                <?= $isWorking ? 'Đi làm' : 'Nghỉ' ?>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="time" name="days[<?= $d ?>][start_time]" class="form-control form-control-sm" 
                                               value="<?= $start ?>">
                                    </td>
                                    <td>
                                        <input type="time" name="days[<?= $d ?>][end_time]" class="form-control form-control-sm" 
                                               value="<?= $end ?>">
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= url('admin/staff') ?>" class="btn btn-light px-4">Hủy bỏ</a>
                    <button type="submit" class="btn btn-bloom px-4">
                        <i class="bi bi-save me-1"></i> Lưu ca làm việc tuần
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
