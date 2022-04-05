<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/18
 * @Time: 16:15
 */

namespace api\module;



class Api
{
    public function synchronizeRepo( $project_name, $root_dir, $address, $repository_type, $base_url, $url, $param, $repo_token, $owner)
    {
        try
        {
            $tool = new \tool\module\Tool();
            $api_dir_url = "";
            if ($url == "/")
            {
                if ($repository_type == 0)
                {
                    $api_dir_url = $base_url."/tree".$param;
                }
                elseif ($repository_type == 1)
                {
                    $api_dir_url = $base_url . $param;
                }
                elseif ($repository_type == 2)
                {
                    $api_dir_url = $base_url . $param;
                }
            }
            else
            {
                // 去掉前面的反斜杠
                $url = ltrim($url, "/");
                if ($repository_type == 0)
                {
                    $api_dir_url =$base_url."/tree".$param."&path=".$url;
                }
                elseif ($repository_type == 1)
                {
                    $api_dir_url = $base_url . "/" . $url . $param;
                }
                elseif ($repository_type == 2)
                {
                    $api_dir_url = $base_url . "/" . $url . $param;
                }
            }
            $api_file_list = $tool->getUrlContentForSwagger($api_dir_url, 10, $repository_type, $repo_token, $owner);
            $api_file_list = json_decode($api_file_list,true);
            foreach ($api_file_list as $api)
            {
                if($api["type"] == "tree" || $api['type'] == "dir")
                {
                    $path = $api["path"];
                    $name = $api['name'];
                    if ($root_dir == '/')
                    {
                        $dir =  $name;

                    }
                    else
                    {
                        $dir = $root_dir . '/' . $name;
                    }
                    // node创建目录
                    $project_module = new \project\module\Project();

                    // 向Node发请求创建项目
                    $oRet = $project_module->createDir($address, $project_name, $dir);
                    // 报错
                    if (!$oRet) {
                        throw new \Exception("创建文件夹失败");
                    }

                    // 静态文件创建目录
                    $project_name = urldecode($project_name);
                    $sFullDirPath = STATIC_PROJECTS . '/' . $address . '/' . $project_name . '/' . $dir;
                    $file_module = new \file\module\File();
                    // 目录不存在，创建
                    $file_module->mkdir($sFullDirPath);


                    // 继续递归
                    $this->synchronizeRepo( $project_name, $dir, $address, $repository_type, $base_url, $path, $param, $repo_token, $owner);
                }
                elseif($api["type"] == "blob" && $repository_type == 0)
                {
                    $file_path = $base_url ."/files/" .urlencode($api['path']) .$param;
                    // 创建文件
                    $name = $api['name'];
                    $file_module = new \file\module\File();
                    $ext = $file_module->getFileExtension($name);
                    if ($file_module->isResourceType($ext)) {

                        if (!$root_dir) {
                            $root_dir = '';
                        }
                        $uploadDirPath = STATIC_PROJECTS. '/' . $address. '/' . urldecode($project_name) . '/' . $root_dir;
                        // 目录不存在则创建
                        $file_module->mkdir( $uploadDirPath);
                        $content = $tool->getUrlContentForSwagger($file_path,15,$repository_type,$repo_token,$owner,true);
                        $fp = fopen($uploadDirPath . '/' . $name,'w');//保存的文件名称用的是链接里面的名称
                        fwrite($fp, $content);
                        fclose($fp);
                        $oRet = $file_module->saveResource($address, $project_name, $root_dir, $uploadDirPath . '/' . $name, $name);
                        if (!$oRet) {
                            throw new \Exception("上传文件失败，请重新再试");
                        }
                    }
                    else
                    {
                        $content = $tool->getUrlContentForSwagger($file_path,5,$repository_type,$repo_token,$owner,true);

//                        $content = $file_module->convertStringEncoding($content);
                        if ($root_dir)
                        {
                            $dir = $root_dir . '/' . $name;
                        }
                        else
                        {
                            $dir =  $name;
                        }

                        $oRet = $file_module->saveFile($address, $project_name, $dir, $content);


                        if (!$oRet) {

                            throw new \Exception("上传文件失败，请重新再试");
                        }

                        // 修改文件内容
                        $project_name = urldecode($project_name);
                        $uploadDirPath = STATIC_PROJECTS . '/' . $address . '/' . $project_name;
                        if ($dir) {
                            $uploadDirPath .= '/' . $dir;
                        }
                        if ($content)
                        {
                            $bRet = $file_module->write($uploadDirPath, $content);
                            if (!$bRet) {
                                throw new \Exception("修改文件内容完成，但是文件系统没有写入");
                            }
                        }

                    }
                }
                elseif ($api['type'] == "file" && ($repository_type == 1 || $repository_type == 2))
                {
                    $file_path = $base_url ."/" . $api['path'] .$param;
                    // 创建文件
                    $name = $api['name'];

                    $file_module = new \file\module\File();
                    $ext = $file_module->getFileExtension($name);
                    if ($file_module->isResourceType($ext)) {

                        if (!$root_dir) {
                            $root_dir = '';
                        }
                        $uploadDirPath = STATIC_PROJECTS. '/' . $address. '/' . urldecode($project_name) . '/' . $root_dir;
                        // 目录不存在则创建
                        $file_module->mkdir( $uploadDirPath);
                        $content = $tool->getUrlContentForSwagger($file_path,5,$repository_type,$repo_token,$owner,true);
                        $fp = fopen($uploadDirPath . '/' . $name,'w');//保存的文件名称用的是链接里面的名称
                        fwrite($fp, $content);
                        fclose($fp);
                        $oRet = $file_module->saveResource($address, $project_name, $root_dir, $uploadDirPath . '/' . $name, $name);

                        if (!$oRet) {
                            throw new \Exception("上传文件失败，请重新再试");
                        }
                    }
                    else
                    {
                        $content = $tool->getUrlContentForSwagger($file_path,5,$repository_type,$repo_token,$owner,true);

//                        $content = $file_module->convertStringEncoding($content);

                        if ($root_dir)
                        {
                            $dir = $root_dir . '/' . $name;
                        }
                        else
                        {
                            $dir =  $name;
                        }

                        $oRet = $file_module->saveFile($address, $project_name, $dir, $content);


                        if (!$oRet) {

                            throw new \Exception("上传文件失败，请重新再试");
                        }

                        // 修改文件内容
                        $project_name = urldecode($project_name);
                        $uploadDirPath = STATIC_PROJECTS . '/' . $address . '/' . $project_name;

                        if ($dir) {
                            $uploadDirPath = $uploadDirPath .'/' . $dir;
                        }
                        if ($content)
                        {
                            $bRet = $file_module->write($uploadDirPath, $content);
                            if (!$bRet) {
                                throw new \Exception("修改文件内容完成，但是文件系统没有写入");
                            }
                        }
                    }
                }
                else
                {
                    continue;
                }
            }
            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }

    }

