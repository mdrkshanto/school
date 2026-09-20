<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Student;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfService
{
    public function fromHtml(string $html, string $filename, array $options = []): StreamedResponse
    {
        $browsershot = $this->browsershot($html, $options);

        $pdf = $browsershot->pdf();

        return response()->streamDownload(
            function () use ($pdf): void {
                echo $pdf;
            },
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Render the marksheet HTML for a student + exam.
     */
    public function marksheetHtml(Student $student, Exam $exam): string
    {
        $sheet = app(ResultService::class)->marksheet($student, $exam);

        $rowsHtml = $sheet['rows']->map(function (array $row) {
            $obtained = $row['is_absent'] ? 'ABSENT' : e((string) $row['obtained']);

            return '<tr>'
                .'<td>'.e($row['subject']).'</td>'
                .'<td>'.e((string) $row['full_marks']).'</td>'
                .'<td>'.e((string) $row['pass_marks']).'</td>'
                .'<td>'.$obtained.'</td>'
                .'<td>'.e($row['grade'] ?? '-').'</td>'
                .'<td>'.e((string) ($row['grade_point'] ?? '-')).'</td>'
                .'</tr>';
        })->implode('');

        $schoolName = e((string) (app(SettingsService::class)->get('school_name', config('app.name'))));
        $resultLabel = $sheet['passed'] ? 'PASSED' : 'FAILED';

        $head = '<div class="school">'.e($schoolName).'</div>'
            .'<div class="meta">Academic Result Sheet</div>';

        return <<<HTML
<html><head><style>
body{font-family:'DejaVu Sans',sans-serif;color:#1e293b;padding:24px}
.school{font-size:22px;font-weight:800;text-align:center}
.meta{text-align:center;color:#64748b;margin-bottom:16px}
.grid{display:flex;justify-content:space-between;margin-bottom:18px;font-size:13px}
.grid b{font-weight:700}
table{width:100%;border-collapse:collapse;font-size:13px}
th,td{border:1px solid #cbd5e1;padding:8px;text-align:center}
th{background:#0f172a;color:#fff}
tr:nth-child(even){background:#f1f5f9}
.summary{margin-top:14px;font-size:14px}
</style></head><body>
{$head}
<div class="grid">
  <div><b>Student:</b> {$student->name} ({$student->admission_no})</div>
  <div><b>Class:</b> {$student->class?->name} {$student->section?->name}</div>
  <div><b>Exam:</b> {$exam->name}</div>
</div>
<table>
<tr><th>Subject</th><th>Full</th><th>Pass</th><th>Obtained</th><th>Grade</th><th>Point</th></tr>
{$rowsHtml}
</table>
<div class="summary">
  <b>Total:</b> {$sheet['total_marks']} / {$sheet['total_full']} &nbsp;&nbsp;
  <b>GPA:</b> {$sheet['gpa']} &nbsp;&nbsp;
  <b>Grade:</b> {$sheet['grade']} &nbsp;&nbsp;
  <b>Result:</b> {$resultLabel}
</div>
</body></html>
HTML;
    }

    /**
     * Return the raw PDF bytes for inline display.
     */
    public function pdfString(string $html, array $options = []): string
    {
        return $this->browsershot($html, $options)->pdf();
    }

    private function browsershot(string $html, array $options = []): Browsershot
    {
        return Browsershot::html($html)
            ->setChromePath((string) env('BROWSERSHOT_CHROME_PATH', '/usr/bin/google-chrome-stable'))
            ->setNodeModulePath(base_path('node_modules'))
            ->showBrowserHeaderAndFooter(false)
            ->format($options['format'] ?? 'A4')
            ->margins(12, 12, 12, 12)
            ->defaultBackground(true)
            ->dismissDialogs();
    }
}
