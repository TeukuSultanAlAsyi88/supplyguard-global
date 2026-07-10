<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchlistTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_user_can_add_and_remove_watchlist_via_ajax(): void
    {
        $user = User::where('email', 'user@supplyguard.test')->firstOrFail();
        $country = Country::where('code', 'ID')->firstOrFail();

        $create = $this->actingAs($user)
            ->postJson('/daftar-pemantauan/'.$country->id);

        $create->assertOk()->assertJsonPath('success', true);
        $watchlistId = $create->json('data.id');
        $this->assertDatabaseHas('watchlists', ['id' => $watchlistId, 'user_id' => $user->id]);

        $this->actingAs($user)
            ->deleteJson('/daftar-pemantauan/'.$watchlistId)
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('watchlists', ['id' => $watchlistId]);
    }
}
