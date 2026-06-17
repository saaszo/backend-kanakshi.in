<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0; padding:0; background:{{ $backgroundColor }}; font-family:Arial, Helvetica, sans-serif; color:{{ $textColor }};">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        {{ $preheader }}
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:{{ $backgroundColor }}; padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:680px; background:{{ $surfaceColor }}; border-radius:20px; overflow:hidden;">
                    <tr>
                        <td style="background:#17120f; padding:24px 28px 18px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="left" valign="middle">
                                        <a href="{{ $siteUrl }}" style="text-decoration:none;">
                                            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="display:block; max-width:170px; width:100%; height:auto; border:0;">
                                        </a>
                                    </td>
                                    <td align="right" valign="middle" style="color:#d9c7a1; font-size:12px; letter-spacing:1px; text-transform:uppercase;">
                                        {{ $eyebrow }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px 28px 10px;">
                            <div style="display:inline-block; background:{{ $accentColorSoft }}; color:{{ $accentColor }}; font-size:12px; font-weight:700; letter-spacing:0.8px; text-transform:uppercase; padding:8px 12px; border-radius:999px;">
                                Kanakshi.in
                            </div>
                            <h1 style="margin:18px 0 0; font-size:30px; line-height:1.2; color:{{ $textColor }};">{{ $subject }}</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:8px 28px 0; font-size:16px; line-height:1.7; color:{{ $textColor }};">
                            @if($greeting)
                                <p style="margin:0 0 16px;">{{ $greeting }}</p>
                            @endif

                            @foreach($paragraphs as $paragraph)
                                <p style="margin:0 0 16px;">{{ $paragraph }}</p>
                            @endforeach
                        </td>
                    </tr>

                    @if($otpCode)
                        <tr>
                            <td style="padding:8px 28px 0;">
                                <div style="background:#fbf7ef; border:1px solid #eadfc9; border-radius:18px; padding:20px 22px; text-align:center;">
                                    <div style="font-size:12px; text-transform:uppercase; letter-spacing:1px; color:{{ $mutedColor }}; margin-bottom:10px;">Your Secure OTP</div>
                                    <div style="font-size:34px; line-height:1; font-weight:700; color:{{ $textColor }}; letter-spacing:8px;">{{ $otpCode }}</div>
                                </div>
                            </td>
                        </tr>
                    @endif

                    @if($detailTitle || count($details) > 0)
                        <tr>
                            <td style="padding:18px 28px 0;">
                                <div style="border:1px solid #eadfc9; border-radius:18px; padding:20px 22px;">
                                    @if($detailTitle)
                                        <div style="font-size:14px; font-weight:700; color:{{ $textColor }}; margin-bottom:14px;">{{ $detailTitle }}</div>
                                    @endif

                                    @foreach($details as $detail)
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin:0 0 10px;">
                                            <tr>
                                                <td style="font-size:13px; color:{{ $mutedColor }}; padding:0 12px 0 0; vertical-align:top;">{{ $detail['label'] }}</td>
                                                <td style="font-size:14px; color:{{ $textColor }}; text-align:right; font-weight:600; vertical-align:top;">{{ $detail['value'] }}</td>
                                            </tr>
                                        </table>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endif

                    @foreach($listSections as $section)
                        <tr>
                            <td style="padding:18px 28px 0;">
                                <div style="border:1px solid #eadfc9; border-radius:18px; padding:20px 22px;">
                                    <div style="font-size:14px; font-weight:700; color:{{ $textColor }}; margin-bottom:12px;">{{ $section['title'] }}</div>
                                    <ul style="margin:0; padding-left:18px; color:{{ $textColor }};">
                                        @foreach($section['items'] as $item)
                                            <li style="margin:0 0 8px; font-size:14px; line-height:1.6;">{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @if($actionUrl)
                        <tr>
                            <td align="center" style="padding:24px 28px 0;">
                                <a href="{{ $actionUrl }}" style="display:inline-block; background:{{ $accentColor }}; color:#17120f; text-decoration:none; font-size:14px; font-weight:700; padding:14px 24px; border-radius:999px;">
                                    {{ $actionLabel }}
                                </a>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:26px 28px 14px; font-size:15px; line-height:1.7; color:{{ $textColor }};">
                            @foreach($closingLines as $line)
                                <div>{{ $line }}</div>
                            @endforeach
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 28px 30px; color:{{ $mutedColor }}; font-size:12px; line-height:1.7; border-top:1px solid #efe6d8;">
                            <div>{{ $siteName }}</div>
                            <div>
                                <a href="{{ $siteUrl }}" style="color:{{ $mutedColor }}; text-decoration:none;">{{ preg_replace('#^https?://#', '', $siteUrl) }}</a>
                                @if($supportEmail)
                                    &nbsp;|&nbsp;
                                    <a href="mailto:{{ $supportEmail }}" style="color:{{ $mutedColor }}; text-decoration:none;">{{ $supportEmail }}</a>
                                @endif
                                @if($supportPhone)
                                    &nbsp;|&nbsp; {{ $supportPhone }}
                                @endif
                            </div>
                            <div style="margin-top:8px;">This message was sent by {{ $siteName }} regarding your account, order, or service activity.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
