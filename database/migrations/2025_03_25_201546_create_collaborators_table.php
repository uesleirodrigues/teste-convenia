<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollaboratorsTable extends Migration
{
    public function up()
    {
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('cpf');
            $table->string('city');
            $table->string('state');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // FK - Relaciona ao usuário gestor
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('collaborators');
    }
}
