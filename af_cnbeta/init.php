<?php
class Af_CnBeta extends Plugin {
    private $host;

    function about() {
        return array(1.0,
            'Remove AD for cnBeta',
            'Kurt Chen');
    }

    function init($host) {
        // Boilerplate to register hooks.
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
    }

    function api_version() {
        return 2;
    }

    function hook_article_filter($article) {
        $owner_uid = $article['owner_uid'];

        if (strpos($article['link'], 'cnbeta.com/') !== FALSE) {
            // This string will be saved in the `plugin_data` field in the
            // database for the corresponding item after applying the filter.
            // By checking for the existence of this value, we make sure the
            // filter isn't applied everytime the items are loaded.
            $plugin_string = 'af_cnbeta,' . $owner_uid . ':';

            if (strpos($article['plugin_data'], $plugin_string) === FALSE) {
                $doc = new DOMDocument('1.0', 'utf-8');
                @$doc->loadHTML(mb_convert_encoding($article['content'], 'HTML-ENTITIES', "UTF-8"));

                if ($doc) {
                    // Remove G+/FB/Twitter/etc. images and unnecessary links.
                    // Might be easier to just fetch the summary and comments
                    // and build a completely new DOMObject to discard
                    // everything else.
                    $search = new DomXPath($doc);
                    $tags = $search->query('//div|//img|//table|//br|//tr|//td|//a');
                    foreach ($tags as $tag) {
                        $tag->parentNode->removeChild($tag);
                    }

                    $body = $doc->getElementsByTagName('body')->item(0);

                    // If we fail to find this element, something has gone
                    // awry. Then do nothing and just return $article as it
                    // was. Else, write the new content to the database.
                    if ($body) {
                        $article['content'] = $doc->saveXML($body, LIBXML_NOEMPTYTAG);
                        $article['plugin_data'] = $plugin_string . $article['plugin_data'];
                    }
                }
            } else if (isset($article['stored']['content'])) {
                $article['content'] = $article['stored']['content'];
            }
        }
        return $article;
    }
}
?>

