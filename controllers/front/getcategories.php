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
        if (Tools::getValue('total_number')){
            $this->ajaxRender(json_encode([
                'success' => true,
                'code' => 200,
                'psdata' => count($this->getTopLevelCategories($this->context->language->id))
            ]));
            die;
        }

        $page = Tools::getValue('page');
        $resultsPerPage = Tools::getValue('resultsPerPage');

        $start = (($page -1) * $resultsPerPage);
        $limit = $resultsPerPage;

        $root_categories = $this->getTopLevelCategories($this->context->language->id, true, false, $start, $limit);
        $result = array();

        foreach ($root_categories as $category){
            $categories = $this->generateCategoriesMenu(
                Category::getNestedCategories((int)$category['id_category'], $this->context->language->id, false)
            );
            $result = array_merge($result, $categories);
        }

        $this->ajaxRender(json_encode([
            'success' => true,
            'code' => 200,
            'psdata' => $result
        ]));
        die;
    }

    protected function getTopLevelCategories($idLang, $active = true, $idShop = false, $start = 0, $limit = 0){
        $idParent = Configuration::get('PS_HOME_CATEGORY');
        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        $cacheId = 'Category::getChildren_' . (int) $idParent . '-' . (int) $idLang . '-' . (bool) $active . '-' . (int) $idShop;
        if (!Cache::isStored($cacheId)) {
            $query = 'SELECT c.`id_category`, cl.`name`, cl.`link_rewrite`, category_shop.`id_shop`
			FROM `' . _DB_PREFIX_ . 'category` c
			LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . ')
			' . Shop::addSqlAssociation('category', 'c') . '
			WHERE `id_lang` = ' . (int) $idLang . '
			AND c.`id_parent` = ' . (int) $idParent . '
			' . ($active ? 'AND `active` = 1' : '') . '
			GROUP BY c.`id_category`
			ORDER BY category_shop.`position` ASC ' . ($limit > 0 ? ' LIMIT ' . (int) $start . ',' . (int) $limit : '');
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            Cache::store($cacheId, $result);

            return $result;
        }
        return Cache::retrieve($cacheId);
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
