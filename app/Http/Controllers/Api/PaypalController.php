<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Paypal;
use PayPal\Api\Currency;
use PayPal\Api\Payout;
use PayPal\Api\PayoutItem;
use PayPal\Api\PayoutSenderBatchHeader;

class PaypalController extends Controller
{

    private $_apiContext;

    public function __construct()
    {
        $this->_apiContext = PayPal::ApiContext(
            config('services.paypal.client_id'),
            config('services.paypal.secret'));

        $this->_apiContext->setConfig(array(

//            'mode' => 'live',                   ////////////    live mode for product version
//            'service.EndPoint' => 'https://api.paypal.com',          ////  api endpoint for live mode

            'mode' => 'sandbox',                ////  sandbox mode for test
            'service.EndPoint' => 'https://api.sandbox.paypal.com',    ///// api endpoint for sandbox mode
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'FINE'
        ));

    }

    public function payPremium()
    {
        return view('payPremium');
    }

    public function getCheckout(Request $request)
    {

        $payer = PayPal::Payer();
        $payer->setPaymentMethod('paypal');

        $amount = PayPal:: Amount();
        $amount->setCurrency('USD');
        $amount->setTotal($request->input('pay'));

        $transaction = PayPal::Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('Buy Premium '.$request->input('type').' Plan on '.$request->input('pay'));

        $redirectUrls = PayPal:: RedirectUrls();
        $redirectUrls->setReturnUrl(route('getDone'));
        $redirectUrls->setCancelUrl(route('getCancel'));

        $payment = PayPal::Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setRedirectUrls($redirectUrls);
        $payment->setTransactions(array($transaction));

        $response = $payment->create($this->_apiContext);
        $redirectUrl = $response->links[1]->href;

        return redirect()->to( $redirectUrl );
    }

    public function getDone(Request $request)
    {
        $id = $request->get('paymentId');
        $token = $request->get('token');
        $payer_id = $request->get('PayerID');

        $payment = PayPal::getById($id, $this->_apiContext);

        $paymentExecution = PayPal::PaymentExecution();

        $paymentExecution->setPayerId($payer_id);
        $executePayment = $payment->execute($paymentExecution, $this->_apiContext);

        print_r($executePayment);

        return ("<script>
                    window.opener.postMessage({type: 'buy_mins', success: true}, \"*\");
                    window.close();
                </script>");
    }

    public function getCancel()
    {
        return ("<script> 
                    window.close();
                </script>");

        //return redirect()->route('payPremium');
    }

    public function payout(Request $request)
    {

        $amount = $request->input('amount');
        $email = $request->input('email');

        $payout = new Payout();

        $senderBatchHeader = new PayoutSenderBatchHeader();

        $senderBatchHeader->setSenderBatchId(uniqid())->setEmailSubject('You have a Payout!');

        $senderItem = new PayoutItem();
        $senderItem->setRecipientType('Email')
            ->setNote('Thank you for your patronage!')
            ->setReceiver($email)
            ->setAmount(new Currency('{
                "value":"'.$amount.'",
                "currency":"USD"}'));

        $payout->setSenderBatchHeader($senderBatchHeader)->addItem($senderItem);

        $request = clone $payout;

        try {
            $output = $payout->create(null, $this->_apiContext);

            return response()->json([
                'success' => true,
            ]);

        } catch (Exception $ex) {


//            \ResultPrinter::printError("Created Single Synchronous Payout", "Payout", null, $request, $ex);
//            exit(1);

            return response()->json([
                'success' => false,
            ]);

        }


        //ResultPrinter::printResult("Created Single Synchronous Payout", "Payout", $output->getBatchHeader()->getPayoutBatchId(), $request, $output);


    }
}
