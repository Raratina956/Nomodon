<?php
session_start(); // セッションを開始

require "db-connect.php";
try {
    $pdo = new PDO("mysql:host=" . SERVER . ";dbname=" . DBNAME, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch(PDOException $e){
    echo "接続エラー: " . $e->getMessage();
    exit();  
}

// URLから相手のuser_idを取得
$partner_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// ログイン中のユーザーIDをセッションから取得
$logged_in_user_id = isset($_SESSION['user']['user_id']) ? $_SESSION['user']['user_id'] : null;

// user_idが取得できない場合の処理
if ($partner_id === null || $logged_in_user_id === null) {
    echo "ユーザーIDが指定されていません。";
    exit();
}

// メッセージを取得する関数
function getMessages($pdo, $logged_in_user_id, $partner_id) {
    $sql = "SELECT send_id, sent_id, message_detail, message_time 
            FROM Message 
            WHERE (send_id = :logged_in_user_id AND sent_id = :partner_id)
               OR (send_id = :partner_id AND sent_id = :logged_in_user_id)
            ORDER BY message_time ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':logged_in_user_id', $logged_in_user_id, PDO::PARAM_INT);
    $stmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 相手の情報を取得
$sql = "SELECT user_name FROM Users WHERE user_id = :partner_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':partner_id', $partner_id, PDO::PARAM_INT);
$stmt->execute();
$partner = $stmt->fetch(PDO::FETCH_ASSOC);

// メッセージ送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $send_id = $logged_in_user_id; // セッションから送信者のIDを取得
    $sent_id = $partner_id; // 受信者のIDはリンクから取得した相手のID
    $message_detail = $_POST['text'];
    $message_time = date('Y/m/d H:i:s');

    $sql = "INSERT INTO Message (send_id, sent_id, message_detail, message_time) 
            VALUES (:send_id, :sent_id, :message_detail, :message_time)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':send_id', $send_id);
    $stmt->bindParam(':sent_id', $sent_id);
    $stmt->bindParam(':message_detail', $message_detail);
    $stmt->bindParam(':message_time', $message_time);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        $error_info = $stmt->errorInfo(); 
        echo "登録に失敗しました: " . $error_info[2]; 
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>チャット</title>
    <link rel="stylesheet" href="css/chat2.css">
</head>
<body>
<div class="chat-system">
    <div class="chat-box">

        <!-- 相手のアイコンと名前表示部分 -->
        <div class="chat-header">
        <?php echo "<img src='image/{$partner_id }.png'>";  ?>
            <span class="partner-name"><?php echo htmlspecialchars($partner['user_name']); ?></span>
        </div>

        <!-- 広告バナー -->
        <div class="ad-banner" id="ad-banner">
            <a href="https://aso2201195.boo.jp/zonotown/top.php" target="_blank">
                <img src="image/banner.png" alt="広告バナー" class="ad-image">
            </a>
        </div>

        <div class="chat-area" id="chat-area">
            <?php 
            // 指定した相手とのチャット履歴を取得して表示
            $messages = getMessages($pdo, $logged_in_user_id, $partner_id);
            foreach ($messages as $message): ?>
                <?php $class = ($message['send_id'] == $logged_in_user_id) ? 'person1' : 'person2'; ?>
                <div class="<?php echo $class; ?>">
                    <div class="chat">
                        <small class="chat-time"><?php echo htmlspecialchars($message['message_time']); ?></small>
                        <span><?php echo htmlspecialchars($message['message_detail']); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- メッセージ送信フォーム -->
        <form class="send-box flex-box" action="chat.php?user_id=<?php echo htmlspecialchars($partner_id); ?>#chat-area" method="post">
            <textarea id="textarea" name="text" rows="1" required placeholder="message.."></textarea>
            <input type="submit" name="submit" value="送信" id="submit">
        </form>

        <!-- トップページに戻るボタン -->
        <form action="chat-home.php" method="GET">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['user']['user_id']); ?>">
            <input class="btn back-btn" type="submit" value="Topページに戻る">
        </form>

    </div>
</div>

<!-- フェードインのスクリプト -->
<script>
    window.addEventListener('load', function() {
        const adBanner = document.getElementById('ad-banner');
        setTimeout(() => {
            adBanner.classList.add('show'); // 3秒後にバナーにshowクラスを追加してフェードインさせる
        }, 3000); // 3000ミリ秒 = 3秒
    });
</script>

</body>
</html>
