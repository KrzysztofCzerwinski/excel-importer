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
use Kczer\ExcelImporter\Exception\JsonExcelRowsLoadException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;
use function array_map;
use function json_decode;
use function json_encode;
use function key;

abstract class AbstractExcelImporter
{
    /** @var ExcelCellConfiguration[] */
    private $excelCellConfigurations;

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
     * @return ExcelCellConfiguration[]
     */
    protected function getExcelCellConfigurations(): array
    {
        return $this->excelCellConfigurations;
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
     * @throws UnexpectedExcelCellClassException
     */
    protected abstract function configureExcelCells(): void;

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
     * @throws EmptyExcelColumnException
     * @throws JsonExcelRowsLoadException
     * @throws UnexpectedExcelCellClassException
     */
    public function parseJson(string $jsonExcelRows): self
    {
        $rawExcelRows = json_decode($jsonExcelRows, true);
        if (null === $rawExcelRows) {

            throw new JsonExcelRowsLoadException($jsonExcelRows);
        }
        $this->parseRawExcelRows($rawExcelRows, false);

        return $this;
    }

    /**
     * @throws EmptyExcelColumnException
     * @throws ExcelFileLoadException
     * @throws UnexpectedExcelCellClassException
     */
    public function parseExcelFile(string $excelFilePath): self
    {
        try {
            $sheet = IOFactory::load($excelFilePath)->getActiveSheet();
            $rawExcelRows = $sheet->toArray('', true, true, true);
        } catch (Throwable $exception) {

            throw new ExcelFileLoadException($excelFilePath, $exception);
        }
        $this->parseRawExcelRows($rawExcelRows);

        return $this;
    }

    public function getExcelRowsAsJson(): string
    {
        return json_encode(
            array_map(static function (ExcelRow $excelRow): array {
                return $excelRow->toArray();
            }, $this->excelRows)
        );
    }

    public function serializeInstance(): string
    {
        return base64_encode(serialize($this));
    }

    /**
     * @throws EmptyExcelColumnException
     * @throws UnexpectedExcelCellClassException
     */
    protected function parseRawExcelRows(array $rawExcelRows, bool $skipFirstRow = true): void
    {
        $this->configureExcelCells();
        $skeletonExcelCells = $this->createSkeletonExcelCells();
        foreach ($rawExcelRows as $rowKey => $rawCellValues) {
            if ($skipFirstRow && key($rawExcelRows) === $rowKey) {

                continue;
            }
            $this->excelRows[] = ExcelRowFactory::createFromExcelCellSkeletonsAndRawCellValues($skeletonExcelCells, $this->parseRawCellValuesString($rawCellValues));
        }
        $this->checkRowRequirements();
    }

    /**
     * Checks any additional requirements- useful to check requirements between separate cells or rows. <br>
     * Errors can be added via ExcelRow::addErrorMessage
     */
    protected function checkRowRequirements(): void
    {
    }

    /**
     * @throws UnexpectedExcelCellClassException
     */
    protected function addExcelCell(string $excelCellClass, string $cellName, string $columnKey, bool $cellRequired = true): self
    {
        $this->excelCellConfigurations[$columnKey] = new ExcelCellConfiguration($excelCellClass, $cellName, $cellRequired);

        return $this;
    }

    /**
     * Create ExcelCell without value (To avoid re-calling of dictionary setups which are the same for all rows).
     *
     * @return AbstractExcelCell[]
     */
    private function createSkeletonExcelCells(): array
    {
        $initialExcelCells = [];
        foreach ($this->getExcelCellConfigurations() as $columnKey => $excelCellConfiguration) {
            $initialExcelCells[$columnKey] = ExcelCellFactory::makeSkeletonFromConfiguration($excelCellConfiguration);
        }

        return $initialExcelCells;
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