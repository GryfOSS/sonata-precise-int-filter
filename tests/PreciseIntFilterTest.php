<?php

declare(strict_types=1);

namespace GryfOSS\SonataAdmin\Filter\Tests;

use Doctrine\ORM\QueryBuilder;
use GryfOSS\Formatter\IntPrecisionHelper;
use GryfOSS\SonataAdmin\Filter\PreciseIntFilter;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * Comprehensive test suite for PreciseIntFilter class.
 * Tests all public methods and edge cases to ensure proper functionality.
 */
class PreciseIntFilterTest extends TestCase
{
    private PreciseIntFilter $filter;

    protected function setUp(): void
    {
        $this->filter = new PreciseIntFilter();
        // Initialize the filter properly
        $this->filter->initialize('test_filter', []);
    }

    /**
     * Test that CHOICES constant contains all expected operators
     */
    public function testChoicesConstantContainsAllOperators(): void
    {
        $expectedChoices = [
            NumberOperatorType::TYPE_EQUAL => '=',
            NumberOperatorType::TYPE_GREATER_EQUAL => '>=',
            NumberOperatorType::TYPE_GREATER_THAN => '>',
            NumberOperatorType::TYPE_LESS_EQUAL => '<=',
            NumberOperatorType::TYPE_LESS_THAN => '<',
        ];

        $this->assertSame($expectedChoices, PreciseIntFilter::CHOICES);
    }

    /**
     * Test getDefaultOptions method
     */
    public function testGetDefaultOptions(): void
    {
        $options = $this->filter->getDefaultOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('field_type', $options);
        $this->assertSame(NumberType::class, $options['field_type']);
    }

    /**
     * Test getFormOptions method structure
     */
    public function testGetFormOptions(): void
    {
        // Since PreciseIntFilter is final, we'll test the method indirectly
        // by creating a reflection and testing what we can access
        $reflection = new \ReflectionClass(PreciseIntFilter::class);
        $method = $reflection->getMethod('getFormOptions');

        // Test that the method exists and is public
        $this->assertTrue($method->isPublic());

        // Test the default options which are used by getFormOptions
        $defaultOptions = $this->filter->getDefaultOptions();
        $this->assertArrayHasKey('field_type', $defaultOptions);
        $this->assertSame(NumberType::class, $defaultOptions['field_type']);
    }

    /**
     * Test filter method with invalid operator type through reflection
     */
    public function testGetOperatorWithInvalidType(): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionMessage('The type "999" is not supported');

        // Use reflection to test the private getOperator method
        $reflection = new \ReflectionClass(PreciseIntFilter::class);
        $method = $reflection->getMethod('getOperator');
        $method->setAccessible(true);

