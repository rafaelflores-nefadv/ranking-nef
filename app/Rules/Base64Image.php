<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64Image implements Rule
{
    /**
     * @param array<int, string> $allowedMimes
     */
    public function __construct(
        private readonly array $allowedMimes,
        private readonly int $maxKb
    ) {}

    public function passes($attribute, $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        if (!preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $value, $matches)) {
            return false;
        }

        $mime = 'image/' . strtolower($matches[1]);
        if (!in_array($mime, $this->allowedMimes, true)) {
            return false;
        }

        $base64 = substr($value, strpos($value, ',') + 1);
        $binary = base64_decode($base64, true);
        if ($binary === false) {
            return false;
        }

        $sizeKb = (int) ceil(strlen($binary) / 1024);
        return $sizeKb <= $this->maxKb;
    }

    public function message(): string
    {
        return 'A imagem enviada pela webcam é inválida ou excede o tamanho máximo permitido.';
    }
}
