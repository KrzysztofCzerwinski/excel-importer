<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Model\Factory;

use Exception;
use Kczer\ExcelImporter\ExcelElement\ExcelRow;
use Kczer\ExcelImporter\Exception\Annotation\SetterNotCompatibleWithExcelCellValueException;
use Kczer\ExcelImporter\Model\ModelMetadata;

class ModelFactory
{
    /**
     * @param string $modelClass
     * @param ExcelRow[] $excelRows
     * @param ModelMetadata $modelMetadata
     *
     * @return array array of models associated with ModelImport class
     *
     * @throws SetterNotCompatibleWithExcelCellValueException
     */
    public static function createModelsFromExcelRowsAndModelMetadata(string $modelClass, array $excelRows, ModelMetadata $modelMetadata): array
    {
        $models = [];
        foreach ($excelRows as $excelRow) {
            $model = new $modelClass();
            $excelCells = $excelRow->getExcelCells();
            foreach ($modelMetadata->getModelPropertiesMetadataWithConfiguredColumnKeys() as $columnKey => $modelPropertyMetadata) {
                $setterMethodName = $modelPropertyMetadata->getSetterName();
                $excelCell = $excelCells[$columnKey];
                try {
                    $model->{$setterMethodName}($excelCell->getValue());
                } catch (Exception $exception) {

                    throw new SetterNotCompatibleWithExcelCellValueException($excelCell, $modelPropertyMetadata, $exception);
                }
            }

            $models[] = $model;
        }

        return $models;
    }
}