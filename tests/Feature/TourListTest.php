<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourListTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_tour_list_by_travel_slug_return_correct_tours(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);

        $res = $this->get("/api/v1/travels/$travel->slug/tour");

        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data');
        $res->assertJsonFragment(['id' => $tour->id]);

    }

    public function test_tour_price_is_shown_correctly(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create(['travel_id' => $travel->id, 'price' => 123.23]);

        $res = $this->get("/api/v1/travels/{$travel->slug}/tour");
        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data');
        $res->assertJsonFragment(['price' => '123.23']);

    }

    public function test_tour_list_returns_pagination(): void
    {
        $toursPerPage = config('app.paginationPerpage.tours');

        $travel = Travel::factory()->create();
        $tour = Tour::factory($toursPerPage + 1)->create(['travel_id' => $travel->id, 'price' => 123.23]);

        $res = $this->get("/api/v1/travels/$travel->slug/tour");

        $res->assertStatus(200);
        $res->assertJsonCount($toursPerPage, 'data');
        $res->assertJsonPath('meta.last_page', 2);

    }

    public function test_tours_list_sort_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();

        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(5),
        ]);

        $earlierTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(2),
        ]);

        $res = $this->get("/api/v1/travels/$travel->slug/tour");

        $res->assertStatus(200);
        $res->assertJsonPath('data.0.id', $earlierTour->id);
        $res->assertJsonPath('data.1.id', $laterTour->id);
    }

    public function test_tours_list_filter_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();

        $expensiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);

        $cheaperTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
        ]);

        $endpoint = "/api/v1/travels/$travel->slug/tour";

        $res = $this->get($endpoint.'?priceFrom=100');
        $res->assertStatus(200);
        $res->assertJsonCount(2, 'data');
        $res->assertJsonFragment(['id' => $expensiveTour->id]);
        $res->assertJsonFragment(['id' => $cheaperTour->id]);

        $res = $this->get($endpoint.'?priceFrom=150');
        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data');
        $res->assertJsonMissing(['id' => $cheaperTour->id]);
        $res->assertJsonFragment(['id' => $expensiveTour->id]);

        $res = $this->get($endpoint.'?priceFrom=250');
        $res->assertStatus(200);
        $res->assertJsonCount(0, 'data');

        $res = $this->get($endpoint.'?priceTo=200');
        $res->assertStatus(200);
        $res->assertJsonCount(2, 'data');
        $res->assertJsonFragment(['id' => $expensiveTour->id]);
        $res->assertJsonFragment(['id' => $cheaperTour->id]);

        $res = $this->get($endpoint.'?priceTo=150');
        $res->assertStatus(200);
        $res->assertJsonCount(1, 'data');
        $res->assertJsonMissing(['id' => $expensiveTour->id]);
        $res->assertJsonFragment(['id' => $cheaperTour->id]);

        $res = $this->get($endpoint.'?priceFrom=100&priceTo=250');
        $res->assertStatus(200);
        $res->assertJsonCount(2, 'data');
        $res->assertJsonFragment(['id' => $expensiveTour->id]);
        $res->assertJsonFragment(['id' => $cheaperTour->id]);

    }

    public function test_tours_returns_validation_errors(): void
    {
        $travel = Travel::factory()->create();

        $res = $this->getJson("/api/v1/travels/$travel->slug/tour?dateFrom=abcde");
        $res->assertStatus(422);

        $res = $this->getJson("/api/v1/travels/$travel->slug/tour?priceFrom=abcde");
        $res->assertStatus(422);

    }
}
