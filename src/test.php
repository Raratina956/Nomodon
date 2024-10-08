<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/test.css">
    <title>Document</title>
</head>

<body>
    <header>
        <div class="header-area">
            <div class="hamburger">
                <!-- ハンバーガーメニューの線 -->
                <span></span>
                <span></span>
                <span></span>
                <!-- /ハンバーガーメニューの線 -->
            </div>
        </div>
        <ul class="slide-menu">
            <li>MAP</li>
            <li>ユーザー情報</li>
            <li>お気に入り</li>
            <li>QRカメラ</li>
            <li>チャット</li>
            <li>みんなのタグ</li>
            <li>MYタグ</li>
            <li>アナウンス</li>
            <div class="logout"><li>ログアウト</li></div>
        </ul>
    </header>

    <script>
        document.querySelector('.hamburger').addEventListener('click', function () {
            this.classList.toggle('active');
            document.querySelector('.slide-menu').classList.toggle('active');
        });
    </script>
</body>

</html>