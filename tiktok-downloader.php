<?php

/**
 * Tiktok Downloader
 *
 *
 * @wordpress-plugin
 * Plugin Name: Tiktok Downloader
 * Plugin URI:  http://belajarapaaja.net
 * Description: This plugin to download tiktok through copied URL
 * Version:     1.0.0
 * Author:      Tubagus Putra Kencana
 * Author URI:  https://belajarapaaja.net
 * Text Domain: tiktok-downloader
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */


function tiktok_input()
{
  $store_locally = true;
  $post = false;

  // process when user push button generate
  if (isset($_POST['tiktok-url']) && !empty($_POST['tiktok-url'])) {
    $post = true;
    $url = trim($_POST['tiktok-url']);
    $resp = getContent($url);
    $check = explode('"downloadAddr":"', $resp);

    // checking address
    if (count($check) > 1) {
      $contentURL = explode("\"", $check[1])[0];
      $contentURL = str_replace("\\u0026", "&", $contentURL);
      $contentURL = str_replace("\\u002F", "/", $contentURL);

      // create folder
      if (!file_exists("wp-content/uploads/tiktok_downloader") && $store_locally) {
        mkdir("wp-content/uploads/tiktok_downloader");
      }

      // download video and generate link for download button
      if ($store_locally) {
        $name = get_site_url() . "/" . downloadVideo($contentURL);
      }
    }
  }

  // output at front end after user put their shortcode anywhere
  $output =
    '<form method="POST" >
      <input type="text" placeholder="https://www.tiktok.com/@username/video/123456" style="width:100%"  name="tiktok-url"><br><br>
      <button class="btn btn-success" type="submit">Generate</button>
   </form>
  ';

  // when crawling are done then 'download' button appear
  if ($post) {
    $output .= '<button id="wmarked_link" class="btn btn-primary mt-3" onclick="window.location.href=\'' . $name . '\'">Download Video</button> ';
  }

  return $output;
}

function generateRandomString($length = 10)
{
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }

  return $randomString;
}

function downloadVideo($video_url, $geturl = false)
{
  $ch = curl_init();
  $headers = array(
    'Range: bytes=0-',
  );
  $options = array(
    CURLOPT_URL            => $video_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => false,
    CURLOPT_HTTPHEADER     => $headers,
    CURLOPT_FOLLOWLOCATION => true,
    CURLINFO_HEADER_OUT    => true,
    CURLOPT_USERAGENT => 'okhttp',
    CURLOPT_ENCODING       => "utf-8",
    CURLOPT_AUTOREFERER    => true,
    CURLOPT_COOKIEJAR      => 'cookie.txt',
    CURLOPT_COOKIEFILE     => 'cookie.txt',
    CURLOPT_REFERER        => 'https://www.tiktok.com/',
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_MAXREDIRS      => 10,
  );
  curl_setopt_array($ch, $options);
  if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  }

  // getting video from url
  $data = curl_exec($ch);
  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if ($geturl === true) {
    return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
  }
  curl_close($ch);

  // set filename and process writing into server
  $filename = "wp-content/uploads/tiktok_downloader/" . generateRandomString() . ".mp4";
  $d = fopen($filename, "w");
  fwrite($d, $data);
  fclose($d);


  return $filename;
}


function getContent($url, $geturl = false)
{
  $ch = curl_init();
  $options = array(
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER         => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Mobile Safari/537.36',
    CURLOPT_ENCODING       => "utf-8",
    CURLOPT_AUTOREFERER    => false,
    CURLOPT_COOKIEJAR      => 'cookie.txt',
    CURLOPT_COOKIEFILE     => 'cookie.txt',
    CURLOPT_REFERER        => 'https://www.tiktok.com/',
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_MAXREDIRS      => 10,
  );
  curl_setopt_array($ch, $options);
  if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
  }
  $data = curl_exec($ch);
  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  if ($geturl === true) {
    return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
  }
  curl_close($ch);
  return strval($data);
}
add_shortcode('tiktok-downloader', 'tiktok_input');