    public function getProjectContentList($target_address, $target_project,  $target_path, &$result, $language)
    {
        $project_module = new \project\module\Project();
        // 先判断项目是否存在
        $oRet = $project_module->getProjectDetail($target_address, $target_project, $target_path);
        $oRet = json_decode($oRet, true);
        if ($oRet['iCode'] != 0)
        {
            return false;
        }
        else
        {
            foreach ($oRet['oRet'] as $file)
            {
                if ($file['type'] == "directory")
                {
                    if ($target_path == "/")
                    {
                        $this->getProjectContentList($target_address,$target_project,$file['name'], $result, $language);
                    }
                    else
                    {
                        $this->getProjectContentList($target_address,$target_project,$target_path .'/' .$file['name'], $result, $language);
                    }
                }
                elseif ($file['type'] == "file" )
                {
                    if ($language == "java" && substr($file['name'],-5) == ".java")
                    {
                        $result[$file['hash']] = $file['name'];
                    }
                    elseif ($language == "php" && substr($file['name'],-4) == ".php")
                    {
                        $result[$file['hash']] = $file['name'];
                    }
                    else
                    {
                        continue;
                    }
                }
                else
                {
                    continue;
                }
            }
        }
        return $oRet;
    }



    public function getWord($data)
    {
        quick_require(PATH_EXTEND . 'word/PHPWord.php');
        $PHPWord = new \PHPWord();
        $PHPWord->getProperties();

        //设置默认字体
        $PHPWord-> setDefaultFontName('黑体');
        $PHPWord->setDefaultFontSize(12);
        // 标题风格,两个标题，一个分组名，一个api名
        $PHPWord->addTitleStyle(1, array(
            'size' => 22,
            'name' => '微软雅黑',
            'bold' => true
        ));
        $PHPWord->addTitleStyle(2, array(
            'size' => 16,
            'name' => '微软雅黑',
            'bold' => true
        ));

        // 设置文档属性
        $properties = $PHPWord->getProperties();
        $properties->setCreator('denglu1');
        $properties->setCompany('denglu1');
        $properties->setTitle('API文档');
        $properties->setDescription('coderChain基于swagger生成API文档');
        $properties->setKeywords('api,denglu1');

        // 表单字体属性
        $tableFontStyle = array('size'=>11 ,'bold'=>true);
        $styleCell = array('valign'=>'center', 'align'=>'center');
        // 表单格式初始化
        $styleTable = array('borderSize'=>6, 'borderColor'=>'000000', 'cellMargin'=>80);
        $PHPWord->addTableStyle('tableContent', $styleTable, array('size'=>11 ,'bold'=>true));

        // 添加页面
        $sectionStyle = array('orientation' => null,
            'marginLeft' => 900,
            'marginRight' => 900,
            'marginTop' => 900,
            'marginBottom' => 900
        );
        $section = $PHPWord->createSection($sectionStyle);
        // 目录添加
        $styleTOC = array('tabLeader'=>\PHPWord_Style_TOC::TABLEADER_DOT);
        $styleFont = array('spaceAfter'=>60, 'name'=>'Tahoma', 'size'=>12);
        $section->addTOC($styleFont, $styleTOC);
        $section->addPageBreak();
        //添加正文风格
        $fontStyle = array( '黑体'=>true,'size'=>12, 'bold'=>false);
        // 正文内容
        $PHPWord->addFontStyle('content', $fontStyle);
        $PHPWord->addFontStyle('tip', array(
            "italic"=>true,
            "size"=>8
        ));
        //加粗开头
        $PHPWord->addFontStyle('boldBegin', array('bold'=>true, '黑体'=>true, 'size'=>14));
        // 段落格式
        $PHPWord->addParagraphStyle('pStyle', array('spaceBefore'=>120));

        $request_type_list = array(
            0=>'POST'  ,
            1=>'GET' ,
            2 =>'PUT'  ,
            3=>'DELETE'  ,
            4=>'HEAD'  ,
            5=>'OPTIONS'  ,
            6=>'PATCH'
        );
        foreach ($data['apiList'] as $key=>$list)
        {
            $section->addTitle($key,1);
            $section->addTextBreak();
            $i = 1;
            foreach ($list as $item)
            {
                $section->addTitle($i."、".$item['apiName'],2);
                $section->addTextBreak();
                $section->addText('API路径：', 'boldBegin' );
                $section->addText("  {$item['apiURI']}", 'content','pStyle');
                $section->addTextBreak();
                if ($item['apiNote'])
                {
                    $section->addText('API描述：', 'boldBegin' );
                    $section->addText("  {$item['apiNote']}", 'content','pStyle');
                    $section->addTextBreak();
                }
                $section->addText('请求方式：', 'boldBegin' );
                $section->addText("  {$request_type_list[$item['apiRequestType']]}请求", 'content','pStyle');
                $section->addTextBreak();


                if (count($item['requestInfo'])>0 && $item['apiRequestParamType'] == 0)
                {
                    $section->addText('请求参数：', 'boldBegin' );

                    $this->createTable($section,$styleCell,$tableFontStyle,$item['requestInfo'],"formData",true);
                    $section->addTextBreak();
                }
                elseif (count($item['requestInfo'])>0 && $item['apiRequestParamType'] == 2)
                {
                    $section->addText('请求参数：', 'boldBegin' );
                    $this->createTable($section,$styleCell,$tableFontStyle,$item['requestInfo'],"body参数",true);
                    $section->addTextBreak();
                }
                if (count($item['urlParam'])>0)
                {
                    $section->addText('query参数：', 'boldBegin' );
                    $this->createTable($section,$styleCell,$tableFontStyle,$item['urlParam'],"query参数",true);
                    $section->addTextBreak();
                }
                if (count($item['restfulParam'])>0)
                {
                    $section->addText('restful参数：', 'boldBegin' );
                    $this->createTable($section,$styleCell,$tableFontStyle,$item['restfulParam'],"restful参数",true);
                    $section->addTextBreak();
                }

                if (count($item['resultInfo'])>0)
                {
                    $section->addText('请求响应：', 'boldBegin' );
                    $this->createTable($section,$styleCell,$tableFontStyle,$item['resultInfo'],"响应体",true);
                    $section->addTextBreak();
                }
                if (count($item['dataStructureList'])>0)
                {
                    $section->addText('数据结构列表：', 'boldBegin' );
                    $section->addText('接口中用到的数据结构类型：', 'tip' );

                    foreach ($item['dataStructureList'] as $structure)
                    {
                        $this->createStructureTable($section,$styleCell,$tableFontStyle,$structure,true);
                    }
                    $section->addTextBreak();
                }
                $i++;
                $section->addPageBreak();
            }
        }


        $section->addTextBreak();
        $section->addText('coderChain', array('underline'=>true,'黑体'=>true, 'size'=>11), array('align'=>'right', 'spaceBefore'=>200));
        $objWriter = \PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
        return $objWriter;
    }

