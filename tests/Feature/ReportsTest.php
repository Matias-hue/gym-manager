<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private Gym $gym;
    private User $owner;
    private User $reception;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        // Miércoles 11 de noviembre de 2026
        Carbon::setTestNow(Carbon::create(2026, 11, 11, 12));

        $this->gym = Gym::create([
            'name'       => 'Gimnasio piloto',
            'timezone'   => 'America/Argentina/Buenos_Aires',
            'currency'   => 'ARS',
            'grace_days' => 14,
        ]);

        $this->owner = User::factory()->create(['gym_id' => $this->gym->id, 'role' => 'owner']);
        $this->reception = User::factory()->create(['gym_id' => $this->gym->id, 'role' => 'reception']);

        $this->plan = Plan::create([
            'gym_id'        => $this->gym->id,
            'name'          => 'Musculación libre',
            'price'         => 25000,
            'duration_days' => 30,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ---------------------------------------------------------------------
    // Acceso
    // ---------------------------------------------------------------------

    public function test_un_visitante_va_al_login(): void
    {
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_recepcion_no_puede_ver_los_reportes(): void
    {
        $this->actingAs($this->reception)->get(route('reports.index'))->assertForbidden();
    }

    public function test_recepcion_no_puede_exportar(): void
    {
        $this->actingAs($this->reception)
            ->get(route('reports.export', ['report' => 'payments']))
            ->assertForbidden();
    }

    /**
     * @dataProvider tabs
     */
    public function test_el_dueno_ve_cada_pestana_aun_sin_datos(string $tab): void
    {
        $this->actingAs($this->owner)
            ->get(route('reports.index', ['tab' => $tab]))
            ->assertOk()
            ->assertSee('Reportes');
    }

    public static function tabs(): array
    {
        return [
            'resumen'    => ['summary'],
            'ingresos'   => ['income'],
            'socios'     => ['members'],
            'asistencia' => ['attendance'],
            'caja'       => ['cash'],
        ];
    }

    public function test_un_rango_invertido_se_rechaza(): void
    {
        $this->actingAs($this->owner)
            ->from(route('reports.index'))
            ->get(route('reports.index', ['from' => '2026-11-10', 'to' => '2026-11-01']))
            ->assertSessionHasErrors('to');
    }

    public function test_una_pestana_inexistente_se_rechaza(): void
    {
        $this->actingAs($this->owner)
            ->from(route('reports.index'))
            ->get(route('reports.index', ['tab' => 'secreto']))
            ->assertSessionHasErrors('tab');
    }

    // ---------------------------------------------------------------------
    // Ingresos
    // ---------------------------------------------------------------------

    public function test_los_ingresos_no_cuentan_pagos_anulados(): void
    {
        $member = $this->member('Lucas', 'Benítez');
        $this->payment($member, 25000, 'cash');
        $this->payment($member, 20000, 'transfer');
        $this->payment($member, 9999, 'cash', ['voided_at' => now(), 'void_reason' => 'Error de carga']);

        $response = $this->actingAs($this->owner)->get(route('reports.index', ['tab' => 'income']));

        $response->assertOk();
        $data = $response->viewData('data');

        $this->assertSame(45000, $data['kpis'][0]['value']);          // ingresos
        $this->assertSame(2, $data['kpis'][1]['value']);              // cobros
        $this->assertSame(22500, $data['kpis'][2]['value']);          // ticket promedio
        $this->assertSame(1, $data['voided']['count']);
        $this->assertSame(9999, $data['voided']['total']);
        $this->assertSame(3, $data['payments']->total());             // el detalle sí lista el anulado
    }

    public function test_los_ingresos_se_filtran_por_metodo(): void
    {
        $member = $this->member('Lucas', 'Benítez');
        $this->payment($member, 25000, 'cash');
        $this->payment($member, 20000, 'transfer');

        $response = $this->actingAs($this->owner)
            ->get(route('reports.index', ['tab' => 'income', 'method' => 'transfer']));

        $this->assertSame(20000, $response->viewData('data')['kpis'][0]['value']);
    }

    public function test_los_ingresos_de_otro_gimnasio_no_se_mezclan(): void
    {
        $otherGym = Gym::create(['name' => 'Otro', 'timezone' => 'America/Argentina/Buenos_Aires', 'currency' => 'ARS', 'grace_days' => 14]);
        $otherOwner = User::factory()->create(['gym_id' => $otherGym->id, 'role' => 'owner']);
        $otherMember = Member::create(['gym_id' => $otherGym->id, 'document_number' => '999', 'first_name' => 'Ajeno', 'last_name' => 'Ajeno', 'status' => 'active']);

        Payment::create([
            'gym_id' => $otherGym->id, 'member_id' => $otherMember->id, 'amount' => 77777,
            'method' => 'cash', 'paid_at' => now(), 'received_by' => $otherOwner->id,
        ]);

        $this->payment($this->member('Lucas', 'Benítez'), 25000, 'cash');

        $response = $this->actingAs($this->owner)->get(route('reports.index', ['tab' => 'income']));

        $this->assertSame(25000, $response->viewData('data')['kpis'][0]['value']);
    }

    public function test_el_csv_de_cobros_trae_los_datos_y_marca_los_anulados(): void
    {
        $member = $this->member('Lucas', 'Benítez');
        $this->payment($member, 25000, 'cash');
        $this->payment($member, 9999, 'cash', ['voided_at' => now()]);

        $response = $this->actingAs($this->owner)->get(route('reports.export', [
            'report' => 'payments', 'from' => '2026-11-01', 'to' => '2026-11-30',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Importe', $csv);
        $this->assertStringContainsString('Benítez, Lucas', $csv);
        $this->assertStringContainsString('25000', $csv);
        $this->assertStringContainsString('Anulado', $csv);
    }

    public function test_exportar_un_reporte_que_no_existe_da_404(): void
    {
        $this->actingAs($this->owner)->get('/reports/export/inventado')->assertNotFound();
    }

    // ---------------------------------------------------------------------
    // Socios
    // ---------------------------------------------------------------------

    public function test_el_estado_de_los_socios_respeta_la_regla_de_gracia(): void
    {
        $this->membership($this->member('Al', 'Día'), '2026-11-20');          // al día
        $this->membership($this->member('En', 'Gracia'), '2026-11-05');       // venció hace 6 días: gracia (14)
        $this->membership($this->member('Ya', 'Vencido'), '2026-10-20');      // venció hace 22 días: vencido
        $this->member('Sin', 'Cuota');                                         // nunca pagó

        $response = $this->actingAs($this->owner)->get(route('reports.index', ['tab' => 'members']));

        $response->assertOk();
        $states = $response->viewData('data')['charts']['states']['datasets'][0]['data'];

        $this->assertSame([1, 1, 1, 1], $states); // current, grace, expired, none
    }

    public function test_la_lista_de_vencen_pronto_incluye_solo_cuotas_al_dia_por_vencer(): void
    {
        $this->membership($this->member('Vence', 'Mañana'), '2026-11-12');
        $this->membership($this->member('Vence', 'Lejos'), '2026-12-30');

        $response = $this->actingAs($this->owner)->get(route('reports.index', ['tab' => 'members']));

        $expiring = $response->viewData('data')['expiring'];

        $this->assertSame(1, $expiring['total']);
        $this->assertSame('Mañana, Vence', $expiring['rows'][0]->name);
    }

    public function test_el_csv_de_socios_lista_a_todos(): void
    {
        $this->membership($this->member('Al', 'Día'), '2026-11-20');

        $csv = $this->actingAs($this->owner)
            ->get(route('reports.export', ['report' => 'members']))
            ->streamedContent();

        $this->assertStringContainsString('Día', $csv);
        $this->assertStringContainsString('Al día', $csv);
    }

    // ---------------------------------------------------------------------
    // Auxiliares
    // ---------------------------------------------------------------------

    private function member(string $first, string $last): Member
    {
        static $document = 30000000;

        return Member::create([
            'gym_id'          => $this->gym->id,
            'document_number' => (string) ++$document,
            'first_name'      => $first,
            'last_name'       => $last,
            'status'          => 'active',
            'joined_at'       => '2026-11-01',
        ]);
    }

    private function membership(Member $member, string $endsOn): Membership
    {
        return Membership::create([
            'gym_id' => $member->gym_id,
            'member_id'     => $member->id,
            'plan_id'       => $this->plan->id,
            'starts_on'     => Carbon::parse($endsOn)->subMonth()->toDateString(),
            'ends_on'       => $endsOn,
            'price_charged' => 25000,
            'status'        => 'active',
        ]);
    }

    private function payment(Member $member, int $amount, string $method, array $extra = []): Payment
    {
        $payment = new Payment();
        $payment->forceFill($extra + [
            'gym_id'      => $this->gym->id,
            'member_id'   => $member->id,
            'amount'      => $amount,
            'method'      => $method,
            'paid_at'     => now(),
            'received_by' => $this->owner->id,
        ])->save();

        return $payment;
    }
}
