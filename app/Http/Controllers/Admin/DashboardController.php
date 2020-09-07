<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Image;

use Illuminate\Database\Eloquent\Model;

use App\Offers;
use App\Blog;

use App\User;

use Illuminate\Support\Facades\Hash;

use App\Models\Country;
use App\Models\Region;

use Illuminate\Support\Facades\Auth;

use DB;

use File;

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



class DashboardController extends Controller
{
    //


      //  public function login(){
     //       $this->middleware('guest')->except('logout');
     //   }


/*    public function index(Request $request)
    {

        $result = $request->session()->all();//получаем данные из сессии
	   $token = $result['_token'];
        return view('index',['token'=>$token]); //передаём данные в шаблон
    }                                                                                 

*/


        
        private function login()
        {
        	
            $client = new WooppaySoapClient('https://www.test.wooppay.com/api/wsdl');
        	$login_request = new CoreLoginRequest();
        	$login_request->username = 'Agent3';
        	$login_request->password = 'A12345678a';
        	return $client->login($login_request) ? $client : false;
            

/*
            $client = new WooppaySoapClient('https://www.test.wooppay.com/api/wsdl');
        	$login_request = new CoreLoginRequest();
        	$login_request->username = '2test_merchant';
        	$login_request->password = 'A12345678a';
        	return $client->login($login_request) ? $client : false;

*/
/*            
            $client = new WooppaySoapClient('https://www.wooppay.com/api/wsdl');
        	$login_request = new CoreLoginRequest();
        	$login_request->username = 'gazety';
        	$login_request->password = 'ob95NcxqLg';
        	return $client->login($login_request) ? $client : false;

*/

        }



         public function payment ($id,$operation_data=''){
            
            //echo '<a href="https://femida24.kz/payment/wooppay/callback_admin/' . $id . '/' . md5($id).'">callback_admin</a>';
            
            
            $payment = DB::table('users_payments')
                        ->where('users_payments.id', $id)
                        ->leftJoin('users','users.id', '=', 'users_payments.from_user_id')
                        ->select('users_payments.*','users.email as user_email','users.phone as user_phone','users.card as user_card','users.wooppay as user_wooppay')
                        ->first();
            
/*            
            if(isset($payment->operationId)){
            
        		try {
        			if ($client = $this->login()) {
                        
                          $operation_request = new CashGetOperationDataRequest();
                          $operation_request->operationId=$payment->operationId;
    
                          //$confirm_request->type=1;
                          //print_r($confirm_request);
    
                          $operation_data = $client->getOperationData($operation_request);   
                          
                          //echo 111;
                          print_r($operation_data);                   
                
                
        			}else{
        			 
                     echo 'no connect woopay'; //die;
                     
        			}
        		} catch (Exception $e) {
        			echo 'error';//sprintf('Wooppay exception : %s order id (%s)', $e->getMessage(), $this->request->get['order']);
        		}  


            }
*/
            
            return view('admin.payment', ['payment' => $payment,'operation_data'=>$operation_data]); 



         }

/////////////////////////////////// Подтверждение платежа
        
         public function payment_confirm($id){
            

            $payment = DB::table('users_payments')
                        ->where('users_payments.id', $id)
                        ->leftJoin('users','users.id', '=', 'users_payments.from_user_id')
                        ->select('users_payments.*','users.email as user_email')
                        ->first();
            
            
            //echo $id;
            
    		try {
    			if ($client = $this->login()) {
                    

                        switch ($payment->payment_system) {
                        /////////////////////////////////// Оплата на карту     
                        case 'card': 
                                            
                                              $confirm_request = new cashCashOutCompleteRequest();
                                              $confirm_request->operationID=$payment->operationId;
                        
                                              //$confirm_request->type=1;
                                              //print_r($confirm_request);
                        
                                              $confirm_data = $client->cashOutComplete($confirm_request);   
                                              
                                              //print_r($confirm_data);
                                              
                                              //die;
                                              
                                              if(isset($confirm_data->error_code) && $confirm_data->error_code==0){

                                                    DB::table('users_payments')
                                                    ->where('id', $id)
                                                    ->update(['status' => 3]);
                                                    
                                                    //$this->payment_check($id);
                                                      

                                              }                                              
                                              
                                                                 
                        
                        break;
                        /////////////////////////////////// Кошелек wooppay
                        case 'wooppay':
                        
                                              $confirm_request = new CashConfirmOperationRequest();
                                              
                                              $confirm_request->operationId=$payment->operationId;
                                              $confirm_request->type=1;
                                              
                                              $confirm_data = $client->confirmOperation($confirm_request);  
                                              //print_r($confirm_data);  
                                              if(isset($confirm_data->error_code) && $confirm_data->error_code==0){

                                                    DB::table('users_payments')
                                                    ->where('id', $id)
                                                    ->update(['status' => 3]);
                                                    
                                                    //$this->payment_check($id);  

                                              }
                        
                        break;
                        }  
            
            
    			}else{
    			 
                 echo 'no connect woopay';// die;
                 
    			}
    		} catch (Exception $e) {
    			echo 'error';//sprintf('Wooppay exception : %s order id (%s)', $e->getMessage(), $this->request->get['order']);
    		}             
            
            
            return redirect()->route('payment',$id);
            
            
            
            
            
            
            
            
            //////////////////return view('admin.payment', ['payment' => $payment]); 
            
         }


 
 
