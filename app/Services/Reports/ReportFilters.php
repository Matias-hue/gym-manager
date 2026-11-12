<?php

namespace App\Services\Reports;

use App\Models\Gym;

/**
 * Filtros ya validados que reciben todos los servicios de reportes.
 */
final class ReportFilters
{
    public function __construct(
        public readonly Gym $gym,
        public readonly ReportPeriod $period,
        public readonly ?string $method = null,
        public readonly ?int $planId = null,
    ) {}

    public function gymId(): int
    {
        return (int) $this->gym->getKey();
    }

    public function withPeriod(ReportPeriod $period): self
    {
        return new self($this->gym, $period, $this->method, $this->planId);
    }

    /** Sin método ni plan: para reportes que no los usan (resumen, socios...). */
    public function withoutExtraFilters(): self
    {
        return new self($this->gym, $this->period);
    }

    /** @return array{from: string, to: string} */
    public function periodQuery(): array
    {
        return $this->period->toQuery();
    }

    /** @return array<string, string|int> */
    public function extraQuery(): array
    {
        return array_filter([
            'method'  => $this->method,
            'plan_id' => $this->planId,
        ], fn ($value) => $value !== null && $value !== '');
    }

    /** @return array<string, string|int> */
    public function toQuery(): array
    {
        return $this->periodQuery() + $this->extraQuery();
    }
}
