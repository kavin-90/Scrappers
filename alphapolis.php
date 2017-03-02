<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Extract Numbers
function extract_numbers_string($string) {
if(preg_match_all('/([\d]+)/', $string, $match))
return $match[1][0];
else return $string;
}

/* cUrl Call */
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

/* Check 404 or Invalid Request */
function get_http_response_code($url) {
$headers = get_headers($url);
return substr($headers[0], 9, 3);
}

function get_chapters($url){
	
$text = create_dom($url);

$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($text); // the variable $ads contains the HTML code above
libxml_clear_errors();
$xpath = new DOMXPath($doc);
$cover = $xpath->query('//div[@class="bigbanner section"]/img/@src');
$slug = preg_match("/http:\/\/www.alphapolis.jp\/manga\/regina_books\/(.*)\/top.jpg/i", $cover[0]->nodeValue, $get_slug);
$slug = isset($get_slug[1]) ? $get_slug[1] : null;
$title = $xpath->query('//div[@class="title_area"]/h2');
$summary = $xpath->query('//div[@class="info section"]/p');
$chapters = $xpath->query('//li/a[@class="read_button"]');
$series = array();
$chapter_list = array();
foreach ($chapters as $data) {
$href = $data->getAttribute('href');
$href_value = $data->nodeValue;
$chapter_list[] = array("url" => 'http://www.alphapolis.co.jp'.$href,"title" => $href_value);
}

$series["cover"] = $cover[0]->nodeValue;
$series["title"] = $title[0]->nodeValue;
$series["slug"] = $slug;
$series["summary"] = $summary[0]->nodeValue;
$series["chapter_list"] = $chapter_list;
return $series;
}


// Get Pages
function get_pages($url,$slug = null){

$text = create_dom($url);

$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($text); // the variable $ads contains the HTML code above
libxml_clear_errors();
$xpath = new DOMXPath($doc);

$page_list = $xpath->query('//script[contains(.,"var _pages")]');

// Get Chapter No.
$chapter_slug = $xpath->query('//div[@class="postscript"]/h2');
$chapter_slug = isset($chapter_slug[0]) ? $chapter_slug[0]->nodeValue: null;

preg_match_all('/(https?:\/\/\S+\.(?:jpg|png|gif))/', $page_list[0]->nodeValue, $pages);

$pages = call_user_func_array('array_merge', $pages);

if(count($pages)) {
foreach($pages as $page) {

// Chapter Slug
$chapter_slug = extract_numbers_string($chapter_slug);
// Series Slug
$series_slug = $slug;

if(!is_dir("oh/$series_slug")) mkdir("oh/$series_slug");
if(!is_dir("oh/$series_slug/$chapter_slug")) mkdir("oh/$series_slug/$chapter_slug");

$file_name = basename($page);
$destination = "oh/$series_slug/$chapter_slug/$file_name";

$content = file_get_contents($page);

if(!file_exists($destination)){
if(file_put_contents($destination,$content)) $success = true;
else $error = true;
}

}

}

return '';
}



// Current Page
$page = isset($_GET["page"]) ? filter_var ( $_GET["page"], FILTER_SANITIZE_STRING) : "dashboard";


//$baka = get_pages("http://www.alphapolis.co.jp/manga/viewManga/1477");

//print_r($baka);

//exit;

?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="edit-Type" edit="text/html; charset=utf-8">
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
.pull-left{float:left !important;}
.pull-right{float: right !important;}
.hidden{display:none !important}

