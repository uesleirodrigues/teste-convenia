<!DOCTYPE html>
<html>
<head>
    <title>Falha na Importação de Colaboradores</title>
</head>
<body>
    <h1>Falha na Importação de Colaboradores</h1>
    <p>Houve um erro ao tentar importar o arquivo: <strong>{{ $fileName }}</strong>.</p>
    <p>Detalhes do erro:</p>
    <p style="color: red;">{{ $errorMessage }}</p>
    <p>Por favor, verifique o arquivo e tente novamente.</p>
    <p>Obrigado,</p>
    <p>{{ config('app.name') }}</p>
</body>
</html>