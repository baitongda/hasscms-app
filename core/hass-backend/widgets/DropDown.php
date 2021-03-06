<?php
/**
 *
* HassCMS (http://www.hassium.org/)
*
* @link http://github.com/hasscms for the canonical source repository
* @copyright Copyright (c) 2014-2099 Hassium Software LLC.
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
namespace hass\backend\widgets;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\Widget;
use yii\bootstrap\BootstrapPluginAsset;
/**
* @package hass\backend
* @author zhepama <zhepama@gmail.com>
* @since 0.1.0
 */
class DropDown extends Widget {
	/**
	 * @var array list of menu items in the dropdown. Each array element can be either an HTML string,
	 * or an array representing a single menu with the following structure:
	 *
	 * - label: string, required, the label of the item link
	 * - url: string|array, optional, the url of the item link. This will be processed by [[Url::to()]].
	 *   If not set, the item will be treated as a menu header when the item has no sub-menu.
	 * - visible: boolean, optional, whether this menu item is visible. Defaults to true.
	 * - linkOptions: array, optional, the HTML attributes of the item link.
	 * - options: array, optional, the HTML attributes of the item.
	 * - items: array, optional, the submenu items. The structure is the same as this property.
	 *   Note that Bootstrap doesn't support dropdown submenu. You have to add your own CSS styles to support it.
	 *
	 * To insert divider use `<li role="presentation" class="divider"></li>`.
	 */
	public $items = [];
	/**
	 * @var boolean whether the labels for header items should be HTML-encoded.
	 */
	public $encodeLabels = true;


	/**
	 * Initializes the widget.
	 * If you override this method, make sure you call the parent implementation first.
	 */
	public function init() {
		parent::init();
		Html::addCssClass($this->options, 'treeview-menu');
	}

	/**
	 * Renders the widget.
	 */
	public function run() {
		echo $this->renderItems($this->items, $this->options);
		BootstrapPluginAsset::register($this->getView());
	}

	/**
	 * Renders menu items.
	 *
	 * @param array $items   the menu items to be rendered
	 * @param array $options the container HTML attributes
	 *
	 * @return string the rendering result.
	 * @throws InvalidConfigException if the label option is not specified in one of the items.
	 */
	protected function renderItems($items, $options = []) {
		$lines = [];
		foreach ($items as $i => $item) {
			if (isset($item['visible']) && !$item['visible']) {
				unset($items[$i]);
				continue;
			}
			if (is_string($item)) {
				$lines[] = $item;
				continue;
			}
			if (!array_key_exists('label', $item)) {
				throw new InvalidConfigException("The 'label' option is required.");
			}
			$encodeLabel             = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
			$label                   = $encodeLabel ? Html::encode($item['label']) : $item['label'];
			$icons                   = ArrayHelper::getValue($item, 'icon', 'fa-folder');
			$itemOptions             = ArrayHelper::getValue($item, 'options', []);
			$linkOptions             = ArrayHelper::getValue($item, 'linkOptions', []);
			$linkOptions['tabindex'] = '-1';
			$linkOptions['type'] = 'ajax';
			$url                     = array_key_exists('url', $item) ? $item['url'] : null;
			if (empty($item['items'])) {
				if ($url === null) {
					$content = $label;
					Html::addCssClass($itemOptions, 'dropdown-header');
				} else {
					$icons   = ArrayHelper::getValue($item, 'icon', 'fa-circle-o');
					$content = Html::a('<i class="fa '.$icons.'"></i>'.$label, $url, $linkOptions);
				}
			} else {
				$submenuOptions = $options;
				unset($submenuOptions['id']);
				$content = Html::a('<i class="fa '.$icons.'"></i><span>'.$label.'</span><i class="fa fa-angle-left pull-right"></i>', $url === null ? '#' : $url, $linkOptions).$this->renderItems($item['items'], $submenuOptions);
				Html::addCssClass($itemOptions, 'treeview');
			}

			$lines[] = Html::tag('li', $content, $itemOptions);
		}

		return Html::tag('ul', implode("\n", $lines), $options);
	}
}
