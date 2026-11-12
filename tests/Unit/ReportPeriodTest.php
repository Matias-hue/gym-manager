<?php

namespace Tests\Unit;

use App\Services\Reports\ReportPeriod;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportPeriodTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Miércoles 11 de noviembre de 2026
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 12));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_este_mes_va_del_primero_hasta_hoy(): void
    {
        $period = ReportPeriod::preset('this_month');

        $this->assertSame('2026-11-01', $period->from->toDateString());
        $this->assertSame('2026-11-11', $period->to->toDateString());
        $this->assertSame(11, $period->days());
        $this->assertSame(11, $period->elapsedDays());
    }

    public function test_el_periodo_anterior_tiene_los_mismos_dias_y_termina_el_dia_previo(): void
    {
        $previous = ReportPeriod::preset('this_month')->previous();

        $this->assertSame('2026-10-21', $previous->from->toDateString());
        $this->assertSame('2026-10-31', $previous->to->toDateString());
        $this->assertSame(11, $previous->days());
    }

    public function test_mes_pasado_cubre_octubre_completo(): void
    {
        $period = ReportPeriod::preset('last_month');

        $this->assertSame('2026-10-01', $period->from->toDateString());
        $this->assertSame('2026-10-31', $period->to->toDateString());
        $this->assertSame(31, $period->days());
        $this->assertSame(31, $period->elapsedDays());
    }

    public function test_periodos_cortos_se_agrupan_por_dia_con_ceros_incluidos(): void
    {
        $period = ReportPeriod::preset('7d');
        $buckets = $period->buckets();

        $this->assertSame('day', $period->granularity());
        $this->assertCount(7, $buckets);
        $this->assertArrayHasKey('2026-11-05', $buckets);
        $this->assertArrayHasKey('2026-11-11', $buckets);
        $this->assertSame('05/11', $buckets['2026-11-05']);
    }

    public function test_periodos_largos_se_agrupan_por_mes(): void
    {
        $period = ReportPeriod::preset('this_year');
        $buckets = $period->buckets();

        $this->assertSame('month', $period->granularity());
        $this->assertCount(11, $buckets);
        $this->assertArrayHasKey('2026-01', $buckets);
        $this->assertArrayHasKey('2026-11', $buckets);
    }

    public function test_si_las_fechas_vienen_al_reves_se_ordenan(): void
    {
        $period = ReportPeriod::make(Carbon::parse('2026-11-10'), Carbon::parse('2026-11-01'));

        $this->assertSame('2026-11-01', $period->from->toDateString());
        $this->assertSame('2026-11-10', $period->to->toDateString());
    }

    public function test_un_periodo_futuro_no_divide_por_cero_en_los_promedios(): void
    {
        $period = ReportPeriod::make(Carbon::parse('2026-12-01'), Carbon::parse('2026-12-31'));

        $this->assertSame(1, $period->elapsedDays());
    }

    public function test_equals_compara_ambos_extremos(): void
    {
        $this->assertTrue(ReportPeriod::preset('today')->equals(ReportPeriod::make(today(), today())));
        $this->assertFalse(ReportPeriod::preset('today')->equals(ReportPeriod::preset('7d')));
    }
}
