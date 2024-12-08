<?php
session_start();

require 'parts/auto-login.php';

try {
   
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'データベース接続エラー: ' . $e->getMessage();
    exit();
}

$partner_id = $_SESSION['user']['user_id'];

// ユーザーのアイコンを取得
$iconStmt = $pdo->prepare('SELECT icon_name FROM Icon WHERE user_id = ?');
$iconStmt->execute([$partner_id]);
$icon = $iconStmt->fetch(PDO::FETCH_ASSOC);
$iconUrl = $icon ? $icon['icon_name'] : 'default-icon.png'; // デフォルトアイコンを設定


// $followList = $pdo->prepare('
//      SELECT follower_id
//      FROM Favorite
//      WHERE follow_id = ?
// ');
// $followList->execute([$partner_id]);
// $followedUsers = $followList->fetchAll(PDO::FETCH_ASSOC); // $followListの結果を取得

$followStmt = $pdo->prepare('
    SELECT 
        Favorite.follower_id, 
        Icon.icon_name, 
        Users.user_name, 
        locations.latitude, 
        locations.longitude 
    FROM Favorite
    LEFT JOIN Icon ON Favorite.follower_id = Icon.user_id
    LEFT JOIN Users ON Favorite.follower_id = Users.user_id
    LEFT JOIN locations ON Favorite.follower_id = locations.user_id
    WHERE Favorite.follow_id = ?
    AND locations.latitude IS NOT NULL
    AND locations.longitude IS NOT NULL
');
$followStmt->execute([$partner_id]);
$followedUsers = $followStmt->fetchAll(PDO::FETCH_ASSOC);



?>
<?php if (!$isMobile): ?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>現在地にピンを立てる</title>
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css' rel='stylesheet' />
    <link rel="stylesheet" href="css/mapindex.css">
</head>
<body>
<div id="sidebar">
<button id="back-btn">前のページに戻る</button>

    <h2>友達一覧</h2>
    <ul id="friend-list">
        <!-- 友達リストはJavaScriptで生成 -->
    </ul>
    <button id="update-location-btn">位置情報を更新</button>
</div>
<div id='map'></div>

<script>
mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v11',
    center: [130.4017, 33.5902], // 初期位置：福岡市の中心
    zoom: 12 // 適切なズームレベルに調整（市全体を表示）
});



document.getElementById('back-btn').addEventListener('click', () => {
    window.history.back();
});


// フォローしているユーザーの位置情報を取得
const followedUsers = <?php echo json_encode($followedUsers); ?>;
console.log('フォローしているユーザーのデータ:', followedUsers);

// 友達リストを作成
const friendList = document.getElementById('friend-list');
followedUsers.forEach(user => {
    if (user.icon_name && user.user_name) {
        const listItem = document.createElement('li');
        listItem.className = 'friend-item';

        // アイコンと名前を表示
        const userIcon = document.createElement('img');
        userIcon.src = user.icon_name; // アイコン画像のURL
        userIcon.alt = `${user.user_name}のアイコン`;
        userIcon.style.width = '32px'; // アイコンのサイズ調整

        const userName = document.createElement('span');
        userName.textContent = user.user_name;

        listItem.appendChild(userIcon);
        listItem.appendChild(userName);

        // 友達リスト項目にクリックイベントを追加
        listItem.addEventListener('click', () => {
            const userPosition = [user.longitude, user.latitude];
            map.flyTo({ center: userPosition, zoom: 15 });

            // クリック時にポップアップ表示
            new mapboxgl.Popup()
                .setLngLat(userPosition)
                .setHTML(`<div>ユーザー名: ${user.user_name}</div>`)
                .addTo(map);
        });

        friendList.appendChild(listItem);
    } else {
        console.warn('不完全なデータ:', user);
    }
});

// 現在地を取得し、自分のマーカーを表示
function updateLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const userLocation = [position.coords.longitude, position.coords.latitude];
            map.setCenter(userLocation);

            const myMarkerElement = document.createElement('div');
            myMarkerElement.className = 'marker';
            myMarkerElement.style.backgroundImage = `url(${<?php echo json_encode($iconUrl); ?>})`;
            myMarkerElement.style.width = '40px';
            myMarkerElement.style.height = '40px';

            new mapboxgl.Marker(myMarkerElement)
                .setLngLat(userLocation)
                .setPopup(new mapboxgl.Popup({ offset: 25 })
                    .setHTML('<div>あなたの現在地です</div>'))
                .addTo(map);

            // 現在地をサーバーに送信
            fetch('save-location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_id: "<?php echo $partner_id; ?>",  // ここでpartner_idが正しく設定されていることを確認
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('位置情報が保存されました:', data);
            })
            .catch(error => {
                console.error('位置情報の保存に失敗しました:', error);
            });
        }, error => {
            console.error('現在地を取得できませんでした:', error);
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });
    } else {
        alert("Geolocationがサポートされていません");
    }
}


// 位置情報更新ボタンのクリックイベント
document.getElementById('update-location-btn').addEventListener('click', updateLocation);

// フォローしているユーザーのマーカーを表示
followedUsers.forEach(user => {
    if (user.icon_name && user.latitude && user.longitude) {
        const markerElement = document.createElement('div');
        markerElement.className = 'marker';
        markerElement.style.backgroundImage = `url(${user.icon_name})`;
        markerElement.style.width = '32px';
        markerElement.style.height = '32px';

        const userPosition = [user.longitude, user.latitude];

        new mapboxgl.Marker(markerElement)
            .setLngLat(userPosition)
            .setPopup(new mapboxgl.Popup({ offset: 25 })
                .setHTML(`<div>ユーザー名: ${user.user_name}</div>`))
            .addTo(map);
    }
});
</script>

