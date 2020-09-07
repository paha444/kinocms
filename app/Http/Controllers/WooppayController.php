<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/*
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\Model;

use App\Offers;

use App\Files;

use App\Message;

use App\Pages;


use Illuminate\Support\Facades\Auth;

use DB;
*/

use App\User;

use DB;

use SoapClient;

use Illuminate\Support\Facades\Log;


class WooppaySoapClient
{

	private $c;

	public function __construct($url, $options = array())
	{
		try {
			$this->c = new SoapClient($url, $options);
		} catch (Exception $e) {
			throw new WooppaySoapException($e->getMessage());
		}
		if (empty($this->c)) {
			throw new WooppaySoapException('Cannot create instance of Soap client');
		}
	}

	/**
	 * @param $method
	 * @param $data
	 * @return WooppaySoapResponse
	 * @throws BadCredentialsException
	 * @throws UnsuccessfulResponseException
	 * @throws WooppaySoapException
	 */
	public function __call($method, $data)
	{
		try {
		    //print_r($data);  

			$response = $this->c->$method($data[0]);
		} catch (Exception $e) {
			throw new WooppaySoapException($e->getMessage());
		}
		$response = new WooppaySoapResponse($response);
        //print_r($response);
		switch ($response->error_code) {
			case 0:
				return $response;
				break;
			case 5:
				throw new BadCredentialsException();
				break;
			default:
				//throw new UnsuccessfulResponseException('Error code ' . $response->error_code);
                echo 'Error code ' . $response->error_code;
		}

	}

	public function login(CoreLoginRequest $data)
	{
		$response = $this->core_login($data);
        
        //print_r($response);
        
		if (isset($response->response->session)) {
			$this->c->__setCookie('session', $response->response->session);
			return true;
		} else {
			return false;
		}
	}

	public function getOperationData(CashGetOperationDataRequest $data)
	{
		return $this->cash_getOperationData($data);
	}

	public function createInvoice(CashCreateInvoiceByServiceRequest $data)
	{
		return $this->cash_createInvoiceByService($data);
	}


	public function createOperation(CashCreateOperationRequest $data)
	{
		return $this->cash_createOperation($data);
	}

	public function confirmOperation(CashConfirmOperationRequest $data)
	{
		return $this->cash_confirmOperation($data);
	}


	public function cashOut(CashCashOutRequest $data)
	{
		return $this->cash_cashOut($data);
	}


	public function cashOutComplete(cashCashOutCompleteRequest $data)
	{
		return $this->cash_cashOutComplete($data);
	}







	public function getLastDialog()
	{
		return array('req' => $this->c->__getLastRequest(), 'res' => $this->c->__getLastResponse());
	}
}


class CoreLoginRequest
{
	/**
	 * @var string $username
	 * @soap
	 */
	public $username;
	/**
	 * @var string $password
	 * @soap
	 */
	public $password;
	/**
	 * @var string $captcha
	 * @soap
	 */
	public $captcha = null;
}

class CashGetOperationDataRequest
{
	/**
	 * @var $operationId array
	 */
	public $operationId;

}

//////////// выплата на кошель



class CashConfirmOperationRequest
{

	public $operationId;
	public $type;

}



class CashCreateOperationRequest
{
	public $type;
	public $receiver;
	public $amount;
	public $description;
	public $service;

	public $fields;
	public $productId;
	public $userGroup;


}

/////////////////////////////////// выплата на карту


/*
class cashGetOperationDataRequest 
{
	public $operationId;    
}
*/


class cashCashOutCompleteRequest
{
	public $operationID;
}


class CashCashOutRequest
{
	public $amount;
	public $returnURL;
	public $postLink;
	public $extID;
	public $phone;
	public $addParams;


}


////////////////////////////////////

class CashCreateInvoiceRequest
{
	/**
	 * @var string $referenceId
	 * @soap
	 */
	public $referenceId;
	/**
	 * @var string $backUrl
	 * @soap
	 */
	public $backUrl;
	/**
	 * @var string $requestUrl
	 * @soap
	 */
	public $requestUrl = '';
	/**
	 * @var string $addInfo
	 * @soap
	 */
	public $addInfo;
	/**
	 * @var float $amount
	 * @soap
	 */
	public $amount;
	/**
	 * @var string $deathDate
	 * @soap
	 */
	public $deathDate;
	/**
	 * @var int $serviceType
	 * @soap
	 */
	public $serviceType = null;
	/**
	 * @var string $description
	 * @soap
	 */
	public $description = '';
	/**
	 * @var int $orderNumber
	 * @soap
	 */
	public $orderNumber = null;
	/**
	 * @var string $userEmail
	 * @soap
	 */
	public $userEmail = null;
	/**
	 * @var string $userPhone
	 * @soap
	 */
	public $userPhone = null;
}

