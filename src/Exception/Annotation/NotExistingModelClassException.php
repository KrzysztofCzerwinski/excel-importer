<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Exception\Annotation;

use Exception;
use Throwable;

class NotExistingModelClassException extends Exception
{
    public function __construct(string $givenClass, Throwable $previous = null)
    {
        parent::__construct(sprintf("given class model '%s' does not exist", $givenClass), 0, $previous);
    }
}