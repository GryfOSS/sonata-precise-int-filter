# Sonata Precise Int Filter

A Sonata Admin filter for precise integer operations with float-to-int conversion using the IntPrecisionHelper library.

## Description

This filter allows you to filter database values by converting float inputs (e.g., `12.34`) to their integer representation (e.g., `1234`) before performing database comparisons. This is particularly useful for tables storing monetary values as integers (cents) instead of floats (dollars), enabling precise operations without floating-point precision issues.

## Installation

Install via Composer:

```bash
composer require gryfoss/sonata-precise-int-filter
```

## Usage

Use the `PreciseIntFilter` in your Sonata Admin configuration:

First register it in your services.yaml:

```yaml
  GryfOSS\SonataAdmin\Filter\PreciseIntFilter:
    tags:
      - { name: sonata.admin.filter.type, alias: gryfoss_filter_precise_int }
```

(You may use a different alias.)

```php
use GryfOSS\SonataAdmin\Filter\PreciseIntFilter;

protected function configureDatagridFilters(DatagridMapper $filter): void
{
    $filter
        ->add('price', PreciseIntFilter::class, [
            'label' => 'Price',
        ]);
}
```

## Features

- Supports all standard comparison operators: `=`, `>=`, `>`, `<=`, `<`
- Automatically converts float values to integers using IntPrecisionHelper
- Handles empty values and non-numeric inputs gracefully
- Validates operator types and throws appropriate exceptions
- Fully compatible with Sonata Admin Bundle

## Testing

Run the test suite:

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage
```

### Test Coverage

The project includes comprehensive unit tests covering:

- ✅ All public methods (getDefaultOptions, getFormOptions, filter)
- ✅ Private method testing via reflection (getOperator)
- ✅ All supported operators (=, >=, >, <=, <)
- ✅ Edge cases (empty values, non-numeric values, zero values)
- ✅ Integration with IntPrecisionHelper
- ✅ Error handling and validation
- ✅ Filter initialization and proper setup

Current test coverage: **75% (18/24 lines, 3/4 methods)**

### Running Tests

```bash
# Install dependencies
composer install

# Run tests
./vendor/bin/phpunit tests/

# Run tests with detailed output
./vendor/bin/phpunit tests/ --testdox

# Run tests with coverage (requires Xdebug)
XDEBUG_MODE=coverage ./vendor/bin/phpunit tests/ --coverage-text
```

## Future Plans

We have several enhancements planned for future releases:

- **Scale Option Support**: Add configurable scale option to allow different decimal precision levels beyond the default 2 decimal places
- **Functional Tests**: Implement comprehensive functional tests to verify filter behavior in real Sonata Admin environments
- **Enhanced Documentation**: Add more usage examples and integration guides

## Contributing

Contributions are welcome! Please feel free to:

- **Open Issues**: Report bugs, suggest features, or ask questions
- **Submit Pull Requests**: Contribute code improvements, bug fixes, or new features
- **Improve Documentation**: Help enhance the README or add code examples

Please ensure all contributions include appropriate tests and follow the existing code style.

## A Note on Future Sonata Development

This filter was created because Sonata Admin Bundle currently doesn't provide built-in support for precise integer filtering and doesn't allow easy decoration or extension of existing filters.

We hope that in future versions of Sonata Admin, the framework will:
- Allow proper decoration of existing filters
- Provide more flexibility in extending filter functionality
- Include built-in support for precision-based numeric filtering

If such features become available, this standalone filter may become unnecessary, which would be a positive development for the Sonata ecosystem.

## Dependencies

- `sonata-project/doctrine-orm-admin-bundle: ^4.19`
- `symfony/form: ^7.3`
- `gryfoss/int-precision-helper: ^1.1`

## Development Dependencies

- `phpunit/phpunit: ^10.0`
- `doctrine/dbal: ^3.0`
- `doctrine/orm: ^2.0`
- `symfony/framework-bundle: ^7.3`

## License

MIT License