</body>
</html>



<!-- スマホ版 -->
<?php else: ?>
    <!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>現在地にピンを立てる</title>
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.13.0/mapbox-gl.css' rel='stylesheet' />
    <link rel="stylesheet" href="css/mapindexmobile.css">
</head>
<body>
    <button id="hamburger-btn">☰</button> <!-- ハンバーガーメニューのボタン -->
    
    <div id="side">
    <div id="sidebar">
        <button id="back-btn">前のページに戻る</button>
        <h2>友達一覧mobileVar</h2>
        <ul id="friend-list">
            <!-- 友達リストはJavaScriptで生成 -->
        </ul>
        <button id="update-location-btn">位置情報を更新</button>
    </div>
    </div>

    <div id='map'></div>

    <script>
        mapboxgl.accessToken = 'pk.eyJ1Ijoia2F3YW1vdG9kZXN1IiwiYSI6ImNtMTc2OHBwcTBqY2IycG43cGpiN2VnZXAifQ.60SZqVIysOhn7YhEjRWVCQ';

        // mapの初期化
        const mapInstance = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [130.4017, 33.5902], // 初期位置：福岡市の中心
            zoom: 12 // 適切なズームレベルに調整（市全体を表示）
        });

        // ハンバーガーメニューの表示/非表示を切り替える
        document.getElementById('hamburger-btn').addEventListener('click', () => {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        });

        // 戻るボタン
        document.getElementById('back-btn').addEventListener('click', () => {
            window.history.back();
        });

        // フォローしているユーザーの位置情報を取得
        const followedUsers = <?php echo json_encode($followedUsers, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
        console.log('フォローしているユーザーのデータ:', followedUsers);

        // 友達リストを作成
        const friendList = document.getElementById('friend-list');
        followedUsers.forEach(user => {
            if (user.icon_name && user.user_name) {
                const listItem = document.createElement('li');
                listItem.className = 'friend-item';

                // アイコンと名前を表示
                const userIcon = document.createElement('img');
                userIcon.src = user.icon_name; // アイコン画像のURL
                userIcon.alt = `${user.user_name}のアイコン`;
                userIcon.style.width = '32px'; // アイコンのサイズ調整

                const userName = document.createElement('span');
                userName.textContent = user.user_name;

                listItem.appendChild(userIcon);
                listItem.appendChild(userName);

                // 友達リスト項目にクリックイベントを追加
                listItem.addEventListener('click', () => {
                    const sidebar = document.getElementById('sidebar');
                    sidebar.classList.remove('open');

                    const userPosition = [user.longitude, user.latitude];
                    mapInstance.flyTo({ center: userPosition, zoom: 15 });

                    // ポップアップを表示
                    new mapboxgl.Popup()
                        .setLngLat(userPosition)
                        .setHTML(`<div>ユーザー名: ${user.user_name}</div>`)
                        .addTo(mapInstance);
                });

                friendList.appendChild(listItem);
            } else {
                console.warn('不完全なデータ:', user);
            }
        });

        // 現在地を取得し、自分のマーカーを表示
        function updateLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const userLocation = [position.coords.longitude, position.coords.latitude];
                    mapInstance.setCenter(userLocation);

                    const myMarkerElement = document.createElement('div');
                    myMarkerElement.className = 'marker';
                    myMarkerElement.style.backgroundImage = `url(${<?php echo json_encode($iconUrl, JSON_HEX_TAG | JSON_HEX_QUOT); ?>})`;
                    myMarkerElement.style.width = '40px';
                    myMarkerElement.style.height = '40px';

                    new mapboxgl.Marker(myMarkerElement)
                        .setLngLat(userLocation)
                        .setPopup(new mapboxgl.Popup({ offset: 25 })
                            .setHTML('<div>あなたの現在地です</div>'))
                        .addTo(mapInstance);

                    // 現在地をサーバーに送信
                    fetch('save-location.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: "<?php echo addslashes($partner_id); ?>",  // ここでpartner_idが正しく設定されていることを確認
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('位置情報が保存されました:', data);
                    })
                    .catch(error => {
                        console.error('位置情報の保存に失敗しました:', error);
                    });
                }, error => {
                    console.error('現在地を取得できませんでした:', error);
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            } else {
                alert("Geolocationがサポートされていません");
            }
        }

        // 位置情報更新ボタンのクリックイベント
        document.getElementById('update-location-btn').addEventListener('click', () => {
            // メニューを閉じる
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');

            // 現在地を更新
            updateLocation();
        });

        // フォローしているユーザーのマーカーを表示
        followedUsers.forEach(user => {
            if (user.icon_name && user.latitude && user.longitude) {
                const markerElement = document.createElement('div');
                markerElement.className = 'marker';
                markerElement.style.backgroundImage = `url(${user.icon_name})`;
                markerElement.style.width = '32px';
                markerElement.style.height = '32px';

                const userPosition = [user.longitude, user.latitude];

                new mapboxgl.Marker(markerElement)
                    .setLngLat(userPosition)
                    .setPopup(new mapboxgl.Popup({ offset: 25 })
                        .setHTML(`<div>ユーザー名: ${user.user_name}</div>`))
                    .addTo(mapInstance);
            }
        });
    </script>
</body>
</html>



<?php endif; ?>