StoreSyncer.

To run demo version i've predifined store settings to sync data between.
You Can found this configuration in class AppBundle/Config/Config.
It was easier to store data there then in db.
But anyway data from this Config stored to session here in StoreConfigListener.
In all application we should work with configuration from session, it's our little storage for some initial data.
By default i've defined token for both store but if you wish to login and create your own please change configration
in Config file, and make token field empty. After your logging to the store the token will be stored in the session.
To sync data between stores i've decide to create Containers to hold data that we sync. In depends on entity type Product
container for products, and Category container for categories.
Also i've created two class it's ShopifyStore and SeoShop. That can fill the container with theirs api.
For Shopify i've created my own api client, using library Guzzle.
For SeoShop i use official api client.

But anyway i can't avoid using db. I use it for data mapping between stores. We can use this if we make a lot of data syncs
and we have already created products and we only need to update them. For this i've create EntityMapping Class and it's
repository.

Our main objects are Syncer And SyncProcessor that do all magic. :)

That's all thx.

