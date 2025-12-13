<?php

namespace Tests\Feature;

use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_routes_index(): void
    {
        $response = $this->get('/routes');

        $response->assertOk();
    }

    public function test_guest_can_view_approved_route_show(): void
    {
        $route = Route::factory()->approved()->create();

        $response = $this->get(route('routes.show', $route));

        $response->assertOk();
    }

    public function test_guest_cannot_view_pending_route_show(): void
    {
        $route = Route::factory()->pending()->create();

        $response = $this->get(route('routes.show', $route));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_from_routes_create(): void
    {
        $response = $this->get(route('routes.create'));

        $response->assertRedirectToRoute('login');
    }
}

