<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_growth_metrics_and_charts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(3)->create();

        $category = Category::create([
            'name' => 'Workshop',
            'slug' => 'workshop',
        ]);

        Event::create([
            'category_id' => $category->id,
            'title' => 'Laravel Meetup',
            'description' => 'Test event',
            'date' => now()->addDay(),
            'location' => 'Bandung',
            'price' => 100000,
            'stock' => 50,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Total Pengguna');
        $response->assertSee('Pertumbuhan Pengguna');
        $response->assertSee('Pertumbuhan Event');
        $response->assertSee(now()->subMonths(5)->translatedFormat('M'));
    }
}
