<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\Model;


use function array_map;
use function in_array;

class ModelMetadata
{
    /** @var ModelPropertyMetadata[] */
    private $modelPropertiesMetadata;

    /**
     * @return ModelPropertyMetadata[] Keys are 0- indexed keys
     */
    public function getModelPropertiesMetadataWithIntKeys(): array
    {
        return $this->modelPropertiesMetadata;
    }

    /**
     * @return ModelPropertyMetadata[] Keys are column ids if all properties ExcelCells have columnId defined
     */
    public function getModelPropertiesMetadataWithConfiguredColumnKeys(): array
    {
        $haveAllPropertiesColumnIds = $this->haveAllPropertiesColumnIds();

        $modelPropertiesMetadata = [];
        foreach ($this->modelPropertiesMetadata as $index => $modelPropertyMetadata) {
            $modelPropertiesMetadata[$haveAllPropertiesColumnIds ? $modelPropertyMetadata->getExcelColumn()->getColumnKey() : $index] = $modelPropertyMetadata;
        }

        return $modelPropertiesMetadata;
    }

    /**
     * @param ModelPropertyMetadata[] $modelPropertiesMetadata
     */
    public function setModelPropertiesMetadata(array $modelPropertiesMetadata): self
    {
        $this->modelPropertiesMetadata = $modelPropertiesMetadata;

        return $this;
    }

    public function haveAllPropertiesColumnIds(): bool
    {
        return !in_array(false,
            array_map(static function (ModelPropertyMetadata $modelPropertyMetadata): bool {
                return $modelPropertyMetadata->hasColumnId();
            }, $this->modelPropertiesMetadata),
            true
        );
    }
}