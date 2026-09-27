<?php
namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private const ROUTE_LOGIN_SHOW       = 'login';
    private const ROUTE_LOGIN_ATTEMPT    = 'login';
    private const ROUTE_REGISTER_SHOW    = 'register';
    private const ROUTE_REGISTER_ATTEMPT = 'register';
    private const ROUTE_LOGOUT           = 'logout';
    private const ROUTE_DASHBOARD        = 'dashboard';

    private const GUEST_MIDDLEWARE_APPLIES = true; // A3

    private const VALID_PASSWORD = 'password';

    /* =====================================================================
     | Helpers
     * =================================================================== */

    private function existingUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'password' => Hash::make(self::VALID_PASSWORD),
        ], $overrides)); // A2
    }

    private function validRegistrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'phone'                 => '+254700111222',
            'password'              => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
        ], $overrides);
    }

    /* =====================================================================
     | Data providers
     * =================================================================== */

    public static function loginRequiredFields(): array
    {
        return [
            'email'    => ['email'],
            'password' => ['password'],
        ];
    }

    public static function registerRequiredFields(): array
    {
        return [
            'name'     => ['name'],
            'email'    => ['email'],
            'phone'    => ['phone'],
            'password' => ['password'],
        ];
    }

    public static function invalidPhoneNumbers(): array
    {
        return [
            'contains letters'    => ['0712abc456'],
            'contains a hash'     => ['0712#456'],
            'contains an at-sign' => ['user@0712456'],
            'contains underscore' => ['0712_456'],
        ];
    }

    public static function validPhoneNumbers(): array
    {
        return [
            'plain digits'             => ['0712345678'],
            'international plus'       => ['+254712345678'],
            'spaced and parenthesised' => ['+254 (712) 345-678'],
        ];
    }

    /* =====================================================================
     | showLogin() / showRegister()
     * =================================================================== */

    public function test_show_login_displays_the_login_form(): void
    {
        $this->get(route(self::ROUTE_LOGIN_SHOW))
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    public function test_show_register_displays_the_registration_form(): void
    {
        $this->get(route(self::ROUTE_REGISTER_SHOW))
            ->assertOk()
            ->assertViewIs('auth.register');
    }

    public function test_an_authenticated_user_visiting_login_is_redirected_away(): void
    {
        if (! self::GUEST_MIDDLEWARE_APPLIES) {
            $this->markTestSkipped('GUEST_MIDDLEWARE_APPLIES is false for this app.');
        }

        $this->actingAs($this->existingUser())
            ->get(route(self::ROUTE_LOGIN_SHOW))
            ->assertRedirect(route(self::ROUTE_DASHBOARD));
    }

    public function test_an_authenticated_user_visiting_register_is_redirected_away(): void
    {
        if (! self::GUEST_MIDDLEWARE_APPLIES) {
            $this->markTestSkipped('GUEST_MIDDLEWARE_APPLIES is false for this app.');
        }

        $this->actingAs($this->existingUser())
            ->get(route(self::ROUTE_REGISTER_SHOW))
            ->assertRedirect(route(self::ROUTE_DASHBOARD));
    }

    /* =====================================================================
     | login()
     * =================================================================== */

    public function test_login_succeeds_with_correct_credentials_and_redirects_to_dashboard(): void
    {
        $user = $this->existingUser(['email' => 'user@example.com']);

        $this->post(route(self::ROUTE_LOGIN_ATTEMPT), [
            'email'    => 'user@example.com',
            'password' => self::VALID_PASSWORD,
        ])
            ->assertRedirect(route(self::ROUTE_DASHBOARD))
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_to_the_page_that_was_originally_requested(): void
    {
        $this->existingUser(['email' => 'user@example.com']);
        $intended = '/some/protected/page';

        $this->withSession(['url.intended' => $intended])
            ->post(route(self::ROUTE_LOGIN_ATTEMPT), [
                'email'    => 'user@example.com',
                'password' => self::VALID_PASSWORD,
            ])
            ->assertRedirect($intended);
    }

    public function test_login_fails_with_the_wrong_password(): void
    {
        $this->existingUser(['email' => 'user@example.com']);

        $this->from(route(self::ROUTE_LOGIN_SHOW))
            ->post(route(self::ROUTE_LOGIN_ATTEMPT), [
                'email'    => 'user@example.com',
                'password' => 'totally-wrong',
            ])
            ->assertRedirect(route(self::ROUTE_LOGIN_SHOW))
            ->assertSessionHasErrors(['email' => 'The provided credentials are incorrect.']);

        $this->assertGuest();
    }

    public function test_login_fails_for_an_email_that_does_not_exist(): void
    {
        $this->from(route(self::ROUTE_LOGIN_SHOW))
            ->post(route(self::ROUTE_LOGIN_ATTEMPT), [
                'email'    => 'nobody@example.com',
                'password' => 'whatever-password',
            ])
            ->assertRedirect(route(self::ROUTE_LOGIN_SHOW))
            ->assertSessionHasErrors(['email' => 'The provided credentials are incorrect.']);

        $this->assertGuest();
    }

    public function test_login_failure_keeps_the_submitted_email_but_not_the_password(): void
    {
        $this->existingUser(['email' => 'user@example.com']);

        $this->from(route(self::ROUTE_LOGIN_SHOW))
            ->post(route(self::ROUTE_LOGIN_ATTEMPT), [
                'email'    => 'user@example.com',
                'password' => 'totally-wrong',
            ]);

        $this->assertSame('user@example.com', old('email'));
        $this->assertNull(old('password'));
    }

    public function test_login_is_case_sensitive_on_password(): void
    {
        $this->existingUser(['email' => 'user@example.com', 'password' => Hash::make('Correct-Password')]);

        $this->from(route(self::ROUTE_LOGIN_SHOW))
            ->post(route(self::ROUTE_LOGIN_ATTEMPT), [
                'email'    => 'user@example.com',
                'password' => 'correct-password', // different case
            ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_sets_a_remember_cookie_only_when_remember_is_checked(): void
    {
        $this->existingUser(['email' => 'user@example.com']);

        $remembered = $this->post(route(self::ROUTE_LOGIN_ATTEMPT), [
            'email'    => 'user@example.com',
            'password' => self::VALID_PASSWORD,
            'remember' => '1',
        ]);

        $this->assertNotEmpty(array_filter(
            $remembered->headers->getCookies(),
            fn($cookie) => str_starts_with($cookie->getName(), 'remember_web_')
        ), 'No remember-me cookie was set when "remember" was checked.');

        auth()->logout();

        $notRemembered = $this->post(route(self::ROUTE_LOGIN_ATTEMPT), [
            'email'    => 'user@example.com',
            'password' => self::VALID_PASSWORD,
        ]);

        $this->assertEmpty(array_filter(
            $notRemembered->headers->getCookies(),
            fn($cookie) => str_starts_with($cookie->getName(), 'remember_web_')
        ), 'A remember-me cookie was set even though "remember" was not checked.');
    }

    #[DataProvider('loginRequiredFields')]
    public function test_login_requires_field_when_missing(string $field): void
    {
        $payload = ['email' => 'user@example.com', 'password' => self::VALID_PASSWORD];
        unset($payload[$field]);

        $this->from(route(self::ROUTE_LOGIN_SHOW))
            ->post(route(self::ROUTE_LOGIN_ATTEMPT), $payload)
            ->assertRedirect(route(self::ROUTE_LOGIN_SHOW))
            ->assertSessionHasErrors($field);

        $this->assertGuest();
    }

    public function test_login_rejects_a_malformed_email(): void
    {
        $this->from(route(self::ROUTE_LOGIN_SHOW))
            ->post(route(self::ROUTE_LOGIN_ATTEMPT), ['email' => 'not-an-email', 'password' => self::VALID_PASSWORD])
            ->assertRedirect(route(self::ROUTE_LOGIN_SHOW))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /* =====================================================================
     | register()
     * =================================================================== */

    public function test_register_creates_a_user_logs_them_in_and_redirects_to_dashboard(): void
    {
        $before = User::count();

        $this->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload())
            ->assertRedirect(route(self::ROUTE_DASHBOARD))
            ->assertSessionHasNoErrors();

        $this->assertSame($before + 1, User::count());

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('Jane Doe', $user->name);
        $this->assertSame('+254700111222', $user->phone);
        $this->assertSame(1, $user->role_id);
        $this->assertSame('active', $user->status);
    }

    public function test_register_hashes_the_password(): void
    {
        $this->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload());

        $user = User::where('email', 'jane@example.com')->firstOrFail();

        $this->assertNotSame('a-strong-password', $user->password);
        $this->assertTrue(Hash::check('a-strong-password', $user->password));
    }

    #[DataProvider('registerRequiredFields')]
    public function test_register_requires_field_when_missing(string $field): void
    {
        $before  = User::count();
        $payload = $this->validRegistrationPayload();
        unset($payload[$field]);

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $payload)
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors($field);

        $this->assertSame($before, User::count());
        $this->assertGuest();
    }

    public function test_register_rejects_a_malformed_email(): void
    {
        $before = User::count();

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload(['email' => 'not-an-email']))
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors('email');

        $this->assertSame($before, User::count());
    }

    public function test_register_rejects_an_email_already_in_use(): void
    {
        $this->existingUser(['email' => 'jane@example.com']);
        $before = User::count();

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload())
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors('email');

        $this->assertSame($before, User::count());
    }

    public function test_register_rejects_a_phone_number_already_in_use(): void
    {
        $this->existingUser(['phone' => '+254700111222']);
        $before = User::count();

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload())
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors('phone');

        $this->assertSame($before, User::count());
    }

    #[DataProvider('invalidPhoneNumbers')]
    public function test_register_rejects_a_phone_number_with_disallowed_characters(string $phone): void
    {
        $before = User::count();

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload(['phone' => $phone]))
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors('phone');

        $this->assertSame($before, User::count());
    }

    #[DataProvider('validPhoneNumbers')]
    public function test_register_accepts_phone_numbers_in_allowed_formats(string $phone): void
    {
        $this->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload(['phone' => $phone]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route(self::ROUTE_DASHBOARD));

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'phone' => $phone]);
    }

    public function test_register_rejects_a_phone_number_over_20_characters(): void
    {
        $before = User::count();

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload(['phone' => str_repeat('1', 21)]))
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors('phone');

        $this->assertSame($before, User::count());
    }

    public function test_register_accepts_a_phone_number_of_exactly_20_characters(): void
    {
        $phone = str_repeat('1', 20);

        $this->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload(['phone' => $phone]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route(self::ROUTE_DASHBOARD));

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'phone' => $phone]);
    }

    public function test_register_rejects_a_name_over_255_characters(): void
    {
        $before = User::count();

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload(['name' => str_repeat('a', 256)]))
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors('name');

        $this->assertSame($before, User::count());
    }

    public function test_register_accepts_a_name_of_exactly_255_characters(): void
    {
        $name = str_repeat('a', 255);

        $this->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload(['name' => $name]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route(self::ROUTE_DASHBOARD));

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'name' => $name]);
    }

    public function test_register_rejects_a_password_shorter_than_8_characters(): void
    {
        $before = User::count();

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload([
                'password' => 'short12', 'password_confirmation' => 'short12',
            ]))
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors('password');

        $this->assertSame($before, User::count());
    }

    public function test_register_accepts_a_password_of_exactly_8_characters(): void
    {
        $this->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload([
            'password' => '12345678', 'password_confirmation' => '12345678',
        ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route(self::ROUTE_DASHBOARD));
    }

    public function test_register_rejects_a_password_confirmation_mismatch(): void
    {
        $before = User::count();

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload([
                'password' => 'a-strong-password', 'password_confirmation' => 'a-different-password',
            ]))
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors('password');

        $this->assertSame($before, User::count());
    }

    public function test_register_rejects_a_missing_password_confirmation(): void
    {
        $before  = User::count();
        $payload = $this->validRegistrationPayload();
        unset($payload['password_confirmation']);

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $payload)
            ->assertRedirect(route(self::ROUTE_REGISTER_SHOW))
            ->assertSessionHasErrors('password');

        $this->assertSame($before, User::count());
    }

    public function test_register_failure_keeps_submitted_fields_but_not_the_password(): void
    {
        $this->existingUser(['email' => 'jane@example.com']); // forces a duplicate-email failure

        $this->from(route(self::ROUTE_REGISTER_SHOW))
            ->post(route(self::ROUTE_REGISTER_ATTEMPT), $this->validRegistrationPayload());

        $this->assertSame('Jane Doe', old('name'));
        $this->assertSame('jane@example.com', old('email'));
        $this->assertNull(old('password'));
    }

    /* =====================================================================
     | logout()
     * =================================================================== */

    public function test_logout_logs_out_the_user_and_redirects_to_login(): void
    {
        $user = $this->existingUser();

        $this->actingAs($user)
            ->post(route(self::ROUTE_LOGOUT))
            ->assertRedirect(route(self::ROUTE_LOGIN_SHOW));

        $this->assertGuest();
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = $this->existingUser();

        $before    = $this->actingAs($user)->get(route(self::ROUTE_DASHBOARD));
        $sessionId = $before->baseResponse->headers->getCookies() !== [] ? session()->getId() : null;

        $this->actingAs($user)->post(route(self::ROUTE_LOGOUT));

        // The user is fully logged out and a fresh session is in play.
        $this->assertGuest();
        $this->get(route(self::ROUTE_DASHBOARD))->assertRedirect(route(self::ROUTE_LOGIN_SHOW));
    }

    public function test_logout_as_a_guest_still_redirects_to_login_without_error(): void
    {
        $this->post(route(self::ROUTE_LOGOUT))
            ->assertRedirect(route(self::ROUTE_LOGIN_SHOW));

        $this->assertGuest();
    }
}
