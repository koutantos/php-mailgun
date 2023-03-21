<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

define("YOUR_DOMAIN_NAME", "xxxxxxx.com");
define("YOUR_API_KEY", "");
define("EMAIL_FROM", "<example@example.com>");
define("EMAIL_NAME_FROM", "EXAMPLE");

function send($timeTosend='',$dayTosend='',$list='',$zone){
	//$zone is +0200 for Greece Athens
	
	$emails="";
	$html="";
	$subject="";
	$newDate = date('Y-m-d '.$timeTosend, strtotime($dayTosend));
	$date=date_create($newDate);
	$mailgunDate=date_format($date,"D, d M Y H:i:s")." ".$zone;

	$text = file_get_contents($list);
	$text = explode("\n",$text);
	$output = "";
	foreach($text as $line)
	{  
	    $emails = $emails.$line;
	}

	$text = file_get_contents('content.html');
	$text = explode("\n",$text);
	$output = "";
	foreach($text as $line)
	{  
	    $html = $html.$line;
	}

	$text = file_get_contents('subject.txt');
	$text = explode("\n",$text);
	$output = "";
	foreach($text as $line)
	{  
	    $subject = $subject.$line;
	}

	$files=array();
	foreach (glob("files/*.{pdf,jpg,docx}", GLOB_BRACE) as $filename) {
	    $filename = basename($filename);
	    array_push($files,$filename);
	}
	//var_dump($files);die();
	$parameters = array('from' => EMAIL_NAME_FROM.''.EMAIL_FROM,
			 'to' => EMAIL_FROM,
		        'bcc' => $emails,
		        'subject' => $subject,
		        'html' => $html,
		        'o:deliverytime'=>$mailgunDate);
		                  
	$i=1;
	foreach($files as $k=>$f){
		$ext = pathinfo($f, PATHINFO_EXTENSION);
		if($ext=="pdf"){
			$parameters["attachment[".$i."]"]=curl_file_create("files/".$f, 'application/pdf', $f);
		}
		if($ext=="docx"){
			$parameters["attachment[".$i."]"]=curl_file_create("files/".$f, 'application/docx', $f);
		}
		if($ext=="jpg"){
			$parameters["attachment[".$i."]"]=curl_file_create("files/".$f, 'application/jpg', $f);
		}
		$i++;
	}                
	//var_dump($parameters);die();

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api.eu.mailgun.net/v3/'.YOUR_DOMAIN_NAME.'/messages');
	curl_setopt($ch, CURLOPT_USERPWD, 'api:'.YOUR_API_KEY);

	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($ch);
	var_dump($response);
}


//check content.html has the text to be sent 
//check subject.txt has the subject to be sent 
//check folder files that has attachments pdf,doc,jpg to be sent
//check emails.txt that has emails separated by comma.
$timeTosend='11:10:00';
$dayTosend='2023-03-21';
$zone="+0200";

send($timeTosend,$dayTosend,$list='emails.txt',$zone);



?>
