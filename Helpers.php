<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * General Helpers & View Renderer
 */

/**
 * Xây dựng URL hoàn chỉnh từ Base URL
 */
function url(string $path = ''): string {
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    return $path === '' ? $base : $base . '/' . $path;
}

/**
 * Đường dẫn tĩnh cho file trong assets/
 */
function asset(string $path): string {
    return url('public/assets/' . ltrim($path, '/'));
}

/**
 * Trả về URL ảnh dịch vụ (hỗ trợ cả file tĩnh cục bộ và link online URL)
 * Tự động ưu tiên ảnh chụp thực tế (.jpg/.png) nếu có thay cho biểu tượng vector (.svg)
 */
function serviceImage(?string $image): string {
    if (empty($image)) {
        return asset('images/service-gel.jpg');
    }
    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
        return $image;
    }
    // Nếu trong CSDL đang lưu file .svg nhưng đã có file ảnh mẫu thực tế .jpg tương ứng thì tự động ưu tiên ảnh .jpg
    $cleanName = ltrim($image, '/');
    $jpgName = preg_replace('/\.svg$/i', '.jpg', $cleanName);
    if (file_exists(PUBLIC_PATH . '/assets/images/' . $jpgName)) {
        return asset('images/' . $jpgName);
    }
    return asset('images/' . $cleanName);
}

/**
 * Chuyển hướng người dùng
 */
function redirect(string $path): void {
    header("Location: " . url($path));
    exit;
}

/**
 * Chống tấn công XSS - Thoát chuỗi an toàn
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Định dạng tiền tệ VND (VD: 180.000 đ)
 */
function formatMoney(float|int|string|null $amount): string {
    if ($amount === null || $amount === '') {
        $amount = 0;
    }
    return number_format((float)$amount, 0, ',', '.') . ' đ';
}

/**
 * Định dạng ngày (d/m/Y)
 */
function formatDate(?string $date): string {
    if (!$date) return '—';
    $time = strtotime($date);
    return $time ? date('d/m/Y', $time) : $date;
}

/**
 * Định dạng giờ (H:i)
 */
function formatTime(?string $time): string {
    if (!$time) return '—';
    $t = strtotime($time);
    return $t ? date('H:i', $t) : $time;
}

/**
 * Định dạng ngày giờ đầy đủ (H:i d/m/Y)
 */
function formatDateTime(?string $datetime): string {
    if (!$datetime) return '—';
    $time = strtotime($datetime);
    return $time ? date('H:i d/m/Y', $time) : $datetime;
}

/**
 * Trả về Badge HTML cho trạng thái lịch hẹn
 */
function appointmentStatusBadge(string $status): string {
    return match ($status) {
        'pending'     => '<span class="badge badge-warning-soft"><i class="bi bi-clock-history me-1"></i>Chờ xác nhận</span>',
        'confirmed'   => '<span class="badge badge-info-soft"><i class="bi bi-check-circle me-1"></i>Đã xác nhận</span>',
        'in_progress' => '<span class="badge badge-primary-soft"><i class="bi bi-gear-wide-connected me-1"></i>Đang thực hiện</span>',
        'completed'   => '<span class="badge badge-success-soft"><i class="bi bi-check-all me-1"></i>Đã hoàn thành</span>',
        'cancelled'   => '<span class="badge badge-danger-soft"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>',
        default       => '<span class="badge badge-secondary-soft">' . htmlspecialchars($status) . '</span>',
    };
}

/**
 * Trả về Badge HTML cho trạng thái hóa đơn
 */
function invoiceStatusBadge(string $status): string {
    return match ($status) {
        'unpaid'    => '<span class="badge badge-warning-soft"><i class="bi bi-hourglass-split me-1"></i>Chưa thanh toán</span>',
        'paid'      => '<span class="badge badge-success-soft"><i class="bi bi-check2-circle me-1"></i>Đã thanh toán</span>',
        'cancelled' => '<span class="badge badge-danger-soft"><i class="bi bi-x-circle me-1"></i>Đã hủy</span>',
        default     => '<span class="badge badge-secondary-soft">' . htmlspecialchars($status) . '</span>',
    };
}

/**
 * Trả về phản hồi dạng JSON (cho API)
 */
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Render View giao diện
 *
 * @param string $viewPath Đường dẫn tương đối từ app/views (VD: 'home/index')
 * @param array $data Mảng biến truyền vào view
 * @param string|null $layout Tên layout ('header', 'admin', null)
 */
function view(string $viewPath, array $data = [], ?string $layout = 'default'): void {
    extract($data);

    $viewFile = APP_PATH . '/views/' . $viewPath . '.php';

    if (!file_exists($viewFile)) {
        die("View không tồn tại: " . htmlspecialchars($viewPath));
    }

    if ($layout === 'admin') {
        require_once APP_PATH . '/views/layouts/admin_header.php';
        require_once $viewFile;
        require_once APP_PATH . '/views/layouts/admin_footer.php';
    } elseif ($layout === 'default') {
        require_once APP_PATH . '/views/layouts/header.php';
        require_once $viewFile;
        require_once APP_PATH . '/views/layouts/footer.php';
    } else {
        // Render trực tiếp không layout (dành cho modal hoặc file in hóa đơn)
        require_once $viewFile;
    }
}
