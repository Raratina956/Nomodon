<?php
    require 'parts/auto-login.php';
    require 'header.php';
    
?>

   
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="css/user.css" media="screen and (min-width: 1280px)">
        <link rel="stylesheet" type="text/css" href="mob_css/user-mob.css" media="screen and (max-width: 480px)">

        <title>Document</title>
       
    </head>
       
        <body>
<?php
    //フォロー・フォロワー機能
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $follower_id = $_POST['user_id'];
        $follow_id = $_SESSION['user']['user_id'];

        if (isset($_POST['action']) && $_POST['action'] == 'follow') {
            // フォローを追加
            $sql = $pdo->prepare('insert into Favorite (follow_id, follower_id) values (?, ?)');
            $sql->execute([$follow_id, $follower_id]);
        } elseif (isset($_POST['action']) && $_POST['action'] == 'unfollow') {
            // フォローを解除
            $sql = $pdo->prepare('delete from Favorite where follow_id=? and follower_id=?');
            $sql->execute([$follow_id, $follower_id]);
        }

        // リダイレクトして同じページを再読み込み
        header('Location: user.php?user_id=' .$_POST['user_id']);
        exit();
    }

   
    //ユーザー情報を持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    // $users->execute([$_SESSION['user']['user_id']]);
    $users->execute([$_GET['user_id']]);
    
    //アイコン情報を持ってくる
    $iconStmt=$pdo->prepare('select icon_name from Icon where user_id=?');
    $iconStmt->execute([$_GET['user_id']]);
    $icon = $iconStmt->fetch(PDO::FETCH_ASSOC);


    //DBから持ってきたユーザー情報を「$user」に入れる
    foreach($users as $user){

        //自分か相手側かで表示する内容を変更
        if($_SESSION['user']['user_id'] == ($user['user_id'])){
            //自分のプロフィール

            
            //編集ボタン
            echo '<button class="confirmbutton" onclick="location.href=\'useredit.php\'">編集</button>';
            //アイコン表示
            echo '<div class="profile-container">';
            echo '<div class="user-container">';
            echo '<img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon">';

         
      

            //ユーザー情報
            if($user['s_or_t'] == 0){
                //クラスを持ってくる
                $classtagStmt=$pdo->prepare('select * from Classtag_attribute where user_id=?');
                $classtagStmt->execute([$_SESSION['user']['user_id']]);
                $classtag = $classtagStmt->fetch();
    
                $classtagnameStmt=$pdo->prepare('select * from Classtag_list where classtag_id=?');
                $classtagnameStmt->execute([$classtag['classtag_id']]);
                $classtagname = $classtagnameStmt->fetch();

                echo '<div class="profile">';
                //生徒(名前、クラス、メールアドレス)
                echo '名前：',$user['user_name'],"<br>";
                echo 'クラス：', $classtagname['classtag_name'], '<br>';
                echo $user['mail_address'],"<br>";
                echo '</div>';
            }else{
                //先生(名前、メールアドレス)
                echo '<div class="profile"><br>';
                echo '名前：',$user['user_name'], "先生<br>";
                echo $user['mail_address'];
                echo '</div>';
            }

            echo '</div>';
            echo '</div>';
            echo '<br>';
            //タグ情報を「$_SESSION['user']['user_id']」を使って持ってくる
            echo '<div class="tag">';
            $attribute=$pdo->prepare('select * from Tag_attribute where user_id=?');
            $attribute->execute([$_SESSION['user']['user_id']]);
            $attributes = $attribute->fetchAll(PDO::FETCH_ASSOC);

            echo 'タグ一覧<br><br>';
            foreach($attributes as $tag_attribute){
                $tagStmt=$pdo->prepare('select * from Tag_list where tag_id=?');
                $tagStmt->execute([$tag_attribute['tag_id']]);
                $tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

                //タグ一覧
                foreach($tags as $tag){
                    echo $tag['tag_name'];
                    echo '&emsp;';
                }
                
            }
            echo '</div>';
        }else{
            //相手のプロフィール
            //チャットボタン表示
            echo '<div class="profile-container">';
            echo '<div class="favorite-container">';
            echo '<button type="submit" class="star">';
            echo '<img src="img\chat.png" width="85%" height="100% class="chat">';
            echo '</button>';

            //お気に入りボタン表示
            $followStmt=$pdo->prepare('select * from Favorite where follow_id=? and follower_id=?');
            $followStmt->execute([$_SESSION['user']['user_id'], $_GET['user_id']]);
            $follow = $followStmt->fetch();
            if($follow){
                echo '<form action="user.php" method="post">
                        <input type="hidden" name="user_id" value=', $_GET['user_id'], '>
                        <input type="hidden" name="action" value="unfollow">
                        <button type="submit" class="star">
                            <img src="img\star.png" width="85%" height="100%">
                        </button>
                      </form><br>';
            }else{
                echo '<form action="user.php" method="post">
                        <input type="hidden" name="user_id" value=', $_GET['user_id'], '>
                        <input type="hidden" name="action" value="follow">
                        <button type="submit">
                            <img src="img\notstar.png" width="85%" height="100%" class="star">
                        </button>
                      </form><br>';
            }
            echo '</div>';

            //アイコン表示
            echo '<div class="user-container">';
            echo '<img src="', $icon['icon_name'], '" width="20%" height="50%" class="usericon"><br>';

            //ユーザー情報
            if($user['s_or_t'] == 0){
                //クラスを持ってくる
                $classtagStmt=$pdo->prepare('select * from Classtag_attribute where user_id=?');
                $classtagStmt->execute([$_GET['user_id']]);
                $classtag = $classtagStmt->fetch();
    
                $classtagnameStmt=$pdo->prepare('select * from Classtag_list where classtag_id=?');
                $classtagnameStmt->execute([$classtag['classtag_id']]);
                $classtagname = $classtagnameStmt->fetch();

                //生徒(名前、クラス、メールアドレス)
                echo '<div class="profile">';
                echo '名前：',$user['user_name'],"<br>";
                echo 'クラス：', $classtagname['classtag_name'], '<br>';
                echo $user['mail_address'],"<br>";
                echo '</div>';
            }else{
                //先生(名前、メールアドレス)
                echo '<div class="profile">';
                echo '名前：',$user['user_name'], "先生<br>";
                echo $user['mail_address'],"<br>";
                echo '</div>';
            }

            echo '</div>';
            echo '</div>';
            
            //タグ情報を「$_SESSION['user']['user_id']」を使って持ってくる
            echo '<div class="tag">';
            echo 'タグ一覧<br>';
         
            $attribute=$pdo->prepare('select * from Tag_attribute where user_id=?');
            $attribute->execute([$_SESSION['user']['user_id']]);
            $attributes = $attribute->fetchAll(PDO::FETCH_ASSOC);
            foreach($attributes as $tag_attribute){
                $tagStmt=$pdo->prepare('select * from Tag_list where tag_id=?');
                $tagStmt->execute([$tag_attribute['tag_id']]);
                $tags = $tagStmt->fetchAll(PDO::FETCH_ASSOC);

                //タグ一覧
                foreach($tags as $tag){
                    echo $tag['tag_name'];
                    echo '&emsp;';
                }
              
            }
            echo '</div>';
        }
    }
?>
</body>
</html>