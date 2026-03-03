<?php

namespace App\Helpers;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class OmmlToLatex
{
    /**
     * Convert an OMML <m:oMath> XML string to LaTeX.
     */
    public static function convert(string $ommlXml): string
    {
        $doc = new DOMDocument();
        $doc->loadXML($ommlXml);
        return trim(self::processNode($doc->documentElement));
    }

    /**
     * Pre-process a .docx file: replace OMML equations with LaTeX text markers.
     * Returns the path to a temporary modified .docx file.
     */
    public static function preprocessDocx(string $docxPath): string
    {
        $tempPath = sys_get_temp_dir() . '/docx_math_' . uniqid() . '.docx';
        copy($docxPath, $tempPath);

        $zip = new \ZipArchive();
        if ($zip->open($tempPath) !== true) {
            return $docxPath; // fallback to original
        }

        $xml = $zip->getFromName('word/document.xml');
        if (!$xml) {
            $zip->close();
            return $docxPath;
        }

        // Register math namespace
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/officeDocument/2006/math');
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        // Find all m:oMathPara and m:oMath elements
        $mathParas = $xpath->query('//m:oMathPara');
        foreach ($mathParas as $mathPara) {
            $latex = self::processNode($mathPara);
            self::replaceMathWithText($doc, $mathPara, '\\( ' . $latex . ' \\)');
        }

        // Process remaining standalone m:oMath (not inside m:oMathPara)
        $maths = $xpath->query('//m:oMath');
        foreach ($maths as $math) {
            $latex = self::processNode($math);
            self::replaceMathWithText($doc, $math, '\\( ' . $latex . ' \\)');
        }

        $zip->addFromString('word/document.xml', $doc->saveXML());
        $zip->close();

        return $tempPath;
    }

    private static function replaceMathWithText(DOMDocument $doc, DOMNode $mathNode, string $text): void
    {
        $parent = $mathNode->parentNode;
        if (!$parent) return;

        // Create a w:r > w:t element with the LaTeX text
        $nsW = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $run = $doc->createElementNS($nsW, 'w:r');
        $t = $doc->createElementNS($nsW, 'w:t');
        $t->setAttribute('xml:space', 'preserve');
        $t->appendChild($doc->createTextNode($text));
        $run->appendChild($t);

        $parent->replaceChild($run, $mathNode);
    }

    private static function processNode(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            return $node->textContent;
        }

        if (!($node instanceof DOMElement)) {
            $result = '';
            foreach ($node->childNodes as $child) {
                $result .= self::processNode($child);
            }
            return $result;
        }

        $localName = $node->localName;

