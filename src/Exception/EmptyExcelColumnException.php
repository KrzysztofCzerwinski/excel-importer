<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Exception;

use Exception;
use Throwable;

class EmptyExcelColumnException extends Exception
{
    /** @var string */
    private $cellName;

    /** @var string|int */
    private $columnKey;

    /**
     * @param string $cellName
     * @param string|int $columnKey
     * @param Throwable|null $previous
     */
    public function __construct(string $cellName, $columnKey, Throwable $previous = null)
    {
        $this->cellName = $cellName;
        $this->columnKey = $columnKey;

        parent::__construct(sprintf("No data provided in column '%s' of key '%s'", $cellName, (string)$columnKey), 0, $previous);
    }

    public function getCellName(): string
    {
        return $this->cellName;
    }

    /**
     * @return int|string
     */
    public function getColumnKey()
    {
        return $this->columnKey;
    }


}