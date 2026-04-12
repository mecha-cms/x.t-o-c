<?php

namespace x\t_o_c {
    function page__content($content) {
        \extract(\lot(), \EXTR_SKIP);
        return $state->is('page') ? \x\t_o_c\to\content($content, $state->x->{'t-o-c'}->min ?? 2) : $content;
    }
    function route__page() {
        \extract(\lot());
        \class_exists("\\Asset") && $state->is('page') && \Asset::set(__DIR__ . \D . 'index' . (\defined("\\TEST") && \TEST ? '.' : '.min.') . 'css', 10);
    }
    \Hook::set('page.content', __NAMESPACE__ . "\\page__content", 2.3);
    \Hook::set('route.page', __NAMESPACE__ . "\\route__page", -1);
}

namespace x\t_o_c\page__content {
    function tree($content) {
        \extract(\lot(), \EXTR_SKIP);
        $c = $state->x->{'t-o-c'};
        if (
            // Skip if not a page…
            !$state->is('page') ||
            // Skip if disabled by the extension state…
            (isset($c->status) && !$c->status && !isset($this->state['x']['t-o-c'])) ||
            // Skip if disabled by the page state…
            (isset($this->state['x']['t-o-c']) && !$this->state['x']['t-o-c']) ||
            // Skip if table of content(s) tree is empty…
            (!$tree = \x\t_o_c\to\tree($content, $c->min ?? 2, true))
        ) {
            return $content;
        }
        $id = $this->id;
        return (new \HTML(\Hook::fire('y.t-o-c', [['details', [
            'title' => ['summary', \i('Table of Contents'), [
                'id' => 't-o-c:' . $id,
                'role' => 'heading'
            ]],
            'content' => $tree
        ], [
            'aria-labelledby' => 't-o-c:' . $id,
            'open' => !isset($c->open) || !empty($c->open),
            'role' => 'doc-toc'
        ]]]), true)) . $content;
    }
    \Hook::set('page.content', __NAMESPACE__ . "\\tree", 2.2);
}

