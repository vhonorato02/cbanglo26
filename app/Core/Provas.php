<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Calendario oficial das provas por unidade.
 * Mantem a regra em um unico ponto: Anglo Pinda tem duas datas; as demais,
 * somente 17 de outubro.
 */
final class Provas
{
    /** @var array<int, string> */
    private static $meses = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro',
    ];

    /** @return array<int, array{data: string, hora: string, label: string}> */
    public static function opcoesParaNome($nome)
    {
        $nomeNormalizado = self::normalizar((string) $nome);
        $opcoes = [];

        if (strpos($nomeNormalizado, 'pinda') !== false) {
            $opcoes[] = self::opcao('2026-09-26');
        }

        $opcoes[] = self::opcao('2026-10-17');
        return $opcoes;
    }

    /** @param array<int, array<string, mixed>> $escolas */
    public static function opcoesPorEscolas(array $escolas)
    {
        $mapa = [];
        foreach ($escolas as $escola) {
            $id = (int) ($escola['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $mapa[(string) $id] = [
                'escola' => (string) ($escola['nome'] ?? ''),
                'datas' => self::opcoesParaNome($escola['nome'] ?? ''),
            ];
        }
        return $mapa;
    }

    /** @param array<string, mixed> $escola */
    public static function dataPermitida($data, array $escola)
    {
        foreach (self::opcoesParaNome($escola['nome'] ?? '') as $opcao) {
            if ($opcao['data'] === $data) {
                return true;
            }
        }
        return false;
    }

    public static function rotulo($data, $hora = '09:00')
    {
        $ts = strtotime((string) $data);
        if ($ts === false) {
            return (string) $data;
        }

        $dia = (int) date('j', $ts);
        $mes = self::$meses[(int) date('n', $ts)];
        return $dia . ' de ' . $mes . ', às ' . self::horaCurta($hora);
    }

    public static function rotuloSelecionada($data)
    {
        return self::rotulo($data, '09:00');
    }

    /** @return array<int, array{unidade: string, datas: string, hora: string}> */
    public static function resumoCampanha()
    {
        return [
            ['unidade' => 'Anglo Pinda', 'datas' => '26 de setembro ou 17 de outubro', 'hora' => '9h'],
            ['unidade' => 'Fênix, Drummond e Anglo Cruzeiro', 'datas' => '17 de outubro', 'hora' => '9h'],
        ];
    }

    /** @return array{data: string, hora: string, label: string} */
    private static function opcao($data)
    {
        return [
            'data' => $data,
            'hora' => '09:00',
            'label' => self::rotulo($data, '09:00'),
        ];
    }

    private static function horaCurta($hora)
    {
        $hora = (string) $hora;
        if (!preg_match('/^(\d{1,2}):(\d{2})/', $hora, $m)) {
            return $hora;
        }
        $h = (int) $m[1];
        return $m[2] === '00' ? $h . 'h' : $h . 'h' . $m[2];
    }

    private static function normalizar($texto)
    {
        $texto = mb_strtolower((string) $texto, 'UTF-8');
        $semAcento = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        return $semAcento !== false ? $semAcento : $texto;
    }
}
