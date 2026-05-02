<?php

namespace WPStaging\Pro\Backup\Dto\Task\Extract\Response;

use WPStaging\Framework\Job\Dto\TaskResponseDto;

/**
 * Task response for the extraction finish step.
 */
class ExtractFinishResponseDto extends TaskResponseDto
{
    /** @var string */
    private $path;

    /** @var int */
    private $extracted = 0;

    /** @var int */
    private $skipped = 0;

    /** @var string[] */
    private $errors = [];

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return (string)$this->path;
    }

    public function setExtracted(int $extracted)
    {
        $this->extracted = max(0, $extracted);
    }

    public function getExtracted(): int
    {
        return (int)$this->extracted;
    }

    public function setSkipped(int $skipped)
    {
        $this->skipped = max(0, $skipped);
    }

    public function getSkipped(): int
    {
        return (int)$this->skipped;
    }

    /**
     * @param string[] $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = array_values(array_filter(array_map('strval', $errors)));
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
