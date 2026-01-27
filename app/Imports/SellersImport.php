<?php

namespace App\Imports;

use App\Models\Seller;
use App\Models\Season;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class SellersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    protected $activeSeason;
    protected $imported = 0;
    protected $skipped = 0;
    protected $errors = [];
    protected ?string $sectorId = null;

    public function __construct(?string $sectorId = null)
    {
        $this->sectorId = $sectorId;
        // Buscar temporada ativa
        $this->activeSeason = Season::where('is_active', true)->first();
        
        if (!$this->activeSeason) {
            throw new \Exception('Nenhuma temporada ativa encontrada. Por favor, ative uma temporada antes de importar vendedores.');
        }
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Laravel Excel normaliza cabeçalhos: lowercase e substitui espaços/hífens por underscore
        // "nome" -> "nome", "e-mail" -> "e_mail"
        // Buscar nome (aceita: nome, name, n)
        $name = $this->getValue($row, ['nome', 'name', 'n']);
        
        // Buscar email (aceita: e-mail, e_mail, email, e)
        $email = $this->getValue($row, ['e-mail', 'e_mail', 'email', 'e']);

        // Validar se os campos obrigatórios estão presentes
        if (empty($name) || empty($email)) {
            $this->skipped++;
            return null;
        }

        // Normalizar email
        $email = trim(strtolower($email));

        // Validar formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->skipped++;
            $this->errors[] = "Email inválido: {$email}";
            return null;
        }

        // Verificar se o email já existe
        if (Seller::where('email', $email)->where('sector_id', $this->sectorId)->exists()) {
            $this->skipped++;
            $this->errors[] = "Email {$email} já existe no sistema";
            return null;
        }

        // Criar vendedor
        $seller = new Seller([
            'sector_id' => $this->sectorId,
            'name' => trim($name),
            'email' => $email,
            'season_id' => $this->activeSeason->id,
            'team_id' => null, // Sem equipe inicialmente
            'status' => 'active', // Status ativo
            'points' => 0,
        ]);

        $this->imported++;
        return $seller;
    }

    /**
     * Get validation rules
     * Laravel Excel normaliza: "nome" -> "nome", "e-mail" -> "e_mail"
     */
    public function rules(): array
    {
        return [
            'nome' => 'nullable|string',
            'name' => 'nullable|string',
            'n' => 'nullable|string',
            'e-mail' => 'nullable|email',
            'e_mail' => 'nullable|email',
            'email' => 'nullable|email',
            'e' => 'nullable|email',
        ];
    }

    /**
     * Get value from row by trying multiple possible keys
     * Laravel Excel normaliza cabeçalhos: lowercase e substitui espaços/hífens por underscore
     * Exemplo: "Nome" -> "nome", "E-mail" -> "e_mail"
     */
    protected function getValue(array $row, array $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            // Normalizar a chave (lowercase e substituir espaços/hífens por underscore)
            $normalizedKey = str_replace([' ', '-'], '_', strtolower($key));
            
            // Tentar com diferentes variações
            $variations = [
                $normalizedKey,           // Normalizado (lowercase + underscore) - mais comum
                strtolower($key),         // Lowercase original
                $key,                     // Original
                str_replace('_', '-', $normalizedKey), // Com hífen (e-mail)
                str_replace('_', ' ', $normalizedKey), // Com espaço
            ];

            // Buscar no array (case-insensitive)
            foreach ($variations as $variation) {
                foreach ($row as $rowKey => $value) {
                    // Comparação case-insensitive
                    if (strtolower(trim($rowKey)) === strtolower(trim($variation))) {
                        return $value;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get imported count
     */
    public function getImportedCount(): int
    {
        return $this->imported;
    }

    /**
     * Get skipped count
     */
    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
