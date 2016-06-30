<?php
namespace ArchiFooter;

class ArchiFooter
{
    public static function main(&$return, \Skin $skin)
    {
        global $wgUser, $wgParser;
        $title = $skin->getTitle();
        if ($title->getNamespace() == NS_ADDRESS) {
            //Edit button
            $text = '['.$title->getFullURL(array('veaction'=>'edit')).' Contribuez aussi à cet article]';
            $output = $wgParser->parse($text, $title, new \ParserOptions($wgUser));
            $return .= $output->getText();

            //Nearby addresses
            $params = new \DerivativeRequest(
                $skin->getRequest(),
                array(
                    'action'=>'browsebysubject',
                    'subject'=>$title->getFullText()
                )
            );
            $api = new \ApiMain($params);
            $api->execute();
            $results = $api->getResult()->getResultData();
            $props = array();
            foreach ($results['query']['data'] as $data) {
                if (isset($data['property'])) {
                    switch ($data['property']) {
                        case 'Rue':
                            $props['street'] = $data['dataitem'][0]['item'];
                            break;
                        case 'Numéro':
                            $props['number'] = $data['dataitem'][0]['item'];
                            break;
                    }
                }
            }
            if (isset($props['street']) && isset($props['number'])) {
                $text = '
<div class="noexcerpt">
{{#ask:
[[Rue::'.$props['street'].']]
[[Numéro::<<'.$props['number'].']]
|limit=1
|sort=Numéro
|order=desc
|searchlabel=
|intro=<&nbsp;
}}

{{#ask:
[[Rue::'.$props['street'].']]
[[Numéro::>>'.$props['number'].']]
|limit=1
|sort=Numéro
|order=asc
|searchlabel=
|outro=&nbsp;>
}}
</div>
                ';
                $output = $wgParser->parse($text, $title, new \ParserOptions($wgUser));
                $return .= $output->getText();
            }

            //Comments
            $text = '== Commentaires =='.PHP_EOL.
                '<comments />';
            $output = $wgParser->parse($text, $title, new \ParserOptions($wgUser));
            $return .= $output->getText();
        }
    }

    public static function addScripts(&$parser)
    {
        global $wgOut;
        $wgOut->addModules('ext.comments.js');
    }
}
