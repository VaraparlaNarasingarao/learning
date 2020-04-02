<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		echo "Invalid View";
	}
	public function create_otp()
	{
		$data = json_decode(file_get_contents("php://input"),true);
		$data=(isset($data))? $data : $_POST;
		$mobileno=$data['mobileno'];
		if(isset($data['device_id']))
		{
			$device_id=$data['device_id'];
		}else{
			$device_id=0;
		}
		if(isset($data['pnr_id']))
		{
			$pnr_id=$data['pnr_id'];
		}else{
			$pnr_id=0;
		}
		$otp=rand(1000,9999);
		if($mobileno !='' && $device_id !='' && $pnr_id !=''){
			$users=$this->db->get_where('user_details',array('mobileno'=>$mobileno,'device_id'=>$device_id))->result_array();
			if(count($users)>0)
			{
				$userdata=array('pnr_id'=>$pnr_id,'otp'=>$otp);
				$this->db->where('mobileno',$mobileno);
				$this->db->where('device_id',$device_id);
				$this->db->update('user_details',$userdata);
			}
			else{
				
				$userdata=array('mobileno'=>$mobileno,'device_id'=>$device_id,'pnr_id'=>$pnr_id,'otp'=>$otp);
				$this->db->insert('user_details',$userdata);
			}
			$this->sendnotification($mobileno,$device_id);
			$response=array('status'=>'1', 'message'=>'Otp generated succwssfully');
			echo json_encode($response);
		}
		else{
			$response=array('status'=>'0', 'message'=>'Please provide all mandatory details !');
		    echo json_encode($response);
		}
	}
	public function sendnotification($mobileno,$device_id)
	{
		$res=$this->db->get_where('user_details',array('mobileno'=>$mobileno,'device_id'=>$device_id))->result_array();
	    if(!empty($res))
	    {
	        for($a=0; count($res)>$a; $a++)
	        {
	         
	     	 $fcmRegIds= $this->db->query("SELECT  `device_id`,`pnr_id` FROM  user_details where device_id='$device_id' AND 'mobileno'=$mobileno ")->result_array();
                     if(!empty($fcmRegIds))
                	 {
                	//print_r($fcmRegIds); exit;
                	$fids=array_column($fcmRegIds,'pnr_id');
                	$device_ids=array_column($fcmRegIds,'device_id');
                	//$dd = $this->getusersbydevice($device_ids,$user_id);
                	//return $fids;
                
                	#API access key from Google API's Console
                	define( 'API_ACCESS_KEY', 'AAAABCNrv-g:APA91bEO-2EYMpX9b5bE4BrTdpc_dr5IK6JvVJNcQZSNnFs18Gm3vfJQBapi8esYPG9_9P1mPZV-mO4RReNjwuP0QBV2M4GpdEyKkxzqGteSGF66jjSkKqsmvSqUHVGNsuBGEI26ZJfl' );
                	$registrationIds =$fids;
                	//   print_r($registrationIds); exit;
                	#prep the bundle
                	$msg = array
                	(
                	'body'  => "Your otp is ".$res[0]['otp'],
                	'title' =>"OTP",
                	'click_action'=>'FCM_PLUGIN_ACTIVITY',
                	'icon' => 'fcm_push_icon',/*Default Icon*/
                    "color"=>"#7CFC00",
                	'sound' => 'mySound'/*Default sound*/
                	);
                
                	$fields = array
                	(
                	'registration_ids'  => $registrationIds,
                	'notification' => $msg
                	);
                
                
                	$headers = array
                	(
                	'Authorization: key=' . API_ACCESS_KEY,
                	'Content-Type: application/json'
                	);
                
                	#Send Reponse To FireBase Server 
                	$ch = curl_init();
                	curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
                	curl_setopt( $ch,CURLOPT_POST, true );
                	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
                	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
                	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
                	curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
                	$result = curl_exec($ch );
                	curl_close( $ch );
                
                	#Echo Result Of FireBase Server
                	echo  $result;
                	
                	 }
                	 else{
                		 echo 0;
                	 }
	     	}
	    }
	}
	public function check_otp()
	{
		$data = json_decode(file_get_contents("php://input"),true);
		$data=(isset($data))? $data : $_POST;
		$mobileno=$data['mobileno'];
		$otp_number=$data['otp_number'];
		if(isset($data['device_id']))
		{
			$device_id=$data['device_id'];
		}else{
			$device_id=0;
		}
		if($mobileno !='' && $device_id !='' && $otp_number !=''){
			$users=$this->db->get_where('user_details',array('mobileno'=>$mobileno,'device_id'=>$device_id,'otp'=>$otp_number))->result_array();
			if(count($users)>0){
				 $response=array('status'=>'1', 'message'=>'Otp verified succwssfully');
			}
			else{
				$response=array('status'=>'0', 'message'=>'Invalid Otp');
			}
		}
		else{
			$response=array('status'=>'0', 'message'=>'Please provide all mandatory details !');
		}
		 echo json_encode($response);
	}
	
}
