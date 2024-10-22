<?php
require 'parts/auto-login.php';
function timeAgo($logtime)
{
    if (empty($logtime)) {
        return '日時が設定されていません。'; // 空の日時に対する処理
    }
    $now = new DateTime(); // 現在時刻
    $ago = new DateTime($logtime); // 保存された日時
    $diff = $now->diff($ago); // 差分を取得

    // 差分を秒単位で計算
    $diffInSeconds = $now->getTimestamp() - $ago->getTimestamp();

    // 結果の判定
    if ($diffInSeconds < 3600) { // 1時間以内なら
        // 分数で表示
        $minutes = floor($diffInSeconds / 60);
        return $minutes . '分前';
    } elseif ($diffInSeconds < 86400) { // 24時間以内なら
        // 時間数で表示
        $hours = floor($diffInSeconds / 3600);
        return $hours . '時間前';
    } else {
        // 日数で表示
        $days = floor($diffInSeconds / 86400);
        return $days . '日前';
    }
}
?>
<?php
require 'header.php';
?>
<link rel="stylesheet" href="css/info.css">
<h1>お知らせ</h1>
<?php
// Announce_check参照
$list_sql = $pdo->prepare('SELECT * FROM Announce_check WHERE user_id=?');
$list_sql->execute([$_SESSION['user']['user_id']]);
$list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);
if ($list_raw) {
    ?>
    <form action="info.php" method="post">
        <label>種別</label>
        <select name="narrow">
            <option value=0 selected>全て</option>
            <option value=1>アナウンス</option>
            <option value=2>位置情報</option>
        </select>
        <label>ユーザー別</label>
        <select name="n_user">
            <option value="0" selected>全て</option>
            <?php
            $n_users = [];
            foreach ($list_raw as $row) {
                // アナウンス
                $n_announcement_id = $list_raw['announcement_id'];
                $n_announce_s = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
                $n_send_person_id = $list_raw['send_person'];
                $n_users = $n_send_person_id;

                // 位置情報
                $n_current_location_id = $list_raw['current_location_id'];
                
                $list_sql->execute([$_SESSION['user']['user_id']]);
                $list_raw = $list_sql->fetchAll(PDO::FETCH_ASSOC);



            }
            ?>
        </select>
        <input type="submit" value="検索">
    </form>
    <?php
    echo '<table>';
    if (isset($_POST['narrow'])) {
        $narrow = $_POST['narrow'];
    } else {
        $narrow = 0;
    }
    foreach ($list_raw as $row) {
        switch ($row['type']) {
            case 1:
                if ($narrow == 0 or $narrow == 1) {
                    $announcement_id = $row['announcement_id'];
                    $read_check = $row['read_check'];
                    $info_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
                    $info_sql->execute([$announcement_id]);
                    $info_row = $info_sql->fetch();
                    $send_id = $info_row['send_person'];
                    $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
                    $user_sql->execute([$send_id]);
                    $user_row = $user_sql->fetch();
                    $send_name = $user_row['user_name'];
                    $title = $info_row['title'];
                    $content = $info_row['content'];
                    $logtime = $info_row['sending_time'];
                    echo '<tr>';
                    echo '<td>アイコン（仮）</td>';
                    echo '<td rowspan="1">', $send_name, 'さんから、アナウンスが届きました</td>';
                    if ($read_check == 0) {
                        echo '<td>未読</td>';
                    }
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>', timeAgo($logtime), '</td>';
                    echo '<td>', $title, '</td>';
                    ?>
                    <form action="info_detail.php" method="post">
                        <input type="hidden" name="announcement_id" value=<?php echo $announcement_id; ?>>
                        <td><input type="submit" value="詳細"></td>
                    </form>
                    <?php
                }
                break;
            case 2:
                // 位置情報
                if ($narrow == 0 or $narrow == 2) {
                    $current_location_id = $row['current_location_id'];
                    $read_check = $row['read_check'];
                    $info_sql = $pdo->prepare('SELECT * FROM Current_location WHERE current_location_id=?');
                    $info_sql->execute([$current_location_id]);
                    $info_row = $info_sql->fetch();
                    $send_id = $info_row['user_id'];
                    $user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
                    $user_sql->execute([$send_id]);
                    $user_row = $user_sql->fetch();
                    $send_name = $user_row['user_name'];
                    $logtime = $info_row['logtime'];
                    echo '<tr>';
                    echo '<td>アイコン</td>';
                    echo '<td rowspan="2">', $send_name, 'さんが位置情報を更新しました</td>';
                    if ($read_check == 0) {
                        echo '<td>未読</td>';
                    }
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td>', timeAgo($logtime), '</td>';
                    ?>
                    <form action="info_detail.php" method="post">
                        <input type="hidden" name="current_location_id" value=<?php echo $current_location_id; ?>>
                        <td><input type="submit" value="詳細"></td>
                    </form>
                    <?php
                }
                break;
            default:
                echo 'その他';
                break;
        }
    }
    echo '</table>';
} else {
    echo 'お知らせがありません';
}
?>