<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Estilizado</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e8e8e8;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            max-width: 580px;
            margin: 30px auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.075);
        }

        h1, p {
            margin: 5px 0;
        }

        .action-link {
            display: block;
            padding: 12px 25px;
            background-color: #3498db;
            color: #ffffff;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }

        .action-link:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <h1>Convite para Envio de Documentos</h1>
        <p>Caro(a) {{ $name }},</p>
        <p>Esperamos que esteja bem.</p>
        <p>Gostaríamos de lembrar sobre a necessidade de enviar os documentos para completar sua solicitação.</p>
        <p>Acesse o link abaixo para enviar os documentos: <a href="http://localhost:5174/adocoes/documentos/1" class="action-link">Enviar Documentos</a></p>
        <p>Documentos necessários:</p>
        <ul>
            <li>Identidade (RG)</li>
            <li>Cadastro de Pessoa Física (CPF)</li>
            <li>Comprovante de Endereço</li>
            <li>Contrato de Adoção Assinado</li>
        </ul>
        <p>Por favor, preencha o formulário e faça o upload dos documentos requisitados o quanto antes. Este é um passo crucial para dar continuidade ao processo.</p>
        <p>Estamos disponíveis para esclarecer qualquer dúvida e ajudar no que for necessário. Agradecemos sua cooperação e ficamos no aguardo dos documentos.</p>
        <p>Cordialmente,</p>
    </div>
</body>

</html>
