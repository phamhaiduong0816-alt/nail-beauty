<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: ServiceController (Dịch vụ Nail & Beauty)
 */

require_once APP_PATH . '/models/Service.php';

class ServiceController {
    private Service $serviceModel;

    public function __construct() {
        $this->serviceModel = new Service();
    }

    /**
     * Danh sách dịch vụ công khai cho khách hàng
     */
    public function index(): void {
        $category = trim($_GET['category'] ?? '');
        $keyword  = trim($_GET['keyword'] ?? '');

        $services   = $this->serviceModel->getAll($keyword ?: null, $category ?: null, true);
        $categories = $this->serviceModel->getCategories();

        view('services/index', [
            'title'            => 'Danh sách dịch vụ - ' . APP_NAME,
            'services'         => $services,
            'categories'       => $categories,
            'selectedCategory' => $category,
            'keyword'          => $keyword,
        ]);
    }

    /**
     * Chi tiết một dịch vụ
     */
    public function detail(): void {
        $id = (int)($_GET['id'] ?? 0);
        $service = $this->serviceModel->findById($id);

        if (!$service || (!$service['is_active'] && !Session::isAdmin())) {
            Session::setFlash('danger', 'Dịch vụ không tồn tại hoặc đã ngừng cung cấp.');
            redirect('services');
        }

        // Lấy các dịch vụ cùng danh mục liên quan
        $relatedServices = array_filter(
            $this->serviceModel->getActiveServices(),
            fn($s) => $s['category'] === $service['category'] && $s['id'] !== $service['id']
        );

        view('services/detail', [
            'title'           => $service['name'] . ' - ' . APP_NAME,
            'service'         => $service,
            'relatedServices' => array_slice($relatedServices, 0, 3),
        ]);
    }

    /**
     * Bảng quản lý dịch vụ dành cho Admin
     */
    public function adminList(): void {
        AuthMiddleware::requireAdmin();

        $keyword  = trim($_GET['keyword'] ?? '');
        $category = trim($_GET['category'] ?? '');

        $services = $this->serviceModel->getAll($keyword ?: null, $category ?: null, null);
        $categories = $this->serviceModel->getCategories();

        view('services/admin_list', [
            'title'            => 'Quản lý Dịch vụ - ' . APP_NAME,
            'services'         => $services,
            'categories'       => $categories,
            'selectedCategory' => $category,
            'keyword'          => $keyword,
        ], 'admin');
    }

    /**
     * Form thêm dịch vụ mới
     */
    public function create(): void {
        AuthMiddleware::requireAdmin();
        view('services/form', [
            'title'   => 'Thêm Dịch vụ mới',
            'service' => null,
            'isEdit'  => false,
        ], 'admin');
    }

    /**
     * Lưu dịch vụ mới
     */
    public function store(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi bảo mật (CSRF).');
            redirect('admin/services');
        }

        $code        = strtoupper(trim($_POST['code'] ?? ''));
        $name        = trim($_POST['name'] ?? '');
        $category    = trim($_POST['category'] ?? 'Nail Care');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $duration    = (int)($_POST['duration_minutes'] ?? 60);
        $isActive    = isset($_POST['is_active']) ? 1 : 0;
        $imageUrl    = trim($_POST['image_url'] ?? 'service-gel.svg');

        if (empty($code) || empty($name) || $price <= 0 || $duration <= 0) {
            Session::setFlash('danger', 'Vui lòng điền đúng mã, tên, giá tiền (> 0) và thời lượng (> 0 phút).');
            redirect('admin/services/create');
        }

        if ($this->serviceModel->findByCode($code)) {
            Session::setFlash('danger', 'Mã dịch vụ "' . htmlspecialchars($code) . '" đã tồn tại.');
            redirect('admin/services/create');
        }

        $this->serviceModel->create([
            'code'             => $code,
            'name'             => $name,
            'category'         => $category,
            'description'      => $description,
            'price'            => $price,
            'duration_minutes' => $duration,
            'image_url'        => $imageUrl,
            'is_active'        => $isActive,
        ]);

        Session::setFlash('success', 'Đã thêm dịch vụ mới thành công!');
        redirect('admin/services');
    }

    /**
     * Form sửa dịch vụ
     */
    public function edit(): void {
        AuthMiddleware::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $service = $this->serviceModel->findById($id);

        if (!$service) {
            Session::setFlash('danger', 'Không tìm thấy dịch vụ.');
            redirect('admin/services');
        }

        view('services/form', [
            'title'   => 'Chỉnh sửa dịch vụ: ' . $service['name'],
            'service' => $service,
            'isEdit'  => true,
        ], 'admin');
    }

    /**
     * Cập nhật dịch vụ
     */
    public function update(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi bảo mật (CSRF).');
            redirect('admin/services');
        }

        $id          = (int)($_POST['id'] ?? 0);
        $code        = strtoupper(trim($_POST['code'] ?? ''));
        $name        = trim($_POST['name'] ?? '');
        $category    = trim($_POST['category'] ?? 'Nail Care');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $duration    = (int)($_POST['duration_minutes'] ?? 60);
        $isActive    = isset($_POST['is_active']) ? 1 : 0;
        $imageUrl    = trim($_POST['image_url'] ?? 'service-gel.svg');

        $service = $this->serviceModel->findById($id);
        if (!$service) {
            Session::setFlash('danger', 'Dịch vụ không tồn tại.');
            redirect('admin/services');
        }

        if (empty($code) || empty($name) || $price <= 0 || $duration <= 0) {
            Session::setFlash('danger', 'Vui lòng kiểm tra lại thông tin bắt buộc.');
            redirect('admin/services/edit?id=' . $id);
        }

        $existingWithCode = $this->serviceModel->findByCode($code);
        if ($existingWithCode && (int)$existingWithCode['id'] !== $id) {
            Session::setFlash('danger', 'Mã dịch vụ này đã bị trùng với dịch vụ khác.');
            redirect('admin/services/edit?id=' . $id);
        }

        $this->serviceModel->update($id, [
            'code'             => $code,
            'name'             => $name,
            'category'         => $category,
            'description'      => $description,
            'price'            => $price,
            'duration_minutes' => $duration,
            'image_url'        => $imageUrl,
            'is_active'        => $isActive,
        ]);

        Session::setFlash('success', 'Đã cập nhật dịch vụ thành công!');
        redirect('admin/services');
    }

    /**
     * Ẩn / Hiện dịch vụ
     */
    public function toggleActive(): void {
        AuthMiddleware::requireAdmin();

        if (!Session::validateCsrf()) {
            Session::setFlash('danger', 'Lỗi bảo mật.');
            redirect('admin/services');
        }

        $id = (int)($_POST['id'] ?? 0);
        $this->serviceModel->toggleActive($id);
        Session::setFlash('success', 'Đã thay đổi trạng thái hoạt động của dịch vụ.');
        redirect('admin/services');
    }
}
