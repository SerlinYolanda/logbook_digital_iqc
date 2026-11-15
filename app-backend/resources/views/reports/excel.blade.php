<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="ProgId" content="Excel.Sheet">
<meta name="Generator" content="IQC Digital Logbook Portal">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>IQC Recapitulation</x:Name>
    <x:WorksheetOptions>
     <x:DisplayGridlines/>
     <x:Print>
      <x:ValidPrinterInfo/>
      <x:HorizontalResolution>600</x:HorizontalResolution>
      <x:VerticalResolution>600</x:VerticalResolution>
     </x:Print>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
@endverbatim
<style>
    body {
        font-family: Calibri, 'Segoe UI', Arial, sans-serif;
        font-size: 11pt;
        color: #1e293b;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    .title-main {
        font-size: 16pt;
        font-weight: bold;
        color: #0f172a;
        text-align: left;
    }
    .title-sub {
        font-size: 10pt;
        color: #64748b;
        font-style: italic;
        text-align: left;
    }
    .meta-text {
        font-size: 9pt;
        color: #475569;
        font-weight: bold;
    }
    th {
        background-color: #1e293b;
        color: #ffffff;
        font-weight: bold;
        font-size: 10.5pt;
        text-align: center;
        border: 1px solid #475569;
        padding: 8px 10px;
        vertical-align: middle;
    }
    td {
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        vertical-align: middle;
        font-size: 10pt;
    }
    .row-zebra {
        background-color: #f8fafc;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }
    .font-mono {
        font-family: 'Consolas', 'Courier New', monospace;
    }
    .status-accept {
        background-color: #dcfce7;
        color: #15803d;
        font-weight: bold;
        text-align: center;
        border: 1px solid #86efac;
    }
    .status-reject {
        background-color: #fee2e2;
        color: #b91c1c;
        font-weight: bold;
        text-align: center;
        border: 1px solid #fca5a5;
    }
    .status-pending {
        background-color: #fef3c7;
        color: #b45309;
        font-weight: bold;
        text-align: center;
        border: 1px solid #fde68a;
    }
    .summary-row {
        background-color: #e2e8f0;
        font-weight: bold;
        border-top: 2px solid #0f172a;
    }
    .summary-cell {
        font-weight: bold;
        text-align: right;
        padding: 8px 10px;
    }
</style>
</head>
<body>

<table>
    <!-- Report Title Header -->
    <tr>
        <td colspan="16" class="title-main" style="border:none; padding:4px 0;">PT OSI ELECTRONICS BATAM &bull; INCOMING QUALITY CONTROL (IQC) DIVISION</td>
    </tr>
    <tr>
        <td colspan="16" class="title-sub" style="border:none; padding:2px 0;">Incoming Material Quality Inspection Recapitulation Report &bull; ISO 9001:2015 Controlled Record</td>
    </tr>
    <tr>
        <td colspan="16" class="meta-text" style="border:none; padding:2px 0 10px 0;">
            Period: {{ $startDate ?? '-' }} to {{ $endDate ?? '-' }} &bull; Downloaded: {{ $generatedAt ?? now()->format('d M Y H:i') }} &bull; Total: {{ count($logbooks ?? $inspections ?? []) }} Lots
        </td>
    </tr>
    <tr>
        <td colspan="16" style="border:none; height:8px;"></td>
    </tr>

    <!-- Table Header Columns -->
    <thead>
        <tr>
            <th style="width: 45px;">No</th>
            <th style="width: 160px;">GRN No</th>
            <th style="width: 150px;">PO No</th>
            <th style="width: 150px;">Lot No</th>
            <th style="width: 160px;">Part Code</th>
            <th style="width: 260px;">Component Name</th>
            <th style="width: 130px;">Commodity</th>
            <th style="width: 220px;">Supplier / Vendor</th>
            <th style="width: 120px;">Country of Origin</th>
            <th style="width: 110px;">Inbound Qty</th>
            <th style="width: 90px;">Sample Size</th>
            <th style="width: 130px;">Inspection Date</th>
            <th style="width: 150px;">IQC Inspector</th>
            <th style="width: 140px;">Quality Status</th>
            <th style="width: 160px;">MRB Disposition</th>
            <th style="width: 280px;">Supervisor Notes</th>
        </tr>
    </thead>
    <tbody>
        @php
            $items = $logbooks ?? $inspections ?? [];
            $totalQtyDatang = 0;
            $totalQtySampel = 0;
            $totalAccept = 0;
            $totalReject = 0;
        @endphp

        @forelse($items as $index => $item)
            @php
                $isZebra = $index % 2 == 1 ? 'row-zebra' : '';
                $rcv = $item->receiving ?? null;
                $mrb = $item->latestMrbDisposition ?? null;

                $grnNo = $rcv->no_grn_iqa ?? $item->grn_number ?? '-';
                $poNo = $rcv->no_po ?? $item->po_number ?? '-';
                $lotNo = $rcv->no_lot ?? $item->lot_number ?? '-';
                $partCode = $rcv->part->kode_part ?? $item->part->part_number ?? '-';
                $partName = $rcv->part->nama_part ?? $item->part->part_name ?? '-';
                $komoditas = $rcv->part->komoditas_label ?? 'EE Part';
                $supplierName = $rcv->supplier->nama_supplier ?? $item->supplier_name ?? '-';
                $originCountry = $rcv->supplier->negara_asal ?? 'Indonesia';
                $qtyDatang = (int) ($rcv->qty_datang ?? $item->lot_quantity ?? 0);
                $qtySampel = (int) ($item->qty_sampel ?? $item->sample_size ?? 0);
                $inspDate = $item->tanggal_inspeksi ? date('Y-m-d H:i', strtotime($item->tanggal_inspeksi)) : ($item->inspection_date ?? '-');
                $inspectorName = $item->inspector->name ?? $item->inspector_name ?? '-';
                $statusLabel = $item->status_label ?? strtoupper($item->status ?? '-');
                $dispLabel = $mrb ? $mrb->decision_label : ($item->disposition ?? '-');
                $catatan = $mrb ? $mrb->catatan : ($item->evaluation_notes ?? '-');

                $totalQtyDatang += $qtyDatang;
                $totalQtySampel += $qtySampel;

                $statusLower = strtolower($item->status ?? '');
                if (in_array($statusLower, ['accept', 'approved'])) {
                    $badgeClass = 'status-accept';
                    $totalAccept++;
                } elseif (in_array($statusLower, ['return_to_vendor', 'scrap', 'rework', 'rejected'])) {
                    $badgeClass = 'status-reject';
                    $totalReject++;
                } else {
                    $badgeClass = 'status-pending';
                }
            @endphp
            <tr class="{{ $isZebra }}">
                <td class="text-center font-mono">{{ $index + 1 }}</td>
                <td class="font-mono" style="mso-number-format:'\@';">{{ $grnNo }}</td>
                <td class="font-mono" style="mso-number-format:'\@';">{{ $poNo }}</td>
                <td class="font-mono" style="mso-number-format:'\@';">{{ $lotNo }}</td>
                <td class="font-mono" style="mso-number-format:'\@';">{{ $partCode }}</td>
                <td>{{ $partName }}</td>
                <td class="text-center">{{ $komoditas }}</td>
                <td>{{ $supplierName }}</td>
                <td class="text-center">{{ $originCountry }}</td>
                <td class="text-right font-mono" style="mso-number-format:'#,##0';">{{ $qtyDatang }}</td>
                <td class="text-center font-mono" style="mso-number-format:'#,##0';">{{ $qtySampel }}</td>
                <td class="text-center">{{ $inspDate }}</td>
                <td>{{ $inspectorName }}</td>
                <td class="{{ $badgeClass }}">{{ $statusLabel }}</td>
                <td>{{ $dispLabel }}</td>
                <td>{{ $catatan }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="16" class="text-center" style="padding: 20px; color:#94a3b8;">
                    No inspection records found for the selected filter.
                </td>
            </tr>
        @endforelse

        <!-- Summary Totals Row -->
        <tr class="summary-row">
            <td colspan="9" class="summary-cell" style="font-weight:bold; font-size:10.5pt;">GRAND TOTAL ({{ count($items) }} LOTS):</td>
            <td class="text-right font-mono" style="font-weight:bold; font-size:10.5pt; mso-number-format:'#,##0';">
                {{ $totalQtyDatang }}
            </td>
            <td class="text-center font-mono" style="font-weight:bold; font-size:10.5pt; mso-number-format:'#,##0';">
                {{ $totalQtySampel }}
            </td>
            <td colspan="2" class="text-center" style="font-size:9.5pt; font-weight:bold; color:#15803d;">
                ACCEPT: {{ $totalAccept }} Lots
            </td>
            <td colspan="3" class="text-center" style="font-size:9.5pt; font-weight:bold; color:#b91c1c;">
                DEFECT/MRB: {{ $totalReject }} Lots
            </td>
        </tr>
    </tbody>
</table>

</body>
</html>
