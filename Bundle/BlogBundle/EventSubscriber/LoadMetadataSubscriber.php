<?php
namespace Victoire\Bundle\BlogBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Victoire\Bundle\BlogBundle\Entity\Article;

/**
 * This class listen Mission Entity changes, update its status and create or update ScheduledEvent.
 */
class LoadMetadataSubscriber implements EventSubscriber
{
    /**
     * bind to LoadClassMetadata method
     *
     * @return array The list of events
     */
    public function getSubscribedEvents()
    {
        return array(
            'loadClassMetadata',
        );
    }

    /**
     * Insert DiscriminatorMap in the class metadatas
     * @param string $class
     */
    public function loadClassMetadata($class)
    {
        if ($class->getClassMetadata()->name === 'Victoire\Bundle\PageBundle\Entity\Page') {
            $class->getClassMetadata()->discriminatorMap[Article::TYPE] = 'Victoire\Bundle\BlogBundle\Entity\Article';
        }
    }
}
