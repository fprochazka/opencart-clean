<?php

use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use Nette\Utils\Html;




/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class XmlObject extends Nette\Object implements IteratorAggregate
{

	const TOKEN_TAG = 'tag';
	const TOKEN_TAG_OPEN = 'tagOpen';
	const TOKEN_SLASH = 'slash';
	const TOKEN_TAG_CLOSE = 'tagClose';
	const TOKEN_TEXT = 'text';

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $attributes = array();

	/**
	 * @var array|XmlObject[]
	 */
	private $elements = array();

	/**
	 * @var string
	 */
	private $innerXml;



	/**
	 * @param string $xml
	 */
	public function __construct($xml)
	{
		list($this->name, $this->attributes, $this->elements, $this->innerXml) = $this->parseXml($xml);
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string $name
	 * @return string|null
	 */
	public function getAttribute($name)
	{
		if (!isset($this->attributes[$name])) {
			return NULL;
		}

		return $this->attributes[$name];
	}



	/**
	 * @param string $name
	 *
	 * @throws Nette\OutOfRangeException
	 * @return mixed
	 */
	public function firstElement($name)
	{
		$all = $this->allElements($name);
		return reset($all);
	}



	/**
	 * @param string $name
	 *
	 * @throws Nette\OutOfRangeException
	 * @return string
	 */
	public function allElements($name)
	{
		if (!isset($this->elements[$name = strtolower($name)])) {
			throw new Nette\OutOfRangeException("Element $name not found.");
		}

		return $this->elements[$name];
	}



	/**
	 * @param $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		$el = $this->firstElement($name);
		return $el;
	}



	/**
	 * @return string
	 */
	public function getText()
	{
		$text = NULL;
		foreach ($this->elements as $el) {
			if ($el instanceof XmlObject) {
				$text .= $el->getText();
				continue;
			}

			$text .= $el;
		}

		return $text;
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->innerXml;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator(Arrays::flatten($this->elements));
	}



	/**
	 * @param string $xml
	 * @return array
	 * @throws Nette\InvalidArgumentException
	 */
	private static function parseXml($xml)
	{
		$tokens = static::tokenize($xml);

		// find main element
		$first = static::shiftNext($tokens);
		$openEl = Html::el($first['value'][1]); // tag contents
		$name = $openEl->getName();
		$attributes = $openEl->attrs;
		unset($first);

		$element = $text = $innerXml = NULL;
		$elements = $tagQueue = array();
		$token = static::shiftNext($tokens);
		while (($next = static::shiftNext($tokens)) || $token) {
			if (!$element) {
				if ($token['type'] === self::TOKEN_TAG) {
					if ($text = trim($text)) {
						$elements[] = $text;
						$text = NULL;
					}

					$tagName = static::firstWord($token['value'][1]);
					if ($token['isTagEnd'] && $tagName === $name) {
						break; // end while
					}

					$element .= implode($token['value']); // opening tag
					if ($token['isTagShort']) {
						$innerXml .= $element;
						static::pushElement($elements, $element);

					} else {
						$tagQueue[] = $tagName;
					}

				} else {
					$text .= $token['value'];
					$innerXml .= $token['value'];
				}

			} else {
				if ($token['type'] === self::TOKEN_TAG) {
					$tagName = static::firstWord($token['value'][1]);
					$element .= implode($token['value']);

					if ($token['isTagEnd'] === FALSE) { // nested tag
						$tagQueue[] = $tagName;

					} else {
						$lastTag = array_pop($tagQueue);
						if ($lastTag !== $tagName) {
							throw new \Nette\InvalidArgumentException("Unexpected </$tagName> on line {$token['line']}.");
						}

						if (!$tagQueue) {
							$innerXml .= $element;
							static::pushElement($elements, $element);
						}
					}

				} else {
					$element .= $token['value']; // add text
				}
			}

			$token = $next;
		}

		return array($name, $attributes, $elements, trim($innerXml));
	}



	/**
	 * @param array $elements
	 * @param string $element
	 */
	private static function pushElement(array &$elements, &$element)
	{
		$xmlObj = new XmlObject($element);
		$elements[strtolower($xmlObj->getName())][] = $xmlObj;
		$element = NULL;
	}



	/**
	 * @param string $xml
	 *
	 * @throws Nette\InvalidArgumentException
	 * @return array|null
	 */
	private static function tokenize($xml)
	{
		$tokenizer = new \Nette\Utils\Tokenizer(array(
			self::TOKEN_TAG_OPEN => '<',
			self::TOKEN_TAG_CLOSE => '>',
			self::TOKEN_SLASH => '\\/',
			self::TOKEN_TEXT => '[^<>/]+'
		));

		return $tokenizer->tokenize(trim($xml));
	}



	/**
	 * @param array $tokens
	 * @return null
	 * @throws Nette\InvalidArgumentException
	 */
	private static function shiftNext(array &$tokens)
	{
		$element = array();
		$line = $type = NULL;
		$isTagShort = $isTagEnd = FALSE;

		while ($tokens && ($token = array_shift($tokens))) {
			if ($line === NULL) {
				$line = $token['line'];
			}

			if (!$element) {
				switch ($token['type']) {
					case self::TOKEN_TAG_OPEN:
						$element[] = $token['value'];
						$type = self::TOKEN_TAG;
						break;

					case self::TOKEN_TEXT:
					case self::TOKEN_SLASH:
						$element = $token['value'];
						$type = self::TOKEN_TEXT;
						break 2;

					default:
						throw new \Nette\InvalidArgumentException("Unexpected token.");
				}

			} elseif ($type === self::TOKEN_TAG) {
				$element[] = $token['value'];

				switch ($token['type']) {
					case self::TOKEN_SLASH:
						$isTagShort = count($element) === 3;
						if ($isTagEnd = count($element) === 2) {
							$element = array('</');
						}
						break;

					case self::TOKEN_TAG_CLOSE:
						if ($isTagShort) {
							$element = array_slice($element, 0, -2);
							$element[] = '/>';
						}
						break 2;
				}
			}
		}

		if (!$element) {
			return NULL;
		}

		return array(
			'value' => $element,
			'type' => $type,
			'isTagEnd' => $isTagEnd,
			'isTagShort' => $isTagShort,
			'line' => $line,
		);
	}



	/**
	 * @param string $string
	 * @return string
	 */
	private static function firstWord($string)
	{
		$words = Strings::split($string, '~\s*~', 2);
		return reset($words) ?: $string;
	}

}