    public function createStructureTable($section,$styleCell,$tableFontStyle,$data,$need_title,$parent_name=null)
    {
        $type_list = array(
            3=>"int",
            0=>"string",
            12=>"array",
            11=>"long",
            4=>"float",
            5=>"double",
            9=>"byte",
            1=>"file",
            6=>"date",
            7=>"dateTime",
            8=>"boolean",
            10=>"short",
            2=>"json",
            13=>"object",
            14=>"number"
        );
        if ($need_title && isset($data['structureName']))
        {
            $section->addTextBreak();
            $table = $section->addTable('tableContent');
            $table->addRow(900);
            $table->addCell(4000, $styleCell)->addText('结构名', $tableFontStyle, $styleCell);
            $table->addCell(2000, $styleCell)->addText('结构类型', $tableFontStyle, $styleCell);
            $table->addCell(4000, $styleCell)->addText('结构描述', $tableFontStyle, $styleCell);
            $table->addRow(900);
            $table->addCell(4000, $styleCell)->addText("{$data['structureName']}", 'content', $styleCell);
            $table->addCell(2000, $styleCell)->addText("{$type_list[$data['structureType']]}", 'content', $styleCell);
            $table->addCell(4000, $styleCell)->addText("{$data['structureDesc']}", 'content', $styleCell);
            $table->addRow(900);
            $table->addCell(2000, $styleCell)->addText('参数名', $tableFontStyle, $styleCell);
            $table->addCell(2000, $styleCell)->addText('参数类型', $tableFontStyle, $styleCell);
            $table->addCell(2000, $styleCell)->addText('是否必填', $tableFontStyle, $styleCell);
            $table->addCell(2000, $styleCell)->addText('参数描述', $tableFontStyle, $styleCell);

            $table->addCell(2000, $styleCell)->addText('备注', $tableFontStyle, $styleCell);
            $params = json_decode($data['structureData'],true);
        }
        else
        {
            $table = $section;
            $params = $data;
        }

        foreach ($params as $item)
        {
            $table->addRow();
            $isDataStructure = false;
            if ($parent_name)
            {
                $table->addCell(2000)->addText("{$parent_name}>>{$item['paramKey']}", 'content', $styleCell);
            }
            else
            {
                $table->addCell(2000)->addText($item['paramKey'], 'content', $styleCell);
            }

            if ($type_list[$item['paramType']])
            {
                $table->addCell(2000)->addText($type_list[$item['paramType']], 'content', $styleCell);
            }
            else
            {
                $table->addCell(2000)->addText($item['paramType'], 'content', $styleCell);
                $isDataStructure = true;
            }

            if ($item['paramNotNull'] == 0)
            {
                $table->addCell(2000)->addText("true", 'content', $styleCell);
            }
            else
            {
                $table->addCell(2000)->addText("false", 'content', $styleCell);
            }

            $table->addCell(2000)->addText($item['paramName'], 'content', $styleCell);

            if ($parent_name)
            {
                if ($isDataStructure)
                {
                    $table->addCell(2000)->addText("参数{$parent_name}的子参数，具体参数请相应的数据结构列表", 'content', $styleCell);
                }
                else
                {
                    $table->addCell(2000)->addText("参数{$parent_name}的子参数", 'content', $styleCell);
                }

            }
            else
            {
                if ($isDataStructure)
                {
                    $table->addCell(2000)->addText("数据结构类型，具体参数请参考相应的数据结构列表", 'content', $styleCell);
                }
                else
                {
                    $table->addCell(2000)->addText("", 'content', $styleCell);
                }

            }
            if ($item['childList'])
            {
                if ($parent_name)
                {
                    $name = $parent_name .">>" .$item['paramKey'];

                }
                else
                {
                    $name = $item['paramKey'];

                }
                $this->createStructureTable($table,$styleCell,$tableFontStyle,$item['childList'],false, $name);
            }
        }
    }


