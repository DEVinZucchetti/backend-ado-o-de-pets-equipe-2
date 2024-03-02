<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\People;
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

        //aprovação de adoção com dados inválidos
        $response = $this->actingAs($user)->post('/api/adoptions/realized', ['adoption_id' => 999]);

        $response->assertStatus(404)->assertJson([
            'message' => 'Dado não encontrado',
            'status' => 404,
            'errors' => [],
            'data' => []
        ]);

        //aprovação de adoção
        $adoption = Adoption::query()->first();

        $this->assertDatabaseHas('adoptions', ['id' => $adoption->id, 'status' => 'PENDENTE']);
        $response = $this->actingAs($user)->post('/api/adoptions/realized', ['adoption_id' => $adoption->id]);

        /* Verifica a mudança de status */
        $this->assertDatabaseHas('adoptions', ['id' => $adoption->id, 'status' => 'APROVADO']);

        /* Verifica a criação da pessoa e do cliente */
        $this->assertDatabaseHas('peoples', ['email' => $adoption->email, 'cpf' => $adoption->cpf]);
        $people = People::query()->where(['cpf' => $adoption->cpf])->first();
        $this->assertDatabaseHas('clients', ['people_id' => $people->id]);

        /* Verifica se pet recebeu o id do cliente */
        $people->load('client');
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'client_id' => $people->client->id]);

        /* Verifica se criou a solicitação com o id do cliente vinculado */
        $this->assertDatabaseHas('solicitations_documents', ['client_id' =>  $people->client->id]);

        $response->assertStatus(201);
        $response->assertJson([
            'id' => true,
            'people_id' => true,
            'bonus' => true
        ]);
    }
}