         public function payment_back_from_iframe ($id){


                //if(isset($invoice_data->response->operationId)){

                    DB::table('users_payments')
                    ->where('id', $id)
                    ->update([
                    'status' => 2
                    ]);  
                    
                //}

                    return redirect()->route('payment',$id);
         }         
        


    	public function payment_check($id)
    	{  
            $user = Auth::user();
    
            $order_info = array();
            //$order_info['order_id']=$payment->id;
            $order_info['email']='test@mail.ru';
            $order_info['telephone']='77765431515';
            
            //echo 1;
            //$data ='';
            
    		try {
    			if ($client = $this->login()) {
    			 
                 //echo 2;
                 
                        $payment = DB::table('users_payments')
                            ->where('users_payments.id', $id)
                            //->where('users_replenishment.to_user_id', $user->id)
                            ->where('users_payments.status','!=', 1)
                            ->first();
                        
                        //print_r($payment);
                        
    					//$operationId = $payment->operationId;
    					if (isset($payment->operationId)) {
    						$operationdata_request = new CashGetOperationDataRequest();
    						$operationdata_request->operationId = array($payment->operationId);
    						$operation_data = $client->getOperationData($operationdata_request);
                            
                            //print_r($operation_data); die;
                            
    						if (!isset($operation_data->response->records[0]->status) || empty($operation_data->response->records[0]->status)) {
    							exit;
    						}
    
    						if ($operation_data->response->records[0]->status == WooppayOperationStatus::OPERATION_STATUS_DONE) {
    
                                    DB::table('users_payments')
                                    ->where('id', $payment->id)
                                    ->update(['status' => 1]);  
    
    
    						    
                            }else{
    							Log::info(sprintf('Wooppay выплата : счет не оплачен (%s) order id (%s)', $operation_data->response->records[0]->status, $id));
    					       
                                //$data=$operation_data;
                                
                                return $this->payment($id,$operation_data);
                                    
                                //return view('admin.payment', ['payment' => $payment,'operation_data'=>$operation_data]);
                            }
                    
                    } else
							Log::info(sprintf('Wooppay выплата not found : %s order id (%s)', $id , $id));
				}
			} catch (Exception $e) {
				Log::info(sprintf('Wooppay exception : %s выплата id (%s)', 0, $id));
			}
    		
            //$this->response->redirect($this->url->link('checkout/failure', '', 'SSL'));
            
            
    	    
            return redirect()->route('payment',$id);
        }




/////////////////////////////////// Создание платежа
        
