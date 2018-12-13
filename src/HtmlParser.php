<?php
/**
 * Created by PhpStorm.
 * User: heman
 * Date: 07.06.2018
 * Time: 16:27
 */

namespace bezdelnique\parserToolbox;


use Symfony\Component\CssSelector\CssSelectorConverter;


class HtmlParser
{
    public static function domHtmlAsArray(\DOMElement $doc): array
    {
        return (new self())->domAsArray($doc);
    }


    public static function domToInnerHTML($element): string
    {
        $innerHTML = '';
        $children = $element->childNodes;
        foreach ($children as $child) {
            $tmpDom = new \DOMDocument();
            $tmpDom->preserveWhiteSpace = false;
            $tmpDom->formatOutput = true;
            $tmpDom->appendChild($tmpDom->importNode($child, true));
            $innerHTML .= $tmpDom->saveHTML();
        }
        return $innerHTML;
    }


    public static function nodeQuery($node, string $selector)
    {
        $doc= new \DOMDocument();
        $doc->appendChild($doc->importNode($node, true));
        $xpath = new \DOMXPath($doc);
        $converter = new CssSelectorConverter();
        $xpathSelector = $converter->toXPath($selector);
        return $xpath->query($xpathSelector);
    }


    public static function htmlQuery(string $content, string $selector)
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($content);
        $xpath = new \DOMXPath($doc);
        $converter = new CssSelectorConverter();
        $xpathSelector = $converter->toXPath($selector);
        return $xpath->query($xpathSelector);
    }


    public static function htmlAsArray(string $content, string $selector): array
    {
        $doc = new \DOMDocument();
        $doc->loadHTML($content);
        $xpath = new \DOMXPath($doc);
        $converter = new CssSelectorConverter();
        $xpathSelector = $converter->toXPath($selector);
        $elements = $xpath->query($xpathSelector);

        if ($elements->length < 1) {
            throw new ToolboxException("Nothing found by selector {$selector}.");
        }

        if ($elements->length == 1) {
            return (new self())->domAsArray($elements[0]);
        }

        $domArray = [];
        foreach ($elements as $element) {
            $domArray[] = (new self())->domAsArray($element);
        }
        return $domArray;
    }


    public function domAsArray(\DOMElement $doc): array
    {
        // $doc = new \DOMDocument();
        // $doc->load($content);
        // $root = $doc->;
        // echo count($doc->childNodes);
        // $domAsArray = $this->_traverse($doc);

        $level = 0;
        $node = $doc;
        $domAsArray = [
            'level' => $level,
            'className' => (new \ReflectionClass($node))->getName(),
            'tagName' => $node->tagName,
            'name' => $node->nodeName,
            'value' => null,
            'class' => $node->getAttribute('class'),
            'attrs' => $this->_getAttrs($node),
            'childNodes' => $this->_traverse($doc),
        ];


        return $domAsArray;
    }


    private function _getAttrs($node): array
    {
        $attrs = [];
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                $name = $attr->nodeName;
                $value = $attr->nodeValue;
                $attrs[$name] = $value;
            }
        }

        return $attrs;
    }


    private function _traverse(\DOMElement $node, $level = 0): array
    {
        $childNodes = [];
        if ($node->hasChildNodes()) {
            $children = $node->childNodes;
            foreach ($children as $childNode) {
                if ($childNode->nodeType == XML_ELEMENT_NODE) {
                    $childLevel = $level + 1;
                    $childChildNodes = $this->_traverse($childNode, $childLevel);

                    $childNodes[] = [
                        'level' => $childLevel,
                        'className' => (new \ReflectionClass($childNode))->getName(),
                        'tagName' => $childNode->tagName,
                        'name' => $childNode->nodeName,
                        // 'value' => (empty($childChildNodes) ? $childNode->nodeValue : null),
                        'value' => $childNode->nodeValue,
                        'class' => $childNode->getAttribute('class'),
                        'href' => $childNode->getAttribute('href'),
                        'valueAttr' => $childNode->getAttribute('value'),
                        'attrs' => $this->_getAttrs($childNode),
                        'childNodes' => $childChildNodes,
                    ];
                }
            }
        }

        return $childNodes;
    }
}

