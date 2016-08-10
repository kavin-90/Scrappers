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

function get_chapters($url){
$string = create_dom($url);

preg_match_all("/<a href=\"(.*?)\" class=\"(button|read)\">(.*?)<\/a>/", $string, $chapters);

if(isset($chapters[1])){
foreach($chapters[1] as $chapter_url){

$chapter_url = "https://www.sunday-webry.com/$chapter_url";

echo "Reading : $chapter_url <br/>\r\n";

get_page($chapter_url);

}	

} else {
echo "No Chapters Found!";
}

}


function get_page($url){

if(!isset($url)){

die("Invalid Url");
}

echo "Scrapping: $url<br>\r\n";

$string = create_dom($url);

// Get Key
preg_match("/key: \"(.*?)\",/", $string, $key);
// Get Series Slug
preg_match("/manga_slug: \"(.*?)\",/", $string, $series_info);
// Get Chapter Slug
preg_match("/episode_slug: \"(.*?)\",/", $string, $chapter_info);

if(isset($key[1])){
$key = $key[1];
} else {
die("No Images found.");
}

$series_slug = $series_info[1];
$chapter_slug = $chapter_info[1];

$json_file = "https://www.sunday-webry.com/assets/episodes/$key/episode.json";

echo "Reading Image List : $json_file <br/>\r\n";

$json_url  = file_get_contents($json_file);

$data = json_decode($json_url, true);

if(isset($data['pages'])){

// Create Series Folder
if(!file_exists($series_slug)){
mkdir($series_slug);
}
// Create Chapter Folder
if(!file_exists("$series_slug/$chapter_slug")){
mkdir("$series_slug/$chapter_slug");
}


$save_path = "$series_slug/$chapter_slug";

$i = 0;
foreach($data['pages']as $json_data){
$i++;
$image_path = isset($json_data['files']['h1536.jpeg']) ? $json_data['files']['h1536.jpeg'] : $json_data['files']['h1024.jpeg'];
$image_url = "https://www.sunday-webry.com/assets/episodes/$key/$image_path";

$content = file_get_contents($image_url);

if(file_put_contents("$save_path/$image_path",$content)){

echo "Page $i saved <br>\r\n";

} else {

echo "Unable to save Page $i <br>\r\n";
}

} // Foreach

} // Check Images Exists

}



$get_url = "https://www.sunday-webry.com/comics/bokutachitsukiattemasu/";


echo get_chapters($get_url);
