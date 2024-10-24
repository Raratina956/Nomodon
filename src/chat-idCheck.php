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

// 検索フォームから入力された値を取得
$search_keyword = isset($_POST['search_keyword']) ? $_POST['search_keyword'] : null;

// ユーザー検索関数
function searchUsers($pdo, $search_keyword) {
    $sql = "SELECT user_id, user_name FROM Users WHERE user_id = :keyword OR user_name LIKE :keyword_like";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':keyword', $search_keyword, PDO::PARAM_INT);
    $stmt->bindValue(':keyword_like', '%' . $search_keyword . '%', PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー検索</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
if ($search_keyword) {
    // 検索機能を実行
    $search_results = searchUsers($pdo, $search_keyword);

    if (!empty($search_results)) {
        echo "<h3>検索結果:</h3>";
        foreach ($search_results as $user) {
            // ユーザー名をリンク化して表示
            echo '<p><a href="chat.php?user_id=' . htmlspecialchars($user['user_id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8') . '</a></p>';
        }
    } else {
        echo "<p>該当するユーザーが見つかりません。</p>";
    }
}
?>

</body>
</html>
