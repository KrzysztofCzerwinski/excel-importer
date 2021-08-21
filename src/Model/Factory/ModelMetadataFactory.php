<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Model\Factory;

use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use Kczer\ExcelImporter\Annotation\ExcelColumn;
use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use Kczer\ExcelImporter\Exception\Annotation\ModelExcelColumnConfigurationException;
use Kczer\ExcelImporter\Exception\Annotation\ModelPropertyNotSettableException;
use Kczer\ExcelImporter\Exception\Annotation\NotExistingModelClassException;
use Kczer\ExcelImporter\Exception\Annotation\UnexpectedColumnExcelCellClassException;
use Kczer\ExcelImporter\Exception\DuplicateColumnKeyException;
use Kczer\ExcelImporter\Exception\ExcelImportConfigurationException;
use Kczer\ExcelImporter\Model\ModelMetadata;
use Kczer\ExcelImporter\Model\ModelPropertyMetadata;
use ReflectionClass;
use ReflectionException;
use function is_a;
use function key_exists;

class ModelMetadataFactory
{
    /**
     * @throws ModelExcelColumnConfigurationException
     * @throws ExcelImportConfigurationException
     */
    public function createMetadataFromModelClass(string $modelClass): ModelMetadata
    {
        try {
            $modelReflectionClass = new ReflectionClass($modelClass);
        } catch (ReflectionException $exception) {

            throw new NotExistingModelClassException($modelClass, $exception);
        }

        $reader = new AnnotationReader();
        $modelPropertiesMetadata = [];
        foreach ($modelReflectionClass->getProperties() as $reflectionProperty) {
            /** @var ExcelColumn|null $excelColumn */
            $excelColumn = $reader->getPropertyAnnotation($reflectionProperty, ExcelColumn::class);
            if (null === $excelColumn) {

                continue;
            }
            $modelPropertyMetadata = (new ModelPropertyMetadata())->setExcelColumn($excelColumn)->setPropertyName($reflectionProperty->getName());
            $this->validateExcelCellClass($modelPropertyMetadata)->validatePropertySettable($modelReflectionClass, $modelPropertyMetadata);
            $columnKey = $excelColumn->getColumnKey();
            if (key_exists($columnKey, $modelPropertiesMetadata)) {

                throw new DuplicateColumnKeyException($columnKey);
            }
            $modelPropertiesMetadata[$columnKey] = $modelPropertyMetadata;
        }

        return (new ModelMetadata())->setModelPropertiesMetadata($modelPropertiesMetadata);
    }

    /**
     * @throws UnexpectedColumnExcelCellClassException
     */
    private function validateExcelCellClass(ModelPropertyMetadata $modelPropertyMetadata): self
    {
        $excelColumn = $modelPropertyMetadata->getExcelColumn();
        if (!is_a($excelColumn->getTargetExcelCellClass(), AbstractExcelCell::class, true)) {

            throw new UnexpectedColumnExcelCellClassException($excelColumn->getTargetExcelCellClass(), $modelPropertyMetadata->getPropertyName());
        }

        return $this;
    }


    /**
     * @throws ModelPropertyNotSettableException
     */
    private function validatePropertySettable(ReflectionClass $modelReflectionClass, ModelPropertyMetadata $modelPropertyMetadata): void
    {
        try {
            $setterReflection = $modelReflectionClass->getMethod($modelPropertyMetadata->getSetterName());
        } catch (ReflectionException $exception) {

            throw new ModelPropertyNotSettableException($modelPropertyMetadata, $modelReflectionClass, $exception);
        }
        if (!$setterReflection->isPublic()) {

            throw new ModelPropertyNotSettableException($modelPropertyMetadata, $modelReflectionClass);
        }

    }
}