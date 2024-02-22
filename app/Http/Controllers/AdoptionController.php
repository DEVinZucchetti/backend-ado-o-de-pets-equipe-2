<?php

namespace App\Http\Controllers;

use App\Mail\SendDocuments;
use App\Models\Adoption;
use App\Models\Client;
use App\Models\File;
use App\Models\People;
use App\Models\Pet;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AdoptionController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {
        try {
            $filters = $request->query();
            $pets = Pet::query()
                ->select(
                    'id',
                    'pets.name as pet_name',
                    'pets.breed_id',
                    'pets.age as age'
                )
                ->with(['breed' => function ($query) {
                    $query->select('name', 'id');
                }])
                ->where('client_id', null);

            // Verifica se há uma string de pesquisa
            if ($request->has('search') && !empty($request->input('search'))) {
                $searchQuery = '%' . $request->input('search') . '%';

                $pets->where(function ($query) use ($searchQuery) {
                    $query->where('name', 'ilike', $searchQuery)
                        ->orWhere('age', 'ilike', $searchQuery)
                        ->orWhere('weight', 'ilike', $searchQuery)
                        ->orWhereHas('breed', function ($query) use ($searchQuery) {
                            $query->where('name', 'ilike', $searchQuery);
                        });
                });
            }
            return $pets->orderBy('created_at', 'desc')->get();
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function show($id)
    {
        $pet = Pet::find($id)->load('breed', 'specie');

        if ($pet->client_id) return $this->error('Dados confidenciais', Response::HTTP_FORBIDDEN);
        if (!$pet) return $this->error('Dado não encontrado!', Response::HTTP_NOT_FOUND);

        return $pet;
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            $request->validate([
                'name' => 'string|required|max:255',
                'contact' => 'string|required|max:20',
                'email' => 'string|required',
                'cpf' => 'string|required',
                'observations' => 'string|required',
                'pet_id' => 'integer|required',
            ]);

            $adoption = Adoption::create([...$data, 'status' => 'PENDENTE']);
            return $adoption;
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function getAdoptions(Request $request)
    {
        $search = $request->input('search');

        $adoptions = Adoption::query()
            ->with('pet')
            ->where('name', 'ilike', "%$search%")
            ->orWhere('email', 'ilike', "%$search%")
            ->orWhere('contact', 'ilike', "%$search%")
            ->orWhere('status', 'ilike', "%$search%");

        return $adoptions->get();
    }

    public function approve(Request $request)
    {
        // Atualiza o status da adoção para aprovado
        $data = $request->all();

        $request->validate([
            'adoption_id' => 'integer|required',
        ]);

        $adoption = Adoption::find($data['adoption_id']);

        if (!$adoption)  return $this->error('Dado não encontrado', Response::HTTP_NOT_FOUND);

        $adoption->update(['status' => 'APROVADO']);
        $adoption->save();

        // efetivo o cadastro da pessoa que tem intenção de adotar no sistema
        $people = People::create([
            'name' => $adoption->name,
            'email' => $adoption->email,
            'cpf' => $adoption->cpf,
            'contact' => $adoption->contact,
        ]);

        $client = Client::create([
            'people_id' => $people->id,
            'bonus' => true
        ]);

        // vincula o pet com cliente criado
        $pet = Pet::find($adoption->pet_id);
        $pet->update(['client_id' => $client->id]);
        $pet->save();

        Mail::to($people->email, $people->name)
            ->send(new SendDocuments($people->name));

        return $client;
    }

    public function upload(Request $request)
    {
        $file = $request->file('file');
        $description =  $request->input('description');

        $slugName = Str::of($description)->slug();
        $fileName = $slugName . '.' . $file->extension();

        $pathBucket = Storage::disk('s3')->put('documentos', $file);

        $fullPathFile = Storage::disk('s3')->url($fileName);

        File::create(
            [
                'name' => $fileName,
                'size' => $file->getSize(),
                'mime' => $file->extension(),
                'url' => $fullPathFile
            ]
        );

        return ['message' => 'Arquivo criado com sucesso'];
    }
}
