<?php
/**
 * Created by PhpStorm.
 * User: joeba
 * Date: 9/16/2018
 * Time: 12:01 AM
 */

define('API_KEY', "Bearer 01puNkq9t_4wKTfYtYE_4NokaujpCizeSzaY8ll2rEYW0QDfHhTNgSySHhIRpNxn7hCSEJ72wLvhSexAZ8hp6uCSodEPA");
define('HEADERS', array(
    'Authorization: '.API_KEY,
    'Content-Type: application/json'
));
define('HEADERS_MULTI', array(
    'Authorization: '.API_KEY,
    'Content-Type: multipart/form-data'
));
define('HEADERS_GET', array(
    'Authorization: '.API_KEY,
    'Accept: application/vnd.rev.transcript.v1.0+json'
));

function submit_job_file($image){
    var_dump($image);
    $path = $image['tmp_name'];
    $uploaded_path = "/uploads/img.mp3";
    move_uploaded_file($path, $uploaded_path);
    var_dump($uploaded_path);
    $url = "https://api.rev.ai/revspeech/v1beta/jobs";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, HEADERS_MULTI);
    curl_setopt($ch, CURLOPT_POST,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('media'=>'@' . $uploaded_path, 'type'=>'audio/mp3'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    var_dump($result);
}

function submit_job_url($media_url){
    $url = "https://api.rev.ai/revspeech/v1beta/jobs";
    $fields = array(
        'media_url' => $media_url,
        'metadata' => "This is a sample submit jobs option"
    );
    $postfields = json_encode($fields);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, HEADERS);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    if($result){
        return json_decode($result)->id;
    }else{
        return false;
    }
}

function get_transcript($id){
    $url = "https://api.rev.ai/revspeech/v1beta/jobs/$id/transcript";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, HEADERS_GET);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result);
}

function summarize($text){
    $url = 'https://api.algorithmia.com/v1/algo/nlp/Summarizer/0.1.8';
    $apiKey = 'simVeHMWvc1HegdwTNkpem+O/fs1';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
        array('Content-Type: application/json', "Authorization: Simple $apiKey"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($text));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result)->result;
}

function wait($id){
    while(true){
        $resp = get_transcript($id);
        if(isset($resp->current_value)){
            if($resp->current_value == "failed"){
                return "Failed";
            }
            if($resp->current_value != "in_progress"){
                $monologues = $resp->monologues;
                $text = '';
                foreach ($monologues as $monologue){
                    $elements = $monologue->elements;
                    foreach ($elements as $element){
                        $text .= $element->value;
                    }
                }
                return $text;
            }
            sleep(10);
        }else{
            $monologues = $resp->monologues;
            $text = '';
            foreach ($monologues as $monologue){
                $elements = $monologue->elements;
                foreach ($elements as $element){
                    $text .= $element->value;
                }
            }
            return $text;
        }
    }
    return false;
}

function getDataURL($url){
    $host = 'localhost';
    $db   = 'briefly';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
    if($stmt = $pdo->prepare("select * from stored_images where url=:url;")){
        $stmt->bindParam(":url", $url);
        if($stmt->execute()){
            if($stmt->rowCount()>0){
                return $stmt->fetch();
            }
        }
    }

    $id = submit_job_url($url);
    $text = wait($id);
    $textSum = summarize($text);
    if($stmt = $pdo->prepare("insert into stored_images (url, text, summary) values (:url, :text, :summary);")){
        $stmt->bindParam(":url", $url);
        $stmt->bindParam(":text", $text);
        $stmt->bindParam(":summary", $textSum);
        $stmt->execute();
    }
    return array('url' => $url, 'text' => $text, 'summary' => $textSum);
}

function getDataImage($image){
    $host = 'localhost';
    $db   = 'briefly';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
    if($stmt = $pdo->prepare("select * from stored_images where url=:url;")){
        $stmt->bindParam(":url", $url);
        if($stmt->execute()){
            if($stmt->rowCount()>0){
                return $stmt->fetch();
            }
        }
    }

    $id = submit_job_file($image);
    $text = wait($id);
    $textSum = summarize($text);
    if($stmt = $pdo->prepare("insert into stored_images (url, text, summary) values (:url, :text, :summary);")){
        $stmt->bindParam(":url", $url);
        $stmt->bindParam(":text", $text);
        $stmt->bindParam(":summary", $textSum);
        $stmt->execute();
    }
    return array('url' => $url, 'text' => $text, 'summary' => $textSum);
}

//$url = "https://support.rev.com/hc/en-us/article_attachments/200043975/FTC_Sample_1_-_Single.mp3";
//echo (getDataURL($url)['text']);