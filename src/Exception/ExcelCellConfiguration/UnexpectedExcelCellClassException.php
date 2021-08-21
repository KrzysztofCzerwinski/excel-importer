<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Exception\ExcelCellConfiguration;

use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use Kczer\ExcelImporter\Exception\ExcelImportConfigurationException;
use Throwable;

class UnexpectedExcelCellClassException extends ExcelImportConfigurationException
{
    public function __construct(string $givenClass, Throwable $previous = null)
    {
        parent::__construct(sprintf('Class %s does not extend %s.', $givenClass, AbstractExcelCell::class), 0, $previous);
    }
}