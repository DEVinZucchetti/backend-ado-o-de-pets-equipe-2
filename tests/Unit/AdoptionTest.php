<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\Pet;
use App\Models\Race;
use App\Models\Specie;

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

        Pet::factory(10)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $body = [
            'name' => 'Gabriel',
            'contact' => '51 98057-9171',
            'email' => 'gabriel@gmail.com',
            'cpf' => '02489256017',
            'observations' => 'Quero um cachorro grande',
            'pet_id' => $pet->id
        ];

        $response = $this->post('/api/pets/adocao', $body);



        $response->assertStatus(201);
        $response->assertJson([
            ...$body,
            'status' => 'PENDENTE'
        ]);
    }
}
