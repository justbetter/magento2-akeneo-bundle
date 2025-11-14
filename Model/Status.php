<?php

namespace JustBetter\AkeneoBundle\Model;

class Status
{
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 2;

    /**
     * @return array<int, string>
     */
    public static function getOptionArray(): array
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled'),
        ];
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public function getAllOptions(): array
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    public function getOptionText(string $optionId): ?string
    {
        $options = self::getOptionArray();

        return $options[$optionId] ?? null;
    }
}
