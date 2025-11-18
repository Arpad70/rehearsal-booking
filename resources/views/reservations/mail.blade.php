<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            color: #374151;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reservation-details {
            background: #f3f4f6;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .detail-label {
            color: #6b7280;
            font-weight: 500;
        }
        .qr-info {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 6px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="color: #1f2937; margin-bottom: 10px;">Potvrzení rezervace</h1>
        </div>

        <p>Dobrý den,</p>
        <p>Vaše rezervace byla úspěšně vytvořena. Zde jsou detaily:</p>

        <div class="reservation-details">
            <div class="detail-row">
                <span class="detail-label">Místnost:</span>
                <strong>{{ $reservation->room->name }}</strong>
            </div>
            <div class="detail-row">
                <span class="detail-label">Začátek:</span>
                <strong>{{ $reservation->start_at->format('d.m.Y H:i') }}</strong>
            </div>
            <div class="detail-row">
                <span class="detail-label">Konec:</span>
                <strong>{{ $reservation->end_at->format('d.m.Y H:i') }}</strong>
            </div>
        </div>

        <div class="qr-info">
            <p style="margin-bottom: 15px;">
                <strong>Přístupový QR kód</strong>
            </p>
            <p>QR kód najdete v příloze tohoto e-mailu.<br>
               Použijte jej pro vstup do místnosti během vaší rezervace.</p>
        </div>

        <div class="footer">
            <p>Toto je automaticky generovaný e-mail, prosím neodpovídejte na něj.</p>
        </div>
    </div>
</body>
</html>