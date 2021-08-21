<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Model\Factory;

use Kczer\ExcelImporter\ExcelElement\ExcelRow;
use Kczer\ExcelImporter\Exception\Annotation\SetterNotCompatibleWithExcelCellValueException;
use Kczer\ExcelImporter\Model\ModelMetadata;
use Throwable;

class ModelFactory
{
    /**
     * @param string $modelClass
     * @param ExcelRow[] $excelRows
     * @param ModelMetadata $modelMetadata
     *
     * @return array Array of models associated with ModelImport class
     *
     * @throws SetterNotCompatibleWithExcelCellValueException
     */
    public static function createModelsFromExcelRowsAndModelMetadata(string $modelClass, array $excelRows, ModelMetadata $modelMetadata): array
    {
        $models = [];
        foreach ($excelRows as $excelRow) {
            $model = new $modelClass();
            $excelCells = $excelRow->getExcelCells();
            foreach ($modelMetadata->getModelPropertiesMetadata() as $columnKey => $modelPropertyMetadata) {
                $setterMethodName = $modelPropertyMetadata->getSetterName();
                $excelCell = $excelCells[$columnKey];
                try {
                    $excelCellValue = $excelCell->getValue();
                    if (null !== $excelCellValue) {
                        $model->{$setterMethodName}($excelCellValue);
                    }
                } catch (Throwable $exception) {

                    throw new SetterNotCompatibleWithExcelCellValueException($excelCell, $modelPropertyMetadata, $exception);
                }
            }

            $models[] = $model;
        }

        return $models;
    }
}