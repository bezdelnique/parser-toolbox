<?php
/**
 * Created by PhpStorm.
 * User: heman
 * Date: 07.06.2018
 * Time: 16:27
 */

namespace bezdelnique\parserToolbox;


use Symfony\Component\CssSelector\CssSelectorConverter;


class ParserToolbox
{
    public static function nodeChildNodesToInnerHTML($node): string
    {
        if (!$node->hasChildNodes()) {
            return '';
        }

        $innerHTML = '';
        $children = $node->childNodes;
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
        $xpath = static::nodeToXPath($node);
        return static::xpathQuery($xpath, $selector);
    }


    public static function htmlQuery(string $html, string $selector)
    {
        $xpath = static::htmlToXPath($html);
        return static::xpathQuery($xpath, $selector);
    }


    public static function xpathQuery(\DOMXPath $xpath, string $selector)
    {
        $xpathSelector = (new CssSelectorConverter())->toXPath($selector);
        return $xpath->query($xpathSelector);
    }


    public static function htmlToXPath(string $html)
    {
        // http://msyk.es/blog/domdocument-whitespace-php/
        $html = preg_replace('~>[\s\n]+<~', '><', $html);

        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        $xpath = new \DOMXPath($doc);
        return $xpath;
    }


    public static function nodeToXPath($node)
    {
        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($node, true));
        $xpath = new \DOMXPath($doc);
        return $xpath;
    }


    public static function htmlToTraverseChildNodes(string $html, string $selector): array
    {
        $node = static::htmlQuery($html, $selector);
        return static::traverseChildNodes($node[0]);
    }


    public static function traverseChildNodes(\DOMElement $node, $level = 0): array
    {
        $childNodes = [];
        if ($node->hasChildNodes()) {
            $children = $node->childNodes;
            foreach ($children as $childNode) {
                if ($childNode->nodeType == XML_ELEMENT_NODE) {
                    $childLevel = $level + 1;
                    $childChildNodes = static::traverseChildNodes($childNode, $childLevel);

                    $childNodes[] = [
                        'level' => $childLevel,
                        'className' => (new \ReflectionClass($childNode))->getName(),
                        'tagName' => $childNode->tagName,
                        'nodeName' => $childNode->nodeName,
                        'nodeValue' => $childNode->nodeValue,
                        /*
                        'attrValue' => $childNode->getAttribute('value'),
                        'attrClass' => $childNode->getAttribute('class'),
                        'attrHref' => $childNode->getAttribute('href'),
                        */
                        'attrs' => static::nodeAttrs($childNode),
                        'childNodes' => $childChildNodes,
                    ];
                }
            }
        }

        return $childNodes;
    }


    public static function dumpNode($node): array
    {
        $nodeData = [
            'className' => (new \ReflectionClass($node))->getName(),
            'tagName' => $node->tagName,
            'nodeName' => $node->nodeName,
            'nodeValue' => $node->nodeValue,
            'attrClass' => $node->getAttribute('class'),
            'attrs' => static::nodeAttrs($node),
            'childNodesCount' => ($node->hasChildNodes() ? count($node->childNodes) : 0),
        ];


        return $nodeData;
    }


    public static function validateNode($node, string $tagName, $className = null): bool
    {
        if ($node->tagName != $tagName) {
            return false;
        }

        if (!is_null($className)) {
            if (stripos($node->getAttribute('class'), $className) === false) {
                return false;
            }
        }

        return true;
    }


    public static function nodeAttrs($node): array
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


    static public function getPath(string $companyNick)
    {
        $path = \Yii::getAlias(sprintf('@parser/download/%s', $companyNick));
        return $path;
    }
}

