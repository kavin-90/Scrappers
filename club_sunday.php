<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

function create_dom($url,$follow=1) {
$ch = curl_init();
curl_setopt( $ch, CURLOPT_USERAGENT, " Google Mozilla/5.0 (compatible; Googlebot/2.1;)" );
if($follow==1){
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
}
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt( $ch, CURLOPT_REFERER, "https://www.google.com/bot.html" );
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9999999999999);
curl_setopt($ch, CURLOPT_TIMEOUT, 9999999999999);
$result = curl_exec($ch);
return $result;
}


function get_page($url){

if(!isset($url)){

die("Invaild Url");
}

$string = create_dom($url);
preg_match("/bookCode     = '(.*?)',/i", $string, $book_code);
preg_match("/CF_KEY_PAIR = '(.*?)',/i", $string, $get_key);

preg_match_all("/CF_PARAMS\['(.*?.jpg)'\] = '(\d+)&(.*?)'/", $string, $output_array);

$mi = new MultipleIterator();
$mi->attachIterator(new ArrayIterator($output_array[1]));
$mi->attachIterator(new ArrayIterator($output_array[2]));
$mi->attachIterator(new ArrayIterator($output_array[3]));


$key = $get_key[1];
$book_code = $book_code[1];

$msg = '';

$i = 0;
foreach ( $mi as $value ) {
$i++;
list($file_name, $expire, $signuture) = $value;


$url = "http://d23tvpbm084vy2.cloudfront.net/data_p/Tachiyomi/$book_code/$file_name?Expires=$expire&Key-Pair-Id=$key&Signature=$signuture";

$content = file_get_contents($url);
if(file_put_contents("oh/$file_name",$content)) { 
$msg .= "Page Saved $i <br>\r\n"; 
} else { $msg .= "Unable to Save Page $i : $url"; 
}
}

return $msg;
}



$get_url = "http://club-page.comsho.com/script/dl.php?param=tTifriLmv0kgdAG9GXJlBpBb3fnqcjRsMrrDxhW4XF4HeOTxeIC3ZDq7h1TeZTJbs%2BoN3uM6%2FnzFFzUjePs90HsgbzkhjuE%2Bhnx0j4b0UihtSuSct0SgISCgX7jPm9uT1dHk3nDYeqO1q0p5YNAxWbQpvuzky6ZtA4o8HYBp30CYrGSJ%2BL0Pfj7FlXZaJKI9";


echo get_page($get_url);


