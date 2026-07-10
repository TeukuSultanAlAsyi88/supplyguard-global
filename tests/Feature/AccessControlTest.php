<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/masuk');
    }

    public function test_normal_user_cannot_open_admin_dashboard(): void
    {
        $user = User::where('email', 'user@supplyguard.test')->firstOrFail();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_open_admin_dashboard(): void
    {
        $admin = User::where('email', 'admin@supplyguard.test')->firstOrFail();
        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}
