<?php

namespace App\Services;

class PixService
{
    public static function gerarCopiaECola(string $chavePix, string $nomeBeneficiario, string $cidade, string $valor, string $txid = '***')
    {
        // Força o TxId como *** para evitar bloqueios em bancos rigorosos (Itaú, Nubank, Bradesco)
        $txid = '***'; 

        // Limpa a chave caso alguém digite CPF com pontos ou Celular com traços futuramente
        $chavePix = trim($chavePix);
        $chavePix = str_replace([' ', '-', '.', '(', ')'], '', $chavePix);
        
        // Converte tudo para Maiúsculo e remove acentos (Exigência do Banco Central EMV)
        $nomeBeneficiario = strtoupper(substr(self::removerAcentos($nomeBeneficiario), 0, 25));
        $cidade = strtoupper(substr(self::removerAcentos($cidade), 0, 15));
        
        // Garante que o valor venha como um decimal puro (ex: 108.00)
        $valorFloat = (float) str_replace(',', '.', $valor);
        $valorStr = number_format($valorFloat, 2, '.', '');

        $payload = self::getValue('00', '01');
        $payload .= self::getMerchantAccountInformation($chavePix);
        $payload .= self::getValue('52', '0000');
        $payload .= self::getValue('53', '986');
        
        if ($valorFloat > 0) {
            $payload .= self::getValue('54', $valorStr);
        }
        
        $payload .= self::getValue('58', 'BR');
        $payload .= self::getValue('59', $nomeBeneficiario);
        $payload .= self::getValue('60', $cidade);
        $payload .= self::getAdditionalDataFieldTemplate($txid);

        $payload .= '6304';
        $payload .= self::calcularCRC16($payload);

        return $payload;
    }

    private static function getValue(string $id, string $value): string
    {
        $size = str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $size . $value;
    }

    private static function getMerchantAccountInformation(string $chavePix): string
    {
        $gui = self::getValue('00', 'br.gov.bcb.pix');
        $key = self::getValue('01', $chavePix);
        return self::getValue('26', $gui . $key);
    }

    private static function getAdditionalDataFieldTemplate(string $txid): string
    {
        $txidValue = self::getValue('05', $txid);
        return self::getValue('62', $txidValue);
    }

    private static function calcularCRC16(string $payload): string
    {
        // CÁLCULO MATEMÁTICO CORRIGIDO (PADRÃO CCITT-FALSE OFICIAL DO BANCO CENTRAL)
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) {
                        $resultado ^= $polinomio;
                    }
                    $resultado &= 0xFFFF;
                }
            }
        }
        return strtoupper(str_pad(dechex($resultado), 4, '0', STR_PAD_LEFT));
    }

    private static function removerAcentos(string $string): string
    {
        $string = preg_replace(
            ['/(á|à|ã|â|ä)/', '/(Á|À|Ã|Â|Ä)/', '/(é|è|ê|ë)/', '/(É|È|Ê|Ë)/', '/(í|ì|î|ï)/', '/(Í|Ì|Î|Ï)/', '/(ó|ò|õ|ô|ö)/', '/(Ó|Ò|Õ|Ô|Ö)/', '/(ú|ù|û|ü)/', '/(Ú|Ù|Û|Ü)/', '/(ñ)/', '/(Ñ)/', '/(ç)/', '/(Ç)/'],
            ['a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U', 'n', 'N', 'c', 'C'],
            $string
        );
        // Garante que não vá nenhum caractere especial que quebre o banco
        return preg_replace('/[^A-Za-z0-9 ]/', '', $string);
    }
}