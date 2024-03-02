<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class People extends Model
{
    use HasFactory;

    protected $table = "peoples";

    protected $fillable = ['name', 'cpf', 'email', 'contact'];

    public function client()
    {
        return $this->hasOne(Client::class, 'people_id', 'id');
    }
}
