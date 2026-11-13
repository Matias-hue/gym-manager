<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

/**
 * Comprueba que un gimnasio nunca ve ni toca datos de otro.
 * Se crean dos gimnasios (A y B) y se actúa siempre como usuario del A.
 */
class GymIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Gym $gymA;

    private Gym $gymB;

    private User $userA;

    private Member $memberA;

    private Member $memberB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Model::unguard();

        $this->gymA = Gym::create(['name' => 'Gimnasio A']);
        $this->gymB = Gym::create(['name' => 'Gimnasio B']);

        $this->userA = $this->makeOwner($this->gymA, 'dueno.a@test.com');
        $this->makeOwner($this->gymB, 'dueno.b@test.com');

        $this->memberA = $this->makeMember($this->gymA, 'Alicia', 'DeAlfa');
        $this->memberB = $this->makeMember($this->gymB, 'Bruno', 'DeBeta');
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_el_listado_solo_muestra_socios_del_propio_gimnasio(): void
    {
        $this->actingAs($this->userA)
            ->get(route('members.index'))
            ->assertOk()
            ->assertSee('DeAlfa')
            ->assertDontSee('DeBeta');
    }

    public function test_la_busqueda_no_encuentra_socios_de_otro_gimnasio(): void
    {
        // La página repite el texto buscado, así que se mira el resultado y no el HTML
        $this->actingAs($this->userA)
            ->get(route('members.index', ['q' => 'DeBeta']))
            ->assertOk()
            ->assertViewHas('members', fn ($members) => $members->total() === 0);

        $this->actingAs($this->userA)
            ->get(route('members.index', ['q' => 'DeAlfa']))
            ->assertOk()
            ->assertViewHas('members', fn ($members) => $members->total() === 1);
    }

    public function test_no_se_puede_ver_ni_editar_un_socio_de_otro_gimnasio(): void
    {
        $this->actingAs($this->userA)
            ->get(route('members.show', $this->memberB->id))
            ->assertNotFound();

        $this->actingAs($this->userA)
            ->get(route('members.edit', $this->memberB->id))
            ->assertNotFound();
    }

    public function test_no_se_puede_modificar_un_socio_de_otro_gimnasio(): void
    {
        $this->actingAs($this->userA)
            ->put(route('members.update', $this->memberB->id), [
                'first_name' => 'Hackeado',
                'last_name' => 'Hackeado',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('members', ['id' => $this->memberB->id, 'first_name' => 'Bruno']);
    }

    public function test_un_socio_nuevo_queda_siempre_en_el_gimnasio_del_usuario(): void
    {
        // Aunque alguien mande un gym_id ajeno en el formulario, se ignora
        $this->actingAs($this->userA)
            ->post(route('members.store'), [
                'first_name' => 'Nuevo',
                'last_name' => 'Socio',
                'gym_id' => $this->gymB->id,
            ]);

        $this->assertDatabaseHas('members', ['first_name' => 'Nuevo', 'gym_id' => $this->gymA->id]);
        $this->assertDatabaseMissing('members', ['first_name' => 'Nuevo', 'gym_id' => $this->gymB->id]);
    }

    public function test_las_consultas_con_usuario_solo_devuelven_su_gimnasio(): void
    {
        $this->actingAs($this->userA);

        $this->assertSame([$this->memberA->id], Member::pluck('id')->all());
        $this->assertNull(Member::find($this->memberB->id));
    }

    public function test_un_registro_creado_sin_gym_id_hereda_el_del_usuario(): void
    {
        $this->actingAs($this->userA);

        $member = Member::create(['first_name' => 'Auto', 'last_name' => 'Asignado', 'status' => 'active']);

        $this->assertSame($this->gymA->id, $member->gym_id);
    }

    public function test_un_registro_no_se_puede_mover_a_otro_gimnasio(): void
    {
        $this->expectException(LogicException::class);

        $this->memberA->update(['gym_id' => $this->gymB->id]);
    }

    public function test_el_mismo_documento_se_permite_en_gimnasios_distintos(): void
    {
        $this->makeMember($this->gymA, 'Doc', 'Uno', '30111222');
        $this->makeMember($this->gymB, 'Doc', 'Dos', '30111222');

        $this->assertSame(2, Member::withoutGlobalScopes()->where('document_number', '30111222')->count());
    }

    public function test_el_mismo_documento_no_se_repite_dentro_del_mismo_gimnasio(): void
    {
        $this->makeMember($this->gymA, 'Doc', 'Uno', '30111222');

        $this->expectException(QueryException::class);

        $this->makeMember($this->gymA, 'Doc', 'Repetido', '30111222');
    }

    public function test_varios_socios_sin_documento_conviven_en_el_mismo_gimnasio(): void
    {
        $this->makeMember($this->gymA, 'Sin', 'DocUno');
        $this->makeMember($this->gymA, 'Sin', 'DocDos');

        $this->assertSame(3, Member::withoutGlobalScopes()->where('gym_id', $this->gymA->id)->count());
    }

    private function makeOwner(Gym $gym, string $email): User
    {
        return User::create([
            'gym_id' => $gym->id,
            'name' => 'Dueño '.$gym->name,
            'email' => $email,
            'password' => 'secreto-123',
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);
    }

    private function makeMember(Gym $gym, string $first, string $last, ?string $document = null): Member
    {
        return Member::create([
            'gym_id' => $gym->id,
            'first_name' => $first,
            'last_name' => $last,
            'document_number' => $document,
            'status' => 'active',
            'joined_at' => today(),
        ]);
    }
}