<?php
namespace ArchiFooter;

class ArchiFooter
{
    public static function main(&$data, \Skin $skin)
    {
        global $wgUser, $wgParser;
        //Comments
        $text = '== Commentaires =='.PHP_EOL.
            '<comments />';
        $output = $wgParser->parse($text, $skin->getTitle(), new \ParserOptions($wgUser));
        $data = $output->getText();
    }
}
