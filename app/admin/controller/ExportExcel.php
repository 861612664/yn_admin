<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ExportExcel extends Base
{
    public static function export($th = [],$tr = [],$filename = '数据导出'){

        // $th = [
        //     ['field'=>'title','text'=>'标题','width'=>'20'],
        //     ['field'=>'name','text'=>'姓名'],
        // ];
        // $tr = [
        //     ['title'=>'测试','name'=>'测试姓名'],
        //     ['title'=>'测试2','name'=>'测试姓名2'],
        // ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle($filename);
        
        foreach ($th as $k => $v) {
            $column = Coordinate::stringFromColumnIndex($k + 1);
            $sheet->setCellValue($column . 1, $v['text']);
            if(!empty($v['width'])){
                $sheet->getColumnDimension($column)->setWidth($v['width']);
            }
        }
        
        foreach ($tr as $key => $value) {
            foreach ($th as $k => $v) {
                $column = Coordinate::stringFromColumnIndex($k + 1);
                
                $i = $key + 2; //表格是从2开始的
                //dd($v);
                $field_arr = explode('.',$v['field']);
                $text = count($field_arr)>1?$value[$field_arr[0]][$field_arr[1]]:$value[$field_arr[0]];
                $sheet->setCellValue($column . $i, $text);
            }
        }

        $filename = urlencode($filename) . '.xlsx';
        // make file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    
    
}