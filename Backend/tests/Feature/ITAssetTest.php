<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ITAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ITAssetTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->user = User::factory()->create([
            'role' => 'user'
        ]);
        
        $this->adminUser = User::factory()->create([
            'role' => 'admin'
        ]);
    }

    /** @test */
    public function authenticated_user_can_view_assets_list()
    {
        // Create some test assets
        ITAsset::factory()->count(3)->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/it-assets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'asset_number',
                        'asset_description',
                        'building',
                        'floor',
                        'department',
                        'room',
                        'condition',
                        'status',
                        'notes',
                        'created_by',
                        'creator'
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_create_new_asset()
    {
        $assetData = [
            'asset_description' => 'Dell Laptop - Model XPS 13',
            'building' => 'Main Building',
            'floor' => '2nd Floor',
            'department' => 'IT Department',
            'room' => 'Room 201',
            'condition' => 'excellent',
            'status' => 'active',
            'notes' => 'Brand new laptop for development work'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/it-assets', $assetData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'IT Asset created successfully'
            ]);

        $this->assertDatabaseHas('it_assets', [
            'asset_description' => $assetData['asset_description'],
            'building' => $assetData['building'],
            'department' => $assetData['department'],
            'created_by' => $this->user->id
        ]);
    }

    /** @test */
    public function asset_number_is_auto_generated_when_not_provided()
    {
        $assetData = [
            'asset_description' => 'Test Asset',
            'building' => 'Test Building',
            'floor' => '1st Floor',
            'department' => 'Test Department',
            'room' => 'Room 101',
            'condition' => 'good',
            'status' => 'active'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/it-assets', $assetData);

        $response->assertStatus(201);
        
        $asset = ITAsset::latest()->first();
        $this->assertNotNull($asset->asset_number);
        $this->assertStringStartsWith('IT', $asset->asset_number);
    }

    /** @test */
    public function user_can_view_specific_asset()
    {
        $asset = ITAsset::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/it-assets/{$asset->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $asset->id,
                    'asset_number' => $asset->asset_number,
                    'asset_description' => $asset->asset_description
                ]
            ]);
    }

    /** @test */
    public function user_can_update_asset()
    {
        $asset = ITAsset::factory()->create(['created_by' => $this->user->id]);

        $updateData = [
            'asset_description' => 'Updated Asset Description',
            'condition' => 'fair',
            'status' => 'maintenance'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/it-assets/{$asset->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'IT Asset updated successfully'
            ]);

        $this->assertDatabaseHas('it_assets', [
            'id' => $asset->id,
            'asset_description' => $updateData['asset_description'],
            'condition' => $updateData['condition'],
            'status' => $updateData['status']
        ]);
    }

    /** @test */
    public function user_can_delete_asset()
    {
        $asset = ITAsset::factory()->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/it-assets/{$asset->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'IT Asset deleted successfully'
            ]);

        $this->assertDatabaseMissing('it_assets', ['id' => $asset->id]);
    }

    /** @test */
    public function user_can_filter_assets_by_status()
    {
        ITAsset::factory()->create(['status' => 'active', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['status' => 'maintenance', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['status' => 'disposed', 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/it-assets?status=active');

        $response->assertStatus(200);
        $assets = $response->json('data');
        
        $this->assertCount(1, $assets);
        $this->assertEquals('active', $assets[0]['status']);
    }

    /** @test */
    public function user_can_filter_assets_by_condition()
    {
        ITAsset::factory()->create(['condition' => 'excellent', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['condition' => 'good', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['condition' => 'poor', 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/it-assets?condition=excellent');

        $response->assertStatus(200);
        $assets = $response->json('data');
        
        $this->assertCount(1, $assets);
        $this->assertEquals('excellent', $assets[0]['condition']);
    }

    /** @test */
    public function user_can_search_assets()
    {
        ITAsset::factory()->create([
            'asset_description' => 'Dell Laptop',
            'department' => 'IT Department',
            'created_by' => $this->user->id
        ]);
        ITAsset::factory()->create([
            'asset_description' => 'HP Printer',
            'department' => 'HR Department',
            'created_by' => $this->user->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/it-assets?search=Dell');

        $response->assertStatus(200);
        $assets = $response->json('data');
        
        $this->assertCount(1, $assets);
        $this->assertStringContainsString('Dell', $assets[0]['asset_description']);
    }

    /** @test */
    public function user_can_get_asset_statistics()
    {
        ITAsset::factory()->create(['status' => 'active', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['status' => 'active', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['status' => 'maintenance', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['status' => 'disposed', 'created_by' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/it-assets/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total' => 4,
                    'active' => 2,
                    'maintenance' => 1,
                    'disposed' => 1
                ]
            ]);
    }

    /** @test */
    public function user_can_export_assets_to_csv()
    {
        ITAsset::factory()->count(2)->create(['created_by' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/it-assets/export/csv');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function user_can_import_assets_from_csv()
    {
        Storage::fake('local');
        
        $csvContent = "Asset Number,Asset Description,Building,Floor,Department,Room,Condition,Status,Notes\n";
        $csvContent .= "IT202501001,Test Laptop,Main Building,1st Floor,IT Dept,Room 101,excellent,active,Test notes\n";
        $csvContent .= "IT202501002,Test Printer,Main Building,2nd Floor,HR Dept,Room 201,good,active,Another test\n";

        $file = UploadedFile::fake()->createWithContent('assets.csv', $csvContent);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/it-assets/import/csv', [
                'csv_file' => $file
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'imported' => 2
            ]);

        $this->assertDatabaseCount('it_assets', 2);
    }

    /** @test */
    public function asset_creation_requires_authentication()
    {
        $assetData = [
            'asset_description' => 'Test Asset',
            'building' => 'Test Building',
            'floor' => '1st Floor',
            'department' => 'Test Department',
            'room' => 'Room 101',
            'condition' => 'good',
            'status' => 'active'
        ];

        $response = $this->postJson('/api/it-assets', $assetData);

        $response->assertStatus(401);
    }

    /** @test */
    public function asset_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/it-assets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'asset_description',
                'building',
                'floor',
                'department',
                'room',
                'condition',
                'status'
            ]);
    }

    /** @test */
    public function asset_creation_validates_enum_values()
    {
        $assetData = [
            'asset_description' => 'Test Asset',
            'building' => 'Test Building',
            'floor' => '1st Floor',
            'department' => 'Test Department',
            'room' => 'Room 101',
            'condition' => 'invalid_condition',
            'status' => 'invalid_status'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/it-assets', $assetData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['condition', 'status']);
    }

    /** @test */
    public function asset_number_must_be_unique()
    {
        $existingAsset = ITAsset::factory()->create(['created_by' => $this->user->id]);

        $assetData = [
            'asset_number' => $existingAsset->asset_number,
            'asset_description' => 'Test Asset',
            'building' => 'Test Building',
            'floor' => '1st Floor',
            'department' => 'Test Department',
            'room' => 'Room 101',
            'condition' => 'good',
            'status' => 'active'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/it-assets', $assetData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['asset_number']);
    }
}
