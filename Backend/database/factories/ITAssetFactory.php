<?php

namespace Database\Factories;

use App\Models\ITAsset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ITAsset>
 */
class ITAssetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ITAsset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $conditions = ['excellent', 'good', 'fair', 'poor', 'damaged'];
        $Status = ['active', 'inactive', 'maintenance', 'disposed'];
        $buildings = ['Main Building', 'Annex Building', 'Warehouse Building', 'Office Building'];
        $departments = ['IT Department', 'HR Department', 'Finance Department', 'Marketing Department', 'Operations Department'];
        $floors = ['1st Floor', '2nd Floor', '3rd Floor', 'Ground Floor', 'Basement'];
        $rooms = ['Room 101', 'Room 201', 'Room 301', 'Conference Room A', 'Meeting Room B'];

        return [
            'asset_number' => 'IT' . date('Y') . date('m') . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'asset_description' => $this->faker->randomElement([
                'Dell Laptop XPS 13',
                'HP Desktop Computer',
                'Canon Laser Printer',
                'Samsung Monitor 24"',
                'Cisco Network Switch',
                'Apple MacBook Pro',
                'Lenovo ThinkPad',
                'Epson Inkjet Printer',
                'Dell OptiPlex Desktop',
                'HP LaserJet Printer'
            ]),
            'building' => $this->faker->randomElement($buildings),
            'floor' => $this->faker->randomElement($floors),
            'department' => $this->faker->randomElement($departments),
            'room' => $this->faker->randomElement($rooms),
            'condition' => $this->faker->randomElement($conditions),
            'status' => $this->faker->randomElement($Status),
            'notes' => $this->faker->optional(0.7)->sentence(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the asset is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the asset is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }

    /**
     * Indicate that the asset is disposed.
     */
    public function disposed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disposed',
        ]);
    }

    /**
     * Indicate that the asset is in excellent condition.
     */
    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'excellent',
        ]);
    }

    /**
     * Indicate that the asset is in poor condition.
     */
    public function poor(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'poor',
        ]);
    }

    /**
     * Indicate that the asset is damaged.
     */
    public function damaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'damaged',
        ]);
    }

    /**
     * Indicate that the asset belongs to IT department.
     */
    public function itDepartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'department' => 'IT Department',
        ]);
    }

    /**
     * Indicate that the asset is in Main Building.
     */
    public function mainBuilding(): static
    {
        return $this->state(fn (array $attributes) => [
            'building' => 'Main Building',
        ]);
    }
}
