<?php

namespace LaravelMagic\Export;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BaseExport implements FromCollection, WithHeadings, WithTitle, WithEvents, WithChunkReading, WithStyles
{
    /**
     * @var
     * source data for sheet
     */
    protected $collection;
    /**
     * @var
     * columns for sheet
     */
    protected $headings;
    /**
     * @var
     * sheet title
     */
    protected $title;

    /**
     * @var string
     * header background
     */
    protected $header_background = '06d69e';
    /**
     * @var string
     * set border color
     */
    protected $border_color = '06d69e';
    /**
     * @var string .
     * font family
     */
    protected $font_type = 'cairo';
    /**
     * @var string
     * body text color
     */
    protected $body_text_color = 'ffffff';
    /**
     * @var int
     */
    protected $chunk_size = 100;
    /**
     * @var array
     * calculate sum column in the end
     */
    protected $calculated_columns = [];
    /**
     * @var array
     * ['coordinateColumn' => 'color']
     * color has custom background
     */
    protected $column_text_color = [];
    /**
     * @var array
     * has sub table contain statistics
     */
    protected $statistics_table_columns = [];
    /**
     * @var string
     * translation file {default lang}
     */
    public $translation_file;

    /**
     * @param $collection
     * @param $headings
     * @param $title
     * @author BaRaa
     */
    public function __construct($collection, $headings, $title, $translationFile = 'taskly::lang')
    {
        $this->collection = $collection;
        $this->headings = $headings;
        $this->title = $title;
        $this->translation_file = $translationFile;

    }

    /**
     * @return Collection
     * @author BaRaa
     */
    public function collection()
    {
        $data = [];
        for ($i = 0; $i < count($this->collection); $i++) {
            foreach ($this->headings as $heading)
                $data[$i][$heading] = $this->collection[$i][$heading] ?? null;
        }
        return collect($data);
    }

    /**
     * @return array
     * @author BaRaa
     */
    public function headings(): array
    {
        return collect($this->headings)->map(function ($value) {
            return trans("taskly::lang.$value");
        })->toArray();
    }


    /**
     * @return string
     * sheet title
     */
    public function title(): string
    {
        return trans("{$this->translation_file}.$this->title");
    }

    /**
     * @return \Closure[]
     * @author BaRaa
     */
    public function registerEvents(): array
    {
        $char = range('A', 'Z');
        $count = collect($this->headings)->count();
        $collectionCount = collect($this->collection)->count();

        return [AfterSheet::class => function (AfterSheet $event) use ($char, $count, $collectionCount) {
            $event->sheet->autoSize();

            $event->sheet->setAutoFilter("A1:" . $char[$count - 1] . ($collectionCount + 1));

            $event->sheet->getStyle("A1:" . $char[$count - 1] . ($collectionCount + 1))->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $event->sheet->getStyle("A1:" . $char[$count - 1] . ($collectionCount + 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => $this->border_color],
                    ],
                ],
            ]);
            $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(50);
            $event->sheet->getDelegate()->getStyle("A1:" . $char[$count - 1] . '1')
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB($this->header_background);
//
            $event->sheet->getStyle("A1:" . $char[$count - 1] . ($collectionCount + 1))->getFont()->setSize(8)->setName($this->font_type)->setSize('');

            $event->sheet->getDelegate()->getStyle("A1:" . $char[$count - 1] . '1')
                ->getFont()->setBold(true)
                ->getColor()
                ->setARGB($this->body_text_color);

            if (method_exists($this, 'getCalculatedColumns') && count($this->getCalculatedColumns()) > 0)
                $this->calculatedColumns($event);

            if (count($this->statistics_table_columns) > 0)
                $this->statisticsTable($event, $char, $count);
        },

        ];
    }

    /**
     * @param $event
     * @return void
     * @author BaRaa
     */
    public function calculatedColumns($event)
    {
        $record_count = $event->sheet->getHighestRow() + 1;
        collect($this->getCalculatedColumns())->map(function ($column, $index) use ($event, $record_count) {
            $getStyle = $event->sheet->setCellValue($index . ($record_count), "=SUBTOTAL(109,{$index}2:$index" . ($event->sheet->getHighestRow() - 1) . ")")->getStyle($index . ($record_count));
            $getStyle->getFont()->setSize(10)->setName($this->font_type)->setSize('')->setBold(true);
            $getStyle->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => $this->header_background],
                    ],
                ],
            ]);
            $getStyle->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        });
    }

    /**
     * @return int
     * @author BaRaa
     */
    public function chunkSize(): int
    {
        return $this->chunk_size;
    }

    /**
     * @param Worksheet $sheet
     * @return void
     * @author BaRaa
     */
    public function styles(Worksheet $sheet)
    {
        if (count($this->column_text_color) > 0)
            collect($this->column_text_color)->map(function ($color, $coordinate) use ($sheet) {
                $sheet->getStyle("{$coordinate}2:{$coordinate}" . $sheet->getHighestRow())->getFont()->setBold(true)->getColor()->setARGB($color);
            });
    }

    /**
     * @param $event
     * @param $char_count
     * @param $heading_count
     * @return void
     */
    public function statisticsTable($event, $char_count, $heading_count)
    {
        $record_count = $event->sheet->getHighestRow() + 5;
        /**
         * Alignment table center
         */

        $index_columns = array_combine($this->statistics_table_columns, array_keys($this->calculated_columns));

        collect($this->statistics_table_columns)->map(function ($value, $index) use ($event, $record_count, $char_count, $heading_count, $index_columns) {

            $event->sheet->autoSize();

            $table_index = $index_columns[$value];

            $event->sheet->setCellValue($table_index . $record_count, trans("lang.total_$value'"));

            $event->sheet->setCellValue($table_index . ($record_count + 1), "=SUBTOTAL(109,{$table_index}2:$table_index" . ($event->sheet->getHighestRow() - 1) . ")");

            $event->sheet->getDelegate()->getStyle($table_index . $record_count)
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB($this->header_background);

            $event->sheet->getDelegate()->getStyle($table_index . $record_count)->getFont()->setSize(10)->setName($this->font_type)->setSize('')->setBold(true)->getColor()
                ->setARGB($this->getColumnTextColor());;

            $event->sheet->getStyle($table_index . ($record_count + 1))->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $event->sheet->getStyle($table_index . $record_count)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $event->sheet->getDelegate()->getStyle($table_index . ($record_count + 1))->getFont()->setSize(10)->setName($this->font_type)->setSize('')->setBold(true);
            $event->sheet->getDelegate()->getStyle($table_index . ($record_count + 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ]);
        });
    }
}

