<?php


namespace app\api\model;


use think\Controller;
use think\Db;
use think\Model;
use Workerman\Events\React\Base;

class Excel extends Model
{
    /**
     * 将数据库数据导出为excel文件,超链接访问
     */
    function downLoadExcel($data)
    {
        //引入文件
        vendor("PHPExcel.PHPExcel");

        $objPHPExcel = new \PHPExcel();

        // 设置sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // 设置列的宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        // 设置表头
        $objPHPExcel->getActiveSheet()->SetCellValue('A1', '用户名');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', '用户电话');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', '端口');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', '提交页面');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', '提交时间');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', '回访状态');
        //存取数据
        $num = 2;
        foreach ($data as $k => $v) {

            $objPHPExcel->getActiveSheet()->SetCellValue('A' . $num, $v['username']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $num, $v['tel']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $num, $v['tel']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D' . $num, $v['special']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $num, $v['time']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $num, $v['remarks']);
            $num++;
        }

        $start = date('Y-m-d',$kaishi);
        $end = date('Y-m-d',$jieshu);
        // 文件名称
        $fileName = "留言('$start'~'$end')";
        $xlsName = iconv('utf-8', 'gb2312', $fileName);

        // 设置工作表名
        $objPHPExcel->getActiveSheet()->setTitle('sheet');



        //下载
        //进行多个文件压缩
        $zip = new \ZipArchive();
        $filename = "各省用户数据.zip";
        $zip->open($filename, \ZipArchive::CREATE);   //打开压缩包
        foreach ($fileNameArr as $file) {
            //$zip->addFile($file, basename($file));   //向压缩包中添加文件
            $zip->addFromString($file,file_get_contents($file)); //向压缩包中添加文件
        }
        $zip->close();  //关闭压缩包
        foreach ($fileNameArr as $file) {
            unlink($file); //删除csv临时文件
        }
        //输出压缩文件提供下载
        header("Cache-Control: max-age=0");
        header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename=' . iconv('utf-8','gbk//ignore',$filename)); // 文件名
        //header('Content-disposition: attachment; filename=' . basename($filename)); // 文件名
        header("Content-Type: application/zip"); // zip格式的
        header("Content-Transfer-Encoding: binary"); //
        header('Content-Length: ' . filesize($filename)); //
//
//        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
//
//        // $objWriter->save(str_replace('.php', '.xls', dirname(dirname(dirname(dirname(__FILE__)))).'/public/Userbalanceh.xls'));
//        ob_end_clean();     // 清除缓冲区,避免乱码
//
//        header("Pragma: public");
//        header("Expires: 0");
//        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
//        header("Content-Type:application/force-download");
//        header("Content-Type:application/vnd.ms-execl;charset=UTF-8");
//        header("Content-Type:application/octet-stream");
//        header("Content-Type:application/download");
//        header("Content-Disposition:attachment;filename=" . $xlsName . ".xls");
//        header("Content-Transfer-Encoding:binary");
//
//        $objWriter->save("php://output");
    }
}