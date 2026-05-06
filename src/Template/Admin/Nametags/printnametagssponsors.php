<!DOCTYPE html>
<html>
<head>
    <title>Nametags - Sponsors</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: 280mm 230mm;
            margin: 5mm;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .print-hint {
            width: 270mm;
            margin: 0 auto 3mm auto;
            padding: 2mm 3mm;
            border: 1px solid #d9c66a;
            background: #fff9d9;
            color: #5c4b00;
            font-size: 10pt;
            line-height: 1.3;
        }

        .page {
            width: 270mm;
            height: 220mm;
            display: grid;
            grid-template-columns: repeat(3, 90mm);
            grid-template-rows: repeat(4, 55mm);
            page-break-after: always;
        }

        .page.no-break {
            page-break-after: auto;
        }

        .name-card {
            width: 90mm;
            height: 55mm;
            border: 0;
            box-shadow: inset 0 0 0 1px #ccc;
            text-align: center;
            padding: 4mm 3mm;
            position: relative;
            overflow: visible;
        }

        .name-card.empty {
            border-color: transparent;
        }

        .name-card h4 {
            font-weight: bold;
            color: #3d3dc2;
            font-size: 13pt;
            margin-top: 3mm;
            margin-bottom: 2mm;
            line-height: 1.2;
        }

        .name-card .school {
            color: #1b1464;
            font-size: 9pt;
            font-style: italic;
            margin-bottom: 1.5mm;
        }

        .name-card .convention {
            color: #1b1464;
            font-size: 8pt;
            font-style: italic;
            line-height: 1.3;
        }

        .name-card .logo {
            position: absolute;
            width: 10mm;
            bottom: 2mm;
            left: 50%;
            transform: translateX(-50%);
        }

        @media print {
            .print-hint {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="print-hint">For accurate nametag size in Firefox: set Margins to None and Scale to 100% (disable Fit to page width).</div>
<?php
$chunks = array_chunk(iterator_to_array($nametags), 12);
$totalChunks = count($chunks);
foreach ($chunks as $chunkIndex => $chunk):
?>
<div class="page<?php echo ($chunkIndex === ($totalChunks - 1)) ? ' no-break' : ''; ?>">
<?php foreach ($chunk as $datarecord): ?>
    <div class="name-card">
        <h4><?php echo htmlspecialchars($datarecord->Teachers['first_name'] . ' ' . $datarecord->Teachers['last_name']); ?></h4>
        <p class="school"><?php echo htmlspecialchars($datarecord->Users['first_name']); ?></p>
        <p class="convention"><?php echo htmlspecialchars($convSeasD->Conventions['name']); ?><br><?php echo htmlspecialchars($convSeasD->season_year); ?></p>
        <?php echo $this->Html->image('front/scce_logo_tags.jpg', ['class' => 'logo']); ?>
    </div>
<?php endforeach; ?>
<?php
$remainder = count($chunk) % 12;
if ($remainder != 0) {
    for ($i = 0; $i < (12 - $remainder); $i++) {
        echo '<div class="name-card empty"></div>';
    }
}
?>
</div>
<?php endforeach; ?>
<script>window.print();</script>
</body>
</html>