    public function createTable($section,$styleCell,$tableFontStyle,$data,$type,$need_title = false,$parent_name=null)
    {
        $type_list = array(
            3=>"int",
            0=>"string",
            12=>"array",
            11=>"long",
            4=>"float",
            5=>"double",
            9=>"byte",
            1=>"file",
            6=>"date",
            7=>"dateTime",
            8=>"boolean",
            10=>"short",
            2=>"json",
            13=>"object",
            14=>"number"
        );
        if ($need_title)
        {
            $section->addTextBreak();
            $table = $section->addTable('tableContent');
            $table->addRow(900);
            $table->addCell(2000, $styleCell)->addText('参数名', $tableFontStyle, $styleCell);
            $table->addCell(2000, $styleCell)->addText('参数类型', $tableFontStyle, $styleCell);
            $table->addCell(2000, $styleCell)->addText('是否必填', $tableFontStyle, $styleCell);
            $table->addCell(2000, $styleCell)->addText('参数描述', $tableFontStyle, $styleCell);
            $table->addCell(2000, $styleCell)->addText('请求类型', $tableFontStyle, $styleCell);
            $table->addCell(2000, $styleCell)->addText('备注', $tableFontStyle, $styleCell);
        }
        else
        {
            $table = $section;
        }
        foreach ($data as $item)
        {
            $table->addRow();
            $isDataStructure = false;
            if ($parent_name)
            {
                $table->addCell(2000)->addText("{$parent_name}>>{$item['paramKey']}", 'content', $styleCell);
            }
            else
            {
                $table->addCell(2000)->addText($item['paramKey'], 'content', $styleCell);
            }

            if ($type_list[$item['paramType']])
            {
                $table->addCell(2000)->addText($type_list[$item['paramType']], 'content', $styleCell);
            }
            else
            {
                $table->addCell(2000)->addText($item['paramType'], 'content', $styleCell);
                $isDataStructure = true;
            }

            if ($item['paramNotNull'] == 0)
            {
                $table->addCell(2000)->addText("true", 'content', $styleCell);
            }
            else
            {
                $table->addCell(2000)->addText("false", 'content', $styleCell);
            }

            $table->addCell(2000)->addText($item['paramName'], 'content', $styleCell);
            $table->addCell(2000)->addText("{$type}", 'content', $styleCell);
            if ($parent_name)
            {
                if ($isDataStructure)
                {
                    $table->addCell(2000)->addText("参数{$parent_name}的子参数，具体参数请参考数据结构列表", 'content', $styleCell);
                }
                else
                {
                    $table->addCell(2000)->addText("参数{$parent_name}的子参数", 'content', $styleCell);
                }

            }
            else
            {
                if ($isDataStructure)
                {
                    $table->addCell(2000)->addText("数据结构类型，具体参数请参考数据结构列表", 'content', $styleCell);
                }
                else
                {
                    $table->addCell(2000)->addText("", 'content', $styleCell);
                }

            }
            if ($item['childList'])
            {
                if ($parent_name)
                {
                    $name = $parent_name .">>" .$item['paramKey'];

                }
                else
                {
                    $name = $item['paramKey'];

                }
                $this->createTable($table,$styleCell,$tableFontStyle,$item['childList'],$type,false, $name);
            }
        }
    }