class CashCreateInvoiceExtendedRequest extends CashCreateInvoiceRequest
{
	/**
	 * @var string $userEmail
	 * @soap
	 */
	public $userEmail = '';
	/**
	 * @var string $userPhone
	 * @soap
	 */
	public $userPhone = '';
}

class CashCreateInvoiceExtended2Request extends CashCreateInvoiceExtendedRequest
{
	/**
	 * @var int $cardForbidden
	 * @soap
	 */
	public $cardForbidden;
}

class CashCreateInvoiceByServiceRequest extends CashCreateInvoiceExtended2Request
{
	/**
	 * @var string $serviceName
	 * @soap
	 */
	public $serviceName;
}

class WooppaySoapResponse
{

	public $error_code;
	public $response;

	public function __construct($response)
	{
        
        //print_r($response);
        
		if (!is_object($response)) {
			//new BadResponseException('Response is not an object');
		    //echo 1;
        }

		if (!isset($response->error_code)) {
			//new BadResponseException('Response do not contains error code');
            //echo 2;
		}
		$this->error_code = $response->error_code;

		if (!property_exists($response, 'response')) {
			//new BadResponseException('Response do not contains response body');
            //echo 3;
		}
        
        if (is_object($response) && isset($response->response)) {
		  $this->response = $response->response;
        }else{
		  $this->response = '';
        }
	}
}

class WooppayOperationStatus
{
	/**
	 * Новая
	 */
	const OPERATION_STATUS_NEW = 1;
	/**
	 * На рассмотрении
	 */
	const OPERATION_STATUS_CONSIDER = 2;
	/**
	 * Отклонена
	 */
	const OPERATION_STATUS_REJECTED = 3;
	/**
	 * Проведена
	 */
	const OPERATION_STATUS_DONE = 4;
	/**
	 * Сторнирована
	 */
	const OPERATION_STATUS_CANCELED = 5;
	/**
	 * Сторнирующая
	 */
	const OPERATION_STATUS_CANCELING = 6;
	/**
	 * Удалена
	 */
	const OPERATION_STATUS_DELETED = 7;
	/**
	 * На квитовании
	 */
	const OPERATION_STATUS_KVITOVANIE = 4;
	/**
	 * На ожидании подверждения или отказа мерчанта
	 */
	const OPERATION_STATUS_WAITING = 9;
}


class WooppaySoapException
{
}

class BadResponseException extends WooppaySoapException
{
    
    //public $message;
    
   function __construct($message) {
       
       print "$message\n";
   }    
    
    
}

class UnsuccessfulResponseException extends WooppaySoapException
{
}

class BadCredentialsException extends UnsuccessfulResponseException
{
}






class WooppayController extends Controller
{

