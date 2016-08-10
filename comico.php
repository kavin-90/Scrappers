<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function explodeX($delimiters,$string) {
return explode(chr(1),str_replace($delimiters,chr(1),$string));
}

function sanitize($str,$pat){
return preg_replace($pat,"",$str);
}

function extract_numbers($string) {
preg_match_all('/([\d]+)/', $string, $match);
return $match[1][0];
}

function create_dom($url,$follow=1) {
$ch = curl_init();
curl_setopt( $ch, CURLOPT_USERAGENT, " Google Mozilla/5.0 (compatible; Googlebot/2.1;)" );
if($follow==1){
curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
}
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt( $ch, CURLOPT_REFERER, "http://www.google.com/bot.html" );
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9999999999999);
curl_setopt($ch, CURLOPT_TIMEOUT, 9999999999999);
$result = curl_exec($ch);
return $result;
}


// Get All Series

function get_mng($url){
$full_list = create_dom($url);

// Get Series Name and Url
preg_match_all("/<a href=\"(.*?)\" title=\"(.*?)\" itemprop=\"url\">/", $full_list, $output_array);

$msg = array();

if(isset($output_array[1],$output_array[2])){
$get_series_url = array_unique($output_array[1]);
$series_name = array_unique($output_array[2]);

$i = 0;
foreach($get_series_url as $series_url){
$i++;

// echo "$i $series_url\r\n";

get_pagination($series_url);

}

} else {
echo "Unable to find any Series";
exit;
}

return $msg;
}


// Get Pagination
function get_pagination($url){
$full_list = create_dom($url);

// http://www.comico.jp/articleList.nhn?titleNo=9600&page=3
preg_match("/<li class=\"m-pager__item m-pager__item--focus\"><a href=\"#\">(.*?)<\/a><\/li>/", $full_list, $pages);

preg_match_all("/<li class=\"m-pager__item\"><a href=\"(.*)\">(.*)<\/a><\/li>/", $full_list, $output_array);

$msg = array();

if(isset($output_array[1])){

$i = 0;

$new_array_value = str_replace("http://www.comico.jp","",$url);

array_unshift($output_array[1],"$new_array_value&page=1");

foreach(array_reverse($output_array[1]) as $pager){
$i++;

$last = end($output_array[1]);
$ah = explode("&page=",$last);
$total = $ah[1];

if($i === $total){

$replace_number = str_replace("&page=$total","&page=1",$pager);

$new_url = "http://www.comico.jp$replace_number";
}
else {
$new_url = "http://www.comico.jp$pager";	
}

$chap_list = get_chapters($new_url);

}

}

return $msg;
}

// Get Chapters
function get_chapters($url){
$full_list = create_dom($url);

preg_match_all("/<a href=\"(.*?)\" class=\"m-thumb-episode__inner\" itemprop=\"url\">/", $full_list, $output_array);

$chap_list = array();

foreach(array_reverse($output_array[1]) as $chapters){

$chap_url = $chapters;

$chap_list[] = $chap_url;

$get_images = get_images($chap_url);

}

return $chap_list;
}

// Get Images
function get_images($url){
$full_list = create_dom($url);

// All Series and Chapter and Images Storage Directory
$base_dir = "H:/Manga Raw High DB/comico";

$msg = array();


// Series Name
preg_match("/<p class=\"m-title-hero02__title\" itemprop=\"name\">(.*?)<\/p>/is", $full_list, $series_data);
// Chapter Number
preg_match("/<a href=\"(.*?)\" class=\"_contribTitle\">(.*?)<\/a>/is", $full_list, $chap_data);
// Get Images
preg_match_all("/\s<img src=\"(.*?)\" alt=\"(.*?)\"\s/is", $full_list, $output_array);

if(isset($series_data[1],$chap_data[2])){

$bad='/[\/:*?"<>|]/';


$series_dir = trim(sanitize($series_data[1],$bad));
$dir_name = trim(sanitize($chap_data[2],$bad));


// Return Series Name
echo "Good to Scrapping : <b>$series_dir</b><br>".PHP_EOL;
echo "Reading <b>$url</b><br>".PHP_EOL;
echo "Chapter <b>$dir_name</b><br>".PHP_EOL;

if(!file_exists("$base_dir/$series_dir")){
mkdir("wfio://$base_dir/$series_dir");
}

if(isset($output_array[1])){
$i = 0;
foreach($output_array[1] as $images){
$i++;

if(!file_exists("$base_dir/$series_dir/$dir_name")){
mkdir("wfio://$base_dir/$series_dir/$dir_name");
}


$extension = pathinfo($images, PATHINFO_EXTENSION);

if($i<10){
$save_file = "$base_dir/$series_dir/$dir_name/0$i.$extension";
} else {
$save_file = "$base_dir/$series_dir/$dir_name/$i.$extension";
}

if(!file_exists("wfio://$save_file")){

$content = file_get_contents($images);


// Check File Saved
if(!file_put_contents("wfio://$save_file", $content)){
echo "Failed to Download Image : <b>$images</b> <br>".PHP_EOL;
} else {
echo "Downloaded Image : <b>$images</b><br>".PHP_EOL;

$msg = "Status : <b>Success</b>".PHP_EOL;
}

}

}

}


}


return $msg;
}

$url = "http://www.comico.jp/articleList.nhn?titleNo=1300";
//$all_list = get_pagination($url);



$range = range(101,120);

$chapter_dl = "";

foreach($range as $new_url){

$url = "http://www.comico.jp/detail.nhn?titleNo=311&articleNo=$new_url";

$chapter_dl = get_images($url);


}







