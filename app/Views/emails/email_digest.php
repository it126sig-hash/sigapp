<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rangkuman Notifikasi</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2057a3; padding-bottom: 10px; }
        .header h1 { color: #2057a3; margin: 0; font-size: 24px; }
        .content { margin-bottom: 20px; }
        .item { padding: 10px; border-bottom: 1px solid #eee; }
        .item:last-child { border-bottom: none; }
        .time { font-size: 12px; color: #888; }
        .footer { text-align: center; font-size: 12px; color: #aaa; margin-top: 30px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #2057a3; color: #fff; text-decoration: none; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SIGAPP</h1>
        </div>
        <div class="content">
            <p>Halo <strong><?= esc($user->username) ?></strong>,</p>
            <p>Berikut adalah notifikasi terbaru yang belum Anda baca:</p>
            
            <?php foreach ($items as $proyekName => $notifs): ?>
            <div style="margin-bottom: 25px; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fafafa;">
                <div style="background-color: #2057a3; color: #fff; padding: 10px 15px; font-weight: bold; font-size: 16px;">
                    Proyek: <?= esc($proyekName) ?>
                </div>
                <div style="padding: 10px 15px;">
                    <?php foreach ($notifs as $item): ?>
                    <div class="item">
                        <div class="time"><?= date('d M Y, H:i', strtotime($item->notif_date)) ?></div>
                        
                        <?php 
                        $actor = !empty($item->actor_name) ? $item->actor_name : (!empty($item->actor_username) ? $item->actor_username : 'Sistem');
                        $dept = !empty($item->departemen_desc) ? $item->departemen_desc : (!empty($item->departemen_name) ? $item->departemen_name : '');
                        $deptStr = $dept ? " ({$dept})" : '';
                        $kavlingStr = !empty($item->no_kavling) ? " | <strong>Kavling:</strong> " . esc($item->no_kavling) : '';
                        ?>
                        
                        <div style="font-size: 13px; color: #555; margin: 4px 0;">
                            <strong>Dari:</strong> <?= esc($actor) ?><?= esc($deptStr) ?>
                            <?= $kavlingStr ?>
                        </div>
                        <div class="message" style="margin-top: 6px;">
                            <strong>Isi Notifikasi:</strong> <span style="color: #222;"><?= esc($item->notif) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="<?= base_url() ?>" class="btn">Buka Aplikasi</a>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dikirim otomatis oleh sistem SIGAPP.</p>
            <p>Anda dapat menonaktifkan notifikasi email melalui menu Profil di aplikasi.</p>
        </div>
    </div>
</body>
</html>
