<?php
// Asimbel contact endpoint. Sends via the host's mail() / local MTA.
// Optional env vars: MAIL_FROM (must be an address on your domain, e.g. no-reply@asimbel.com)
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
function out($ok,$msg,$code=200){http_response_code($code);echo json_encode(['ok'=>$ok,'message'=>$msg]);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST') out(false,'Method not allowed.',405);
if(!empty($_POST['website'])) out(true,'OK'); // honeypot: silently drop bots

// Rate limit: 5 requests / hour / IP
$f=sys_get_temp_dir().'/asimbel_rl_'.md5($_SERVER['REMOTE_ADDR']??'x');
$hits=array_filter(is_file($f)?(array)json_decode(file_get_contents($f)):[],fn($t)=>$t>time()-3600);
if(count($hits)>=5) out(false,'Too many requests. Please try again later.',429);
$hits[]=time(); file_put_contents($f,json_encode(array_values($hits)),LOCK_EX);

function clean($v,$max){$v=trim((string)$v);$v=str_replace(["\r","\0"],'',$v);return mb_substr(strip_tags($v),0,$max);}
function oneline($v){return str_replace("\n",' ',$v);}
$name=oneline(clean($_POST['name']??'',100));
$email=oneline(clean($_POST['email']??'',150));
$company=oneline(clean($_POST['company']??'',120));
$phone=oneline(clean($_POST['phone']??'',40));
$service=oneline(clean($_POST['service']??'',80));
$details=clean($_POST['details']??'',5000);
$allowed=['Zoho Implementation','Zoho CRM','Zoho Books','Zoho Creator','Zoho Analytics','Business Automation','AI & Intelligent Automation','API & System Integration','Custom Software Development','Digital Transformation','Other',''];
if($name===''||$details==='') out(false,'Please fill in all required fields.',422);
if(!filter_var($email,FILTER_VALIDATE_EMAIL)) out(false,'Please enter a valid email address.',422);
if(!in_array($service,$allowed,true)) out(false,'Invalid service selected.',422);
if($phone!==''&&!preg_match('/^[0-9+()\-\s.]{5,40}$/',$phone)) out(false,'Please enter a valid phone number.',422);

$svc=$service?:'General';
$subject='New Website Enquiry | Asimbel | '.$svc;
$body="New enquiry received through asimbel.com\n\nName: $name\nEmail: $email\nCompany: $company\nPhone: $phone\nService: $svc\nProject Details:\n$details\n\nSubmitted At: ".gmdate('Y-m-d H:i:s')." UTC\n";
$from=getenv('MAIL_FROM')?:'no-reply@asimbel.com';
$headers="From: Asimbel Website <$from>\r\nReply-To: $name <$email>\r\nContent-Type: text/plain; charset=UTF-8\r\nX-Mailer: PHP";
$sent=@mail('info@asimbel.com','=?UTF-8?B?'.base64_encode($subject).'?=',$body,$headers,'-f'.$from);
if(!$sent){error_log('Asimbel mail() failed');out(false,'We could not send your enquiry. Please try again or email info@asimbel.com.',500);}
out(true,'Sent');
