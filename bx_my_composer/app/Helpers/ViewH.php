<?php

namespace App\Helpers;

class ViewH
{
    public static function isSidebarNeeded($curPage)
    {
        $parts_where_sidebar_is_needed = [
            'catalog',
            'personal\/cart',
            'personal\/order\/make',
            'contact-with',
        ];
        $regex = "~^".SITE_DIR."(".implode('|', $parts_where_sidebar_is_needed).")/~";
        return (bool)preg_match($regex, $curPage);
    }

    public static function isWideWorkareaNeeded($curPage)
    {
        $parts_where_sidebar_is_needed = [
            'contact-with',
        ];
        $regex = "~^".SITE_DIR."(".implode('|', $parts_where_sidebar_is_needed).")/~";
        return (bool)preg_match($regex, $curPage);
    }

    public static function isUsualTitleHideNeeded($curPage)
    {
        $parts_where_sidebar_is_needed = [
            'contact-with',
        ];
        $regex = "~^" . SITE_DIR . "(" . implode('|', $parts_where_sidebar_is_needed) . ")/~";
        return (bool)preg_match($regex, $curPage);
    }

    public static function WebFormInputAddAttributes($HTML_CODE, $tag_name, $new_attrs) {
        $dom = new \DOMDocument();
        $dom->loadHTML($HTML_CODE);
        $input = $dom->getElementsByTagName($tag_name)->item(0);
        if($input === null) {
            return '';
        }

        foreach ($new_attrs as $attrName => $attrValue) {
            $oldAttrExists = $input->hasAttribute($attrName);
            $attrValueForApply = $attrValue;
            if($oldAttrExists) {
                switch ($attrName) {
                    case "class":
                        $oldAttrValue = $input->getAttribute($attrName);
                        $attrValueForApply = implode(' ', [$oldAttrValue, $attrValue]);
                        break;
                }
            }
            $input->setAttribute($attrName, $attrValueForApply);
        }

        return $dom->saveHTML();
    }
}
