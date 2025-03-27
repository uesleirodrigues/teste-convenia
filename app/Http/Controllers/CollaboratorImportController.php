<?php

namespace App\Http\Controllers;

use App\Imports\CollaboratorImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollaboratorImportController extends Controller
{
    public function import(Request $request)
    {
        // Validação do arquivo
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        // Processando o arquivo CSV
        Excel::import(new CollaboratorImport, $request->file('file'));

        // Notificando o usuário após a importação
        \Mail::to(Auth::user()->email)->send(new \App\Mail\ImportNotificationMail());

        return back()->with('success', 'Colaboradores importados com sucesso!');
    }
}
