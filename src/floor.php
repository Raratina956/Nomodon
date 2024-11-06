<?php
require 'parts/auto-login.php';
if(!isset($_SESSION['floor']['kai'])){
    $_SESSION['floor'] = [
        'kai' => $_POST['floor']
    ];    
}
require 'header.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($_SESSION['floor']['kai']); ?>階</title>
    <link rel="stylesheet" href="css/floor.css">
</head>
<body>
    <!-- メイン(マップ)に戻る -->
    <button type="button" class="back-link" onclick="location.href='map.php'">戻る</button>
    <?php
    echo '<main><h1>', htmlspecialchars($_SESSION['floor']['kai']), '階</h1>';
    $sql = $pdo->prepare('SELECT * FROM Classroom WHERE classroom_floor = ?');
    $sql->execute([$_SESSION['floor']['kai']]);
    $rows = $sql->fetchAll(PDO::FETCH_ASSOC);

    $sql_current = $pdo->prepare('SELECT classroom_id, COUNT(*) AS user_count FROM Current_location GROUP BY classroom_id');
    $sql_current->execute();
    $results = $sql_current->fetchAll(PDO::FETCH_ASSOC);

    $ul_class = count($rows) < 5 ? 'vertical' : 'horizontal';
    
    echo '<ul class="ul1 ', $ul_class, '">';
    foreach ($rows as $row) {
        $classroom_id = $row['classroom_id'];
        $classroom_name = $row['classroom_name'];
        $user_count = 0;

        foreach ($results as $result) {
            if ($result['classroom_id'] == $classroom_id) {
                $user_count = $result['user_count'];
                break; 
            }
        }

        echo '<li class="li1">';
        echo '<a class="a1" href="room.php?id=', htmlspecialchars($classroom_id), '&update=0">', '<span class="san">‣</span>', htmlspecialchars($classroom_name), '　', $user_count, '人</a>'; // htmlspecialcharsでXSS対策
        echo '</li>';
    }
    echo '</ul></main>';
    ?>
</body>
</html>
