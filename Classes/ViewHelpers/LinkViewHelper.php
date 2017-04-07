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
use TGM\TgmCopyright\Domain\Model\Copyright;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Service\TypoLinkCodecService;

/**
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class LinkViewHelper extends AbstractViewHelper
{

	/**
	 * @var bool
	 */
	protected $escapeOutput = false;

	/**
	 * Render
	 *
	 * @param \TGM\TgmCopyright\Domain\Model\Copyright $copyright
	 * @param string $target
	 * @param array settings
	 * @param string $class
	 * @param string $title
	 * @param array $additionalAttributes
	 * @return string
	 */
	public function render($copyright, $settings, $target = '', $class = '', $title = '', $additionalAttributes = array())
	{
		return static::renderStatic(
			array(
				'copyright' => $copyright,
				'settings' => $settings,
				'target' => $target,
				'class' => $class,
				'title' => $title,
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
		/** @var Copyright $copyright */
		$copyright = $arguments['copyright'];
		$settings = $arguments['settings'];
		$target = $arguments['target'];
		$class = $arguments['class'];
		$title = $arguments['title'];
		$additionalAttributes = $arguments['additionalAttributes'];

		if(true === isset($settings[$copyright->getTablenames()])
			&& true === isset($settings[$copyright->getTablenames()]['detailPid'])
			&& true === isset($settings[$copyright->getTablenames()]['linkParam'])
		) {
			$parameter = $settings[$copyright->getTablenames()]['detailPid'];
			$additionalParams = $settings[$copyright->getTablenames()]['linkParam'] . $copyright->getUidForeign();
		} else {
			$parameter = $copyright->getPid();
			$additionalParams = '';
		}

		// Merge the $parameter with other arguments
		$typolinkParameter = self::createTypolinkParameterArrayFromArguments($parameter, $target, $class, $title);

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
						'additionalParams' => $additionalParams,
						'ATagParams' => $aTagParams,
						'useCacheHash' => true,
					)
				)
			);
		}

		return $content;
	}

	/**
	 * Transforms ViewHelper arguments to typo3link.parameters.typoscript option as array.
	 * @param string $parameter Example: 19 _blank - "testtitle \"with whitespace\""
	 * @param string $target
	 * @param string $class
	 * @param string $title
	 * @return string The final TypoLink string
	 */
	protected static function createTypolinkParameterArrayFromArguments($parameter, $target = '', $class = '', $title = '')
	{
		$typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
		$typolinkConfiguration = $typoLinkCodec->decode($parameter);
		if (empty($typolinkConfiguration)) {
			return $typolinkConfiguration;
		}

		// Override target if given in target argument
		if ($target) {
			$typolinkConfiguration['target'] = $target;
		}

		// Combine classes if given in both "parameter" string and "class" argument
		if ($class) {
			$classes = explode(' ', trim($typolinkConfiguration['class']) . ' ' . trim($class));
			$typolinkConfiguration['class'] = implode(' ', array_unique(array_filter($classes)));
		}

		// Override title if given in title argument
		if ($title) {
			$typolinkConfiguration['title'] = $title;
		}

		return $typoLinkCodec->encode($typolinkConfiguration);
	}

}

?>