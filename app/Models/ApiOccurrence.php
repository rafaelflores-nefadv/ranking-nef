<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Get the seller by email
     */
    public function seller(): ?Seller
    {
        return Seller::where('email', $this->email_funcionario)->first();
    }
}
