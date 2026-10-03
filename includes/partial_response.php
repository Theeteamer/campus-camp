<?php

function start_partial_response(): void
{
    if (($_SERVER["HTTP_X_PARTIAL_REQUEST"] ?? "") !== "1") {
        return;
    }

    ob_start(static function (string $html): string {
        if (!preg_match('~<main\b[^>]*>.*?</main>~is', $html, $main_match)) {
            return $html;
        }

        if (preg_match_all('~<style\b[^>]*>.*?</style>~is', $html, $style_matches) && $style_matches[0]) {
            $styles = implode("", $style_matches[0]);
            $main_match[0] = preg_replace_callback(
                '~(<main\b[^>]*>)~i',
                static function (array $matches) use ($styles): string {
                    return $matches[1] . $styles;
                },
                $main_match[0],
                1
            );
        }

        if (preg_match('~<title\b[^>]*>(.*?)</title>~is', $html, $title_match)) {
            $title = html_entity_decode(strip_tags($title_match[1]), ENT_QUOTES | ENT_HTML5, "UTF-8");
            header("X-Partial-Title: " . rawurlencode($title));
        }

        header("X-Partial-Response: main");
        header("Content-Type: text/html; charset=UTF-8");

        return $main_match[0];
    });
}
