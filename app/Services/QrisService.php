<?php

namespace App\Services;

class QrisService
{
    /**
     * Generate dynamic QRIS string with specific amount by calculating CRC16 verification.
     * Based on EMVCo standards.
     * 
     * @param float $amount
     * @return string
     * @throws \Exception
     */
    public function generateDynamicQris(float $amount): string
    {
        $payload = config('services.qris.static_payload');

        if (!$payload) {
            throw new \Exception('Static QRIS payload is not configured.');
        }

        // 1. Remove the old CRC (Tag 63) which is the last 8 characters (6304 + 4 hex chars)
        $payloadWithoutCrc = substr($payload, 0, -8);

        // 2. Parse TLV (Tag-Length-Value)
        $tags = [];
        $index = 0;
        while ($index < strlen($payloadWithoutCrc)) {
            $tagId = substr($payloadWithoutCrc, $index, 2);
            $tagLen = (int) substr($payloadWithoutCrc, $index + 2, 2);
            $tagVal = substr($payloadWithoutCrc, $index + 4, $tagLen);
            $tags[$tagId] = $tagVal;
            $index += 4 + $tagLen;
        }

        // 3. Change Point of Initiation Method (Tag 01) from Static (11) to Dynamic (12)
        if (isset($tags['01']) && $tags['01'] === '11') {
            $tags['01'] = '12';
        }

        // 4. Format the amount (Integer string for IDR)
        $amountStr = (string) intval(round($amount));
        $tags['54'] = $amountStr;

        // 5. Reconstruct EMVCo string in numerical order
        ksort($tags);
        $reconstructedPayload = "";
        foreach ($tags as $id => $val) {
            $len = str_pad((string) strlen($val), 2, '0', STR_PAD_LEFT);
            $reconstructedPayload .= $id . $len . $val;
        }

        // 6. Append placeholder for the new CRC
        $payloadToHash = $reconstructedPayload . "6304";

        // 7. Calculate CRC16 CCITT
        $crc = self::calculateCrc16($payloadToHash);

        // 8. Return the final string
        return $payloadToHash . $crc;
    }

    /**
     * Calculate CRC16-CCITT for QRIS (Polynomial 0x1021, Initial Value 0xFFFF)
     * 
     * @param string $str
     * @return string
     */
    private static function calculateCrc16(string $str): string
    {
        $crc = 0xFFFF;
        $polynomial = 0x1021;
        
        for ($i = 0; $i < strlen($str); $i++) {
            $b = ord($str[$i]);
            for ($j = 0; $j < 8; $j++) {
                $bit = (($b >> (7 - $j) & 1) == 1);
                $c15 = (($crc >> 15 & 1) == 1);
                $crc <<= 1;
                if ($c15 ^ $bit) {
                    $crc ^= $polynomial;
                }
            }
        }
        
        $crc &= 0xFFFF;
        
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
