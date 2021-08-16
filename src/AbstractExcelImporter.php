<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter;

use Kczer\ExcelImporter\ExcelElement\ExcelCell\AbstractExcelCell;
use Kczer\ExcelImporter\ExcelElement\ExcelCell\Configuration\ExcelCellConfiguration;
use Kczer\ExcelImporter\ExcelElement\ExcelRow;
use Kczer\ExcelImporter\ExcelElement\Factory\ExcelCellFactory;
use Kczer\ExcelImporter\ExcelElement\Factory\ExcelRowFactory;
use Kczer\ExcelImporter\Exception\ExcelCellConfiguration\UnexpectedExcelCellClassException;
use Kczer\ExcelImporter\Exception\ExcelFileLoadException;
use Kczer\ExcelImporter\Exception\InvalidExcelImporterSerializedDataException;
use Kczer\ExcelImporter\Exception\EmptyExcelColumnException;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use function array_keys;
use function count;
use function key;

abstract class AbstractExcelImporter
{
    /** @var AbstractExcelCell[]; */
    private $skeletonExcelCells;

    /** @var ExcelRow[] */
    private $excelRows;

    /**
     * @return ExcelRow[]
     */
    public function getExcelRows(): array
    {
        return $this->excelRows;
    }


    /**
     * @throws InvalidExcelImporterSerializedDataException
     */
    public static function createFromSerialized(string $serializedData): self
    {
        $staticInstance = unserialize(base64_decode($serializedData));
        if (!($staticInstance instanceof static)) {

            throw new InvalidExcelImporterSerializedDataException(static::class);
        }

        return $staticInstance;
    }


    /**
     * @return ExcelCellConfiguration[] Array with 'A-Z' or numeric keys and values and corresponding ExcelCellConfigurations as values
     *
     * @throws UnexpectedExcelCellClassException
     */
    protected abstract function getExcelCellConfigurations(): array;

    /**
     * Do something with parsed data (ExcelRows instances available via getExcelRows)
     */
    public abstract function processParsedData(): void;


    public function hasErrors(): bool
    {
        return in_array(
            true,
            array_map(static function (ExcelRow $excelCell): bool {
                return $excelCell->hasErrors();
            }, $this->excelRows)
        );
    }

    /**
     * @throws ExcelFileLoadException
     * @throws EmptyExcelColumnException
     * @throws UnexpectedExcelCellClassException
     */
    public function parseExcelData(string $excelFileAbsolutePath, bool $skipFirstRow = true): self
    {
        $this->skeletonExcelCells = $this->createSkeletonExcelCells();

        try {
            $sheet = IOFactory::load($excelFileAbsolutePath)->getActiveSheet();

            $rawExcelRows = $sheet->toArray('', true, true, !$this->areConfigurationKeysIntegers());
        } catch (Exception $exception) {

            throw new ExcelFileLoadException($excelFileAbsolutePath, $exception);
        }

        foreach ($rawExcelRows as $rowKey => $rawCellValues) {
            if (key($rawExcelRows) === $rowKey) {

                continue;
            }
            $this->excelRows[] = ExcelRowFactory::createFromExcelCellSkeletonsAndRawCellValues($this->skeletonExcelCells, $this->parseRawCellValuesString($rawCellValues));
        }

        return $this;
    }

    public function serializeInstance(): string
    {
        return base64_encode(serialize($this));
    }

    /**
     * Create ExcelCell without value (To avoid re-calling of dictionary setups which are the same for all rows).
     *
     * @return AbstractExcelCell[]
     *
     * @throws UnexpectedExcelCellClassException
     */
    private function createSkeletonExcelCells(): array
    {
        $initialExcelCells = [];
        foreach ($this->getExcelCellConfigurations() as $columnKey => $excelCellConfiguration) {
            $initialExcelCells[$columnKey] = ExcelCellFactory::makeSkeletonFromConfiguration($excelCellConfiguration);
        }

        return $initialExcelCells;
    }

    private function areConfigurationKeysIntegers(): bool
    {
        return array_keys($this->skeletonExcelCells) === range(0, count($this->skeletonExcelCells) - 1);
    }

    /**
     * @param array $rawCellValues
     *
     * @return string[]
     */
    private function parseRawCellValuesString(array $rawCellValues): array
    {
        return array_map('strval', $rawCellValues);
    }

}