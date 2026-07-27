<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_can_only_see_its_own_events(): void
    {
        $category = Category::create([
            'name' => 'Workshop',
            'slug' => 'workshop',
        ]);

        $organizerA = Organizer::create([
            'user_id' => User::factory()->create(['role' => 'organizer'])->id,
            'name' => 'HIMA A',
            'slug' => 'hima-a',
            'status' => 'approved',
        ]);

        $organizerB = Organizer::create([
            'user_id' => User::factory()->create(['role' => 'organizer'])->id,
            'name' => 'HIMA B',
            'slug' => 'hima-b',
            'status' => 'approved',
        ]);

        Event::create([
            'organizer_id' => $organizerA->id,
            'category_id' => $category->id,
            'title' => 'Event A',
            'description' => 'A',
            'date' => now()->addDay(),
            'location' => 'Bandung',
            'price' => 10000,
            'stock' => 20,
        ]);

        Event::create([
            'organizer_id' => $organizerB->id,
            'category_id' => $category->id,
            'title' => 'Event B',
            'description' => 'B',
            'date' => now()->addDays(2),
            'location' => 'Jakarta',
            'price' => 20000,
            'stock' => 10,
        ]);

        $this->actingAs($organizerA->user)->get(route('organizer.dashboard'))
            ->assertOk()
            ->assertSee('Event A')
            ->assertDontSee('Event B');
    }
}
