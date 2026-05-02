<?php

namespace WPStaging\Pro\Backup\Dto\Service;

use WPStaging\Backup\Dto\Service\ArchiverDto as BaseArchiverDto;

class ArchiverDto extends BaseArchiverDto
{
    /** @var int */
    private $compressedBytesTotal = 0;

    /** @var bool|null */
    private $isCompressed = null;

    /**
     * @return void
     */
    public function reset()
    {
        parent::reset();
        $this->setCompressedBytesTotal(0);
        $this->isCompressed = null;
    }

    /**
     * @param int $compressedBytesTotal
     * @return void
     */
    public function setCompressedBytesTotal(int $compressedBytesTotal)
    {
        $this->compressedBytesTotal = $compressedBytesTotal;
    }

    /**
     * @param int $compressedBytesTotal
     * @return void
     */
    public function appendCompressedBytesTotal(int $compressedBytesTotal)
    {
        $this->compressedBytesTotal += $compressedBytesTotal;
    }

    public function getCompressedBytesTotal(): int
    {
        return $this->compressedBytesTotal;
    }

    /**
     * @return bool|null
     */
    public function getIsCompressed()
    {
        return $this->isCompressed;
    }

    /**
     * @param bool $isCompressed
     * @return void
     */
    public function setIsCompressed(bool $isCompressed)
    {
        $this->isCompressed = $isCompressed;
    }
}
