<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Exception\ExcelCellConfiguration;

use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use Exception;
use Throwable;

class UnexpectedExcelCellClassException extends Exception
{
    public function __construct(string $givenClass, Throwable $previous = null)
    {
        parent::__construct(sprintf('Class %s does not extend %s.', $givenClass, AbstractExcelCell::class), 0, $previous);
    }
}