<?php

namespace Encomage\Catalog\Block\Product\View;


class QuestionAboutSize extends \Encomage\Catalog\Block\Product\View\Qty
{
    public function isShowQuestion()
    {
        return (bool)$this->getProduct()->getAskAboutShoeSize();
    }
}