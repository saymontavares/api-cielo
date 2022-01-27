<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Cielo\API30\Ecommerce\Sale;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Payment;
use Cielo\API30\Ecommerce\CreditCard;
use Illuminate\Http\Request;
use App\Pagamento;

use Cielo\API30\Ecommerce\Request\CieloRequestException;

class CardController extends Controller
{
    private $validate;

    public function __construct()
    {
        $this->validate = [
            'i_empresa' => 'required|integer',
            'ref' => 'required|string',
            'nome' => 'required|string',
            'valor' => 'required|numeric',
            'cartao' => 'required|array',
            'cartao.numero' => 'required|numeric',
            'cartao.validade' => 'required|date_format:m/Y',
            'cartao.cvv' => 'required|digits:3',
            'cartao.nome' => 'required|string',
        ];
    }

    public function CreditCard(Request $request)
    {
        $validate = $this->validate;
        $validate['parcelas'] = 'required|integer';
        // $validate['cartao.bandeira'] = 'required|in:visa,mastercard,amex,elo,diners,discover,jcb,aura';
        $this->validate($request, $validate);

        // check bin
        $cli = new Client(['http_errors' => false]);
        $bin = substr($request->cartao['numero'], 0, 9);
        $res = $cli->request('GET', config('cielo.url_bin')."/{$bin}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'MerchantId' => env('MERCHANT_ID'),
                'MerchantKey' => env('MERCHANT_KEY')
            ]
        ]);

        if ($res->getStatusCode() != 200) {
            $card = json_decode($res->getBody());
            return response()->json($card[0], 400);
        }
        $card = json_decode($res->getBody());
        $brand = strtolower($card->Provider);

        switch ($brand) {
            case 'visa':
                $bandeira = CreditCard::VISA;
                break;
            case 'mastercard':
                $bandeira = CreditCard::MASTERCARD;
                break;
            case 'amex':
                $bandeira = CreditCard::AMEX;
                break;
            case 'elo':
                $bandeira = CreditCard::ELO;
                break;
            case 'diners':
                $bandeira = CreditCard::DINERS;
                break;
            case 'discover':
                $bandeira = CreditCard::DISCOVER;
                break;
            case 'jcb':
                $bandeira = CreditCard::JCB;
                break;
            case 'aura':
                $bandeira = CreditCard::AURA;
                break;
        }

        $valor = intval(100 * $request->valor);
        $sale = new Sale($request->ref);
        $customer = $sale->customer($request->nome);
        $payment = $sale->payment($valor, $request->parcelas);
        $payment->setCapture(true)
                ->setType(Payment::PAYMENTTYPE_CREDITCARD)
                ->creditCard($request->cartao['cvv'], $bandeira)
                ->setExpirationDate($request->cartao['validade'])
                ->setCardNumber($request->cartao['numero'])
                ->setHolder($request->cartao['nome']);

        $pagamento = new Pagamento;
        $pagamento->i_empresa = $request->i_empresa;
        $pagamento->nome = strtoupper($request->nome);
        $pagamento->valor = $request->valor;
        $pagamento->parcelas = $request->parcelas;
        $pagamento->usuario = 'automatico';
        $pagamento->dt_sistema = date('Y-m-d H:i:s');
        $pagamento->adquirente = 'C';

        try {
            $sale = (new CieloEcommerce(config('cielo.merchant'), config('cielo.environment')))->createSale($sale);
            $pagamento->tid = $sale->getPayment()->getPaymentId();
            $pagamento->codret = $sale->getPayment()->getStatus() == 1 ? '00' : $sale->getPayment()->getReturnCode();
            $pagamento->mensagemret = $sale->getPayment()->getReturnMessage();
            $pagamento->tipo = 'C';
            $pagamento->save();

            $pagamento->tidint = $sale->getPayment()->getTid();

            return response()->json($pagamento, $sale->getPayment()->getStatus() == 1 || $sale->getPayment()->getStatus() == 2 ? 200 : 400);
        } catch (CieloRequestException $e) {
            $error = $e->getCieloError();
            $pagamento->tid = $sale->getPayment()->getPaymentId();
            $pagamento->codret = $error->getCode();
            $pagamento->mensagemret = $error->getMessage();
            $pagamento->tipo = 'C';
            $pagamento->save();

            $pagamento->tidint = $sale->getPayment()->getTid();

            return response()->json($pagamento, 400);
        }
    }

    public function DebitCard(Request $request)
    {
        $validate = $this->validate;
        // $validate['cartao.bandeira'] = 'required|in:visa,mastercard';
        $this->validate($request, $validate);

        // check bin
        $cli = new Client(['http_errors' => false]);
        $bin = substr($request->cartao['numero'], 0, 9);
        $res = $cli->request('GET', config('cielo.url_bin')."/{$bin}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'MerchantId' => env('MERCHANT_ID'),
                'MerchantKey' => env('MERCHANT_KEY')
            ]
        ]);

        if ($res->getStatusCode() != 200) {
            $card = json_decode($res->getBody());
            return response()->json($card[0], 400);
        }
        $card = json_decode($res->getBody());
        $brand = strtolower($card->Provider);

        switch ($brand) {
            case 'visa':
                $bandeira = CreditCard::VISA;
                break;
            case 'mastercard':
                $bandeira = CreditCard::MASTERCARD;
                break;
        }

        $valor = intval(100 * $request->valor);
        $sale = new Sale($request->ref);
        $customer = $sale->customer($request->nome);
        $payment = $sale->payment($valor);
        $payment->setReturnUrl(env('APP_URL'));
        $payment->setCapture(true)
                ->debitCard($request->cartao['cvv'], $bandeira)
                ->setExpirationDate($request->cartao['validade'])
                ->setCardNumber($request->cartao['numero'])
                ->setHolder($request->cartao['nome']);

        $pagamento = new Pagamento;
        $pagamento->i_empresa = $request->i_empresa;
        $pagamento->nome = strtoupper($request->nome);
        $pagamento->valor = $request->valor;
        $pagamento->parcelas = 0;
        $pagamento->usuario = 'automatico';
        $pagamento->dt_sistema = date('Y-m-d H:i:s');
        $pagamento->adquirente = 'C';

        try {
            $sale = (new CieloEcommerce(config('cielo.merchant'), config('cielo.environment')))->createSale($sale);
            $pagamento->tid = $sale->getPayment()->getPaymentId();
            $pagamento->codret = $sale->getPayment()->getStatus() == 1 ? '00' : $sale->getPayment()->getReturnCode();
            $pagamento->mensagemret = $sale->getPayment()->getReturnMessage();
            $pagamento->tipo = 'D';
            $pagamento->save();

            $pagamento->authenticationUrl = $sale->getPayment()->getAuthenticationUrl();

            $pagamento->tidint = $sale->getPayment()->getTid();

            return response()->json($pagamento, $sale->getPayment()->getStatus() == 1 || $sale->getPayment()->getStatus() == 2 ? 200 : 400);

        } catch (CieloRequestException $e) {
            $error = $e->getCieloError();
            $pagamento->tid = $sale->getPayment()->getPaymentId();
            $pagamento->codret = $error->getCode();
            $pagamento->mensagemret = $error->getMessage();
            $pagamento->tipo = 'D';
            $pagamento->save();

            $pagamento->tidint = $sale->getPayment()->getTid();

            return response()->json($pagamento, 400);
        }
    }

    public function getSale(Request $request)
    {
        $this->validate($request, [
            'paymentid' => 'required'
        ]);

        try {
            $sale = (new CieloEcommerce(config('cielo.merchant'), config('cielo.environment')))->getSale($request->paymentid);
            return response()->json($sale);
        } catch(CieloRequestException $e){
            $erro = $e->getCieloError();
            if (!empty($erro)) {
                return response()->json([
                    'code' => $erro->getCode(),
                    'message' => $erro->getMessage()
                ], 400);
            }
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
