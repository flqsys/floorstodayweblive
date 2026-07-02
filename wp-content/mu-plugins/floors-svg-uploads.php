<?php
/**
 * Plugin Name: Floors Today SVG Uploads
 * Description: Allows trusted admins to upload sanitized SVG files.
 * Version: 0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function ft_svg_uploads_allowed_user() {
    return current_user_can('manage_options') && current_user_can('unfiltered_html');
}

function ft_svg_uploads_is_svg_file($file) {
    $name = is_array($file) && isset($file['name']) ? (string) $file['name'] : (string) $file;

    return strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'svg';
}

function ft_svg_uploads_sanitize_file($path) {
    if (!is_readable($path) || !is_writable($path) || !class_exists('DOMDocument')) {
        return false;
    }

    $contents = file_get_contents($path);

    if (!is_string($contents) || strlen($contents) > 1024 * 1024) {
        return false;
    }

    if (preg_match('/<!doctype|<!entity|<\?xml-stylesheet/i', $contents)) {
        return false;
    }

    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $loaded = $dom->loadXML($contents, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded || !$dom->documentElement || strtolower($dom->documentElement->localName) !== 'svg') {
        return false;
    }

    ft_svg_uploads_clean_node($dom->documentElement);

    $sanitized = $dom->saveXML($dom->documentElement);

    if (!is_string($sanitized) || stripos($sanitized, '<svg') === false) {
        return false;
    }

    return file_put_contents($path, $sanitized) !== false;
}

function ft_svg_uploads_clean_node(DOMNode $node) {
    $allowed_tags = [
        'svg',
        'g',
        'path',
        'rect',
        'circle',
        'ellipse',
        'line',
        'polyline',
        'polygon',
        'text',
        'tspan',
        'defs',
        'lineargradient',
        'radialgradient',
        'stop',
        'clippath',
        'mask',
        'pattern',
        'title',
        'desc',
        'use',
    ];

    $allowed_attrs = [
        'aria-hidden',
        'aria-label',
        'class',
        'clip-path',
        'clip-rule',
        'cx',
        'cy',
        'd',
        'dx',
        'dy',
        'fill',
        'fill-opacity',
        'fill-rule',
        'focusable',
        'font-family',
        'font-size',
        'font-weight',
        'height',
        'id',
        'mask',
        'offset',
        'opacity',
        'points',
        'preserveaspectratio',
        'r',
        'role',
        'rx',
        'ry',
        'spreadmethod',
        'stop-color',
        'stop-opacity',
        'stroke',
        'stroke-dasharray',
        'stroke-dashoffset',
        'stroke-linecap',
        'stroke-linejoin',
        'stroke-miterlimit',
        'stroke-opacity',
        'stroke-width',
        'text-anchor',
        'transform',
        'version',
        'viewbox',
        'width',
        'x',
        'x1',
        'x2',
        'xlink:href',
        'xml:space',
        'xmlns',
        'xmlns:xlink',
        'y',
        'y1',
        'y2',
    ];

    for ($child = $node->firstChild; $child;) {
        $next = $child->nextSibling;

        if ($child instanceof DOMElement) {
            $tag = strtolower($child->localName);

            if (!in_array($tag, $allowed_tags, true)) {
                $node->removeChild($child);
            } else {
                ft_svg_uploads_clean_node($child);
            }
        } elseif (!$child instanceof DOMText) {
            $node->removeChild($child);
        }

        $child = $next;
    }

    if (!$node instanceof DOMElement || !$node->hasAttributes()) {
        return;
    }

    for ($i = $node->attributes->length - 1; $i >= 0; $i--) {
        $attribute = $node->attributes->item($i);

        if (!$attribute) {
            continue;
        }

        $name = strtolower($attribute->name);
        $value = trim($attribute->value);
        $remove = !in_array($name, $allowed_attrs, true);

        if (strpos($name, 'on') === 0 || preg_match('/javascript:|data:|vbscript:|expression\s*\(/i', $value)) {
            $remove = true;
        }

        if (preg_match('/url\s*\(\s*(?![\'"]?#)/i', $value)) {
            $remove = true;
        }

        if (in_array($name, ['href', 'xlink:href'], true) && $value !== '' && strpos($value, '#') !== 0) {
            $remove = true;
        }

        if ($remove) {
            $node->removeAttributeNode($attribute);
        }
    }
}

add_filter('upload_mimes', function ($mimes) {
    if (ft_svg_uploads_allowed_user()) {
        $mimes['svg'] = 'image/svg+xml';
    }

    return $mimes;
});

add_filter('wp_handle_upload_prefilter', function ($file) {
    if (!ft_svg_uploads_is_svg_file($file)) {
        return $file;
    }

    if (!ft_svg_uploads_allowed_user()) {
        $file['error'] = __('SVG uploads are restricted to trusted administrators.', 'floors-today');
        return $file;
    }

    if (empty($file['tmp_name']) || !ft_svg_uploads_sanitize_file($file['tmp_name'])) {
        $file['error'] = __('This SVG could not be sanitized safely.', 'floors-today');
    }

    return $file;
});

add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename) {
    if (ft_svg_uploads_allowed_user() && ft_svg_uploads_is_svg_file($filename)) {
        $data['ext'] = 'svg';
        $data['type'] = 'image/svg+xml';
    }

    return $data;
}, 10, 3);
