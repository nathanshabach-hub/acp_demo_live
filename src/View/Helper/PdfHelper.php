<?php
namespace App\View\Helper;

use Cake\View\Helper;
//require_once(BASE_PATH . DS . 'vendor' . DS . 'PHPExcel/PHPExcel.php');
class PdfHelper  extends Helper                                  //2
{
    public $core;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        // TCPDF must be loaded separately if needed
        // $this->core = new \TCPDF();
    }
     
}
?>