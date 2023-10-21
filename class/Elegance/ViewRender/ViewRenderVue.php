<?php

namespace Elegance\ViewRender;

use Elegance\Dir;
use Elegance\File;

abstract class ViewRenderVue extends ViewRender
{
    protected static array $vue = [];

    protected static array $script = [];
    protected static array $style = [];
    protected static array $incorp = [];
    protected static array $content = [];

    protected static array $renderized = [];

    protected static array $prepareReplace = [
        '// [#' => '[#',
        '/* [#' => '[#',
        '] */' => ']',
        '<!-- [#' => '[#',
        '] -->' => ']',
        '<!--[#' => '[#',
        ']-->' => ']',
    ];

    /** Aplica ações extras ao renderizar uma view */
    protected static function renderizeAction(string $content, array $params = []): string
    {
        self::init();
        $content = self::renderizeVue($content, array_shift($params));
        self::close();

        return $content;
    }

    /** Renderiza um componente ou aplicação VueJs */
    protected static function renderizeVue($content, ?string $name = null): string
    {
        $vue = end(self::$vue);

        $content = str_replace(array_keys(self::$prepareReplace), array_values(self::$prepareReplace), $content);
        $content = self::applyPrepare($content);

        list($template, $script, $style, $content) = self::explodeComponent($content);

        if (!isset(self::$renderized[$vue['key']])) {
            self::$renderized[$vue['key']] = true;

            $script = str_replace('export default', "let $vue[key] = ", $script);

            $template = trim($template);
            $template = str_replace_all(["\n", "  "], [' '], $template);
            if (!empty($template)) {
                $template = addslashes($template);
                $script .= "\n$vue[key].template = `$template`";
            }

            self::$style[] = $style;
            self::$script[] = $script;
            self::$script[] = "$vue[key].components = $vue[key].components ?? {};";
            self::$content[] = $content;
        }

        $content = '';

        if (count(self::$vue) === 1) {

            $divId = $name ?? $vue['key'];

            self::$incorp[] = "front.core.load.vue($vue[key],'#$divId')";

            $script = implode("\n", [...self::$script, ...self::$incorp]);
            $script = ViewRenderJs::minify($script);
            if (!is_blank($script))
                $script = "<script>\n(function(){\n$script\n})()\n</script>";

            $style = implode("\n", self::$style);
            $style = ViewRenderScss::minify($style);
            if (!empty($style))
                $style = "<style>\n$style\n</style>";

            $content = implode("\n", self::$content);

            $content .= is_null($name) ? "$style\n<div id='$divId'></div>\n$script" : "$style\n$script";
            $content = str_replace_all(["\n\n", "\n ", "  "], ["\n", "\n", ' '], trim($content));
        } else {
            $name = $name ?? $vue['name'];
            $prev = self::$vue[count(self::$vue) - 2];
            self::$incorp[] = "$prev[key].components['$name'] = $vue[key];";
        }

        return $content ?? '';
    }

    /** Explode um arquivo vue em um array de [template,script,style,rest] */
    protected static function explodeComponent(string $content): array
    {
        $src = [];
        $script = [];
        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $content, $tag);
        $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
        foreach ($tag[1] as $key => $value)
            if (empty(trim($value)))
                $src[] = $tag[0][$key];
            else
                $script[] = $value;

        $src = implode("\n", $src ?? []);
        $script = implode("\n", $script ?? []);

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $content, $tag);
        $content = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $content);
        $style = $tag[1];
        $style = implode("\n", $style ?? []);

        preg_match_all('/<template[^>]*>(.*?)<\/template>/s', $content, $tag);
        $content = preg_replace('#<template(.*?)>(.*?)</template>#is', '', $content);
        $template = implode("\n", $tag[1] ?? []);

        $content = trim($content);
        $content = "$src\n$content";

        return [$template, $script, $style, $content];
    }

    /** Inicia a renderização de um componente vue */
    protected static function init()
    {
        $key = self::currentGet('key');

        if (self::currentGet('ref')) {
            $ref = self::currentGet('ref');
            $name = File::getName($ref);
            if ($name == 'content') {
                $name = Dir::getOnly($ref);
                $name = explode('/', $name);
                $name = array_pop($name);
            }
            $name = strtoupper($name);
        }

        self::$vue[] = [
            'key' => "VUE_$key",
            'name' => $name ?? null,
            'script' => [],
            'style' => [],
            'incorp' => []
        ];
    }

    /** Finaliza a renderizaçao de um componente vue */
    protected static function close()
    {
        array_pop(self::$vue);
        if (!count(self::$vue)) {
            self::$script = [];
            self::$style = [];
            self::$incorp = [];
            self::$renderized = [];
        }
    }
}
