<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Exception\Annotation;

use Exception;
use Throwable;
use function gettype;
use function is_object;
use function sprintf;

class InvalidAnnotationParamException extends Exception
{
    public function __construct(string $paramName, string $annotationClass, $givenParam, string $expectedType, Throwable $previous = null)
    {
        parent::__construct(sprintf(
            "param '%s' from %s annotation is expected to be of %s type, %s given",
            $paramName,
            $annotationClass,
            $expectedType,
            is_object($givenParam) ? get_class($givenParam) : gettype($givenParam)
        ), 0, $previous);
    }
}