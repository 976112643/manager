<?php 
namespace Common\Help;
/**
 * 文件转换html
 * @author Administrator
 */
class ViewHelp extends BaseHelp
{
    /** 需要操作的文件*/
    private $file;
    /** 操作后需要保存的文件路径*/
    private $save_path;
    
    /** 文件名带扩展名*/
    private $file_basename;
    /** 文件名不带扩展名*/
    private $file_filename;
    /** 文件路径*/
    private $file_path;
    /** 文件扩展名*/
    private $file_extension;
    /**
     * 构造函数
     * @param [type] $file_path [需要处理的文件名]
     * @param [type] $save_path [需要保存的文件名]
     */
    public function __construct($file_path,$save_path)
    {
        $this->file         = $file_path ? $file_path : '1.txt';
        $this->save_path    = $save_path ? $save_path : 'Uploads/Office_cache/';
        $this->cutFileInfo();
    }
    /**
     * 根据文件生成对应文件名的html
     */
    public function create()
    {
        /** 处理文件路径*/
        if(!$this->mkDir()){
            throw_exception('保存的路径不存在，请手动创建');
        }
        /**　处理文件html*/
        $info = $this->switchExtensionObject();
        /** 生成同名html*/
        if($info['html'] && $info['exc'] != 'pdf'){
            $file_name = $this->file_filename . '.html';
            $fp = fopen ( $this->save_path . $file_name, "w+" );
            if (flock ( $fp, LOCK_EX )) {
                fwrite ( $fp, $info['html']);
                flock ( $fp, LOCK_UN );
            } else {
                return false;
            }
            fclose ( $fp );
            return true;
        }else{
            if($info['exc'] == 'pdf'){
                copy($this->file, $this->getCacheHtml());
                return true;
            }
            return false;
        }
    }
    /**
     * 查看文件
     */
    public function views()
    {
        if($this->file_extension == 'pdf'){
            redirect(U($this->file,array(),false,true));
        }
        ob_clean();
        echo file_get_contents($this->getCacheHtml());
    }
    /**
     * 返回生成后的文件路径
     */
    public function getCacheHtml()
    {
        if($this->file_extension == 'pdf'){
            return $this->save_path . $this->file_basename;
        }
        return $this->save_path . $this->file_filename . '.html';
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
     * 返回文件名
     */
    public function getFile()
    {
        return $this->file;
    }
    /**
     * 返回文件保存路径
     */
    public function getFileSavePath()
    {
        return $this->save_path;
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
    /**
     * 根据文件后缀，调用不同实例，实现生成html
     */
    private function switchExtensionObject()
    {
        switch ($this->file_extension){
            case 'doc':
            case 'docx':
                $html = $this->createOfficeHtml();
                break;
            case 'xls':
            case 'xlsx':
                $html = $this->createOfficeHtml();
                break;
            case 'ppt':
            case 'pptx':
                $html = $this->createOfficeHtml();
                break;
            case 'pdf':
                $html = $this->createPdfHtml();
                break;
            default :
                $html = '';
                break;
        }
        return array('exc'=>$this->file_extension,'html'=>$html);
    }
    /**
     * office文件处理业务
     */
    private function createOfficeHtml()
    {
        $url = urlencode(U($this->file,array(),false,true));
        //$url = 'http%3A%2F%2Ftest.cnsunrun.com%2Fjiushengtongchi%2FPublic%2Fword1.pptx';
        $base_url = 'http://view.officeapps.live.com/op/view.aspx?src=';
        return $this->curlInfo($base_url . $url);
    }
    /**
     * pdf文件处理业务
     */
    private function createPdfHtml()
    {
        return $this->file;
    }
    /**
     * 判断是否是路径
     */
    private function mkDir()
    {
        $dir = $this->save_path;
        $mode = 0777;
        if (is_dir($dir) || @mkdir($dir, $mode, true)) {
            return true;
        }
        if (! mk_dir(dirname($dir), $mode)) {
            return false;
        }
    }
    /**
     * 发送curl,get请求
     * @param string $url 请求路径
     */
    private function curlInfo($url)
    {
        $UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);  //0表示不输出Header，1表示输出
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($curl); 
        curl_close($curl);
        return $data;
    }
}
?>