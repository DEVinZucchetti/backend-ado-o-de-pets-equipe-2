<?php

namespace Tests\Unit;

use App\Models\Adoption;
use App\Models\Pet;
use App\Models\Race;
use App\Models\Specie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdoptionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_add_new_adoption(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $body =
        [
            'name' => 'Drew',
            'contact' => '41 999489761',
            'cpf' => '08917989948',
            'email' => 'drewvieirasocial3@gmail.com',
            'observations' => 'Quero um Dog',
            'pet_id' => $pet->id
        ];

        $response = $this->post('/api/pets/adocao', $body);

        $this->assertDatabaseCount('adoptions', 1);

        $response->assertStatus(201);
        $response->assertJson([
            ...$body,
            'status' => 'PENDENTE'
        ]);
    }

    public function test_user_cannot_add_new_adoptions_without_name(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $body = [
            'name' => '',
            'contact' => '41 999489761',
            'cpf' => '08917989948',
            'email' => 'drewvieirasocial3@gmail.com',
            'observations' => 'Quero um Dog',
            'pet_id' => $pet->id
        ];

        $response = $this->post('/api/pets/adocao', $body);

        $response->assertStatus(400)->assertJson([
            'message' => 'The name field must be a string. (and 1 more error)',
            'status' => 400,
            'errors' => [],
            'data' => []
        ]);
    }

    public function test_can_get_all_adoptions(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Adoption::factory(10)->create(['pet_id' => $pet->id]);

        $user = User::factory()->create(['profile_id' => 3, 'password' => '12345678']);

        $response = $this->actingAs($user)->get('/api/adoptions');
        $response->assertStatus(200);

        $response->assertJsonStructure([
            '*' => [
                'id' => true,
                'name' => true,
                'email' => true,
                'cpf' => true,
                'contact' => true,
                'observations' => true,
                'status' => true,
                'pet_id' => true
            ]
        ]);
    }
}