         public function payment_create($id){
            
            

            $payment = DB::table('users_payments')
                        ->where('users_payments.id', $id)
                        ->leftJoin('users','users.id', '=', 'users_payments.from_user_id')
                        ->select('users_payments.*','users.email as user_email','users.phone as user_phone','users.card as user_card','users.wooppay as user_wooppay')
                        ->first();
            
            
            $invoice_data = [];
            
            //$id=1000;
            
            //print_r($payment); 

            //die;

    		try {
    			if ($client = $this->login()) {
    			 
                    switch ($payment->payment_system) {
                    /////////////////////////////////// Оплата на карту     
                    case 'card':                    
                                     
                                        
                    
                                        if(isset($payment->operationId)){
                                            
                                            echo $payment->operationId;

/*                                            $operation_request = new CashGetOperationDataRequest();
                                            $operation_request->operationId=$payment->operationId;
                                            
                                            //$confirm_request->type=1;
                                            //print_r($confirm_request);
                                            
                                            $invoice_data = $client->getOperationData($operation_request);   
                                            
                                            print_r($invoice_data);
*/                        				    //if()  
                                            //Redirect::to($payment->result);
                                        
                                        }else{
                                            //echo 111;
                                            
                                            //$id = 1033331112;
                                        
                                            $cashout_request = new CashCashOutRequest();
                                            //echo 2;
                                            $cashout_request->amount=$payment->summ;
                                            $cashout_request->returnURL='https://femida24.kz/admin/payments/'.$id.'/payment_back_from_iframe';
                                            $cashout_request->postLink='https://femida24.kz/payment/wooppay/callback_admin/' . $id . '/' . md5($id.'jLbjQ1E0P4');
                                            $cashout_request->extID=$id;
                                            $cashout_request->phone='+77710151515';//'+380937197698'; 
                                            $cashout_request->addParams='Выплата на карту '.$payment->user_card.' - id заявки '.$payment->id;
                        
                                            //print_r($cashout_request);
                                            
                                            $invoice_data = $client->cashOut($cashout_request);
                                            
                                            //echo 3;
                                            
                                            //print_r($invoice_data);
                                            
                                            //die;
                                            
                                            if(isset($invoice_data->response->operationId)){
                        
                                                DB::table('users_payments')
                                                ->where('id', $id)
                                                ->update([
                                                'result' => $invoice_data->response->redirectURL,
                                                'operationId' => $invoice_data->response->operationId,
                                                //'status' => 2
                                                ]);  
                                                
                                            }
                                            
                                            //return redirect()->away($invoice_data->response->redirectURL);
                                            
                                            //Redirect::to();
                                            
                                        }
                    
                    
                    
                    break;
                    /////////////////////////////////// Кошелек wooppay
                    case 'wooppay':

                                        if(isset($payment->operationId)){
                                            
                                            echo $payment->operationId;
                        				
                                        }else{
                    
                        
                                            $operation_request = new CashCreateOperationRequest();
                                            
                                            $operation_request->type=1;
                                            $operation_request->receiver=$payment->user_wooppay;
                                            $operation_request->amount=$payment->summ;
                                            $operation_request->description='Выплата на кошелек '.$payment->user_wooppay.' - id заявки '.$payment->id;
                        
                            				$invoice_data = $client->createOperation($operation_request);
                            				
                                            //print_r($operation_data);
                                            
                                            if(isset($invoice_data->response->operationId)){
                        
                                                DB::table('users_payments')
                                                ->where('id', $id)
                                                ->update(['operationId' => $invoice_data->response->operationId,'status' => 2]);  
                                                
                                            }
                                        
                                        
                                        }
                    
                    break;
                    }  
                    
  			}else{
    			 
                 echo 'no connect woopay';
                 
    			 }
    		} catch (Exception $e) {
    			echo 'error';//sprintf('Wooppay exception : %s order id (%s)', $e->getMessage(), $this->request->get['order']);
    		}            
            
            
/*            if(!isset($invoice_data)){
                if(isset($invoice_data->error_code)){
                    print_r($invoice_data);
                    return view('admin.payment', ['payment' => $payment,'invoice_data'=>$invoice_data]);
                }
                 
            }else{
                //return redirect()->route('payment',$id);
            }
*/              
              //return view('admin.payment', ['payment' => $payment,'invoice_data'=>$invoice_data]);
              
              return redirect()->route('payment',$id);
              
            //return view('admin.payment', ['payment' => $payment,'invoice_data'=>$invoice_data]); 

         }   



         public function user_transactions($id){
            
            $get_user = User::find($id);
            
            $user_transactions = DB::table('users_transactions_report as utr')
                        ->where('utr.user_id', $id)
                        ->leftJoin('users','users.id', '=', 'utr.user_id')
                        ->select('utr.*')
                        ->orderByDesc('utr.id')
                        ->get();

            
            return view('admin.user_transactions', ['user'=>$get_user,'user_transactions' => $user_transactions]); 


        }






