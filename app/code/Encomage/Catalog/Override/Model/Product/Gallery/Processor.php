<?php
/**
 * Default (Template) Project
 *
 * @category Default (Template) Project-module
 * @package  Encomage
 * @author   Encomage <hello@encomage.com>
 * @license  OSL https://opensource.org/licenses/OSL-3.0
 * @link     http://encomage.com
 */

namespace Encomage\Catalog\Override\Model\Product\Gallery;

class Processor extends \Magento\Catalog\Model\Product\Gallery\Processor
{
    public function clearMediaAttribute(\Magento\Catalog\Model\Product $product, $mediaAttribute)
    {
        $mediaAttributeCodes = $this->mediaConfig->getMediaAttributeCodes();

        if (is_array($mediaAttribute)) {
            foreach ($mediaAttribute as $attribute) {
                if (in_array($attribute, $mediaAttributeCodes)) {
                   // $product->setData($attribute, 'no_selection');
                }
            }
        } elseif (in_array($mediaAttribute, $mediaAttributeCodes)) {
            //$product->setData($mediaAttribute, 'no_selection');
        }

        return $this;
    }

}