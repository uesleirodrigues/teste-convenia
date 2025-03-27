<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Collaborator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CollaboratorControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Cria um usuário de teste
        $this->user = User::factory()->create();

        // Gera um token JWT para autenticação
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function it_can_create_a_collaborator()
    {
        $collaboratorData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'cpf' => $this->generateValidCPF(),
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/collaborators', $collaboratorData);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'cpf',
                    'city',
                    'state'
                ]
            ]);

        $this->assertDatabaseHas('collaborators', [
            'name' => $collaboratorData['name'],
            'email' => $collaboratorData['email'],
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function it_cannot_create_collaborator_with_invalid_data()
    {
        $invalidData = [
            'name' => '',
            'email' => 'invalid-email',
            'cpf' => '123', // CPF inválido
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/collaborators', $invalidData);

        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }

    /** @test */
    public function it_can_update_a_collaborator()
    {
        // Cria um colaborador para o usuário
        $collaborator = Collaborator::factory()->create([
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'cpf' => $this->generateValidCPF(),
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/collaborators/{$collaborator->id}", $updateData);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'cpf',
                    'city',
                    'state'
                ]
            ]);

        $this->assertDatabaseHas('collaborators', [
            'id' => $collaborator->id,
            'name' => $updateData['name'],
            'email' => $updateData['email']
        ]);
    }

    /** @test */
    public function it_can_list_collaborators()
    {
        // Cria alguns colaboradores para o usuário
        Collaborator::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/collaborators');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_delete_a_collaborator()
    {
        // Cria um colaborador para o usuário
        $collaborator = Collaborator::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/collaborators/{$collaborator->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('collaborators', ['id' => $collaborator->id]);
    }

    // Método utilitário para gerar CPF válido
    protected function generateValidCPF()
    {
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= mt_rand(0, 9);
        }

        // Calcula os dígitos verificadores
        $sum1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum1 += intval($cpf[$i]) * (10 - $i);
        }
        $digit1 = 11 - ($sum1 % 11);
        $digit1 = $digit1 > 9 ? 0 : $digit1;
        $cpf .= $digit1;

        $sum2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum2 += intval($cpf[$i]) * (11 - $i);
        }
        $digit2 = 11 - ($sum2 % 11);
        $digit2 = $digit2 > 9 ? 0 : $digit2;
        $cpf .= $digit2;

        return $cpf;
    }
}