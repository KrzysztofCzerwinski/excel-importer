<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\ExcelElement;

use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use function array_filter;
use function array_map;
use function array_merge;
use function implode;
use function in_array;

class ExcelRow
{
    /** @var AbstractExcelCell[] */
    private $excelCells;

    /** @var string[] */
    private $errorMessages = [];

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

    /**
     * @return string[]
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }


    public function addErrorMessage(string $errorMessage): void
    {
        $this->errorMessages[] = $errorMessage;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errorMessages) ||
            in_array(
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
                array_merge(
                    $this->errorMessages,
                    array_map(static function (AbstractExcelCell $excelCell) use ($separator): ?string {
                        return $excelCell->getErrorMessage();
                    }, $this->excelCells)
                )
            )
        );
    }

    /**
     * @return string[] Key are column keys. Values are rawValues from EXCEL file
     */
    public function toArray(): array
    {
        $excelCells = [];
        foreach ($this->excelCells as $columnKey => $excelCell) {
            $excelCells[$columnKey] = (string)$excelCell->getRawValue();
        }

        return $excelCells;
    }
}