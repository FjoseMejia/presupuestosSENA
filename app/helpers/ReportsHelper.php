<?php

namespace presupuestos\helpers;

class ReportsHelper
{
    /**
     * Limpia el texto de una dependencia eliminando prefijos del centro.
     */
    public static function extraerDependencia(string $texto): string
    {
        if (empty($texto)) return '';

        $textoOriginal = $texto;

        // Lista de nombres padre a eliminar
        $nombresPadre = [
            'CENTRO AGROPECUARIO-CAUCA',
            'CENTRO AGROPECUARIO, CAUCA',
            'CENTRO AGROPECUARIO CAUCA',
            'CENTRO AGORPECUARIO-CAUCA',
            'CENTRO AGROPECUARIO'
        ];

        foreach ($nombresPadre as $padre) {
            // Eliminar al inicio
            $patronInicio = '/^' . preg_quote($padre, '/') . '\s*[-,\s]*/i';
            $texto = preg_replace($patronInicio, '', $texto);

            // Eliminar en medio o final (opcional)
            $patronMedio = '/\s*[-,\s]*' . preg_quote($padre, '/') . '\s*$/i';
            $texto = preg_replace($patronMedio, '', $texto);
        }

        $texto = trim($texto);
        $texto = preg_replace('/^[-\s,]+/', '', $texto);

        return empty(trim($texto)) ? $textoOriginal : trim($texto);
    }

    /**
     * Extrae solo el texto antes de los dos puntos.
     */
    public static function extraerConcepto(string $texto): string
    {
        if (empty($texto)) return '';
        $partes = explode(':', $texto, 2);
        return trim($partes[0]);
    }

    /**
     * Extrae el texto hasta el segundo guión.
     */
    public static function extraerServicio(string $texto): string
    {
        if (empty($texto)) return '';
        $partes = explode('-', $texto);

        if (count($partes) >= 3) {
            return trim($partes[0]) . ' - ' . trim($partes[1]);
        } elseif (count($partes) == 2) {
            return trim($partes[0]);
        }

        return trim($texto);
    }
}
