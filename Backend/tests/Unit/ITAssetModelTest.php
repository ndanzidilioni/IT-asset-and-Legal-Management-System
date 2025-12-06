<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\ITAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ITAssetModelTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_an_asset()
    {
        $assetData = [
            'asset_number' => 'IT202501001',
            'asset_description' => 'Dell Laptop XPS 13',
            'building' => 'Main Building',
            'floor' => '2nd Floor',
            'department' => 'IT Department',
            'room' => 'Room 201',
            'condition' => 'excellent',
            'status' => 'active',
            'notes' => 'Brand new laptop',
            'created_by' => $this->user->id
        ];

        $asset = ITAsset::create($assetData);

        $this->assertInstanceOf(ITAsset::class, $asset);
        $this->assertEquals($assetData['asset_number'], $asset->asset_number);
        $this->assertEquals($assetData['asset_description'], $asset->asset_description);
        $this->assertEquals($assetData['building'], $asset->building);
        $this->assertEquals($assetData['department'], $asset->department);
        $this->assertEquals($assetData['condition'], $asset->condition);
        $this->assertEquals($assetData['status'], $asset->status);
    }

    /** @test */
    public function it_belongs_to_a_creator()
    {
        $asset = ITAsset::factory()->create(['created_by' => $this->user->id]);

        $this->assertInstanceOf(User::class, $asset->creator);
        $this->assertEquals($this->user->id, $asset->creator->id);
    }

    /** @test */
    public function it_can_scope_active_assets()
    {
        ITAsset::factory()->create(['status' => 'active', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['status' => 'inactive', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['status' => 'maintenance', 'created_by' => $this->user->id]);

        $activeAssets = ITAsset::active()->get();

        $this->assertCount(1, $activeAssets);
        $this->assertEquals('active', $activeAssets->first()->status);
    }

    /** @test */
    public function it_can_scope_by_condition()
    {
        ITAsset::factory()->create(['condition' => 'excellent', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['condition' => 'good', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['condition' => 'poor', 'created_by' => $this->user->id]);

        $excellentAssets = ITAsset::byCondition('excellent')->get();

        $this->assertCount(1, $excellentAssets);
        $this->assertEquals('excellent', $excellentAssets->first()->condition);
    }

    /** @test */
    public function it_can_scope_by_status()
    {
        ITAsset::factory()->create(['status' => 'active', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['status' => 'maintenance', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['status' => 'disposed', 'created_by' => $this->user->id]);

        $maintenanceAssets = ITAsset::byStatus('maintenance')->get();

        $this->assertCount(1, $maintenanceAssets);
        $this->assertEquals('maintenance', $maintenanceAssets->first()->status);
    }

    /** @test */
    public function it_can_scope_by_department()
    {
        ITAsset::factory()->create(['department' => 'IT Department', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['department' => 'HR Department', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['department' => 'Finance Department', 'created_by' => $this->user->id]);

        $itAssets = ITAsset::byDepartment('IT')->get();

        $this->assertCount(1, $itAssets);
        $this->assertStringContainsString('IT', $itAssets->first()->department);
    }

    /** @test */
    public function it_can_scope_by_building()
    {
        ITAsset::factory()->create(['building' => 'Main Building', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['building' => 'Annex Building', 'created_by' => $this->user->id]);
        ITAsset::factory()->create(['building' => 'Warehouse Building', 'created_by' => $this->user->id]);

        $mainBuildingAssets = ITAsset::byBuilding('Main')->get();

        $this->assertCount(1, $mainBuildingAssets);
        $this->assertStringContainsString('Main', $mainBuildingAssets->first()->building);
    }

    /** @test */
    public function it_generates_unique_asset_numbers()
    {
        $assetNumber1 = ITAsset::generateAssetNumber();
        $assetNumber2 = ITAsset::generateAssetNumber();

        $this->assertNotEquals($assetNumber1, $assetNumber2);
        $this->assertStringStartsWith('IT', $assetNumber1);
        $this->assertStringStartsWith('IT', $assetNumber2);
    }

    /** @test */
    public function it_increments_asset_number_sequence()
    {
        // Create an asset with a specific number pattern
        $firstAsset = ITAsset::create([
            'asset_number' => 'IT2025010001',
            'asset_description' => 'First Asset',
            'building' => 'Test Building',
            'floor' => '1st Floor',
            'department' => 'Test Department',
            'room' => 'Room 101',
            'condition' => 'good',
            'status' => 'active',
            'created_by' => $this->user->id
        ]);

        // Generate a new asset number
        $newAssetNumber = ITAsset::generateAssetNumber();

        // The new number should be different from the existing one
        $this->assertNotEquals($firstAsset->asset_number, $newAssetNumber);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
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
        ];

        $asset = new ITAsset();

        $this->assertEquals($fillable, $asset->getFillable());
    }

    /** @test */
    public function it_casts_timestamps_to_datetime()
    {
        $asset = ITAsset::factory()->create(['created_by' => $this->user->id]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $asset->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $asset->updated_at);
    }

    /** @test */
    public function it_can_be_soft_deleted()
    {
        $asset = ITAsset::factory()->create(['created_by' => $this->user->id]);
        $assetId = $asset->id;

        $asset->delete();

        $this->assertSoftDeleted('it_assets', ['id' => $assetId]);
    }

    /** @test */
    public function it_can_restore_soft_deleted_asset()
    {
        $asset = ITAsset::factory()->create(['created_by' => $this->user->id]);
        $asset->delete();

        $this->assertSoftDeleted('it_assets', ['id' => $asset->id]);

        $asset->restore();

        $this->assertDatabaseHas('it_assets', [
            'id' => $asset->id,
            'deleted_at' => null
        ]);
    }
}
