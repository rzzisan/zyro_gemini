<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../core/config.php';
require_once ROOT_PATH . '/core/db.php';
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Website.php';
require_once ROOT_PATH . '/models/ApiToken.php';

class ApiAuthenticationTest extends PHPUnit\Framework\TestCase
{
    private $db;
    private $userModel;
    private $websiteModel;
    private $apiTokenModel;
    private $user_id;
    private $website_id;
    private $token;

    protected function setUp(): void
    {
        $this->db = getDb();
        $this->db->beginTransaction();

        $this->userModel = new User($this->db);
        $this->websiteModel = new Website($this->db);
        $this->apiTokenModel = new ApiToken($this->db);

        // Create a user
        $this->user_id = $this->userModel->createUser('Test User', 'test@example.com', 'password');

        // Create a website
        $this->website_id = $this->websiteModel->create($this->user_id, 'https://test.com');

        // Create an API token
        $this->token = $this->apiTokenModel->generateKey($this->user_id, $this->website_id);
    }

    protected function tearDown(): void
    {
        $this->db->rollBack();
    }

    public function testAuthenticationSuccess()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $this->token;
        $_SERVER['HTTP_USER_AGENT'] = 'WordPress/6.2; https://test.com';

        require_once ROOT_PATH . '/core/auth_guard.php';
        $authenticated_user_id = authenticate_plugin_request();

        $this->assertEquals($this->user_id, $authenticated_user_id);
    }
}