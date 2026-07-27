<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreeEventCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_event_checkout_is_marked_success_and_reduces_stock(): void
    {
        $category = Category::create([
            'name' => 'Gratis',
            'slug' => 'gratis',
        ]);

        $event = Event::create([
            'category_id' => $category->id,
            'title' => 'Free Webinar',
            'description' => 'Event gratis',
            'date' => now()->addDay(),
            'location' => 'Online',
            'price' => 0,
            'stock' => 10,
        ]);

        $response = $this->post(route('checkout.store', $event), [
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '6281234567890',
        ]);

        $response->assertRedirect(route('checkout.success', ['order_id' => $this->getLastOrderId()]));

        $this->assertDatabaseHas('transactions', [
            'event_id' => $event->id,
            'status' => 'success',
            'total_price' => 0,
        ]);

        $event->refresh();
        $this->assertSame(9, $event->stock);
    }

    protected function getLastOrderId(): string
    {
        $transaction = Transaction::latest()->first();

        return $transaction ? $transaction->order_id : '';
    }
}
