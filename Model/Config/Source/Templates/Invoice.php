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
            if($template[2] !== "LCB_DompdfDocuments"){
                continue;
            }
            preg_match('/invoice\/(.*?).phtml/', $template[3], $match);
            $templates[] = ['value' => $match[1], 'label' => ucfirst($match[1]) . ' ' . __("Template")];
        }
        
        return $templates;
        
    }

}