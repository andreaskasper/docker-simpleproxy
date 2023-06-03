<?php

$_ENV["TARGET_HOST"] = $_ENV["TARGET_HOST"] ?? "example.com";

$str = file_get_contents("https://".$_ENV["TARGET_HOST"].$_SERVER["REQUEST_URI"]);
$str = str_replace("//".$_ENV["TARGET_HOST"]."/", "//".$_SERVER["HTTP_HOST"]."/", $str);

header("public, immutable, stale-while-revalidate=86400000, stale-if-error=86400000");
die($str);
exit();