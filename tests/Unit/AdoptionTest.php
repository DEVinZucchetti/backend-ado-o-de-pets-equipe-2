<?php

namespace Tests\Feature;

use App\Models\Adoption;
use App\Models\People;
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

    public function test_cannot_create_with_invalid_name(): void
    {

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

        $response = $this->actingAs($user)->post('/api/pets/adocao', ['name' => 1]);

        $response->assertStatus(400);
        $response->assertJson([
            "message" => "The name field must be a string",
            "status" => 400,

        ]);
    }

    public function test_cat_get_all_adoption(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        Adoption::factory(5)->create(['pet_id' => $pet->id]);

        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);

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
                'pet_id' => true,
            ]
        ]);
    }
    public function test_user_can_add_realized_adoption(): void
    {
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        $adoption = Adoption::factory(5)->create(['pet_id' => $pet->id]);

        $user = User::factory()->create(['profile_id' => 3, 'password' => '12345678']);

        $this->assertDatabaseHas('adoptions', ['id' => $adoption->id, 'status' => 'PENDENTE']);

        $response = $this->actingAs($user)->post('/api/adoptions/realized', ['adoption_id' => $adoption->id]);

        $this->assertDatabaseHas('adoptions', ['id' => $adoption->id, 'status' => 'APROVADO']);

        $this->assertDatabaseHas('peoples', ['email' => $adoption->email, 'cpf' => $adoption->cpf]);
        $this->assertDatabaseCount('clients', 1);

        $people = People::query()->where(['cpf' => $adoption->cpf])->first();

        $this->assertDatabaseHas('clients', ['people_id' => $people->id]);

        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'client_id' => $people->client->id]);

        $this->assertDatabaseHas('solicitacions_documents', ['client_id' => $people->client->id]);

        $response->assertStatus(201);
        $response->assertJSON([
            'id' => true,
            'people_id' => true,
            'bonus' => true,
        ]);
    }
}
