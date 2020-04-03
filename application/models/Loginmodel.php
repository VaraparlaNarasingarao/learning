<?php 
class Loginmodel extends CI_Model
{
   public function sendnotification($mobileno,$device_id)
	{
	    
		$res=$this->db->get_where('user_details',array('mobileno'=>$mobileno,'device_id'=>$device_id))->result_array();
		//print_r($res);
	    if(!empty($res))
	    {
	        
	         
	     	 $fcmRegIds= $this->db->query("SELECT  `device_id`,`pnr_id` FROM  user_details where `device_id`='$device_id' AND `mobileno`='$mobileno'")->result_array();
	     	// print_r($fcmRegIds);
                     if(!empty($fcmRegIds))
                	 {
                	//print_r($fcmRegIds); exit;
                	$fids=array_column($fcmRegIds,'pnr_id');
                	$device_ids=array_column($fcmRegIds,'device_id');
                	//$dd = $this->getusersbydevice($device_ids,$user_id);
                	//return $fids;
                
                	#API access key from Google API's Console
                	define( 'API_ACCESS_KEY', 'AAAAdjvHKMU:APA91bHS892mNgF1-S1QlbLLNn4y-SHdWWHr32EtRZ-gEgSfke8jMiiDZih_5ziLVtnMgWan6PwL8Rkww1M_5WLJhP-_x7AHautqRGWVcVJ88GQptj1E7UfBE9gsoop7RBgwYa0AD_3G' );
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
                //	echo  $result;
                	
                	 }
                	 else{
                		//echo 0;
                	 }
	     	
	    }
	}
	 
}