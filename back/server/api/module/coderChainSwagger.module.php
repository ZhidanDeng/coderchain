<?php
/**
 * Created by PhpStorm.
 * @author: Wuk0
 * @Date: 2020/3/24
 * @Time: 16:59
 */

namespace api\module;


class coderChainSwagger
{
    /**
     * 获取数据结构列表
     * @param string $base_url 代码仓库的基本路径
     * @param string $module_url 需要扫描的数据结构目录路径
     * @param string $param 请求参数
     * @param int $repository_type 仓库类型
     * @return array
     */
    public function getDataStructureList($content_list)
    {
        $api_structure_list = array();
        $model_name_content = array();
        $file_module = new \file\module\File();
        foreach($content_list as $key => $model_content)
        {
            $model_content = $file_module->getFileContent($key);
            // 除去import部分
            $model_content = substr($model_content, strripos($model_content, "import") +1);
            if(is_numeric(strpos($model_content, "@ApiModelProperty")))
            {
                // 看下是否有@ApiModelProperty的属性，没有的话就不处理，暂时仅处理含有@ApiModelProperty注解的实体类属性
                $con = array();
                $title_content = substr($model_content, 0, strpos($model_content, "{"));
                preg_match("/public\s{1,}class\s(\\S*)/", $title_content, $con);
                $class_name = strtolower($con[1]);
                // 去掉<>
                if(is_numeric(strpos($class_name, "<")))
                {
                    $class_name = substr($class_name, 0, strpos($class_name, "<"));
                }
                // 获取实体类名->实体类内容的数组
                $model_name_content[$class_name] = $model_content;
            }
            else
            {
                continue;
            }
        }
        foreach($model_name_content as $key => $content)
        {
            $this->getDataStructure($key, $model_name_content, $api_structure_list);
            unset($content);
        }
        return $api_structure_list;
    }

