<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\PaymentMethod;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use App\Services\CaptchaService;
use App\Enums\ResponseCode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $captchaServiceMock;
    protected $category;
    protected $subcategory;
    protected $paymentMethod;
    const PASSWORD = 'password';
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => Hash::make(self::PASSWORD),
        ]);
        $this->actingAs($this->user, 'sanctum');
        putenv('CAPTCHA_ENABLED=true'); // 临时设置环境变量
    }

    #[Test]
    public function test_register_success_with_mocked_captcha()
    {
        // 設定 mock 來模擬 Captcha 驗證成功
        $this->mock(CaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once() // 預期方法被調用一次
                ->andReturn(true);  // 模擬驗證碼驗證成功
        });

        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'captcha_token' => 'dummy-captcha-token',
        ];

        $response = $this->postJson('/api/register', $data);
        $response->assertStatus(201); // 應該返回201
        $response->assertJson([
            'status' => 'success',
            'code' => ResponseCode::REGISTER_SUCCESS,
            'message' => ResponseCode::getErrorDetails(ResponseCode::REGISTER_SUCCESS)['title'],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
        ]);
    }

    // #[Test]
    public function test_register_fail_due_to_invalid_captcha()
    {
        // 設定 mock 來模擬 Captcha 驗證失敗
        $this->mock(CaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once() // 預期方法被調用一次
                ->andReturn(false);  // 模擬驗證碼驗證成功
        });


        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'captcha_token' => 'invalid-captcha-token',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(400); // 應該返回400
        $response->assertJson([
            'status' => 'fail',
            'code' => ResponseCode::CAPTCHA_INVALID,
            'message' => ResponseCode::getErrorDetails(ResponseCode::CAPTCHA_INVALID)['title'],
        ]);
    }

    #[Test]
    public function test_login_success_with_mocked_captcha()
    {
        // 設定 mock 來模擬 Captcha 驗證成功
        $this->mock(CaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once() // 預期方法被調用一次
                ->andReturn(true);  // 模擬驗證碼驗證成功
        });


        $data = [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'password' => self::PASSWORD,
            'captcha_token' => 'dummy-captcha-token',
        ];
        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(200); // 應該返回200
        $response->assertJson([
            'status' => 'success',
            'code' => ResponseCode::LOGIN_SUCCESS,
            'message' => ResponseCode::getErrorDetails(ResponseCode::LOGIN_SUCCESS)['title'],
        ]);
    }

    #[Test]
    public function test_login_fail_due_to_invalid_captcha()
    {
        // 設定 mock 來模擬 Captcha 驗證失敗
        $this->mock(CaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once() // 預期方法被調用一次
                ->andReturn(false);  // 模擬驗證碼驗證失敗
        });

        $data = [
            'email' => $this->user->email,
            'password' => self::PASSWORD,
            'captcha_token' => 'invalid-captcha-token',
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(400); // 應該返回400
        $response->assertJson([
            'status' => 'fail',
            'code' => ResponseCode::CAPTCHA_INVALID,
            'message' => ResponseCode::getErrorDetails(ResponseCode::CAPTCHA_INVALID)['title'],
        ]);
    }
    #[Test]
    public function test_register_fail_due_to_email_exists()
    {
        $this->mock(CaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once() // 預期方法被調用一次
                ->andReturn(true);  // 模擬驗證碼驗證成功
        });
        // 先創建一個已存在的用戶
        $existingUser = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        $data = [
            'name' => 'New Test User',
            'email' => 'testuser@example.com',  // 已存在的電子郵件
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'captcha_token' => 'dummy-captcha-token',
        ];

        $response = $this->postJson('/api/register', $data);

        $response->assertStatus(400); // 應該返回400
        $response->assertJson([
            'status' => 'fail',
            'code' => ResponseCode::REGISTER_EMAIL_EXISTS,
            'message' => ResponseCode::getErrorDetails(ResponseCode::REGISTER_EMAIL_EXISTS)['title'],
        ]);
    }
    #[Test]
    public function test_login_fail_due_to_invalid_password()
    {
        $this->mock(CaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once() // 預期方法被調用一次
                ->andReturn(true);  // 模擬驗證碼驗證成功
        });
        $data = [
            'email' => $this->user->email,
            'password' => 'wrongpassword',  // 錯誤的密碼
            'captcha_token' => 'dummy-captcha-token',
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(401); // 應該返回400
        $response->assertJson([
            'status' => 'fail',
            'code' => ResponseCode::LOGIN_INVALID_PASSWORD,
            'message' => ResponseCode::getErrorDetails(ResponseCode::LOGIN_INVALID_PASSWORD)['title'],
        ]);
    }
    #[Test]
    public function test_login_fail_due_to_user_not_found()
    {
        $this->mock(CaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once() // 預期方法被調用一次
                ->andReturn(true);  // 模擬驗證碼驗證成功
        });
        $data = [
            'email' => 'nonexistentuser@example.com',  // 不存在的電子郵件
            'password' => self::PASSWORD,
            'captcha_token' => 'dummy-captcha-token',
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(401); // 應該返回400
        $response->assertJson([
            'status' => 'fail',
            'code' => ResponseCode::LOGIN_USER_NOT_FOUND,
            'message' => ResponseCode::getErrorDetails(ResponseCode::LOGIN_USER_NOT_FOUND)['title'],
        ]);
    }
}