         public function payments(){

            $payments = DB::table('users_payments')
                        ->leftJoin('users','users.id', '=', 'users_payments.from_user_id')
                        ->select('users_payments.*','users.email as user_email')
            ->orderByDesc('id')
            ->get();

            
            return view('admin.payments', ['payments' => $payments]); 

         }   

    
         public function messages(){
        
//$offers = App\Offers::all();
//return view('admin.offers', ['offers' => $offers]); 
            $user = Auth::user();

            $offers = DB::table('messages')
            
            //->where('offer_id', $user->id)
            
            ->where('from_user_id','!=', $user->id)
            ->where('to_user_id', $user->id)
            
            //->where('from_user_id', $password)
            //->join('users', 'users.id', '=', 'messages.user_id')
            //->select('offers.*','users.name')
            ->groupBy('from_user_id')
            ->get();
            

           foreach($offers as $key=>$offer){


                $offer_images = DB::table('offers_images')
                ->where('offers_images.offer_id', $offer->offer_id)
                ->join('images', 'images.id', '=', 'offers_images.image_id')
                ->select('images.filename')
                ->get();

                $offers[$key]->images = $offer_images;

           }
           
            //$messages = '';
            //$id = '';
            
            //dd($offers);
            
            return view('admin.messages', ['offers' => $offers]);        

            // return view('admin.offers');
         }    
    


         public function messages_offer($id,$id_m){

            $user = Auth::user();

            $offers = DB::table('messages')
            ->where('from_user_id','!=', $user->id)
            ->where('to_user_id', $user->id)
            ->groupBy('from_user_id')
            ->get();
            

           foreach($offers as $key=>$offer){

                $offer_images = DB::table('offers_images')
                ->where('offers_images.offer_id', $offer->offer_id)
                ->join('images', 'images.id', '=', 'offers_images.image_id')
                ->select('images.filename')
                ->get();

                $offers[$key]->images = $offer_images;

           }
           
            $messages = DB::table('messages')
            ->where('offer_id', $id)
            ->whereIn('from_user_id', [$id_m,$user->id])
            ->whereIn('to_user_id', [$id_m,$user->id])
            ->get();
            
                
            if($messages->count()){
            
                return view('admin.messages', ['offers' => $offers,'messages' => $messages,'id'=>$id,'id_m'=>$id_m,'user_id'=>$user->id]);        
    
            }else{
                
                return redirect()->route('admin_messages');
                
            }

            // return view('admin.offers');
         } 


 
    public function add_message(Request $request,$id,$id_m){ 
 
            
            
            $user = Auth::user();
            
            
            $Offer = Offers::find($id);

/*            $offer_images = DB::table('offers_images')
            ->where('offers_images.offer_id', $Offer->id)
            ->join('images', 'images.id', '=', 'offers_images.image_id')
            ->select('images.filename')
            ->get();
*/            



            
            
            //DB::table('files')->insert($sql);
            
            $Message = new Message();
            
            $Message->offer_id = $Offer->id;
            
            $Message->message = $request->areas;
            
            //if($file){
            //$Message->file_id = $File->id;
            //}
            
            $Message->from_user_id = $user->id;

            $Message->to_user_id = $id_m;
            
            
            
            $Message->save();

            
            return redirect()->route('admin_messages_offer',[$id,$id_m]);
 
 
    }
 


         public function get_cities(Request $request)
         {

            $cities = DB::table('city')
                ->select('city.name')
                ->join('region', 'region.id_region', '=', 'city.id_region')
                ->where('region.name', '=', $request->region)
                ->where('city.name','LIKE',"%{$request->string}%")
                ->get();

                
                $out = '';
                
                foreach($cities as $city){
                    
                    $out .= '<p><a>'.$city->name.'</a></p>';
                    
                }
                
                return response()->json(['data'=>$out]);
                
         }




         public function get_regions(Request $request)
         {

            $regions = DB::table('region')
                ->select('region.name')
                ->join('countries', 'countries.id_country', '=', 'region.id_country')
                ->where('countries.name', '=', $request->country)
                ->where('region.name','LIKE',"%{$request->string}%")
                ->get();

                
                $out = '';
                
                foreach($regions as $region){
                    
                    $out .= '<p><a>'.$region->name.'</a></p>';
                    
                }
                
                return response()->json(['data'=>$out]);
                
         }




