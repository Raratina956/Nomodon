<?php
if (isset($_POST['logout'])) {
    // ユーザー情報をセッションから削除
    unset($_SESSION['user']);

    // データベースからトークンを削除
    if (isset($_COOKIE['remember_me_token'])) {
        $token = $_COOKIE['remember_me_token'];

        // トークンをデータベースから削除
        $sql_delete_token = $pdo->prepare('DELETE FROM Login_tokens WHERE token = ?');
        $sql_delete_token->execute([$token]);
    }

    // クッキーを削除
    setcookie('remember_me_token', '', time() - 3600, "/"); // 過去の時間に設定

    // ログイン画面にリダイレクト
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/login.php';
    header("Location: $redirect_url");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="mob_css/header-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" type="text/css" href="css/header.css" media="screen and (min-width: 1280px)">
    <link rel="stylesheet" href="mob_css/humberger-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/test.css" media="screen and (min-width: 1280px)">
</head>
<header>
    <div class="header-container">
        <a href="main.php" class="icon">
            <img src="img/icon.png" width="460" height="80" class="spot">
        </a>

        <div class="right-elements">
            <?php
            $list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=? AND read_check=?');
            $list_sql->execute([$_SESSION['user']['user_id'], 0]);
            $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <a href="info.php">
                <img src="<?= $list_raw ? 'img/newinfo.png' : 'img/bell.png'; ?>" class="bell" width="50" height="50">
            </a>

            <div class="header-area">
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- スライドメニュー -->
    <div class="slide-menu">
        <!-- メニューリスト -->
        <ul>
            <li>
                <form action="search.php" method="post">
                    <input type="text" name="search" class="tbox">
                    <input type="submit" class="search1" value="検索">
                </form>
            </li>
            <li><a href="map.php">MAP</a></li>
            <?php echo '<li><a href="user.php?user_id=', $_SESSION['user']['user_id'], '">自分のプロフィール</a></li>'; ?>
            <li><a href="favorite.php">お気に入り</a></li>
            <li><a href="qr_read.php">QRカメラ</a></li>
            <?php echo '<li><a href="chat-home.php?user_id=', $_SESSION['user']['user_id'], '">チャット</a></li>'; ?>
            <li><a href="tag_list.php">みんなのタグ</a></li>
            <li><a href="my_tag.php">MYタグ</a></li>
            <li><a href="announce.php">アナウンス</a></li>
            <!-- 以下ログアウト -->
            <form id="myForm" action="" method="post">
                <input type="hidden" name="logout" value="1">
            </form>
            <li><a href="#" id="submitLink">ログアウト</a></li>
            <script>
                document.getElementById('submitLink').addEventListener('click', function (event) {
                    event.preventDefault(); // リンクのデフォルトの動作を防止
                    // 現在のURLを取得
                    var currentUrl = window.location.href;
                    // フォームのactionに現在のURLを設定
                    document.getElementById('myForm').action = currentUrl;
                    // フォームを送信
                    document.getElementById('myForm').submit();
                });
            </script>

            <!-- 以上ログアウト -->
        </ul>
    </div>
    <script>
        document.querySelector('.hamburger').addEventListener('click', function () {
            this.classList.toggle('active');
            document.querySelector('.slide-menu').classList.toggle('active');
        });
    </script>
</header>