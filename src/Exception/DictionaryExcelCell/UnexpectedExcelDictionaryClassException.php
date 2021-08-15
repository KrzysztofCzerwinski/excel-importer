<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Exception\DictionaryExcelCell;

use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractDictionaryExcelCell;
use Exception;
use Throwable;

class UnexpectedExcelDictionaryClassException extends Exception
{
    public function __construct(string $givenClass, Throwable $previous = null)
    {
        parent::__construct(sprintf('Class %s does not extend %s', $givenClass, AbstractDictionaryExcelCell::class), 0, $previous);
    }
}