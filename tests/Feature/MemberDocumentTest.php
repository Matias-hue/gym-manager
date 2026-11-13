<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Documento (DNI) de los socios: opcional, único por gimnasio y sin notas médicas.
 */
class MemberDocumentTest extends TestCase
{
    use RefreshDatabase;

    private Gym $gymA;

    private Gym $gymB;

    private User $userA;

    private Member $memberA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
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

        $this->memberA = $this->makeMember($this->gymA, 'Alicia', 'DeAlfa', '30111222');
        $this->makeMember($this->gymB, 'Bruno', 'DeBeta', '40999888');
    }

    protected function tearDown(): void
    {
        Model::reguard();

        parent::tearDown();
    }

    public function test_un_documento_repetido_en_el_mismo_gimnasio_se_rechaza_con_mensaje(): void
    {
        $this->actingAs($this->userA)
            ->post(route('members.store'), [
                'first_name' => 'Otro',
                'last_name' => 'Socio',
                'document_number' => '30111222',
            ])
            ->assertSessionHasErrors('document_number');

        $this->assertDatabaseMissing('members', ['first_name' => 'Otro']);
    }

    public function test_el_documento_con_puntos_se_normaliza_y_tambien_se_rechaza(): void
    {
        $this->actingAs($this->userA)
            ->post(route('members.store'), [
                'first_name' => 'Otro',
                'last_name' => 'Socio',
                'document_number' => '30.111.222',
            ])
            ->assertSessionHasErrors('document_number');
    }

    public function test_el_documento_de_otro_gimnasio_no_molesta(): void
    {
        $this->actingAs($this->userA)
            ->post(route('members.store'), [
                'first_name' => 'Otro',
                'last_name' => 'Socio',
                'document_number' => '40999888',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('members', [
            'first_name' => 'Otro',
            'gym_id' => $this->gymA->id,
            'document_number' => '40999888',
        ]);
    }

    public function test_un_socio_puede_guardar_su_propio_documento_al_editarse(): void
    {
        $this->actingAs($this->userA)
            ->put(route('members.update', $this->memberA->id), [
                'first_name' => 'Alicia',
                'last_name' => 'DeAlfa',
                'document_number' => '30111222',
                'status' => 'active',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_un_documento_vacio_se_guarda_como_sin_documento(): void
    {
        foreach (['Sin', 'Otro'] as $name) {
            $this->actingAs($this->userA)
                ->post(route('members.store'), [
                    'first_name' => $name,
                    'last_name' => 'Documento',
                    'document_number' => '',
                ])
                ->assertSessionHasNoErrors();

            $this->assertDatabaseHas('members', ['first_name' => $name, 'document_number' => null]);
        }
    }

    public function test_las_notas_medicas_enviadas_no_se_guardan(): void
    {
        $this->actingAs($this->userA)
            ->post(route('members.store'), [
                'first_name' => 'Con',
                'last_name' => 'Notas',
                'medical_notes' => 'Dato de salud que no debe guardarse',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('members', ['first_name' => 'Con', 'medical_notes' => null]);
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