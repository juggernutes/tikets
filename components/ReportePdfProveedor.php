<?php

class ReportesPdfProveedor
{
    public static function outputReporteProveedorFPDF(array $reporte, array $meta = []): void
    {

        // ====== Meta (viene del app) ======
        $nombreProv = (string)($meta['nombreProv'] ?? 'Desconocido');
        $emailProv  = (string)($meta['emailProv']  ?? 'Desconocido');
        $phoneProv  = (string)($meta['phoneProv']  ?? 'Desconocido');
        $fechaRep   = (string)($meta['fechaConsulta'] ?? date('Y-m-d'));

        $filename = (string)($meta['filename'] ?? ("reporte_proveedor_{$nombreProv}_" . date('Ymd') . ".pdf"));
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);

        // ====== Cargar FPDF ======
        require_once __DIR__ . '/../lib/fpdf186/fpdf.php';
        $fpdfPath = __DIR__ . '/../lib/fpdf186/fpdf.php';


        $iconPath = __DIR__ . '/../img/centroNew.png';
        if (!file_exists($fpdfPath)) {
            throw new Exception('No se encontró FPDF en: ' . $fpdfPath);
        }

        if (!class_exists('FPDF')) {
            throw new Exception('FPDF no cargó correctamente.');
        }
        //$footPath = 'Empacadora Rosarito Departamento de Tecnologías';

