<?php

namespace Elegance\Front;

use Elegance\Core\Code;
use Elegance\Server\Request;
use Elegance\Server\Response;
use Elegance\Server\View;
use Elegance\Server\ViewRender\ViewRenderJs;
use Exception;

abstract class Front
{
    protected static array $head = [
        'title' => 'Elegance',
        'favicon' => '/favicon.ico',
        'description' => '',
    ];
    protected static array $aside = [];

    protected static string $layoutView = 'default';
    protected static string $layoutGroup = '';

    /** Define o layout que deve ser utilizado para encapsular a resposta */
    static function layout($view)
    {
        self::$layoutView = $view;
    }

    /** Define um grupo para o layout */
    static function layoutGroup(string $group = '')
    {
        self::$layoutGroup = $group;
    }

    /** Define um conteúdo aside para a página */
    static function aside(string $content, string $name)
    {
        self::$aside[$name] = $content;
    }

    /** Define o titulo da página no navegador */
    static function title(?string $title)
    {
        self::head('title', $title);
    }

    /** Define o favicon da página no navegador */
    static function favicon(?string $favicon)
    {
        self::head('favicon', $favicon);
    }

    /** Define o valor da tag description */
    static function description(?string $description)
    {
        self::head('description', $description);
    }

    /** Define um valor dinamico para o head */
    static function head($name, $value)
    {
        self::$head[$name] = $value;
    }

    /** Resolve um conteúdo encapsulando em uma resposta front */
    static function solve($content)
    {
        if (is_httpStatus($content))
            throw new Exception('', $content);

        if (!is_stringable($content))
            return $content;

        if (Request::header('Front-Request')) {
            Response::type('json');
            Response::status(STS_OK);
            Response::content([
                'info' => [
                    'elegance' => true,
                    'status' => STS_OK,
                    'error' => false,
                ],
                'data' => self::renderToArray($content),
            ]);
            Response::send();
        }

        $content = self::renderToHtml($content);

        Response::type('html');
        Response::status(STS_OK);
        Response::content($content);
        Response::send();
    }

    /** Renderiza um conteúdo dentro de uma estrutua de resposta parcial */
    protected static function renderToArray($content)
    {
        $hash = self::getLayoutHash();

        $response = [
            'head' => self::$head,
            'hash' => $hash,
            'content' => self::organizeHtml($content)
        ];

        if (Request::header('Front-Hash') != $hash)
            $response['content'] = self::renderLayout($response['content']);

        return $response;
    }

    /** Renderia um conteúdo dentro de uma estrutura de resposta completa */
    protected static function renderToHtml($content)
    {
        $content = self::organizeHtml($content);

        $content = self::renderLayout($content);
        $content = self::renderPage($content);

        return $content;
    }

    /** Renderiza o Layout da respsta */
    protected static function renderLayout($content)
    {
        $content = "<div id='front-content'>\n$content\n</div>";

        $aside = [];
        foreach (self::$aside as $name => $asideContent)
            $aside[$name] = self::organizeHtml($asideContent);

        $layout = self::$layoutView;

        $layout = View::render("front/layout/$layout.php", [
            'head' => self::$head,
            'aside' => $aside
        ]);

        $layout = self::organizeHtml($layout);

        $layout = str_replace('[#content]', $content, $layout);

        return $layout ?? $content;
    }

    protected static function renderPage($content)
    {
        $hash = self::getLayoutHash();
        $content = "<div id='front-layout' data-hash='$hash'>\n$content\n</div>";

        $page = View::render("front/base.php", ['head' => self::$head]);
        $page = self::organizeHtml($page);
        $page = str_replace('[#content]', $content, $page);

        return $page;
    }

    /** Retorna o hash do layout atual */
    protected static function getLayoutHash(): string
    {
        $key = prepare('[#]#[#][#]', [
            self::$layoutView ?? Request::host(),
            self::$layoutGroup,
        ]);
        return Code::on([$key, self::$aside]);
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
