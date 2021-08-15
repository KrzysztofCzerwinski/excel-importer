<?php

namespace Kczer\ExcelImporter\ExcelElement\ExcelCell;

use Kczer\ExcelImporter\MessageInterface;

/**
 * An excell cell that requires value to be a valid number
 */
class FloatExcelCell extends AbstractExcelCell
{

    /**
     * @inheritDoc
     */
    protected function getParsedValue(): ?float
    {
        return null !== $this->rawValue ? (float)$this->rawValue : null;
    }

    /**
     * @inheritDoc
     */
    protected function validateValueRequirements(): ?string
    {
        if (!is_numeric($this->rawValue)) {

            return $this->createErrorMessageWithNamePrefix(MessageInterface::NUMERIC_VALUE_REQUIRED);
        }

        return null;
    }
}