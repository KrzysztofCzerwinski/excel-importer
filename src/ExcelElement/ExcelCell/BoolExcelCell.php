<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\ExcelElement\ExcelCell;

use function in_array;
use function strtolower;

class BoolExcelCell extends AbstractExcelCell
{

    /** @var string[] */
    private const SUPPORTED_TRUE_VALUES  = ['y', 'yes', 't', 'tak', 't', 'true'];

    /**
     * @inheritDoc
     */
    protected function getParsedValue(): ?bool
    {
        return null !== $this->rawValue ? in_array(strtolower($this->rawValue), self::SUPPORTED_TRUE_VALUES, true) : null;
    }

    /**
     * @inheritDoc
     */
    protected function validateValueRequirements(): ?string
    {
        return null;
    }
}