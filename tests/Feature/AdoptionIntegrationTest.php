<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\Pet;
use App\Models\Race;
use App\Models\Specie;
use App\Models\User;
use Tests\TestCase;

class AdoptionIntegrationTest extends TestCase
{
    public function test_adoption_integration(): void
    {
        //cadastro com dados inválidos
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $body = [
            'name' => '',
            'contact' => '',
            'email' => '',
            'cpf' => '',
            'observations' => '',
            'pet_id' => $pet->id,
        ];

        $response = $this->post('/api/pets/adocao', $body);

        $response->assertStatus(400)->assertJson([
            'message' => 'The name field must be a string. (and 9 more errors)',
            'status' => 400,
            'errors' => [],
            'data' => []
        ]);

        //cadastro com dados válidos
        $body = [
            'name' => 'Caroline',
            'contact' => '8599181-1333',
            'email' => 'testing@email.com',
            'cpf' => '999.999.999-99',
            'observations' => 'This is a test observations',
            'pet_id' => $pet->id,
        ];

        $response = $this->post('/api/pets/adocao', $body);

        $this->assertDatabaseCount('adoptions', 1);
        $response->assertStatus(201)->assertJson([
            ...$body,
            'status' => 'PENDENTE',
        ]);

        //listagem de todas as adoções
        Adoption::factory(4)->create(['pet_id' => $pet->id]);

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

        $response = $this->actingAs($user)->get('/api/adoptions');

        $response->assertStatus(200)->assertJsonCount(5)->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'contact',
                'email',
                'cpf',
                'observations',
                'pet_id',
                'status',
                'created_at',
                'updated_at',
            ]
        ]);

        //listagem de adoção com filtro
        $response = $this->actingAs($user)->get('/api/adoptions?search=Caroline');

        $response->assertStatus(200)->assertJsonCount(1)->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'contact',
                'email',
                'cpf',
                'observations',
                'pet_id',
                'status',
                'created_at',
                'updated_at',
            ]
        ]);
    }
}
