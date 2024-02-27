<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\People;
use App\Models\Race;
use App\Models\Pet;
use App\Models\Specie;
use App\Models\User;
use Tests\TestCase;

class AdoptionTest extends TestCase
{

    public function test_user_can_add_new_adoptions(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

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
    }

    public function test_user_cannot_add_new_adoptions_without_name(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $body = [
            'name' => '',
            'contact' => '8599181-1333',
            'email' => 'testing@email.com',
            'cpf' => '999.999.999-99',
            'observations' => 'This is a test observations',
            'pet_id' => $pet->id,
        ];

        $response = $this->post('/api/pets/adocao', $body);

        $response->assertStatus(400)->assertJson([
            'message' => 'The name field must be a string. (and 1 more error)',
            'status' => 400,
            'errors' => [],
            'data' => []
        ]);
    }

    public function test_user_cannot_add_new_adoptions_without_contact(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $body = [
            'name' => 'Caroline',
            'contact' => '',
            'email' => 'testing@email.com',
            'cpf' => '999.999.999-99',
            'observations' => 'This is a test observations',
            'pet_id' => $pet->id,
        ];

        $response = $this->post('/api/pets/adocao', $body);

        $response->assertStatus(400)->assertJson([
            'message' => 'The contact field must be a string. (and 1 more error)',
            'status' => 400,
            'errors' => [],
            'data' => []
        ]);
    }

    public function test_user_cannot_add_new_adoptions_without_email(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $body = [
            'name' => 'Caroline',
            'contact' => '8599181-1333',
            'email' => '',
            'cpf' => '999.999.999-99',
            'observations' => 'This is a test observations',
            'pet_id' => $pet->id,
        ];

        $response = $this->post('/api/pets/adocao', $body);

        $response->assertStatus(400)->assertJson([
            'message' => 'The email field must be a string. (and 1 more error)',
            'status' => 400,
            'errors' => [],
            'data' => []
        ]);
    }

    public function test_user_cannot_add_new_adoptions_without_cpf(): void
    {
        $specie = Specie::factory()->create();
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $body = [
            'name' => 'Caroline',
            'contact' => '8599181-1333',
            'email' => 'testing@email.com',
            'cpf' => '',
            'observations' => 'This is a test observations',
            'pet_id' => $pet->id,
        ];

        $response = $this->post('/api/pets/adocao', $body);

        $response->assertStatus(400)->assertJson([
            'message' => 'The cpf field must be a string. (and 1 more error)',
            'status' => 400,
            'errors' => [],
            'data' => []
        ]);
    }

    public function test_user_cannot_add_new_adoptions_without_observations(): void
    {
        $specie = Specie::factory()->create();
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $body = [
            'name' => 'Caroline',
            'contact' => '8599181-1333',
            'email' => 'testing@email.com',
            'cpf' => '999.999.999-99',
            'observations' => '',
            'pet_id' => $pet->id,
        ];

        $response = $this->post('/api/pets/adocao', $body);

        $response->assertStatus(400)->assertJson([
            'message' => 'The observations field must be a string. (and 1 more error)',
            'status' => 400,
            'errors' => [],
            'data' => []
        ]);
    }

    public function test_user_can_get_all_adoptions(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Adoption::factory(10)->create(['pet_id' => $pet->id]);

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

        $response = $this->actingAs($user)->get('/api/adoptions');

        $response->assertStatus(200)->assertJsonCount(10)->assertJsonStructure([
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

    public function test_user_can_get_adoptions_with_search(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Adoption::factory()->create(['name' => 'Caroline', 'pet_id' => $pet->id]);
        Adoption::factory(5)->create(['pet_id' => $pet->id]);

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

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

    public function test_user_can_realized_adoption(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $adoption = Adoption::factory()->create(['pet_id' => $pet->id]);
        $user = User::factory()->create(['profile_id' => 3, 'password' => '12345678']);

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

    public function test_user_cannot_realized_adoption_with_invalid_adoption_id(): void
    {
        $user = User::factory()->create(['profile_id' => 3, 'password' => '12345678']);

        $response = $this->actingAs($user)->post('/api/adoptions/realized', ['adoption_id' => 999]);

        $response->assertStatus(404)->assertJson([
            'message' => 'Dado não encontrado',
            'status' => 404,
            'errors' => [],
            'data' => []
        ]);
    }

    public function test_user_cannot_realized_adoption_without_adoption_id(): void
    {
        $user = User::factory()->create(['profile_id' => 3, 'password' => '12345678']);

        $response = $this->actingAs($user)->post('/api/adoptions/realized', []);

        $response->assertStatus(400)->assertJson([
            'message' => 'The adoption id field is required.',
            'status' => 400,
            'errors' => [],
            'data' => []
        ]);
    }
}
