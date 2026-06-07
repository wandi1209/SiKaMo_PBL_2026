<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Audit Log #{{ $log->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 16px;
        }

        h2 {
            border-bottom: 2px solid #d1d5db;
            font-size: 13px;
            font-weight: bold;
            margin: 20px 0 10px;
            padding-bottom: 6px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 16px;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            width: 25%;
        }

        pre {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <h1>Audit Log #{{ $log->id }}</h1>
    <div class="subtitle">{{ $log->merchant?->name ?? 'Merchant' }}</div>

    <h2>Informasi Aksi</h2>
    <table>
        <tr>
            <th>Waktu</th>
            <td>{{ $log->created_at?->format('d M Y, H:i:s') ?? '-' }}</td>
        </tr>
        <tr>
            <th>User</th>
            <td>{{ $log->user_name ?: 'System' }} ({{ $log->user_role ?: 'system' }})</td>
        </tr>
        <tr>
            <th>Aksi</th>
            <td>{{ $log->event }}</td>
        </tr>
        <tr>
            <th>Data</th>
            <td>{{ class_basename($log->auditable_type) }} - {{ $log->auditable_label ?: '-' }}</td>
        </tr>
        <tr>
            <th>Ringkasan</th>
            <td>{{ $log->summary ?: '-' }}</td>
        </tr>
    </table>

    <h2>Perubahan Data</h2>
    <table>
        <tr>
            <th>Sebelum</th>
            <td><pre>{{ json_encode($log->old_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
        </tr>
        <tr>
            <th>Sesudah</th>
            <td><pre>{{ json_encode($log->new_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
        </tr>
        <tr>
            <th>Field Berubah</th>
            <td>{{ implode(', ', $log->changed_fields ?? []) ?: '-' }}</td>
        </tr>
    </table>

    <h2>Konteks Request</h2>
    <table>
        <tr>
            <th>Method</th>
            <td>{{ $log->request_method ?: '-' }}</td>
        </tr>
        <tr>
            <th>Route</th>
            <td>{{ $log->route_name ?: '-' }}</td>
        </tr>
        <tr>
            <th>URL</th>
            <td>{{ $log->request_url ?: '-' }}</td>
        </tr>
        <tr>
            <th>IP</th>
            <td>{{ $log->ip_address ?: '-' }}</td>
        </tr>
    </table>
</body>
</html>