        // ====== Helpers ======
        $toPdf = function ($s): string {
            $s = (string)$s;
            $s = str_replace("\r", '', $s);
            $out = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s);
            return $out !== false ? $out : $s;
        };

        $limitLines = function (string $s, int $maxLines = 5): string {
            $s = str_replace("\r", "", $s);
            $parts = explode("\n", $s);
            $parts = array_slice($parts, 0, $maxLines);
            return implode("\n", $parts);
        };

        $cut = function ($s, int $max, string $suffix = '...') use ($toPdf): string {
            $s = trim((string)$s);
            if ($s === '') return $toPdf('-');

            if (function_exists('mb_strimwidth')) {
                $s = mb_strimwidth($s, 0, $max, $suffix, 'UTF-8');
            } else {
                if (strlen($s) > $max) $s = substr($s, 0, max(0, $max - strlen($suffix))) . $suffix;
            }
            return $toPdf($s);
        };

        $nbLines = function ($pdf, $width, $text) {
            $text = (string)$text;
            $text = str_replace("\r", '', $text);
            $lines = 0;

            $parts = explode("\n", $text);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    $lines++;
                    continue;
                }

                $words = preg_split('/\s+/', $part);
                $current = '';

                foreach ($words as $w) {
                    $test = ($current === '') ? $w : ($current . ' ' . $w);
                    if ($pdf->GetStringWidth($test) <= ($width - 2)) {
                        $current = $test;
                    } else {
                        $lines++;
                        $current = $w;
                    }
                }
                if ($current !== '') $lines++;
            }

            return max(1, $lines);
        };

        // ====== PDF ======
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $bottomMargin = 10;
        $pdf->SetAutoPageBreak(true, $bottomMargin);

        if (file_exists($iconPath)) {
            $pdf->Image($iconPath, 12, 8, 18);
        }

        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Ln(4);
        $pdf->Cell(0, 9, $toPdf("Reporte de actividades - Proveedor #{$nombreProv}"), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, $toPdf("Fecha de consulta: {$fechaRep}"), 0, 1, 'C');
        $pdf->Ln(2);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, $toPdf("Proveedor: {$nombreProv}"), 0, 1);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, $toPdf("Email: {$emailProv}"), 0, 1);
        $pdf->Cell(0, 6, $toPdf("Teléfono: {$phoneProv}"), 0, 1);
        $pdf->Ln(4);
        // Columnas
        $headers = ['Estado', 'Inicio', 'Solución', 'Descripción', 'Empleado', 'Solución'];
        $w = [20, 26, 26, 70, 50, 85];

        $printHeader = function () use ($pdf, $headers, $w, $toPdf) {
            $pdf->SetFont('Arial', 'B', 8);
            foreach ($headers as $i => $h) {
                $pdf->Cell($w[$i], 6, $toPdf($h), 1, 0, 'C');
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 8);
        };

        $printHeader();

        $lineHeight = 4;

        foreach ($reporte as $row) {
            $estado = (string)($row['ESTADO'] ?? '-');

            $iniRaw = (string)($row['FECHA_INICIO'] ?? '-');
            $solRaw = (string)($row['FECHA_SOLUCION'] ?? '-');

            // Quitar horas (si viene DATETIME)
            $ini = ($iniRaw !== '-' && strlen($iniRaw) >= 10) ? substr($iniRaw, 0, 10) : $iniRaw;
            $sol = ($solRaw !== '-' && strlen($solRaw) >= 10) ? substr($solRaw, 0, 10) : $solRaw;

            $desc = (string)($row['DESCRIPCION'] ?? '-');
            $emp  = (string)($row['EMPLEADO'] ?? '-');
            $solu = (string)($row['SOLUCION'] ?? '-');

            // Limpieza + limitar saltos para que no se haga gigante
            $desc = $limitLines(trim(str_replace("\r", '', $desc)), 5);
            $solu = $limitLines(trim(str_replace("\r", '', $solu)), 5);

            // Celdas simples
            $estado_pdf = $cut($estado, 14);
            $ini_pdf    = $cut($ini, 10);
            $sol_pdf    = $cut($sol, 10);
            $emp_pdf    = $cut($emp, 40);

            // MultiCell (convertido)
            $desc_pdf = $toPdf(function_exists('mb_strimwidth') ? mb_strimwidth($desc, 0, 450, '...', 'UTF-8') : $desc);
            $solu_pdf = $toPdf(function_exists('mb_strimwidth') ? mb_strimwidth($solu, 0, 650, '...', 'UTF-8') : $solu);

            // Altura de fila (cap de líneas)
            $linesDesc = $nbLines($pdf, $w[3], $desc_pdf);
            $linesSol  = $nbLines($pdf, $w[5], $solu_pdf);

            $maxLines = 6;
            $lines = min($maxLines, max($linesDesc, $linesSol));
            //$rowH  = max($lineHeight, $lines * $lineHeight);
            $minLines = 2; // mínimo 2 renglones
            $rowH = max($minLines * $lineHeight, $lines * $lineHeight);

            // ✅ Salto de página correcto (calcula el límite sin acceder a propiedades protegidas)
            $pageHeight = method_exists($pdf, 'GetPageHeight') ? $pdf->GetPageHeight() : 210; // A4 (L) alto
            $bottom = $pageHeight - $bottomMargin;
            if ($pdf->GetY() + $rowH > $bottom) {
                $pdf->AddPage();
                $printHeader();
            }

            $x = $pdf->GetX();
            $y = $pdf->GetY();

            $pdf->Cell($w[0], $rowH, $estado_pdf, 1);
            $pdf->Cell($w[1], $rowH, $ini_pdf, 1);
            $pdf->Cell($w[2], $rowH, $sol_pdf, 1);

            // Descripción
            $pdf->SetXY($x + $w[0] + $w[1] + $w[2], $y);
            $pdf->MultiCell($w[3], $lineHeight, $desc_pdf, 1);

            // Empleado
            $pdf->SetXY($x + $w[0] + $w[1] + $w[2] + $w[3], $y);
            $pdf->MultiCell($w[4], $lineHeight, $emp_pdf, 1);

            // Solución
            $pdf->SetXY($x + $w[0] + $w[1] + $w[2] + $w[3] + $w[4], $y);
            $pdf->MultiCell($w[5], $lineHeight, $solu_pdf, 1);

            // siguiente fila
            $pdf->SetXY($x, $y + $rowH);
        }

        // ====== FOOTER ======
        $pdf->Ln(3);
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Line(10, $pdf->GetY(), $pdf->GetPageWidth() - 10, $pdf->GetY());
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 7);

        // Izquierda
        $pdf->Cell(
            0,
            4,
            $toPdf('Generado por el Sistema de Tickets | Empacadora Rosarito ® · Departamento de Tecnologías'),
            0,
            0,
            'L'
        );

        // Derecha (misma línea)
        $pdf->SetX(-60);
        $pdf->Cell(
            50,
            4,
            $toPdf('Total de registros: ' . count($reporte)),
            0,
            0,
            'R'
        );

        $pdf->Ln(4);



        // salida limpia
        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $pdf->Output('I', $filename);
        exit;
    }

     public static function reporteSemanal(array $reporte, array $meta = []): void
    {
        $anio   = (string)($meta['anio'] ?? date('Y'));
        $semana = (string)($meta['semana'] ?? date('W'));

        $filename = (string)($meta['filename'] ?? ("reporte_semanal_{$anio}_semana_{$semana}.pdf"));
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);

        $fpdfPath = __DIR__ . '/../lib/fpdf186/fpdf.php';
        if (!file_exists($fpdfPath)) {
            throw new Exception('No se encontró FPDF en: ' . $fpdfPath);
        }
        require_once $fpdfPath;

        $iconPath = __DIR__ . '/../img/centroNew.png';

        // ===== Helpers =====
        $toPdf = function ($s): string {
            $s = (string)$s;
            $s = str_replace("\r", '', $s);
            $out = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s);
            return $out !== false ? $out : $s;
        };

        $limitLines = function (string $s, int $maxLines = 5): string {
            $s = str_replace("\r", "", $s);
            $parts = explode("\n", $s);
            $parts = array_slice($parts, 0, $maxLines);
            return implode("\n", $parts);
        };

        $cut = function ($s, int $max, string $suffix = '...') use ($toPdf): string {
            $s = trim((string)$s);
            if ($s === '') return $toPdf('-');

            if (function_exists('mb_strimwidth')) {
                $s = mb_strimwidth($s, 0, $max, $suffix, 'UTF-8');
            } else {
                if (strlen($s) > $max) $s = substr($s, 0, max(0, $max - strlen($suffix))) . $suffix;
            }
            return $toPdf($s);
        };

        $nbLines = function ($pdf, $width, $text) {
            $text = (string)$text;
            $text = str_replace("\r", '', $text);
            $lines = 0;

            $parts = explode("\n", $text);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') { $lines++; continue; }

                $words = preg_split('/\s+/', $part);
                $current = '';

                foreach ($words as $w) {
                    $test = ($current === '') ? $w : ($current . ' ' . $w);
                    if ($pdf->GetStringWidth($test) <= ($width - 2)) {
                        $current = $test;
                    } else {
                        $lines++;
                        $current = $w;
                    }
                }
                if ($current !== '') $lines++;
            }

            return max(1, $lines);
        };

        // ===== PDF =====
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->AddPage();
        $bottomMargin = 10;
        $pdf->SetAutoPageBreak(true, $bottomMargin);

        if (file_exists($iconPath)) {
            $pdf->Image($iconPath, 12, 8, 18);
        }

        $pdf->SetFont('Arial', 'B', 13);
        $pdf->Ln(4);
        $pdf->Cell(0, 9, $toPdf("Reporte semanal (Fuera de horario) - Año {$anio} · Semana {$semana}"), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, $toPdf("Fecha de generación: " . date('Y-m-d H:i')), 0, 1, 'C');
        $pdf->Ln(4);

        // Columnas (ajústalas si quieres)
        $headers = ['Estado', 'Reporte', 'Solución', 'Descripción', 'Empleado', 'Sucursal'];
        $w = [22, 26, 26, 110, 55, 35]; // suma ~288 (A4 landscape ~297 con márgenes)

        $printHeader = function () use ($pdf, $headers, $w, $toPdf) {
            $pdf->SetFont('Arial', 'B', 8);
            foreach ($headers as $i => $h) {
                $pdf->Cell($w[$i], 6, $toPdf($h), 1, 0, 'C');
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 8);
        };

        $printHeader();

        $lineHeight = 4;

        foreach ($reporte as $row) {
            $estado = (string)($row['ESTADO'] ?? '-');

            $repRaw = (string)($row['FECHA_REPORTE'] ?? '-');
            $solRaw = (string)($row['FECHA_SOLUCION'] ?? '-');

            // fecha corta (si viene datetime)
            $rep = ($repRaw !== '-' && strlen($repRaw) >= 16) ? substr($repRaw, 0, 16) : $repRaw;
            $sol = ($solRaw !== '-' && strlen($solRaw) >= 16) ? substr($solRaw, 0, 16) : $solRaw;

            $desc = (string)($row['DESCRIPCION'] ?? '-');
            $emp  = (string)($row['EMPLEADO'] ?? '-');
            $suc  = (string)($row['SUCURSAL'] ?? '-');

            $desc = $limitLines(trim(str_replace("\r", '', $desc)), 6);

            $estado_pdf = $cut($estado, 20);
            $rep_pdf    = $cut($rep, 16);
            $sol_pdf    = $cut($sol, 16);
            $emp_pdf    = $cut($emp, 45);
            $suc_pdf    = $cut($suc, 18);

            $desc_pdf = $toPdf(function_exists('mb_strimwidth') ? mb_strimwidth($desc, 0, 900, '...', 'UTF-8') : $desc);

            $linesDesc = $nbLines($pdf, $w[3], $desc_pdf);
            $maxLines = 6;
            $lines = min($maxLines, $linesDesc);

            $minLines = 2;
            $rowH = max($minLines * $lineHeight, $lines * $lineHeight);

            // Salto de página
            $pageHeight = method_exists($pdf, 'GetPageHeight') ? $pdf->GetPageHeight() : 210;
            $bottom = $pageHeight - $bottomMargin;
            if ($pdf->GetY() + $rowH > $bottom) {
                $pdf->AddPage();
                $printHeader();
            }

            $x = $pdf->GetX();
            $y = $pdf->GetY();

            $pdf->Cell($w[0], $rowH, $estado_pdf, 1);
            $pdf->Cell($w[1], $rowH, $rep_pdf, 1);
            $pdf->Cell($w[2], $rowH, $sol_pdf, 1);

            // Descripción (MultiCell)
            $pdf->SetXY($x + $w[0] + $w[1] + $w[2], $y);
            $pdf->MultiCell($w[3], $lineHeight, $desc_pdf, 1);

            // Empleado
            $pdf->SetXY($x + $w[0] + $w[1] + $w[2] + $w[3], $y);
            $pdf->MultiCell($w[4], $lineHeight, $emp_pdf, 1);

            // Sucursal
            $pdf->SetXY($x + $w[0] + $w[1] + $w[2] + $w[3] + $w[4], $y);
            $pdf->MultiCell($w[5], $lineHeight, $suc_pdf, 1);

            $pdf->SetXY($x, $y + $rowH);
        }

        // Footer
        $pdf->Ln(3);
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Line(10, $pdf->GetY(), $pdf->GetPageWidth() - 10, $pdf->GetY());
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, $toPdf('Generado por el Sistema de Tickets | Empacadora Rosarito ® · Departamento de Tecnologías'), 0, 0, 'L');

        $pdf->SetX(-60);
        $pdf->Cell(50, 4, $toPdf('Total: ' . count($reporte)), 0, 0, 'R');

        if (ob_get_length()) { ob_clean(); }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        $pdf->Output('I', $filename);
        exit;
    }
}
