<?php

namespace Elegance\Energize;

use Elegance\Core\Code;
use Elegance\Server\Request;
use Elegance\Server\View;
use Elegance\Server\ViewRender\ViewRenderJs;
use Exception;

abstract class Page
{
    protected static array $heads = [
        'title' => '⚡ENERGIZE⚡',
        'favicon' => '/favicon.ico',
        'description' => '',
    ];

    protected static array $asides = [];

    protected static string $layout = 'default';
    protected static string $layoutState = '';

    /** Define o layout que deve ser utilizado para encapsular a resposta */
    static function layout($layout)
    {
        self::$layout = $layout;
    }

    /** Define o estado para o tempate */
    static function layoutState(string $state = '')
    {
        self::$layoutState = $state;
    }

    /** Define um conteúdo aside para a página */
    static function aside(string $content, string $name)
    {
        self::$asides[$name] = $content;
    }

    /** Define o titulo da página*/
    static function title(?string $title)
    {
        self::head('title', $title);
    }

    /** Define o favicon da página */
    static function favicon(?string $urlFavicon)
    {
        self::head('favicon', $urlFavicon);
    }

    /** Define o valor da tag description */
    static function description(?string $description)
    {
        self::head('description', $description);
    }

    /** Define um valor dinamico para o head */
    static function head($name, $value)
    {
        self::$heads[$name] = $value;
    }

    /** Renderiza a resposta da página */
    static function renderize($content): string|array
    {
        if (is_httpStatus($content))
            throw new Exception('', $content);

        if (IS_ENERGIZE)
            return self::renderizeFragment($content);

        return self::renderizePage($content);
    }

    /** Renderiza uma página completa */
    static function renderizePage($content): string
    {
        $content = self::organizeHtml($content);
        $content = self::renderLayout($content);
        $content = self::renderPage($content);
        return $content;
    }

    /** Renderiza um fragmento de página */
    static function renderizeFragment($content): array
    {
        $hash = self::getLayoutHash();

        if (is_array($content))
            return $content;

        $content = self::organizeHtml($content);

        if (IS_FRAGMENT)
            return ['content' => $content];

        if (Request::header('Energize-Hash') != $hash)
            $content = self::renderLayout($content);

        return [
            'head' => self::getHeads(),
            'hash' => $hash,
            'content' => $content
        ];
    }

    /** Renderiza o layout da respsta */
    static function renderLayout($content)
    {
        $content = "<div id='energize-content'>\n$content\n</div>";

        $aside = [];
        foreach (self::$asides as $name => $asideContent)
            $aside[$name] = self::organizeHtml($asideContent);

        $layout = self::$layout;

        $layout = View::render("layout/page/$layout", [
            'head' => self::$heads,
            'aside' => $aside
        ]);

        $layout = self::organizeHtml($layout);

        $layout = str_replace('[#content]', $content, $layout);

        return $layout ?? $content;
    }

    static function renderPage($content)
    {
        $hash = self::getLayoutHash();
        $content = "<div id='energize-layout' data-hash='$hash'>\n$content\n</div>";

        $page = View::render("layout/page/base", ['head' => self::$heads]);
        $page = self::organizeHtml($page);
        $page = str_replace('[#content]', $content, $page);

        return $page;
    }

    static function getHeads(): array
    {
        return self::$heads;
    }

    /** Retorna o hash do layout atual */
    static function getLayoutHash(): string
    {
        return Code::on([
            self::$layout,
            self::$layoutState,
            self::$asides
        ]);
    }

    /** Retorna uma string HTML organizando as tags style e script */
    static function organizeHtml(string $string): string
    {
        preg_match('/<html[^>]*>(.*?)<\/html>/s', $string, $html);

        $string = count($html) ? self::organizeComplete($string) : self::organizePartial($string);

        $string = str_replace_all(["\n\n", "\n ", "  ", "\r"], ["\n", "\n", ' ', ' '], trim($string));

        return $string;
    }

    /** Aplica a organização em uma estrutura HTML parcial */
    protected static function organizePartial(string $string): string
    {
        $src = [];
        $script = [];
        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $string, $tag);
        $string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        foreach ($tag[1] as $key => $value)
            if (empty(trim($value)))
                $src[] = $tag[0][$key];
            else
                $script[] = $value;

        $src = implode("\n", $src ?? []);
        $script = implode("\n", $script ?? []);

        if (!empty($script)) {
            $script = ViewRenderJs::minify($script);
            if (!empty($script))
                $script = "<script>\n$script\n</script>";
        }

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $string, $tag);
        $string = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $string);
        $style = $tag[1];

        $style = implode("\n", $style ?? []);

        if (!empty($style)) {
            $style = Scss::compile($style);
            if (!empty($style))
                $style = "<style>$style</style>";
        }

        $string = [$src, $style, $string, $script];
        $string = implode("\n", $string);

        return $string;
    }

    /** Aplica a organização em uma estrutura HTML completa*/
    protected static function organizeComplete(string $string): string
    {
        $src = [];
        $script = [];
        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $string, $tag);
        $string = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $string);
        foreach ($tag[1] as $key => $value)
            if (empty(trim($value)))
                $src[] = $tag[0][$key];
            else
                $script[] = $value;

        $src = implode("\n", $src ?? []);
        $script = implode("\n", $script ?? []);

        if (!empty($script)) {
            $script = ViewRenderJs::minify($script);
            if (!empty($script))
                $script = "<script>\n$script\n</script>";
        }

        preg_match_all('/<style[^>]*>(.*?)<\/style>/s', $string, $tag);
        $string = preg_replace('#<style(.*?)>(.*?)</style>#is', '', $string);
        $style = $tag[1];

        $style = implode("\n", $style ?? []);

        if (!empty($style)) {
            $style = Scss::compile($style);
            if (!empty($style))
                $style = "<style>\n$style\n</style>";
        }

        preg_match_all('/<head[^>]*>(.*?)<\/head>/s', $string, $tag);
        $string = str_replace($tag[0], '[#head]', $string);
        $string = preg_replace('#<head(.*?)>(.*?)</head>#is', '', $string);
        $head = $tag[1];

        $head[] = $style;
        $head[] = $src;
        $head[] = $script;

        $head = implode("\n", $head);
        $head = "<head>\n$head\n</head>";

        $string = prepare($string, ['head' => $head]);

        return $string;
    }
}
