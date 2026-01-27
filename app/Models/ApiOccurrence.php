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
        'sector_id',
        'api_token_id',
        'collaborator_identifier_type',
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

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function apiToken(): BelongsTo
    {
        return $this->belongsTo(ApiToken::class);
    }

    /**
     * Get the seller by email
     */
    public function seller(): ?Seller
    {
        $query = Seller::query()->where('sector_id', $this->sector_id);

        if ($this->collaborator_identifier_type === 'external_code') {
            return $query->where('external_code', $this->email_funcionario)->first();
        }

        return $query->where('email', $this->email_funcionario)->first();
    }
}
