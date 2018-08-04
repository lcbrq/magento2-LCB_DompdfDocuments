<?php

/**
 * Source model for invoice template select
 * 
 * @category   LCB
 * @package    LCB_Invoice_DompdfDocuments
 * @author     Tomasz Gregorczyk <tom@lcbrq.com>
 * 
 */

namespace LCB\DompdfDocuments\Model\Config\Source\Templates;

use Magento\Framework\App\Utility\Files;

class Invoice implements \Magento\Framework\Option\ArrayInterface
{
    
    /**
     * @var Files
     */
    private $filesUtils;

    /**
     * @param Files $filesUtils
     */
    public function __construct(
        Files $filesUtils
    ) {
        $this->filesUtils = $filesUtils;
    }
    
    /**
     * Get list of possible templates
     * 
     * @return array
     */
    public function toOptionArray()
    {
        
        $templates = [
            ['value' =>  false, 'label' => __('No')]
        ];
        
        foreach ($this->filesUtils->getPhtmlFiles(true, false) as $key => $template) {

            $moduleName = $template[2];

            if ($moduleName !== "LCB_DompdfDocuments") {
                continue;
            }

            preg_match('/invoice\/(.*?).phtml/', $template[3], $match);

            $fileName = $match[1];

            $templates[$fileName] = ['value' => $fileName, 'label' => ucfirst($fileName) . ' ' . __("Template")];
        }

        return $templates;
    }

}