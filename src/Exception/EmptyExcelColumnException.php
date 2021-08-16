<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Exception;

use Exception;
use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use Throwable;
use function get_class;

class EmptyExcelColumnException extends Exception
{
    /** @var AbstractExcelCell */
    private $excelCell;

    /** @var string|int */
    private $columnKey;

    /**
     * @param AbstractExcelCell $excelCell
     * @param string|int $columnKey
     * @param Throwable|null $previous
     */
    public function __construct(AbstractExcelCell $excelCell, $columnKey, Throwable $previous = null)
    {
        $this->excelCell = $excelCell;
        $this->columnKey = $columnKey;

        parent::__construct(
            sprintf("Empty column '%s' of key '%s', expected %s compatible values", $excelCell->getName(), (string)$columnKey, get_class($excelCell)),
            0,
            $previous
        );
    }

    public function getCellName(): AbstractExcelCell
    {
        return $this->excelCell;
    }

    /**
     * @return int|string
     */
    public function getColumnKey()
    {
        return $this->columnKey;
    }


}