<?php
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = file_get_contents('database.sql');
$sql = str_ireplace('BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY', 'INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
$sql = str_ireplace('INT UNSIGNED', 'INTEGER', $sql);
$db->exec($sql);

$lambalar = $db->query("SELECT kavsaklar.kavsak_adi, trafik_lambalari.yon, trafik_lambalari.suanki_renk, trafik_lambalari.kalan_sure FROM trafik_lambalari INNER JOIN kavsaklar ON trafik_lambalari.kavsak_id = kavsaklar.id")->fetchAll(PDO::FETCH_ASSOC);
$kavsakAdi = !empty($lambalar) ? $lambalar[0]['kavsak_adi'] : 'Kavşak Simülasyonu';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4 Yönlü Trafik Lambası Simülasyonu</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: #0a0c10;
            color: #e2e8f0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .header p {
            color: #64748b;
            font-size: 14px;
            margin-top: 6px;
        }

        .badge {
            display: inline-block;
            background: #1e293b;
            border: 1px solid #334155;
            color: #38bdf8;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            max-width: 1100px;
            width: 100%;
        }

        @media (max-width: 900px) {
            .grid-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 500px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }

        .light-card {
            background: #11141a;
            border: 1px solid #1e2430;
            border-radius: 20px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            transition: border-color 0.3s ease;
        }

        .direction-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .traffic-housing {
            background: #050608;
            border: 3px solid #1f2533;
            border-radius: 36px;
            padding: 16px 14px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.8), 0 8px 20px rgba(0, 0, 0, 0.6);
        }

        .lamp {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 3px solid #151820;
            transition: all 0.3s ease;
        }

        .lamp-red {
            background: #2a0808;
            border-color: #1a0505;
        }

        .lamp-red.active {
            background: #ff2222;
            border-color: #ff6666;
            box-shadow: 0 0 28px #ff2222, inset 0 0 12px #ff9999;
        }

        .lamp-yellow {
            background: #2a2205;
            border-color: #1a1503;
        }

        .lamp-yellow.active {
            background: #ffb800;
            border-color: #ffe066;
            box-shadow: 0 0 28px #ffb800, inset 0 0 12px #fff099;
        }

        .lamp-green {
            background: #062410;
            border-color: #031408;
        }

        .lamp-green.active {
            background: #00e65b;
            border-color: #66ff9e;
            box-shadow: 0 0 28px #00e65b, inset 0 0 12px #99ffc7;
        }

        .timer-display {
            margin-top: 18px;
            background: #050608;
            border: 1px solid #1e2430;
            border-radius: 12px;
            padding: 8px 16px;
            width: 100%;
            text-align: center;
        }

        .timer-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }

        .timer-val {
            font-family: 'Orbitron', monospace;
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            margin-top: 2px;
        }

        .status-pill {
            margin-top: 10px;
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .status-red {
            background: rgba(255, 34, 34, 0.15);
            color: #ff5555;
        }

        .status-yellow {
            background: rgba(255, 184, 0, 0.15);
            color: #ffb800;
        }

        .status-green {
            background: rgba(0, 230, 91, 0.15);
            color: #00e65b;
        }

        .sql-panel {
            margin-top: 36px;
            max-width: 1100px;
            width: 100%;
            background: #11141a;
            border: 1px solid #1e2430;
            border-radius: 16px;
            padding: 20px;
        }

        .sql-panel h3 {
            font-size: 15px;
            margin-bottom: 12px;
            color: #94a3b8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th,
        td {
            padding: 10px 14px;
            text-align: left;
            border-bottom: 1px solid #1e2430;
        }

        th {
            color: #64748b;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>4 Yönlü Sıralı Trafik Lambası Sistemi</h1>
        <p><?= htmlspecialchars($kavsakAdi) ?> - Anlık Akıllı Kavşak Simülasyonu</p>
        <div class="badge">Sıralı Geçiş Döngüsü: KUZEY &rarr; DOĞU &rarr; GÜNEY &rarr; BATI</div>
    </div>

    <div class="grid-container">
        <?php
        $yonler = ['KUZEY', 'DOGU', 'GUNEY', 'BATI'];
        foreach ($yonler as $index => $y):
            ?>
            <div class="light-card" id="card-<?= strtolower($y) ?>" data-yon="<?= $y ?>">
                <div class="direction-title">
                    <span><?= $y ?></span>
                </div>

                <div class="traffic-housing">
                    <div class="lamp lamp-red" id="lamp-red-<?= $index ?>"></div>
                    <div class="lamp lamp-yellow" id="lamp-yellow-<?= $index ?>"></div>
                    <div class="lamp lamp-green" id="lamp-green-<?= $index ?>"></div>
                </div>

                <div class="timer-display">
                    <div class="timer-label">Kalan Süre</div>
                    <div class="timer-val" id="timer-<?= $index ?>">--</div>
                </div>

                <div class="status-pill status-red" id="pill-<?= $index ?>">BEKLİYOR</div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="sql-panel">
        <h3>[i] SQL Veritabanı Canlı Tablosu (INNER JOIN)</h3>
        <table>
            <thead>
                <tr>
                    <th>Kavşak</th>
                    <th>Yön</th>
                    <th>Başlangıç Rengi</th>
                    <th>Başlangıç Süresi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lambalar as $l): ?>
                    <tr>
                        <td><?= htmlspecialchars($l['kavsak_adi']) ?></td>
                        <td><strong><?= htmlspecialchars($l['yon']) ?></strong></td>
                        <td><?= htmlspecialchars($l['suanki_renk']) ?></td>
                        <td><?= htmlspecialchars($l['kalan_sure']) ?> sn</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        const SURE_YESIL = 15;
        const SURE_SARI = 3;
        const DONGU_SURESI_TEK_YON = SURE_YESIL + SURE_SARI;
        const TOPLAM_DONGU = DONGU_SURESI_TEK_YON * 4;

        let genelSaniye = 0;

        function renderTraffic() {
            const aktifSira = Math.floor(genelSaniye / DONGU_SURESI_TEK_YON);
            const siraIciSaniye = genelSaniye % DONGU_SURESI_TEK_YON;

            for (let i = 0; i < 4; i++) {
                const redLamp = document.getElementById(`lamp-red-${i}`);
                const yellowLamp = document.getElementById(`lamp-yellow-${i}`);
                const greenLamp = document.getElementById(`lamp-green-${i}`);
                const timerVal = document.getElementById(`timer-${i}`);
                const pill = document.getElementById(`pill-${i}`);

                redLamp.classList.remove('active');
                yellowLamp.classList.remove('active');
                greenLamp.classList.remove('active');

                if (i === aktifSira) {
                    if (siraIciSaniye < SURE_YESIL) {
                        greenLamp.classList.add('active');
                        const kalan = SURE_YESIL - siraIciSaniye;
                        timerVal.textContent = kalan + 's';
                        timerVal.style.color = '#00e65b';
                        pill.className = 'status-pill status-green';
                        pill.textContent = 'GEÇİŞ SERBEST';
                    } else {
                        yellowLamp.classList.add('active');
                        const kalan = DONGU_SURESI_TEK_YON - siraIciSaniye;
                        timerVal.textContent = kalan + 's';
                        timerVal.style.color = '#ffb800';
                        pill.className = 'status-pill status-yellow';
                        pill.textContent = 'DİKKAT';
                    }
                } else {
                    redLamp.classList.add('active');
                    let siraFarki = (i - aktifSira + 4) % 4;
                    let kalanBekleme = (siraFarki * DONGU_SURESI_TEK_YON) - siraIciSaniye;
                    timerVal.textContent = kalanBekleme + 's';
                    timerVal.style.color = '#ff5555';
                    pill.className = 'status-pill status-red';
                    pill.textContent = 'BEKLEYİN';
                }
            }

            genelSaniye = (genelSaniye + 1) % TOPLAM_DONGU;
        }

        renderTraffic();
        setInterval(renderTraffic, 1000);
    </script>
</body>

</html>