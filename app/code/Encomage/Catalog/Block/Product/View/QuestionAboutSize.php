<?php

namespace Encomage\Catalog\Block\Product\View;


/**
 * Class QuestionAboutSize
 *
 * @package Encomage\Catalog\Block\Product\View
 */
class QuestionAboutSize extends Qty
{

    /**
     * @return bool
     */
    public function isShowQuestion()
    {
        return (bool)$this->getProduct()->getAskAboutShoeSize();
    }
}