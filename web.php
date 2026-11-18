<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Router Bảng điều hướng các tuyến đường (Routes)
 */

class Router {
    private static array $routes = [];

    public static function get(string $path, string $handler): void {
        self::$routes['GET'][trim($path, '/')] = $handler;
    }

    public static function post(string $path, string $handler): void {
        self::$routes['POST'][trim($path, '/')] = $handler;
    }

    public static function dispatch(string $method, string $uri): void {
        $trimmedUri = '';

        // 1. Hỗ trợ tham số query ?r= hoặc ?route= (Dự phòng tuyệt đối khi server không bật mod_rewrite)
        if (!empty($_GET['r'])) {
            $trimmedUri = trim($_GET['r'], '/');
        } elseif (!empty($_GET['route'])) {
            $trimmedUri = trim($_GET['route'], '/');
        } else {
            // 2. Lấy đường dẫn từ REQUEST_URI (Clean URL Rewrite)
            $cleanUri = parse_url($uri, PHP_URL_PATH) ?? '';

            // Chuẩn hóa đường dẫn tương đối từ script gốc
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $baseFolder = rtrim(preg_replace('#/public$#', '', $scriptDir), '/');

            if ($baseFolder !== '' && str_starts_with($cleanUri, $baseFolder)) {
                $cleanUri = substr($cleanUri, strlen($baseFolder));
            }

            // Bỏ đuôi /public nếu có trong URI
            if (str_starts_with($cleanUri, '/public')) {
                $cleanUri = substr($cleanUri, 7);
            }

            // Bỏ /index.php nếu có trong URI
            if (str_starts_with($cleanUri, '/index.php')) {
                $cleanUri = substr($cleanUri, 10);
            }

            $trimmedUri = trim($cleanUri, '/');
        }

        // Tách query string nếu còn sót trong URI
        if (str_contains($trimmedUri, '?')) {
            $trimmedUri = explode('?', $trimmedUri)[0];
        }

        if ($trimmedUri === '' || $trimmedUri === 'index.php') {
            $trimmedUri = 'home';
        }

        $method = strtoupper($method);

        if (isset(self::$routes[$method][$trimmedUri])) {
            $handler = self::$routes[$method][$trimmedUri];
            [$controllerName, $action] = explode('@', $handler);

            $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controller = new $controllerName();
                if (method_exists($controller, $action)) {
                    $controller->$action();
                    return;
                }
            }
        }

        // Route không tồn tại -> 404
        http_response_code(404);
        view('errors/404', ['title' => '404 - Không tìm thấy trang'], 'default');
    }
}

// =====================================================================
// ĐĂNG KÝ CÁC TUYẾN ĐƯỜNG (ROUTES REGISTRATION)
// =====================================================================

// Tuyến đường công khai (Public)
Router::get('', 'HomeController@index');
Router::get('home', 'HomeController@index');
Router::get('services', 'ServiceController@index');
Router::get('services/detail', 'ServiceController@detail');

// Xác thực & Phân quyền (Auth)
Router::get('login', 'AuthController@showLogin');
Router::post('login', 'AuthController@login');
Router::get('register', 'AuthController@showRegister');
Router::post('register', 'AuthController@register');
Router::get('logout', 'AuthController@logout');

// Đặt lịch hẹn (Booking)
Router::get('booking', 'BookingController@index');
Router::post('booking', 'BookingController@store');
Router::get('booking/success', 'BookingController@success');
Router::get('api/available-slots', 'BookingController@getAvailableSlots');

// Cá nhân & Khách hàng (Customer & Profile)
Router::get('profile', 'ProfileController@index');
Router::post('profile', 'ProfileController@update');
Router::get('customer/appointments', 'AppointmentController@myAppointments');
Router::post('customer/appointments/cancel', 'AppointmentController@customerCancel');
Router::get('customer/invoices', 'InvoiceController@myInvoices');

// Quản trị & Nhân viên (Admin & Staff)
Router::get('admin/dashboard', 'DashboardController@index');
Router::get('admin/appointments', 'AppointmentController@index');
Router::get('admin/appointments/detail', 'AppointmentController@detail');
Router::post('admin/appointments/status', 'AppointmentController@updateStatus');
Router::post('admin/appointments/assign', 'AppointmentController@assignStaff');

// Quản lý hóa đơn & thanh toán
Router::get('admin/invoices', 'InvoiceController@index');
Router::get('admin/invoices/detail', 'InvoiceController@detail');
Router::post('admin/invoices/pay', 'InvoiceController@markPaid');

// Quản lý Dịch vụ (Admin Only)
Router::get('admin/services', 'ServiceController@adminList');
Router::get('admin/services/create', 'ServiceController@create');
Router::post('admin/services/store', 'ServiceController@store');
Router::get('admin/services/edit', 'ServiceController@edit');
Router::post('admin/services/update', 'ServiceController@update');
Router::post('admin/services/toggle', 'ServiceController@toggleActive');

// Quản lý Nhân viên & Lịch làm việc (Admin Only)
Router::get('admin/staff', 'StaffController@index');
Router::get('admin/staff/create', 'StaffController@create');
Router::post('admin/staff/store', 'StaffController@store');
Router::get('admin/staff/edit', 'StaffController@edit');
Router::post('admin/staff/update', 'StaffController@update');
Router::get('admin/staff/hours', 'StaffController@workingHours');
Router::post('admin/staff/hours', 'StaffController@saveWorkingHours');

// Quản lý Khách hàng (Admin Only)
Router::get('admin/customers', 'CustomerController@index');
Router::get('admin/customers/detail', 'CustomerController@detail');
