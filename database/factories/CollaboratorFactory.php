<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Collaborator;
use Illuminate\Support\Str;

class CollaboratorFactory extends Factory
{
    protected $model = Collaborator::class;

    public function definition()
    {
        // Gera CPF válido
        $cpf = $this->generateValidCPF();

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'cpf' => $cpf,
            'city' => $this->faker->city,
            'state' => $this->faker->stateAbbr
        ];
    }

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