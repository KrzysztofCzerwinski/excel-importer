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
use Kczer\ExcelImporter\Model\ModelMetadata;
use Kczer\ExcelImporter\Model\ModelPropertyMetadata;
use ReflectionClass;
use ReflectionException;
use function is_a;

class ModelMetadataFactory
{

    /**
     * @throws ModelExcelColumnConfigurationException
     */
    public function createMetadataFromModelClass(string $modelClass): ModelMetadata
    {
        try {

            return $this->retrieveFromModelClass($modelClass);
        } catch (NotExistingModelClassException | UnexpectedColumnExcelCellClassException | ModelPropertyNotSettableException | Exception $exception) {

            throw new ModelExcelColumnConfigurationException($exception);
        }
    }

    /**
     * @throws NotExistingModelClassException
     * @throws UnexpectedColumnExcelCellClassException
     * @throws ModelPropertyNotSettableException
     */
    private function retrieveFromModelClass(string $modelClass): ModelMetadata
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
            if ($excelColumn === null) {

                continue;
            }
            $modelPropertyMetadata = (new ModelPropertyMetadata())->setExcelColumn($excelColumn)->setPropertyName($reflectionProperty->getName());
            $this->validateExcelCellClass($modelPropertyMetadata)->validatePropertySettable($modelReflectionClass, $modelPropertyMetadata);

            $modelPropertiesMetadata[] = $modelPropertyMetadata;
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
        if (! $setterReflection->isPublic()) {

            throw new ModelPropertyNotSettableException($modelPropertyMetadata, $modelReflectionClass);
        }

    }
}