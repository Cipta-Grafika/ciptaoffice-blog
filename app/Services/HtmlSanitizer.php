<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    public function clean(?string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));
        $config->set('HTML.Allowed', 'p[class],br,h1[class],h2[class],h3[class],strong,em,u,s,ul,ol,li[class],blockquote[class],a[href|title|target|rel],img[src|alt|title],pre[class],code');
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
        ]);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
        $config->set('HTML.TargetBlank', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        return (new HTMLPurifier($config))->purify($html ?? '');
    }
}
