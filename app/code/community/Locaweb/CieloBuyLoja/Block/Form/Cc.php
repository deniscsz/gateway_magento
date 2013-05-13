<?php
class Locaweb_CieloBuyLoja_Block_Form_Cc extends Mage_Payment_Block_Form_Ccsave
{
  protected function _construct()
  {
      parent::_construct();
      $this->setTemplate('abstract/form/cc.phtml');
  }
  
    public function getProductFinalPrice()
    {
        $price = preg_replace("/^R\\$[ ]*/i", "", $this->getRequest()->getParam('price'));
        $price = str_replace(".", "", $price);
        $price = str_replace(",", ".", $price);
    
        if ($price > 0) {
            $this->_showScripts = false;
        } else {
    
            $productId = $this->getRequest()->getParam('id');
            if (!Mage::registry('product') && $productId) {
                $product = Mage::getModel('catalog/product')->load($productId);
                Mage::register('product', $product);
            } else {
                $product = Mage::registry('product');
            }
    
            if ($product) {
                $price = $product->getFinalPrice();
            } else {
                $price = 0;
            }
    
        }
    
        return $price;
    
    }
  
    public function getParcelas()
    {
        $max_parcelas = Mage::getStoreConfig('payment/cielobuyloja/nummax');
        $valor_minimo = Mage::getStoreConfig('payment/cielobuyloja/valormin');
        $parcelas_sem_juros = Mage::getStoreConfig('payment/cielobuyloja/nummaxnojuros');
        $taxa_juros = Mage::getStoreConfig('payment/cielobuyloja/juros');
        
        $total = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
        if(!$total) {
            $total = $this->getProductFinalPrice();
        }
        
        $total_com_juros = $total;
        
        $n = floor($total / $valor_minimo);
        if($n > $max_parcelas) {
            $n = $max_parcelas;
        } elseif($n < 1){
            $n = 1;
        }
        
        $parcelas = array();
        for ($i=0; $i < $n; $i++) {
            $total_com_juros *= 1 + ($taxa_juros / 100);
        
            if($i+1 == 1){
                $label = '1x sem juros de '.Mage::helper('checkout')->formatPrice($total);
            }elseif($taxa_juros > 0 && $i+1 > $parcelas_sem_juros){
                $label = ($i+1).'x com juros de '.Mage::helper('checkout')->formatPrice($total_com_juros/($i + 1));
            }else{
                $label = ($i+1).'x sem juros de '.Mage::helper('checkout')->formatPrice($total/($i + 1));
            }
            $parcelas[] = array('parcela' => $i+1, 'label' => $label);
        }
        return $parcelas;
    }
}
?>
