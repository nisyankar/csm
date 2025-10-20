<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            
            // Two-factor authentication
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            
            // Account security
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'last_login_ip' => $this->faker->optional(0.8)->ipv4(),
            'password_changed_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            
            // Account status
            'is_active' => $this->faker->boolean(95), // 95% active users
            'is_verified' => $this->faker->boolean(90), // 90% verified
            'is_temp_user' => false,
            'temp_expires_at' => null,
            
            // Profile information
            'avatar_path' => $this->faker->optional(0.3)->imageUrl(200, 200, 'people'),
            'phone' => $this->faker->optional(0.7)->phoneNumber(),
            'timezone' => $this->faker->randomElement([
                'Europe/Istanbul', 'UTC', 'Europe/London', 
                'America/New_York', 'Asia/Tokyo'
            ]),
            'locale' => $this->faker->randomElement(['tr', 'en', 'de', 'fr']),
            'date_format' => $this->faker->randomElement(['Y-m-d', 'd/m/Y', 'm/d/Y']),
            'time_format' => $this->faker->randomElement(['H:i', 'h:i A']),
            
            // Notification preferences
            'notification_preferences' => json_encode([
                'email_notifications' => $this->faker->boolean(80),
                'sms_notifications' => $this->faker->boolean(40),
                'push_notifications' => $this->faker->boolean(70),
                'weekly_digest' => $this->faker->boolean(60),
                'project_updates' => $this->faker->boolean(90),
                'timesheet_reminders' => $this->faker->boolean(85),
            ]),
            
            // Theme preferences
            'theme_preferences' => json_encode([
                'theme' => $this->faker->randomElement(['light', 'dark', 'auto']),
                'sidebar_collapsed' => $this->faker->boolean(30),
                'compact_mode' => $this->faker->boolean(20),
                'color_scheme' => $this->faker->randomElement(['blue', 'green', 'purple', 'orange']),
            ]),
            
            // Login tracking
            'login_history' => json_encode([]),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin User',
            'email' => 'admin@company.com',
            'is_active' => true,
            'is_verified' => true,
            'email_verified_at' => now(),
            'notification_preferences' => json_encode([
                'email_notifications' => true,
                'sms_notifications' => true,
                'push_notifications' => true,
                'weekly_digest' => true,
                'project_updates' => true,
                'timesheet_reminders' => false, // Admin doesn't need timesheet reminders
            ]),
        ]);
    }

    /**
     * Indicate that the user is a manager.
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'is_verified' => true,
            'notification_preferences' => json_encode([
                'email_notifications' => true,
                'sms_notifications' => false,
                'push_notifications' => true,
                'weekly_digest' => true,
                'project_updates' => true,
                'timesheet_reminders' => false,
                'approval_notifications' => true,
                'team_updates' => true,
            ]),
        ]);
    }

    /**
     * Indicate that the user is an HR personnel.
     */
    public function hr(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'is_verified' => true,
            'notification_preferences' => json_encode([
                'email_notifications' => true,
                'sms_notifications' => true,
                'push_notifications' => true,
                'weekly_digest' => true,
                'project_updates' => false,
                'timesheet_reminders' => false,
                'leave_requests' => true,
                'employee_updates' => true,
                'compliance_alerts' => true,
            ]),
        ]);
    }

    /**
     * Indicate that the user is a regular employee.
     */
    public function employee(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'is_verified' => true,
            'notification_preferences' => json_encode([
                'email_notifications' => true,
                'sms_notifications' => false,
                'push_notifications' => true,
                'weekly_digest' => false,
                'project_updates' => true,
                'timesheet_reminders' => true,
            ]),
        ]);
    }

    /**
     * Indicate that the user has unverified email.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'is_verified' => false,
        ]);
    }

    /**
     * Indicate that the user account is locked.
     */
    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'failed_login_attempts' => 5,
            'locked_until' => now()->addHours(1),
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'last_login_at' => $this->faker->dateTimeBetween('-1 year', '-3 months'),
        ]);
    }

    /**
     * Indicate that the user is a temporary user.
     */
    public function temporary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_temp_user' => true,
            'temp_expires_at' => now()->addDays(30),
            'password' => Hash::make('temp123'),
            'email_verified_at' => null,
            'is_verified' => false,
        ]);
    }

    /**
     * Indicate that the user has two-factor authentication enabled.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('base32secret'),
            'two_factor_recovery_codes' => encrypt(json_encode([
                'ABCD-1234', 'EFGH-5678', 'IJKL-9012',
                'MNOP-3456', 'QRST-7890', 'UVWX-1234',
                'YZAB-5678', 'CDEF-9012'
            ])),
            'two_factor_confirmed_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    /**
     * Indicate that the user prefers dark theme.
     */
    public function darkTheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'theme_preferences' => json_encode([
                'theme' => 'dark',
                'sidebar_collapsed' => false,
                'compact_mode' => true,
                'color_scheme' => 'blue',
            ]),
        ]);
    }

    /**
     * Indicate that the user has minimal notifications.
     */
    public function minimalNotifications(): static
    {
        return $this->state(fn (array $attributes) => [
            'notification_preferences' => json_encode([
                'email_notifications' => false,
                'sms_notifications' => false,
                'push_notifications' => true,
                'weekly_digest' => false,
                'project_updates' => true,
                'timesheet_reminders' => true,
            ]),
        ]);
    }

    /**
     * Indicate that the user has Turkish locale.
     */
    public function turkish(): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => 'tr',
            'timezone' => 'Europe/Istanbul',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
        ]);
    }

    /**
     * Indicate that the user is a new user (recent signup).
     */
    public function newUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDays(rand(1, 7)),
            'last_login_at' => null,
            'password_changed_at' => now()->subDays(rand(1, 7)),
            'login_history' => json_encode([]),
        ]);
    }
}