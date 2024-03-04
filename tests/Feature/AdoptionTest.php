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
    public function test_complete_flow_from_creation_to_approval(): void
    {
        // Primeiro, criamos as instâncias de espécie e raça necessárias para o teste
        $specie = Specie::factory()->create();
        $race = Race::factory()->create();

        // Criamos um pet e 20 outros pets com a mesma raça e espécie para simular um ambiente realista
        $pet  = Pet::factory()->create(['race_id' => $race->id, 'specie_id' => $specie->id]);
        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);

        // Tentamos criar uma adoção com dados inválidos para testar as validações
        $body = [
            'name' => '',
            'contact' => '',
            'email' => '',
            'cpf' => '',
            'observations' => '',
            'pet_id' => $pet->id,
        ];

        // Enviamos a requisição POST para a rota de adoção e verificamos se a validação está funcionando corretamente
        $response = $this->post('/api/pets/adocao', $body);
        $response->assertStatus(400)->assertJson([
            'message' => 'The name field must be a string. (and 9 more errors)',
            'status' => 400,
            'errors' => [],
            'data' => []
        ]);

        // Após a falha, enviamos dados válidos para criar uma adoção
        $body = [
            'name' => 'Drew',
            'contact' => '419999-9999',
            'email' => 'drew@drew.com',
            'cpf' => '08917989943',
            'observations' => 'Looking for a friendly pet',
            'pet_id' => $pet->id,
        ];

        // Verificamos se a adoção foi criada com sucesso e se os dados retornados estão corretos
        $response = $this->post('/api/pets/adocao', $body);
        $this->assertDatabaseCount('adoptions', 1);
        $response->assertStatus(201)->assertJson([
            ...$body,
            'status' => 'PENDENTE',
        ]);

        // Criamos mais 4 adoções para testar a listagem
        Adoption::factory(4)->create(['pet_id' => $pet->id]);

        // Autenticamos um usuário para testar a listagem de adoções
        $user = User::factory()->create(['profile_id' => 2, 'password' => '12345678']);
        $response = $this->actingAs($user)->get('/api/adoptions');
        $response->assertStatus(200)->assertJsonCount(5);

        // Testamos a funcionalidade de busca na listagem de adoções
        $response = $this->actingAs($user)->get('/api/adoptions?search=Mel');
        $response->assertStatus(200)->assertJsonCount(1);

        // Tentamos aprovar uma adoção inexistente para testar o tratamento de erros
        $response = $this->actingAs($user)->post('/api/adoptions/realized', ['adoption_id' => 999]);
        $response->assertStatus(404);

        // Aprovamos uma adoção existente e verificamos se o status foi atualizado corretamente
        $adoption = Adoption::query()->first();
        $response = $this->actingAs($user)->post('/api/adoptions/realized', ['adoption_id' => $adoption->id]);
        $this->assertDatabaseHas('adoptions', ['id' => $adoption->id, 'status' => 'APROVADO']);

        // Verificamos se as operações de criação de pessoa, cliente, e a vinculação do pet foram realizadas corretamente
        $this->assertDatabaseHas('peoples', ['email' => $adoption->email, 'cpf' => $adoption->cpf]);
        $people = People::query()->where(['cpf' => $adoption->cpf])->first();
        $this->assertDatabaseHas('clients', ['people_id' => $people->id]);
        $people->load('client');
        $this->assertDatabaseHas('pets', ['id' => $pet->id, 'client_id' => $people->client->id]);

        // Finalmente, verificamos se a adoção foi aprovada com sucesso e se os dados retornados estão corretos
        $response->assertStatus(201);
        $response->assertJson([
            'id' => true,
            'people_id' => true,
            'bonus' => true
        ]);
    }
}

