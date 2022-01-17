<?

namespace Uplab\Core\Properties;


/*
	Отображает в админке свойство привязки к строке ORM таблицы
*/

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;
use DigitalWand\AdminHelper\Helper\AdminBaseHelper;
use Uplab\Core\Traits\SingletonTrait;


class RelatedORM
{
	use SingletonTrait;
	
	const PROPERTY_USER_TYPE = "UplabRelatedOrm";
	const PROPERTY_ID = "related.orm";
	
	private $MODULE_ID = "uplab.core";
	private $moduleSrc;
	private $moduleDir;
	
	function __construct()
	{
		$this->moduleDir = getLocalPath("modules/{$this->MODULE_ID}");
		$this->moduleSrc = $_SERVER["DOCUMENT_ROOT"] . $this->moduleDir;
		
		IncludeModuleLangFile(
			$this->moduleSrc . '/properties/' . self::PROPERTY_ID . '.php'
		);
	}
	
	public static function getUserTypeDescription()
	{
		self::getInstance();
		
		return [
			"PROPERTY_TYPE"        => "S",
			"USER_TYPE"            => self::PROPERTY_USER_TYPE,
			"DESCRIPTION"          => "Свойство «Привязка к ORM»",
			"GetPropertyFieldHtml" => [self::class, "GetPropertyFieldHtml"],
			"PrepareSettings"      => [self::class, "PrepareSettings"],
			"GetSettingsHTML"      => [self::class, "GetSettingsHTML"],
		];
	}
	
	
	public static function GetSettingsHTML($arProperty, $strHTMLControlName, &$arPropertyFields)
	{
		$settings = self::PrepareSettings($arProperty);
		
		$arPropertyFields = [
			"HIDE" => ["ROW_COUNT", "COL_COUNT", "MULTIPLE_CNT"],
		];
		
		return '
			<tr valign="top">
				<td>Класс ListHelper для отображения элементов сущности</td>
				<td>
					<input type="text"
					       size="50"
					       name="' . $strHTMLControlName["NAME"] . '[className]" value="' . $settings["className"] . '">
				</td>
			</tr>
			<tr valign="top">
				<td>Поле с названием</td>
				<td>
					<input type="text"
					       size="50"
					       name="' . $strHTMLControlName["NAME"] . '[elTitle]" value="' . $settings["elTitle"] . '">
				</td>
			</tr>
			';
		
	}
	
	public static function PrepareSettings($arProperty)
	{
		$className = '';
		if (is_array($arProperty["USER_TYPE_SETTINGS"])) {
			$className = trim(strip_tags($arProperty["USER_TYPE_SETTINGS"]["className"]));
			//if (!class_exists($className)) $className = "";
		}
		
		if (is_array($arProperty["USER_TYPE_SETTINGS"]) && $arProperty["USER_TYPE_SETTINGS"]["multiple"] === "Y") {
			$multiple = "Y";
		} else {
			$multiple = "N";
		}
		
		return [
			"multiple"  => $multiple,
			"className" => $className,
			"elTitle"   => $arProperty["USER_TYPE_SETTINGS"]["elTitle"],
		];
	}
	
	public static function getName($id, $settings)
	{
		/** @var AdminBaseHelper $interface */
		try {
			$className = $settings["className"];
			if (self::checkModules() && self::isClassNameExistence($className)) {
				$model = $className::getModel();
				$inputValue = $model::getByPrimary($id)->fetch();
			}
		} catch (\Exception $ex) {
			ShowError($ex->getMessage());
		}
		
		return $inputValue[$settings["elTitle"]];
	}
	
	public static function checkModules()
	{
		return Loader::includeModule('digitalwand.admin_helper');
	}
	
	public static function isClassNameExistence($className)
	{
		return !empty($className) && class_exists($className) && method_exists($className, 'getUrl') && method_exists($className, 'getModel');
	}
	
	public static function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
	{
		$settings = self::PrepareSettings($arProperty);
		$className = $settings["className"];
		if (!self::checkModules()) {
			return 'Ошибка не установлены обязательные модули';
		}
		if (!self::isClassNameExistence($className)) {
			return 'Ошибка. Неверно указан класс сущности.';
		}
		$html = '';
		ob_start();
		try {
			$inpValue = self::getName($value["VALUE"], $settings);
			
			$inpID = "prop_" . $arProperty["CODE"];
			$inpKey = $arProperty["ID"];
			$link = (new Uri($className::getUrl()))
				->addParams([
					"popup"   => "Y",
					"n"       => $inpID,
					"k"       => $inpKey,
					"eltitle" => $settings["elTitle"],
				])->getUri();
			
			
			?>

			<!--suppress HtmlFormInputWithoutLabel -->
			<input type="text" id="<?= "{$inpID}[{$inpKey}]" ?>"
				   name="<?= $strHTMLControlName["VALUE"] ?>"
				   value="<?= $value["VALUE"] ?>"
				   size='10'
			>
			&nbsp;
			<!--suppress JSUnresolvedVariable, JSUnresolvedFunction -->
			<input type="button"
				   value="..."
				   onclick="jsUtils.OpenWindow('<?= $link ?>', 800, 600);">

			&nbsp;
			<span id="sp_<?= md5($inpID) . "_" . $inpKey ?>"><?= $inpValue ?></span>
			
			<?
		} catch (\Exception $ex) {
			ShowError($ex->getMessage());
		}
		$html .= ob_get_clean();
		
		return $html;
	}
}