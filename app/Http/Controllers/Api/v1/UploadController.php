<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadStoreRequest;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as readXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as writeXlsx;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend as ChartLegend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Writer\Word2007 as writeDocx;
use \XMLReader as XMLReader;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class UploadController extends Controller
{
    public function fileXLSX(UploadStoreRequest $request)
    {
        if($request->hasFile('file')) {
            $file=$request->file('file');
            $path = $file->store("xls",'local');
            //dd($file);
            $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix(); //путь до хранилища
            //$url = Storage::url($path); //относительный путь

            return response(self::parseXLSX($storagePath.$path))->withHeaders([ //выдача ответа
                'Content-Type' => 'application/json',
                'X-Header-One' => 'Header Value',
                'X-Header-Two' => 'Header Value',
            ]);;
        }
        //dd($request);// 'done2';
        return 'none';
    }

    private static function parseXLSX(string $pathfile) {
        $reader = new readXlsx(); //объект чтения Excel
        $reader->setReadDataOnly(true); //настройка только для чтения
        $spreadsheet = $reader->load($pathfile); //загрузка листа
        $cells = $spreadsheet->getActiveSheet()->getCellCollection(); //оперделение переменной ячеек
        $str='{"data":'; //переменная для формирования JSON строки
        for ($iRow = 1; $iRow <= $cells->getHighestRow(); $iRow++) //Пробежаться по строкам
        {
            //for ($iCol = 'A'; $iCol <= 'C'; $iCol++)
            $str.= '['; //формирование массива в JSON строке
            for ($iCol = 'A'; $iCol <= $cells->getHighestColumn(); $iCol++) //пробежаться по всем столбцам
            {
            $cell = $cells->get($iCol.$iRow); //получение ячейки
            if($cell) //если ячейка не пустая
            {
                $str.= '{"'.$iCol.$iRow.'":"'.$cell->getValue().'"},'; //заполнить объект JSON
            }
            }
            $str = $this->cutLastChar($str,','); //удаление последней запятой
            $str.= '],';
        } 
        $str = $this->cutLastChar($str,',');
        $str.='}'; 
        return $str;
    }

    private static function parseXML(string $pathfile) {
        $reader = new XMLReader();
        $str='{"data":'; //переменная для формирования JSON строки
        $reader->open($pathfile); // указываем ридеру что будем парсить этот файл
        //$reader=XMLReader::open($pathfile);
        //$reader->setParserProperty(XMLReader::VALIDATE, true); //включение валидации парсера
        //Log::debug($reader->isValid());
        // циклическое чтение документа
        while($reader->read()) {
            if($reader->nodeType == XMLReader::ELEMENT) {
                // если находим элемент <card>
                if($reader->localName == 'host') {
                    //$data = array();
                    // считываем аттрибут number
                    $str.= $reader->getAttribute('ip');
                    // читаем дальше для получения текстового элемента
                    //$reader->read();
                    //if($reader->nodeType == XMLReader::TEXT) {
                    //    $data['name'] = $reader->value;
                   // }
                    // ну и запихиваем в бд, используя методы нашего адаптера к субд
                }
            }
        }
        $str.="'}";
        return $str;
    }

    public function fileXML(UploadStoreRequest $request)
    {
        if($request->hasFile('file')) {
            $file=$request->file('file');
            $path = $file->store("xml",'local');
            //dd($file);
            $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix(); //путь до хранилища
            //$url = Storage::url($path); //относительный путь
            //Log::debug('witl');

            return response(self::parseXML($storagePath.$path))->withHeaders([ //выдача ответа
                'Content-Type' => 'application/json',
                'X-Header-One' => 'Header Value',
                'X-Header-Two' => 'Header Value',
            ]);
        }
        return 'none';
    }

    public function getSiteData(UploadStoreRequest $request) {
        Log::debug('site');
        $web_driver = RemoteWebDriver::create(env('SELENOID_PATH', "http://localhost:4444/wd/hub/"),
                                                array("browserName"=>env('SELENOID_BROWSER_NAME', "chrome"), "browserVersion"=>env('SELENOID_BROWSER_VERSION', "103.0"))
                                            );
        Log::debug('site2');
        $web_driver->get(env('SELENOID_TARGET', "http://ya.ru"));
        Log::debug('site2');
        $html = $web_driver->getPageSource();
        Log::debug(print_r($html,true));
        $web_driver->quit();
        return response('done')->withHeaders([ //выдача ответа
            'Content-Type' => 'application/json',
            'X-Header-One' => 'Header Value',
            'X-Header-Two' => 'Header Value',
        ]);
    }

    public function genword(UploadStoreRequest $request) {
        $content=json_decode($request->getContent(), true);
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText(
            '"Learn from yesterday, live for today, hope for tomorrow. '
                . 'The important thing is not to stop questioning." '
                . '(Albert Einstein)'
        );
        
        /*
         * Note: it's possible to customize font style of the Text element you add in three ways:
         * - inline;
         * - using named font style (new font style object will be implicitly created);
         * - using explicitly created font style object.
         */
        
        // Adding Text element with font customized inline...
        $section->addText(
            '"Great achievement is usually born of great sacrifice, '
                . 'and is never the result of selfishness." '
                . '(Napoleon Hill)',
            array('name' => 'Tahoma', 'size' => 10)
        );
        
        // Adding Text element with font customized using named font style...
        $fontStyleName = 'oneUserDefinedStyle';
        $phpWord->addFontStyle(
            $fontStyleName,
            array('name' => 'Tahoma', 'size' => 10, 'color' => '1B2232', 'bold' => true)
        );
        $section->addText(
            '"The greatest accomplishment is not in never falling, '
                . 'but in rising again after you fall." '
                . '(Vince Lombardi)',
            $fontStyleName
        );
        
        // Adding Text element with font customized using explicitly created font style object...
        $fontStyle = new \PhpOffice\PhpWord\Style\Font();
        $fontStyle->setBold(true);
        $fontStyle->setName('Tahoma');
        $fontStyle->setSize(13);
        $myTextElement = $section->addText('"Believe you can and you\'re halfway there." (Theodor Roosevelt)');
        $myTextElement->setFontStyle($fontStyle);

        // Adding Text element with font customized using named font style...
        $fontStyleName = 'oneUserDefinedStyle';
        $phpWord->addFontStyle(
            $fontStyleName,
            array('name' => 'Arial', 'size' => 12, 'color' => '1B2232', 'bold' => false)
        );
        $section->addText(
            'Title'.':'.$content['title'],
            $fontStyleName
        );

        $tableStyle = array(
            'borderColor' => '006699',
            'borderSize'  => 6,
            'cellMargin'  => 50
        );
        $firstRowStyle = array('bgColor' => '66BBFF');
        $phpWord->addTableStyle('myTable', $tableStyle, $firstRowStyle);
        $table = $section->addTable('myTable');
        $table->addRow();
        $table->addCell(2000)->addText("- {$content['counts'][0]}");
        $table->addCell(2000)->addText("- {$content['counts'][1]}");
        $table->addCell(2000)->addText("- {$content['counts'][2]}");
        
        // Saving the document as OOXML file...
        $objWriter = new writeDocx($phpWord);
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix(); //путь до хранилища
        $objWriter->save($storagePath.'helloWorld.docx');

        return response()->download($storagePath.'helloWorld.docx');
    }

    //Генерация файла эксель
    //Сохранение его
    //И выдача в ответ
    public function genexcel(UploadStoreRequest $request) {
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
        $content=json_decode($request->getContent(), true);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->SetCreator('Laravel Server')
            ->setLastModifiedBy('Laravel Server')
            ->setTitle('Excel Test Document')
            ->setSubject('Office 2007 XLSX Test Document')
            ->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
            ->setKeywords('office 2007 openxml php')
            ->setCategory('Test result file');
        $worksheet = $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('F1', 'Title:')
            ->setCellValue('G1', $content['title'])
            ->setCellValue('F2', 'Перечисление:')
            ->setCellValue('G2', $content['counts'][0])
            ->setCellValue('H2', $content['counts'][1])
            ->setCellValue('I2', $content['counts'][2]);
        //$worksheet = $spreadsheet->getActiveSheet();
        $worksheet->fromArray(
            [
                ['', 2010, 2011, 2012],
                ['Q1', 12, 15, 21],
                ['Q2', 56, 73, 86],
                ['Q3', 52, 61, 69],
                ['Q4', 30, 32, 10],
            ]
        );
        // Set the Labels for each data series we want to plot
        //     Datatype
        //     Cell reference for data
        //     Format Code
        //     Number of datapoints in series
        //     Data values
        //     Data Marker
        $dataSeriesLabels1 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$C$1', null, 1), // 2011
        ];
        // Set the X-Axis Labels
        //     Datatype
        //     Cell reference for data
        //     Format Code
        //     Number of datapoints in series
        //     Data values
        //     Data Marker
        $xAxisTickValues1 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$A$2:$A$5', null, 4), // Q1 to Q4
        ];
        // Set the Data values for each data series we want to plot
        //     Datatype
        //     Cell reference for data
        //     Format Code
        //     Number of datapoints in series
        //     Data values
        //     Data Marker
        $dataSeriesValues1 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$C$2:$C$5', null, 4),
        ];

        // Build the dataseries
        $series1 = new DataSeries(
            DataSeries::TYPE_PIECHART, // plotType
            null, // plotGrouping (Pie charts don't have any grouping)
            range(0, count($dataSeriesValues1) - 1), // plotOrder
            $dataSeriesLabels1, // plotLabel
            $xAxisTickValues1, // plotCategory
            $dataSeriesValues1          // plotValues
        );

        // Set up a layout object for the Pie chart
        $layout1 = new Layout();
        $layout1->setShowVal(true);
        $layout1->setShowPercent(true);

        // Set the series in the plot area
        $plotArea1 = new PlotArea($layout1, [$series1]);
        // Set the chart legend
        $legend1 = new ChartLegend(ChartLegend::POSITION_RIGHT, null, false);

        $title1 = new Title('Test Pie Chart');

        // Create the chart
        $chart1 = new Chart(
            'chart1', // name
            $title1, // title
            $legend1, // legend
            $plotArea1, // plotArea
            true, // plotVisibleOnly
            DataSeries::EMPTY_AS_GAP, // displayBlanksAs
            null, // xAxisLabel
            null   // yAxisLabel - Pie charts don't have a Y-Axis
        );

        // Set the position where the chart should appear in the worksheet
        $chart1->setTopLeftPosition('A7');
        $chart1->setBottomRightPosition('H20');

        // Add the chart to the worksheet
        $worksheet->addChart($chart1);

        // Set the Labels for each data series we want to plot
        //     Datatype
        //     Cell reference for data
        //     Format Code
        //     Number of datapoints in series
        //     Data values
        //     Data Marker
        $dataSeriesLabels2 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$C$1', null, 1), // 2011
        ];
        // Set the X-Axis Labels
        //     Datatype
        //     Cell reference for data
        //     Format Code
        //     Number of datapoints in series
        //     Data values
        //     Data Marker
        $xAxisTickValues2 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, 'Worksheet!$A$2:$A$5', null, 4), // Q1 to Q4
        ];
        // Set the Data values for each data series we want to plot
        //     Datatype
        //     Cell reference for data
        //     Format Code
        //     Number of datapoints in series
        //     Data values
        //     Data Marker
        $dataSeriesValues2 = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, 'Worksheet!$C$2:$C$5', null, 4),
        ];

        // Build the dataseries
        $series2 = new DataSeries(
            DataSeries::TYPE_DONUTCHART, // plotType
            null, // plotGrouping (Donut charts don't have any grouping)
            range(0, count($dataSeriesValues2) - 1), // plotOrder
            $dataSeriesLabels2, // plotLabel
            $xAxisTickValues2, // plotCategory
            $dataSeriesValues2        // plotValues
        );

        // Set up a layout object for the Pie chart
        $layout2 = new Layout();
        $layout2->setShowVal(true);
        $layout2->setShowCatName(true);

        // Set the series in the plot area
        $plotArea2 = new PlotArea($layout2, [$series2]);

        $title2 = new Title('Test Donut Chart');

        // Create the chart
        $chart2 = new Chart(
            'chart2', // name
            $title2, // title
            null, // legend
            $plotArea2, // plotArea
            true, // plotVisibleOnly
            DataSeries::EMPTY_AS_GAP, // displayBlanksAs
            null, // xAxisLabel
            null   // yAxisLabel - Like Pie charts, Donut charts don't have a Y-Axis
        );

        // Set the position where the chart should appear in the worksheet
        $chart2->setTopLeftPosition('I7');
        $chart2->setBottomRightPosition('P20');

        // Add the chart to the worksheet
        $worksheet->addChart($chart2);

        $writer = new writeXlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix(); //путь до хранилища
        $writer->save($storagePath.'new.xlsx');
        return response()->download($storagePath.'new.xlsx');
        //return 'done';
    }

    private function cutLastChar(string $str,string $char) {
        if ($str[strlen($str)-1] == $char) {
            $str = substr($str,0,-1);
        }
        return $str;
    }
}