    /**
     * 通过数据结构类名获取数据结构
     * @param string $class_name 要找的数据结构的类名
     * @param array $model_name_content 数据结构列表内容
     * @param array $api_structure_list 数据结构列表
     * @return array|bool
     */
    public function getDataStructureByName($class_name, $model_name_content, &$api_structure_list)
    {
        if(isset($api_structure_list[$class_name]))
        {
            // 如果数据结构列表已经有了，直接返回
            return $api_structure_list[$class_name];
        }
        elseif(isset($model_name_content[$class_name]))
        {
            // 从实体类内容列表查询
            $structure_result = $this->getDataStructure($class_name, $model_name_content, $api_structure_list);
            if($structure_result)
            {
                return $structure_result;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取数据结构内容
     * @param string $class_name 数据结构类名
     * @param array $model_name_content 数据结构内容列表
     * @param array $api_structure_list 数据结构列表
     * @return array|bool
     */
    public function getDataStructure($class_name, $model_name_content, &$api_structure_list)
    {
        $java_structure = array(
            "Iterator",
            "iterator",
            "Collection",
            "collection",
            "Map",
            "map",
            "AbstractMap",
            "abstractmap",
            "LinkIterator",
            "linkiterator",
            "List",
            "list",
            "Set",
            "set",
            "Queue",
            "queue",
            "SortedMap",
            "sortedmap",
            "AbstractListCollection",
            "abstractlistcollection",
            "SortedSet",
            "sortedset",
            "TreeMap",
            "treemap",
            "HashMap",
            "hashmap",
            "AbstractList",
            "abstractlist",
            "AbstractSet",
            "abstractset",
            "IdentityHashMap",
            "identityhashmap",
            "LinkedHashMap",
            "linkedhashmap",
            "HashSet",
            "hashset",
            "TreeSet",
            "treeset",
            "LinkedHashSet",
            "linkedhashset",
            "WeakHashMap",
            "weakhashmap",
            "HashTable",
            "hashtable",
            "Vector",
            "vector",
            "Stack",
            "stack",
            "ArrayList",
            "arraylist",
            "AbstractSequentialList",
            "abstractsequentiallist",
            "LinkedList",
            "linkedlist",
            "Comparable",
            "comparable",
            "Comparator",
            "comparator",
            "Collections",
            "collections",
            "Arrays",
            "arrays",
            "Long",
            "long",
            "String",
            "string",
            "Integer",
            "integer",
            "int",
            "float",
            "Float",
            "double",
            "Double",
            "Byte",
            "byte",
            "Short",
            "short",
            "boolean",
            "Boolean"
        );
        if(isset($api_structure_list[$class_name]))
        {
            return $api_structure_list[$class_name];
        }
        elseif(isset($model_name_content[$class_name]))
        {
            // 实体类内容数组，包括描述和参数列表
            $model_array = array();
            $model_array["structureName"] = $class_name;

            // 获取父类的参数
            $content_array = explode("class", $model_name_content[$class_name], 2);
            // 获取类名,如"class UserController{"的形式,并进行处理
            $class_message = substr($content_array[1], 0, strpos($content_array[1], "{"));
            // 父类名
            $class_extend = "";
            // 父类参数
            $extend_structure_param = array();
            $extend_generic_type = "";
            $extend_generic = array();
            if(is_numeric(strpos($class_message, "extends")))
            {
                // 截取"class UserController extends FatherClass{"
                $class_extend = explode("extends", $class_message, 2)[1];
                $class_extend = strtolower(str_replace(" ", "", $class_extend));
                if(preg_match("/<(.*?)>/", $class_extend, $extend_generic) >0)
                {
                    $extend_generic_type = strtolower($extend_generic[1]);
                    $extend_generic_type = str_replace(" ", "", $extend_generic_type);
                    $class_extend = explode("<", $class_extend, 2)[0];
                }
            }


            $generic_type = "";
            // 泛型类型的类
            $generic = array();
            if(preg_match("/<(.*?)>/", $class_message, $generic) >0)
            {
                $generic_type = strtolower($generic[1]);
                $generic_type = str_replace(" ", "", $generic_type);
            }
            if($class_extend)
            {
                $extend_structure = $this->getDataStructureByName($class_extend, $model_name_content, $api_structure_list);
                $extend_structure_param = json_decode($extend_structure['structureData'], true);
                // 继承类的泛型
                if ($extend_generic_type == $generic_type && isset($extend_structure['structureGenericType']))
                {
                    $model_array["structureExtendGenericType"] = $extend_structure['structureGenericType'];
                }
            }



            // 判断是否有@ApiModel可以获取实体类的描述
            if(is_numeric(strpos($model_name_content[$class_name], "@ApiModel")))
            {
                // 先获取description，没有的话再获取value，都没有的话获取类名作为描述
                $param_array = array(
                    "description",
                    "value"
                );
                $res_model = $this->regexAnnotations($model_name_content[$class_name], "ApiModel", $param_array);
                if(count($res_model) >0)
                {
                    // 仅取一个@ApiModel防止用户写了多个@ApiModel
                    $res_model = $res_model[count($res_model) -1];
                    if(isset($res_model["description"]))
                    {
                        $model_array["structureDesc"] = $res_model["description"];
                    }
                    elseif(isset($res_model["value"]))
                    {
                        $model_array["structureDesc"] = $res_model["value"];
                    }
                    else
                    {
                        $model_array["structureDesc"] = $class_name;
                    }
                }
                else if(preg_match("/@ApiModel\(\"([\\s\\S]*?)\"\)/", $model_name_content[$class_name], $des) >0)
                {
                    // 获取@ApiModel("xxx")的xxx作为描述
                    $model_array["structureDesc"] =  $des[1];
                }
                else
                {

                    $model_array["structureDesc"] = $class_name;
                }
            }
            else
            {
                $model_array["structureDesc"] = $class_name;
            }
            $model_properties = array();
            $con = array();
            preg_match_all("/(@ApiModelProperty[\\s\\S]*?);/", $model_name_content[$class_name], $con);
            $param_array = array(
                "hidden",
                "value",
                "required",
                "example"
            );
            $i = 0;
            foreach($con[1] as $c)
            {
                $param = array();
                $property_content = array();
                preg_match("/@ApiModelProperty\\s{0,}\\([\\s\\S]*?\\)/", $c, $property_content);
                $param_base_message = $this->regexAnnotations($property_content[0], "ApiModelProperty", $param_array);
                if($param_base_message)
                {
                    $param_base_message = $param_base_message[0];
                    if (isset($param_base_message['hidden']) && $param_base_message['hidden'] == 'true')
                    {
                        // 隐藏参数，不处理
                        continue;
                    }
                    if(isset($param_base_message["value"]))
                    {
                        $param['paramName'] = $param_base_message["value"];
                    }
                    if(isset($param_base_message["required"]) &&$param_base_message["required"] =="true")
                    {
                        $param['paramNotNull'] = 0;
                    }
                    else
                    {
                        $param['paramNotNull'] = 1;
                    }
                    if(isset($param_base_message["example"]))
                    {
                        $param['paramExample'] = $param_base_message["example"];
                    }
                }
                else if(preg_match("/@ApiModelProperty\(\"([\\s\\S]*?)\"\)/", $property_content[0], $des) >0)
                {
                    // 获取@ApiModelProperty("xxx")的xxx作为描述
                    $param['paramName'] = $des[1];
                }
                $param_statement = array();
                preg_match("/@ApiModelProperty\\([\\s\\S]*?\\)([\\s\\S]*)/", $c, $param_statement);
                if(is_numeric(strpos($param_statement[1], "=")))
                {
                    $param_statement[1] = substr($param_statement[1], 0, strpos($param_statement[1], "="));
                }
                $param_information = array();
                if(preg_match("/(public|private|protected)\\s{1,}([\\s\\S]*?)\\s{1,}(\\S*)/", $param_statement[1], $param_information) >0)
                {
                    $param["paramKey"] = $param_information[3];
                    $type = $param_information[2];
                    $type = str_replace(" ", "", $type);
                    $structure_generic = "";
                    $special_generic = "";
                    if(is_numeric(strpos($type, "<")))
                    {
                        $content = array();
                        preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>$/", $type, $content);
                        // 接口返回参数
                        $content[1] = str_replace(" ", "", $content[1]);
                        $content[2] = str_replace(" ", "", strtolower($content[2]));
                        if(! in_array($content[1], $java_structure))
                        {
                            $type = $content[1];
                            if ($generic_type)
                            {
                                if ($content[2] == $generic_type)
                                {
                                    // 看看是否是 数据结构<T>，是的话转为 数据结构<eo-swagger>
                                    $special_generic = "eo-swagger";
                                }
                                elseif (strpos($content[2], "<$generic_type>") !== FALSE)
                                {
                                    // 看看是否是 数据结构<xxx<T>>，是的话转为 数据结构<xxx<eo-swagger>>
                                    $content[2] = str_replace("<$generic_type>" , "<eo-swagger>", $content[2] );
                                    $special_generic = $content[2];
                                }
                                else
                                {
                                    $structure_generic = $content[2];
                                }
                            }
                            else
                            {
                                $structure_generic = $content[2];
                            }
                        }
                        elseif ($content[1] == "List" || $content[1] == 'ArrayList' || $content[1] == "list" || $content[1] == 'arraylist'|| $content[1] == 'Collection' || $content[1] == 'collection')
                        {
                            // 看是不是List<数据结构>类型
                            $type = $content[1];
                            if ($generic_type)
                            {
                                // 看看是否是List<T>
                                if ($content[2] == $generic_type)
                                {
                                    // 看看是否是 List<T>，是的话转为 List<eo-swagger>
                                    $special_generic = "eo-swagger";
                                }
                                elseif (strpos($content[2], "<$generic_type>") !== FALSE)
                                {
                                    // 看看是否是 List<xxx<T>>，是的话转为 List<xxx<eo-swagger>>
                                    $content[2] = str_replace("<$generic_type>" , "<eo-swagger>", $content[2] );
                                    $special_generic = $content[2];
                                }
                                else
                                {
                                    $structure_generic = $content[2];
                                }
                            }
                            else
                            {
                                $structure_generic = $content[2];
                            }
                        }
                        else
                        {
                            $content[2] = str_replace(" ", "", $content[2]);
                            $type = $content[2];
                            if (! in_array($type, $java_structure))
                            {
                                while (is_numeric(strpos($type, "<")))
                                {
                                    $content = array();
                                    preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $type, $content);
                                    if(is_numeric(strpos($type, ',')))
                                    {
                                        $content_arr = explode(',', $content[1]);
                                        if (isset($content_arr[1]))
                                        {
                                            $content[1] = $content_arr[1];
                                        }
                                        else
                                        {
                                            $content[1] = $content_arr[0];
                                        }
                                    }
                                    $content[1] = str_replace(" ", "", $content[1]);
                                    if(! in_array($content[1], $java_structure))
                                    {
                                        $type = $content[1];
                                        $structure_generic = strtolower($content[2]);
                                    }
                                    else
                                    {
                                        $type = $content[2];
                                    }
                                }
                                if(is_numeric(strpos($type, ',')))
                                {
                                    $type = explode(',', $type);
                                    $type = $type[count($type) -1];
                                }
                            }
                        }
                    }
                    $type = strtolower($type);
                    switch($type)
                    {
                        case "integer":
                            $param['paramType'] = '3';
                            break;
                        case "bigdecimal":
                            $param['paramType'] = '3';
                            break;
                        case "int":
                            $param['paramType'] = '3';
                            break;
                        case "int[]":
                            $param['paramType'] = '12';
                            break;
                        case "string":
                            $param['paramType'] = '0';
                            break;
                        case "string[]":
                            $param['paramType'] = '12';
                            break;
                        case 'long':
                            $param['paramType'] = '11';
                            break;
                        case 'float':
                            $param['paramType'] = '4';
                            break;
                        case 'double':
                            $param['paramType'] = '5';
                            break;
                        case 'byte':
                            $param['paramType'] = '9';
                            break;
                        case 'file':
                            $param['paramType'] = '1';
                            break;
                        case 'date':
                            $param['paramType'] = '6';
                            break;
                        case 'dateTime':
                            $param['paramType'] = '7';
                            break;
                        case 'timestamp' :
                            $param ['paramType'] = '7';
                            break;
                        case 'boolean':
                            $param['paramType'] = '8';
                            break;
                        case 'bool':
                            $param['paramType'] = '8';
                            break;
                        case 'array':
                            $param['paramType'] = '12';
                            break;
                        case 'short':
                            $param['paramType'] = '10';
                            break;
                        case 'json':
                            $param['paramType'] = '2';
                            break;
                        case 'object':
                            $param['paramType'] = '13';
                            break;
                        case 'number':
                            $param['paramType'] = '14';
                            break;
                        default:
                            $param['paramType'] = $type;
                    }
                    if(! is_numeric($param["paramType"]))
                    {
                        if ($special_generic)
                        {
                            // 数据结构<T> 或 list<T>等类型
                            $param['paramType'] = $special_generic;
                            $param['parentParamType'] = $type;
                            $model_array["structureGenericType"] = $param;
                            continue;
                        }
                        elseif($structure_generic)
                        {
                            if ($type == "list" || $type == "arraylist" || $type == "List" || $type == "ArrayList")
                            {
                                $param['paramType'] = '12';
                            }
                            else
                            {
                                $param['paramType'] = '13';
                            }
                            $param['childList'] = $this->getGenericStructure($type, $structure_generic, $api_structure_list, $model_name_content);
                        }
                        else
                        {
                            // 类型是特殊的数据结构
                            $param['paramType'] = '13';
                            $type_structure = $this->getDataStructureByName($type, $model_name_content, $api_structure_list);
                            if($type_structure)
                            {
                                $child_structure_params = json_decode($type_structure["structureData"], true);
                                $param['childList'] = $child_structure_params;
                                $param['paramType'] = '13';
                            }
                            else if($generic_type ==$type)
                            {
                                // 判断是否为泛型
                                $param['paramType'] = $type;
                                $model_array["structureGenericType"] = $param;
                                continue;
                            }
                        }
                    }
                    $model_properties[$i] = $param;
                    $i ++;
                }
                else
                {
                    // 仅有@ApiModelProperty却无具体的变量声明
                    reset($param);
                    continue;
                }
            }
            // 合并父类参数
            if($extend_structure_param)
            {
                $model_properties = array_merge($extend_structure_param, $model_properties);
            }
            $model_array["structureData"] = json_encode($model_properties);
            $model_array["structureType"] = "13";
            $api_structure_list[$class_name] = $model_array;
            return $model_array;
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取接口数据
     * @param string $api_structure_list 数据结构列表
     * @param string $base_url 基础路径
     * @param string $api_dir_path 控制器类文件目录
     * @param string $param 请求参数
     * @param int $repository_type 仓库类型
     * @return array
     */
    public function getApiData($content_list)
    {
        $api_structure_list = $this->getDataStructureList($content_list);
        $request_type_list = array(
            'POST' => 0,
            'GET' => 1,
            'PUT' => 2,
            'DELETE' => 3,
            'HEAD' => 4,
            'OPTIONS' => 5,
            'PATCH' => 6
        );
        $java_structure = array(
            "Iterator",
            "iterator",
            "Collection",
            "collection",
            "Map",
            "map",
            "AbstractMap",
            "abstractmap",
            "LinkIterator",
            "linkiterator",
            "List",
            "list",
            "Set",
            "set",
            "Queue",
            "queue",
            "SortedMap",
            "sortedmap",
            "AbstractListCollection",
            "abstractlistcollection",
            "SortedSet",
            "sortedset",
            "TreeMap",
            "treemap",
            "HashMap",
            "hashmap",
            "AbstractList",
            "abstractlist",
            "AbstractSet",
            "abstractset",
            "IdentityHashMap",
            "identityhashmap",
            "LinkedHashMap",
            "linkedhashmap",
            "HashSet",
            "hashset",
            "TreeSet",
            "treeset",
            "LinkedHashSet",
            "linkedhashset",
            "WeakHashMap",
            "weakhashmap",
            "HashTable",
            "hashtable",
            "Vector",
            "vector",
            "Stack",
            "stack",
            "ArrayList",
            "arraylist",
            "AbstractSequentialList",
            "abstractsequentiallist",
            "LinkedList",
            "linkedlist",
            "Comparable",
            "comparable",
            "Comparator",
            "comparator",
            "Collections",
            "collections",
            "Arrays",
            "arrays",
            "Long",
            "long",
            "String",
            "string",
            "Integer",
            "integer",
            "int",
            "float",
            "Float",
            "double",
            "Double",
            "Byte",
            "byte",
            "Short",
            "short",
            "boolean",
            "Boolean"
        );
        $data = array(
            'apiList' => array(),
            "swaggerIDList" => array(),
            "groupList" => array(
                "默认分组"
            )
        );

        $file_module = new \file\module\File();
        foreach($content_list as $key=>$content)
        {
            $content = $file_module->getFileContent($key);

            // 判断是否为控制器类
            if(is_numeric(strpos($content, "@RestController")) ||is_numeric(strpos($content, "@Controller")))
            {
                // 除去import部分
                if(is_numeric(strpos($content, "@RestController")))
                {
                    $content = substr($content, strpos($content, "@RestController") -1);
                }
                elseif(is_numeric(strpos($content, "@Controller")))
                {
                    $content = substr($content, strpos($content, "@Controller") -1);
                }
                $base_params = array();
                // 类描述
                $content_array = explode("class", $content, 2);
                // 获取类名,如"class UserController{"的形式,并进行处理
                $class_name = substr($content_array[1], 0, strpos($content_array[1], "{"));
                if(is_numeric(strpos($class_name, "extends")))
                {
                    // 截取"class UserController extends FatherClass{"
                    $class_name = explode("extends", $class_name, 2)[0];
                }
                if(is_numeric(strpos($class_name, "implements")))
                {
                    // 截取"class UserController extends FatherClass{"
                    $class_name = explode("implements", $class_name, 2)[0];
                }
                $class_name = str_replace(" ", "", $class_name);
                // 类基本请求方式获取
                $base_method = $this->getMethod($content_array[0]);
                // 类基本请求路径获取
                $base_path = $this->getPath($content_array[0]);
                // 类基本Produces获取
                $base_produces = $this->getProduces($content_array[0]);
                // 类基本Consumes获取
                $base_consumes = $this->getConsumes($content_array[0]);
                // 类基本参数获取$base_params
                $params = $this->getParam($content_array[0]);

                if(isset($params))
                {
                    if(is_array($params))
                    {
                        foreach($params as $param)
                        {
                            $param = str_replace(" ", "", $param);
                            $base_params[$param]["paramKey"] = $param;
                            $base_params[$param]["in"] = "query";
                            $base_params[$param]["paramNotNull"] = 0;
                            $base_params[$param]["paramType"] = "0";
                        }
                    }
                    else
                    {
                        $params = str_replace(" ", "", $params);
                        $base_params[$params]["paramKey"] = $params;
                        $base_params[$params]["in"] = "query";
                        $base_params[$params]["paramNotNull"] = 0;
                        $base_params[$params]["paramType"] = "0";
                    }
                }
                else
                {
                    $base_params = array();
                }
                // @Api内容获取，value作用未知，tags接口分组(默认为类名)，description为分组描述，basePath未知（基础路径是mapping的value）
                $param_array = array(
                    "description",
                    "tags"
                );
                $res = $this->regexAnnotations($content_array[0], "Api", $param_array);
                // @Api仅取一个
                $tags = array();
                if($res)
                {
                    $res = $res[count($res) -1];
                    if(isset($res["tags"]))
                    {
                        if(is_array($res["tags"]))
                        {
                            $i = 0;
                            foreach($res["tags"] as $tag)
                            {
                                preg_match_all("/[\"|\']([\\s\\S]*?)[\"|\']/", $tag, $content);
                                $tags[$i] = $content[1][0];
                                $i ++;
                            }
                        }
                        else
                        {
                            $tags[0] = $res["tags"];
                        }
                    }
                    else
                    {
                        $tags[0] = $class_name;
                    }
                }
                else
                {
                    $tags[0] = $class_name;
                }
                $base_tags = $tags;

                // 接口描述数组部分
                $operation_str = explode("return", $content_array[1]);
                foreach($operation_str as $operation)
                {
                    // 接口数据结构
                    $api_data_structure = array();
                    $param_content = array();
                    // 要有Mapping才能成立接口
                    if(preg_match_all("/[Request|Get|Post|Patch|Put|Delete|Head|Option]+Mapping/", $operation, $param_content) >0)
                    {
                        // 接口tags获取
                        $operation_tags = array();
                        // 接口基础路径mapping部分
                        $operation_path = $this->getPath($operation);
                        // 接口为public类型
                        $content = array();
                        // 泛型
                        $operation_generic = "";
                        if (strpos($operation, "throws") !== FALSE)
                        {
                            $operation = preg_replace("/throws\\s{1,}.*?Exception/", "", $operation);

                        }
                        preg_match("/public\s{1,}([\\s\\S]*?)\s{0,}\\(([\\s\\S]*?)\\)\s{0,}\\{/", $operation, $content);
                        // 接口括号中的参数获取
                        $brackets_param_string = $content[2];
                        if(is_numeric(strpos($content[1], "<")))
                        {
                            preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>\\s{1,}([\\s\\S]*)/", $content[1], $content);
                            // 接口方法名获取
                            $operation_name = $content[3];
                            // 接口返回参数
                            $content[1] = str_replace(" ", "", $content[1]);
                            if(! in_array($content[1], $java_structure))
                            {
                                // 确认是泛型形如：数据结构<...>
                                $operation_response_type = $content[1];
                                $operation_generic = strtolower($content[2]);
                            }
                            else
                            {
                                // map,List<数据结构>，map<key,<key,data>>，map<key,data<data>>等类型
                                $content[2] = str_replace(" ", "", $content[2]);
                                $operation_response_type = $content[2];
                                if (! in_array($operation_response_type, $java_structure))
                                {
                                    while(is_numeric(strpos($operation_response_type, "<")))
                                    {
                                        preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $operation_response_type, $content);
                                        if(is_numeric(strpos($operation_response_type, ',')))
                                        {
                                            $content_arr = explode(',', $content[1]);
                                            if (isset($content_arr[1]))
                                            {
                                                $content[1] = $content_arr[1];
                                            }
                                            else
                                            {
                                                $content[1] = $content_arr[0];
                                            }
                                        }
                                        $content[1] = str_replace(" ", "", $content[1]);
                                        if(! in_array($content[1], $java_structure))
                                        {
                                            $operation_response_type = $content[1];
                                            $operation_generic = strtolower($content[2]);
                                        }
                                        else
                                        {
                                            $operation_response_type = $content[2];
                                        }
                                    }
                                    if(is_numeric(strpos($operation_response_type, ',')))
                                    {
                                        $operation_response_type = explode(',', $operation_response_type);
                                        $operation_response_type = $operation_response_type[count($operation_response_type) -1];
                                    }
                                }
                            }
                        }
                        else
                        {
                            preg_match("/([\\s\\S]*?)\\s{1,}([\\s\\S]*)/", $content[1], $content);
                            // 接口方法名获取
                            $operation_name = $content[2];
                            // 接口返回参数
                            $operation_response_type = $content[1];
                        }
                        $api_result_param = array();
                        $type = strtolower($operation_response_type);
                        $type = str_replace(" ", "", $type);
                        switch($type)
                        {
                            case "integer":
                                $operation_response_type = '3';
                                break;
                            case "int":
                                $operation_response_type = '3';
                                break;
                            case "int[]":
                                $operation_response_type = '12';
                                break;
                            case "string":
                                $operation_response_type = '0';
                                break;
                            case "string[]":
                                $operation_response_type = '12';
                                break;
                            case "list":
                                $operation_response_type = '12';
                                break;
                            case 'long':
                                $operation_response_type = '11';
                                break;
                            case 'float':
                                $operation_response_type = '4';
                                break;
                            case 'double':
                                $operation_response_type = '5';
                                break;
                            case 'byte':
                                $operation_response_type = '9';
                                break;
                            case 'file':
                                $operation_response_type = '1';
                                break;
                            case 'date':
                                $operation_response_type = '6';
                                break;
                            case 'dateTime':
                                $operation_response_type = '7';
                                break;
                            case 'timestamp' :
                                $operation_response_type = '7';
                                break;
                            case 'boolean':
                                $operation_response_type = '8';
                                break;
                            case 'bool':
                                $operation_response_type = '8';
                                break;
                            case 'array':
                                $operation_response_type = '12';
                                break;
                            case 'short':
                                $operation_response_type = '10';
                                break;
                            case 'json':
                                $operation_response_type = '2';
                                break;
                            case 'object':
                                $operation_response_type = '13';
                                break;
                            case 'number':
                                $operation_response_type = '14';
                                break;
                            default:
                                $operation_response_type = $type;
                        }
                        // 判断有没有@ApiResponse注解且code为200返回数据结构，有的话以注解为准
                        $param_array = array(
                            "code",
                            "response",
                            "message",
                        );
                        $res = $this->regexAnnotations($operation, "ApiResponse", $param_array);
                        if ($res)
                        {
                            $flag = false;
                            foreach ($res as $response)
                            {
                                if ($response['code'] == 200 && isset($response['response']))
                                {
                                    $flag = true;
                                    $res_type = strtolower(explode(".class", $response['response'])[0]);
                                    if(isset($api_structure_list[$res_type]))
                                    {
                                        $api_result_param = array_merge($api_result_param, json_decode($api_structure_list[$res_type]["structureData"], true));
                                    }
                                    elseif($res_type != "void" && !in_array($res_type, $java_structure))
                                    {
                                        $api_result_param[] = array(
                                            "paramName" => $res_type,
                                            "paramNotNull" => "0",
                                            "paramKey" => $res_type,
                                            "paramType" => "13"
                                        );
                                    }
                                }
                                else
                                {
                                    continue;
                                }
                            }
                            if (!$flag)
                            {
                                // 没有@ApiResponse注解且code为200且返回数据结构的情况
                                // 暂时仅处理实体类
                                if(! is_numeric($operation_response_type))
                                {
                                    if(isset($api_structure_list[$operation_response_type]))
                                    {
                                        if($operation_generic)
                                        {
                                            $api_result_param = array_merge($api_result_param, $this->getGenericStructureForResponse($operation_response_type, $operation_generic, $api_structure_list));
                                        }
                                        else
                                        {
                                            $api_result_param = array_merge($api_result_param, json_decode($api_structure_list[$operation_response_type]["structureData"], true));
                                        }
                                    }
                                    elseif($operation_response_type != "void" && !in_array($operation_response_type, $java_structure))
                                    {
                                        $api_result_param[] = array(
                                            "paramName" => $operation_response_type,
                                            "paramNotNull" => "0",
                                            "paramKey" => $operation_response_type,
                                            "paramType" => "13"
                                        );
                                    }
                                }
                            }
                        }
                        else
                        {
                            // 暂时仅处理实体类
                            if(! is_numeric($operation_response_type))
                            {
                                if(isset($api_structure_list[$operation_response_type]))
                                {
                                    if($operation_generic)
                                    {
                                        $api_result_param = array_merge($api_result_param, $this->getGenericStructureForResponse($operation_response_type, $operation_generic, $api_structure_list));
                                    }
                                    else
                                    {
                                        $api_result_param = array_merge($api_result_param, json_decode($api_structure_list[$operation_response_type]["structureData"], true));
                                    }
                                }
                                elseif($operation_response_type != "void" && !in_array($operation_response_type, $java_structure))
                                {
                                    $api_result_param[] = array(
                                        "paramName" => $operation_response_type,
                                        "paramNotNull" => "0",
                                        "paramKey" => $operation_response_type,
                                        "paramType" => "13"
                                    );
                                }
                            }
                        }

                        // 接口路径获取，与基础路径组合搭配
                        $operation_result_path = array();
                        // 路径数组组合
                        if(count($base_path) <1 &&count($operation_path) <1)
                        {
                            $operation_result_path = array(
                                "/"
                            );
                        }
                        else
                        {
                            if(count($base_path) <1)
                            {
                                $operation_result_path = $operation_path;
                            }
                            elseif(count($operation_path) <1)
                            {
                                $operation_result_path = $base_path;
                            }
                            else
                            {
                                foreach($base_path as $base)
                                {
                                    foreach($operation_path as $opbase)
                                    {
                                        if($base == "/")
                                        {
                                            $operation_result_path[] = $opbase;
                                        }
                                        else
                                        {
                                            $operation_result_path[] = $base .$opbase;
                                        }
                                    }
                                }
                            }
                        }
                        // 接口请求方法获取
                        $operation_method = $this->getMethod($operation);
                        if(count($operation_method) <1 &&count($base_method) <1)
                        {
                            $operation_method[] = "get";
                        }
                        else
                        {
                            $operation_method = array_unique(array_merge($operation_method, $base_method));
                        }
                        // 接口Produces获取
                        $operation_produces = $this->getProduces($operation);
                        // 接口consumes获取
                        $operation_consumes = $this->getConsumes($operation);
                        // 接口operationId，直接处理为接口名
                        $operation_id = $operation_name;
                        // 接口参数获取
                        $operation_params = $base_params;

                        // 处理@ApiOperation
                        $param_array = array(
                            "value",
                            "notes",
                            "httpMethod",
                            "tags",
                            "produces",
                            "consumes"
                        );
                        $res = $this->regexAnnotations($operation, "ApiOperation", $param_array);
                        // @ApiOperation仅取一个
                        if($res)
                        {
                            $res = $res[count($res) -1];
                            // 处理produces
                            if(count($base_produces) >0 ||count($operation_produces) >0)
                            {
                                if(isset($res["produces"]))
                                {
                                    $res["produces"] = explode(",", $res["produces"]);
                                    $produce_arr = array();
                                    foreach($res["produces"] as $produce)
                                    {
                                        $produce_arr[] = array(
                                            "listDepth" => 0,
                                            "headerName" => "Content-Type",
                                            "headerValue" => $produce
                                        );
                                    }
                                    $operation_produces = array_unique(array_merge($operation_produces, $base_produces, $produce_arr), SORT_REGULAR);
                                }
                                else
                                {
                                    $operation_produces = array_unique(array_merge($operation_produces, $base_produces), SORT_REGULAR);
                                }
                            }
                            else
                            {
                                if(isset($res["produces"]))
                                {
                                    $res["produces"] = explode(",", $res["produces"]);
                                    $produce_arr = array();
                                    foreach($res["produces"] as $produce)
                                    {
                                        $produce_arr[] = array(
                                            "listDepth" => 0,
                                            "headerName" => "Content-Type",
                                            "headerValue" => $produce
                                        );
                                    }
                                    $operation_produces = $produce_arr;
                                }
                                else
                                {
                                    $operation_produces = array();
                                }
                            }

                            // 处理consumes
                            if(count($base_consumes) >0 ||count($operation_consumes) >0)
                            {
                                if(isset($res["consumes"]))
                                {
                                    $res["consumes"] = explode(",", $res["consumes"]);
                                    $consumes_arr = array();
                                    foreach($res["consumes"] as $consumes)
                                    {
                                        $consumes_arr[] = array(
                                            "headerName" => "Accept",
                                            "headerValue" => $consumes
                                        );
                                    }
                                    $operation_consumes = array_unique(array_merge($operation_consumes, $base_consumes, $consumes_arr), SORT_REGULAR);
                                }
                                else
                                {
                                    $operation_consumes = array_unique(array_merge($operation_consumes, $base_consumes), SORT_REGULAR);
                                }
                            }
                            else
                            {
                                if(isset($res["consumes"]))
                                {
                                    $res["consumes"] = explode(",", $res["consumes"]);
                                    $consumes_arr = array();
                                    foreach($res["consumes"] as $consumes)
                                    {
                                        $consumes_arr[] = array(
                                            "headerName" => "Accept",
                                            "headerValue" => $consumes
                                        );
                                    }
                                    $operation_consumes = $consumes_arr;
                                }
                                else
                                {
                                    $operation_consumes = array();
                                }
                            }
                            // 处理tags
                            if(isset($res["tags"]))
                            {
                                // apioperation注解写了tags，处理为接口的tags
                                if(is_array($res["tags"]))
                                {
                                    $i = 0;
                                    foreach($res["tags"] as $tag)
                                    {
                                        preg_match_all("/[\"|\']([\\s\\S]*?)[\"|\']/", $tag, $content);
                                        $operation_tags[$i] = $content[1][0];
                                        $i ++;
                                    }
                                }
                                else
                                {
                                    $operation_tags = array(
                                        $res["tags"]
                                    );
                                }
                            }
                            else
                            {
                                $j = 0;
                                foreach($base_tags as $tags)
                                {
                                    $operation_tags[$j] = $tags;
                                    $j ++;
                                }
                            }
                            // 处理value->接口名summary
                            if(isset($res['value']))
                            {
                                $operation_summary = $res['value'];
                            }
                            else
                            {
                                $operation_summary = $operation_name;
                            }
                            // 处理notes->接口描述describtion
                            if(isset($res['notes']))
                            {
                                $operation_description = $res["notes"];
                            }
                            else
                            {
                                $operation_description = "";
                            }
                        }
                        else
                        {
                            // 处理produces
                            $operation_produces = array_unique(array_merge($operation_produces, $base_produces));
                            // 处理consumes
                            $operation_consumes = array_unique(array_merge($operation_consumes, $base_consumes), SORT_REGULAR);
                            // 处理tags
                            $j = 0;
                            foreach($base_tags as $tags)
                            {
                                $operation_tags[$j] = $tags;
                                $j ++;
                            }

                            // 处理value->接口名summary
                            $operation_summary = $operation_name;
                            // 处理notes->接口描述describtion
                            $operation_description = "";
                            // 接口参数处理
                        }
                        $operation_params = $this->getOperationParam($operation, $operation_params, $brackets_param_string);
                        // 将所有接口的分组加入groupList
                        $data['groupList'] = array_unique(array_merge($data['groupList'], $operation_tags));
                        // 参数处理
                        // path类型
                        $api_restful_param = array();
                        // body或form类型
                        $api_request_param = array();
                        // query类型
                        $api_url_param = array();
                        // header类型为$operation_params
                        $api = array();
                        // 初始化为0
                        $api['apiRequestParamType'] = 0;
                        // 判断请求参数请求类型
                        foreach($operation_params as $request_param)
                        {
                            $request_param['in'] = str_replace(" ", "", $request_param['in']);
                            if(! is_numeric($request_param['paramType']))
                            {
                                if ($request_param['paramType'] == "httpservletrequest" || $request_param['paramType'] == "httpservletresponse" || $request_param['paramType'] == "bindingresult")
                                {
                                    continue;
                                }
                                $api['apiRequestParamType'] = 2;
                                $param_generic = "";
                                if(is_numeric(strpos($request_param['paramType'], "<")))
                                {
                                    // 对数据结构<...>及List/ArrayList<...>类型的请求参数进行处理，其余暂时不处理
                                    $content = array();
                                    preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>$/", $request_param['paramType'], $content);
                                    // 接口返回参数
                                    $content[1] = str_replace(" ", "", $content[1]);
                                    if(! in_array($content[1], $java_structure))
                                    {
                                        $request_param['paramType'] = $content[1];
                                        $param_generic = strtolower($content[2]);
                                    }
                                    elseif ($content[1] == "List" || $content[1] == 'ArrayList' || $content[1] == "list" || $content[1] == 'arraylist')
                                    {
                                        // 看是不是List<数据结构>类型
                                        $request_param['paramType'] = $content[1];
                                        $param_generic = strtolower($content[2]);
                                    }
                                }
                                if ($param_generic)
                                {
                                    if ($request_param['paramType'] == "List" || $request_param['paramType'] == 'ArrayList' || $request_param['paramType'] == "list" || $request_param['paramType'] == 'arraylist')
                                    {
                                        $request_param['childList'] = $this->getGenericStructureForResponse($request_param['paramType'], $param_generic, $api_structure_list);
                                        $request_param['paramType'] = "12";
                                        unset($request_param['in']);
                                        $api_request_param[] = $request_param;
                                    }
                                    else
                                    {
                                        $api_request_param = array_merge($api_request_param, $this->getGenericStructureForResponse($request_param['paramType'], $param_generic, $api_structure_list));
                                    }
                                }
                                elseif(isset($api_structure_list[$request_param['paramType']]))
                                {
                                    $child_structure_params = json_decode($api_structure_list[$request_param['paramType']]["structureData"], true);
                                    $api_request_param = array_merge($api_request_param, $child_structure_params);
                                    $api_data_structure[] = $api_structure_list[$request_param['paramType']];
                                }
                                else
                                {
                                    unset($request_param['in']);
                                    $request_param['paramType'] = '13';
                                    $api_request_param[] = $request_param;
                                }
                                continue;
                            }
                            if($request_param['in'] =='body')
                            {
                                unset($request_param['in']);
                                $api_request_param[] = $request_param;
                                $api['apiRequestParamType'] = 2;
                            }
                            elseif($request_param['in'] =='form')
                            {
                                unset($request_param['in']);
                                $api_request_param[] = $request_param;
                                $api['apiRequestParamType'] = 0;
                            }
                            elseif($request_param['in'] =='header')
                            {
                                unset($request_param['in']);
                                $operation_consumes[] = array(
                                    'headerName' => $request_param['paramKey'],
                                    'headerValue' => ''
                                );
                            }
                            elseif($request_param['in'] =='path')
                            {
                                unset($request_param['in']);
                                $api_restful_param[] = $request_param;
                            }
                            elseif($request_param['in'] =='query')
                            {
                                unset($request_param['in']);
                                $api_url_param[] = $request_param;
                            }
                        }
                        unset($operation_params);

                        // 接口信息整合于$controller_result["path"]中，包括路径，方法，tags，summary，description，operationId，consumes，produces，parameters，responses

                        foreach($operation_result_path as $path)
                        {
                            foreach($operation_method as $method)
                            {
                                foreach($operation_tags as $tags)
                                {
                                    $api['apiRequestType'] = $request_type_list[strtoupper($method)];
                                    $api['apiURI'] = $path;
                                    if ($operation_summary)
                                    {
                                        $api['apiName'] = $operation_summary;
                                    }
                                    elseif ($operation_name)
                                    {
                                        $api['apiName'] = $operation_name;
                                    }
                                    else
                                    {
                                        $path_name = explode("/", $path);
                                        $api['apiName'] = end($path_name);
                                    }
                                    // 分组
                                    $api['groupName'] = $tags;
                                    // 接口ID
                                    $api['swaggerID'] = $operation_id ."Using" .strtoupper($method) . "In" . $class_name;
                                    $data['swaggerIDList'][] = $operation_id ."Using" .strtoupper($method) . "In" . $class_name;
                                    // 接口描述
                                    $api['apiNote'] = $operation_description;
                                    // 请求头
                                    $api['apiHeader'] = $operation_consumes;
                                    // 响应头
                                    $api['responseHeader'] = $operation_produces;
                                    // 请求体参数
                                    $api['requestInfo'] = $api_request_param;
                                    // url参数
                                    $api['urlParam'] = $api_url_param;
                                    // restful参数
                                    $api['restfulParam'] = $api_restful_param;
                                    // 返回参数
                                    $api['resultInfo'] = $api_result_param;
                                    $api['apiFailureContentType'] = "text/html; charset=UTF-8";
                                    $api['apiFailureStatusCode'] = "200";
                                    $api['apiSuccessContentType'] = "text/html; charset=UTF-8";
                                    $api['apiSuccessStatusCode'] = "200";
                                    $api['apiRequestParamJsonType'] = 0;
                                    $api['resultParamJsonType'] = 0;
                                    $api['resultParamType'] = 0;
                                    $api['dataStructureList'] = $api_data_structure;
                                    $data['apiList'][$tags][] = $api;
                                }
                            }
                        }
                    }
                    else
                    {
                        continue;
                    }
                }
            }
            else
            {
                continue;
            }
        }
        return $data;
    }

    /**
     * 正则匹配,匹配注解括号中的参数内容
     * @param string $str 匹配字符串
     * @param string $annotations 匹配注解
     * @param  array $param_array 匹配参数
     * @return array|bool
     */
    public function regexAnnotations($str, $annotations, $param_array)
    {
        if(strpos($str, $annotations))
        {
            $content = array();
            $attribute_list = array();
            preg_match_all("/@{$annotations}\s{0,}\\(([\\s\\S]*?)\\)/", $str, $attribute_list);
            $i = 0;
            foreach($attribute_list[1] as $attribute)
            {
                foreach($param_array as $param)
                {
                    if(is_numeric(strpos($attribute, $param)))
                    {
                        $param_content = array();
                        if(preg_match_all("/{$param}\s{0,}=\s{0,}[\"]([\\s\\S]*?)[\"]/", $attribute, $param_content) >0)
                        {
                            // value = ""的类型
                            if (strpos($attribute, "\\\"") !== FALSE)
                            {
                                $attribute_copy = str_replace("\\\"", "@eo", $attribute);
                                if(preg_match_all("/{$param}\s{0,}=\s{0,}[\"]([\\s\\S]*?)[\"]/", $attribute_copy, $param_content_copy) >0)
                                {
                                    $content[$i][$param] = str_replace("@eo", "\\\"", $param_content_copy[1][0]);
                                }
                                else
                                {
                                    $content[$i][$param] = $param_content[1][0];
                                }
                            }
                            else
                            {
                                $content[$i][$param] = $param_content[1][0];
                            }
                        }
                        else if (preg_match_all("/{$param}\s{0,}=\s{0,}[\']([\\s\\S]*?)[\']/", $attribute, $param_content) >0)
                        {
                            // value = ''的类型
                            $content[$i][$param] = $param_content[1][0];
                        }
                        else if(preg_match_all("/{$param}\s{0,}=\s{0,}\{([\\s\\S]*?)\}/", $attribute, $param_content) >0)
                        {
                            // value = {}的类型
                            $resArr = explode(",", $param_content[1][0]);
                            $content[$i][$param] = $resArr;
                        }
                        else if(preg_match_all("/{$param}\s{0,}=\s{0,}([\\s\\S]*)\s{0,},{0,}/", $attribute, $param_content) >0)
                        {
                            // value = 的类型
                            $content[$i][$param] = explode(",", $param_content[1][0])[0];
                            $param = str_replace(" ", "", $param);
                            if($param =="method")
                            {
                                $str = $content[$i][$param];

                                if(is_numeric($str))
                                {

                                    switch($str)
                                    {
                                        case 0:
                                            $content[$i][$param] = "get";
                                            break;
                                        case 1:
                                            $content[$i][$param] = "head";
                                            break;
                                        case 2:
                                            $content[$i][$param] = "post";
                                            break;
                                        case 3:
                                            $content[$i][$param] = "put";
                                            break;
                                        case 4:
                                            $content[$i][$param] = "patch";
                                            break;
                                        case 5:
                                            $content[$i][$param] = "delete";
                                            break;
                                        case 6:
                                            $content[$i][$param] = "options";
                                            break;
                                        case 7:
                                            // 目前没有，默认get
                                            // $content[$param] = "trace";
                                            $content[$i][$param] = "get";
                                            break;
                                        default:
                                            $content[$i][$param] = "get";
                                            break;
                                    }
                                }
                                else if(is_numeric(strpos($str, "RequestMethod")))
                                {

                                    $content[$i][$param] = explode(",", strtolower(substr($str, strripos($str, ".") +1)))[0];
                                }
                                else
                                {
                                    $content[$i][$param] = "get";
                                }
                            }
                        }
                    }
                }
                $i ++;
            }
            return $content;
        }
        else
        {
            return false;
        }
    }

    /**
     * 从Mapping注解中获取请求方式
     * @param string $str 匹配字符串
     * @return array
     */
    public function getMethod($str)
    {
        $param_content = array();
        if(preg_match_all("/[Request|Get|Post|Patch|Put|Delete|Head|Option]+Mapping/", $str, $param_content) >0)
        {
            // Mapping仅取一个
            $res = substr($param_content[0][count($param_content[0]) -1], 0, strrpos($param_content[0][count($param_content[0]) -1], "Mapping"));
            $base_method = array();
            $res = str_replace(" ", "", $res);
            if($res =="Request")
            {
                $param_array = array(
                    "method"
                );
                $method = $this->regexAnnotations($str, "RequestMapping", $param_array);
                if(count($method) >0)
                {
                    $method = $method[count($method) -1];
                    if(isset($method["method"]))
                    {
                        if(is_array($method["method"]))
                        {
                            $i = 0;
                            foreach($method["method"] as $val)
                            {
                                if(is_numeric($val))
                                {
                                    switch($val)
                                    {
                                        case 0:
                                            $base_method[$i] = "get";
                                            break;
                                        case 1:
                                            $base_method[$i] = "head";
                                            break;
                                        case 2:
                                            $base_method[$i] = "post";
                                            break;
                                        case 3:
                                            $base_method[$i] = "put";
                                            break;
                                        case 4:
                                            $base_method[$i] = "patch";
                                            break;
                                        case 5:
                                            $base_method[$i] = "delete";
                                            break;
                                        case 6:
                                            $base_method[$i] = "options";
                                            break;
                                        case 7:
                                            // 目前没有，默认get
                                            // $content[$param] = "trace";
                                            $base_method[$i] = "get";
                                            break;
                                        default:
                                            $base_method[$i] = "get";
                                            break;
                                    }
                                }
                                else
                                {
                                    $base_method[$i] = explode(",", strtolower(substr($val, strripos($val, ".") +1)))[0];
                                }
                                $i ++;
                            }
                        }
                        else
                        {
                            $base_method[] = $method["method"];
                        }
                    }
                    else
                    {
                        $base_method = array();
                    }
                }
                else
                {
                    $param_array = array(
                        "httpMethod"
                    );
                    $res = $this->regexAnnotations($str, "ApiOperation", $param_array);
                    // @ApiOperation仅取一个
                    if($res)
                    {
                        $res = $res[count($res) - 1];
                        if(isset($res['httpMethod']))
                        {
                            $base_method[] = strtolower($res['httpMethod']);
                        }
                        else
                        {
                            $base_method = array();
                        }
                    }
                    else
                    {
                        $base_method = array();
                    }
                }
            }
            else
            {
                $base_method[] = strtolower($res);
            }
            return $base_method;
        }
        else
        {
            return array();
        }
    }

    /**
     * 从Mapping注解中获取请求路径
     * @param string $str 匹配字符串
     * @return array
     */
    public function getPath($str)
    {
        $param_content = array();
        if(preg_match_all("/[Request|Get|Post|Patch|Put|Delete|Head|Option]+Mapping/", $str, $param_content) >0)
        {
            $res = substr($param_content[0][count($param_content[0]) -1], 0, strrpos($param_content[0][count($param_content[0]) -1], "Mapping"));
            $param_array = array(
                "value"
            );
            $path = $this->regexAnnotations($str, "{$res}Mapping", $param_array);
            if($path)
            {
                $path = $path[count($path) -1];
                $base_paths = array();
                if(isset($path["value"]))
                {
                    if(is_array($path["value"]))
                    {
                        $i = 0;
                        foreach($path["value"] as $val)
                        {
                            $content = array();
                            preg_match_all("/[\"|\']([\\s\\S]*?)[\"|\']/", $val, $content);
                            $base_paths[$i] = $content[1][0];
                            $i ++;
                        }
                    }
                    else
                    {
                        $base_paths[] = $path["value"];
                    }
                }
                return $base_paths;
            }
            else if(preg_match_all("/@{$res}Mapping\(\"([\\s\\S]*?)\"\)/", $str, $path) >0)
            {
                // 获取@RequestMapping("xxx")的xxx作为基本路径
                return $path[1];
            }
            else
            {
                return array();
            }
        }
        else
        {
            return array();
        }
    }

    /**
     * 从Mapping注解中获取produces
     * @param string $str 匹配字符串
     * @return array
     */
    public function getProduces($str)
    {
        $param_content = array();
        if(preg_match_all("/[Request|Get|Post|Patch|Put|Delete|Head|Option]+Mapping/", $str, $param_content) >0)
        {
            $res = substr($param_content[0][count($param_content[0]) -1], 0, strrpos($param_content[0][count($param_content[0]) -1], "Mapping"));
            $param_array = array(
                "produces"
            );
            $produces = $this->regexAnnotations($str, "{$res}Mapping", $param_array);
            if($produces)
            {
                $produces = $produces[count($produces) -1];
                $base_produces = array();
                if(isset($produces["produces"]))
                {
                    if(is_array($produces["produces"]))
                    {

                        $i = 0;
                        foreach($produces["produces"] as $val)
                        {
                            $content = array();
                            preg_match_all("/[\"|\']([\\s\\S]*?)[\"|\']/", $val, $content);
                            $base_produces[$i] = array(
                                "listDepth" => 0,
                                "headerName" => "Content-Type",
                                "headerValue" => $content[1][0]
                            );
                            $i ++;
                        }
                    }
                    else
                    {
                        $base_produces[] = array(
                            "listDepth" => 0,
                            "headerName" => "Content-Type",
                            "headerValue" => $produces["produces"]
                        );
                    }
                }
                return $base_produces;
            }
            else
            {
                return array();
            }
        }
        else
        {
            return array();
        }
    }

    /**
     * 从Mapping注解中获取consumes
     * @param $str
     * @return array
     */
    public function getConsumes($str)
    {
        $param_content = array();
        if(preg_match_all("/[Request|Get|Post|Patch|Put|Delete|Head|Option]+Mapping/", $str, $param_content) >0)
        {
            $res = substr($param_content[0][count($param_content[0]) -1], 0, strrpos($param_content[0][count($param_content[0]) -1], "Mapping"));
            $param_array = array(
                "consumes"
            );
            $consumes = $this->regexAnnotations($str, "{$res}Mapping", $param_array);
            if($consumes)
            {
                $consumes = $consumes[count($consumes) -1];
                $base_consumes = array();
                if(isset($consumes["consumes"]))
                {
                    if(is_array($consumes["consumes"]))
                    {
                        $i = 0;
                        foreach($consumes["consumes"] as $val)
                        {
                            $content = array();
                            preg_match_all("/[\"|\']([\\s\\S]*?)[\"|\']/", $val, $content);
                            $base_consumes[$i] = array(
                                "headerName" => "Accept",
                                "headerValue" => $content[1][0]
                            );
                            $i ++;
                        }
                    }
                    else
                    {
                        $base_consumes[] = array(
                            "headerName" => "Accept",
                            "headerValue" => $consumes["consumes"]
                        );
                    }
                }
                return $base_consumes;
            }
            else
            {
                return array();
            }
        }
        else
        {
            return array();
        }
    }

    /**
     * 从Mapping注解中获取请求参数
     * @param $str
     * @return array
     */
    public function getParam($str)
    {
        $param_content = array();
        if(preg_match_all("/[Request|Get|Post|Patch|Put|Delete|Head|Option]+Mapping/", $str, $param_content) >0)
        {
            $res = substr($param_content[0][count($param_content[0]) -1], 0, strrpos($param_content[0][count($param_content[0]) -1], "Mapping"));
            $param_array = array(
                "params"
            );
            $params = $this->regexAnnotations($str, "{$res}Mapping", $param_array);
            if($params)
            {
                $params = $params[count($params) -1];
                $base_params = array();
                if(isset($params["params"]))
                {
                    if(is_array($params["params"]))
                    {
                        $i = 0;
                        foreach($params["params"] as $val)
                        {
                            $content = array();
                            preg_match_all("/[\"|\']([\\s\\S]*?)[\"|\']/", $val, $content);
                            $base_params[$i] = $content[1][0];
                            $i ++;
                        }
                    }
                    else
                    {
                        $base_params[] = $params["params"];
                    }
                }
                return $base_params;
            }
            else
            {
                return array();
            }
        }
        else
        {
            return array();
        }
    }

    /**
     * 从ApiOperation注解获取参数
     * @param $str
     * @param $operation_params
     * @param $brackets_param_string
     * @return mixed
     */
    public function getOperationParam($str, $operation_params, $brackets_param_string)
    {
        // 从mapping获取
        $params = $this->getParam($str);
        if(isset($params))
        {
            if(is_array($params))
            {
                foreach($params as $param)
                {
                    $operation_params[$param]["paramKey"] = $param;
                    $operation_params[$param]["in"] = "query";
                    $operation_params[$param]["paramNotNull"] = 0;
                    $operation_params[$param]["paramType"] = "0";
                }
            }
            else
            {
                $operation_params[$params]["paramKey"] = $params;
                $operation_params[$params]["in"] = "query";
                $operation_params[$params]["paramNotNull"] = 0;
                $operation_params[$params]["paramType"] = "0";
            }
        }
        // 从@ApiParam()获取
        $param_array = array(
            "name",
            "value",
            "required",
            "example"
        );
        $api_param_list = $this->regexAnnotationsForApiParam($str, "ApiParam", $param_array);
        if(is_array($api_param_list))
        {
            foreach ($api_param_list as $api_param)
            {
                $operation_params[$api_param['paramKey']] = $api_param;
            }
        }

        // 处理下@RequestBody，@RequestHeader，@RequestParam，@PathVariable

        $request_param_body = array();
        $request_param_content = array();
        if(preg_match_all("/@RequestBody\s{0,}\\(([\\s\\S]*?)\\)\s{0,}([\\s\\S]*?)\s{0,}[,|)]/", $str, $request_param_content) >0)
        {
            $k = 0;
            foreach($request_param_content[2] as $con)
            {
                $param_array = array(
                    "name",
                    "value",
                    "required"
                );
                $param_message = $this->regexAnnotations($request_param_content[0][$k], "RequestBody", $param_array);
                if ($param_message)
                {
                    $param_message = $param_message[0];
                }
                $result_content = array();
                preg_match("/([\\s\\S]*?)\s{1,}(.*)/", $con, $result_content);
                $result_content[2] = str_replace(" ","", $result_content[2]);
                $request_param_body[$k]["in"] = "body";
                if ($param_message['name'])
                {
                    $request_param_body[$k]["paramKey"] = $param_message['name'];
                }
                elseif ($param_message['value'])
                {
                    $request_param_body[$k]["paramKey"] = $param_message['value'];
                }
                else
                {
                    $request_param_body[$k]["paramKey"] = $result_content[2];
                }
                $request_param_body[$k]["paramName"] = $result_content[2];

                if (isset($param_message['required']) && $param_message['required'] =="true")
                {
                    $request_param_body[$k]["paramNotNull"] = 0;
                }
                else
                {
                    $request_param_body[$k]["paramNotNull"] = 1;
                }
                $type = strtolower($result_content[1]);
                $type = str_replace(" ", "", $type);
                switch($type)
                {
                    case "integer":
                        $request_param_body[$k]["paramType"] = '3';
                        break;
                    case "int":
                        $request_param_body[$k]["paramType"] = '3';
                        break;
                    case "int[]":
                        $request_param_body[$k]["paramType"] = '12';
                        break;
                    case "string":
                        $request_param_body[$k]["paramType"] = '0';
                        break;
                    case "string[]":
                        $request_param_body[$k]["paramType"] = '12';
                        break;
                    case 'long':
                        $request_param_body[$k]["paramType"] = '11';
                        break;
                    case 'float':
                        $request_param_body[$k]["paramType"] = '4';
                        break;
                    case 'double':
                        $request_param_body[$k]["paramType"] = '5';
                        break;
                    case 'byte':
                        $request_param_body[$k]["paramType"] = '9';
                        break;
                    case 'file':
                        $request_param_body[$k]["paramType"] = '1';
                        break;
                    case 'date':
                        $request_param_body[$k]["paramType"] = '6';
                        break;
                    case 'dateTime':
                        $request_param_body[$k]["paramType"] = '7';
                        break;
                    case 'timestamp' :
                        $request_param_body[$k]["paramType"] = '7';
                        break;
                    case 'boolean':
                        $request_param_body[$k]["paramType"] = '8';
                        break;
                    case 'bool':
                        $request_param_body[$k]["paramType"] = '8';
                        break;
                    case 'array':
                        $request_param_body[$k]["paramType"] = '12';
                        break;
                    case 'short':
                        $request_param_body[$k]["paramType"] = '10';
                        break;
                    case 'json':
                        $request_param_body[$k]["paramType"] = '2';
                        break;
                    case 'object':
                        $request_param_body[$k]["paramType"] = '13';
                        break;
                    case 'number':
                        $request_param_body[$k]["paramType"] = '14';
                        break;
                    default:
                        $request_param_body[$k]["paramType"] = $type;
                }
                $k ++;
            }
        };
        if ($request_param_body)
        {
            foreach ($request_param_body as $request_param)
            {
                $operation_params[$request_param['paramKey']] = $request_param;
            }
        };
        // 处理下@RequestHeader
        $request_param_header = array();
        if(preg_match_all("/@RequestHeader\s{0,}\\(([\\s\\S]*?)\\)\s{0,}([\\s\\S]*?)\s{0,}[,|)]/", $str, $request_param_content) >0)
        {
            $k = 0;
            foreach($request_param_content[2] as $con)
            {
                $param_array = array(
                    "name",
                    "value",
                    "required"
                );
                $param_message = $this->regexAnnotations($request_param_content[0][$k], "RequestHeader", $param_array);
                if ($param_message)
                {
                    $param_message = $param_message[0];
                }
                preg_match("/([\\s\\S]*?)\s{1,}(.*)/", $con, $result_content);
                $result_content[2] = str_replace(" ","", $result_content[2]);
                $request_param_header[$k]["in"] = "header";
                if ($param_message['name'])
                {
                    $request_param_header[$k]["paramKey"] = $param_message['name'];
                }
                elseif ($param_message['value'])
                {
                    $request_param_header[$k]["paramKey"] = $param_message['value'];
                }
                else
                {
                    $request_param_header[$k]["paramKey"] = $result_content[2];
                }
                $request_param_header[$k]["paramName"] = $result_content[2];

                if (isset($param_message['required']) && $param_message['required'] =="true")
                {
                    $request_param_header[$k]["paramNotNull"] = 0;
                }
                else
                {
                    $request_param_header[$k]["paramNotNull"] = 1;
                }
                $type = strtolower($result_content[1]);
                $type = str_replace(" ", "", $type);
                switch($type)
                {
                    case "integer":
                        $request_param_header[$k]["paramType"] = '3';
                        break;
                    case "int":
                        $request_param_header[$k]["paramType"] = '3';
                        break;
                    case "int[]":
                        $request_param_header[$k]["paramType"] = '12';
                        break;
                    case "string":
                        $request_param_header[$k]["paramType"] = '0';
                        break;
                    case "string[]":
                        $request_param_header[$k]["paramType"] = '12';
                        break;
                    case 'long':
                        $request_param_header[$k]["paramType"] = '11';
                        break;
                    case 'float':
                        $request_param_header[$k]["paramType"] = '4';
                        break;
                    case 'double':
                        $request_param_header[$k]["paramType"] = '5';
                        break;
                    case 'byte':
                        $request_param_header[$k]["paramType"] = '9';
                        break;
                    case 'file':
                        $request_param_header[$k]["paramType"] = '1';
                        break;
                    case 'date':
                        $request_param_header[$k]["paramType"] = '6';
                        break;
                    case 'dateTime':
                        $request_param_header[$k]["paramType"] = '7';
                        break;
                    case 'timestamp' :
                        $request_param_header[$k]["paramType"] = '7';
                        break;
                    case 'boolean':
                        $request_param_header[$k]["paramType"] = '8';
                        break;
                    case 'bool':
                        $request_param_header[$k]["paramType"] = '8';
                        break;
                    case 'array':
                        $request_param_header[$k]["paramType"] = '12';
                        break;
                    case 'short':
                        $request_param_header[$k]["paramType"] = '10';
                        break;
                    case 'json':
                        $request_param_header[$k]["paramType"] = '2';
                        break;
                    case 'object':
                        $request_param_header[$k]["paramType"] = '13';
                        break;
                    case 'number':
                        $request_param_header[$k]["paramType"] = '14';
                        break;
                    default:
                        $request_param_header[$k]["paramType"] = $type;
                }
                $k ++;
            }
        }
        if ($request_param_header)
        {
            foreach ($request_param_header as $header_param)
            {
                $operation_params[$header_param['paramKey']] = $header_param;
            }
        };
        // 处理下@RequestParam
        $request_param_param = array();
        if(preg_match_all("/@RequestParam\s{0,}\\(([\\s\\S]*?)\\)\s{0,}([\\s\\S]*?)\s{0,}[,|)]/", $str, $request_param_content) >0)
        {
            $k = 0;
            foreach($request_param_content[2] as $content)
            {
                $param_array = array(
                    "name",
                    "value",
                    "required"
                );
                $param_message = $this->regexAnnotations($request_param_content[0][$k], "RequestParam", $param_array);
                if ($param_message)
                {
                    $param_message = $param_message[0];
                }
                preg_match("/([\\s\\S]*?)\s{1,}([\\s\\S]*)/", $content, $result_content);
                $result_content[2] = str_replace(" ","", $result_content[2]);
                $request_param_param[$k]["in"] = "query";
                if (isset($param_message['name']))
                {
                    $request_param_param[$k]["paramKey"] = $param_message['name'];
                }
                elseif (isset($param_message['value']))
                {
                    $request_param_param[$k]["paramKey"] = $param_message['value'];
                }
                else
                {
                    $request_param_param[$k]["paramKey"] = $result_content[2];
                }
                $request_param_param[$k]["paramName"] = $result_content[2];
                if (isset($param_message['required']) && $param_message['required'] =="true")
                {
                    $request_param_param[$k]["paramNotNull"] = 0;
                }
                else
                {
                    $request_param_param[$k]["paramNotNull"] = 1;
                }
                $type = strtolower($result_content[1]);
                $type = str_replace(" ", "", $type);
                switch($type)
                {
                    case "integer":
                        $request_param_param[$k]["paramType"] = '3';
                        break;
                    case "int":
                        $request_param_param[$k]["paramType"] = '3';
                        break;
                    case "int[]":
                        $request_param_param[$k]["paramType"] = '12';
                        break;
                    case "string":
                        $request_param_param[$k]["paramType"] = '0';
                        break;
                    case "string[]":
                        $request_param_param[$k]["paramType"] = '12';
                        break;
                    case 'long':
                        $request_param_param[$k]["paramType"] = '11';
                        break;
                    case 'float':
                        $request_param_param[$k]["paramType"] = '4';
                        break;
                    case 'double':
                        $request_param_param[$k]["paramType"] = '5';
                        break;
                    case 'byte':
                        $request_param_param[$k]["paramType"] = '9';
                        break;
                    case 'file':
                        $request_param_param[$k]["paramType"] = '1';
                        break;
                    case 'date':
                        $request_param_param[$k]["paramType"] = '6';
                        break;
                    case 'dateTime':
                        $request_param_param[$k]["paramType"] = '7';
                        break;
                    case 'timestamp' :
                        $request_param_param[$k]["paramType"] = '7';
                        break;
                    case 'boolean':
                        $request_param_param[$k]["paramType"] = '8';
                        break;
                    case 'bool':
                        $request_param_param[$k]["paramType"] = '8';
                        break;
                    case 'array':
                        $request_param_param[$k]["paramType"] = '12';
                        break;
                    case 'short':
                        $request_param_param[$k]["paramType"] = '10';
                        break;
                    case 'json':
                        $request_param_param[$k]["paramType"] = '2';
                        break;
                    case 'object':
                        $request_param_param[$k]["paramType"] = '13';
                        break;
                    case 'number':
                        $request_param_param[$k]["paramType"] = '14';
                        break;
                    default:
                        $request_param_param[$k]["paramType"] = $type;
                }
                $k ++;
            }
        }
        if ($request_param_param)
        {
            foreach ($request_param_param as $request_param)
            {
                $operation_params[$request_param['paramKey']] = $request_param;
            }
        }
        // 处理下@PathVariable
        $request_param_path = array();
        if(preg_match_all("/@PathVariable\s{0,}\\(([\\s\\S]*?)\\)\s{0,}([\\s\\S]*?)\s{0,}[,|)]/", $str, $request_param_content) >0)
        {
            $k = 0;
            foreach($request_param_content[2] as $content)
            {
                $param_array = array(
                    "name",
                    "value",
                    "required"
                );
                $param_message = $this->regexAnnotations($request_param_content[0][$k], "PathVariable", $param_array);
                if ($param_message)
                {
                    $param_message = $param_message[0];
                }
                preg_match("/([\\s\\S]*?)\s{1,}(.*)/", $content, $result_content);
                $result_content[2] = str_replace(" ","", $result_content[2]);
                $request_param_path[$k]["in"] = "path";
                if (isset($param_message['name']))
                {
                    $request_param_path[$k]["paramKey"] = $param_message['name'];
                }
                elseif (isset($param_message['value']))
                {
                    $request_param_path[$k]["paramKey"] = $param_message['value'];
                }
                else
                {
                    $request_param_path[$k]["paramKey"] = $result_content[2];
                }
                $request_param_path[$k]["paramName"] = $result_content[2];

                if (isset($param_message['required']) && $param_message['required'] =="true")
                {
                    $request_param_path[$k]["paramNotNull"] = 0;
                }
                else
                {
                    $request_param_path[$k]["paramNotNull"] = 1;
                }
                $type = strtolower($result_content[1]);
                $type = str_replace(" ", "", $type);
                switch($type)
                {
                    case "integer":
                        $request_param_path[$k]["paramType"] = '3';
                        break;
                    case "int":
                        $request_param_path[$k]["paramType"] = '3';
                        break;
                    case "int[]":
                        $request_param_path[$k]["paramType"] = '12';
                        break;
                    case "string":
                        $request_param_path[$k]["paramType"] = '0';
                        break;
                    case "string[]":
                        $request_param_path[$k]["paramType"] = '12';
                        break;
                    case 'long':
                        $request_param_path[$k]["paramType"] = '11';
                        break;
                    case 'float':
                        $request_param_path[$k]["paramType"] = '4';
                        break;
                    case 'double':
                        $request_param_path[$k]["paramType"] = '5';
                        break;
                    case 'byte':
                        $request_param_path[$k]["paramType"] = '9';
                        break;
                    case 'file':
                        $request_param_path[$k]["paramType"] = '1';
                        break;
                    case 'date':
                        $request_param_path[$k]["paramType"] = '6';
                        break;
                    case 'dateTime':
                        $request_param_path[$k]["paramType"] = '7';
                        break;
                    case 'timestamp' :
                        $request_param_path[$k]["paramType"] = '7';
                        break;
                    case 'boolean':
                        $request_param_path[$k]["paramType"] = '8';
                        break;
                    case 'bool':
                        $request_param_path[$k]["paramType"] = '8';
                        break;
                    case 'array':
                        $request_param_path[$k]["paramType"] = '12';
                        break;
                    case 'short':
                        $request_param_path[$k]["paramType"] = '10';
                        break;
                    case 'json':
                        $request_param_path[$k]["paramType"] = '2';
                        break;
                    case 'object':
                        $request_param_path[$k]["paramType"] = '13';
                        break;
                    case 'number':
                        $request_param_path[$k]["paramType"] = '14';
                        break;
                    default:
                        $request_param_path[$k]["paramType"] = $type;
                }
                $k ++;
            }
        }
        if ($request_param_path)
        {
            foreach ($request_param_path as $path_param)
            {
                $operation_params[$path_param['paramKey']] = $path_param;
            }
        }
        // 处理括号里的请求参数
        if ($brackets_param_string)
        {
            if (strpos($brackets_param_string, "@Validated") !== FALSE)
            {
                $brackets_param_string = str_replace("@Validated", "", $brackets_param_string);
            }
            // @ApiParam（.*）等类型已经处理了，但形如@RequestBody String data的还得进行参数处理
            if (strpos($brackets_param_string, "@ApiParam") !== FALSE)
            {
                $brackets_param_string = preg_replace("/@ApiParam\s{0,}\\([\\s\\S]*?\\)/", "@eo", $brackets_param_string);
            }
            if (strpos($brackets_param_string, "@RequestBody") !== FALSE)
            {
                $brackets_param_string = preg_replace("/@RequestBody\s{0,}\\([\\s\\S]*?\\)/", "@eo", $brackets_param_string);
            }
            if (strpos($brackets_param_string, "@RequestHeader") !== FALSE)
            {
                $brackets_param_string = preg_replace("/@RequestHeader\s{0,}\\([\\s\\S]*?\\)/", "@eo", $brackets_param_string);
            }
            if (strpos($brackets_param_string, "@RequestParam") !== FALSE)
            {
                $brackets_param_string = preg_replace("/@RequestParam\s{0,}\\([\\s\\S]*?\\)/", "@eo", $brackets_param_string);
            }
            if (strpos($brackets_param_string, "@PathVariable") !== FALSE)
            {
                $brackets_param_string = preg_replace("/@PathVariable\s{0,}\\([\\s\\S]*?\\)/", "@eo", $brackets_param_string);
            }
            $brackets_param_array = explode(",", $brackets_param_string);
            foreach ($brackets_param_array as $brackets)
            {
                if (strpos($brackets, "@eo" ) !== FALSE)
                {
                    continue;
                }
                $brackets = trim($brackets);
                $param_type_value = explode(" ", $brackets);
                $param_type_value = array_values(array_filter($param_type_value));
                $len = count($param_type_value);
                $type = strtolower($param_type_value[ $len - 2 ]);
                $type = str_replace(" ", "", $type);
                switch($type)
                {
                    case "integer":
                        $type = '3';
                        break;
                    case "int":
                        $type = '3';
                        break;
                    case "int[]":
                        $type = '12';
                        break;
                    case "string":
                        $type = '0';
                        break;
                    case "string[]":
                        $type = '12';
                        break;
                    case 'long':
                        $type = '11';
                        break;
                    case 'float':
                        $type = '4';
                        break;
                    case 'double':
                        $type = '5';
                        break;
                    case 'byte':
                        $type = '9';
                        break;
                    case 'file':
                        $type = '1';
                        break;
                    case 'date':
                        $type = '6';
                        break;
                    case 'dateTime':
                        $type = '7';
                        break;
                    case 'timestamp' :
                        $type = '7';
                        break;
                    case 'boolean':
                        $type = '8';
                        break;
                    case 'bool':
                        $type = '8';
                        break;
                    case 'array':
                        $type = '12';
                        break;
                    case 'short':
                        $type = '10';
                        break;
                    case 'json':
                        $type = '2';
                        break;
                    case 'object':
                        $type = '13';
                        break;
                    case 'number':
                        $type = '14';
                        break;
                }
                $operation_params[$param_type_value[ $len -1 ]] = array(
                    "paramKey" => $param_type_value[ $len -1 ],
                    "paramNotNull" => 0,
                    "in" => "body",
                    "paramType" => $type
                );
            }
        }
        // 从@ApiImplicitParam()获取
        $param_array = array(
            "name",
            "value",
            "required",
            "dataType",
            "paramType",
            "example"
        );
        $api_implicit_param = $this->regexAnnotations($str, "ApiImplicitParam", $param_array);
        $implicit_params = array();
        if(is_array($api_implicit_param))
        {
            $j = 0;
            foreach($api_implicit_param as $param)
            {
                $param["paramType"] = str_replace(" ", "", $param["paramType"]);
                if(isset($param["paramType"]) &&in_array($param["paramType"], array(
                        "body",
                        "query",
                        "path",
                        "header",
                        "form"
                    )))
                {
                    $implicit_params[$j]["in"] = $param["paramType"];
                }
                else
                {
                    $implicit_params[$j]["in"] = "body";
                }

                if(isset($param["dataType"]))
                {
                    $type = strtolower($param["dataType"]);
                    switch($type)
                    {
                        case "integer":
                            $implicit_params[$j]["paramType"] = '3';
                            break;
                        case "int":
                            $implicit_params[$j]["paramType"] = '3';
                            break;
                        case "int[]":
                            $implicit_params[$j]["paramType"] = '12';
                            break;
                        case "string":
                            $implicit_params[$j]["paramType"] = '0';
                            break;
                        case "string[]":
                            $implicit_params[$j]["paramType"] = '12';
                            break;
                        case "list":
                            $implicit_params[$j]["paramType"] = '12';
                            break;
                        case 'long':
                            $implicit_params[$j]["paramType"] = '11';
                            break;
                        case 'float':
                            $implicit_params[$j]["paramType"] = '4';
                            break;
                        case 'double':
                            $implicit_params[$j]["paramType"] = '5';
                            break;
                        case 'byte':
                            $implicit_params[$j]["paramType"] = '9';
                            break;
                        case 'file':
                            $implicit_params[$j]["paramType"] = '1';
                            break;
                        case 'date':
                            $implicit_params[$j]["paramType"] = '6';
                            break;
                        case 'dateTime':
                            $implicit_params[$j]["paramType"] = '7';
                            break;
                        case 'timestamp' :
                            $implicit_params[$j]["paramType"] = '7';
                            break;
                        case 'boolean':
                            $implicit_params[$j]["paramType"] = '8';
                            break;
                        case 'bool':
                            $implicit_params[$j]["paramType"] = '8';
                            break;
                        case 'array':
                            $implicit_params[$j]["paramType"] = '12';
                            break;
                        case 'short':
                            $implicit_params[$j]["paramType"] = '10';
                            break;
                        case 'json':
                            $implicit_params[$j]["paramType"] = '2';
                            break;
                        case 'object':
                            $implicit_params[$j]["paramType"] = '13';
                            break;
                        case 'number':
                            $implicit_params[$j]["paramType"] = '14';
                            break;
                        default:
                            $implicit_params[$j]["paramType"] = $type;
                    }
                }
                else
                {
                    $implicit_params[$j]["paramType"] = "0";
                }
                if(isset($param["name"]))
                {
                    $implicit_params[$j]["paramKey"] = $param["name"];
                }
                else
                {
                    continue;
                }
                if(isset($param["value"]))
                {
                    $implicit_params[$j]["paramName"] = $param["value"];
                }
                else
                {
                    $implicit_params[$j]["paramName"] = $param["name"];
                }
                if (isset($param["required"]))
                {
                    $param["required"] = str_replace(" ", "", $param["required"]);
                    if ($param["required"] =="true")
                    {
                        $implicit_params[$j]["paramNotNull"] = 0;
                    }
                    else
                    {
                        $implicit_params[$j]["paramNotNull"] = 1;
                    }
                }
                else
                {
                    $implicit_params[$j]["paramNotNull"] = 1;
                }
                if(isset($param["defaultValue"]))
                {
                    $implicit_params[$j]["paramValue"] = $param["defaultValue"];
                }
                $j ++;
            }
            foreach ($implicit_params as $implicit)
            {
                $operation_params[$implicit['paramKey']] = $implicit;
            }
        }
        return $operation_params;
    }

    /**
     * 从ApiParam中获取参数
     * @param string $str 匹配字符串
     * @param string $annotations 注解表示
     * @param array $param_array 匹配参数
     * @return array|bool
     */
    public function regexAnnotationsForApiParam($str, $annotations, $param_array)
    {
        if(strpos($str, $annotations))
        {
            $content = array();
            $attribute_list = array();
            preg_match_all("/@{$annotations}\(([\\s\\S]*?)\)\s([\\s\\S]*?)\s([\\s\\S]*?)\s{0,}[,|)]/", $str, $attribute_list);
            if(count($attribute_list[1]) >0)
            {
                $i = 0;
                foreach($attribute_list[1] as $attribute)
                {
                    foreach($param_array as $param)
                    {
                        if(is_numeric(strpos($attribute, $param)))
                        {
                            $param_content = array();
                            if(preg_match_all("/{$param}\s{0,}=\s{0,}[\"]([\\s\\S]*?)[\"]/", $attribute, $param_content) >0)
                            {
                                // value = ""
                                $content[$i][$param] = $param_content[1][0];
                            }
                            else if(preg_match_all("/{$param}\s{0,}=\s{0,}[\']([\\s\\S]*?)[\']/", $attribute, $param_content) >0)
                            {
                                // value = ''
                                $content[$i][$param] = $param_content[1][0];
                            }
                            else if(preg_match_all("/{$param}\s{0,}=\s{0,}([\\s\\S]*)\s{0,},{0,}/", $attribute, $param_content) >0)
                            {
                                // value = ""
                                $content[$i][$param] = explode(",", $param_content[1][0])[0];
                            }
                        }
                    }
                    $i ++;
                }
            }
            $param_content = array();
            if(count($content) >0)
            {
                $j = 0;
                foreach($content as $param)
                {
                    $param_content[$j]["in"] = "body";
                    if(isset($param["name"]))
                    {
                        $param_content[$j]["paramKey"] = $param["name"];
                    }
                    else
                    {
                        $param_content[$j]["paramKey"] = $attribute_list[3][$j];
                    }
                    if(isset($param["value"]))
                    {
                        $param_content[$j]["paramName"] = $param["value"];
                    }
                    else
                    {
                        $param_content[$j]["paramName"] = $attribute_list[3][$j];
                    }
                    $param["required"] = str_replace(" ", "", $param["required"]);
                    if(isset($param["required"]) &&$param["required"] =="true")
                    {
                        $param_content[$j]["paramNotNull"] = 0;
                    }
                    else
                    {
                        $param_content[$j]["paramNotNull"] = 1;
                    }
                    if(isset($param["example"]))
                    {
                        $param_content[$j]["paramValue"] = $param["example"];
                    }
                    // 数据类型判断,初步处理，暂时无法处理这里的数据结构
                    $type = strtolower($attribute_list[2][$j]);
                    $type = str_replace(" ", "", $type);
                    switch($type)
                    {
                        case "integer":
                            $param_content[$j]['paramType'] = '3';
                            break;
                        case "int":
                            $param_content[$j]['paramType'] = '3';
                            break;
                        case "int[]":
                            $param_content[$j]['paramType'] = '12';
                            break;
                        case "string":
                            $param_content[$j]['paramType'] = '0';
                            break;
                        case "string[]":
                            $param_content[$j]['paramType'] = '12';
                            break;
                        case 'long':
                            $param_content[$j]['paramType'] = '11';
                            break;
                        case 'float':
                            $param_content[$j]['paramType'] = '4';
                            break;
                        case 'double':
                            $param_content[$j]['paramType'] = '5';
                            break;
                        case 'byte':
                            $param_content[$j]['paramType'] = '9';
                            break;
                        case 'file':
                            $param_content[$j]['paramType'] = '1';
                            break;
                        case 'date':
                            $param_content[$j]['paramType'] = '6';
                            break;
                        case 'dateTime':
                            $param_content[$j]['paramType'] = '7';
                            break;
                        case 'timestamp' :
                            $param_content[$j]['paramType'] = '7';
                            break;
                        case 'boolean':
                            $param_content[$j]['paramType'] = '8';
                            break;
                        case 'bool':
                            $param_content[$j]['paramType'] = '8';
                            break;
                        case 'array':
                            $param_content[$j]['paramType'] = '12';
                            break;
                        case 'short':
                            $param_content[$j]['paramType'] = '10';
                            break;
                        case 'json':
                            $param_content[$j]['paramType'] = '2';
                            break;
                        case 'object':
                            $param_content[$j]['paramType'] = '13';
                            break;
                        case 'number':
                            $param_content[$j]['paramType'] = '14';
                            break;
                        default:
                            $param_content[$j]['paramType'] = $type;
                    }
                    $j ++;
                }
            }
            return $param_content;
        }
        else
        {
            return false;
        }
    }

    /**
     * 对返回类型为泛型的接口进行处理
     * @param $parent_type string 父类
     * @param $children_type string 泛型
     * @param $api_structure_list array 数据结构列表
     * @param array $model_name_content 数据结构列表内容
     * @return array|mixed
     */
    private function getGenericStructure($parent_type, $children_type, &$api_structure_list, $model_name_content)
    {
        $structure_result = $this->getDataStructureByName($parent_type, $model_name_content, $api_structure_list);
        if ($structure_result)
        {
            $structure = $structure_result;
        }
        elseif ( $parent_type == "list" || $parent_type == "arraylist" || $parent_type == "List" || $parent_type == "ArrayList" || $parent_type == 'Collection' || $parent_type == 'collection')
        {
            if(is_numeric(strpos($children_type, "<")))
            {
                preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $children_type, $content);
                $content[1] = str_replace(" ", "", strtolower($content[1]));
                $content[2] = str_replace(" ", "", strtolower($content[2]));
                return $this->getGenericStructure($content[1], $content[2], $api_structure_list, $model_name_content);
            }
            else
            {
                $structure_result = $this->getDataStructureByName($children_type, $model_name_content, $api_structure_list);
                if ($structure_result)
                {
                    $structure = $structure_result;
                    $structure_data = json_decode($structure["structureData"], true);
                    return $structure_data;
                }
                else
                {
                    return array();
                }
            }
        }
        else
        {
            return array();
        }
        $structure_data = json_decode($structure["structureData"], true);
        switch($children_type)
        {
            case "integer":
                $children_type = '3';
                break;
            case "int":
                $children_type = '3';
                break;
            case "int[]":
                $children_type = '12';
                break;
            case "string":
                $children_type = '0';
                break;
            case "string[]":
                $children_type = '12';
                break;
            case 'long':
                $children_type = '11';
                break;
            case 'float':
                $children_type = '4';
                break;
            case 'double':
                $children_type = '5';
                break;
            case 'byte':
                $children_type = '9';
                break;
            case 'file':
                $children_type = '1';
                break;
            case 'date':
                $children_type = '6';
                break;
            case 'dateTime':
                $children_type = '7';
                break;
            case 'timestamp' :
                $children_type = '7';
                break;
            case 'boolean':
                $children_type = '8';
                break;
            case 'bool':
                $children_type = '8';
                break;
            case 'array':
                $children_type = '12';
                break;
            case 'short':
                $children_type = '10';
                break;
            case 'json':
                $children_type = '2';
                break;
            case 'object':
                $children_type = '13';
                break;
            case 'number':
                $children_type = '14';
                break;
        }
        if (isset($structure['structureGenericType']) && is_numeric($children_type))
        {
            $structure['structureGenericType']['paramType'] = $children_type;
            $structure_data[] = $structure['structureGenericType'];
        }
        elseif(is_numeric(strpos($children_type, "<")))
        {
            $content = array();
            preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $children_type, $content);
            $content[1] = str_replace(" ", "", strtolower($content[1]));
            $content[2] = str_replace(" ", "", strtolower($content[2]));

            if (isset($structure['structureGenericType']) && ($content[1] == "list" || $content[1] == "arraylist" || $content[1] == "List" || $content[1] == "ArrayList") )
            {
                $structure['structureGenericType']['paramType'] = "12";
                if (is_numeric(strpos($content[2], "<")))
                {
                    preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $children_type, $content);
                    $content[1] = str_replace(" ", "", strtolower($content[1]));
                    $content[2] = str_replace(" ", "", strtolower($content[2]));
                    $structure['structureGenericType']['childList'] = $this-> getGenericStructure($content[1], $content[2], $api_structure_list, $model_name_content);
                }
                else
                {
                    $structure_result = $this->getDataStructureByName($content[2], $model_name_content, $api_structure_list);
                    if ($structure_result)
                    {
                        // 处理LIST类型，即List<数据结构>类型
                        $child_structure_params = json_decode($structure_result["structureData"], true);
                        $structure['structureGenericType']['childList'] = $child_structure_params;
                    }
                    else
                    {
                        $structure['structureGenericType']['childList'] = array();
                    }
                }
                $structure_data[] = $structure['structureGenericType'];
            }
            elseif(isset($structure['structureGenericType']) && $this->getDataStructureByName($content[1], $model_name_content, $api_structure_list))
            {
                if (isset($structure['structureGenericType']['parentParamType']))
                {
                    $structure['structureGenericType']['paramType'] = str_replace("eo-swagger", $content[1], $structure['structureGenericType']['paramType']);
                    $structure['structureGenericType']['childList'] = $this-> getGenericStructure($structure['structureGenericType']['parentParamType'], $structure['structureGenericType']['paramType'], $api_structure_list, $model_name_content);
                    if ($structure['structureGenericType']['parentParamType'] == "list" || $structure['structureGenericType']['parentParamType'] == "arraylist" || $structure['structureGenericType']['parentParamType'] == "List" || $structure['structureGenericType']['parentParamType'] == "ArrayList")
                    {
                        $structure['structureGenericType']['paramType'] = '12';
                    }
                    else
                    {
                        $structure['structureGenericType']['paramType'] = '13';
                    }
                }
                else
                {
                    $structure['structureGenericType']['paramName'] = $api_structure_list[$content[1]]["structureDesc"];
                    $structure['structureGenericType']['paramType'] = '13';
                    $structure['structureGenericType']['childList'] = $this-> getGenericStructure($content[1], $content[2], $api_structure_list, $model_name_content);
                }
                $structure_data[] = $structure['structureGenericType'];
            }
        }
        elseif(isset($structure['structureGenericType']) && $this->getDataStructureByName($children_type, $model_name_content, $api_structure_list))
        {
            if (isset($structure['structureGenericType']['parentParamType']))
            {
                $structure['structureGenericType']['paramType'] = str_replace("eo-swagger", $children_type, $structure['structureGenericType']['paramType']);
                $structure['structureGenericType']['childList'] = $this-> getGenericStructure($structure['structureGenericType']['parentParamType'], $structure['structureGenericType']['paramType'], $api_structure_list, $model_name_content);
                if ($structure['structureGenericType']['parentParamType'] == "list" || $structure['structureGenericType']['parentParamType'] == "arraylist" || $structure['structureGenericType']['parentParamType'] == "List" || $structure['structureGenericType']['parentParamType'] == "ArrayList")
                {
                    $structure['structureGenericType']['paramType'] = '12';
                }
                else
                {
                    $structure['structureGenericType']['paramType'] = '13';
                }
            }
            else
            {
                $child_structure_params = json_decode($api_structure_list[$children_type]["structureData"], true);
                $structure['structureGenericType']['paramType'] = '13';
                $structure['structureGenericType']['paramName'] = $api_structure_list[$children_type]["structureDesc"];
                $structure['structureGenericType']['childList'] = $child_structure_params;
            }
            $structure_data[] = $structure['structureGenericType'];
        }
        return $structure_data;
    }

