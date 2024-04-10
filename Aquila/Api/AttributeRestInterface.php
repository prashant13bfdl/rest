<?php
namespace Magento\Aquila\Api;

interface AttributeRestInterface
{
    /**
     * Retrieve list of attribute sets.
     *
     * @return string[]
     */
    public function getList();
}
