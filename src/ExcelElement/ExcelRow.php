<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\ExcelElement;

use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use function array_filter;
use function array_map;
use function implode;
use function in_array;

class ExcelRow
{
    /** @var AbstractExcelCell[] */
    private $excelCells;


    /**
     * @param AbstractExcelCell[] $excelCells
     */
    public function __construct(array $excelCells)
    {
        $this->excelCells = $excelCells;
    }


    /**
     * @return AbstractExcelCell[]
     */
    public function getExcelCells(): array
    {
        return $this->excelCells;
    }


    public function hasErrors(): bool
    {
        return in_array(
            true,
            array_map(static function (AbstractExcelCell $excelCell): bool {
                return $excelCell->hasError();
            }, $this->excelCells)
        );
    }

    /**
     * @param string $separator string used to separate the messages
     *
     * @return string messages from all ExcelCells merged into one string
     */
    public function getMergedErrorMessage(string $separator = ' | '): string
    {
        return implode(
            $separator,
            array_filter(
                array_map(static function (AbstractExcelCell $excelCell) use ($separator): ?string {
                    return $excelCell->getErrorMessage();
                }, $this->excelCells)
            )
        );
    }
}