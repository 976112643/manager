<?php
namespace Common\Plugin;
/**
 * 敏感词管理类
 */
class Sensitive{
	/** 敏感词源文件 ,目前只支持txt文件*/
	private $file;
	/** 文件名带扩展名*/
    private $file_basename;
    /** 文件名不带扩展名*/
    private $file_filename;
    /** 文件路径*/
    private $file_path;
    /** 文件扩展名*/
    private $file_extension;
	/**
	 * 构造方法
	 * @param [type] $file [文件路径]
	 */
	public function __construct($file = '')
	{
		if($file){
			$this->file = $file;
			/** 获取文件信息 */
			$this->cutFileInfo();
			/** 判断是否支持读取的格式 */
			$this->checkExtend();
		}
	}
    public function test()
    {
        $bad_words = get_no_del('sensitive');

        $sensitive = new Sensitive();
        $search_str = '我要打倒共产主义';
        $bad_words = array_column($bad_words,'name');
        $str = $sensitive->filterSensitive($search_str,$bad_words);
        dump($str);
        exit;
    }
    /**
     * 返回文件名带后缀
     */
    public function getFileBaseName()
    {
        return $this->file_basename;
    }
    /**
     * 返回文件名不带后缀
     */
    public function getFileName()
    {
        return $this->file_filename;
    }
    /**
     * 返回文件名后缀
     */
    public function getFileExtend()
    {
        return $this->file_extension;
    }
    /**
     * 返回文件路径
     */
    public function getFile()
    {
        return $this->file;
    }
    /**
     * 读取文件内容1，返回数据格式
     * $contetn = '1
     * 2
     * 3
     * 4
     * 5'
     */
    public function getFileContentByLine()
    {
        $arr = $this->getFileContentBySegmentation("\r\n");
        return $arr;
    }
    /**
     * 读取文件内容2，返回数组格式
     * $content = '1|2|3|4'
     */
    public function getFileContentByVertical()
    {
        $arr = $this->getFileContentBySegmentation("|");
        return $arr;
    }
    /**
     * 读取文件内容3，返回数组格式
     * $content = '自定义字符分割'
     */
    public function getFileContentBySegmentation($segmentation)
    {
    	$content = $this->getFileContent();
    	$arr = explode("$segmentation", $content);//转换成数组
        $arr = $this->removeFileContent($arr);
        return $arr;
    }
    /**
     * 字符串去重
     * $str = 'aa,bb,cc,aa,bb'
     */
    public function removeDitto($str)
    {
    	return array_unique(explode(',', $str));
    }
    /**
     * 过滤敏感词
     */
    public function filterSensitive($search_str,$bad_words)
    {
    	$badword = array_combine($bad_words,array_fill(0,count($bad_words),'*'));
    	$search_str = mb_convert_encoding($search_str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
    	$str = strtr($search_str, $badword);
    	return $str;
    }
    /**
     * 读取文件内容
     * @return [string] [内容字符串]
     */
    private function getFileContent()
    {
    	$str = file_get_contents($this->file);//将整个文件内容读入到一个字符串中
        $str_encoding = mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');//转换字符集（编码）
    	return $str_encoding;
    }
    /**
     * 过滤文件内容
     */
    private function removeFileContent( array $arr)
    {
    	//去除值中的空格
        foreach ($arr as &$row) {
        	if(!$row) unset($row);
            $row = trim($row);
        }
        unset($row);
        return $arr;
    }
    /**
     * 判断支持的文件类型
     * @return [type] [description]
     */
    private function checkExtend()
    {
    	if( $this->file_extension != 'txt'){
    		throw new \Exception("文件不是txt格式,请检查文件", 1);
    	}
    }
	/**
     * 处理文件信息
     */
    private function cutFileInfo()
    {
        if(is_file($this->file)){
            $file = pathinfo($this->file);
            $this->file_basename    =   $file['basename'];
            $this->file_filename    =   $file['filename'];
            $this->file_path        =   $file['dirname'];
            $this->file_extension   =   strtolower($file['extension']);
        }
    }
}
?>