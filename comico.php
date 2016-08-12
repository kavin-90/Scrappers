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

preg_match("/<a href=\"(.*?)?page=(.*?)\" class='_last'>››<\/a>/i", $full_list, $last_page);

// Get Page Links
//preg_match_all("/<li class=\"m-pager__item\"><a href=\"(.*)\">(.*)<\/a><\/li>/", $full_list, $page_list);
if(isset($last_page[2])){
setcookie("total_pages", $last_page[2]);
}


$last_page = isset($_COOKIE['total_pages']) ? $_COOKIE['total_pages'] : 0;

$page_list = range($last_page,1);

$msg = "";


foreach($page_list as $pager){

$new_url = "$url&page=$pager\r\n";

$chap_list = get_chapters($new_url);

}


return $msg;
}

// Get Chapters
function get_chapters($url){
$full_list = create_dom($url);

$chap_list = array();

if(empty($full_list)) { 
echo "Unable to Read Page: $url\r\n"; 
} else {

echo "Reading Page No. $url\r\n";

preg_match_all("/<a href=\"(.*?)\" class=\"m-thumb-episode__inner\" itemprop=\"url\">/i", $full_list, $chapter_list);

if(isset($chapter_list[1])){

foreach(array_reverse($chapter_list[1]) as $chapters){

$chap_url = $chapters;

$chap_list[] = $chap_url;

$get_images = get_images($chap_url);

} 
} else {
echo "No Chapters Found!";
}

}

return $chap_list;
}

// Get Images
function get_images($url){
$full_list = create_dom($url);

// All Series and Chapter and Images Storage Directory
$base_dir = "comico";

if(!file_exists("$base_dir")){
mkdir("$base_dir");
}

$msg = array();


// Series Name
preg_match("/<p class=\"m-title-hero02__title\" itemprop=\"name\">(.*?)<\/p>/is", $full_list, $series_data);
// Chapter Number
preg_match("/<a href=\"(.*?)\" class=\"_contribTitle\">(.*?)<\/a>/is", $full_list, $chap_data);
// Get Images
preg_match_all("/\s<img src=\"(.*?)\" alt=\"(.*?)\"\s/is", $full_list, $image_list);

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

if(isset($image_list[1])){
$i = 0;
foreach($image_list[1] as $images){
$i++;

if(!file_exists("$base_dir/$series_dir/$1 - $dir_name")){
mkdir("wfio://$base_dir/$series_dir/$i - $dir_name");
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

$url = "http://www.comico.jp/articleList.nhn?titleNo=1423";

$all_list = get_pagination($url);

echo $all_list;