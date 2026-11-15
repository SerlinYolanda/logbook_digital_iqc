<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="ProgId" content="Excel.Sheet">
<meta name="Generator" content="Logbook Digital IQC Portal">
@verbatim
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>IQC Logbook Archive</x:Name>
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
        background-color: #0f766e;
        color: #ffffff;
        font-weight: bold;
        font-size: 10.5pt;
        text-align: center;
        border: 1px solid #115e59;
        padding: 10px 8px;
        vertical-align: middle;
        white-space: nowrap;
    }
    td {
        border: 1px solid #cbd5e1;
        padding: 7px 10px;
        font-size: 10pt;
        vertical-align: middle;
    }
    .row-zebra {
        background-color: #f8fafc;
    }
    .text-center {
        text-align: center;
    }
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    .col-num {
        mso-number-format: "\#\,\#\#0";
        text-align: right;
    }
    .col-code {
        mso-number-format: "\@";
        font-family: Consolas, 'Courier New', monospace;
        font-weight: bold;
        text-align: center;
    }
    .col-date {
        mso-number-format: "yyyy-mm-dd";
        text-align: center;
    }
    .status-approved {
        background-color: #dcfce7;
        color: #166534;
        font-weight: bold;
        text-align: center;
        border: 1px solid #86efac;
    }
    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
        font-weight: bold;
        text-align: center;
        border: 1px solid #fca5a5;
    }
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
        font-weight: bold;
        text-align: center;
        border: 1px solid #fde68a;
    }
    .status-draft {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: bold;
        text-align: center;
        border: 1px solid #cbd5e1;
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
        <td colspan="15" class="title-main" style="border:none; padding:4px 0;">INCOMING QUALITY CONTROL (IQC) - LOGBOOK ARCHIVE</td>
    </tr>
    <tr>
        <td colspan="15" class="title-sub" style="border:none; padding:2px 0;">Complete Inspection History & Compliance Records &bull; Component Quality Assurance</td>
    </tr>
    <tr>
        <td colspan="15" class="meta-text" style="border:none; padding:2px 0 10px 0;">
            Exported On: {{ $generatedAt }} &bull; Total Filtered Records: {{ count($inspections) }} Lot(s)
        </td>
    </tr>
    <tr>
        <td colspan="15" style="border:none; height:8px;"></td>
    </tr>

    <!-- Table Header Columns -->
    <thead>
        <tr>
            <th style="width: 45px;">No</th>
            <th style="width: 120px;">Inspection Date</th>
            <th style="width: 170px;">PO / GRN Number</th>
            <th style="width: 170px;">Vendor Lot Number</th>
            <th style="width: 170px;">Part Number</th>
            <th style="width: 280px;">Part Name</th>
            <th style="width: 240px;">Supplier / Manufacturer</th>
            <th style="width: 190px;">Inspection Mode</th>
            <th style="width: 110px;">Lot Qty (pcs)</th>
            <th style="width: 110px;">Sample Size</th>
            <th style="width: 140px;">Quality Status</th>
            <th style="width: 150px;">Final Disposition</th>
            <th style="width: 160px;">Inspector</th>
            <th style="width: 160px;">Supervisor Authorized</th>
            <th style="width: 300px;">Disposition Notes</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalLotQty = 0;
            $totalSampleQty = 0;
            $countApproved = 0;
            $countRejected = 0;
            $countPending = 0;
        @endphp

        @forelse($inspections as $index => $ins)
            @php
                $isZebra = $index % 2 == 1 ? 'row-zebra' : '';
                $totalLotQty += (int) $ins->lot_quantity;
                $totalSampleQty += (int) $ins->sample_size;

                $rawStatus = strtolower($ins->status);
                if ($rawStatus === 'approved') {
                    $statusClass = 'status-approved';
                    $statusLabel = 'APPROVED';
                    $countApproved++;
                } elseif ($rawStatus === 'rejected') {
                    $statusClass = 'status-rejected';
                    $statusLabel = 'REJECTED';
                    $countRejected++;
                } elseif ($rawStatus === 'pending_approval') {
                    $statusClass = 'status-pending';
                    $statusLabel = 'PENDING SPV';
                    $countPending++;
                } else {
                    $statusClass = 'status-draft';
                    $statusLabel = strtoupper($ins->status);
                }

                $disposition = $ins->disposition ? strtoupper(str_replace('_', ' ', $ins->disposition)) : '-';
                $inspectionDate = $ins->inspection_date ? $ins->inspection_date->format('Y-m-d') : '-';
                $modeLabel = $ins->inspection_type === 'visual' ? 'Visual & Packaging (JEDEC)' : 'Physical Dimensional Measurement';
            @endphp
            <tr class="{{ $isZebra }}">
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="col-date">{{ $inspectionDate }}</td>
                <td class="col-code">{{ $ins->po_number }}</td>
                <td class="col-code">{{ $ins->lot_number }}</td>
                <td class="col-code">{{ $ins->part->part_number ?? '-' }}</td>
                <td class="text-left" style="font-weight: 500;">{{ $ins->part->part_name ?? '-' }}</td>
                <td class="text-left">{{ $ins->part->supplier_name ?? '-' }}</td>
                <td class="text-center">{{ $modeLabel }}</td>
                <td class="col-num">{{ $ins->lot_quantity }}</td>
                <td class="col-num">{{ $ins->sample_size }}</td>
                <td class="{{ $statusClass }}">{{ $statusLabel }}</td>
                <td class="text-center" style="font-weight: bold;">{{ $disposition }}</td>
                <td class="text-center">{{ $ins->inspector->name ?? '-' }}</td>
                <td class="text-center">{{ $ins->supervisor->name ?? '-' }}</td>
                <td class="text-left">{{ $ins->disposition_notes ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="15" class="text-center" style="padding: 24px; color: #64748b; font-style: italic;">
                    No inspection records found.
                </td>
            </tr>
        @endforelse

        <!-- Total / Summary Row -->
        @if(count($inspections) > 0)
            <tr class="summary-row">
                <td colspan="8" class="summary-cell" style="text-align: right;">TOTAL:</td>
                <td class="col-num" style="font-weight: bold; background-color: #e2e8f0;">{{ $totalLotQty }}</td>
                <td class="col-num" style="font-weight: bold; background-color: #e2e8f0;">{{ $totalSampleQty }}</td>
                <td colspan="3" class="summary-cell" style="text-align: center; font-size: 9pt;">
                    Pass: <b>{{ $countApproved }}</b> &bull; Reject: <b>{{ $countRejected }}</b> &bull; Pending: <b>{{ $countPending }}</b>
                </td>
                <td colspan="2" style="background-color: #e2e8f0;"></td>
            </tr>
        @endif
    </tbody>
</table>

</body>
</html>
