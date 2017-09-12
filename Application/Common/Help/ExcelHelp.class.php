<?php
namespace Common\Help;
/**
 * 基础框架-助手类-Excel处理类
 */
class ExcelHelp extends BaseHelp
{

    /**
     * 仅仅用于展示使用PHP_EXCEL类
     */
    public function demo()
    {
        // 首先创建一个新的对象 PHPExcel object
        vendor("PHPEXCEL.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        
        // 设置文件的一些属性，在xls文件——>属性——>详细信息里可以看到这些值，xml表格里是没有这些值的
        $objPHPExcel->getProperties()
            -> // 获得文件属性对象，给下文提供设置资源
setCreator("Maarten Balliauw")
            -> // 设置文件的创建者
setLastModifiedBy("Maarten Balliauw")
            -> // 设置最后修改者
setTitle("Office 2007 XLSX Test Document")
            -> // 设置标题
setSubject("Office 2007 XLSX Test Document")
            -> // 设置主题
setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            -> // 设置备注
setKeywords("office 2007 openxml php")
            -> // 设置标记
setCategory("Test result file"); // 设置类别
                                                // 位置aaa *为下文代码位置提供锚
                                                // 给表格添加数据
        $objPHPExcel->setActiveSheetIndex(0)
            -> // 设置第一个内置表（一个xls文件里可以有多个表）为活动的
setCellValue('A1', 'Hello')
            -> // 给表的单元格设置数据
setCellValue('B2', 'world!')
            -> // 数据格式可以为字符串
setCellValue('C1', 12)
            -> // 数字型
setCellValue('D2', 12)
            -> //
setCellValue('D3', true)
            -> // 布尔型
setCellValue('D4', '=SUM(C1:D2)'); // 公式
                                                   
        // 得到当前活动的表,注意下文教程中会经常用到$objActSheet
        $objActSheet = $objPHPExcel->getActiveSheet();
        // 位置bbb *为下文代码位置提供锚
        // 给当前活动的表设置名称
        $objActSheet->setTitle('Simple2222');
        // 代码还没有结束，可以复制下面的代码来决定我们将要做什么
        // 我们将要做的是
        // 1,直接生成一个文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('myexchel.xlsx');
        
        // 2、提示下载文件
        // excel 2003 .xls
        // 生成2003excel格式的xls文件
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="01simple.xls"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
        
        // excel 2007 .xlsx
        // 生成2007excel格式的xlsx文件
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit();
        
        // pdf 文件
        // 下载一个pdf文件
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="01simple.pdf"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
        $objWriter->save('php://output');
        exit();
        // 生成一个pdf文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
        $objWriter->save('a.pdf');
        
        // CSV 文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV')->setDelimiter(',')
            -> // 设置分隔符
setEnclosure('"')
            -> // 设置包围符
setLineEnding("\r\n")
            -> // 设置行分隔符
setSheetIndex(0)
            -> // 设置活动表
save(str_replace('.php', '.csv', __FILE__));
        
        // HTML 文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'HTML'); // 将$objPHPEcel对象转换成html格式的
        $objWriter->setSheetIndex(0); // 设置活动表
                                      // $objWriter->setImagesRoot('http://www.example.com');
        $objWriter->save(str_replace('.php', '.htm', __FILE__)); // 保存文件
                                                                 
        // 设置表格样式和数据格式
                                                                 // 设置默认的字体和文字大小
        $objPHPExcel->getDefaultStyle()
            ->getFont()
            ->setName('Arial');
        $objPHPExcel->getDefaultStyle()
            ->getFont()
            ->setSize(20);
        
        // 日期格式
        // 获得秒值变量
        $dateTimeNow = time();
        // 三个表格分别设置为当前实际的 日期格式、时间格式、日期和时间格式
        // 首先将单元格的值设置为由PHPExcel_Shared_Date::PHPToExcel方法转换后的excel格式的值，然后用过得到该单元格的样式里面数字样式再设置显示格式
        $objActSheet->setCellValue('C9', PHPExcel_Shared_Date::PHPToExcel($dateTimeNow));
        $objActSheet->getStyle('C9')
            ->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
        $objActSheet->setCellValue('C10', PHPExcel_Shared_Date::PHPToExcel($dateTimeNow));
        $objActSheet->getStyle('C10')
            ->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4);
        $objActSheet->setCellValue('C10', PHPExcel_Shared_Date::PHPToExcel($dateTimeNow));
        $objActSheet->getStyle('C10')
            ->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4);
        // 将E4到E13的数字格式设置为EUR
        $objPHPExcel->getActiveSheet()
            ->getStyle('E4:E13')
            ->getNumberFormat()
            ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        
        // 置列的宽度
        $objActSheet->getColumnDimension('B')->setAutoSize(true); // 内容自适应
        $objActSheet->getColumnDimension('A')->setWidth(30); // 30宽
                                                              