    private function login()
    {
        	
            $client = new WooppaySoapClient('https://www.test.wooppay.com/api/wsdl');
        	$login_request = new CoreLoginRequest();
        	$login_request->username = '2test_merchant';
        	$login_request->password = 'A12345678a';
        	return $client->login($login_request) ? $client : false;
            


    }
 
 
    public function callback_admin(Request $req,$order,$key){ 


/*        $file = $_SERVER['DOCUMENT_ROOT'].'/data_order_key.txt';
        file_put_contents($file, $order.$key);    
        
        //$file = $_SERVER['DOCUMENT_ROOT'].'/data_admin.txt';
        //file_put_contents($file, $data);    



 
        $data = json_decode($req->getContent(), true);
        
        $file = $_SERVER['DOCUMENT_ROOT'].'/data_admin.txt';
        file_put_contents($file, $data);    
*/
        $file = $_SERVER['DOCUMENT_ROOT'].'/data_admin.txt';
        file_put_contents($file, 'ok111');    
		
        
        if ($req->key == md5($req->order.'jLbjQ1E0P4')) {

        $file = $_SERVER['DOCUMENT_ROOT'].'/data_admin2.txt';
        file_put_contents($file, 'ok222');    

 
			try {
				if ($client = $this->login()) {

                    $payment = DB::table('users_payments')
                        ->where('users_payments.id', $req->order)
                        ->first();

					$operationId = $payment->operationId;
					if ($operationId) {
						$operationdata_request = new CashGetOperationDataRequest();
						$operationdata_request->operationId = array($payment->operationId);
						$operation_data = $client->getOperationData($operationdata_request);
						if (!isset($operation_data->response->records[0]->status) || empty($operation_data->response->records[0]->status)) {
							exit;
						}

						if ($operation_data->response->records[0]->status == WooppayOperationStatus::OPERATION_STATUS_DONE) {

                                    DB::table('users_payments')
                                    ->where('id', $req->order)
                                    ->update(['status' => 1]);  

						    
                        } else
							Log::info(sprintf('Wooppay выплата : счет не оплачен (%s) order id (%s)', $operation_data->response->records[0]->status, $req->order));
					} else
							Log::info(sprintf('Wooppay выплата not found : %s order id (%s)', $operation_data->response->records[0]->status, $req->order));
				}
			} catch (Exception $e) {
				Log::info(sprintf('Wooppay exception : %s выплата id (%s)', $operation_data->response->records[0]->status, $req->order));
			}
             
 
        }
 
 
    }
 
    
    public function callback(Request $req,$order,$key){
    
        $data = json_decode($req->getContent(), true);
        
        $file = $_SERVER['DOCUMENT_ROOT'].'/data.txt';
        file_put_contents($file, $data);    

		if ($req->key == md5($req->order)) {
		  
        $file = $_SERVER['DOCUMENT_ROOT'].'/data2.txt';
        file_put_contents($file, 'ok');    
          
          
          
			try {
				if ($client = $this->login()) {

                    $payment = DB::table('users_replenishment')
                        ->where('users_replenishment.id', $id)
                        //->where('users_replenishment.to_user_id', $user->id)
                        ->where('users_replenishment.status','!=', 1)
                        ->first();

					//$operationId = $payment->operationId;
					if (isset($payment->operationId)) {
						$operationdata_request = new CashGetOperationDataRequest();
						$operationdata_request->operationId = array($payment->operationId);
						$operation_data = $client->getOperationData($operationdata_request);
						if (!isset($operation_data->response->records[0]->status) || empty($operation_data->response->records[0]->status)) {
							exit;
						}

						if ($operation_data->response->records[0]->status == WooppayOperationStatus::OPERATION_STATUS_DONE) {

                                    DB::table('users_replenishment')
                                    ->where('id', $payment->id)
                                    ->update(['status' => 1]);  
                                    
                                    $get_user = User::find($payment->to_user_id);
                                    
                                    DB::table('users')
                                    ->where('id', $get_user->id)
                                    ->update(['amount'=>($get_user->amount + $payment->summ)]);                 


						    
                        } else
							Log::info(sprintf('Wooppay пополнение : счет не оплачен (%s) order id (%s)', $operation_data->response->records[0]->status, $payment->id));
					} //else
						//	Log::info(sprintf('Wooppay пополнение not found : %s order id (%s)', $payment->summ, $payment->id));
                
                }
			} catch (Exception $e) {
				Log::info(sprintf('Wooppay exception : %s пополнение id (%s)', $operation_data->response->records[0]->status, $req->order));
			}          
          
          
          
          
/*			try {
				if ($client = $this->login()) {
					$this->load->model('extension/payment/wooppay');
					$operationId = $this->model_extension_payment_wooppay->getTransactionRow($this->request->get['order']);
					if ($operationId) {
						$operationdata_request = new CashGetOperationDataRequest();
						$operationdata_request->operationId = array($operationId['wooppay_transaction_id']);
						$operation_data = $client->getOperationData($operationdata_request);
						if (!isset($operation_data->response->records[0]->status) || empty($operation_data->response->records[0]->status)) {
							exit;
						}

						if ($operation_data->response->records[0]->status == WooppayOperationStatus::OPERATION_STATUS_DONE) {
							$this->load->model('checkout/order');
							$this->model_checkout_order->addOrderHistory($this->request->get['order'], $this->config->get('payment_wooppay_order_success_status_id'));
						} else
							$this->log->write(sprintf('Wooppay callback : счет не оплачен (%s) order id (%s)', $operation_data->response->records[0]->status, $this->request->get['order']));
					} else
						$this->log->write(sprintf('Wooppay order not found : %s order id (%s)', $this->request->get['order'], $this->request->get['order']));
				}
			} catch (Exception $e) {
				$this->log->write(sprintf('Wooppay exception : %s order id (%s)', $e->getMessage(), $this->request->get['order']));
			}*/
		} //else
		//	$this->log->write('Wooppay callback : неверный key или order : ' . print_r($_REQUEST, true));
		//echo json_encode(['data' => 1]);        
        
        
    }
    
    public function success(Request $req){
        
    }
    
}