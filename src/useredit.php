<?php
    require 'parts/auto-login.php';
    require 'header.php';

    //ユーザー情報を「$_SESSION['user']['user_id']」を使って持ってくる
    $users=$pdo->prepare('select * from Users where user_id=?');
    $users->execute([$_SESSION['user']['user_id']]);

    //formを送る先を指定
    echo '<form id="uploadForm" action="useredit-output.php" method="post" enctype="multipart/form-data">';

    foreach($users as $user){
        //先生か生徒か判別
        if($user['s_or_t'] == 0){
            //生徒情報編集
            //アイコン編集
            $iconStmt = $pdo->prepare('select * from Icon where user_id = ?');
            $iconStmt->execute([$_SESSION['user']['user_id']]);
            $icon = $iconStmt->fetch();
            if ($icon) {
                echo '<img id="existingIcon" src="', $icon['icon_name'], '" class="icon">';
            }
?>
            <link rel="stylesheet" type="text/css" href="css/useredit.css">
            <input type="file" id="fileInput" name="icon_file" accept=".jpg"><br>
            <img id="preview" src="#" alt="Preview" style="display:none;"><br>
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user']['user_id']; ?>">

<?php
            //名前変更
            echo '名前：<input type="text" name="user_name" value="', $user['user_name'], '"><br>';
?>
            <!-- クラス変更 -->
            <label for="class">クラス：</label>
            <select name="class" id="class">
                <?php
                    $classtagStmt=$pdo->prepare('select * from Classtag_attribute where user_id=?');
                    $classtagStmt->execute([$_SESSION['user']['user_id']]);
                    $classtag = $classtagStmt->fetch();

                    $classtagnameStmt=$pdo->prepare('select * from Classtag_list where classtag_id=?');
                    $classtagnameStmt->execute([$classtag['classtag_id']]);
                    $classtagname = $classtagnameStmt->fetch();

                    echo '<option value="', $classtag['classtag_id'], '" selected hidden>', $classtagname['classtag_name'], '</option>';


                    $classStmt=$pdo->query('select * from Classtag_list');
                    foreach($classStmt as $class){
                        echo '<option value="', $class['classtag_id'], '">', $class['classtag_name'], '</option>';
                    }
                ?>
            </select>
  <?php          
        }
    }
?>
    <button type="button" id="uploadButton">保存</button>
    <script>
    document.getElementById('fileInput').onchange = function (event) {
        var reader = new FileReader();
        reader.onload = function () {
            var existingIcon = document.getElementById('existingIcon');
            var preview = document.getElementById('preview');
            
            if (existingIcon) {
                existingIcon.src = reader.result;  // 既存のアイコンを置き換える
            } else {
                preview.src = reader.result;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    };

    document.getElementById('uploadButton').onclick = function () {
        var form = document.getElementById('uploadForm');
        if (form) {
            form.submit();
        } else {
            console.error('uploadForm not found');
        }
    };
    </script>
    </form>