namespace x\t_o_c\to {
    function content(?string $content, int $min = 2): ?string {
        if (!$content || false === \strpos($content, '</')) {
            return $content;
        }
        $count = [];
        $r = "";
        foreach (\apart($content, ['caption', 'div', 'dt', 'figcaption', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'summary'], ['script', 'style', 'textarea']) as $v) {
            $k = 0;
            // Skip token(s) that are not complete
            if (1 !== $v[1]) {
                $r .= $v[0];
                continue;
            }
            // This gets the element name part
            $n = \substr($v[0], 1, \strcspn($v[0], " \n\r\t>", 1));
            // Skip `<script>`, `<style>`, and `<textarea>` element(s)
            if (false !== \strpos(',script,style,textarea,', ',' . $n . ',')) {
                $r .= $v[0];
                continue;
            }
            // This gets the element attribute(s) part
            $test = \substr(\substr($v[0], 0, $v[2]), \strlen($n) + 1, -1);
            if (false !== \strpos(',h1,h2,h3,h4,h5,h6,', ',' . $n . ',')) {
                // Skip header element(s) with a `role` attribute whose value is not `heading`
                if (false !== \strpos($test, 'role=') && 'heading' !== (\pair($test)['role'] ?? 0)) {
                    $r .= $v[0];
                    continue;
                }
                if (false !== \strpos($test, 'id=') && ($k = \pair($test)['id'] ?? 0)) {} else {
                    // Auto-generate an `id` attribute from the current element’s content
                    $k = 'to:' . \To::kebab(\w(\substr($v[0], $v[2], $v[3])) ?: \substr(\uniqid(), 6));
                }
            // Include other non-header element(s) with a `role` attribute whose value is `heading`
            } else if (false !== \strpos($test, 'role=') && 'heading' === (($q = \pair($test))['role'] ?? 0) && !empty($q['aria-level'])) {
                if (false !== \strpos($test, 'id=') && ($k = $q['id'] ?? 0)) {} else {
                    // Auto-generate an `id` attribute from the current element’s content
                    $k = 'to:' . \To::kebab(\w(\substr($v[0], $v[2], $v[3])) ?: \substr(\uniqid(), 6));
                }
            }
            if (0 !== $k) {
                $count[$k] = ($count[$k] ?? -1) + 1;
                $e = new \HTML($v[0]);
                $e['id'] = $k . ($count[$k] > 0 ? '.' . $count[$k] : "");
                $r .= (string) $e;
                continue;
            }
            $r .= $v[0];
        }
        return \count($count) >= $min ? $r : $content;
    }
    function tree(?string $content, int $min = 2, $array = false) {
        if (!$content || false === \strpos($content, '</')) {
            return null;
        }
        \extract(\lot(), \EXTR_SKIP);
        $count = $lot = $stack = [];
        $query = $link->query ?? "";
        foreach (\apart($content, ['caption', 'div', 'dt', 'figcaption', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'summary'], ['script', 'style', 'textarea']) as $v) {
            $k = $level = 0;
            // Skip token(s) that are not complete
            if (1 !== $v[1]) {
                continue;
            }
            // This gets the element name part
            $n = \substr($v[0], 1, \strcspn($v[0], " \n\r\t>", 1));
            // Skip `<script>`, `<style>`, and `<textarea>` element(s)
            if (false !== \strpos(',script,style,textarea,', ',' . $n . ',')) {
                continue;
            }
            // This gets the element attribute(s) part
            $test = \substr(\substr($v[0], 0, $v[2]), \strlen($n) + 1, -1);
            if (false !== \strpos(',h1,h2,h3,h4,h5,h6,', ',' . $n . ',')) {
                // Skip header element(s) with a `role` attribute whose value is not `heading`
                if (false !== \strpos($test, 'role=') && 'heading' !== (\pair($test)['role'] ?? 0)) {
                    continue;
                }
                $level = (int) \substr($n, 1);
                if (false !== \strpos($test, 'id=') && ($k = \pair($test)['id'] ?? 0)) {} else {
                    // Auto-generate an `id` attribute from the current element’s content
                    $k = 'to:' . \To::kebab(\w(\substr($v[0], $v[2], $v[3])) ?: \substr(\uniqid(), 6));
                }
            // Include other non-header element(s) with a `role` attribute whose value is `heading`
            } else if (false !== \strpos($test, 'role=') && 'heading' === (($q = \pair($test))['role'] ?? 0) && !empty($q['aria-level'])) {
                $level = (int) $q['aria-level'];
                if (false !== \strpos($test, 'id=') && ($k = $q['id'] ?? 0)) {} else {
                    // Auto-generate an `id` attribute from the current element’s content
                    $k = 'to:' . \To::kebab(\w(\substr($v[0], $v[2], $v[3])) ?: \substr(\uniqid(), 6));
                }
            }
            if (0 !== $k) {
                $count[$k] = ($count[$k] ?? -1) + 1;
                if ($level > 0) {
                    $lot[] = [$level, \trim(\w(\substr($v[0], $v[2], $v[3]), ['abbr', 'b', 'br', 'cite', 'code', 'del', 'dfn', 'em', 'i', 'ins', 'kbd', 'mark', 'q', 'span', 'strong', 'sub', 'sup', 'svg', 'time', 'u', 'var'])), $query . '#' . $k . ($count[$k] > 0 ? '.' . $count[$k] : "")];
                }
                continue;
            }
        }
        if (!$lot || \count($lot) < $min) {
            return null;
        }
        $r = ['ol', [], []];
        foreach ($lot as $v) {
            $e = ['li', [['a', $v[1], ['href' => $v[2]]]]];
            while (!empty($stack) && \end($stack)[0] >= $v[0]) {
                \array_pop($stack);
            }
            if (empty($stack)) {
                $r[1][] = $e;
                $r[2]['role'] = 'doc-pagelist';
                $current = &$r[1][\array_key_last($r[1])];
            } else {
                $parent = &$stack[\array_key_last($stack)][1];
                if (!isset($parent[1][1]) || 'ol' !== $parent[1][1][0]) {
                    $parent[1][1] = ['ol', [], []];
                }
                $parent[1][1][1][] = $e;
                $current = &$parent[1][1][1][\array_key_last($parent[1][1][1])];
            }
            $stack[] = [$v[0], &$current];
        }
        return $array ? $r : new \HTML($r, true);
    }
    if (\defined("\\TEST") && 'x.t-o-c' === \TEST && \is_file($test = __DIR__ . \D . 'test.php')) {
        require $test;
    }
}