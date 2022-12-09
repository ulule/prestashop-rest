<?php

class APIRoutes
{
    public static final function getRoutes(): array
    {
        return [
            'module-bienoubien-login' => [
                'rule' => 'rest/login',
                'keywords' => [],
                'controller' => 'login',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-register' => [
                'rule' => 'rest/register',
                'keywords' => [],
                'controller' => 'register',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-logout' => [
                'rule' => 'rest/logout',
                'keywords' => [],
                'controller' => 'logout',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-accountinfo' => [
                'rule' => 'rest/accountInfo',
                'keywords' => [],
                'controller' => 'accountinfo',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-accountedit' => [
                'rule' => 'rest/accountedit',
                'keywords' => [],
                'controller' => 'accountedit',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-bootstrap' => [
                'rule' => 'rest/bootstrap',
                'keywords' => [],
                'controller' => 'bootstrap',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-lightbootstrap' => [
                'rule' => 'rest/lightbootstrap',
                'keywords' => [],
                'controller' => 'lightbootstrap',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-productdetail' => [
                'rule' => 'rest/productdetail',
                'keywords' => [],
                'controller' => 'productdetail',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-orderhistory' => [
                'rule' => 'rest/orderhistory',
                'keywords' => [],
                'controller' => 'orderhistory',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-cart' => [
                'rule' => 'rest/cart',
                'keywords' => [],
                'controller' => 'cart',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-categoryproducts' => [
                'rule' => 'rest/categoryProducts',
                'keywords' => [],
                'controller' => 'categoryproducts',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-productsearch' => [
                'rule' => 'rest/productSearch',
                'keywords' => [],
                'controller' => 'productsearch',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-checkout' => [
                'rule' => 'rest/checkout',
                'keywords' => [],
                'controller' => 'checkout',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-featuredproducts' => [
                'rule' => 'rest/featuredproducts',
                'keywords' => [],
                'controller' => 'featuredproducts',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-address' => [
                'rule' => 'rest/address',
                'keywords' => [],
                'controller' => 'address',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-alladdresses' => [
                'rule' => 'rest/alladdresses',
                'keywords' => [],
                'controller' => 'alladdresses',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-addressform' => [
                'rule' => 'rest/addressform',
                'keywords' => [],
                'controller' => 'addressform',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-carriers' => [
                'rule' => 'rest/carriers',
                'keywords' => [],
                'controller' => 'carriers',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-setaddresscheckout' => [
                'rule' => 'rest/setaddresscheckout',
                'keywords' => [],
                'controller' => 'setaddresscheckout',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-setcarriercheckout' => [
                'rule' => 'rest/setcarriercheckout',
                'keywords' => [],
                'controller' => 'setcarriercheckout',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-paymentoptions' => [
                'rule' => 'rest/paymentoptions',
                'keywords' => [],
                'controller' => 'paymentoptions',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-resetpasswordemail' => [
                'rule' => 'rest/resetpasswordemail',
                'keywords' => [],
                'controller' => 'resetpasswordemail',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-resetpasswordcheck' => [
                'rule' => 'rest/resetpasswordcheck',
                'keywords' => [],
                'controller' => 'resetpasswordcheck',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-resetpasswordenter' => [
                'rule' => 'rest/resetpasswordenter',
                'keywords' => [],
                'controller' => 'resetpasswordenter',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-resetpasswordbyemail' => [
                'rule' => 'rest/resetpasswordbyemail',
                'keywords' => [],
                'controller' => 'resetpasswordbyemail',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-listcomments' => [
                'rule' => 'rest/listcomments',
                'keywords' => [],
                'controller' => 'listcomments',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-postcomment' => [
                'rule' => 'rest/postcomment',
                'keywords' => [],
                'controller' => 'postcomment',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-hello' => [
                'rule' => 'rest',
                'keywords' => [],
                'controller' => 'hello',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-ps_checkpayment' => [
                'rule' => 'rest/ps_checkpayment',
                'keywords' => [],
                'controller' => 'ps_checkpayment',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-ps_wirepayment' => [
                'rule' => 'rest/ps_wirepayment',
                'keywords' => [],
                'controller' => 'ps_wirepayment',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-wishlist' => [
                'rule' => 'rest/wishlist',
                'keywords' => [],
                'controller' => 'wishlist',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-emailsubscription' => [
                'rule' => 'rest/emailsubscription',
                'keywords' => [],
                'controller' => 'emailsubscription',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-createorder' => [
                'rule' => 'rest/createorder',
                'keywords' => [],
                'controller' => 'createorder',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-getorders' => [
                'rule' => 'rest/getorders',
                'keywords' => [],
                'controller' => 'getorders',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-search-orders' => [
                'rule' => 'rest/search-orders',
                'keywords' => [],
                'controller' => 'searchorders',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-webhook' => [
                'rule' => 'rest/webhook',
                'keywords' => [],
                'controller' => 'webhook',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
            'module-bienoubien-getcategories' => [
                'rule' => 'rest/getcategories',
                'keywords' => [],
                'controller' => 'getcategories',
                'params' => [
                    'fc' => 'module',
                    'module' => 'bienoubien'
                ]
            ],
        ];
    }
}
