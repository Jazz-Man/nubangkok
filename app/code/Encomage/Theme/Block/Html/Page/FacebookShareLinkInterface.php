<?php

namespace Encomage\Theme\Block\Html\Page;

interface FacebookShareLinkInterface
{
    /**
     * @return string
     */
    public function getLink();

    /**
     * @return string
     */
    public function getCssClass();

    /**
     * @param string $cssClass
     * @return mixed
     */
    public function setCssClass(string $cssClass);
}