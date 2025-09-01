<?php

namespace App\Utils;

class EmailHelper
{
    /**
     * Render an HTML template string with placeholders replaced.
     *
     * Example:
     *   EmailHelper::render("
     *     <p>Hello {name}, your order #{orderId} is ready.</p>",
     *     ['name' => 'John', 'orderId' => 123]
     *   );
     */
    public static function render(string $template, array $vars = []): string
    {
        $replacements = [];

        foreach ($vars as $key => $value) {
            $replacements['{' . $key . '}'] = e($value); // escape by default
        }

        return strtr($template, $replacements);
    }

    /**
     * If you need raw (unescaped) HTML in values:
     */
    public static function renderRaw(string $template, array $vars = []): string
    {
        $replacements = [];

        foreach ($vars as $key => $value) {
            $replacements['{' . $key . '}'] = $value; // no escaping
        }

        return strtr($template, $replacements);
    }
}
