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


function get_chapters($url){

if(!isset($url)){

die("Invaild Url");
}

$string = create_dom($url);

echo "Reading Url : $url <br>\r\n";

$msg = "";

preg_match_all("/<img class=\"manga_image\" src=\"(.*?)\" alt=\"(.*?)\" draggable=\"false\"\s\/>/i", $string, $images);

if(isset($image['1'])){

$i = 0;
foreach($image['1'] as $pages){
$i++;

$file_name = "Page_$1";

preg_match("/http:\/\/www.alphapolis.jp\/manga\/toaruosan\/(.*?)\/(\d+)\/images\/i", $pages, $get_chapters);

$series_slug = $get_chapters[1];

$chapter_number = $get_chapters[2];

$dest_series = "oh/$series_slug";

$dest_chapter = "$dest_series/$chapter_number";

$destination = "$dest_series/$chapter_number/$file_name";

if(file_exists($dest_series)) { mkdir($dest_series); }
if(file_exists($dest_chapter)) { mkdir($dest_chapter); }

echo "Scrapping Image : $i - $pages <br>\r\n";

$content = file_get_contents($pages);

if(file_put_contents($destination,$content)){

$msg = "$file_name has been Saved.";

}


} // End Foreach

} // End Checking Exists

}

//$range = array(1093,1125,1174,1211,1247);
$range = range(11,24);
$page_list = range(1,26);

foreach($range as $chapter_list){

$i = 0;
foreach($page_list as $pages) {
$i++;

if($i > 9){
$url = "http://www.alphapolis.jp/manga/toaruosan/toaruosan/11/images/0$pages.jpg";
} else {
$url = "http://www.alphapolis.jp/manga/toaruosan/toaruosan/11/images/00$pages.jpg";
}

$file_name = basename($url);

preg_match("/http:\/\/www.alphapolis.jp\/manga\/toaruosan\/(.*?)\/(\d+)\/images/i", $url, $get_chapters);

$series_slug = $get_chapters[1];

$chapter_number = $get_chapters[2];

$dest_series = "$series_slug";

$dest_chapter = "$dest_series/$chapter_number";

$destination = "$dest_series/$chapter_number/$file_name";

if(!file_exists($dest_series)) { mkdir($dest_series); }
if(!file_exists($dest_chapter)) { mkdir($dest_chapter); }

echo "Scrapping Image : $i - Page $pages <br>\r\n";

if(get_http_response_code($url) != "200"){
echo "Error on $i $url";
}else{
$content = file_get_contents($url);

if(!file_exists($destination)){

if(file_put_contents($destination,$content)){

$msg = "$file_name has been Saved.";
}
}
}

} // End Foreach

}