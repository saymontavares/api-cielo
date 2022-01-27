<?php

namespace App\Http\Controllers;

use Cielo\API30\Merchant;
use Cielo\API30\Ecommerce\Environment;
use Cielo\API30\Ecommerce\CieloEcommerce;
use Cielo\API30\Ecommerce\Request\CieloRequestException;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private $merchant, $environment;

    public function __construct()
    {
        $this->merchant = new Merchant(config('cielo.merchant_id'), config('cielo.merchant_key'));
        $this->environment = app()->environment('production') ? Environment::production() : Environment::sandbox();
    }

    public function index(Request $request)
    {
        switch ($request->ChangeType) {
            case '1':
                try {
                    $sale = (new CieloEcommerce($this->merchant, $this->environment))->getSale($request->PaymentId);
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
                break;
        }
    }
}
