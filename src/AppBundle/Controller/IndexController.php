<?php

namespace AppBundle\Controller;


use AppBundle\Config\Config;
use AppBundle\Oauth2\Provider\ShopifyProvider;
use AppBundle\Oauth2\ShopifyOauth2Provider;
use AppBundle\Service\Syncer;
use AppBundle\Store\SeoShopStore;
use AppBundle\Store\ShopifyStore;
use AppBundle\Store\StoreFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use sandeepshetty\shopify_api;

class IndexController extends Controller
{
    /**
     * @Route("/", name="main")
     */
    public function indexAction(Request $request)
    {
        $shopifyConfig = $this->get('session')->get(Config::STORE_CONFIG_SHOPIFY_SESSION_KEY);
        $seoShopConfig = $this->get('session')->get(Config::STORE_CONFIG_SEOSHOP_SESSION_KEY);
        $isActiveShopifyToken = false;
        $isActiveSeoShopToken = false;
        if (isset($shopifyConfig[Config::STORE_CONFIG_SECTION][ShopifyStore::CONFIG_ACCESS_TOKEN])
            && !empty($shopifyConfig[Config::STORE_CONFIG_SECTION][ShopifyStore::CONFIG_ACCESS_TOKEN])){
            $isActiveShopifyToken = true;
        }

        if (isset($seoShopConfig[Config::STORE_CONFIG_SECTION][SeoShopStore::CONFIG_ACCESS_TOKEN])
            && !empty($seoShopConfig[Config::STORE_CONFIG_SECTION][SeoShopStore::CONFIG_ACCESS_TOKEN])){
            $isActiveSeoShopToken = true;
        }
        $params = [
            'isActiveShopifyToken' => $isActiveShopifyToken,
            'isActiveSeoShopToken' => $isActiveSeoShopToken,
            'shopifyStoreType' => ShopifyStore::STORE_TYPE,
            'seoshopStoreType' => SeoShopStore::STORE_TYPE
        ];
        return $this->render("AppBundle:Index:index.html.twig",$params);



    }

    /**
     * @Route("/sync/{storeTypeFrom}/{storeTypeTo}", name="sync")
     */
    public function syncAction($storeTypeFrom, $storeTypeTo, Request $request)
    {
        $result = [];
        $session = $this->get('session');
        $storeFromConfigKey = Config::getConfigKeyByStoreType($storeTypeFrom);
        $storeToConfigKey = Config::getConfigKeyByStoreType($storeTypeTo);
        if ($storeFromConfigKey && $storeToConfigKey){
            $storeFromConfig = $session->get($storeFromConfigKey);
            $storeToConfig = $session->get($storeToConfigKey);
            if ($storeFromConfig && $storeToConfig){
                /** @var Syncer $syncer */
                $syncer = $this->get('app.syncer');
                $result = $syncer->syncData($storeFromConfig,$storeToConfig, true);
            }
        }
        return $this->render("AppBundle:Index:sync.html.twig",['result'=>$result]);
    }


}
