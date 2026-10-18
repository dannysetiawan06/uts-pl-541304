<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ItemCrudApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_to_items_returns_401(): void
    {
        $response = $this->getJson('/api/items');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_new_item_returns_201(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'name' => 'Monitor Gaming 240Hz',
            'description' => 'Fast IPS 1ms HDR',
            'price' => 3800000,
            'stock' => 5,
        ];

        $response = $this->postJson('/api/items', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Item created successfully',
                'data' => [
                    'name' => 'Monitor Gaming 240Hz',
                    'user_id' => $user->id,
                ],
            ]);

        $this->assertDatabaseHas('items', [
            'name' => 'Monitor Gaming 240Hz',
            'stock' => 5,
        ]);
    }

    public function test_creating_item_with_empty_or_invalid_fields_returns_422(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/items', [
            'name' => '',
            'price' => 'bukan-angka',
            'stock' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'stock']);
    }

    public function test_authenticated_user_can_list_items_returns_200(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Item::create([
            'user_id' => $user->id,
            'name' => 'Mouse Wireless',
            'description' => 'Ergonomic mouse',
            'price' => 450000,
            'stock' => 12,
        ]);

        $response = $this->getJson('/api/items');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => ['id', 'name', 'price', 'stock', 'user_id', 'created_at', 'updated_at'],
                ],
            ]);
    }

    public function test_authenticated_user_can_update_item_returns_200(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $item = Item::create([
            'user_id' => $user->id,
            'name' => 'Old Item',
            'description' => 'Old description',
            'price' => 100000,
            'stock' => 10,
        ]);

        $response = $this->putJson("/api/items/{$item->id}", [
            'name' => 'Updated Item Name',
            'price' => 150000,
            'stock' => 20,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Item updated successfully',
                'data' => [
                    'id' => $item->id,
                    'name' => 'Updated Item Name',
                ],
            ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Updated Item Name',
        ]);
    }

    public function test_authenticated_user_can_delete_item_returns_200(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $item = Item::create([
            'user_id' => $user->id,
            'name' => 'Item to Delete',
            'price' => 50000,
            'stock' => 1,
        ]);

        $response = $this->deleteJson("/api/items/{$item->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Item deleted successfully',
            ]);

        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
        ]);
    }
}