<?php
// ================================
// PROXY PHP (SUBSTITUI O proxy.js)
// ================================

// Liberação total de CORS (igual ao Node)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Resposta imediata para preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Se a rota for /print → age como proxy
if ($_SERVER['REQUEST_URI'] === '/print') {

    $json = file_get_contents("php://input");

    if (!$json) {
        http_response_code(400);
        echo "Nenhum dado recebido";
        exit;
    }

    error_log("[" . date('H:i:s') . "] Comando recebido -> Enviando ao Delphi");

    $ch = curl_init("http://localhost:9000");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_TIMEOUT => 3
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erro = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log("ERRO: O Delphi está fechado ou na porta errada (9000).");
        http_response_code(500);
        echo "Delphi Offline";
        exit;
    }

    http_response_code($httpCode ?: 200);
    echo $response;
    exit;
}

// ================================
// PAINEL HTML
// ================================
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Impressão LOCAL</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #1a1a1a;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: #252525;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 350px;
            text-align: center;
            border: 1px solid #444;
        }
        h2 { color: #00ff88; margin-bottom: 30px; }
        .btn {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
        }
        .btn-epson { background: #0058a9; color: white; }
        .btn-elgin { background: #f3c300; color: #1a1a1a; }
        .btn-ambas { background: #555; color: white; }
        #status {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            font-size: 13px;
            background: #000;
            color: #00ff88;
            border: 1px solid #333;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Impressão Local</h2>
    <button class="btn btn-epson" onclick="enviar('EPSON')">1. Imprimir na EPSON</button>
    <button class="btn btn-elgin" onclick="enviar('ELGIN')">2. Imprimir na ELGIN</button>
    <button class="btn btn-ambas" onclick="ambas()">3. Imprimir em AMBAS</button>
    <div id="status">Status: Pronto</div>
</div>

<script>
const URL_PROXY = 'http://localhost:9000/print';

async function enviar(impressora) {
    const status = document.getElementById('status');
    status.innerText = `Enviando para ${impressora}...`;

    const dados = {
        impressora: { caminho: impressora, model: 1 },
        pedido: { cliente: "TESTE LOCAL", pedido: 123 },
        produtos: [
            { descricao: "ITEM TESTE", quantidade: 1, observacao: ["TESTE LOCAL"] }
        ]
    };

    try {
        const r = await fetch(URL_PROXY, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });

        if (r.ok) {
            status.innerText = `✅ SUCESSO: ${impressora}`;
        } else {
            status.innerText = `❌ ERRO: ${r.status}`;
        }
    } catch (e) {
        status.innerText = "❌ Delphi Offline";
    }
}

async function ambas() {
    await enviar('EPSON');
    setTimeout(() => enviar('ELGIN'), 1000);
}
</script>

</body>
</html>