    public function getPdf(&$data)
    {

        $request_type_list = array(
            0=>'POST'  ,
            1=>'GET' ,
            2 =>'PUT'  ,
            3=>'DELETE'  ,
            4=>'HEAD'  ,
            5=>'OPTIONS'  ,
            6=>'PATCH'
        );
        quick_require(PATH_EXTEND . 'pdf/tcpdf.php');
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, TRUE, 'UTF-8', TRUE);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetTitle('基于码农链的api文档');
        $pdf->SetSubject('基于码农链的api pdf文档');
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH);
        $pdf->setFooterData(array(
            0,
            64,
            0
        ), array(
            0,
            64,
            128
        ));
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->setFontSubsetting(true);
        // 设置内容与边缘的间距
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        foreach ($data['apiList'] as $key=>$list)
        {
            $i = 1;
            $pdf->AddPage();
            $pdf->SetFont('msyh', 'B', 20);
            $pdf->Write(15, $key,'', 0, 'L', true, 0, false, false, 0);
            $pdf->Ln(7);
            foreach ($list as $item)
            {
                $pdf->SetFont('msyh', 'B', 16, '', true);
                $pdf->Write(15, $i."、".$item['apiName']);
                $pdf->Ln(10);
                $pdf->SetFont('msyh', 'B', 14);
                $pdf->Write(15, 'API路径：' );
                $pdf->Ln(7);
                $pdf->Write(15,"  {$item['apiURI']}");
                $pdf->Ln(15);
                if ($item['apiNote'])
                {

                    $pdf->Write(15,'API描述：');
                    $pdf->Ln(7);

                    $pdf->Write(15,"  {$item['apiNote']}");
                    $pdf->Ln(15);
                }

                $pdf->Write(15,'请求方式：');
                $pdf->Ln(7);

                $pdf->Write(15,"  {$request_type_list[$item['apiRequestType']]}请求");
                $pdf->Ln(15);

                if (count($item['requestInfo'])>0 && $item['apiRequestParamType'] == 0)
                {

                    $pdf->Write(15,'请求参数：' );
                    $pdf->Ln(7);
                    $html = "";
                    $this->createTableForPdf($pdf,$item['requestInfo'],"formData",$html,true);
                    $pdf->Ln(15);
                }
                elseif (count($item['requestInfo'])>0 && $item['apiRequestParamType'] == 2)
                {

                    $pdf->Write(15,'请求参数：');
                    $pdf->Ln(7);
                    $html = "";
                    $this->createTableForPdf($pdf,$item['requestInfo'],"body参数",$html,true);
                    $pdf->Ln(15);
                }
                if (count($item['urlParam'])>0)
                {

                    $pdf->Write(15,'query参数：');
                    $pdf->Ln(7);
                    $html = "";
                    $this->createTableForPdf($pdf,$item['urlParam'],"query参数",$html,true);
                    $pdf->Ln(15);
                }
                if (count($item['restfulParam'])>0)
                {

                    $pdf->Write(15,'restful参数：');
                    $pdf->Ln(7);
                    $html = "";
                    $this->createTableForPdf($pdf,$item['restfulParam'],"restful参数",$html,true);
                    $pdf->Ln(15);
                }

                if (count($item['resultInfo'])>0)
                {

                    $pdf->Write(15,'请求响应：' );
                    $pdf->Ln(7);
                    $html = "";
                    $this->createTableForPdf($pdf,$item['resultInfo'],"响应体",$html,true);
                    $pdf->Ln(15);
                }
                if (count($item['dataStructureList'])>0)
                {

                    $pdf->Write(15,'数据结构列表：');
                    $pdf->Ln(10);
                    $pdf->SetFont('msyh', '', 8);
                    $pdf->Write(8,'接口中用到的数据结构类型：');
                    $pdf->Ln(5);
                    foreach ($item['dataStructureList'] as $structure)
                    {
                        $html = "";
                        $this->createStructureTableForPdf($pdf, $structure,$html,true);
                    }
                    $pdf->Ln(15);
                }
                if ($i != count($list))
                {
                    $pdf->AddPage();
                }
                $i++;
            }
        }

        $pdf->AddPage();
        $pdf->SetFont('msyh', 'B', 15);
        $pdf->Write(0, '感谢您的关注，更多信息请访问：', '',0, 'R', true, 0, false, false, 0);
        $pdf->Ln(7);
        $pdf->Write(0, '码农链官网 https://coderchain.cn/#/', 'https://coderchain.cn/#/', 0, 'R', true, 0, false, false, 0);
        return $pdf;
    }

    public function createTableForPdf(&$pdf,&$data,$type,&$html,$need_title = false,$parent_name=null)
    {
        $type_list = array(
            3=>"int",
            0=>"string",
            12=>"array",
            11=>"long",
            4=>"float",
            5=>"double",
            9=>"byte",
            1=>"file",
            6=>"date",
            7=>"dateTime",
            8=>"boolean",
            10=>"short",
            2=>"json",
            13=>"object",
            14=>"number"
        );
        if ($need_title)
        {
            $pdf->Ln(7);
            $html .= '<table border="1" cellspacing="0" cellpadding="3">
					<tr>
						<th align="left">参数名</th>
						<th align="left">参数类型</th>
						<th align="left">是否必填</th>
						<th align="left">参数描述</th>
						<th align="left">请求类型</th>
						<th align="left">备注</th>
					</tr>';
        }

        foreach ($data as $item)
        {
            $html.='<tr>';
            $isDataStructure = false;
            if ($parent_name)
            {
                $html.='<td align="left">'."{$parent_name}>>{$item['paramKey']}".'</td>';
            }
            else
            {
                $html.='<td align="left">'.$item['paramKey'].'</td>';
            }

            if ($type_list[$item['paramType']])
            {
                $html.='<td align="left">'.$type_list[$item['paramType']].'</td>';
            }
            else
            {
                $html.='<td align="left">'.$item['paramType'].'</td>';
                $isDataStructure = true;
            }

            if ($item['paramNotNull'] == 0)
            {
                $html.='<td align="left">true</td>';
            }
            else
            {
                $html.='<td align="left">false</td>';
            }
            $html.='<td align="left">'.$item['paramName'].'</td>';
            $html.='<td align="left">'.$type.'</td>';

            if ($parent_name)
            {
                if ($isDataStructure)
                {
                    $html.='<td align="left">'."参数{$parent_name}的子参数，具体参数请参考数据结构列表".'</td>';
                }
                else
                {
                    $html.='<td align="left">'."参数{$parent_name}的子参数".'</td>';
                }

            }
            else
            {
                if ($isDataStructure)
                {
                    $html.='<td align="left">数据结构类型，具体参数请参考数据结构列表</td>';
                }
                else
                {
                    $html.='<td align="left"></td>';
                }
            }
            $html.='</tr>';
            if ($item['childList'])
            {
                if ($parent_name)
                {
                    $name = $parent_name .">>" .$item['paramKey'];
                }
                else
                {
                    $name = $item['paramKey'];
                }
                $this->createTableForPdf($pdf,$item['childList'],$type,$html,false, $name);
            }
        }
        if ($need_title)
        {
            $html .= '</table>';
            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        }
    }

    public function createStructureTableForPdf(&$pdf,&$data,&$html,$need_title,$parent_name=null)
    {
        $type_list = array(
            3=>"int",
            0=>"string",
            12=>"array",
            11=>"long",
            4=>"float",
            5=>"double",
            9=>"byte",
            1=>"file",
            6=>"date",
            7=>"dateTime",
            8=>"boolean",
            10=>"short",
            2=>"json",
            13=>"object",
            14=>"number"
        );
        if ($need_title && isset($data['structureName']))
        {
            $pdf->Ln(7);
            $html .= '<table border="1" cellspacing="0" cellpadding="3">
					<tr>
						<th align="left" colspan="2">结构名</th>
						<th align="left" colspan="2">结构类型</th>
						<th align="left" colspan="1">结构描述</th>
					</tr>
					<tr>
					<td align="left" colspan="2">'.$data['structureName'].'</td>
					<td align="left" colspan="2">'.$type_list[$data['structureType']].'</td>
					<td align="left" colspan="1">'.$data['structureDesc'].'</td>
					</tr>
					<tr>
						<th align="left">参数名</th>
						<th align="left">参数类型</th>
						<th align="left">是否必填</th>
						<th align="left">参数描述</th>
						<th align="left">备注</th>
					</tr>
					';
            $params = json_decode($data['structureData'],true);
        }
        else
        {
            $params = $data;
        }

        foreach ($params as $item)
        {
            $html.='<tr>';
            $isDataStructure = false;
            if ($parent_name)
            {
                $html.='<td align="left">'."{$parent_name}>>{$item['paramKey']}".'</td>';

            }
            else
            {
                $html.='<td align="left">'."{$item['paramKey']}".'</td>';

            }

            if ($type_list[$item['paramType']])
            {
                $html.='<td align="left">'."{$type_list[$item['paramType']]}".'</td>';
            }
            else
            {
                $html.='<td align="left">'."{$item['paramType']}".'</td>';

                $isDataStructure = true;
            }

            if ($item['paramNotNull'] == 0)
            {
                $html.='<td align="left">true</td>';

            }
            else
            {
                $html.='<td align="left">false</td>';

            }
            $html.='<td align="left">'."{$item['paramName']}".'</td>';


            if ($parent_name)
            {
                if ($isDataStructure)
                {
                    $html.='<td align="left">'."参数{$parent_name}的子参数，具体参数请相应的数据结构列表".'</td>';

                }
                else
                {
                    $html.='<td align="left">'."参数{$parent_name}的子参数".'</td>';

                }

            }
            else
            {
                if ($isDataStructure)
                {
                    $html.='<td align="left">数据结构类型，具体参数请参考相应的数据结构列表</td>';

                }
                else
                {
                    $html.='<td align="left"></td>';
                }

            }
            $html.='</tr>';
            if ($item['childList'])
            {

                if ($parent_name)
                {
                    $name = $parent_name .">>" .$item['paramKey'];

                }
                else
                {
                    $name = $item['paramKey'];

                }
                $this->createStructureTableForPdf($pdf,$item['childList'],$html, false, $name);
            }

        }
        if ($need_title)
        {
            $html .= '</table>';
            $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        }
    }

    public function getJson($data)
    {

    }
}