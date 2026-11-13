<?php

namespace Tests\Feature;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Gym;
use App\Models\Member;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Al cobrar solo se puede elegir un plan propio, activo y no eliminado.
 * Se prueban las reglas del request directamente, así no depende del nombre de las rutas.
 */
class StorePaymentPlanTest extends TestCase
{
    use RefreshDatabase;

    private Gym $gymA;

    private Gym $gymB;

    private User $userA;

    private Member $memberA;

    protected function setUp(): void
    {
        parent::setUp();

        Model::unguard();

        $this->gymA = Gym::create(['name' => 'Gimnasio A']);
        $this->gymB = Gym::create(['name' => 'Gimnasio B']);

        $this->userA = User::create([
            'gym_id' => $this->gymA->id,
            'name' => 'Dueño A',
            'email' => 'dueno.a@test.com',
            'password' => 'secreto-123',
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        $this->memberA = Member::create([
            'gym_id' => $this->gymA->id,
            'first_name' => 'Alicia',
            'last_name' => 'DeAlfa',
            'status' => 'active',
            'joined_at' => today(),
        ]);
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_un_plan_propio_activo_es_valido(): void
    {
        $plan = $this->makePlan($this->gymA);

        $this->assertFalse($this->paymentErrors($plan)->has('plan_id'));
    }

    public function test_un_plan_de_otro_gimnasio_se_rechaza(): void
    {
        $planB = $this->makePlan($this->gymB);

        $this->assertTrue($this->paymentErrors($planB)->has('plan_id'));
    }

    public function test_un_plan_inactivo_se_rechaza(): void
    {
        $plan = $this->makePlan($this->gymA, active: false);

        $this->assertTrue($this->paymentErrors($plan)->has('plan_id'));
    }

    public function test_un_plan_eliminado_se_rechaza(): void
    {
        $plan = $this->makePlan($this->gymA);
        $plan->delete();

        $this->assertTrue($this->paymentErrors($plan)->has('plan_id'));
    }

    private function makePlan(Gym $gym, bool $active = true): Plan
    {
        return Plan::create([
            'gym_id' => $gym->id,
            'name' => 'Plan de prueba',
            'price' => 25000,
            'duration_days' => 30,
            'active' => $active,
        ]);
    }

    private function paymentErrors(Plan $plan): \Illuminate\Support\MessageBag
    {
        $data = ['plan_id' => $plan->id, 'amount' => 25000, 'method' => 'cash'];

        $request = StorePaymentRequest::create('/members/'.$this->memberA->id.'/payments', 'POST', $data);
        $request->setUserResolver(fn () => $this->userA);

        $route = new Route('POST', 'members/{member}/payments', fn () => null);
        $route->bind($request);
        $route->setParameter('member', $this->memberA);
        $request->setRouteResolver(fn () => $route);

        return Validator::make($data, $request->rules())->errors();
    }
}