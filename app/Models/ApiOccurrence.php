<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiOccurrence extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'email_funcionario',
        'ocorrencia',
        'credor',
        'equipe',
        'processed',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'processed' => 'boolean',
        ];
    }
}
