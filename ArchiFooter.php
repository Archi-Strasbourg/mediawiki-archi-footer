<?php
/**
 * ArchiFooter class.
 */

namespace ArchiFooter;

/**
 * Add elements to the footer of every page.
 */
class ArchiFooter
{
    /**
     * Add elements to the footer.
     *
     * @param string $return HTML output
     * @param \Skin  $skin   Current skin
     *
     * @return string HTML
     */
    public static function main(&$return, \Skin $skin)
    {
        global $wgUser, $wgParser;
        $title = $skin->getTitle();
        $article = new \Article($title);
        if ($article->getID() > 0 && in_array($title->getNamespace(), [NS_ADDRESS, NS_ADDRESS_NEWS, NS_PERSON])) {
            //Edit button
            $return .= '<p>'.\Html::rawElement(
                'a',
                ['href' => $title->getFullURL(['veaction' => 'edit'])],
                wfMessage('contribute')->parse()
            ).'</p>';

            //Nearby addresses
            $params = new \DerivativeRequest(
                $skin->getRequest(),
                [
                    'action'  => 'browsebysubject',
                    'subject' => $title->getFullText(),
                ]
            );
            $api = new \ApiMain($params);
            $api->execute();
            $results = $api->getResult()->getResultData();
            $props = [];
            foreach ($results['query']['data'] as $data) {
                if (isset($data['property'])) {
                    $data['dataitem'][0]['item'] = preg_replace('/#[0-9]+#/', '', $data['dataitem'][0]['item']);
                    $data['dataitem'][0]['item'] = str_replace('_', ' ', $data['dataitem'][0]['item']);
                    switch ($data['property']) {
                        case 'Rue':
                            $props['street'] = $data['dataitem'][0]['item'];
                            break;
                        case 'Complément_Rue':
                            $props['street_prefix'] = $data['dataitem'][0]['item'];
                            break;
                        case 'Numéro':
                            $props['number'] = $data['dataitem'][0]['item'];
                            break;
                        case 'Adresse_complète':
                            $props['address'] = $data['dataitem'][0]['item'];
                            break;
                    }
                }
            }
            $return .= '<div class="noexcerpt">'.PHP_EOL;
            if (isset($props['street']) && isset($props['number'])) {
                $text = '{{#ask:';
                if (isset($props['address'])) {
                    $text .= '[[Adresse complète::!'.$props['address'].']]';
                }
                $text .= '[[Rue::'.$props['street'].']]';
                if (isset($props['street_prefix'])) {
                    $text .= '[[Complément_Rue::'.$props['street_prefix'].']]';
                }
                $text .= '[[Numéro::<<'.$props['number'].']]
                |limit=1
                |sort=Numéro
                |order=desc
                |searchlabel=
                |intro=<&nbsp;
                }}'.PHP_EOL.PHP_EOL.
                '{{#ask:';
                if (isset($props['address'])) {
                    $text .= '[[Adresse complète::!'.$props['address'].']]';
                }
                if (isset($props['street_prefix'])) {
                    $text .= '[[Complément_Rue::'.$props['street_prefix'].']]';
                }
                $text .= '[[Rue::'.$props['street'].']]
                [[Numéro::>>'.$props['number'].']]
                |limit=1
                |sort=Numéro
                |order=asc
                |searchlabel=
                |outro=&nbsp;>
                }}
                ';
                $output = $wgParser->parse($text, $title, new \ParserOptions($wgUser));
                $return .= $output->getText();
            }
            $return .= '</div>';

            //Comments
            $text = '== '.wfMessage('comments')->parse().' =='.PHP_EOL.
                '<comments />';
            $output = $wgParser->parse($text, $title, new \ParserOptions($wgUser));
            $return .= $output->getText();
        }
    }

    /**
     * Add scripts to <head>.
     */
    public static function addScripts()
    {
        global $wgOut;
        $wgOut->addModules('ext.comments.js');
    }
}
