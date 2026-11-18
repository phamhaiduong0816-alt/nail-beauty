<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * Controller: HomeController (Trang chủ giới thiệu tiệm)
 */

require_once APP_PATH . '/models/Service.php';
require_once APP_PATH . '/models/Staff.php';

class HomeController {
    private Service $serviceModel;
    private Staff $staffModel;

    public function __construct() {
        $this->serviceModel = new Service();
        $this->staffModel = new Staff();
    }

    public function index(): void {
        $services = $this->serviceModel->getActiveServices();
        $staffMembers = $this->staffModel->getActiveStaff();
        $categories = $this->serviceModel->getCategories();

        view('home/index', [
            'title'        => APP_NAME . ' - ' . APP_SLOGAN,
            'services'     => array_slice($services, 0, 6), // 6 dịch vụ tiêu biểu
            'staffMembers' => $staffMembers,
            'categories'   => $categories,
        ]);
    }
}
