<?php
/**
 * 2020 Wild Fortress, Lda
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 *  @author    Hélder Duarte <cossou@gmail.com>
 *  @copyright 2020 Wild Fortress, Lda
 *  @license   Proprietary and confidential
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class WebhookProduct
{
    private $params;
    private $id_product;

    /**
     * WebhookProduct constructor.
     * @param int $id_product
     * @param array $params
     * @throws Exception
     */
    public function __construct($id_product, $params = array())
    {
        $this->id_product = $id_product;
        $this->params = $params;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function present()
    {
        if (!is_int($this->id_product)) {
            return $this->params;
        }

        $context = Context::getContext();
        $id_lang = $context->language->id;
        $product = new Product($this->id_product, true);

        if (!is_a($product, 'Product')) {
            return $this->params;
        }

        $carriers = $product->getCarriers();
        $combinations = $product->getAttributeCombinations($id_lang);
        $attributes = $product->getFrontFeatures($id_lang);
        $default_attribute = Product::getDefaultAttribute($this->id_product);
        $categories = $this->getCategories();
        $images = $this->getImages($id_lang);

        return array(
            'product' => $product,
            'categories' => $categories,
            'images' => $images,
            'default_attribute' => $default_attribute,
            'attributes' => $attributes,
            'carriers' => $carriers,
            'combinations' => $combinations,
            'extra_params' => $this->params
        );
    }

    private function getCategories()
    {
        $categories = Product::getProductCategoriesFull($this->id_product);

        $formatted_cat = array();

        foreach ($categories as $category) {
            $formatted_cat[] = array(
                'id' => $category['id_category'],
                'name' => $category['name'],
                'url' => $category['link_rewrite'],
            );
        }

        return $formatted_cat;
    }

    private function getImages()
    {
        $images = Image::getImages(1, $this->id_product);
        $cover = Product::getCover($this->id_product);

        $formatted_imgs = array();

        foreach ($images as $image) {
            $image = new Image($image['id_image']);
            $image_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $image->getExistingImgPath() . ".jpg";

            $formatted_imgs[] = array(
                'id' => $image->id_image,
                'url' => $image_url
            );
        }

        $cover_image = new Image($cover['id_image']);
        $cover_image_url = _PS_BASE_URL_ . _THEME_PROD_DIR_ . $cover_image->getExistingImgPath() . ".jpg";

        $formatted_cover = array(
            'id' => $cover['id_image'],
            'url' => $cover_image_url
        );

        return array(
            'cover' => $formatted_cover,
            'images' => $formatted_imgs
        );
    }
}
