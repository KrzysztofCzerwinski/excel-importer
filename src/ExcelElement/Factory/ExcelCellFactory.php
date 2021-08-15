<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\ExcelElement\Factory;

use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use Kczer\ExcelImporter\ExcelElement\ExcelCell\Configuration\ExcelCellConfiguration;

class ExcelCellFactory
{
    public static function makeSkeletonFromConfiguration(ExcelCellConfiguration $configuration): AbstractExcelCell
    {
        $excelCellClass = $configuration->getExcelCellClass();

        return new $excelCellClass($configuration->getCellName(), $configuration->isCellRequired());
    }
}