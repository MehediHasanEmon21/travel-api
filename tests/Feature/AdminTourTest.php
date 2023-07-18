<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTourTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_saves_tour_successfully_with_valid_data(): void
    {   
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));

        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->postJson("/api/v1/admin/travels/{$travel->id}/tour",[
            'name' => 'Tour 123',
            'starting_date' => now(),
            'ending_date' => now()->addDays(2),
            'price' => 123.23
        ]);


        $response->assertStatus(201);

        $response = $this->getJson("/api/v1/travels/{$travel->slug}/tour");

        $response->assertJsonFragment(['name' => 'Tour 123']);
    }
}
