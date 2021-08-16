<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Exception\Annotation;

use Exception;
use Throwable;

class ModelExcelColumnConfigurationException extends Exception
{
    public function __construct(Throwable $previous)
    {
        parent::__construct('[Excel configuration exception]', 0, $previous);
    }
}