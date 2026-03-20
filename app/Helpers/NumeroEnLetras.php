<?php
namespace App\Helpers;

class NumeroEnLetras
{
    private static $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    private static $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    private static $centenas = ['', 'CIEN', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    public static function convertir(int $numero): string
    {
        if ($numero === 0) return 'CERO';
        if ($numero < 0) return 'MENOS ' . self::convertir(abs($numero));

        $resultado = '';

        if ($numero >= 1000000) {
            $millones = intdiv($numero, 1000000);
            $resultado .= ($millones === 1 ? 'UN MILLÓN' : self::convertir($millones) . ' MILLONES');
            $numero %= 1000000;
            if ($numero > 0) $resultado .= ' ';
        }

        if ($numero >= 1000) {
            $miles = intdiv($numero, 1000);
            $resultado .= ($miles === 1 ? 'MIL' : self::convertir($miles) . ' MIL');
            $numero %= 1000;
            if ($numero > 0) $resultado .= ' ';
        }

        if ($numero >= 100) {
            $c = intdiv($numero, 100);
            $resto = $numero % 100;
            if ($c === 1 && $resto > 0) {
                $resultado .= 'CIENTO';
            } else {
                $resultado .= self::$centenas[$c];
            }
            $numero %= 100;
            if ($numero > 0) $resultado .= ' ';
        }

        if ($numero >= 20) {
            $d = intdiv($numero, 10);
            $resultado .= self::$decenas[$d];
            $numero %= 10;
            if ($numero > 0) $resultado .= ' Y ' . self::$unidades[$numero];
        } elseif ($numero > 0) {
            $resultado .= self::$unidades[$numero];
        }

        return trim($resultado);
    }
}
