<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $code = 'CUST-'.str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT);

        return [
            'user_id' => null,
            'working_group_id' => 1, // override in tests
            'customer_code' => $code,
            'full_name' => fake()->name(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->numerify('07########'),
            'whatsapp_number' => null,
            'company_name' => null,
            'company_phone' => null,
            'company_reg_no' => null,
            'type' => 'walk_in',
            'status' => 'active',
            'email_notifications' => true,
            'sms_notifications' => false,
            'notes' => null,
        ];
    }
}

