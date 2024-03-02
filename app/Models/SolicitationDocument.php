<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitationDocument extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'solicitations_documents';

    protected $fillable = ['client_id', 'cpf', 'rg', 'document_address', 'term_adoption',];
}
