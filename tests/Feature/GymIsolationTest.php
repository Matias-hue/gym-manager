<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GymIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_gimnasio_no_ve_los_socios_de_otro(): void
    {
        $gymA = Gym::create(['name' => 'A']);
        $gymB = Gym::create(['name' => 'B']);

        Member::create(['gym_id' => $gymA->id, 'first_name' => 'Ana', 'last_name' => 'A']);
        $socioB = Member::create(['gym_id' => $gymB->id, 'first_name' => 'Beto', 'last_name' => 'B']);

        $usuarioA = User::factory()->create(['gym_id' => $gymA->id, 'role' => 'owner']);

        $this->actingAs($usuarioA);

        $this->assertSame(1, Member::count());
        $this->get(route('members.show', $socioB->id))->assertNotFound();
    }
}