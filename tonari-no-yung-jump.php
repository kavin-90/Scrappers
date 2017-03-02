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

function get_http_response_code($url) {
$headers = get_headers($url);
return substr($headers[0], 9, 3);
}




function parse_html($url){

$text = create_dom($url);

$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8')); // the variable $ads contains the HTML code above
libxml_clear_errors();
$xpath = new DOMXPath($doc);
$series = array();
$page_list = array();
$get_pages = $xpath->query('//img[@class="js-page-image"]');
foreach($get_pages as $pages){
$page_list[] = $pages->getAttribute('src');
}

$title = $xpath->query('//section[@class="viewer-colophon-nomal"]/h4');
$chapter = $xpath->query('//section[@class="viewer-colophon-nomal"]/p[@class="viewer-colophon-series-num"]');

$series["title"] = $title[0]->nodeValue;
$series["slug"] = $title[0]->nodeValue;
$series["chapter_number"] = $chapter[0]->nodeValue;
$series["page_list"] = $page_list;

return $series;
}



function download($url){

$series = parse_html($url);

$page_list = $series["page_list"];

$series_title = $series["title"];
$chapter_number = $series["chapter_number"];

if(!file_exists("wfio://$series_title")) mkdir("wfio://$series_title");

if(!file_exists("wfio://$series_title/$chapter_number")) mkdir("wfio://$series_title/$chapter_number");

if(isset($page_list)){

$i = 0;
foreach($page_list as $files){
$i++;

$file_url = $files;

$image_info = getImageSize($file_url);
switch ($image_info['mime']) {
case 'image/gif':
    $extension = 'gif';
    break;
case 'image/jpeg':
    $extension = 'jpg';
    break;
case 'image/png':        
    $extension = 'png';
    break;
default:
    // handle errors
    break;
}



if($i<10){
$file_name = "0$i.$extension";
} else {
$file_name = "$i.$extension";
}


$destination = "$series_title/$chapter_number/$file_name";

$content = create_dom($file_url);
if(!file_put_contents("wfio://$destination", $content)){
echo "Failed to Download Image : <b>$file_url</b> <br>".PHP_EOL;
} else {
echo "Downloaded Image : <b>$file_url</b><br>".PHP_EOL;
}

}

} else {

echo "No Pages were found!";

}


}

$url = null;

if(isset($_POST["chapter_url"])){

$url = filter_var($_POST["chapter_url"], FILTER_SANITIZE_URL);

// Validate url
if (filter_var($url, FILTER_VALIDATE_URL)) {
    echo "Reading url: $url\r\n";
	
	download($url);
	
	
} else {
    echo "$url is not a valid URL";
}


}

?>

<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="edit-Type" edit="text/html; charset=utf-8">
<title>Tonari no Young Jump Chapter Download</title>
<style type="text/css">
/* Main */
* { box-sizing: border-box }
body {font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.42857143; color: #000; background-color: #fff; padding:0; margin:6px auto;}
td,th{font-size:12px;-webkit-text-size-adjust:none; padding: 0; }
ul{list-style-type: none; margin:0;padding:0}
ul,li { list-style:none;}
table{border-collapse:collapse;border-spacing:0;width:100%;}
.underline{text-decoration:underline}
.clear{clear:both}
.clearfix:before{  display: table; content: " ";}
.clearfix:after{ clear: both;}
.nowrap{white-space:nowrap}
.center{margin:0 auto}


.container { padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto; }


/* Login Box */
.login-box{ position:relative; margin: 10px auto; width: 500px; background: #fff; }
.login.icons input[type="text"],.login.icons input[type="password"] { width:92%; border-left:none;}
.login-addon{ color:#38B63C; float:left; font-size:2.4em; width:8%; background:#fff; text-align:center;	border:1px solid #38B63C; border-right:none; }
.login-box .logo{text-align:center; padding:8px}
.lb-header{ position:relative; color: #00415d; margin: 5px 5px 10px 5px; padding-bottom:10px; border-bottom: 1px solid #32A336; text-align:center; height:30px;}
.lb-header a{ margin: 0 auto; padding: 10px 20px; text-decoration: none; color: #666; border-radius: 4px 4px 0 0; font-size: 15px; -webkit-transition: all 0.1s linear; -moz-transition: all 0.1s linear; transition: all 0.1s linear;}
.lb-header a:hover,.lb-header .active{ color: #fff; background: #32A336; background-size:auto 40px;}
.social-login{ position:relative; float: left; width: 100%; height:auto; padding: 10px 0 15px 0; border-bottom: 1px solid #eee;}
.social-login a{ position:relative; float: left; width: 40%; text-decoration: none; color: #fff; border: 1px solid rgba(0,0,0,0.05); padding: 12px; border-radius: 2px; font-size: 12px; text-transform: uppercase; margin: 0 3%; text-align:center;}
.social-login a:hover{color:#fff;}
.social-login a i{ position: relative; float: left; width: 20px; height: 20px; bottom: 2px; margin-right: 8px; font-size:24px;}
.social-login a:first-child{ background: #49639F;}
.social-login a:last-child{ background: #DF4A32;}
.email-login,.email-signup,.user-forgot-password,.series-create{ position:relative; width: 100%; height:auto; text-align:left;}
.u-form-group{ width:100%; margin-bottom: 10px;}
.u-form-group.half{width: 20%;margin-right: 4px;display:inline-block;}
.u-form-group.half-40{width: 40%;margin-right: 4px;display:inline-block;}
.u-form-group.half-2{width: 15%;margin-right: 4px;display:inline-block;}
.u-form-group label{font-weight:bold;}
.u-form-group textarea,.u-form-group select,.u-form-group input[type="email"],.u-form-group input[type="text"],.u-form-group input[type="password"]{width: 100%; height:45px; outline: none; border: 1px solid #ddd; padding: 0 10px; border-radius: 2px; color: #333; -webkit-transition:all 0.1s linear; -moz-transition:all 0.1s linear; transition:all 0.1s linear;}
.u-form-group textarea:focus,.u-form-group select:focus,.u-form-group input:focus{ border-color: #32A336;}
.u-form-group textarea{height:100%; padding: 6px 12px}
.u-form-group button{width: 100%; background: #32A336; border: none; outline: none; color: #fff; font-size: 14px; font-weight: normal; padding: 14px 0; border-radius: 2px; text-transform: uppercase; cursor:pointer;}
.u-form-group button.reset{ background:#d9534f; }
.forgot-password{ width:50%; text-align: left; text-decoration: underline; color: #888; font-size: 0.75rem;}

.series-download{position:relative; margin: 10% auto; width: 500px; background: #fff;}
.series-download h1{ font-size: 16px; text-align: center;}
</style>
</head>
<body>
<div class="container">	
<form method="post" enctype="multipart/form-data" class="series-download">
<h1>Chapter Downloader</h1>	
<div class="u-form-group">
<label>Chapter Url</label>
<input type="text" name="chapter_url" value="<?php echo $url; ?>">
</div>
<div class="u-form-group">
<button type="submit">Submit</button>
</div>
</form>

</div><!-- Container -->
</body>
</html>