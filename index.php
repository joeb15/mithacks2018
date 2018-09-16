<?php
/**
 * Created by PhpStorm.
 * User: joeba
 * Date: 9/15/2018
 * Time: 10:06 PM
 */

include_once 'revapi.php';

$text = $summary = $percent = "";

if(!empty(trim($_POST['file']))){
    $parts = getDataURL($_POST['file']);
    $text = $parts['text'];
    $summary = $parts['summary'];
    $percent = round(strlen($summary)*100/strlen($text), 1);
    unset($_POST['file']);
}

?>

<!doctype html>
<html lang="en">
<head>
    <title>Briefly</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <a class="navbar-brand"><img src="briefly.svg" height="40" /></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="/">Home <span class="sr-only">(current)</span></a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="bg-image">
        <div id="text" class="center <?php if(empty($text))echo "display-none"?>">
            <div class="frow">
                <p>Summary of Audio</p>
                <p><?php echo "$percent% of original size."?></p>
            </div>
            <p class="outline"><?php echo $summary?></p>
        </div>
        <form id="upload" <?php if(!empty($text))echo "class='display-none'"?> action="#" method="POST" enctype="multipart/form-data" class="center">
            <img src="briefly.svg" class="logo">
            <p>Enter an Audio File to Summarize.</p>
            <input id="input-id" type="text" name="file" class="file">
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script type="text/javascript"></script>
</body>
</html>