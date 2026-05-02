<?php

namespace WPStaging\Pro\Backup\Storage\OneDrive;

use function WPStaging\functions\debug_log;

/**
 * @see https://learn.microsoft.com/en-us/onedrive/developer/code-snippets/quickxorhash?view=odsp-graph-online#sample-code-c-sharp original code posted in c#
 */
class QuickXorHash
{
    /** @var int */
    const BITS_IN_LAST_CELL = 32;

    /** @var int */
    const SHIFT = 11;

    /** @var int */
    const WIDTH_IN_BITS = 160;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var int
     */
    private $lengthSoFar;

    /**
     * @var int
     */
    private $shiftSoFar;

    public function __construct()
    {
        $this->initialize();
    }

    public function initialize()
    {
        $this->data = array_fill(0, intval((self::WIDTH_IN_BITS - 1) / 64) + 1, 0);
        $this->shiftSoFar = 0;
        $this->lengthSoFar = 0;
    }

    public function hashCore(string $array, int $ibStart, int $cbSize)
    {
        $currentShift = $this->shiftSoFar;

        // The bit vector where we'll start xoring
        $vectorArrayIndex = intdiv($currentShift, 64);

        // The position within the bit vector at which we begin xoring
        $vectorOffset = $currentShift % 64;
        $iterations = min($cbSize, self::WIDTH_IN_BITS);

        for ($i = 0; $i < $iterations; $i++) {
            $isLastCell = ($vectorArrayIndex === count($this->data) - 1);
            $bitsInVectorCell = $isLastCell ? self::BITS_IN_LAST_CELL : 64;

            if ($vectorOffset <= $bitsInVectorCell - 8) {
                for ($j = $ibStart + $i; $j < $cbSize + $ibStart; $j += self::WIDTH_IN_BITS) {
                    $this->data[$vectorArrayIndex] ^= (ord($array[$j]) << $vectorOffset);
                }
            } else {
                $index1 = $vectorArrayIndex;
                $index2 = $isLastCell ? 0 : ($vectorArrayIndex + 1);
                $low = $bitsInVectorCell - $vectorOffset;

                $xoredByte = 0;
                for ($j = $ibStart + $i; $j < $cbSize + $ibStart; $j += self::WIDTH_IN_BITS) {
                    $xoredByte ^= ord($array[$j]);
                }

                $this->data[$index1] ^= ($xoredByte << $vectorOffset);
                $this->data[$index2] ^= ($xoredByte >> $low);
            }

            $vectorOffset += self::SHIFT;
            while ($vectorOffset >= $bitsInVectorCell) {
                $vectorArrayIndex = $isLastCell ? 0 : $vectorArrayIndex + 1;
                $vectorOffset -= $bitsInVectorCell;
            }
        }

        // Update the starting position in a circular shift pattern
        $this->shiftSoFar = ($this->shiftSoFar + self::SHIFT * ($cbSize % self::WIDTH_IN_BITS)) % self::WIDTH_IN_BITS;

        $this->lengthSoFar += $cbSize;
    }

    public function hashFinal(): string
    {
        $rgb = array_fill(0, intdiv(self::WIDTH_IN_BITS - 1, 8) + 1, 0);

        // Block copy all bit vectors to this byte array
        for ($i = 0; $i < count($this->data) - 1; $i++) {
            $bytes = pack("P", $this->data[$i]); // Pack unsigned 64-bit integer
            for ($j = 0; $j < 8; $j++) {
                $rgb[$i * 8 + $j] = ord($bytes[$j]);
            }
        }

        $lastCellBytes = pack("P", $this->data[count($this->data) - 1]);
        $remainingBytes = count($rgb) - (count($this->data) - 1) * 8;

        for ($j = 0; $j < $remainingBytes; $j++) {
            $rgb[(count($this->data) - 1) * 8 + $j] = ord($lastCellBytes[$j]);
        }

        // XOR the file length with the least significant bits
        $lengthBytes = pack("P", $this->lengthSoFar); // 64-bit little-endian
        $lengthOffset = self::WIDTH_IN_BITS / 8 - strlen($lengthBytes);

        for ($i = 0; $i < strlen($lengthBytes); $i++) {
            $rgb[$lengthOffset + $i] ^= ord($lengthBytes[$i]);
        }

        // Convert the array to a binary string
        return base64_encode(implode('', array_map("chr", $rgb)));
    }

    public function computeHash(string $filePath): string
    {
        $this->initialize();

        try {
            /**
             * @todo Find a way to chunk calculate the hash; otherwise the current code will trigger memory error.
             * @see https://github.com/wp-staging/wp-staging-pro/issues/4112
             */
            $input = file_get_contents($filePath);
            $this->hashCore($input, 0, strlen($input));
            return $this->hashFinal();
        } catch (\Throwable $th) {
            debug_log('Failed to calculate QuickXorHash. Error message: ' . $th->getMessage());
            return '';
        }
    }
}
