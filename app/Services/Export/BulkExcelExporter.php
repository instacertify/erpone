<?php

namespace App\Services\Export;

use App\Models\CalendarEvent;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\Sample;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkExcelExporter
{
    /**
     * @return array<string, class-string>
     */
    public function datasets(): array
    {
        return [
            'customers' => Customer::class,
            'contacts' => Contact::class,
            'leads' => Lead::class,
            'quotations' => Quotation::class,
            'projects' => Project::class,
            'invoices' => Invoice::class,
            'payments' => Payment::class,
            'samples' => Sample::class,
            'employees' => Employee::class,
            'calendar_events' => CalendarEvent::class,
        ];
    }

    public function download(array $keys = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $datasets = $this->datasets();
        $selected = empty($keys) ? array_keys($datasets) : $keys;
        $index = 0;

        foreach ($selected as $key) {
            if (! isset($datasets[$key])) {
                continue;
            }

            /** @var Collection<int, mixed> $rows */
            $rows = $datasets[$key]::query()->limit(10000)->get();
            $sheet = $spreadsheet->createSheet($index);
            $sheet->setTitle(substr($key, 0, 31));

            if ($rows->isEmpty()) {
                $sheet->setCellValue('A1', 'No records');
                $index++;

                continue;
            }

            $headers = array_keys($rows->first()->getAttributes());
            foreach ($headers as $col => $header) {
                $sheet->setCellValue([$col + 1, 1], $header);
            }

            foreach ($rows as $rowIndex => $row) {
                foreach (array_values($row->getAttributes()) as $col => $value) {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }

                    $sheet->setCellValue([$col + 1, $rowIndex + 2], $value);
                }
            }

            $index++;
        }

        if ($spreadsheet->getSheetCount() === 0) {
            $sheet = $spreadsheet->createSheet(0);
            $sheet->setTitle('export');
            $sheet->setCellValue('A1', 'No datasets selected');
        }

        $filename = 'instacertify-erp-export-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
