<?php
namespace Encomage\Stories\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Escaper;

class StoryActions extends Column
{
    const URL_PATH_DELETE = 'stories/grid/delete';
    const URL_PATH_EDIT = 'stories/grid/edit';

    private $urlBuilder;

    private $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components,
        array $data
    )
    {
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'entity_id' => $item['entity_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete %1', $item['entity_id']),
                                'message' => __('Are you sure you want to delete a %1 record?', $item['entity_id'])
                            ]
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}