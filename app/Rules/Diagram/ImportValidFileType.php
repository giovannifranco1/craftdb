<?php

namespace App\Rules\Diagram;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImportValidFileType implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowedExtensions = ['sql', 'zip', 'php'];
        $allowedMimes = [
            'application/zip',
            'application/x-zip-compressed',
            'application/sql',
            'application/x-sql',
            'text/x-php',
            'application/x-httpd-php',
        ];

        $ext = strtolower($value->getClientOriginalExtension());
        $mime = $value->getMimeType();

        if (!in_array($ext, $allowedExtensions)) {
            $fail("A extensão .$ext não é permitida. Use apenas .sql, .zip ou .php.");
            return;
        }

        if (!in_array($mime, $allowedMimes)) {
            $fail("O tipo MIME '$mime' é inválido para este tipo de arquivo.");
        }
    }
}
