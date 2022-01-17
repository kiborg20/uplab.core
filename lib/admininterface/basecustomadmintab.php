<?php

namespace Uplab\Core\AdminInterface;

/**
 * Class BaseCustomAdminTab
 * @package RT\Calculation\AdminInterface
 *
 * базовый класс для реализации кастомных вкладок в админке
 * совместим со всеми страницами, на которых происходит инстанцирование
 * CAdminTabEngine
 *
 */
abstract class BaseCustomAdminTab
{
	protected static $divId = 'custom';
	protected static $tabName = 'Название таба';
	protected static $tabTitle = 'Тайтл таба';
	protected static $tabSet = 'tabSetName';
	/**
	 * @var $tabSort int - после какого стандартного таба вставлять. Не установлено - после последнего
	 */
	protected static $tabSort = 1;
	protected static $tabIcon = 'sale';

	public static function addTab($formData) : array
	{
		$className = static::class;
		$iblockAdminTab = new $className();
		/**
		 * @var $iblockAdminTab BaseCustomAdminTab
		 */
		$show = $iblockAdminTab->onAddTab($formData);
        if ($show) {
	        return [
		        'TABSET'  => static::$tabSet,
		        'Check'   => [$iblockAdminTab, 'check'],
		        'Action'  => [$iblockAdminTab, 'action'],
		        'GetTabs' => [$iblockAdminTab, 'getTabs'],
		        'ShowTab' => [$iblockAdminTab, 'showTab'],
	        ];
        } else {
            return [];
        }
	}

	/**
     * @param $formData
	 * @return bool
     *
	 * служит для выполнения действий перед возвратом обработчику события
     * параметров таба (пример: динамическое определение заголовка таба, отмена отрисовки таба)
     * возвращаем True в случае успеха и False - если таб отрисовывать не надо
	 */
	protected function onAddTab($formData) : bool
    {
        return true;
    }

	/**
	 * @param $arArgs
	 * @return bool
     *
     * для выполнения действий после сохранения основных данных
     * возвращаем True в случае успеха и False - в случае ошибки
     * в случае ошибки делаем так же $GLOBALS["APPLICATION"]-> ThrowException("Ошибка!!!", "ERROR");
	 */
	public function action($arArgs) : bool
	{
		return true;
	}

	/**
	 * @param $arArgs
	 * @return bool
     *
     * для выполнения действий перед сохранением основных данных (прим. проверка, изменение)
	 * возвращаем True в случае успеха и False - в случае ошибки
	 * в случае ошибки делаем так же $GLOBALS["APPLICATION"]-> ThrowException("Ошибка!!!", "ERROR");
	 */
	public function check($arArgs) : bool
	{
		return true;
	}

	/**
	 * @param $arg
	 * @return array
     *
     * возвращает описание таба
	 */
	public function getTabs($arg) : array
	{
		return [
			[
				"DIV" => static::$divId,
				"TAB" => static::$tabName,
				"ICON" => static::$tabIcon,
				"TITLE" => static::$tabTitle,
				"SORT" => static::$tabSort
			]
		];
	}

	/**
	 * @param $divId
	 * @param $arArgs
	 * @param $bVarsFromForm
     *
     * отрисовка содержимого
     * в обязательном порядке контент должен быть расположен межту тегами <tr><td>тело таба</td></tr>
	 */
	abstract public function showTab($divId = null, $arArgs = null, $bVarsFromForm = null);
}