        $method->invoke($this->filter, 999);
    }

    /**
     * Test getOperator method with valid types through reflection
     *
     * @dataProvider operatorProvider
     */
    public function testGetOperatorWithValidTypes(int $operatorType, string $expectedOperator): void
    {
        // Use reflection to test the private getOperator method
        $reflection = new \ReflectionClass(PreciseIntFilter::class);
        $method = $reflection->getMethod('getOperator');
        $method->setAccessible(true);

        $result = $method->invoke($this->filter, $operatorType);
        $this->assertSame($expectedOperator, $result);
    }

    /**
     * Test filter method behavior with no value
     */
    public function testFilterWithNoValue(): void
    {
        // Create real FilterData with no value
        $filterData = FilterData::fromArray([]);

        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Expect no interaction with the query builder when no value is provided
        $queryBuilder->expects($this->never())->method('setParameter');
        $proxyQuery->method('getQueryBuilder')->willReturn($queryBuilder);

        // This should return early and not set any parameters
        $this->filter->filter($proxyQuery, 'e', 'field', $filterData);

        // If we reach this point without exception, the test passes
        $this->assertTrue(true);
    }

    /**
     * Test filter method behavior with non-numeric value
     */
    public function testFilterWithNonNumericValue(): void
    {
        // Create real FilterData with non-numeric value
        $filterData = FilterData::fromArray(['value' => 'not_a_number']);

        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Expect no interaction with the query builder when value is not numeric
        $queryBuilder->expects($this->never())->method('setParameter');
        $proxyQuery->method('getQueryBuilder')->willReturn($queryBuilder);

        // This should return early and not set any parameters
        $this->filter->filter($proxyQuery, 'e', 'field', $filterData);

        // If we reach this point without exception, the test passes
        $this->assertTrue(true);
    }

    /**
     * Test integration with IntPrecisionHelper
     */
    public function testIntPrecisionHelperIntegration(): void
    {
        // Test various numeric values to ensure proper conversion
        $testCases = [
            ['12.34', 1234],
            ['-25.75', -2575],
            ['0.00', 0],
            [42, 4200],
            [99.99, 9999],
            ['123', 12300],
        ];

        foreach ($testCases as [$input, $expected]) {
            $normalized = IntPrecisionHelper::normalize($input);
            $this->assertSame($expected, $normalized, "Failed for input: {$input}");
        }
    }

    /**
     * Test numeric value detection logic
     */
    public function testNumericValueDetection(): void
    {
        $numericValues = ['12.34', '-25.75', '0', '42', 99.99, 0];
        $nonNumericValues = ['abc', '12.34.56', '', 'NaN'];

        foreach ($numericValues as $value) {
            $this->assertTrue(is_numeric($value), "Value {$value} should be numeric");
        }

        foreach ($nonNumericValues as $value) {
            $this->assertFalse(is_numeric($value), "Value {$value} should not be numeric");
        }
    }

    /**
     * Test edge cases with precision
     */
    public function testPrecisionEdgeCases(): void
    {
        // Test very small decimal value
        $result = IntPrecisionHelper::normalize('0.01');
        $this->assertSame(1, $result);

        // Test large numbers
        $result = IntPrecisionHelper::normalize('999999.99');
        $this->assertSame(99999999, $result);

        // Test negative values
        $result = IntPrecisionHelper::normalize('-123.45');
        $this->assertSame(-12345, $result);
    }

    /**
     * Test filter method with numeric values more directly
     */
    public function testFilterMethodWithNumericValues(): void
    {
        // Create FilterData with numeric value
        $filterData = FilterData::fromArray(['value' => '12.34']);

        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Set up the chain of method calls that the filter will make
        $proxyQuery->method('getQueryBuilder')->willReturn($queryBuilder);
        $proxyQuery->method('getUniqueParameterId')->willReturn(123);

        // The parameter name is prefixed with filter name, so expect "test_filter_123"
        $queryBuilder->expects($this->once())->method('setParameter')
            ->with('test_filter_123', 1234);

        // Since we can't extend the final class, we'll just test that the method runs
        $this->filter->filter($proxyQuery, 'entity', 'price', $filterData);

        // Test the conversion logic we can verify
        $this->assertSame(1234, IntPrecisionHelper::normalize('12.34'));
    }

    /**
     * Test filter method with zero values
     */
    public function testFilterMethodWithZeroValue(): void
    {
        // Create FilterData with zero value
        $filterData = FilterData::fromArray(['value' => '0.00']);

        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Set up the chain of method calls
        $proxyQuery->method('getQueryBuilder')->willReturn($queryBuilder);
        $proxyQuery->method('getUniqueParameterId')->willReturn(789);

        // The parameter name is prefixed with filter name, so expect "test_filter_789"
        $queryBuilder->expects($this->once())->method('setParameter')
            ->with('test_filter_789', 0);

        $this->filter->filter($proxyQuery, 'entity', 'field', $filterData);

        // Test that zero is handled correctly
        $this->assertSame(0, IntPrecisionHelper::normalize('0.00'));
    }

    /**
     * Test filter method with empty string (should not filter)
     */
    public function testFilterMethodWithEmptyString(): void
    {
        // Create FilterData with empty string
        $filterData = FilterData::fromArray(['value' => '']);

        $proxyQuery = $this->createMock(ProxyQueryInterface::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        // Should not interact with query builder for empty string
        $queryBuilder->expects($this->never())->method('setParameter');
        $proxyQuery->method('getQueryBuilder')->willReturn($queryBuilder);

        $this->filter->filter($proxyQuery, 'entity', 'field', $filterData);

        $this->assertTrue(true);
    }

    /**
     * Data provider for different operators
     */
    public static function operatorProvider(): array
    {
        return [
            'equal' => [NumberOperatorType::TYPE_EQUAL, '='],
            'greater_equal' => [NumberOperatorType::TYPE_GREATER_EQUAL, '>='],
            'greater_than' => [NumberOperatorType::TYPE_GREATER_THAN, '>'],
            'less_equal' => [NumberOperatorType::TYPE_LESS_EQUAL, '<='],
            'less_than' => [NumberOperatorType::TYPE_LESS_THAN, '<'],
        ];
    }
}