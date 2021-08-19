<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter;

use Kczer\ExcelImporter\Exception\Annotation\ModelExcelColumnConfigurationException;
use Kczer\ExcelImporter\Exception\Annotation\SetterNotCompatibleWithExcelCellValueException;
use Kczer\ExcelImporter\Exception\EmptyExcelColumnException;
use Kczer\ExcelImporter\Exception\ExcelCellConfiguration\UnexpectedExcelCellClassException;
use Kczer\ExcelImporter\Exception\ExcelFileLoadException;
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
     * @throws ModelExcelColumnConfigurationException
     */
    private function assignModelMetadata(): void
    {
        $this->modelMetadata = (new ModelMetadataFactory())->createMetadataFromModelClass($this->getImportModelClass());
    }

    /**
     * @throws EmptyExcelColumnException
     * @throws ExcelFileLoadException
     * @throws ModelExcelColumnConfigurationException
     * @throws SetterNotCompatibleWithExcelCellValueException
     */
    public function parseExcelData(string $excelFileAbsolutePath, bool $skipFirstRow = true): AbstractExcelImporter
    {
        $this->assignModelMetadata();
        parent::parseExcelData($excelFileAbsolutePath, $skipFirstRow);
        if (!$this->hasErrors()) {
            $this->models = ModelFactory::createModelsFromExcelRowsAndModelMetadata($this->getImportModelClass(), $this->getExcelRows(), $this->modelMetadata);
        }

        return $this;
    }

    /**
     * @throws UnexpectedExcelCellClassException
     */
    protected function configureExcelCells(): void
    {
        foreach ($this->modelMetadata->getModelPropertiesMetadataWithConfiguredColumnKeys() as $columnKey => $propertyMetadata) {
            $propertyExcelColumn = $propertyMetadata->getExcelColumn();

            $this->addExcelCellConfiguration($propertyExcelColumn->getTargetExcelCellClass(), $propertyExcelColumn->getCellName(), $columnKey, $propertyExcelColumn->isRequired());
        }
    }
}