         public function get_countries(Request $request)
         {
                //public $primaryKey = 'name';
                
                //echo $request->string;
                
                $countries = Country::where('name', 'LIKE', "%{$request->string}%")->get(); //where('name', $request->string)->get();
                
                //dd($countries);
                
                $out = '';
                
                foreach($countries as $country){
                    
                    $out .= '<p><a>'.$country->name.'</a></p>';
                    
                }
                
                return response()->json(['data'=>$out]);
                
         }





       //  public function rental_request(){
      //      return view('admin.rental_request');
      //   }


         public function dashboard(){
            
            $user = Auth::user();
            
            return view('admin.dashboard',['user'=>$user]);
         }

         public function offers(){
             return view('admin.offers');
         }

      //   public function rent(){
      //       return view('admin.rent');
      //   }

         public function employers(){
            
             $employers = DB::table('users')->where('role_id', 3)->get();   
            
             return view('admin.employers',['employers' => $employers]);
         }

         public function values(){
             
             $values=[];
             
             $values['param1'] = DB::table('values')->where('param', 'param1')->first();            
             $values['param2'] = DB::table('values')->where('param', 'param2')->first();            
             $values['param3'] = DB::table('values')->where('param', 'param3')->first();            
             $values['param4'] = DB::table('values')->where('param', 'param4')->first();            
             $values['param5'] = DB::table('values')->where('param', 'param5')->first();            
            
             //print_r($values); die;
            
             return view('admin.values',['values'=>$values]);
         }

         public function values_submit(Request $request){
             
             //print_r($request->all()); die;
             
             DB::table('values')->where('param', 'param1')->update(['value' => $request->input('param1')]);
             DB::table('values')->where('param', 'param2')->update(['value' => $request->input('param2')]);
             DB::table('values')->where('param', 'param3')->update(['value' => $request->input('param3')]);
             DB::table('values')->where('param', 'param4')->update(['value' => $request->input('param4')]);
             DB::table('values')->where('param', 'param5')->update(['value' => $request->input('param5')]);
            
             
             return redirect()->route('values');
             //return view('admin.values');
         }

        
        public function categories_lawyer(){
            
            $categories_lawyer = DB::table('categories_lawyer')->paginate(20);  
            
            return view('admin.categories_lawyer',['categories_lawyer'=>$categories_lawyer]);
        }
        
        public function categories_lawyer_add(){

            $categories_lawyer = DB::table('categories_lawyer')->get();  
            
            return view('admin.categories_lawyer_add',['categories_lawyer'=>$categories_lawyer]);

            
        }

        public function categories_lawyer_submit(Request $request){
            
            $req = $request->all();
            //print_r($req);
            //die;
            
            DB::table('categories_lawyer')->insert([
            'parent_id' => $req['parent_id'],
            'link' => $req['link'],
            'is_enabled' => $req['is_enabled'],
            'name_ru' => $req['name_ru'],
            'name_en' => $req['name_en'],
            'name_kz' => $req['name_kz'],
            ]);
 
            return redirect()->route('categories_lawyer');
        
        }
        
        
        
        
         public function categories_lawyer_edit($id)
         {

            $user = Auth::user();
            
            $category = DB::table('categories_lawyer')->where('id', $id)
            //->where('user_id', $user->id)
            ->first();

            $categories_lawyer = DB::table('categories_lawyer')->get();  

            return view('admin.categories_lawyer_edit', ['categories_lawyer'=>$categories_lawyer,'category' => $category]);     


         }


        public function categories_lawyer_edit_submit(Request $request,$id){
            
            $req = $request->all();
            //print_r($req);
            //die;
            
            
            DB::table('categories_lawyer')
            ->where('id', $id)
            ->update([
            'parent_id' => $req['parent_id'],
            'link' => $req['link'],
            'is_enabled' => $req['is_enabled'],
            'name_ru' => $req['name_ru'],
            'name_en' => $req['name_en'],
            'name_kz' => $req['name_kz'],
            ]);
 
            return redirect()->route('categories_lawyer');
        
        }


         public function categories_lawyer_delete($id,$redirect=true)
         {
            
            $user = Auth::user();

            DB::table('categories_lawyer')->where('id', $id)->delete();

             if($redirect){
                return redirect()->route('categories_lawyer');
             }

         }


