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
    private static function getProps(\Skin $skin, \Title $title)
    {
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
                    case 'Ville':
                        $props['city'] = $data['dataitem'][0]['item'];
                        break;
                    case 'Pays':
                        $props['country'] = $data['dataitem'][0]['item'];
                        break;
                }
            }
        }

        return $props;
    }

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
            $props = self::getProps($skin, $title);
            $return .= '<div class="noexcerpt">'.PHP_EOL;
            if (isset($props['street']) && isset($props['number']) && isset($props['city'])) {
                $text = '<div style="float:left;">{{#ask:';
                if (isset($props['address'])) {
                    $text .= '[[Adresse complète::!'.$props['address'].']]';
                }
                $text .= '[[Rue::'.$props['street'].']]';
                $text .= '[[Ville::'.$props['city'].']]';
                $text .= '[[Pays::'.$props['country'].']]';
                if (isset($props['street_prefix'])) {
                    $text .= '[[Complément_Rue::'.$props['street_prefix'].']]';
                }
                $text .= '[[Numéro::<<'.$props['number'].']]
                |limit=1
                |sort=Numéro
                |order=desc
                |searchlabel=
                |intro=<&nbsp;
                }}</div>'.PHP_EOL.PHP_EOL.
                '<div style="float:right;">{{#ask:';
                if (isset($props['address'])) {
                    $text .= '[[Adresse complète::!'.$props['address'].']]';
                }
                if (isset($props['street_prefix'])) {
                    $text .= '[[Complément_Rue::'.$props['street_prefix'].']]';
                }
                $text .= '[[Rue::'.$props['street'].']]
                [[Ville::'.$props['city'].']]
                [[Pays::'.$props['country'].']]
                [[Numéro::>>'.$props['number'].']]
                |limit=1
                |sort=Numéro
                |order=asc
                |searchlabel=
                |outro=&nbsp;>
                }}
                </div>';
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
