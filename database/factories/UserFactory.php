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
        $issuePlaces = [
            'Cục Cảnh sát Quản lý hành chính về trật tự xã hội',
            'Công an thành phố Hà Nội',
            'Công an quận Thanh Xuân',
            'Công an quận Hà Đông',
            'Công an quận Cầu Giấy',
            'Công an quận Đống Đa',
            'Công an quận Ba Đình',
            'Công an quận Hoàn Kiếm',
        ];

        $genders = ['male', 'female'];
        
        // Tạo ngày sinh hợp lý (18-65 tuổi)
        $dateOfBirth = fake()->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d');
        
        // Tạo ngày cấp CCCD hợp lý (sau ngày sinh, trong vòng 10 năm gần đây)
        $idCardIssueDate = fake()->dateTimeBetween('-10 years', 'now')->format('Y-m-d');

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('123456'),
            'remember_token' => Str::random(10),
            'phone' => fake()->unique()->phoneNumber(),
            'id_card_number' => '0012' . str_pad((string) fake()->randomNumber(8, true), 8, '0', STR_PAD_LEFT),
            'id_card_issue_date' => $idCardIssueDate,
            'id_card_issue_place' => fake()->randomElement($issuePlaces),
            'permanent_address' => fake()->address(),
            'date_of_birth' => $dateOfBirth,
            'gender' => fake()->randomElement($genders),
            'avatar' => null,
            'role' => 'customer',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