        // 设置文件打印的页眉和页脚
                                                              // 设置打印时候的页眉页脚（设置完了以后可以通过打印预览来看效果）字符串中的&*好像是一些变量
        $objActSheet->getHeaderFooter()->setOddHeader('&L&G&C&HPlease treat this document as confidential!');
        $objActSheet->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()
            ->getTitle() . '&RPage &P of &N');
        
        // 设置页面文字的方向和页面大小
        $objPHPExcel->getActiveSheet()
            ->getPageSetup()
            ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $objPHPExcel->getActiveSheet()
            ->getPageSetup()
            ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4); // A4纸大小
                                                                                                                       
        // 为页眉添加图片 office中有效 wps中无效
        $objDrawing = new PHPExcel_Worksheet_HeaderFooterDrawing();
        $objDrawing->setName('PHPExcel logo');
        $objDrawing->setPath('./images/phpexcel_logo.gif');
        $objDrawing->setHeight(36);
        $objPHPExcel->getActiveSheet()
            ->getHeaderFooter()
            ->addImage($objDrawing, PHPExcel_Worksheet_HeaderFooter::IMAGE_HEADER_LEFT);
        
        // 设置单元格的批注
        // 给单元格添加批注
        $objPHPExcel->getActiveSheet()
            ->getComment('E13')
            ->setAuthor('PHPExcel'); // 设置作者
        $objCommentRichText = $objPHPExcel->getActiveSheet()
            ->getComment('E13')
            ->getText()
            ->createTextRun('PHPExcel:'); // 添加批注
        $objCommentRichText->getFont()->setBold(true); // 将现有批注加粗
        $objPHPExcel->getActiveSheet()
            ->getComment('E13')
            ->getText()
            ->createTextRun("\r\n"); // 添加更多批注
        $objPHPExcel->getActiveSheet()
            ->getComment('E13')
            ->getText()
            ->createTextRun('Total amount on the current invoice, including VAT.');
        $objPHPExcel->getActiveSheet()
            ->getComment('E13')
            ->setWidth('100pt'); // 设置批注显示的宽高 ，在office中有效在wps中无效
        $objPHPExcel->getActiveSheet()
            ->getComment('E13')
            ->setHeight('100pt');
        $objPHPExcel->getActiveSheet()
            ->getComment('E13')
            ->setMarginLeft('150pt');
        $objPHPExcel->getActiveSheet()
            ->getComment('E13')
            ->getFillColor()
            ->setRGB('EEEEEE'); // 设置背景色 ，在office中有效在wps中无效
                                                                                                   
        // 添加文字块 看效果图 office中有效 wps中无效
                                                                                                   // 大概翻译 创建一个富文本框 office有效 wps无效
        $objRichText = new PHPExcel_RichText();
        $objRichText->createText('This invoice is '); // 写文字
                                                      // 添加文字并设置这段文字粗体斜体和文字颜色
        $objPayable = $objRichText->createTextRun('payable within thirty days after the end of the month');
        $objPayable->getFont()->setBold(true);
        $objPayable->getFont()->setItalic(true);
        $objPayable->getFont()->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_DARKGREEN));
        $objRichText->createText(', unless specified otherwise on the invoice.');
        // 将文字写到A18单元格中
        $objPHPExcel->getActiveSheet()
            ->getCell('A18')
            ->setValue($objRichText);
        // 合并拆分单元格
        $objPHPExcel->getActiveSheet()->mergeCells('A28:B28'); // A28:B28合并
        $objPHPExcel->getActiveSheet()->unmergeCells('A28:B28'); // A28:B28再拆分
                                                                  
        // 单元格密码保护
                                                                  // 单元格密码保护不让修改
        $objPHPExcel->getActiveSheet()
            ->getProtection()
            ->setSheet(true); // 为了使任何表保护，需设置为真
        $objPHPExcel->getActiveSheet()->protectCells('A3:E13', 'PHPExcel'); // 将A3到E13保护 加密密码是 PHPExcel
        $objPHPExcel->getActiveSheet()
            ->getStyle('B1')
            ->getProtection()
            ->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED); // 去掉保护
                                                                                                                                            
        // 设置单元格字体
                                                                                                                                            // 将B1的文字字体设置为Candara，20号的粗体下划线有背景色
        $objPHPExcel->getActiveSheet()
            ->getStyle('B1')
            ->getFont()
            ->setName('Candara');
        $objPHPExcel->getActiveSheet()
            ->getStyle('B1')
            ->getFont()
            ->setSize(20);
        $objPHPExcel->getActiveSheet()
            ->getStyle('B1')
            ->getFont()
            ->setBold(true);
        $objPHPExcel->getActiveSheet()
            ->getStyle('B1')
            ->getFont()
            ->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
        $objPHPExcel->getActiveSheet()
            ->getStyle('B1')
            ->getFont()
            ->getColor()
            ->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
        
        // 文字对齐方式
        $objPHPExcel->getActiveSheet()
            ->getStyle('D11')
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT); // 水平方向上对齐
        $objPHPExcel->getActiveSheet()
            ->getStyle('A18')
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY); // 水平方向上两端对齐
        $objPHPExcel->getActiveSheet()
            ->getStyle('A18')
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); // 垂直方向上中间居中
                                                                                                                                      
        // 设置单元格边框
        $styleThinBlackBorderOutline = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN, // 设置border样式
                                                                   // 'style' => PHPExcel_Style_Border::BORDER_THICK, 另一种样式
                    'color' => array(
                        'argb' => 'FF000000'
                    )
                ) // 设置border颜色

            )
        );
        $objPHPExcel->getActiveSheet()
            ->getStyle('A4:E10')
            ->applyFromArray($styleThinBlackBorderOutline);
        
        // 背景填充颜色
        // 设置填充的样式和背景色
        $objPHPExcel->getActiveSheet()
            ->getStyle('A1:E1')
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()
            ->getStyle('A1:E1')
            ->getFill()
            ->getStartColor()
            ->setARGB('FF808080');
        // 综合设置样例
        $objPHPExcel->getActiveSheet()
            ->getStyle('A3:E3')
            ->applyFromArray(array(
            'font' => array(
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
            'borders' => array(
                'top' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                )
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startcolor' => array(
                    'argb' => 'FFA0A0A0'
                ),
                'endcolor' => array(
                    'argb' => 'FFFFFFFF'
                )
            )
        ));
        
        // 给单元格内容设置url超链接
        $objActSheet->getCell('E26')
            ->getHyperlink()
            ->setUrl('http://www.phpexcel.net'); // 超链接url地址
        $objActSheet->getCell('E26')
            ->getHyperlink()
            ->setTooltip('Navigate to website'); // 鼠标移上去连接提示信息
                                                                                              
        // 给表中添加图片
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName('Paid');
        $objDrawing->setDescription('Paid');
        $objDrawing->setPath('./images/paid.png'); // 图片引入位置
        $objDrawing->setCoordinates('B15'); // 图片添加位置
        $objDrawing->setOffsetX(210);
        $objDrawing->setRotation(25);
        $objDrawing->setHeight(36);
        $objDrawing->getShadow()->setVisible(true);
        $objDrawing->getShadow()->setDirection(45);
        $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        // 还可以添加有gd库生产的图片，详细见自带实例25
        
        // 创建一个新工作表和设置工作表标签颜色
        $objExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1); // 设置第2个表为活动表，提供操作句柄
        $objExcel->getSheet(1)->setTitle('测试2'); // 直接得到第二个表进行设置,将工作表重新命名为测试2
        $objPHPExcel->getActiveSheet()
            ->getTabColor()
            ->setARGB('FF0094FF'); // 设置标签颜色
                                                                                 
        // 添加或删除行和列
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(6, 10); // 在行6前添加10行
        $objPHPExcel->getActiveSheet()->removeRow(6, 10); // 从第6行往后删去10行
        $objPHPExcel->getActiveSheet()->insertNewColumnBefore('E', 5); // 从第E列前添加5类
        $objPHPExcel->getActiveSheet()->removeColumn('E', 5); // 从E列开始往后删去5列
                                                               
        // 隐藏和显示某列
        $objPHPExcel->getActiveSheet()
            ->getColumnDimension('C')
            ->setVisible(false); // 隐藏
        $objPHPExcel->getActiveSheet()
            ->getColumnDimension('D')
            ->setVisible(true); // 显示
                                                                                        
        // 重新命名活动的表的标签名称
        $objPHPExcel->getActiveSheet()->setTitle('Invoice');
        
        // 设置工作表的安全
        $objPHPExcel->getActiveSheet()
            ->getProtection()
            ->setPassword('PHPExcel');
        $objPHPExcel->getActiveSheet()
            ->getProtection()
            ->setSheet(true); // This should be enabled in order to enable any of the following!
        $objPHPExcel->getActiveSheet()
            ->getProtection()
            ->setSort(true);
        $objPHPExcel->getActiveSheet()
            ->getProtection()
            ->setInsertRows(true);
        $objPHPExcel->getActiveSheet()
            ->getProtection()
            ->setFormatCells(true);
        
        // 设置文档安全
        $objPHPExcel->getSecurity()->setLockWindows(true);
        $objPHPExcel->getSecurity()->setLockStructure(true);
        $objPHPExcel->getSecurity()->setWorkbookPassword("PHPExcel"); // 设置密码
                                                                       
        // 样式复制
                                                                       // 将B2的样式复制到B3至B7
        $objPHPExcel->getActiveSheet()->duplicateConditionalStyle($objPHPExcel->getActiveSheet()
            ->getStyle('B2')
            ->getConditionalStyles(), 'B3:B7');
        
        // Add conditional formatting
        echo date('H:i:s'), " Add conditional formatting", PHP_EOL;
        $objConditional1 = new PHPExcel_Style_Conditional();
        $objConditional1->setConditionType(PHPExcel_Style_Conditional::CONDITION_CELLIS);
        $objConditional1->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_BETWEEN);
        $objConditional1->addCondition('200');
        $objConditional1->addCondition('400');
        
        // 设置分页（主要用于打印）
        // 设置某单元格为页尾
        $objPHPExcel->getActiveSheet()->setBreak('A' . $i, PHPExcel_Worksheet::BREAK_ROW);
        
        // 用数组填充表
        // 吧数组的内容从A2开始填充
        $dataArray = array(
            array(
                "2010",
                "Q1",
                "United States",
                790
            ),
            array(
                "2010",
                "Q2",
                "United States",
                730
            )
        );
        $objPHPExcel->getActiveSheet()->fromArray($dataArray, NULL, 'A2');
        
        // 设置自动筛选
        $objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()
            ->calculateWorksheetDimension());
        // $objPHPExcel->getActiveSheet()->calculateWorksheetDimension()....得到A1行的所有内容个
        
        // 打印出的到所有的公式
        $objCalc = PHPExcel_Calculation::getInstance();
        print_r($objCalc->listFunctionNames());
        
        // 设置单元格值的范围
        $objValidation = $objPHPExcel->getActiveSheet()
            ->getCell('B3')
            ->getDataValidation();
        $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_WHOLE);
        $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $objValidation->setAllowBlank(true);
        $objValidation->setShowInputMessage(true); // 设置显示提示信息
        $objValidation->setShowErrorMessage(true); // 设置显示错误信息
        $objValidation->setErrorTitle('Input error'); // 错误标题
                                                      // $objValidation->setShowDropDown(true);
        $objValidation->setError('Only numbers between 10 and 20 are allowed!'); // 错误内容
        $objValidation->setPromptTitle('Allowed input'); // 设置提示标题
        $objValidation->setPrompt('Only numbers between 10 and 20 are allowed.'); // 提示内容
        $objValidation->setFormula1(10); // 设置最大值
        $objValidation->setFormula2(120); // 设置最小值
                                          // 或者这样设置 $objValidation->setFormula2(1,5,6,7); 设置值是1，5，6，7中的一个数
                                          
        // 其他
        $objPHPExcel->getActiveSheet()
            ->getStyle('B5')
            ->getAlignment()
            ->setShrinkToFit(true); // 长度不够显示的时候 是否自动换行
        $objPHPExcel->getActiveSheet()
            ->getStyle('B5')
            ->getAlignment()
            ->setShrinkToFit(true); // 自动转换显示字体大小,使内容能够显示
        $objPHPExcel->getActiveSheet()
            ->getCell(B14)
            ->getValue(); // 获得值，有可能得到的是公式
        $objPHPExcel->getActiveSheet()
            ->getCell(B14)
            ->getCalculatedValue(); // 获得算出的值
                                                                                
        // 导入或读取文件
                                                                                // 通过PHPExcel_IOFactory::load方法来载入一个文件，load会自动判断文件的后缀名来导入相应的处理类，读取格式保含xlsx/xls/xlsm/ods/slk/csv/xml/gnumeric
        $objPHPExcel = PHPExcel_IOFactory::load();
        // 吧载入的文件默认表（一般都是第一个）通过toArray方法来返回一个多维数组
        $dataArray = $objPHPExcel->getActiveSheet()->toArray();
        // 读完直接写到一个xlsx文件里
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); // $objPHPExcel是上文中读的资源
        $objWriter->save(str_replace('.php', '.xlsx', __FILE__));
        
        // 读取xml文件
        $objReader = PHPExcel_IOFactory::createReader('Excel2003XML');
        $objPHPExcel = $objReader->load("Excel2003XMLTest.xml");
        // 读取ods文件
        $objReader = PHPExcel_IOFactory::createReader('OOCalc');
        $objPHPExcel = $objReader->load("OOCalcTest.ods");
        // 读取numeric文件
        $objReader = PHPExcel_IOFactory::createReader('Gnumeric');
        $objPHPExcel = $objReader->load("GnumericTest.gnumeric");
        // 读取slk文件
        $objPHPExcel = PHPExcel_IOFactory::load("SylkTest.slk");
        
        // 循环遍历数据
        $objReader = PHPExcel_IOFactory::createReader('Excel2007'); // 创建一个2007的读取对象
        $objPHPExcel = $objReader->load("05featuredemo.xlsx"); // 读取一个xlsx文件
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) { // 遍历工作表
            echo 'Worksheet - ', $worksheet->getTitle(), PHP_EOL;
            foreach ($worksheet->getRowIterator() as $row) { // 遍历行
                echo '    Row number - ', $row->getRowIndex(), PHP_EOL;
                $cellIterator = $row->getCellIterator(); // 得到所有列
                $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
                foreach ($cellIterator as $cell) { // 遍历列
                    if (! is_null($cell)) { // 如果列不给空就得到它的坐标和计算的值
                        echo '        Cell - ', $cell->getCoordinate(), ' - ', $cell->getCalculatedValue(), PHP_EOL;
                    }
                }
            }
        }
        
        // 吧数组插入的表中
        // 插入的数据 3行数据
        $data = array(
            array(
                'title' => 'Excel for dummies',
                'price' => 17.99,
                'quantity' => 2
            ),
            array(
                'title' => 'PHP for dummies',
                'price' => 15.99,
                'quantity' => 1
            ),
            array(
                'title' => 'Inside OOP',
                'price' => 12.95,
                'quantity' => 1
            )
        );
        $baseRow = 5; // 指定插入到第5行后
        foreach ($data as $r => $dataRow) {
            $row = $baseRow + $r; // $row是循环操作行的行号
            $objPHPExcel->getActiveSheet()->insertNewRowBefore($row, 1); // 在操作行的号前加一空行，这空行的行号就变成了当前的行号
                                                                        // 对应的咧都附上数据和编号
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $row, $r + 1);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $row, $dataRow['title']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $row, $dataRow['price']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $row, $dataRow['quantity']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $row, '=C' . $row . '*D' . $row);
        }
        $objPHPExcel->getActiveSheet()->removeRow($baseRow - 1, 1); // 最后删去第4行，这是示例需要，在此处为大家提供删除实例
    }

    /**
     * 导出EXCEL
     * 
     * @param $[config] [格式：$config
     *            = array(
     *            array('title' =>'姓名','name' =>'username','size' =>15,'callback' =>''),
     *            array('title' =>'性别','name' =>'sex','size' =>10,'callback' =>''),
     *            );
     *            $config里面的字段值(username),应该是$data里面显示的字段值
     * @param $data array()
     *            获取的二维数组（不能是树形结构）
     *            @time 2016-10-10
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public function create_excel($config, $data)
    {
        /**
         * 实例化PHPExcel对象，并配置文件基本信息
         */
        vendor("PHPEXCEL.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
            ->setCreator("sunrun")
            ->setLastModifiedBy("sunrun")
            ->setKeywords("sunrun")
            ->setCategory("zstyle");
        /**
         * 设置保存的表名
         */
        $data['sheetName'] = $data['sheetName'] ? $data['sheetName'] : 'sheet1';
        /**
         * 总页数集合
         */
        $cellName = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI',
            'AJ',
            'AK',
            'AL',
            'AM',
            'AN',
            'AO',
            'AP',
            'AQ',
            'AR',
            'AS',
            'AT',
            'AU',
            'AV',
            'AW',
            'AX',
            'AY',
            'AZ'
        );
        /**
         * 统计工作表展示的列数
         */
        $count_config = count($config);
        /**
         * 设置操作的工作表
         */
        $objPHPExcel->setActiveSheetIndex('0');
        /**
         * 设置工作表的名字
         */
        $objPHPExcel->getActiveSheet()->setTitle($data['sheetName']);
        
        for ($i = 0; $i < $count_config; $i ++) {
            /**
             * 设置工作表的第一栏标题
             */
            $objPHPExcel->setActiveSheetIndex('0')->setCellValue($cellName[$i] . '1', $config[$i]['title']);
            /**
             * 设置表格宽度
             */
            $objPHPExcel->getActiveSheet()
                ->getColumnDimension($cellName[$i])
                ->setWidth($config[$i]['size']);
        }
        // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
        /**
         * 设置列的对齐方式
         */
        // $objPHPExcel->getActiveSheet()->getStyle(C)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        /**
         * 设置列的输出格式
         */
        // $objPHPExcel->getActiveSheet()->getStyle(A)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $i = 2;
        foreach ($data['result'] as $key => $item) {
            /**
             * 设置单个元的值
             */
            for ($j = 0; $j < $count_config; $j ++) {
                if (stristr($item[$config[$j]['name']], 'Uploads')) {
                    $objPHPExcel->getActiveSheet()
                        ->getRowDimension($i)
                        ->setRowHeight(50); // 设置行高
                    $objDrawing = new \PHPExcel_Worksheet_Drawing();
                    $objDrawing->setName('Paid');
                    $objDrawing->setDescription('Paid');
                    $objDrawing->setPath($this->get_true_path($item[$config[$j]['name']])); // 图片引入位置
                    $objDrawing->setHeight(50);
                    $objDrawing->setCoordinates($cellName[$j] . $i); // 图片添加位置
                                                                     // $objDrawing->setOffsetX(50);
                                                                     // $objDrawing->setRotation(45);
                                                                     // $objDrawing->getShadow()->setVisible (true );
                                                                     // $objDrawing->getShadow()->setDirection(45);
                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue($cellName[$j] . $i, $item[$config[$j]['name']]);
                }
            }
            $i ++;
        }
        /**
         * 导出EXCEL
         */
        $outputFileName = $data['sheetName'] . '.xls';
        $xlsWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outputFileName . '"');
        header('Cache-Control: max-age=0');
        header("Pragma: no-cache");
        $xlsWriter->save("php://output");
        exit();
    }

    /**
     * 导入EXCEL
     * 
     * @param $[config] [格式：$config
     *            = array(
     *            ['昵称','nickname','15'],
     *            ['联系方式','mobile','20'],
     *            );
     * @param $file_path 文件路径            
     * @param $method [<回调处理函数>]
     *            @time 2016-10-10
     * @author 陶君行<Silentlytao@outlook.com>
     */
    public function load_excel($config, $file_path = '')
    {
        /**
         * 引入PHPEXCEL类
         */
        vendor("PHPEXCEL.PHPExcel");
        /**
         * 总页数集合
         */
        /**
         * xls、xlsx格式都可导入
         */
        $objPHPRead = PHPExcel_IOFactory::createReaderForFile($file_path);
        /**
         * 忽略单元格格式
         */
        $objPHPRead->setReadDataOnly(true);
        /**
         * 实例化PHPExcel对象
         */
        $objPHPExcel = $objPHPRead->load($file_path);
        /**
         * 获取当前工作表
         */
        $objWorksheet = $objPHPExcel->getActiveSheet();
        /**
         * 获取行数
         */
        $highestRow = $objWorksheet->getHighestRow();
        /**
         * 获取最后一列的列数
         */
        $highestColumn = $objWorksheet->getHighestColumn();
        /**
         * 获取最后一列对应的列数 数字
         */
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        
        $excelData = array();
        for ($row = 2; $row <= $highestRow; $row ++) {
            for ($col = 0; $col < $highestColumnIndex; $col ++) {
                if ($config[$col]['callback']) {
                    $excelData[$row][$config[$col]['name']] = $config[$col]['callback']((string) $objWorksheet->getCellByColumnAndRow($col, $row)->getValue());
                } else {
                    $excelData[$row][$config[$col]['name']] = (string) $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                }
            }
        }
        if (empty($excelData) || count($excelData['2']) !== count($config)) {
            return false;
        }
        return array_values($excelData);
    }
}
?>