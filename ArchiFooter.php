<?php
/**
 * ArchiFooter class.
 */

namespace ArchiFooter;

use ApiMain;
use Article;
use DerivativeRequest;
use Html;
use MWException;
use ParserOptions;
use Skin;
use Title;

/**
 * Add elements to the footer of every page.
 */
class ArchiFooter
{
    /**
     * @param Skin $skin
     * @param Title $title
     * @return array
     */
    private static function getProps(Skin $skin, Title $title)
    {
        $params = new DerivativeRequest(
            $skin->getRequest(),
            [
                'action'  => 'browsebysubject',
                'subject' => $title->getFullText(),
            ]
        );
        $api = new ApiMain($params);
        $api->execute();
        $results = $api->getResult()->getResultData();
        $props = [];
        foreach ($results['query']['data'] as $data) {
            if (isset($data['property'])) {
                $data['dataitem'][0]['item'] = preg_replace('/#[0-9]+##/', '', $data['dataitem'][0]['item']);
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
     * @param Skin $skin Current skin
     *
     * @return void
     */
    public static function main(string &$return, Skin $skin)
    {
        global $wgUser, $wgParser;
        $title = $skin->getTitle();
        $article = new Article($title);
        if ($article->getID() > 0 && in_array($title->getNamespace(), [NS_ADDRESS, NS_ADDRESS_NEWS, NS_PERSON])) {
            //Edit button
            $return .= '<p>'. Html::rawElement(
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
                $text .= '
                [[Numéro de rue::'.strtolower($props['street_prefix']).' '.
                    strtolower(
                        str_replace(
                            '('.$props['city'].')',
                            '',
                            $props['street']
                        )
                    ).
                    '; <<'.$props['number'].']]
                [[Ville::'.$props['city'].']]
                [[Pays::'.$props['country'].']]
                |limit=1
                |sort=Numéro
                |order=desc
                |searchlabel=
                |intro=<&nbsp;
                }}</div>'.PHP_EOL.PHP_EOL;

                $text .= '<div style="float:right;">{{#ask:';
                if (isset($props['address'])) {
                    $text .= '[[Adresse complète::!'.$props['address'].']]';
                }
                $text .= '
                [[Numéro de rue::'.strtolower($props['street_prefix']).' '.
                    strtolower(
                        str_replace(
                            '('.$props['city'].')',
                            '',
                            $props['street']
                        )
                    ).
                    '; >>'.$props['number'].']]
                [[Ville::'.$props['city'].']]
                [[Pays::'.$props['country'].']]
                |limit=1
                |sort=Numéro
                |order=asc
                |searchlabel=
                |outro=&nbsp;>
                }}
                </div>'.PHP_EOL.PHP_EOL;
                $output = $wgParser->parse($text, $title, new ParserOptions($wgUser));
                $return .= $output->getText();
            }
            $return .= '</div>';

            //Comments
            $text = '== '.wfMessage('comments')->parse().' =='.PHP_EOL.
                '<comments />';
            $output = $wgParser->parse($text, $title, new ParserOptions($wgUser));
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
        $wgOut->addModules('ext.archifooter');
    }
}
