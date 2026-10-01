<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation Amivoy</title>
</head>
<body style="margin:0;padding:24px 12px;background:#f6f7f3;font-family:Arial,Helvetica,sans-serif;color:#23372e;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
        <tr><td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:500px;background:#ffffff;border:1px solid #e7ebe4;border-radius:12px;">
                <tr><td style="padding:24px 26px 8px;font-size:14px;font-weight:bold;letter-spacing:1px;color:#133b2c;">AMIVOY</td></tr>
                <tr><td style="padding:10px 26px 26px;">
                    <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Bonjour,</p>
                    <p style="margin:0 0 12px;font-size:15px;line-height:1.6;"><strong>{{ $inviterName }}</strong> vous invite à rejoindre <strong>{{ $groupName }}</strong> sur Amivoy.</p>
                    <p style="margin:0 0 22px;font-size:13px;line-height:1.6;color:#65746b;">L’invitation expire le {{ $expiresAt }}.</p>
                    <p style="margin:0 0 22px;"><a href="{{ $inviteUrl }}" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#133b2c;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;">Ouvrir l’invitation</a></p>
                    @if ($inviteUrl === $appDeepLink)
                        <p style="margin:0;font-size:12px;line-height:1.6;color:#65746b;">Si le bouton ne s’ouvre pas, utilisez ce lien sur un téléphone où Amivoy est installée :<br><a href="{{ $appDeepLink }}" style="color:#133b2c;word-break:break-all;">{{ $appDeepLink }}</a></p>
                    @endif
                    <p style="margin:24px 0 0;font-size:12px;line-height:1.6;color:#8b958e;">Vous n’attendiez pas cette invitation ? Ignorez ce message.</p>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