    /**
     * 处理泛型数据结构用于请求参数和返回参数
     * @param $parent_type
     * @param $children_type
     * @param $api_structure_list
     * @return array|mixed
     */
    private function getGenericStructureForResponse($parent_type, $children_type, &$api_structure_list)
    {
        if (isset($api_structure_list[$parent_type]))
        {
            $structure = $api_structure_list[$parent_type];
        }
        elseif ($parent_type == "list" || $parent_type == "arraylist" || $parent_type == "List" || $parent_type == "ArrayList" || $parent_type == "Collection" || $parent_type == "collection")
        {
            if(is_numeric(strpos($children_type, "<")))
            {
                preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $children_type, $content);
                $content[1] = str_replace(" ", "", strtolower($content[1]));
                $content[2] = str_replace(" ", "", strtolower($content[2]));
                return $this->getGenericStructureForResponse($content[1], $content[2], $api_structure_list);
            }
            else
            {
                if (isset($api_structure_list[$children_type]))
                {
                    $structure = $api_structure_list[$children_type];
                    $structure_data = json_decode($structure["structureData"], true);
                    return $structure_data;
                }
                else
                {
                    return array();
                }
            }
        }
        else
        {
            return array();
        }
        $structure_data = json_decode($structure["structureData"], true);
        switch($children_type)
        {
            case "integer":
                $children_type = '3';
                break;
            case "int":
                $children_type = '3';
                break;
            case "int[]":
                $children_type = '12';
                break;
            case "string":
                $children_type = '0';
                break;
            case "string[]":
                $children_type = '12';
                break;
            case 'long':
                $children_type = '11';
                break;
            case 'float':
                $children_type = '4';
                break;
            case 'double':
                $children_type = '5';
                break;
            case 'byte':
                $children_type = '9';
                break;
            case 'file':
                $children_type = '1';
                break;
            case 'date':
                $children_type = '6';
                break;
            case 'dateTime':
                $children_type = '7';
                break;
            case 'timestamp' :
                $children_type = '7';
                break;
            case 'boolean':
                $children_type = '8';
                break;
            case 'bool':
                $children_type = '8';
                break;
            case 'array':
                $children_type = '12';
                break;
            case 'short':
                $children_type = '10';
                break;
            case 'json':
                $children_type = '2';
                break;
            case 'object':
                $children_type = '13';
                break;
            case 'number':
                $children_type = '14';
                break;
        }
        if (isset($structure['structureGenericType']) && is_numeric($children_type))
        {
            $structure['structureGenericType']['paramType'] = $children_type;
            $structure_data[] = $structure['structureGenericType'];
        }
        elseif(is_numeric(strpos($children_type, "<")))
        {
            $content = array();
            preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $children_type, $content);
            $content[1] = str_replace(" ", "", strtolower($content[1]));
            $content[2] = str_replace(" ", "", strtolower($content[2]));
            if (isset($structure['structureGenericType']) && ($content[1] == "list" || $content[1] == "arraylist" || $content[1] == "List" || $content[1] == "ArrayList") )
            {
                $structure['structureGenericType']['paramType'] = "12";
                if (is_numeric(strpos($content[2], "<")))
                {
                    preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $children_type, $content);
                    $content[1] = str_replace(" ", "", strtolower($content[1]));
                    $content[2] = str_replace(" ", "", strtolower($content[2]));
                    $structure['structureGenericType']['childList'] = $this-> getGenericStructureForResponse($content[1], $content[2], $api_structure_list);
                }
                else
                {
                    if (isset($api_structure_list[$content[2]]))
                    {
                        // 处理LIST类型，即List<数据结构>类型
                        $child_structure_params = json_decode($api_structure_list[$content[2]]["structureData"], true);
                        $structure['structureGenericType']['childList'] = $child_structure_params;
                    }
                    else
                    {
                        $structure['structureGenericType']['childList'] = array();
                    }
                }
                $structure_data[] = $structure['structureGenericType'];
            }
            elseif(isset($structure['structureGenericType']) && isset($api_structure_list[$content[1]]))
            {
                if (isset($structure['structureGenericType']['parentParamType']))
                {
                    $structure['structureGenericType']['paramType'] = str_replace("eo-swagger", $content[1], $structure['structureGenericType']['paramType']);
                    $structure['structureGenericType']['childList'] = $this-> getGenericStructureForResponse($structure['structureGenericType']['parentParamType'], $structure['structureGenericType']['paramType'], $api_structure_list);
                    if ($structure['structureGenericType']['parentParamType'] == "list" || $structure['structureGenericType']['parentParamType'] == "arraylist" || $structure['structureGenericType']['parentParamType'] == "List" || $structure['structureGenericType']['parentParamType'] == "ArrayList")
                    {
                        $structure['structureGenericType']['paramType'] = '12';
                    }
                    else
                    {
                        $structure['structureGenericType']['paramType'] = '13';
                    }
                }
                else
                {
                    $structure['structureGenericType']['paramName'] = $api_structure_list[$content[1]]["structureDesc"];
                    $structure['structureGenericType']['paramType'] = '13';
                    $structure['structureGenericType']['childList'] = $this-> getGenericStructureForResponse($content[1], $content[2], $api_structure_list);
                }
                $structure_data[] = $structure['structureGenericType'];
            }
        }
        elseif(isset($structure['structureGenericType']) && isset($api_structure_list[$children_type]))
        {
            if (isset($structure['structureGenericType']['parentParamType']))
            {
                $structure['structureGenericType']['paramType'] = str_replace("eo-swagger", $children_type, $structure['structureGenericType']['paramType']);
                $structure['structureGenericType']['childList'] = $this-> getGenericStructureForResponse($structure['structureGenericType']['parentParamType'], $structure['structureGenericType']['paramType'], $api_structure_list);
                if ($structure['structureGenericType']['parentParamType'] == "list" || $structure['structureGenericType']['parentParamType'] == "arraylist" || $structure['structureGenericType']['parentParamType'] == "List" || $structure['structureGenericType']['parentParamType'] == "ArrayList")
                {
                    $structure['structureGenericType']['paramType'] = '12';
                }
                else
                {
                    $structure['structureGenericType']['paramType'] = '13';
                }
            }
            else
            {
                $child_structure_params = json_decode($api_structure_list[$children_type]["structureData"], true);
                $structure['structureGenericType']['paramType'] = '13';
                $structure['structureGenericType']['paramName'] = $api_structure_list[$children_type]["structureDesc"];
                $structure['structureGenericType']['childList'] = $child_structure_params;
            }
            $structure_data[] = $structure['structureGenericType'];
        }

        // 继承类的泛型
        if (isset($structure['structureExtendGenericType']) && is_numeric($children_type))
        {
            $structure['structureExtendGenericType']['paramType'] = $children_type;
            $structure_data[] = $structure['structureExtendGenericType'];
        }
        elseif(is_numeric(strpos($children_type, "<")))
        {
            $content = array();
            preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $children_type, $content);
            $content[1] = str_replace(" ", "", strtolower($content[1]));
            $content[2] = str_replace(" ", "", strtolower($content[2]));
            if (isset($structure['structureExtendGenericType']) && ($content[1] == "list" || $content[1] == "arraylist" || $content[1] == "List" || $content[1] == "ArrayList") )
            {
                $structure['structureExtendGenericType']['paramType'] = "12";
                if (is_numeric(strpos($content[2], "<")))
                {
                    preg_match("/([\\s\\S]*?)<([\\s\\S]*?)>/", $children_type, $content);
                    $content[1] = str_replace(" ", "", strtolower($content[1]));
                    $content[2] = str_replace(" ", "", strtolower($content[2]));
                    $structure['structureExtendGenericType']['childList'] = $this-> getGenericStructureForResponse($content[1], $content[2], $api_structure_list);
                }
                else
                {
                    if (isset($api_structure_list[$content[2]]))
                    {
                        // 处理LIST类型，即List<数据结构>类型
                        $child_structure_params = json_decode($api_structure_list[$content[2]]["structureData"], true);
                        $structure['structureExtendGenericType']['childList'] = $child_structure_params;
                    }
                    else
                    {
                        $structure['structureExtendGenericType']['childList'] = array();
                    }
                }
                $structure_data[] = $structure['structureExtendGenericType'];
            }
            elseif(isset($structure['structureExtendGenericType']) && isset($api_structure_list[$content[1]]))
            {
                if (isset($structure['structureExtendGenericType']['parentParamType']))
                {
                    $structure['structureExtendGenericType']['paramType'] = str_replace("eo-swagger", $content[1], $structure['structureExtendGenericType']['paramType']);
                    $structure['structureExtendGenericType']['childList'] = $this-> getGenericStructureForResponse($structure['structureExtendGenericType']['parentParamType'], $structure['structureExtendGenericType']['paramType'], $api_structure_list);
                    if ($structure['structureExtendGenericType']['parentParamType'] == "list" || $structure['structureExtendGenericType']['parentParamType'] == "arraylist" || $structure['structureExtendGenericType']['parentParamType'] == "List" || $structure['structureExtendGenericType']['parentParamType'] == "ArrayList")
                    {
                        $structure['structureExtendGenericType']['paramType'] = '12';
                    }
                    else
                    {
                        $structure['structureExtendGenericType']['paramType'] = '13';
                    }
                }
                else
                {
                    $structure['structureExtendGenericType']['paramName'] = $api_structure_list[$content[1]]["structureDesc"];
                    $structure['structureExtendGenericType']['paramType'] = '13';
                    $structure['structureExtendGenericType']['childList'] = $this-> getGenericStructureForResponse($content[1], $content[2], $api_structure_list);
                }
                $structure_data[] = $structure['structureExtendGenericType'];
            }
        }
        elseif(isset($structure['structureExtendGenericType']) && isset($api_structure_list[$children_type]))
        {
            if (isset($structure['structureExtendGenericType']['parentParamType']))
            {
                $structure['structureExtendGenericType']['paramType'] = str_replace("eo-swagger", $children_type, $structure['structureExtendGenericType']['paramType']);
                $structure['structureExtendGenericType']['childList'] = $this-> getGenericStructureForResponse($structure['structureExtendGenericType']['parentParamType'], $structure['structureExtendGenericType']['paramType'], $api_structure_list);
                if ($structure['structureExtendGenericType']['parentParamType'] == "list" || $structure['structureExtendGenericType']['parentParamType'] == "arraylist" || $structure['structureExtendGenericType']['parentParamType'] == "List" || $structure['structureExtendGenericType']['parentParamType'] == "ArrayList")
                {
                    $structure['structureExtendGenericType']['paramType'] = '12';
                }
                else
                {
                    $structure['structureExtendGenericType']['paramType'] = '13';
                }
            }
            else
            {
                $child_structure_params = json_decode($api_structure_list[$children_type]["structureData"], true);
                $structure['structureExtendGenericType']['paramType'] = '13';
                $structure['structureExtendGenericType']['paramName'] = $api_structure_list[$children_type]["structureDesc"];
                $structure['structureExtendGenericType']['childList'] = $child_structure_params;
            }
            $structure_data[] = $structure['structureExtendGenericType'];
        }
        return $structure_data;
    }
}