         public function categories_lawyers_delete(Request $req)
         {
                
                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->categories_lawyer_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('categories_lawyer');
                
         }        
        
       
        
        
         public function users(){

             $users = DB::table('users')
             ->select('users.*','roles.name_ru')
             ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
             ->where('users.id','!=', 1)
             //->get();   
             ->orderByDesc('id')
             ->paginate(10);   
            
             $roles = DB::table('roles')->where('id','!=', 10)->get();   
             
             //dd($users);   

             return view('admin.users',['users' => $users,'roles' => $roles]);
         }

         public function blog(){
            
            
             $blog = Blog::all();
            
            
             return view('admin.blog', ['blogs' => $blog]);
            
             //return view('admin.blog');
         }
         
         public function blog_edit($id){
            
            
            $post = DB::table('blogs')->where('id', $id)->first();
            
            
             return view('admin.blog_edit', ['post' => $post]);
            
             //return view('admin.blog');
         }
         
         
         
          public function edit_profile(){
            
            
             $user = Auth::user();
            
                
            
            
            
             return view('admin.edit_profile',['user'=>$user]);
         }
        
         
         
         
          public function edit_profile_submit(Request $req){
            
            

            $u = Auth::user();

            $file = $req->file('avatar');
            
            if($file){

                if($u->avatar){
    
                    $image_path = public_path('images/avatars').'/'.$u->avatar;
                    
                    //echo $image_path;
          
                      if(File::exists($image_path)) {
                            File::delete($image_path);
                      }
                    
                }

                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('images/avatars'),$fileName); 

            
            }
            
            //dd($file);
            
    
            $User = User::find($u->id);

            if(isset($fileName))
                $User->avatar = $fileName;


            $User->name = $req->input('login');
            
            if($req->input('password')!=''){
            $password = Hash::make($req->input('password'));
            
            $User->password = $password;
            }
            
