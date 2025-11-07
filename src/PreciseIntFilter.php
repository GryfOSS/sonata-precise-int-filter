<?php

declare(strict_types=1);

namespace GryfOSS\SonataAdmin\Filter;

use GryfOSS\Formatter\IntPrecisionHelper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Filter\Filter;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * PreciseIntFilter allows to filter by conversion of a float from 1.23 to 123 before comparisong with the database.
 * Useful for tables storing monetary values as integers (cents) instead of floats (dollars). Allows precise operation.
 *
 * Created only because SonataAdminBundle does not provide such a filter out of the box, does not allow to decorate or
 * extend existing filters. Decoration ends with a notice about uninitialized filters.
 */
final class PreciseIntFilter extends Filter
{
    public const CHOICES = [
        NumberOperatorType::TYPE_EQUAL => '=',
        NumberOperatorType::TYPE_GREATER_EQUAL => '>=',
        NumberOperatorType::TYPE_GREATER_THAN => '>',
        NumberOperatorType::TYPE_LESS_EQUAL => '<=',
        NumberOperatorType::TYPE_LESS_THAN => '<',
    ];

    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): void
    {
        if (!$data->hasValue() || !is_numeric($data->getValue())) {
            return;
        }

        $modifiedValue = IntPrecisionHelper::normalize($data->getValue());

        $type = $data->getType() ?? NumberOperatorType::TYPE_EQUAL;
        $operator = $this->getOperator($type);

        $parameterName = $this->getNewParameterName($query);
        $this->applyWhere($query, \sprintf('%s.%s %s :%s', $alias, $field, $operator, $parameterName));
        $query->getQueryBuilder()->setParameter($parameterName, $modifiedValue);
    }

    public function getDefaultOptions(): array
    {
        return [
            'field_type' => NumberType::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array
    {
        return [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
            'operator_type' => NumberOperatorType::class,
        ];
    }

    private function getOperator(int $type): string
    {
        if (!isset(self::CHOICES[$type])) {
            throw new \OutOfRangeException(\sprintf(
                'The type "%s" is not supported, allowed one are "%s".',
                $type,
                implode('", "', array_keys(self::CHOICES))
            ));
        }

        return self::CHOICES[$type];
    }
}
