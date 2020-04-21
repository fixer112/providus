<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function providus()
    {
        ini_set("soap.wsdl_cache_enabled", "0");
        ini_set('default_socket_timeout', 5000);
        ini_set('user_agent', 'somerandomuseragent');

        $Client = new \SoapClient(env('PROVIDUS_URL'), [
            'trace' => true,
            'keep_alive' => true,
            'connection_timeout' => 50000,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
        ]);
        return $Client;

    }

    public function getAccount($acc_number, $bank_code)
    {
        $this->validate(request(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $result = $this->providus()->GetNIPAccount(['account_no' => $acc_number, 'bank_code' => $bank_code, 'username' => request()->username, 'password' => request()->password]);
        $result = json_decode($result->return, true);

        return $result;
    }

    public function getBanks()
    {
        $result = $this->providus()->GetNIPBanks();
        $result = json_decode($result->return, true);

        return $result;
    }

    public function transfer()
    {
        $this->validate(request(), [
            'username' => 'required|string',
            'password' => 'required|string',
            'amount' => 'required|numeric',
            'narration' => 'required|string',
            'transaction_reference' => 'required|string',
            'recipient_account_number' => 'required|string',
            'recipient_bank_code' => 'required|string',
            'account_name' => 'required|string',
            'originator_name' => 'required|string',
        ]);

        $data = [
            'currency' => 'NGN',
            'amount' => request()->amount,
            'narration' => request()->narration,
            'transaction_reference' => request()->transaction_reference,
            'recipient_account_number' => request()->recipient_account_number,
            'recipient_bank_code' => request()->recipient_bank_code,
            'account_name' => request()->account_name,
            'originator_name' => request()->originator_name,
            'username' => request()->username,
            'password' => request()->password,
        ];

        $result = $this->providus()->NIPFundTransfer($data);

        return json_decode($result->return, true);

    }
}