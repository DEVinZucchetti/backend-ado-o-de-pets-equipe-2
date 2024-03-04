<?php

namespace Tests\Feature;

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
    public function test_can_add_new_adoption(){
        $specie= Specie::factory()->create();
        $race = Race::factory()->create();
        $pet = Pet::factory()->create(['race_id'=> $race->id,'specie_id'=>$specie->id]);

        Pet::factory(20)->create(['race_id'=> $race->id,'specie_id'=>$specie->id]);
        $body = [
            'name'=> 'Julio',
            'contact' => '51989989898',
            'cpf' => '022022202222',
            'email'=>'j@gmail.com',
            'observations' =>'Gosto de pet',
            'pet_id'=> $pet->id
        ];

        $response = $this->post('/api/pets/adocao',$body);

        Pet::factory(20)->create(['race_id' => $race->id, 'specie_id' => $specie->id]);


        $response->assertStatus(201);
        $response->assertJson([
           ...$body,
           'status' => 'PENDENTE'
        ]);
    }
    public function test_user_cant_add_new_specie_with_invalid_data(){

        $response = $this->post('/api/pets/adocao',['name'=>1]);
        $response->assertStatus(400);
        $response->assertJson([
           'message'=> 'The name field must be a string. (and 5 more errors)',
           'status'=> 400,
           'errors'=>[],
           'data'=>[]
        ]);
    }
}
