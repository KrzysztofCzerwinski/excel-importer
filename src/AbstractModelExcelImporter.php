<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter;

use Kczer\ExcelImporter\Exception\Annotation\ModelExcelColumnConfigurationException;
use Kczer\ExcelImporter\Exception\Annotation\SetterNotCompatibleWithExcelCellValueException;
use Kczer\ExcelImporter\Exception\EmptyExcelColumnException;
use Kczer\ExcelImporter\Exception\ExcelCellConfiguration\UnexpectedExcelCellClassException;
use Kczer\ExcelImporter\Model\Factory\ModelFactory;
use Kczer\ExcelImporter\Model\Factory\ModelMetadataFactory;
use Kczer\ExcelImporter\Model\ModelMetadata;


abstract class AbstractModelExcelImporter extends AbstractExcelImporter
{
    /** @var ModelMetadata */
    private $modelMetadata;

    /** @var array */
    private $models = [];

    /**
     * @return array Array of models associated with ModelClass
     *
     * @warning Array will be empty if import has any errors
     */
    public function getModels(): array
    {
        return $this->models;
    }

    /**
     * Do something with parsed data (models available via getModels())
     */
    public abstract function processParsedData(): void;

    /**
     * @return string Fully qualified class name of model attached to this Importer instance
     */
    protected abstract function getImportModelClass(): string;

    /**
     * @throws UnexpectedExcelCellClassException
     * @throws EmptyExcelColumnException
     * @throws SetterNotCompatibleWithExcelCellValueException
     * @throws ModelExcelColumnConfigurationException
     */
    protected function parseRawExcelRows(array $rawExcelRows, bool $skipFirstRow = true): void
    {
        $this->assignModelMetadata();
        parent::parseRawExcelRows($rawExcelRows, $skipFirstRow);
        if (!$this->hasErrors()) {
            $this->models = ModelFactory::createModelsFromExcelRowsAndModelMetadata($this->getImportModelClass(), $this->getExcelRows(), $this->modelMetadata);
        }
    }

    /**
     * @throws UnexpectedExcelCellClassException
     */
    protected function configureExcelCells(): void
    {
        foreach ($this->modelMetadata->getModelPropertiesMetadata() as $columnKey => $propertyMetadata) {
            $propertyExcelColumn = $propertyMetadata->getExcelColumn();

            $this->addExcelCell($propertyExcelColumn->getTargetExcelCellClass(), $propertyExcelColumn->getCellName(), $columnKey, $propertyExcelColumn->isRequired());
        }
    }

    /**
     * @throws ModelExcelColumnConfigurationException
     */
    private function assignModelMetadata(): void
    {
        $this->modelMetadata = (new ModelMetadataFactory())->createMetadataFromModelClass($this->getImportModelClass());
    }
}