<?php
namespace Magento\Aquila\Model;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
class AttributeRest implements \Magento\Aquila\Api\AttributeRestInterface
{
   
    /**
     * @var AttributeSetCollectionFactory
     */
    protected $attributeSetCollectionFactory; 

    /**
     * @var AttributeGroupRepositoryInterface
     */
    protected $attributeGroupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;


    public function __construct(
        
        AttributeSetCollectionFactory $attributeSetCollectionFactory,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeCollectionFactory $attributeCollectionFactory
    ) {
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Retrieve list of attribute sets.
     *
     * @return string[]
     */
    public function getList()
    {
        $attributeSetList = [];

        // Retrieve all attribute sets
        $attributeSetCollection = $this->attributeSetCollectionFactory->create();

        // Filter attribute sets by entity_type_id = 4 (product)
        $attributeSetCollection->setEntityTypeFilter(4);
        
        // Loop through each attribute set
        foreach ($attributeSetCollection as $attributeSet) {
            $attributeSetData = $attributeSet->getData();

            // Build search criteria to retrieve attribute groups
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('attribute_set_id', $attributeSet->getAttributeSetId())
                ->create();

            // Load associated attribute groups
            $attributeGroups = $this->attributeGroupRepository->getList($searchCriteria)->getItems();

            // Extract attribute group names
            $groupNames = [];
            foreach ($attributeGroups as $attributeGroup) {
                $attributeGroupName = $attributeGroup->getAttributeGroupName();
                $allAttributeCollection = $this->attributeCollectionFactory->create()->setAttributeGroupFilter($attributeGroup->getId())->addVisibleFilter()->load();
                
                $attributeList = [];
                foreach ($allAttributeCollection->getItems() as $attribute) {
                    $attributeList[] = $attribute->getData("attribute_code");
                }
                $groupNames[$attributeGroup->getAttributeGroupName()] = $attributeList;
                
            }

            // Add associated groups to attribute set data
            $attributeSetData['associated_groups'] = $groupNames;

            // Add the attribute set data to the list
            $attributeSetList[] = $attributeSetData;
        }

        return $attributeSetList;
    }

}
