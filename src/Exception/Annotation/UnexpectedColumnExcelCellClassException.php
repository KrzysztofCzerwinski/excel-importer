<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Exception\Annotation;

use Exception;
use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use Throwable;

class UnexpectedColumnExcelCellClassException extends Exception
{
    public function __construct(string $givenClass, string $propertyName, Throwable $previous = null)
    {
        parent::__construct(
            sprintf(
                "Target ExcelCell class '%s' attached to property '%s' does not exists or does not extend %s",
                $givenClass,
                $propertyName,
                AbstractExcelCell::class
            ),
            0,
            $previous
        );
    }
}