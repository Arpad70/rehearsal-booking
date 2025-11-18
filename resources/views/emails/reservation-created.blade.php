<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} - Potvrzení rezervace</title>
    <style>
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.5;
            color: #1a202c;
            background-color: #f7fafc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2d3748;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        h2 {
            color: #4a5568;
            font-size: 18px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        p {
            margin: 10px 0;
            color: #4a5568;
        }
        .details {
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .detail-row {
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #718096;
            font-weight: 500;
        }
        .value {
            color: #2d3748;
            font-weight: 600;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #718096;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Potvrzení rezervace</h1>

        <p>Dobrý den,</p>
        <p>Vaše rezervace byla úspěšně vytvořena. Zde jsou detaily:</p>

        <div class="details">
            <div class="detail-row">
                <span class="label">Místnost:</span>
                <span class="value">{{ $reservation->room->name }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Začátek:</span>
                <span class="value">{{ $reservation->start_at->format('d.m.Y H:i') }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Konec:</span>
                <span class="value">{{ $reservation->end_at->format('d.m.Y H:i') }}</span>
            </div>
        </div>

        <h2>Přístupový QR kód</h2>
        <p>QR kód najdete v příloze tohoto e-mailu.<br>
        Použijte jej pro vstup do místnosti během vaší rezervace.</p>

        <div class="footer">
            <p>Toto je automaticky generovaný e-mail, prosím neodpovídejte na něj.</p>
            <p>{{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>