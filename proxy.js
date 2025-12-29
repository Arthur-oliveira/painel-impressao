const http = require('http');
const httpProxy = require('http-proxy');

const proxy = httpProxy.createProxyServer({});

const server = http.createServer((req, res) => {
    // Adiciona os headers de CORS manualmente para liberar o navegador
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'POST, GET, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

    // Se for uma requisição de "preflight" (OPTIONS), responde OK imediatamente
    if (req.method === 'OPTIONS') {
        res.writeHead(200);
        res.end();
        return;
    }

    // Redireciona para o seu Delphi na porta 9000
    proxy.web(req, res, { target: 'http://localhost:9000' }, (e) => {
        res.writeHead(500);
        res.end("Erro no Proxy: O Delphi está aberto na porta 9000?");
    });
});

console.log("Proxy rodando em http://localhost:8080 -> Direcionando para Delphi na 9000");
server.listen(8080);