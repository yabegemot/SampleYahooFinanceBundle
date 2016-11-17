<?php

namespace Sample\UserBundle\Subscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;

/**
 * Description of TablePrefixSubscriber
 *
 * @author  Yuriy Arutyunov <yabegemot@gmail.com>
 */
class TablePrefixSubscriber implements \Doctrine\Common\EventSubscriber {

    protected $prefix = '';

    public function __construct($prefix) {
        $this->prefix = (string) $prefix;
    }

    public function getSubscribedEvents() {
        return array('loadClassMetadata');
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $args) {
        $classMetadata = $args->getClassMetadata();

        if ($classMetadata->isInheritanceTypeSingleTable() && !$classMetadata->isRootEntity()) {
            return;
        }

        if (FALSE !== strpos($classMetadata->namespace, 'UserBundle')) {
            $classMetadata->setPrimaryTable(array('name' => $this->prefix . $classMetadata->getTableName()));

            foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
                if ($mapping['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY && isset($classMetadata->associationMappings[$fieldName]['joinTable']['name'])) {
                    $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
                    $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
                }
            }
        }
    }

}
