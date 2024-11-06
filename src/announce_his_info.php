<?php
require 'parts/auto-login.php';
if (isset($_POST['announcement_id'])) {
    $announcement_id = $_POST['announcement_id'];
} else {
    $redirect_url = 'https://aso2201203.babyblue.jp/Nomodon/src/announce_his.php';
    header("Location: $redirect_url");
    exit();
}
$ann_sql = $pdo->prepare('SELECT * FROM Notification WHERE announcement_id=?');
$ann_sql->execute([$announcement_id]);
$ann_row = $ann_sql->fetch(PDO::FETCH_ASSOC);
$send_user_id = $ann_row['send_person'];
$sent_tag_id = $ann_row['sent_tag'];
$title = $ann_row['title'];
$content = $ann_row['content'];
$send_time = $ann_row['sending_time'];
$user_sql = $pdo->prepare('SELECT * FROM Users WHERE user_id=?');
$user_sql->execute([$send_user_id]);
$user_row = $user_sql->fetch(PDO::FETCH_ASSOC);
$tag_sql = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_id=?');
$tag_sql->execute([$sent_tag_id]);
$tag_row = $tag_sql->fetch(PDO::FETCH_ASSOC);
$send_user_name = $user_row['user_name'];
$sent_tag_name = $tag_row['tag_name'];
?>
<sapn>アナウンス詳細</sapn><br>
<span><?php echo $title; ?></span><br>
<span><?php echo $content; ?></span><br>
<span><?php echo $send_time; ?></span><br>
<span>送信者：<?php echo $send_user_name; ?></span><br>
<span>宛先：<?php echo $sent_tag_name; ?></span><br>