<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Audit Log #{{ $log->id }}</title>
    <style>
        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 4px;
        }

        h2 {
            border-bottom: 1px solid #d1d5db;
            font-size: 14px;
            margin: 22px 0 8px;
            padding-bottom: 6px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            width: 30%;
        }

        pre {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 10px;
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>Audit Log #{{ $log->id }}</h1>
    <div class="muted">{{ $log->merchant?->name ?? 'Merchant' }}</div>

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
            <th>Field berubah</th>
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
