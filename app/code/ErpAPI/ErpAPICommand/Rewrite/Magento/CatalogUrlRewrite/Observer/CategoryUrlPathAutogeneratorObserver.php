<?php

namespace ErpAPI\ErpAPICommand\Rewrite\Magento\CatalogUrlRewrite\Observer;

use Ausi\SlugGenerator\SlugGenerator;
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Observer\CategoryUrlPathAutogeneratorObserver as CategoryUrlPathAutogeneratorObserverAlias;
use Magento\Framework\Event\Observer;
use ReflectionObject;

/**
 * Class CategoryUrlPathAutogeneratorObserver.
 */
class CategoryUrlPathAutogeneratorObserver extends CategoryUrlPathAutogeneratorObserverAlias
{

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \ReflectionException
     */
    public function execute(Observer $observer)
    {

        /** @var Category $category */
        $category = $observer->getEvent()->getCategory();
        $useDefaultAttribute = !$category->isObjectNew() && !empty($category->getData('use_default')['url_key']);


        if (!$useDefaultAttribute && false !== $category->getUrlKey()) {

            $resultUrlKey = $this->categoryUrlPathGenerator->getUrlKey($category);

            if (empty($resultUrlKey)){
                $resultUrlKey = $this->slugGenerator($category);
            }


            $this->_updateUrlKey($category, $resultUrlKey);
        } elseif ($useDefaultAttribute) {

            $orig_name = $category->getOrigData('name');

            $resultUrlKey = $category->formatUrlKey($orig_name);

            if (empty($resultUrlKey)){

                $resultUrlKey = $this->slugGenerator($category);
            }


            $this->_updateUrlKey($category, $resultUrlKey);
            $category->setUrlKey(null)->setUrlPath(null);
        }
    }

    /**
     * @param Category $category
     * @param string $urlKey
     *
     * @throws \ReflectionException
     */
    private function _updateUrlKey($category, $urlKey){

        $reflector = new ReflectionObject($this);
        $method = $reflector->getMethod('updateUrlKey');

        $method->setAccessible(true);

        $method->invoke($this,$category,$urlKey);
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return string
     */
    private function slugGenerator(Category $category)
    {
        $generator = new SlugGenerator;

        $orig_name = $category->getOrigData('name');

        return $generator->generate($orig_name);

    }
}
