<?php

namespace AppBundle\Controller;


use AppBundle\Config\Config;
use AppBundle\Oauth2\ShopifyOauth2Provider;
use AppBundle\Store\SeoShopStore;
use AppBundle\Store\ShopifyStore;
use AppBundle\Store\StoreFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use sandeepshetty\shopify_api;

class OauthController extends Controller
{
    /**
     * @Route("/init_shopify", name="init_shopify")
     */
    public function initShopifyAction(Request $request)
    {


        $shopifyConfig = $this->get('session')->get(Config::STORE_CONFIG_SHOPIFY_SESSION_KEY);
        $shopifyStoreConfig = $shopifyConfig[Config::STORE_CONFIG_SECTION];
        $apiKey = $shopifyStoreConfig[ShopifyStore::CONFIG_API_KEY];
        $shopId = $shopifyStoreConfig[ShopifyStore::CONFIG_SHOP_ID];
        $url = "https://{$shopId}.myshopify.com/admin/api/auth?api_key={$apiKey}";
        return new RedirectResponse($url);


    }

    /**
     * @Route("/init_seoshop", name="init_seoshop")
     */
    public function initSeoShopAction(Request $request)
    {

        $seoShopConfig = $this->get('session')->get(Config::STORE_CONFIG_SEOSHOP_SESSION_KEY);
        $seoShopStoreConfig = $seoShopConfig[Config::STORE_CONFIG_SECTION];
        $apiKey = $seoShopStoreConfig[SeoShopStore::CONFIG_API_KEY];
        $shopId = $seoShopStoreConfig[SeoShopStore::CONFIG_SHOP_ID];
        $url = "https://api.webshopapp.com/en/apps/install?api_key={$apiKey}&shop_id={$shopId}";
        return new RedirectResponse($url);

    }


    /**
     * @Route("/shopify", name="shopify")
     */
    public function shopifyAction(Request $request)
    {

        $config = $this->get('session')->get(Config::STORE_CONFIG_SHOPIFY_SESSION_KEY);
        /** @var ShopifyOauth2Provider $shopifyOauthProvider */
        $shopifyOauthProvider = $this->get('app.shopify_oauth2_provider');
        $shopifyOauthProvider->setConfig($config[Config::STORE_CONFIG_SECTION]);

        $code = $request->get('code');
        $shop = $request->get('shop');
        $state = $request->get('state');

        if ($code && $state){
            $token = $shopifyOauthProvider->getNewAccessToken($code, $state);
            $config[Config::STORE_CONFIG_SECTION][ShopifyStore::CONFIG_ACCESS_TOKEN] = $token;
            $this->get('session')->set(Config::STORE_CONFIG_SHOPIFY_SESSION_KEY,$config);
            return new RedirectResponse($this->get('router')->generate('main'));
        }

        if ($shop){
            $authUrl = $shopifyOauthProvider->getAuthorizeUrl();
            return new RedirectResponse($authUrl);
        }

        return new RedirectResponse($this->get('router')->generate('main'));

    }


    /**
     * @Route("/seoshop", name="seoshop")
     */
    public function seoShopAction(Request $request)
    {
        $config = $this->get('session')->get(Config::STORE_CONFIG_SEOSHOP_SESSION_KEY);
        $seoShopConfig = $config[Config::STORE_CONFIG_SECTION];

        $lang = $request->get('language');
        $shopId = $request->get('shop_id');
        $signature = $request->get('signature');
        $timestamp = $request->get('timestamp');
        $token = $request->get('token');



        if ($lang
            && $shopId
            && $signature
            && $timestamp
            && $token )
        {
            // Create the signature
            $params = array(
                'language' => $shopId,
                'shop_id' => $signature,
                'timestamp' => $timestamp,
                'token' => $token // in between token
            );

            ksort($params);

            $signature = '';

            foreach ($params as $key => $value)
            {
                $signature .= $key.'='.$value;
            }

            $signature = md5($signature.$seoShopConfig[SeoShopStore::CONFIG_API_SECRET]);

            // Validate the signature
            if ($signature == $signature)
            {
                $config[Config::STORE_CONFIG_SECTION][SeoShopStore::CONFIG_ACCESS_TOKEN] = $token;
                $this->get('session')->set(Config::STORE_CONFIG_SEOSHOP_SESSION_KEY,$config);
                return new RedirectResponse($this->get('router')->generate('main'));
            }
        }
        die('Error please press back on your browser');
    }

}
