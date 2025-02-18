<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/25
 * Time: 14:16
 */
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\facade\Session;
use PHPExcel_Cell_DataType;
use PHPExcel_IOFactory;
use PHPExcel;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
class Phpexcil extends Basess
{

    //公共导入方法返回数组
    public static function import($kname)
    {
        $file = request()->file('excel');
        $info = $file->validate(['ext' => 'xlsx,xls'])->move('./upload/file/');
        if(empty($info)){
             self::return_data_sta(0,10000,'请上传正确格式的文件');
        }
        $exclePath = $info->getSaveName();
        $filename = './upload/file/'. $exclePath;
        $extension = strtolower( pathinfo($filename, PATHINFO_EXTENSION) );
        if($extension == 'xlsx') {
            $objReader =\PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }else if($extension == 'xls'){
            $objReader =\PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet-> getHighestRow();
        $excel_array= $sheet->toArray();
        if(count($excel_array[0])!=count($kname)){
             self::return_data_sta(0,10000,'请上传正常的模板文件');
        }
        $fils = array_serch($kname,$excel_array);
        foreach ($fils as $kl=>&$vl){
            if($kl==0){
                unset($fils[$kl]);
            }
            if($vl['cur_name']==null){
                unset($fils[$kl]);
            }
        }
        return $fils;
    }


    public static function import_all($kname)
    {
        $file = request()->file('excel');
        if (empty($file))
        {
            self::return_data(0,10001, '缺少上传文件excel.');
            exit();
        }
        $info = $file->validate(['ext' => 'xlsx,xls'])->move('./upload/file/');
        if(empty($info)){
            self::return_data_sta(0,10000,'请上传正确格式的文件');
        }
        $exclePath = $info->getSaveName();
        $filename = './upload/file/'. $exclePath;
        $extension = strtolower( pathinfo($filename, PATHINFO_EXTENSION) );
        if($extension == 'xlsx') {
            $objReader =\PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }else if($extension == 'xls'){
            $objReader =\PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }
        $sheet = $objPHPExcel->getSheet(0);
        $excel_array= $sheet->toArray();
        foreach ($excel_array as $ks=>$vs)
        {
            if($vs[0]==null)
            {
                unset($excel_array[$ks]);
            }
        }
        return $excel_array;


    }


    //通用导出方法
    public static function export($filename,$expCellName,$expTableData){
        //1.从数据库中取出数据
        //3.实例化PHPExcel类
        $objPHPExcel = new PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        //创建颜色对象，设置颜色像css那样简单的传个色值，需要传对象
        $styleArray = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FF0000'),
                'size'  => 15,
                'name'  => 'Verdana'
            ));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->applyFromArray($styleArray);
        }
        //设置宽高
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(30);
        }
        //设置第二行内容
//        for($i=0;$i<$cellNum;$i++){
//            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][0]);
//        }
        //循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<$dataNum;$i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        //7.设置保存的Excel表格名称
        $filename = $filename.date('ymd',time()).'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('学生信息');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }

    public static  function  explords($filename,$expCellName,$expTableData)
    {

        $objPHPExcel = new PHPExcel();
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        //5.设置表格头（即excel表格的第一行）
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $styleArray = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FF0000'),
                'size'  => 15,
                'name'  => 'Verdana'
            ));
        //创建颜色对象，设置颜色像css那样简单的传个色值，需要传对象
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->applyFromArray($styleArray);
        }
        //设置宽高
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(30);
        }
        //设置第二行内容
//        for($i=0;$i<$cellNum;$i++){
//            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][0]);
//        }
        //循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<$dataNum;$i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        //7.设置保存的Excel表格名称
        $filename = $filename.date('ymd',time()).'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('学生信息');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }


    //通用导出方法
    public static function export_tow_aaa($filename,$expCellName,$expTableData,$fllist){
        //5.设置表格头（即excel表格的第一行）
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        //3.实例化PHPExcel类
        $objPHPExcel = new PHPExcel();
        $highestRow = 1000;
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        for($i=0;$i<$highestRow;$i++){
        $objValidation = $objActSheet->getCell('B'.($i+2))->getDataValidation(); //这一句为要设置数据有效性的单元格
        $objValidation -> setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
            -> setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
            -> setAllowBlank(false)
            -> setShowInputMessage(true)
            -> setShowErrorMessage(true)
            -> setShowDropDown(true)
            -> setErrorTitle('输入的值有误')
            -> setError('您输入的值不在下拉框列表内.')
            -> setPromptTitle('课程分类')
            -> setFormula1('"' . $fllist . '"');
        }
        //4.激活当前的sheet表
        $objPHPExcel->setActiveSheetIndex(0);
        $styleArray = array(
            'font'  => array(
                'bold'  => true,
                'color' => array('rgb' => 'FF0000'),
                'size'  => 15,
                'name'  => 'Verdana'
            ));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i].'1')->applyFromArray($styleArray);
        }
        //设置宽高
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth(30);
        }
        //设置第二行内容
//        for($i=0;$i<$cellNum;$i++){
//            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][0]);
//        }
        //循环刚取出来的数组，将数据逐一添加到excel表格。
        for($i=0;$i<$dataNum;$i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);
            }
        }
        //7.设置保存的Excel表格名称
        $filename = $filename.date('ymd',time()).'.xls';
        //8.设置当前激活的sheet表格名称；
        $objPHPExcel->getActiveSheet()->setTitle('学生信息');
        //9.设置浏览器窗口下载表格
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$filename.'"');
        //生成excel文件
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        //下载文件在浏览器窗口
        $objWriter->save('php://output');
        exit;
    }
}