code, kbd, pre, samp { font-family: monospace, monospace; font-size: 1em; }
code, kbd, pre, samp { font-family: Menlo, Monaco, Consolas, "Courier New", monospace; }
code { padding: 8px; font-size: 90%; color: #c7254e; background-color: #f9f2f4; border-radius: 4px; }
kbd { padding: 2px 4px; font-size: 90%; color: #fff; background-color: #333; border-radius: 3px; -webkit-box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .25); box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .25); }
kbd kbd { padding: 0; font-size: 100%; font-weight: bold; -webkit-box-shadow: none; box-shadow: none; }
a{ text-decoration:none;}

.container { padding-right: 15px; padding-left: 15px; margin-right: auto; margin-left: auto; }
/* Content */
.panel { margin:0px 10px 5px 10px; max-width:1100px; margin: 0 auto; font-size:14px;}
.panel a { color:inherit;}
/* Series Info */
.list { overflow:hidden; border: 1px solid #32A336; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; padding:6px 5px;}
.list > .title, .large > .title { margin:0px 0px 10px; position:relative; font-size:17px; padding: 6px 8px; color:#fff; background: #32A336 url("../img/slice.jpg"); background-size: auto 60px; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; text-shadow:none;}
.list > .title.fleft, .large > .title.fleft, .large >.title.fright { margin-right:10px; border:none; padding:0px; margin-bottom:0;}
.list .group { margin-top:8px; padding:0 2px 5px 5px; position:relative; overflow:hidden;}
.list .group > .title { font-weight:bold; font-size:16px; border-bottom:1px solid #666;}
.list .group > .title > .meta { font-weight:normal; font-size:11px;}
.list .element { padding: 3px 10px 3px 10px; overflow:hidden;}
.list .element .title,.list .element .sub  { float:left;}
.list .element .sub { font-weight: bold; margin-right: 4px;}
.list .element .meta_r { float:right; margin-top:2px;}
.list .element .meta_r a { font-size:120%;}
.list .group .preview { float:left; max-height:125px; margin-right:5px; position:relative; z-index:100;}
.list.series .group { min-height:70px; padding: 0 8px 8px;}
.list.series .meta_r { position:static; float:none; clear:both;}
.list .element .image { float:left; max-height:125px; margin-right:5px; position:relative; z-index:100;}
.large { padding:6px 5px; margin:0 0 10px; overflow:hidden;	border: 1px solid #32A336; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px;}
.large.comic.alert { color:#f5f5f5; text-shadow:none; background: rgba(255,0,0,0.4);}
.large.comic .title { font-size:20px;}
.thumbnail { padding: 3px; text-align:center;}
.large.comic .thumbnail { width:250px; height:auto; border: 1px solid #999; padding:3px; text-align:center; float:left; margin: 0px 20px 0px 5px;}
.large.comic .thumbnail img {max-width: 100%;}
.large.comic .info { margin:5px;}
.large.comic .info ul { list-style: none; padding:0 0 0 15px;}
.large.comic .info ul li { margin: 6px 0 0; text-indent:-13px;}
.series-info{ float: left; width: calc(100% - 310px);}
/* Sidebar */
.sidebar { float:right; margin:0 0 10px 10px; padding:0; width:300px; list-style: none;}
.sidebar li { margin: 0 0 10px; padding:6px 5px 10px; border: 1px solid #32A336; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px;}
.sidebar li h1,.sidebar li h3{ padding:3px 10px 3px 10px; line-height: 25px; position:relative; text-shadow:none; font-weight:normal; font-size:16px; color:#fff; height:35px; background: #32A336 url("../img/slice.jpg");background-size: auto 35px; border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px; margin: 0 0 7px;}
.sidebar .text { padding: 0 5px;}
.sidebar .manga_of_the_week {padding:2px;}
.sidebar .manga_of_the_week .series-cover{float:left; border:2px solid #32A336; margin-right:8px;}
.sidebar .cover{ margin: 4px auto; width:300px; text-align:center;}
.sidebar .cover img { border: 2px solid #32A336; }
.sidebar .most_popular li { border: none; padding: 0px; margin: 0px; }

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

.series-download{position:relative; margin: 10px auto; width: 500px; background: #fff;}
.series-download h1{ font-size: 16px; text-align: center;}

.list-group { padding-left: 0; margin-bottom: 20px;}
.list-group-item { position: relative; display: block; padding: 10px 15px; background-color: #fff; border: 1px solid #ddd; font-size: 14px;}
.list-group-item:first-child { border-top-left-radius: 4px; border-top-right-radius: 4px;}
.list-group-item:last-child { margin-bottom: 0; border-bottom-right-radius: 4px; border-bottom-left-radius: 4px;}
a.list-group-item { color: #000;}
a.list-group-item .list-group-item-heading { color: #333;}
a.list-group-item:hover,a.list-group-item:focus { color: #fff; text-decoration: none; background-color: #32A336; border-color: #32A336;  }
.list-group-item.active,.list-group-item.active:hover,.list-group-item.active:focus { z-index: 2; color: #fff; background-color: #32A336; border-color: #32A336;}
.chapter_list { height: 150px; border: 1px solid #32A336; border-radius: 5px; overflow: auto; }
</style>
</head>
<body>
<div class="container">	
<?php
if($page === "dashboard"){ ?>
<form action="?page=details" method="post" enctype="multipart/form-data" class="series-download">
<h1>Series Grabber</h1>	
<div class="u-form-group">
<label>Series Url</label>
<input type="text" name="series_url">
</div>
<div class="u-form-group">
<button type="submit">Submit</button>
</div>
</form>
<?php } else if($page === "details") { 

if($_POST){ 

$url = $_POST["series_url"];

$series = get_chapters($url);
?>

<form action="?page=pages" method="post" enctype="multipart/form-data" class="series-download">
<h1>Series Grabber</h1>		
<div class="u-form-group">
<label>Series Title : </label>
<input type="text" name="title" value="<?php echo $series["title"]; ?>">
</div>
<div class="u-form-group">
<label>Series Slug for Directory : </label>
<input type="text" name="slug" value="<?php echo $series["slug"]; ?>">
</div>
<div class="u-form-group chapter_list">
<ul class="list-group">
<?php foreach($series["chapter_list"] as $chapter_list){ ?>	
<li class="list-group-item"><input type="checkbox" value="<?php echo $chapter_list["url"]; ?>" name="chapter_list[]">Chapter <?php echo $chapter_list["title"]; ?></li>
<?php } ?>
</ul>
</div>
<div class="u-form-group">
<button type="submit">Download</button>
</div>
</form>
<?php } else { ?>

<h1>Invalid Response from Server.</h1>

<?php } ?>

<?php } else if($page === "pages") { 
	
$url = $_POST["chapter_list"];
$slug = $_POST["slug"];

if(count($url > 1)){
foreach($url as $chapter_url){
$get_pages = get_pages($chapter_url,$slug);
}
} else {
$get_pages = get_pages($url,$slug);
}

} else { ?>

<form action="?page=details" method="post" enctype="multipart/form-data" class="series-download">
<h1>Series Grabber</h1>	
<div class="u-form-group">
<label>Series Url<label>
<input type="text" name="series_url">
</div>
<div class="u-form-group">
<button type="submit">Submit</button>
</div>
</form>

<?php } ?>

</div><!-- Container -->
</body>
</html>







