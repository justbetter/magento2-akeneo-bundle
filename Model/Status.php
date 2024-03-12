<?php

namespace JustBetter\AkeneoBundle\Model;

class Status
{
    public const STATUS_ENABLED = 1;

    public const STATUS_DISABLED = 2;

    public static function getOptionArray(): array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Retrieve option array with empty value
     */
    public function getAllOptions(): array
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     */
    public function getOptionText(string $optionId): ?string
    {
        $options = self::getOptionArray();

        return $options[$optionId] ?? null;
    }
}