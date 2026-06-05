<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; }
        .ticket {
            border: 2px solid #4f46e5;
            border-radius: 14px;
            margin: 18px;
            padding: 0;
            overflow: hidden;
            page-break-inside: avoid;
        }
        .ticket + .ticket { page-break-before: always; }
        .header {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 18px 22px;
        }
        .header .brand { font-size: 12px; letter-spacing: 2px; text-transform: uppercase; opacity: 0.85; }
        .header h1 { font-size: 22px; margin-top: 4px; }
        .body { padding: 22px; }
        .row { width: 100%; }
        .col-info { display: inline-block; width: 60%; vertical-align: top; }
        .col-qr { display: inline-block; width: 38%; text-align: right; vertical-align: top; }
        .field { margin-bottom: 12px; }
        .label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; }
        .value { font-size: 15px; font-weight: bold; color: #111827; margin-top: 2px; }
        .qr img { width: 150px; height: 150px; }
        .qr .ref { font-size: 10px; color: #6b7280; margin-top: 4px; }
        .footer {
            border-top: 1px dashed #d1d5db;
            padding: 12px 22px;
            font-size: 10px;
            color: #9ca3af;
        }
        .badge {
            display: inline-block;
            background-color: #eef2ff;
            color: #4f46e5;
            font-size: 11px;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    @foreach ($tickets as $t)
        @php $ticket = $t['model']; @endphp
        <div class="ticket">
            <div class="header">
                <div class="brand">EVENTPASS — BILLET ÉLECTRONIQUE</div>
                <h1>{{ $event->title }}</h1>
            </div>
            <div class="body">
                <div class="row">
                    <div class="col-info">
                        <div class="field">
                            <div class="label">Date &amp; heure</div>
                            <div class="value">{{ $event->starts_at->translatedFormat('l j F Y · H\hi') }}</div>
                        </div>
                        <div class="field">
                            <div class="label">Lieu</div>
                            <div class="value">{{ $event->venue }}, {{ $event->city }}</div>
                        </div>
                        <div class="field">
                            <div class="label">Porteur</div>
                            <div class="value">{{ $ticket->holder_name }}</div>
                        </div>
                        <div class="field">
                            <div class="label">Catégorie</div>
                            <div class="value"><span class="badge">{{ $ticket->ticketType->name }}</span></div>
                        </div>
                    </div>
                    <div class="col-qr qr">
                        <img src="{{ $t['qr'] }}" alt="QR code">
                        <div class="ref">Billet #{{ $ticket->id }}</div>
                        <div class="ref">{{ $order->reference }}</div>
                    </div>
                </div>
            </div>
            <div class="footer">
                Présentez ce QR code à l'entrée. Chaque billet est unique et ne peut être scanné qu'une seule fois.
            </div>
        </div>
    @endforeach
</body>
</html>
