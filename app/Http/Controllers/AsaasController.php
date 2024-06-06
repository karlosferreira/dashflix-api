<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class AsaasController extends Controller
{
    public function criarQrCodePix(Request $request)
    {
        // Captura os dados do corpo da requisição
        $requestData = $request->only(['access_token', 'amount', 'description']);

        // Inicializa o cliente Guzzle
        $cliente = new Client();

        try {
            // Realiza a solicitação para gerar o QR Code PIX estático
            $response = $cliente->post('https://sandbox.asaas.com/api/v3/pix/qrCodes/static', [
                'headers' => [
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'access_token' => $requestData['access_token'], // Usa o access_token fornecido
                    'amount' => $requestData['amount'], // Valor variável do checkout
                    'description' => $requestData['description'],
                ],
            ]);

            // Decodifica a resposta JSON
            $data = json_decode($response->getBody()->getContents(), true);

            // Verifica se o QR Code foi gerado com sucesso
            if (isset($data['encodedImage'])) {
                $imageUrl = 'data:image/png;base64,' . $data['encodedImage'];

                // Retorna o QR Code gerado como URL de imagem
                return response()->json(['qr_code_url' => $imageUrl]);
            } else {
                // Retorna uma mensagem de erro se houver problemas ao gerar o QR Code
                return response()->json(['error' => 'Erro ao gerar o QR Code'], 400);
            }
        } catch (\Exception $e) {
            // Retorna uma mensagem de erro se ocorrer uma exceção
            return response()->json(['error' => 'Erro ao processar a solicitação'], 500);
        }
    }
}
