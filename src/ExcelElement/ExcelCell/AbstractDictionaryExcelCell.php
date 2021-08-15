<?php
declare(strict_types=1);

namespace Kczer\ExcelImporter\ExcelElement\ExcelCell;

use Kczer\ExcelImporter\MessageInterface;
use function key_exists;

/**
 * Excel cell type that requires value to be in a certain range (specified by getDictionary method)
 */
abstract class AbstractDictionaryExcelCell extends AbstractExcelCell
{

    /** @var array */
    private $dictionary;

    public function __construct(string $name, bool $required = false)
    {
        parent::__construct($name, $required);

        $this->dictionary = $this->getDictionary();
    }

    /**
     * @return array Array with string keys, which will be compared against excel values, and which values will be returned on getValue() call
     */
    protected abstract function getDictionary(): array;

    /**
     * @inheritDoc
     */
    protected function getParsedValue()
    {
        return null !== $this->rawValue ? $this->dictionary[$this->rawValue] : null;
    }

    /**
     * @inheritDoc
     */
    protected function validateValueRequirements(): ?string
    {
        if (!key_exists($this->rawValue, $this->dictionary)) {

            return $this->createErrorMessageWithNamePrefix(MessageInterface::DICTIONARY_VALUE_NOT_FOUND);
        }

        return null;
    }
}