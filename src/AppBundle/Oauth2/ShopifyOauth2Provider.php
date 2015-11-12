<?php
namespace AppBundle\Oauth2;
use AppBundle\Store\ShopifyStore;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;


/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 31.10.15
 * Time: 17:15
 */
Class ShopifyOauth2Provider{

    const SHOPIFY_CALLBACK_ROUTE = 'shopify';

    protected $configParams = [
        ShopifyStore::CONFIG_SHOP_ID,
        ShopifyStore::CONFIG_API_KEY,
        ShopifyStore::CONFIG_SHARED_KEY

    ];

    protected $shopId;

    protected $apiKey;

    protected $sharedSecret;

    protected $state;

    protected $callbackUrl = '';

    protected $request;

    protected $router;

    protected $defaultPermissions = ['read_products','write_products'];

    public function __construct(Router $router, RequestStack $request){

        $this->request = $request->getCurrentRequest();
        $this->router = $router;

    }

    /**
     * Set Config
     *
     * @param $config
     */
    public function setConfig($config)
    {
        $this->checkConfig($config);
        $this->shopId = $config[ShopifyStore::CONFIG_SHOP_ID];
        $this->apiKey = $config[ShopifyStore::CONFIG_API_KEY];
        $this->sharedSecret = $config[ShopifyStore::CONFIG_SHARED_KEY];
        $this->state = md5($this->shopId.$this->apiKey);
    }

    /**
     * Check Config
     *
     * @param $config
     * @return $this
     * @throws \Exception
     */
    public function checkConfig($config){
        foreach ($this->configParams as $param){
            if (!isset($config[$param])){
                throw new \Exception("Please spicefy correct config for the store. Field ".$param." is unedfined");
            }
        }
        return $this;
    }

    /**
     * Get Shop Id
     *
     * @return mixed
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Get AuthorizeUrl
     *
     * @param array $permissions
     * @param string $redirect_uri
     * @return string
     */
    public function getAuthorizeUrl($permissions = [], $redirect_uri  = ''){
        $callBackUrl = $this->getCallbackUrl();
        $permissions = empty($permissions) ? '&scope='.implode(',', $this->defaultPermissions) : '&scope='.implode(',', $permissions);
        $redirect_uri = empty($redirect_uri) ? '&redirect_uri='.urlencode($callBackUrl) : '&redirect_uri='.urlencode($redirect_uri);
        $state = "&state=".$this->state;
        $url = "https://".$this->shopId.".myshopify.com/admin/oauth/authorize?client_id=".$this->apiKey
            .$permissions .$redirect_uri . $state;
        return $url;
    }

    /**
     * Get CallBackUrl
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        if (empty($this->callbackUrl) && $this->request ){
            $this->callbackUrl = $this->request->getSchemeAndHttpHost() . $this->router->generate(self::SHOPIFY_CALLBACK_ROUTE);
        }
        return $this->callbackUrl;
    }

    /**
     * Get New Access Token
     *
     * @param $code
     * @param $state
     * @return null
     */
    public function getNewAccessToken($code, $state)
    {
        if ($code && $state && $state == $this->state){
            $url = "https://".$this->shopId.".myshopify.com/admin/oauth/access_token";
            $client = new Client();
            $options['form_params'] = array('client_id'=> $this->apiKey , 'client_secret'=>$this->sharedSecret, 'code'=>$code);
            $response = $client->post($url,$options);
            $result = json_decode($response->getBody()->getContents(),true);
            $token = $result['access_token'];
            return $token;
        }

        return null;
    }


}