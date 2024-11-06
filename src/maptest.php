<?php
// セッションを開始
session_start();

// unset関数を使用してセッションのデータを削除
unset($_SESSION['floor']['kai']);

// 定数を定義
const SERVER = 'mysql310.phy.lolipop.lan';
const DBNAME = 'LAA1516821-spotlink';
const USER = 'LAA1516821';
const PASS = 'nomodon';

// データベース接続
$connect = 'mysql:host='. SERVER . ';dbname='. DBNAME . ';charset=utf8';
try {
    $pdo = new PDO($connect, USER, PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "接続失敗: " . $e->getMessage();
    exit;
}

// ユーザーIDを取得
$user_id = $_SESSION['user']['user_id'] ?? null; // null合体演算子でエラー回避

if ($user_id === null) {
    echo "ユーザーIDが設定されていません。";
    exit;
}

// ユーザーのタグ情報を取得
$sql = $pdo->prepare('SELECT * FROM Tag_attribute WHERE user_id=?');
$sql->execute([$user_id]);
$results = $sql->fetchAll(PDO::FETCH_ASSOC);

// 位置情報を取得
$locations = [];
$sql_locations = $pdo->prepare('SELECT * FROM Current_location WHERE user_id=?');
$sql_locations->execute([$user_id]);
$location_data = $sql_locations->fetchAll(PDO::FETCH_ASSOC);

// 位置情報をJavaScript用に整形
foreach ($location_data as $location) {
    $locations[] = [
        'x' => (float)$location['x_coordinate'], // x座標
        'y' => (float)$location['y_coordinate'], // y座標
        'z' => (float)$location['z_coordinate']  // z座標
    ];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mob_css/map-mob.css" media="screen and (max-width: 480px)">
    <link rel="stylesheet" href="css/map.css" media="screen and (min-width: 1280px)">
    <title>3D MAP</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
</head>
<body>

<div class="map">
    <h1 class="title">麻生情報ビジネス専門学校 3Dマップ</h1>
    
    <script>
        // PHPから位置情報をJavaScriptに渡す
        const locations = <?php echo json_encode($locations); ?>;

        // シーンを作成
        const scene = new THREE.Scene();

        // カメラを作成
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 5;

        // レンダラーを作成
        const renderer = new THREE.WebGLRenderer();
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.body.appendChild(renderer.domElement);

        // ライトを追加
        const light = new THREE.AmbientLight(0xffffff); // 白色の光
        scene.add(light);

        // 立方体のジオメトリを作成し、位置情報をもとにオブジェクトを追加
        locations.forEach(location => {
            const geometry = new THREE.BoxGeometry(1, 1, 1);
            const material = new THREE.MeshBasicMaterial({ color: Math.random() * 0xffffff });
            const cube = new THREE.Mesh(geometry, material);
            cube.position.set(location.x, location.y, location.z); // 座標に基づいて配置
            scene.add(cube);
        });

        // アニメーションループ
        function animate() {
            requestAnimationFrame(animate);
            renderer.render(scene, camera);
        }
        animate();

        // リサイズ対応
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    </script>
</div>

<div class="gakugai-container">
    <h2><a href="mapindex.php">学外</a></h2>
</div>
<br>
<br>

</body>
</html>