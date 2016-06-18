<?php
namespace ArchiFooter;

class ArchiFooter
{
    public static function main(&$data, \Skin $skin)
    {
        global $wgUser, $wgParser;
        $title = $skin->getTitle();
        if ($title->getNamespace() == NS_ADDRESS) {
            //Comments
            $text = '== Commentaires =='.PHP_EOL.
                '<comments />';
            $output = $wgParser->parse($text, $title, new \ParserOptions($wgUser));
            $data = $output->getText();
        }
    }

    public static function addScripts(&$parser)
    {
        global $wgOut;
        $wgOut->addModules('ext.comments.js');
    }
}
