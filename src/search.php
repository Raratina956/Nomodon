<?php
require 'parts/auto-login.php';
$search_text = $_POST['search'];
unset($dis);
$user_data = [];
$tag_data = [];
$judge = 0;
if (isset($_POST['kinds'])) {
    $kinds = $_POST['kinds'];
} else {
    $kinds = "a";
}
if (isset($_POST['method'])) {
    $method = $_POST['method'];
} else {
    $method = "part";
}
if ($kinds == "a" || $kinds == "u") {
    // ユーザー検索
    // 部分一致
    if ($method == "part") {
        $search_all_u = $pdo->prepare('SELECT * FROM Users WHERE user_name LIKE ?');
        $search_all_u->execute(['%' . $search_text . '%']);
        $search_all_u_re = $search_all_u->fetchAll(PDO::FETCH_ASSOC);
        if ($search_all_u_re) {
            foreach ($search_all_u_re as $search_all_u_row) {
                $user_data[] = [
                    'type' => 'user',
                    'id' => $search_all_u_row['user_id'],
                    'name' => $search_all_u_row['user_name']
                ];
            }
        }
    } else {
        // 完全一致
        $search_all_u = $pdo->prepare('SELECT * FROM Users WHERE user_name=?');
        $search_all_u->execute([$search_text]);
        $search_all_u_re = $search_all_u->fetchAll(PDO::FETCH_ASSOC);
        if ($search_all_u_re) {
            // 条件指定なし検索ユーザー(結果あり)
            foreach ($search_all_u_re as $search_all_u_row) {
                $user_data[] = [
                    'type' => 'user',
                    'id' => $search_all_u_row['user_id'],
                    'name' => $search_all_u_row['user_name']
                ];
            }
        }
    }
}
if ($kinds == "a" || $kinds == "t") {
    if ($method == "part") {
        // タグ検索
        // 部分一致
        $search_all_t = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_name LIKE ?');
        $search_all_t->execute(['%' . $search_text . '%']);
        $search_all_t_re = $search_all_t->fetchAll(PDO::FETCH_ASSOC);
        if ($search_all_t_re) {
            foreach ($search_all_t_re as $search_all_t_row) {
                $tag_data[] = [
                    'type' => 'tag',
                    'id' => $search_all_t_row['tag_id'],
                    'name' => $search_all_t_row['tag_name']
                ];
            }
        }
    } else {
        // 完全一致
        $search_all_t = $pdo->prepare('SELECT * FROM Tag_list WHERE tag_name=?');
        $search_all_t->execute([$search_text]);
        $search_all_t_re = $search_all_t->fetchAll(PDO::FETCH_ASSOC);
        if ($search_all_t_re) {
            foreach ($search_all_t_re as $search_all_t_row) {
                $tag_data[] = [
                    'type' => 'tag',
                    'id' => $search_all_t_row['tag_id'],
                    'name' => $search_all_t_row['tag_name']
                ];
            }
        }
    }
}
?>
<?php
require 'header.php';
echo '<link rel="stylesheet" href="css/search.css">';
?>
<main>
    <h1>検索結果</h1>
    <h2><?php echo $search_text; ?></h2>
    <form action="search.php" method="post">
        <input type="text" class="search-text"name="search" value="<?php echo $search_text; ?>">
        <select class="sort-tag"name="kinds">
            <option value="a" <?php if ($kinds == "a")
                echo 'selected'; ?>>全て</option>
            <option value="u" <?php if ($kinds == "u")
                echo 'selected'; ?>>ユーザーのみ</option>
            <option value="t" <?php if ($kinds == "t")
                echo 'selected'; ?>>タグのみ</option>
        </select>
        <select name="method">
            <option value="all" <?php if ($method == "all")
                echo 'selected'; ?>>完全一致</option>
            <option value="part" <?php if ($method == "part")
                echo 'selected'; ?>>部分一致</option>
        </select>
        <input class="search"type="submit" value="再検索">
    </form>
    <table>
        <tr>
            <th>種類</th>
            <th>名前</th>
        </tr>
        <?php
        if (isset($user_data) && !empty($user_data)) {
            foreach ($user_data as $data) {
                echo '<tr>';
                echo '<td>', $data['type'], '</td>';
                echo '<td>', $data['name'], '</td>';
                echo '</tr>';
            }
            $judge = 1;
        }
        if (isset($tag_data) && !empty($tag_data)) {
            foreach ($tag_data as $data) {
                echo '<tr>';
                echo '<td>', $data['type'], '</td>';
                echo '<td>', $data['name'], '</td>';
                echo '</tr>';
            }
            $judge = 1;
        }
        if ($judge == 0) {
            echo '<td colspan="3">検索結果なし</td>';
        }
        ?>
    </table>

</main>