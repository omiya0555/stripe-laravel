<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;

class PaymentController extends Controller
{
    public function charge(Request $request){
        //StripeのAPIキーを設定
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try{
            //決済処理を実行
            $charge = Charge::create([
                'amount' => 5000, // 金額はセント単位なので、5000 = 50.00 USD
                'currency' => 'usd',
                'source' => $request->token, // 受け取ったトークンを使う
                'description' => 'Test Payment',
            ]);

            //成功した場合のレスポンス
            return response()->json(['success' => true]);
        }catch(\Exceptoin $e){
            //エラーが発生した場合のレスポンス
            return response()->json(['error' => $e->getMessage()]);
        }
    }
}
