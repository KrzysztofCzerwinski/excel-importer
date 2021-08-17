<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\Annotation\Required;
use Kczer\ExcelImporter\Exception\Annotation\InvalidAnnotationParamException;
use function is_bool;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ExcelColumn
{
    /**
     * Column name
     *
     * @Required()
     *
     * @var string
     */
    private $cellName;

    /**
     * Fully qualified ExcelCell class
     *
     * @Required()
     *
     * @var string
     */
    private $targetExcelCellClass;

    /**
     * Column Id from EXCEL- if not provided, property order is mapped to column ids
     *
     * @var string|null
     */
    private $columnKey;

    /**
     * Whether column cells are required or not
     *
     * @var bool
     */
    private $required;


    /**
     * @throws InvalidAnnotationParamException
     */
    public function __construct(array $annotationData)
    {
        $this->cellName = $annotationData['cellName'] ?? null;
        $this->targetExcelCellClass = $annotationData['targetExcelCellClass'];
        $this->columnKey = $annotationData['columnId'] ?? null;

        $required = $annotationData['required'] ?? true;
        if (!is_bool($required)) {
           throw new InvalidAnnotationParamException('required', static::class, $required, 'bool');
        }
        $this->required = $annotationData['required'] ?? true;
    }

    public function getCellName(): string
    {
        return $this->cellName;
    }

    public function getTargetExcelCellClass(): string
    {
        return $this->targetExcelCellClass;
    }

    public function getColumnKey(): ?string
    {
        return $this->columnKey;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

}