            $User->email = $req->input('email');
            
            

            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            
            
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            $User->country = $req->input('country');
            $User->region = $req->input('region');
            //$Offer->city = $req->input('city');
            $User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            //$User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            //$Offer->status = $req->input('status');
            //$Offer->comment = $req->input('comment');
            
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $User->save();
            
                        
           return redirect()->route('edit_profile');

         }
        
         

         public function settings(){
            

            $roles = DB::table('roles')
            
            ->where('name', '!=', 'admin')

            //->join('users', 'users.id', '=', 'offers.user_id')
            //->select('offers.*','users.name')
            ->get();
            
            
        
            return view('admin.settings', ['roles' => $roles]); 


         }



         public function settings_roles_submit(Request $request){
            

            DB::table('roles')
            ->where('name', '!=', 'admin')
            ->update(['suggestions' => 0,'rental_requests' => 0,'employees' => 0,'values' => 0,
                      'users' => 0,'articles' => 0,'settings' => 0]);
            
            if($request->suggestions):
                foreach($request->suggestions as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['suggestions' => 1]);
                }
            endif;

            if($request->rental_requests):
                foreach($request->rental_requests as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['rental_requests' => 1]);
                }
            endif;
            
            if($request->employees):    
                foreach($request->employees as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['employees' => 1]);
                }
            endif;
            
            if($request->values):
                foreach($request->values as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['values' => 1]);
                }
            endif;
            
            if($request->users):
                foreach($request->users as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['users' => 1]);
                }
            endif;
            
            if($request->articles):
                foreach($request->articles as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['articles' => 1]);
                }
            endif;
            
            if($request->settings):
                foreach($request->settings as $key=>$value){
                    DB::table('roles')->where('id', $key)->update(['settings' => 1]);
                }
            endif;


  
            return redirect()->route('settings');
  
         }





         public function offers_add(Request $request){

            $result = $request->session()->all();//получаем данные из сессии
    	    $token = $result['_token'];


             return view('admin.offers_add',['token'=>$token]);
         }


         public function offers_add_submit(Request $req)
         {

            $Offer = new Offers();
            
            $Offer->message = $req->input('message');
            
            $Offer->save();
            
            
            return redirect()->route('offers');
             
         }

  
        public function add_image(Request $request)
        {
            //
    
            $image = $request->file('file');
            $avatarName = $image->getClientOriginalName();
            $image->move(public_path('images/'),$avatarName);
             
            $imageUpload = new Image();
            $imageUpload->filename = $avatarName;
            $imageUpload->save();
            return response()->json(['success'=>$avatarName,'id'=>$imageUpload->id]);
            
        }

        public function delete_image(Request $request)
        {
            
            
            $Image = DB::table('images')->where('filename', $request->filetodelete)->first();
            
            DB::delete('delete from images where filename = ?',[$request->filetodelete]);
            
            $image_path = public_path('images').'/'.$request->filetodelete;
            
            //echo $image_path;
  
              if(File::exists($image_path)) {
                    File::delete($image_path);
              }
              
              return response()->json(['id'=>$Image->id]);
            
        
        }


        public function offers_delete_file(Request $request)
        {
            
            
            $Offer = Offers::where('filename', $request->filetodelete)->first();
            //$Offer = $Offer->where('image', $path);
            $Offer->filename = '';
            $Offer->save();
                        
            $image_path = public_path('files').'/'.$request->filetodelete;
            
            //echo $image_path;
  
              if(File::exists($image_path)) {
                    File::delete($image_path);
              }
            
        
        }




          public function user_add(){
            
            
             return view('admin.user_add');
         }



          public function user_add_submit(Request $req){
            
            
            $User = new User();
  
  //dd($id);

            $file = $req->file('avatar');
            
            if($file){

                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('images/avatars'),$fileName); 

            
            }
            
            //dd($file);

            if(isset($fileName))
                $User->avatar = $fileName;



            $User->name = $req->input('login');
            
            if($req->input('password')){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');
            
            $User->role_id = $req->input('role_id');
            


            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            
            
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            $User->country = $req->input('country');
            $User->region = $req->input('region');
            //$Offer->city = $req->input('city');
            $User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            $User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            $User->status = $req->input('status');
            //$Offer->comment = $req->input('comment');
            
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $User->save();


            DB::table('role_user')->insert(
                array('role_id' => $req->input('role_id'), 'user_id' => $User->id)
            );


            
           return view('admin.user_edit',['user'=>$User]); 
            
         }
 
 
 
 
          public function user_edit($id){
            
             $user = User::find($id);
             
            
             return view('admin.user_edit',['user'=>$user]);
         }
         
 
 
 
         
         
          public function user_edit_submit(Request $req,$id){
            
            $User = User::find($id);
  
            $file = $req->file('avatar');
            
            if($file){

                if($User->avatar){
    
                    $image_path = public_path('images/avatars').'/'.$User->avatar;
                    
                    //echo $image_path;
          
                      if(File::exists($image_path)) {
                            File::delete($image_path);
                      }
                    
                }




            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('images/avatars'),$fileName); 

            
            }
            
            //dd($file);
            
    
            

            if(isset($fileName))
                $User->avatar = $fileName;

            $User->name = $req->input('login');
            
            if($req->input('password')!=''){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');
            
            $User->role_id = $req->input('role_id');

            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            
            
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            $User->country = $req->input('country');
            $User->region = $req->input('region');
            //$Offer->city = $req->input('city');
            $User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            $User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            
            $User->status = $req->input('status');
            
            if($req->input('status')==1){
                $User->verify_token = null;
            }
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $User->save();


            //DB::table('role_user')->insert(
            //    array('role_id' => $req->input('role_id'), 'user_id' => $User->id)
            //);

            DB::table('role_user')
            ->where('user_id', $User->id)
            ->update(array('role_id' => $req->input('role_id')));            

           return view('admin.user_edit',['user'=>$User]); 
            
         }



         public function user_delete($id,$redirect=true)
         {

            //$user = Auth::user();
            
            $User = User::find($id);
            

            $filename = public_path('files').'/avatars/'.$User->avatar;
            
            //echo $image_path;
  
              if(File::exists($filename)) {
                    File::delete($filename);
              }


            DB::table('users')->where('id', '=', $id)->delete();
            DB::table('role_user')->where('user_id', '=', $id)->delete();

            
             return redirect()->route('users');

            //return view('admin.users',['users' => $users,'roles' => $roles]);   

         }


          public function users_delete(Request $req){
            
            //$Ids = $req->delete;
            
            //dd($req);
            

                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->user_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('users');
            

          }




