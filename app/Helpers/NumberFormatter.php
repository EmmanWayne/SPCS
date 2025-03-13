<?php

namespace App\Helpers;

class NumberFormatter
{
    /**
     * Formatea un número a formato de Lempiras
     *
     * @param float|int|string $amount
     * @return string
     */
    public static function formatLempiras($amount)
    {
        return 'L ' . number_format((float) $amount, 2, '.', ',');
    }

    /**
     * Convierte un string con formato de Lempiras a número
     *
     * @param string $amount
     * @return float
     */
    public static function unformatLempiras($amount)
    {
        return (float) str_replace(['L', ' ', ','], '', $amount);
    }

    /**
     * Convierte números a palabras en Lempiras
     *
     * @param float|int|string $amount
     * @return string
     */
    public static function toWords($amount)
    {
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        $integers = floor($amount);
        $decimals = round(($amount - $integers) * 100);
        
        return ucfirst($formatter->format($integers)) . ' Lempiras' . 
               ($decimals > 0 ? ' con ' . $formatter->format($decimals) . ' centavos' : '');
    }
}
