<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\ExcelElement\Factory;

use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use Kczer\ExcelImporter\ExcelElement\ExcelRow;
use Kczer\ExcelImporter\Exception\EmptyExcelColumnException;
use function key_exists;

class ExcelRowFactory
{
    /**
     * @param AbstractExcelCell[] $skeletonExcelCells
     * @param string[] $rawCellValues
     *
     * @return ExcelRow
     *
     * @throws EmptyExcelColumnException
     */
    public static function createFromExcelCellSkeletonsAndRawCellValues(array $skeletonExcelCells, array $rawCellValues): ExcelRow
    {
        /** @var AbstractExcelCell[] $excelCells */
        $excelCells = [];
        foreach ($skeletonExcelCells as $columnKey => $skeletonExcelCell) {
            if (!key_exists($columnKey, $rawCellValues)) {

                throw new EmptyExcelColumnException($skeletonExcelCell->getName(), $columnKey);
            }
            $excelCells[$columnKey] = (clone $skeletonExcelCell)->setRawValue($rawCellValues[$columnKey]);
        }

        return new ExcelRow($excelCells);
    }
}