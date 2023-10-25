<?php
$__DATA['front.styleVersion'] = md5(view('=public/style/front.css'));
$__DATA['front.scriptVersion'] = md5(view('=public/script/front.js'));
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="[#head.description]" />
    <link rel="icon" type="image/x-icon" href="[#head.favicon]" />
    <title>[#head.title]</title>

    <link rel="stylesheet" href="/assets/style/front.css?v=[#front.styleVersion]">
    <script src="/assets/script/front.js?v=[#front.scriptVersion]"></script>
</head>

<body>
    [#content]
</body>

</html>