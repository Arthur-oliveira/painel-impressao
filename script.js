const API_URL = "http://127.0.0.1:9000/imprimir";

async function chamarApi(nomeDaImpressora) {
    const statusDiv = document.getElementById('status');
    statusDiv.innerText = `Enviando para ${nomeDaImpressora}...`;

    // Monta o JSON para a API
    const dados = {
        "impressora": {
            "caminho": `\\\\localhost\\${nomeDaImpressora}`,
            "model": 1
        },
        "pedido": {
            "cliente": "Joca",
            "pedido": Math.floor(Math.random() * 100000)
        },
        "produtos": [
            {
                "descricao": "PRODUTO TESTE",
                "quantidade": 1,
                "observacao": ["TESTE LOCAL"]
            }
        ]
    };

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });

        if (response.ok) {
            statusDiv.innerText = `Sucesso na ${nomeDaImpressora}!`;
            statusDiv.style.color = "green";
        } else {
            statusDiv.innerText = "Erro: API recusou o pedido.";
            statusDiv.style.color = "red";
        }
    } catch (error) {
        statusDiv.innerText = "Erro: API offline ou bloqueada.";
        statusDiv.style.color = "red";
    }
}

function imprimir(nome) {
    chamarApi(nome);
}

function imprimirAmbas() {
    chamarApi('ELGIN');
    setTimeout(() => {
        chamarApi('EPSON');
    }, 1500); // Espera 1.5s para não embolar a fila
}