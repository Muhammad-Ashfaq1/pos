<?php

namespace App\Reports;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Central catalogue of available reports. Resolve a report by its key and list
 * them for the report picker. Register new reports here — the rest of the
 * reporting stack is generic and needs no changes.
 */
class ReportRegistry
{
    /** @var array<int, class-string<ReportDefinition>> */
    private const REPORTS = [
        SalesReport::class,
        PaymentsReport::class,
        ProductsReport::class,
        CustomersReport::class,
    ];

    /** @var array<string, ReportDefinition>|null */
    private ?array $resolved = null;

    /** @return array<string, ReportDefinition> key => definition */
    public function all(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $resolved = [];

        foreach (self::REPORTS as $class) {
            $definition = new $class;
            $resolved[$definition->key()] = $definition;
        }

        return $this->resolved = $resolved;
    }

    public function resolve(string $key): ReportDefinition
    {
        return $this->all()[$key] ?? throw new NotFoundHttpException("Unknown report [{$key}].");
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function defaultKey(): string
    {
        return array_key_first($this->all());
    }

    /** Lightweight list for the report tabs: [['key' => ..., 'label' => ...], ...]. */
    public function tabs(): array
    {
        return array_map(
            fn (ReportDefinition $definition): array => [
                'key' => $definition->key(),
                'label' => $definition->label(),
            ],
            array_values($this->all()),
        );
    }
}
