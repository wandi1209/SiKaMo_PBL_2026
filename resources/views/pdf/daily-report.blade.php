<!doctype html>
<html lang="id">

<head>
     <meta charset="utf-8">
     <title>Laporan Harian {{ $merchant->name }}</title>
     <style>
          body {
               color: #0f172a;
               font-family: DejaVu Sans, sans-serif;
               font-size: 12px;
               line-height: 1.5;
          }

          h1,
          h2,
          h3,
          p {
               margin: 0;
          }

          .header {
               border-bottom: 2px solid #059669;
               margin-bottom: 24px;
               padding-bottom: 14px;
          }

          .muted {
               color: #64748b;
          }

          .summary {
               margin-bottom: 22px;
               width: 100%;
          }

          .summary td {
               background: #ecfdf5;
               border: 1px solid #a7f3d0;
               padding: 12px;
               width: 33.33%;
          }

          .summary .value {
               color: #047857;
               display: block;
               font-size: 18px;
               font-weight: bold;
               margin-top: 4px;
          }

          table {
               border-collapse: collapse;
               margin-top: 8px;
               width: 100%;
          }

          th {
               background: #f1f5f9;
               color: #334155;
               font-weight: bold;
               text-align: left;
          }

          th,
          td {
               border: 1px solid #e2e8f0;
               padding: 8px;
          }

          .section {
               margin-top: 20px;
          }

          .right {
               text-align: right;
          }
     </style>
</head>

<body>
     <div class="header">
          <h1>Laporan Harian Penjualan</h1>
          <p class="muted">{{ $merchant->name }} &middot; {{ $report->report_date->format('d M Y') }}</p>
          <p class="muted">Dibuat otomatis: {{ $generatedAt->format('d M Y H:i') }} WIB</p>
     </div>

     <table class="summary">
          <tr>
               <td>
                    Total Transaksi
                    <span class="value">{{ number_format($report->total_transactions, 0, ',', '.') }}</span>
               </td>
               <td>
                    Total Pendapatan
                    <span class="value">Rp {{ number_format($report->total_revenue, 0, ',', '.') }}</span>
               </td>
               <td>
                    Barang Terjual
                    <span class="value">{{ number_format($report->total_items_sold, 0, ',', '.') }}</span>
               </td>
          </tr>
     </table>

     <div class="section">
          <h2>Ringkasan Metode Pembayaran</h2>
          <table>
               <thead>
                    <tr>
                         <th>Metode</th>
                         <th class="right">Transaksi</th>
                         <th class="right">Pendapatan</th>
                    </tr>
               </thead>
               <tbody>
                    @forelse ($paymentMethods as $method)
                         <tr>
                              <td>{{ strtoupper($method->payment_method) }}</td>
                              <td class="right">{{ number_format($method->total_transactions, 0, ',', '.') }}</td>
                              <td class="right">Rp {{ number_format($method->total_revenue, 0, ',', '.') }}</td>
                         </tr>
                    @empty
                         <tr>
                              <td colspan="3" class="muted">Belum ada transaksi selesai pada tanggal ini.</td>
                         </tr>
                    @endforelse
               </tbody>
          </table>
     </div>

     <div class="section">
          <h2>Produk Terjual Terbanyak</h2>
          <table>
               <thead>
                    <tr>
                         <th>Produk</th>
                         <th class="right">Qty</th>
                         <th class="right">Pendapatan</th>
                    </tr>
               </thead>
               <tbody>
                    @forelse ($topProducts as $product)
                         <tr>
                              <td>{{ $product->name }}</td>
                              <td class="right">{{ number_format($product->total_quantity, 0, ',', '.') }}</td>
                              <td class="right">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</td>
                         </tr>
                    @empty
                         <tr>
                              <td colspan="3" class="muted">Belum ada produk terjual pada tanggal ini.</td>
                         </tr>
                    @endforelse
               </tbody>
          </table>
     </div>
</body>

</html>