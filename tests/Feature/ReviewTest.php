<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_review_for_successful_settlement_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Workshop',
            'slug' => 'workshop',
        ]);
        $event = Event::create([
            'category_id' => $category->id,
            'title' => 'Laravel Meetup',
            'description' => 'Test event',
            'date' => now()->subDays(2),
            'location' => 'Bandung',
            'price' => 100000,
            'stock' => 50,
        ]);

        Transaction::create([
            'event_id' => $event->id,
            'order_id' => 'ORDER-1',
            'customer_name' => 'Test Customer',
            'customer_email' => $user->email,
            'customer_phone' => '081234567890',
            'total_price' => 100000,
            'status' => 'settlement',
        ]);

        $response = $this->actingAs($user)->post(route('reviews.store', $event), [
            'rating' => 5,
            'comment' => 'Acara sangat bagus',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'rating' => 5,
            'comment' => 'Acara sangat bagus',
        ]);
    }
}
