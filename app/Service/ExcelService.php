<?php

namespace App\Service;

use Hyperf\Utils\ApplicationContext;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ExcelService
{
    public static $instance;

    /**
     * 通过延迟加载（用到时才加载）获取实例
     * @return self
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * import
     * 导入
     */
    public function import($file,$ext, int $sheet = 0, int $columnCnt = 0)
    {
        //设置图片路径
        $folder_name = BASE_PATH . '/public/uploads/images/';
        if (!is_dir($folder_name)) {
            mkdir($folder_name, 0775,true);
        }
        try {
            if($ext == 'xls'){
//                $objRead = IOFactory::createReader('Xls');
                $objRead = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }else{
//                $objRead = IOFactory::createReader('Xlsx');
                $objRead = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            $obj = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);;
            $currSheet = $obj->getSheet($sheet);
            /* 获取总行数 */
            $rowCnt = $currSheet->getHighestRow()-1;
            $data = $currSheet->toArray();
            unset($data[$rowCnt]);//删除多余的最后一行
            foreach ($currSheet->getDrawingCollection() as $drawing) {
                list($startColumn, $startRow) = Coordinate::coordinateFromString($drawing->getCoordinates());
                $imageFileName = $drawing->getCoordinates().random(5).mt_rand(1000, 9999);
                @ob_end_clean();
                switch ($drawing->getExtension()) {
                    case 'jpg':
                    case 'jpeg':
                        $imageFileName .= '.jpg';
                        $source = imagecreatefromjpeg($drawing->getPath());
                        imagejpeg($source, $folder_name . $imageFileName);
                        break;
                    case 'gif':
                        $imageFileName .= '.gif';
                        $source = imagecreatefromgif($drawing->getPath());
                        imagegif($source, $folder_name . $imageFileName);
                        break;
                    case 'png':
                        $imageFileName .= '.png';
                        $source = imagecreatefrompng($drawing->getPath());
                        imagepng($source, $folder_name.$imageFileName);
                        break;
                }
                $ten = 0;
                $len = strlen($startColumn);
                for($i=1;$i<=$len;$i++){
                    $char = substr($startColumn,0-$i,1);//反向获取单个字符
                    $int = ord($char);
                    $ten += ($int-65)*pow(26,$i-1);
                }
                $startColumn = $ten;
                $data[$startRow-1][$startColumn] = $folder_name . $imageFileName;
            }
            return $data;
        } catch (\Exception $e) {
            return fail($e->getMessage());
        }
    }

    /**
     * export
     * 导出
     */
    public function export($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        //设置sheet的名字  两种方法
        $sheet->setTitle('phpspreadsheet——demo');
        $spreadsheet->getActiveSheet()->setTitle('Hello');
        //设置第一行小标题
        $k = 1;
        $sheet->setCellValue('A' . $k, '问题');
        $sheet->setCellValue('B' . $k, '选项');
        $sheet->setCellValue('C' . $k, '答案');
        $sheet->setCellValue('D' . $k, '图片');

        // 设置个表格宽度
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(16);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(80);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);

        // 垂直居中
        $spreadsheet->getActiveSheet()->getStyle('A')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $spreadsheet->getActiveSheet()->getStyle('B')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $spreadsheet->getActiveSheet()->getStyle('C')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $spreadsheet->getActiveSheet()->getStyle('D')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $info = $data;
        //  设置A单元格的宽度 同理设置每个
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        //  设置第三行的高度
        $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(50);
        //  A1水平居中
        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1')->applyFromArray($styleArray);
        //  将A3到D4合并成一个单元格
        $spreadsheet->getActiveSheet()->mergeCells('A3:D4');
        //  拆分合并单元格
        $spreadsheet->getActiveSheet()->unmergeCells('A3:D4');
        //  将A2到D8表格边框 改变为红色
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => 'FFFF0000'],
                ],
            ],
        ];
        //  $sheet->getStyle('A2:E8')->applyFromArray($styleArray);
        //  设置超链接
        //  $sheet->setCellValue('D6', 'www.baidu.com');
        //  $spreadsheet->getActiveSheet()->setCellValue('E6', 'www.baidu.com');
        //  循环赋值
        $k = 2;
        foreach ($info as $key => $value) {
            $sheet->setCellValue('A' . $k, $value['question']);
            $sheet->setCellValue('B' . $k, $value['question_options']);
            $sheet->setCellValue('C' . $k, $value['answer']);

            $img = httpRequest($value['img']);
            $dir = BASE_PATH . '/public/uploads/images';
            $file_info = pathinfo($value['img']);
            if (!empty($file_info['basename'])) { //过滤非文件类型
                $basename = $file_info['basename'];
                is_dir($dir) OR mkdir($dir, 0777, true); //进行检测文件是否存在
                file_put_contents($dir . $basename, $img);

                $drawing[$k] = new Drawing();
                $drawing[$k]->setName('Logo');
                $drawing[$k]->setDescription('Logo');
                $drawing[$k]->setPath($dir . $basename);
                $drawing[$k]->setWidth(80);
                $drawing[$k]->setHeight(80);
                $drawing[$k]->setCoordinates('D' . $k);
                $drawing[$k]->setOffsetX(12);
                $drawing[$k]->setOffsetY(12);
                $drawing[$k]->setWorksheet($spreadsheet->getActiveSheet());
            } else {
                $sheet->setCellValue('D' . $k, '');
            }
            $sheet->getRowDimension($k)->setRowHeight(80);
            $k++;
        }
        $file_name = date('Y-m-d', time()) . rand(1000, 9999);
        //  第一种保存方式
        /*$writer = new Xlsx($spreadsheet);
        //保存的路径可自行设置
        $file_name = '../'.$file_name . ".xlsx";
        $writer->save($file_name);*/
        //  第二种直接页面上显示下载
        $file_name = $file_name . ".xls";
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $file_name . '"');
        header('Cache-Control: max-age=0');
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');
        //  注意createWriter($spreadsheet, 'Xls') 第二个参数首字母必须大写
        $writer->save('php://output');
    }
}