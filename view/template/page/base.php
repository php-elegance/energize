<?php
$__DATA['energize.styleVersion'] = md5(view('=library/assets/style/energize.css'));
$__DATA['energize.scriptVersion'] = md5(view('=library/assets/script/energize.js'));
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

    <link rel="stylesheet" href="/assets/style/energize.css?v=[#energize.styleVersion]">
    <script src="/assets/script/energize.js?v=[#energize.scriptVersion]"></script>
</head>

<body>
    [#content]
</body>

</html>