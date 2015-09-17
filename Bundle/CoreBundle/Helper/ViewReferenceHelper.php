<?php

namespace Victoire\Bundle\CoreBundle\Helper;

use Victoire\Bundle\BusinessPageBundle\Entity\BusinessPage;
use Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage;
use Victoire\Bundle\CoreBundle\Entity\View;
use Victoire\Bundle\CoreBundle\Entity\WebViewInterface;

/**
 * ref: victoire_core.helper.view_reference_helper
 * Class ViewReferenceHelper
 * @package Victoire\CoreBundle\Helper
 */
class ViewReferenceHelper
{

    /**
     * @param View $view
     * @param $entity
     * @return string
     */
    public function getViewReferenceId(View $view, $entity = null)
    {
        $id = $view->getId();
        if ($view instanceof BusinessPage) {
            $entity = $view->getBusinessEntity();
            if ($view instanceof VirtualBusinessPage) {
                $id = $view->getTemplate()->getId();
            }
        } else if (!$view instanceof WebViewInterface) {
            return $view->getId();
        }

        $refId = sprintf('ref_%s', $id);
        if ($entity) {
            $refId .= '_'.$entity->getId();
        }

        return $refId;
    }


    /**
     * @param \SimpleXMLElement $rootNode
     * @param array             $viewReference
     */
    public function removeViewReference(\SimpleXMLElement $rootNode, array $viewReference)
    {
        //Clean by searching by id
        $regex = sprintf("//viewReference[@id='%s']", $viewReference['id']);

        //Clean by searching by url
        if (isset($viewReference['url'])) {
            $regex .= sprintf("| //viewReference[@url='%s']", $viewReference['url']);
        }

        foreach ($rootNode->xpath($regex) as $item) {
            unset($item[0]);
        }
    }

    /**
     * @return array
     */
    public function convertXmlCacheToArray($xml)
    {

        $cachedArray = json_decode(json_encode((array) $xml), TRUE);
        $viewsReferences = [];

        foreach ($cachedArray['viewReference'] as $cachedViewReference) {
            $viewReference['id']              = !empty($cachedViewReference['@attributes']['id']) ? $cachedViewReference['@attributes']['id'] : null ;
            $viewReference['locale']          = !empty($cachedViewReference['@attributes']['locale']) ? $cachedViewReference['@attributes']['locale'] : null ;
            $viewReference['entityId']        = !empty($cachedViewReference['@attributes']['entityId']) ? $cachedViewReference['@attributes']['entityId'] : null ;
            $viewReference['entityNamespace'] = !empty($cachedViewReference['@attributes']['entityNamespace']) ? $cachedViewReference['@attributes']['entityNamespace'] : null ;
            $viewReference['url']             = !empty($cachedViewReference['@attributes']['url']) ? $cachedViewReference['@attributes']['url'] : null ;
            $viewReference['viewId']          = !empty($cachedViewReference['@attributes']['viewId']) ? $cachedViewReference['@attributes']['viewId'] : null ;
            $viewReference['viewNamespace']   = !empty($cachedViewReference['@attributes']['viewNamespace']) ? $cachedViewReference['@attributes']['viewNamespace'] : null ;
            $viewReference['patternId']       = !empty($cachedViewReference['@attributes']['patternId']) ? $cachedViewReference['@attributes']['patternId'] : null ;
            $viewReference['name']            = !empty($cachedViewReference['@attributes']['name']) ? $cachedViewReference['@attributes']['name'] : null ;

            $viewsReferences[] = $viewReference;
        }

        return $viewsReferences;
    }

    /**
     *
     * @param $viewsReferences
     */
    public function cleanVirtualViews($viewsReferences)
    {
        $urls = [];

        foreach ($viewsReferences as $key => $viewReference) {
            // If viewReference is a persisted page, we want to clean virtual BEPs
            if (!empty($viewReference['type']) && $viewReference['type'] == 'business_page') {
                $viewsReferences = array_filter($viewsReferences, function ($_viewReference) use ($viewReference) {

                        // If my current viewReference already exists as a virtualBusinessPage, I remove it from viewReferences
                        $shouldRemove = !($_viewReference['viewNamespace'] == 'Victoire\Bundle\BusinessPageBundle\Entity\VirtualBusinessPage'
                            && !empty($_viewReference['entityNamespace']) && $_viewReference['entityNamespace'] == $viewReference['entityNamespace']
                            && !empty($_viewReference['entityId']) && $_viewReference['entityId'] == $viewReference['entityId']
                            && !empty($_viewReference['patternId']) && $_viewReference['patternId'] == $viewReference['patternId']);
                        
                        return $shouldRemove;

                    });
            }
            // while viewReference url is found in viewreferences, increment the url slug to be unique
            $url = $viewReference['url'];
            $i = 1;
            while (in_array($url, $urls)) {
                $url = $viewReference['url'] . "-" . $i;
                $i++;
            }
            $viewsReferences[$key]['url'] = $url;
            $urls[] = $url;

        }

        return $viewsReferences;

    }

}