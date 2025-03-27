<?php

namespace App\Providers;

use Illuminate\Auth\AuthServiceProvider as ServiceProvider;
use App\Models\Model;
use App\Policies\ModelPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Model de policies
     *
     * @var array
     */
    protected $policies = [
        Model::class => ModelPolicy::class,
    ];

    /**
     * Registro de policies
     *
     * @return void
     */
    public function boot()
    {
        //$this->registerPolicies(); // Agora vai funcionar se houver políticas registradas
    }
}
