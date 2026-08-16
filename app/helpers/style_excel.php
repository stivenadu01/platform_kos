<?php

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;




function applyStyle($sheet, string $range, array $classes)
{
  $style = [
    // === FONT ===
    'bold' => [
      'font' => ['bold' => true],
    ],

    // === BORDER ===
    'border' => [
      'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
      ],
    ],

    // === WRAP TEXT ===
    'wrap' => [
      'alignment' => ['wrapText' => true],
    ],

    // === ALIGNMENT CENTER ===
    'center' => [
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
      ],
    ],

    // === ALIGNMENT LEFT / RIGHT ===
    'left' => [
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical'   => Alignment::VERTICAL_CENTER,
      ],
    ],
    'right' => [
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
        'vertical'   => Alignment::VERTICAL_CENTER,
      ],
    ],

    // === TOP ALIGN ===
    'topLeft' => [
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical'   => Alignment::VERTICAL_TOP,
      ],
    ],
    'topCenter' => [
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_TOP,
      ],
    ],
    'topRight' => [
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
        'vertical'   => Alignment::VERTICAL_TOP,
      ],
    ],
  ];

  $finalStyle = [];

  foreach ($classes as $cls) {
    if (!isset($style[$cls])) continue;

    foreach ($style[$cls] as $key => $value) {
      if (!isset($finalStyle[$key])) {
        $finalStyle[$key] = $value;
      } else {
        $finalStyle[$key] = array_replace_recursive($finalStyle[$key], $value);
      }
    }
  }

  $sheet->getStyle($range)->applyFromArray($finalStyle);
}
