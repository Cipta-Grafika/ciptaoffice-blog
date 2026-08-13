<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    public function clean(?string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));
        $config->set('HTML.Allowed', 'p[class],br,h1[class],h2[class],h3[class],strong,em,u,s,ul,ol,li[class],blockquote[class],a[href|title|target|rel],img[src|alt|title],iframe[class|src|title|loading|referrerpolicy|frameborder|allowfullscreen|width|height],pre[class],code,div[class],table[data-full],caption,colgroup[data-full],col[width|data-full],thead,tbody,tfoot,tr,th[colspan|rowspan|scope|style],td[colspan|rowspan|style]');
        $config->set('CSS.AllowedProperties', ['height']);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^https://(?:www\.youtube(?:-nocookie)?\.com/embed/[A-Za-z0-9_-]{11}|player\.vimeo\.com/video/\d+)(?:[/?#].*)?$%D');
        $config->set('Attr.AllowedClasses', [
            'ql-indent-1',
            'ql-indent-2',
            'ql-indent-3',
            'ql-indent-4',
            'ql-indent-5',
            'ql-indent-6',
            'ql-indent-7',
            'ql-indent-8',
            'ql-align-center',
            'ql-align-right',
            'ql-align-justify',
            'ql-table-wrapper',
            'ql-video',
        ]);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
        $config->set('HTML.TargetBlank', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        $definition = $config->getHTMLDefinition(true);
        foreach (['table', 'colgroup', 'col'] as $element) {
            $definition->addAttribute($element, 'data-full', 'Enum#true');
        }
        $definition->addAttribute('iframe', 'title', 'Text');
        $definition->addAttribute('iframe', 'loading', 'Enum#lazy,eager');
        $definition->addAttribute('iframe', 'referrerpolicy', 'Enum#strict-origin-when-cross-origin,no-referrer');
        $definition->addAttribute('iframe', 'allowfullscreen', 'Bool#allowfullscreen');

        $cleanHtml = (new HTMLPurifier($config))->purify($html ?? '');

        return $this->normalizeEditorArtifacts($cleanHtml);
    }

    private function normalizeEditorArtifacts(string $html): string
    {
        $html = str_replace(["\u{00A0}", '&nbsp;', '&#160;', '&#xA0;'], ' ', $html);
        if ($html === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previousState = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="editor-content-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        $xpath = new DOMXPath($dom);
        $hasDomChanges = false;
        $unsafeIframes = [];
        foreach ($xpath->query('//*[@id="editor-content-root"]//iframe') as $iframe) {
            if (! $iframe instanceof DOMElement || $this->isAllowedVideoEmbed($iframe->getAttribute('src'))) {
                continue;
            }

            $unsafeIframes[] = $iframe;
        }
        foreach ($unsafeIframes as $iframe) {
            $iframe->parentNode?->removeChild($iframe);
            $hasDomChanges = true;
        }

        $blocks = $xpath->query(
            '//*[@id="editor-content-root"]//*[self::h1 or self::h2 or self::h3 or self::p or self::blockquote or self::li][not(ancestor::*[contains(concat(" ", normalize-space(@class), " "), " ql-table-wrapper ")])]',
        );
        $contentBlocks = [];

        foreach ($blocks as $block) {
            if ($block instanceof DOMElement && trim($block->textContent) !== '') {
                $contentBlocks[] = $block;
            }
        }

        $hasGlobalJustifyArtifact = count($contentBlocks) > 1;
        foreach ($contentBlocks as $block) {
            if (! $this->hasClass($block, 'ql-align-justify')) {
                $hasGlobalJustifyArtifact = false;
                break;
            }
        }

        if (! $hasGlobalJustifyArtifact && ! $hasDomChanges) {
            return $html;
        }

        foreach ($blocks as $block) {
            if ($block instanceof DOMElement) {
                $this->removeClass($block, 'ql-align-justify');
            }
        }

        $root = $dom->getElementById('editor-content-root');
        if (! $root) {
            return $html;
        }

        $normalizedHtml = '';
        foreach ($root->childNodes as $child) {
            $normalizedHtml .= $dom->saveHTML($child);
        }

        return $normalizedHtml;
    }

    private function isAllowedVideoEmbed(string $url): bool
    {
        return preg_match(
            '%^https://(?:www\.youtube(?:-nocookie)?\.com/embed/[A-Za-z0-9_-]{11}|player\.vimeo\.com/video/\d+)(?:[/?#].*)?$%D',
            $url,
        ) === 1;
    }

    private function hasClass(DOMElement $element, string $class): bool
    {
        $classes = preg_split('/\s+/', trim($element->getAttribute('class'))) ?: [];

        return in_array($class, $classes, true);
    }

    private function removeClass(DOMElement $element, string $class): void
    {
        $classes = preg_split('/\s+/', trim($element->getAttribute('class'))) ?: [];
        $classes = array_values(array_filter($classes, fn (string $item) => $item !== $class));

        if ($classes === []) {
            $element->removeAttribute('class');

            return;
        }

        $element->setAttribute('class', implode(' ', $classes));
    }
}