        switch ($localName) {
            case 'oMathPara':
            case 'oMath':
                return self::processChildren($node);

            case 'r': // m:r - math run (contains text)
                return self::processMathRun($node);

            case 'f': // m:f - fraction
                return self::processFraction($node);

            case 'sSup': // m:sSup - superscript
                return self::processSuperscript($node);

            case 'sSub': // m:sSub - subscript
                return self::processSubscript($node);

            case 'sSubSup': // m:sSubSup - sub-superscript
                return self::processSubSuperscript($node);

            case 'rad': // m:rad - radical/root
                return self::processRadical($node);

            case 'nary': // m:nary - n-ary (sum, integral, product)
                return self::processNary($node);

            case 'bar': // m:bar - bar/overline
                return self::processBar($node);

            case 'acc': // m:acc - accent (hat, tilde, etc.)
                return self::processAccent($node);

            case 'd': // m:d - delimiter (parentheses, brackets)
                return self::processDelimiter($node);

            case 'eqArr': // m:eqArr - equation array
                return self::processEqArr($node);

            case 'limLow': // m:limLow - limit lower
                return self::processLimLow($node);

            case 'limUpp': // m:limUpp - limit upper
                return self::processLimUpp($node);

            case 'func': // m:func - function (sin, cos, etc.)
                return self::processFunc($node);

            case 'm': // m:m - matrix
                return self::processMatrix($node);

            case 'box':
            case 'borderBox':
                return self::processChildren($node);

            case 'sPre': // m:sPre - pre-script (like prescripts for tensors)
                return self::processPreScript($node);

            case 'e': // m:e - element/base
            case 'num': // m:num - numerator
            case 'den': // m:den - denominator
            case 'sup': // m:sup - superscript content
            case 'sub': // m:sub - subscript content
            case 'deg': // m:deg - degree
            case 'fName': // m:fName - function name
            case 'lim': // m:lim - limit
                return self::processChildren($node);

            case 'rPr': // m:rPr - run properties (skip)
            case 'ctrlPr': // control properties (skip)
            case 'fPr': // fraction properties
            case 'sSubPr':
            case 'sSupPr':
            case 'sSubSupPr':
            case 'radPr':
            case 'naryPr':
            case 'barPr':
            case 'accPr':
            case 'dPr':
            case 'eqArrPr':
            case 'limLowPr':
            case 'limUppPr':
            case 'funcPr':
            case 'mPr':
            case 'sPrePr':
            case 'boxPr':
            case 'borderBoxPr':
                return '';

            case 't': // m:t - text content
                return self::processText($node);

            default:
                return self::processChildren($node);
        }
    }

    private static function processChildren(DOMNode $node): string
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            $result .= self::processNode($child);
        }
        return $result;
    }

    private static function processMathRun(DOMElement $node): string
    {
        $text = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 't') {
                $text .= self::processText($child);
            }
        }
        return $text;
    }

    private static function processText(DOMElement $node): string
    {
        $text = $node->textContent;

        // Map common Unicode math characters to LaTeX
        $map = [
            '×' => '\\times ', '÷' => '\\div ', '±' => '\\pm ', '∓' => '\\mp ',
            '≤' => '\\leq ', '≥' => '\\geq ', '≠' => '\\neq ', '≈' => '\\approx ',
            '∞' => '\\infty ', '∑' => '\\sum ', '∏' => '\\prod ', '∫' => '\\int ',
            '∂' => '\\partial ', '∇' => '\\nabla ', '√' => '\\sqrt ',
            '∈' => '\\in ', '∉' => '\\notin ', '⊂' => '\\subset ', '⊃' => '\\supset ',
            '⊆' => '\\subseteq ', '⊇' => '\\supseteq ', '∪' => '\\cup ', '∩' => '\\cap ',
            '∧' => '\\wedge ', '∨' => '\\vee ', '¬' => '\\neg ',
            '→' => '\\rightarrow ', '←' => '\\leftarrow ', '↔' => '\\leftrightarrow ',
            '⇒' => '\\Rightarrow ', '⇐' => '\\Leftarrow ', '⇔' => '\\Leftrightarrow ',
            '∀' => '\\forall ', '∃' => '\\exists ',
            // Greek letters
            'α' => '\\alpha ', 'β' => '\\beta ', 'γ' => '\\gamma ', 'δ' => '\\delta ',
            'ε' => '\\epsilon ', 'ζ' => '\\zeta ', 'η' => '\\eta ', 'θ' => '\\theta ',
            'ι' => '\\iota ', 'κ' => '\\kappa ', 'λ' => '\\lambda ', 'μ' => '\\mu ',
            'ν' => '\\nu ', 'ξ' => '\\xi ', 'π' => '\\pi ', 'ρ' => '\\rho ',
            'σ' => '\\sigma ', 'τ' => '\\tau ', 'υ' => '\\upsilon ', 'φ' => '\\varphi ',
            'χ' => '\\chi ', 'ψ' => '\\psi ', 'ω' => '\\omega ',
            'Α' => 'A', 'Β' => 'B', 'Γ' => '\\Gamma ', 'Δ' => '\\Delta ',
            'Θ' => '\\Theta ', 'Λ' => '\\Lambda ', 'Ξ' => '\\Xi ', 'Π' => '\\Pi ',
            'Σ' => '\\Sigma ', 'Φ' => '\\Phi ', 'Ψ' => '\\Psi ', 'Ω' => '\\Omega ',
            // Chemistry
            '⟶' => '\\longrightarrow ', '⟵' => '\\longleftarrow ',
            '⇌' => '\\rightleftharpoons ', '↑' => '\\uparrow ', '↓' => '\\downarrow ',
            '°' => '^{\\circ}',
            // Other
            '·' => '\\cdot ', '…' => '\\ldots ', '⋯' => '\\cdots ',
        ];

        $text = strtr($text, $map);

        // Handle function names
        $functions = ['sin', 'cos', 'tan', 'cot', 'sec', 'csc',
                      'log', 'ln', 'exp', 'lim', 'max', 'min',
                      'sup', 'inf', 'det', 'dim', 'mod', 'gcd'];
        foreach ($functions as $fn) {
            if ($text === $fn) {
                return '\\' . $fn . ' ';
            }
        }

        return $text;
    }

    private static function processFraction(DOMElement $node): string
    {
        $num = '';
        $den = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'num') $num = self::processChildren($child);
                elseif ($child->localName === 'den') $den = self::processChildren($child);
            }
        }
        return '\\frac{' . trim($num) . '}{' . trim($den) . '}';
    }

    private static function processSuperscript(DOMElement $node): string
    {
        $base = '';
        $sup = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'e') $base = self::processChildren($child);
                elseif ($child->localName === 'sup') $sup = self::processChildren($child);
            }
        }
        $base = trim($base);
        $sup = trim($sup);
        if (mb_strlen($base) > 1 && !preg_match('/^\\\\/', $base)) $base = '{' . $base . '}';
        return $base . '^{' . $sup . '}';
    }

    private static function processSubscript(DOMElement $node): string
    {
        $base = '';
        $sub = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'e') $base = self::processChildren($child);
                elseif ($child->localName === 'sub') $sub = self::processChildren($child);
            }
        }
        $base = trim($base);
        $sub = trim($sub);
        if (mb_strlen($base) > 1 && !preg_match('/^\\\\/', $base)) $base = '{' . $base . '}';
        return $base . '_{' . $sub . '}';
    }

    private static function processSubSuperscript(DOMElement $node): string
    {
        $base = '';
        $sub = '';
        $sup = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'e') $base = self::processChildren($child);
                elseif ($child->localName === 'sub') $sub = self::processChildren($child);
                elseif ($child->localName === 'sup') $sup = self::processChildren($child);
            }
        }
        $base = trim($base);
        return $base . '_{' . trim($sub) . '}^{' . trim($sup) . '}';
    }

    private static function processRadical(DOMElement $node): string
    {
        $deg = '';
        $base = '';
        $hideDeg = false;
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'radPr') {
                    // Check if degree is hidden
                    foreach ($child->childNodes as $prop) {
                        if ($prop instanceof DOMElement && $prop->localName === 'degHide') {
                            $val = $prop->getAttribute('m:val') ?: $prop->getAttribute('val');
                            if ($val === '1' || $val === 'on' || $val === 'true') $hideDeg = true;
                        }
                    }
                }
                elseif ($child->localName === 'deg') $deg = self::processChildren($child);
                elseif ($child->localName === 'e') $base = self::processChildren($child);
            }
        }
        $deg = trim($deg);
        $base = trim($base);
        if ($hideDeg || $deg === '' || $deg === '2') {
            return '\\sqrt{' . $base . '}';
        }
        return '\\sqrt[' . $deg . ']{' . $base . '}';
    }

    private static function processNary(DOMElement $node): string
    {
        $op = '\\int ';
        $sub = '';
        $sup = '';
        $base = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'naryPr') {
                    foreach ($child->childNodes as $prop) {
                        if ($prop instanceof DOMElement && $prop->localName === 'chr') {
                            $char = $prop->getAttribute('m:val') ?: $prop->getAttribute('val');
                            $opMap = [
                                '∑' => '\\sum', '∏' => '\\prod', '∫' => '\\int',
                                '∬' => '\\iint', '∭' => '\\iiint', '∮' => '\\oint',
                                '⋃' => '\\bigcup', '⋂' => '\\bigcap',
                                '⋁' => '\\bigvee', '⋀' => '\\bigwedge',
                            ];
                            $op = ($opMap[$char] ?? '\\int') . ' ';
                        }
                    }
                }
                elseif ($child->localName === 'sub') $sub = self::processChildren($child);
                elseif ($child->localName === 'sup') $sup = self::processChildren($child);
                elseif ($child->localName === 'e') $base = self::processChildren($child);
            }
        }
        $result = trim($op);
        $sub = trim($sub);
        $sup = trim($sup);
        if ($sub !== '') $result .= '_{' . $sub . '}';
        if ($sup !== '') $result .= '^{' . $sup . '}';
        return $result . ' ' . trim($base);
    }

    private static function processBar(DOMElement $node): string
    {
        $base = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'e') {
                $base = self::processChildren($child);
            }
        }
        return '\\overline{' . trim($base) . '}';
    }

    private static function processAccent(DOMElement $node): string
    {
        $base = '';
        $accChar = '̂'; // default hat
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'accPr') {
                    foreach ($child->childNodes as $prop) {
                        if ($prop instanceof DOMElement && $prop->localName === 'chr') {
                            $accChar = $prop->getAttribute('m:val') ?: $prop->getAttribute('val') ?: $accChar;
                        }
                    }
                }
                elseif ($child->localName === 'e') $base = self::processChildren($child);
            }
        }
        $accMap = [
            '̂' => '\\hat', '̃' => '\\tilde', '̇' => '\\dot', '̈' => '\\ddot',
            '⃗' => '\\vec', '̄' => '\\bar', '˘' => '\\breve', '̌' => '\\check',
            '^' => '\\hat', '~' => '\\tilde', '→' => '\\vec',
            '¨' => '\\ddot', '˙' => '\\dot',
        ];
        $cmd = $accMap[$accChar] ?? '\\hat';
        return $cmd . '{' . trim($base) . '}';
    }

    private static function processDelimiter(DOMElement $node): string
    {
        $begChar = '(';
        $endChar = ')';
        $elements = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'dPr') {
                    foreach ($child->childNodes as $prop) {
                        if ($prop instanceof DOMElement) {
                            if ($prop->localName === 'begChr') {
                                $begChar = $prop->getAttribute('m:val') ?: $prop->getAttribute('val') ?: '(';
                            }
                            elseif ($prop->localName === 'endChr') {
                                $endChar = $prop->getAttribute('m:val') ?: $prop->getAttribute('val') ?: ')';
                            }
                        }
                    }
                }
                elseif ($child->localName === 'e') {
                    $elements[] = self::processChildren($child);
                }
            }
        }
        $charMap = [
            '(' => '(', ')' => ')', '[' => '[', ']' => ']',
            '{' => '\\{', '}' => '\\}', '|' => '|',
            '⟨' => '\\langle ', '⟩' => '\\rangle ',
            '⌈' => '\\lceil ', '⌉' => '\\rceil ',
            '⌊' => '\\lfloor ', '⌋' => '\\rfloor ',
            '' => '.', // empty = invisible delimiter
        ];
        $left = $charMap[$begChar] ?? $begChar;
        $right = $charMap[$endChar] ?? $endChar;
        $content = implode(', ', array_map('trim', $elements));

        return '\\left' . $left . ' ' . $content . ' \\right' . $right;
    }

    private static function processEqArr(DOMElement $node): string
    {
        $rows = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'e') {
                $rows[] = self::processChildren($child);
            }
        }
        return '\\begin{aligned} ' . implode(' \\\\ ', array_map('trim', $rows)) . ' \\end{aligned}';
    }

    private static function processLimLow(DOMElement $node): string
    {
        $base = '';
        $lim = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'e') $base = self::processChildren($child);
                elseif ($child->localName === 'lim') $lim = self::processChildren($child);
            }
        }
        return trim($base) . '_{' . trim($lim) . '}';
    }

    private static function processLimUpp(DOMElement $node): string
    {
        $base = '';
        $lim = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'e') $base = self::processChildren($child);
                elseif ($child->localName === 'lim') $lim = self::processChildren($child);
            }
        }
        return trim($base) . '^{' . trim($lim) . '}';
    }

    private static function processFunc(DOMElement $node): string
    {
        $name = '';
        $base = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'fName') $name = self::processChildren($child);
                elseif ($child->localName === 'e') $base = self::processChildren($child);
            }
        }
        return trim($name) . '{' . trim($base) . '}';
    }

    private static function processMatrix(DOMElement $node): string
    {
        $rows = [];
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'mr') {
                $cols = [];
                foreach ($child->childNodes as $cell) {
                    if ($cell instanceof DOMElement && $cell->localName === 'e') {
                        $cols[] = self::processChildren($cell);
                    }
                }
                $rows[] = implode(' & ', array_map('trim', $cols));
            }
        }
        return '\\begin{pmatrix} ' . implode(' \\\\ ', $rows) . ' \\end{pmatrix}';
    }

    private static function processPreScript(DOMElement $node): string
    {
        $base = '';
        $sub = '';
        $sup = '';
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if ($child->localName === 'e') $base = self::processChildren($child);
                elseif ($child->localName === 'sub') $sub = self::processChildren($child);
                elseif ($child->localName === 'sup') $sup = self::processChildren($child);
            }
        }
        $result = '';
        if (trim($sub) !== '') $result .= '_{' . trim($sub) . '}';
        if (trim($sup) !== '') $result .= '^{' . trim($sup) . '}';
        return $result . trim($base);
    }
}
