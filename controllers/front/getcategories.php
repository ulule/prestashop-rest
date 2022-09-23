<?php

/**
 * BINSHOPS
 *
 * @author BINSHOPS
 * @copyright BINSHOPS
 * @license   BINSHOPS
 *
 */

require_once dirname(__FILE__) . '/../AbstractRESTController.php';

use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderPresenter;

class BienoubienGetcategoriesModuleFrontController extends AbstractRESTController
{
    protected function processGetRequest(){
        $root_categories = Category::getHomeCategories($this->context->language->id);
        $result = array();

        foreach ($root_categories as $category){
            $root_node = $this->makeNode([
                'label' => null,
                'type' => 'root_categories',
                'children' => [],
            ]);

            $categories = $this->generateCategoriesMenu(
                Category::getNestedCategories((int)$category['id_category'], $this->context->language->id, false)
            );
            $root_node['children'] = array_merge($root_node['children'], $categories);

            $result[] = $root_node;
        }

        $this->ajaxRender(json_encode([
            'success' => true,
            'code' => 200,
            'psdata' => $result
        ]));
        die;
    }

    protected function generateCategoriesMenu($categories, $is_children = 0)
    {
        $nodes = [];

        foreach ($categories as $key => $category) {
            $node = $this->makeNode([]);
            if ($category['level_depth'] > 1) {
                $cat = new Category($category['id_category']);
                $link = $cat->getLink();
                // Check if customer is set and check access
                if (Validate::isLoadedObject($this->context->customer) && !$cat->checkAccess($this->context->customer->id)) {
                    continue;
                }
            } else {
                $link = $this->context->link->getPageLink('index');
            }

            $node['id'] = $category['id_category'];
            $node['slug'] = $category['link_rewrite'];
            $node['url'] = $link;
            $node['type'] = 'category';
            $node['page_identifier'] = 'category-' . $category['id_category'];

            /* Whenever a category is not active we shouldnt display it to customer */
            if ((bool) $category['active'] === false) {
                continue;
            }

            $current = $this->page_name == 'category' && (int) Tools::getValue('id_category') == (int) $category['id_category'];
            $node['current'] = $current;
            $node['label'] = $category['name'];
            $node['image_urls'] = [];

            if (isset($category['children']) && !empty($category['children'])) {
                $node['children'] = $this->generateCategoriesMenu($category['children'], 1);
            }

            $nodes[] = $node;
        }

        return $nodes;
    }

    protected function makeNode(array $fields)
    {
        $defaults = [
            'id' => '',
            'slug' => '',
            'type' => '',
            'label' => '',
            'url' => '',
            'children' => [],
            'image_urls' => [],
            'page_identifier' => null
        ];

        return array_merge($defaults, $fields);
    }

    protected function processPostRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'POST not supported on this path'
        ]));
        die;
    }

    protected function processPutRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'put not supported on this path'
        ]));
        die;
    }

    protected function processDeleteRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'delete not supported on this path'
        ]));
        die;
    }
}
