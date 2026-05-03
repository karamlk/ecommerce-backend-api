<?php

namespace Tests\Feature\Order;

use App\Jobs\SendOrderConfirmationJob;
use App\Mail\OrderConfirmationMail;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderAsyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_confirmation_job_is_dispatched_after_store()
    {
        Queue::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100,
        ]);

        CartItem::create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders');

        $response->assertStatus(201);

        Queue::assertPushed(SendOrderConfirmationJob::class, function ($job) use ($user) {
            return $job->email === $user->email;
        });
    }

    public function test_order_confirmation_email_is_not_sent_synchronously()
    {
        Queue::fake();
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 100,
        ]);

        CartItem::create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/orders')->assertStatus(201);

        // Email was NOT sent during the request — it's in the queue
        Mail::assertNotSent(OrderConfirmationMail::class);

        // But the job IS in the queue waiting to send it
        Queue::assertPushed(SendOrderConfirmationJob::class);
    }
}
