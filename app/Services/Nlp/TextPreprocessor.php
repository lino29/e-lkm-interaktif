<?php

namespace App\Services\Nlp;

class TextPreprocessor
{
    /**
     * @var array<string, string>
     */
    private array $normalizations = [
        'tdk' => 'tidak',
        'nggak' => 'tidak',
        'ga' => 'tidak',
        'gak' => 'tidak',
        'yg' => 'yang',
        'dgn' => 'dengan',
        'dlm' => 'dalam',
        'energi2' => 'energi',
    ];

    public function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? trim($text);

        $words = array_map(
            fn (string $word): string => $this->normalizations[$word] ?? $word,
            $text === '' ? [] : explode(' ', $text),
        );

        return implode(' ', $words);
    }

    /**
     * @return array<int, string>
     */
    public function tokens(string $text): array
    {
        $normalized = $this->normalize($text);

        if ($normalized === '') {
            return [];
        }

        return array_values(array_filter(explode(' ', $normalized)));
    }
}
