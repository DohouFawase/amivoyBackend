<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#133b2c">
    <title>Invitation Amivoy · {{ $groupName }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#f4f5ef;color:#173b2d;font-family:Arial,Helvetica,sans-serif;display:grid;place-items:center;padding:24px}.card{width:min(100%,480px);background:#fcfbf7;border-radius:24px;padding:34px;box-shadow:0 18px 60px #133b2c16}.brand{display:flex;align-items:center;gap:10px;color:#133b2c;font-size:14px;font-weight:900;letter-spacing:3px}.mark{display:grid;place-items:center;width:34px;height:34px;border-radius:11px;background:#ffd000;font-size:20px}.eyebrow{margin:34px 0 10px;color:#738478;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase}h1{margin:0 0 16px;font-size:30px;line-height:1.15}p{color:#607166;line-height:1.7}.group{margin:22px 0;padding:17px 18px;border-left:4px solid #ffd000;border-radius:12px;background:#eef3e9;font-size:18px;font-weight:bold}.button{display:block;margin-top:28px;padding:16px;border-radius:13px;background:#133b2c;color:white;text-align:center;text-decoration:none;font-weight:bold}.fine{margin-top:22px;font-size:12px;color:#89948b}
    </style>
</head>
<body>
    <main class="card">
        <div class="brand"><span class="mark">✳</span> AMIVOY</div>
        <p class="eyebrow">Une place vous attend</p>
        <h1>Partagez l’aventure.</h1>
        <p>{{ $inviterName }} vous invite à rejoindre :</p>
        <div class="group">{{ $groupName }}</div>
        <p>Ouvrez cette invitation dans l’application pour répondre et retrouver votre groupe.</p>
        <a class="button" href="{{ $appDeepLink }}">Continuer dans Amivoy&nbsp; →</a>
        <p class="fine">Si le bouton ne s’ouvre pas, installez ou mettez à jour l’application Amivoy, puis réessayez. Cette invitation peut expirer ou avoir déjà reçu une réponse.</p>
    </main>
</body>
</html>
