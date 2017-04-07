<?php
namespace TGM\TgmCopyright\ViewHelpers;

/***************************************************************
 *  Copyright notice
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

/**
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class LinkViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Link\TypolinkViewHelper
{

	/**
	 * Render
	 *
	 * @param string $parameter stdWrap.typolink style parameter string
	 * @param string $target
	 * @param string $class
	 * @param string $title
	 * @param string $additionalParams
	 * @param array $additionalAttributes
	 *
	 * @return string
	 */
	public function render($parameter, $target = '', $class = '', $title = '', $additionalParams = '', $additionalAttributes = array())
	{
		return static::renderStatic(
			array(
				'parameter' => $parameter,
				'target' => $target,
				'class' => $class,
				'title' => $title,
				'additionalParams' => $additionalParams,
				'additionalAttributes' => $additionalAttributes
			),
			$this->buildRenderChildrenClosure(),
			$this->renderingContext
		);
	}

	/**
	 * @param array $arguments
	 * @param callable $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed|string
	 * @throws \InvalidArgumentException
	 * @throws \UnexpectedValueException
	 */
	public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
	{
		DebuggerUtility::var_dump("Test");
		$parameter = $arguments['parameter'];
		$target = $arguments['target'];
		$class = $arguments['class'];
		$title = $arguments['title'];
		$additionalParams = $arguments['additionalParams'];
		$additionalAttributes = $arguments['additionalAttributes'];

		// Merge the $parameter with other arguments
		$typolinkParameter = self::createTypolinkParameterArrayFromArguments($parameter, $target, $class, $title, $additionalParams);

		// array(param1 -> value1, param2 -> value2) --> param1="value1" param2="value2" for typolink.ATagParams
		$extraAttributes = array();
		foreach ($additionalAttributes as $attributeName => $attributeValue) {
			$extraAttributes[] = $attributeName . '="' . htmlspecialchars($attributeValue) . '"';
		}
		$aTagParams = implode(' ', $extraAttributes);

		// If no link has to be rendered, the inner content will be returned as such
		$content = (string)$renderChildrenClosure();

		if ($parameter) {
			/** @var ContentObjectRenderer $contentObject */
			$contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
			$contentObject->start(array(), '');
			$content = $contentObject->stdWrap(
				$content,
				array(
					'typolink.' => array(
						'parameter' => $typolinkParameter,
						'ATagParams' => $aTagParams,
					)
				)
			);
		}

		return $content;
	}
}

?>