///////////////////////////





          public function employers_add(){
            
            
             return view('admin.employers_add');
         }



          public function employers_add_submit(Request $req){
            
            
            $User = new User();
  
  //dd($id);

            $file = $req->file('avatar');
            
            if($file){

                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('images/avatars'),$fileName); 

            
            }
            
            //dd($file);

            if(isset($fileName))
                $User->avatar = $fileName;



            $User->name = $req->input('login');
            
            if($req->input('password')){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');
            
            $role_id = 3;
            
            $User->role_id = $role_id;
            


            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            
            
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            $User->country = $req->input('country');
            $User->region = $req->input('region');
            //$Offer->city = $req->input('city');
            $User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            $User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            $User->status = $req->input('status');
            //$Offer->comment = $req->input('comment');
            
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $User->save();

            DB::table('role_user')->insert(
                array('role_id' => $role_id, 'user_id' => $User->id)
            );

            
           return view('admin.employers_edit',['user'=>$User]); 
            
         }
 
 
 
 
          public function employers_edit($id){
            
             $user = User::find($id);
             
            
             return view('admin.employers_edit',['user'=>$user]);
         }
         
 
 
 
         
         
          public function employers_edit_submit(Request $req,$id){
            
            $User = User::find($id);
  
            $file = $req->file('avatar');
            
            if($file){

                if($User->avatar){
    
                    $image_path = public_path('images/avatars').'/'.$User->avatar;
                    
                    //echo $image_path;
          
                      if(File::exists($image_path)) {
                            File::delete($image_path);
                      }
                    
                }




            
                //$fileName = $file->getClientOriginalName();
                
                $fileName = substr(md5(microtime() . rand(0, 9999)), 0, 20).'_'.date('d_m_Y').'.'.$file->getClientOriginalExtension();
                
                //$fileName = $file->getClientOriginalExtension();
                
                $file->move(public_path('images/avatars'),$fileName); 

            
            }
            
            //dd($file);
            
    
            

            if(isset($fileName))
                $User->avatar = $fileName;

            $User->name = $req->input('login');
            
            if($req->input('password')){
                $password = Hash::make($req->input('password'));
                $User->password = $password;
            }
            $User->email = $req->input('email');
            
            $User->role_id = $req->input('role_id');

            $User->fullname = $req->input('fullname');
            $User->family = $req->input('family');
            $User->surname = $req->input('surname');
            $User->date = $req->input('date');
            $User->month = $req->input('month');
            $User->year = $req->input('year');
            
            if($req->input('sex_m')=='on'){ 
                $User->sex='m';
            }
            if($req->input('sex_w')=='on'){ 
                $User->sex='w';
            }
            //$Offer->lift = $req->input('lift') ? 1 : 0;
            //$Offer->parking = $req->input('parking') ? 1: 0;
            
            
            //$Offer->area = $req->input('area');
            //$Offer->rooms = $req->input('rooms');
            $User->country = $req->input('country');
            $User->region = $req->input('region');
            //$Offer->city = $req->input('city');
            $User->district = $req->input('district');
            //$Offer->street = $req->input('street');
            //$Offer->house = $req->input('house');
            
            $User->facebook = $req->input('facebook');
            $User->vk = $req->input('vk');
            $User->instagram = $req->input('instagram');

            $User->phone = $req->input('phone');
            $User->map_coordinates = $req->input('map_coordinates');

            //$Offer->furniture = $req->input('furniture') ? 1: 0;
            $User->status = $req->input('status');
            //$Offer->comment = $req->input('comment');
            
            
            
            //$Offer->user_id = $user->id;
            
            //$Offer->views = 0;
            
            $User->save();


            DB::table('role_user')
            ->where('user_id', $User->id)
            ->update(array('role_id' => $req->input('role_id')));            

            

           return view('admin.employers_edit',['user'=>$User]); 
            
         }



         public function employer_delete($id,$redirect=true)
         {

            //$user = Auth::user();
            
            $User = User::find($id);
            

            $filename = public_path('files').'/avatars/'.$User->avatar;
            
            //echo $image_path;
  
              if(File::exists($filename)) {
                    File::delete($filename);
              }


            DB::table('users')->where('id', '=', $id)->delete();

             if($redirect){
                return redirect()->route('employers');
             }  
            
//             return redirect()->route('employers');

            //return view('admin.users',['users' => $users,'roles' => $roles]);   

         }

         public function employers_delete(Request $req)
         {
         
                if($req->delete){
                
                    foreach($req->delete as $value){
                    
                        $this->employer_delete($value,false);
                        
                    }
                
                }
                
                return redirect()->route('employers');         
         
         }   
        
        


}
