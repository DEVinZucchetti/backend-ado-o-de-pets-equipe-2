<?php

namespace Tests\Feature;

use App\Models\Adoption;
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
}
