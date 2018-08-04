<?php
/**
 * DISCLAIMER
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   LCB
 * @package    LCB_Invoice_DompdfDocuments
 * @author     Tomasz Gregorczyk <tom@lcbrq.com>
 */

namespace LCB\DompdfDocuments\Plugin\Sales\Model\Order\Pdf;

use \Dompdf\Dompdf;

class Invoice
{
    
    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;
    
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
     protected $emulation;
     
    /**
     * @var \Magento\Framework\App\AreaList
     */
     protected $areaList;
     
    /**
     * Invoice contructor
     * 
     * @param \Magento\Framework\View\Layout $layout
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Magento\Framework\App\AreaList $areaList
     */
    public function __construct(
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\App\AreaList $areaList
    )
    {
        $this->layout = $layout;
        $this->scopeConfig = $scopeConfig;
        $this->areaList = $areaList;
        $this->emulation = $emulation;
    }

    /**
     * Return PDF document
     *
     * @param \Magento\Sales\Model\Order\Pdf\Invoice $subject
     * @param callable $proceed
     * @param array|Collection $invoices
     * @return \Zend_Pdf|\Dompdf\Dompdf
     */
    public function aroundGetPdf(\Magento\Sales\Model\Order\Pdf\Invoice $subject, callable $proceed, $invoices = [])
    {
        if (!$template = $this->scopeConfig->getValue('sales_pdf/invoice/dompdf_document')) {
           return $proceed($invoices);
        }

        $html = '';
        $name = [];
        
        foreach ($invoices as $invoice) {

            $block = $this->layout->createBlock('LCB\DompdfDocuments\Block\Invoice');
            
            if ($invoice->getStoreId() != \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                $areaObject = $this->areaList->getArea(\Magento\Framework\App\Area::AREA_FRONTEND);
                $areaObject->load(\Magento\Framework\App\Area::PART_TRANSLATE);
                $block->setArea('frontend');
                $this->emulation->startEnvironmentEmulation($invoice->getStoreId(), 'frontend', true);
            }

            $name[] = $invoice->getIncrementId();
            $html .= $block->setInvoice($invoice)->setTemplate("LCB_DompdfDocuments::invoice/$template.phtml")->toHtml();

            if ($invoice->getStoreId() != \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                $this->emulation->stopEnvironmentEmulation();
            }
            
        }

        $domPdf = new Dompdf();
        $domPdf->loadHtml($html);
        $domPdf->setPaper('A4', 'portrait');
        $domPdf->render();
        $domPdf->stream(implode("_", $name));

        return $domPdf;
    }
}